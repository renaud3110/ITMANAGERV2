package main

import (
	"bytes"
	"crypto/tls"
	"encoding/json"
	"fmt"
	"io"
	"log"
	"net/http"
	"net/url"
	"strconv"
	"strings"
	"time"
)

// DiscoveryJob job de découverte NAS récupéré de l'API
type DiscoveryJob struct {
	ID      int    `json:"id"`
	NasID   int    `json:"nas_id"`
	NasName string `json:"nas_name"`
	Host    string `json:"host"`
	Port    int    `json:"port"`
	Type    string `json:"type"`
}

// DiscoveryCredentials identifiants pour un job
type DiscoveryCredentials struct {
	Success  bool   `json:"success"`
	Username string `json:"username"`
	Password string `json:"password"`
	Error    string `json:"error"`
}

// discoveryAPIBase URL de base de l'API (sans chemin)
func discoveryAPIBase(cfg *Config) string {
	url := strings.TrimRight(cfg.APIURL, "/")
	url = strings.TrimSuffix(url, "inventory.php")
	url = strings.TrimSuffix(url, "monitor_telemetry.php")
	return strings.TrimRight(url, "/")
}

// RunDiscovery polling des jobs de découverte et exécution
func RunDiscovery(cfg *Config) {
	if cfg.SiteID <= 0 {
		return
	}
	baseURL := discoveryAPIBase(cfg)
	hostname := getHostname()
	jobsURL := baseURL + "/discovery_jobs.php?site_id=" + strconv.Itoa(cfg.SiteID) + "&agent_hostname=" + url.QueryEscape(hostname)
	credsURL := baseURL + "/discovery_credentials.php"
	resultURL := baseURL + "/discovery_jobs.php"

	client := &http.Client{
		Timeout: 30 * time.Second,
		Transport: &http.Transport{
			TLSClientConfig: &tls.Config{InsecureSkipVerify: true},
		},
	}

	req, err := http.NewRequest("GET", jobsURL, nil)
	if err != nil {
		log.Printf("discovery: %v", err)
		return
	}
	req.Header.Set("X-Api-Key", cfg.APIKey)
	req.Header.Set("User-Agent", "ITManager-Monitor/1.0")

	resp, err := client.Do(req)
	if err != nil {
		log.Printf("discovery GET jobs: %v", err)
		return
	}
	defer resp.Body.Close()

	if resp.StatusCode != 200 {
		return
	}

	body, _ := io.ReadAll(resp.Body)
	var out struct {
		Success bool          `json:"success"`
		Jobs    []DiscoveryJob `json:"jobs"`
	}
	if json.Unmarshal(body, &out) != nil || !out.Success || len(out.Jobs) == 0 {
		return
	}

	for _, job := range out.Jobs {
		// Marquer comme running (optionnel, on le fera côté serveur si besoin)
		// Récupérer les identifiants
		credsReq, _ := http.NewRequest("GET", credsURL+"?job_id="+strconv.Itoa(job.ID)+"&site_id="+strconv.Itoa(cfg.SiteID), nil)
		credsReq.Header.Set("X-Api-Key", cfg.APIKey)
		credsResp, err := client.Do(credsReq)
		if err != nil {
			log.Printf("discovery credentials job %d: %v", job.ID, err)
			postDiscoveryResult(client, resultURL, cfg.APIKey, job.ID, hostname, false, nil, nil, nil, err.Error())
			continue
		}
		credsBody, _ := io.ReadAll(credsResp.Body)
		credsResp.Body.Close()

		var creds DiscoveryCredentials
		if json.Unmarshal(credsBody, &creds) != nil || !creds.Success {
			errMsg := creds.Error
			if errMsg == "" {
				errMsg = "impossible de récupérer les identifiants"
			}
			log.Printf("discovery job %d: %s", job.ID, errMsg)
			postDiscoveryResult(client, resultURL, cfg.APIKey, job.ID, hostname, false, nil, nil, nil, errMsg)
			continue
		}

		// Diagnostic: longueur des identifiants (sans les exposer)
		log.Printf("discovery job %d (%s): user_len=%d pass_len=%d", job.ID, job.NasName, len(creds.Username), len(creds.Password))

		// Exécuter la découverte Synology
		shares, volumes, disks, errMsg := discoverSynology(job.Host, job.Port, creds.Username, creds.Password)
		if errMsg != "" {
			log.Printf("discovery job %d (%s): %s", job.ID, job.NasName, errMsg)
			postDiscoveryResult(client, resultURL, cfg.APIKey, job.ID, hostname, false, shares, volumes, disks, errMsg)
		} else {
			log.Printf("discovery job %d (%s): OK, %d partages, %d volumes, %d disques", job.ID, job.NasName, len(shares), len(volumes), len(disks))
			postDiscoveryResult(client, resultURL, cfg.APIKey, job.ID, hostname, true, shares, volumes, disks, "")
		}
	}
}

func postDiscoveryResult(client *http.Client, url, apiKey string, jobID int, hostname string, success bool, shares, volumes, disks []map[string]interface{}, errMsg string) {
	payload := map[string]interface{}{
		"job_id":         jobID,
		"success":        success,
		"agent_hostname": hostname,
		"error_message":  errMsg,
	}
	if shares != nil {
		payload["shares"] = shares
	}
	if volumes != nil {
		payload["volumes"] = volumes
	}
	if disks != nil {
		payload["disks"] = disks
	}
	body, _ := json.Marshal(payload)
	req, err := http.NewRequest("POST", url, bytes.NewReader(body))
	if err != nil {
		return
	}
	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("X-Api-Key", apiKey)
	resp, err := client.Do(req)
	if err != nil {
		log.Printf("discovery POST result: %v", err)
		return
	}
	resp.Body.Close()
}

func discoverSynology(host string, port int, username, password string) (shares []map[string]interface{}, volumes []map[string]interface{}, disks []map[string]interface{}, errMsg string) {
	useHttps := (port == 5001 || port == 443)
	scheme := "http"
	if useHttps {
		scheme = "https"
	}
	baseURL := fmt.Sprintf("%s://%s:%d", scheme, host, port)
	client := &http.Client{
		Timeout: 15 * time.Second,
		Transport: &http.Transport{
			TLSClientConfig: &tls.Config{InsecureSkipVerify: true},
		},
	}

	// Login FileStation (pour partages + storage)
	loginURL := baseURL + "/webapi/auth.cgi"
	formData := url.Values{}
	formData.Set("api", "SYNO.API.Auth")
	formData.Set("version", "6")
	formData.Set("method", "login")
	formData.Set("account", username)
	formData.Set("passwd", password)
	formData.Set("format", "sid")
	formData.Set("session", "FileStation")

	loginResp, err := client.PostForm(loginURL, formData)
	if err != nil {
		return nil, nil, nil, "connexion impossible: " + err.Error()
	}
	loginBody, _ := io.ReadAll(loginResp.Body)
	loginResp.Body.Close()

	var loginData struct {
		Data struct {
			SID string `json:"sid"`
		} `json:"data"`
		Error struct {
			Code int `json:"code"`
		} `json:"error"`
	}
	if json.Unmarshal(loginBody, &loginData) != nil {
		return nil, nil, nil, "réponse login invalide"
	}
	if loginData.Data.SID == "" {
		code := loginData.Error.Code
		if code == 400 || code == 401 {
			return nil, nil, nil, "identifiants incorrects (vérifiez user/mdp, 2FA désactivée?)"
		}
		if code == 403 {
			return nil, nil, nil, "accès refusé (compte peut-être désactivé)"
		}
		return nil, nil, nil, fmt.Sprintf("échec auth Synology (code %d)", code)
	}
	sid := loginData.Data.SID

	// 1. Partages: SYNO.Core.Share (tous les dossiers partagés admin) + FileStation list_share
	shareMap := make(map[string]map[string]interface{})
	var fsVolumeStatus map[string]struct {
		Free  int64 `json:"free_space"`
		Total int64 `json:"total_space"`
	}

	// Essayer SYNO.Core.Share pour tous les partages (Paramètres > Dossiers partagés)
	coreShareURL := fmt.Sprintf("%s/webapi/entry.cgi?api=SYNO.Core.Share&version=1&method=list&_sid=%s", baseURL, url.QueryEscape(sid))
	coreResp, err := client.Get(coreShareURL)
	if err == nil {
		coreBody, _ := io.ReadAll(coreResp.Body)
		coreResp.Body.Close()
		var coreData struct {
			Data struct {
				Shares []struct {
					Name   string `json:"name"`
					VolID  string `json:"volume_id"`
					Desc   string `json:"desc"`
					Path   string `json:"path"`
				} `json:"shares"`
			} `json:"data"`
			Error struct {
				Code int `json:"code"`
			} `json:"error"`
		}
		if json.Unmarshal(coreBody, &coreData) == nil && coreData.Error.Code == 0 && coreData.Data.Shares != nil {
			for _, s := range coreData.Data.Shares {
				if s.Name != "" {
					shareMap[s.Name] = map[string]interface{}{
						"name":  s.Name,
						"path":  s.Path,
						"desc":  s.Desc,
						"vol_id": s.VolID,
					}
				}
			}
		}
	}

	// FileStation list_share (compléter si Core n'a pas tout)
	fsShareURL := fmt.Sprintf("%s/webapi/entry.cgi?api=SYNO.FileStation.List&version=2&method=list_share&limit=100&offset=0&additional=volume_status&_sid=%s", baseURL, url.QueryEscape(sid))
	fsResp, err := client.Get(fsShareURL)
	if err == nil {
		fsBody, _ := io.ReadAll(fsResp.Body)
		fsResp.Body.Close()
		var fsData struct {
			Data struct {
				Shares []struct {
					Name    string `json:"name"`
					VolPath string `json:"vol_path"`
					Desc    string `json:"desc"`
				} `json:"shares"`
				VolumeStatus map[string]struct {
					Free  int64 `json:"free_space"`
					Total int64 `json:"total_space"`
				} `json:"volume_status"`
			} `json:"data"`
		}
		if json.Unmarshal(fsBody, &fsData) == nil && fsData.Data.Shares != nil {
			fsVolumeStatus = fsData.Data.VolumeStatus
			for _, s := range fsData.Data.Shares {
				if _, ok := shareMap[s.Name]; !ok {
					shareMap[s.Name] = map[string]interface{}{
						"name": s.Name,
						"path": s.VolPath,
						"desc": s.Desc,
					}
				}
			}
		}
	}

	for _, m := range shareMap {
		shares = append(shares, m)
	}

	// 2. Volumes et disques: SYNO.Storage.CGI.Storage — nécessite session StorageManager
	storageSID := sid
	formData.Set("session", "StorageManager")
	storageLoginResp, errStorage := client.PostForm(loginURL, formData)
	if errStorage == nil {
		storageLoginBody, _ := io.ReadAll(storageLoginResp.Body)
		storageLoginResp.Body.Close()
		var storageLogin struct {
			Data struct {
				SID string `json:"sid"`
			} `json:"data"`
			Error struct {
				Code int `json:"code"`
			} `json:"error"`
		}
		if json.Unmarshal(storageLoginBody, &storageLogin) == nil && storageLogin.Data.SID != "" {
			storageSID = storageLogin.Data.SID
		}
	}
	formData.Set("session", "FileStation")

	// Découvrir l'API Storage via SYNO.API.Info (path, version)
	var storagePath string
	storageVersion := 1
	queryURL := baseURL + "/webapi/query.cgi?api=SYNO.API.Info&version=1&method=query&query=SYNO.Storage.CGI.Storage"
	qResp, qErr := client.Get(queryURL)
	if qErr == nil {
		qBody, _ := io.ReadAll(qResp.Body)
		qResp.Body.Close()
		var qData struct {
			Data struct {
				Storage struct {
					Path      string `json:"path"`
					MinVersion int    `json:"minVersion"`
					MaxVersion int    `json:"maxVersion"`
				} `json:"SYNO.Storage.CGI.Storage"`
			} `json:"data"`
		}
		if json.Unmarshal(qBody, &qData) == nil && qData.Data.Storage.Path != "" {
			storagePath = qData.Data.Storage.Path
			if qData.Data.Storage.MaxVersion > 0 {
				storageVersion = qData.Data.Storage.MaxVersion
			}
		}
	}
	if storagePath == "" {
		storagePath = "entry.cgi"
	}

	storageEndpoints := []struct {
		url string
	}{
		{fmt.Sprintf("%s/webapi/%s?api=SYNO.Storage.CGI.Storage&version=%d&method=load_info&_sid=%s", baseURL, storagePath, storageVersion, url.QueryEscape(storageSID))},
		{fmt.Sprintf("%s/webapi/entry.cgi?api=SYNO.Storage.CGI.Storage&version=1&method=load_info&_sid=%s", baseURL, url.QueryEscape(storageSID))},
		{fmt.Sprintf("%s/webapi/entry.cgi?api=SYNO.Storage.CGI.Storage&version=1&method=load&_sid=%s", baseURL, url.QueryEscape(storageSID))},
		{fmt.Sprintf("%s/webman/modules/StorageManager/storagehandler.cgi?api=SYNO.Storage.CGI.Storage&version=1&action=load_info&_sid=%s", baseURL, url.QueryEscape(storageSID))},
	}
	for _, ep := range storageEndpoints {
		stResp, err := client.Get(ep.url)
		if err != nil {
			continue
		}
		stBody, _ := io.ReadAll(stResp.Body)
		stResp.Body.Close()

		var stData struct {
			Data struct {
				Volumes []struct {
					ID         string `json:"id"`
					DeviceType string `json:"device_type"`
					Status     string `json:"status"`
					FsType     string `json:"fs_type"`
					Size       struct {
						Total string `json:"total"`
						Used  string `json:"used"`
					} `json:"size"`
					DisplayName string `json:"display_name"`
					Name        string `json:"name"`
					SizeInt     int64  `json:"size"`
					UsedSizeInt int64  `json:"used_size"`
				} `json:"volumes"`
				Disks []struct {
					ID           string `json:"id"`
					Name         string `json:"name"`
					Device       string `json:"device"`
					Status       string `json:"status"`
					SmartStatus  string `json:"smart_status"`
					Temp         int    `json:"temp"`
					Model        string `json:"model"`
					Vendor       string `json:"vendor"`
					SizeTotal    int64  `json:"size_total"`
					ExceedBad    bool   `json:"exceed_bad_sector_thr"`
					BelowLife    bool   `json:"below_remain_life_thr"`
				} `json:"disks"`
			} `json:"data"`
			Error struct {
				Code   int    `json:"code"`
				Errors interface{} `json:"errors"`
			} `json:"error"`
		}
		if json.Unmarshal(stBody, &stData) != nil {
			continue
		}
		if stData.Error.Code != 0 {
			// 102=API inexistante, 119=session invalide, 105=privilège insuffisant
			log.Printf("discovery Storage API err code=%d", stData.Error.Code)
			continue
		}
		// Volumes
		for _, v := range stData.Data.Volumes {
			total, okTotal := parseSizeStr(v.Size.Total)
			used, okUsed := parseSizeStr(v.Size.Used)
			if !okTotal || !okUsed {
				total = v.SizeInt
				used = v.UsedSizeInt
			}
			name := v.DisplayName
			if name == "" {
				name = v.Name
			}
			if name == "" {
				name = v.ID
			}
			volumes = append(volumes, map[string]interface{}{
				"name":        name,
				"id":          v.ID,
				"size":        total,
				"used":        used,
				"status":      v.Status,
				"device_type": v.DeviceType,
				"fs_type":     v.FsType,
			})
		}
		// Disques
		for _, d := range stData.Data.Disks {
			disk := map[string]interface{}{
				"name":  d.Name,
				"id":    d.ID,
				"device": d.Device,
				"status": d.Status,
				"smart_status": d.SmartStatus,
				"model": d.Model,
				"vendor": d.Vendor,
				"size_total": d.SizeTotal,
			}
			if d.Temp > 0 {
				disk["temp"] = d.Temp
			}
			if d.ExceedBad {
				disk["exceed_bad_sector"] = true
			}
			if d.BelowLife {
				disk["below_life_threshold"] = true
			}
			disks = append(disks, disk)
		}
		if len(volumes) > 0 || len(disks) > 0 {
			break
		}
	}

	// Fallback: volumes depuis FileStation list_share additional=volume_status
	if len(volumes) == 0 && fsVolumeStatus != nil {
		for volName, vs := range fsVolumeStatus {
			if vs.Total > 0 {
				volumes = append(volumes, map[string]interface{}{
					"name":   volName,
					"size":   vs.Total,
					"used":   vs.Total - vs.Free,
					"status": "normal",
				})
			}
		}
	}

	// Logout
	logoutURL := fmt.Sprintf("%s/webapi/auth.cgi?api=SYNO.API.Auth&version=1&method=logout&_sid=%s", baseURL, url.QueryEscape(sid))
	client.Get(logoutURL)
	return shares, volumes, disks, ""
}

func parseSizeStr(s string) (int64, bool) {
	if s == "" {
		return 0, false
	}
	n, err := strconv.ParseInt(s, 10, 64)
	return n, err == nil
}
