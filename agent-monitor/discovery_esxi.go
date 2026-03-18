package main

import (
	"bytes"
	"context"
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

	"github.com/vmware/govmomi"
	"github.com/vmware/govmomi/property"
	"github.com/vmware/govmomi/view"
	"github.com/vmware/govmomi/vim25/mo"
	"github.com/vmware/govmomi/vim25/types"
)

// EsxiDiscoveryJob job de découverte ESXi ou Proxmox
type EsxiDiscoveryJob struct {
	ID              int    `json:"id"`
	EsxiHostID      int    `json:"esxi_host_id"`
	EsxiName        string `json:"esxi_name"`
	Host            string `json:"host"`
	Port            int    `json:"port"`
	HypervisorType  string `json:"hypervisor_type"` // "esxi" ou "proxmox"
}

// EsxiDiscoveryCredentials identifiants pour un job ESXi
type EsxiDiscoveryCredentials struct {
	Success  bool   `json:"success"`
	Username string `json:"username"`
	Password string `json:"password"`
	Error    string `json:"error"`
}

// RunEsxiDiscovery polling des jobs ESXi et exécution via govmomi
func RunEsxiDiscovery(cfg *Config) {
	if cfg.SiteID <= 0 {
		return
	}
	baseURL := discoveryAPIBase(cfg)
	hostname := getHostname()
	jobsURL := baseURL + "/esxi_discovery_jobs.php?site_id=" + strconv.Itoa(cfg.SiteID) + "&agent_hostname=" + url.QueryEscape(hostname)
	credsURL := baseURL + "/esxi_discovery_credentials.php"
	resultURL := baseURL + "/esxi_discovery_jobs.php"

	client := &http.Client{
		Timeout: 30 * time.Second,
		Transport: &http.Transport{
			TLSClientConfig: &tls.Config{InsecureSkipVerify: true},
		},
	}

	req, err := http.NewRequest("GET", jobsURL, nil)
	if err != nil {
		log.Printf("esxi discovery: %v", err)
		return
	}
	req.Header.Set("X-Api-Key", cfg.APIKey)
	req.Header.Set("User-Agent", "ITManager-Monitor/1.0")

	resp, err := client.Do(req)
	if err != nil {
		log.Printf("esxi discovery GET jobs: %v", err)
		return
	}
	defer resp.Body.Close()

	if resp.StatusCode != 200 {
		return
	}

	body, _ := io.ReadAll(resp.Body)
	var out struct {
		Success bool             `json:"success"`
		Jobs    []EsxiDiscoveryJob `json:"jobs"`
	}
	if json.Unmarshal(body, &out) != nil || !out.Success || len(out.Jobs) == 0 {
		return
	}

	for _, job := range out.Jobs {
		credsReq, _ := http.NewRequest("GET", credsURL+"?job_id="+strconv.Itoa(job.ID)+"&site_id="+strconv.Itoa(cfg.SiteID), nil)
		credsReq.Header.Set("X-Api-Key", cfg.APIKey)
		credsResp, err := client.Do(credsReq)
		if err != nil {
			log.Printf("esxi discovery credentials job %d: %v", job.ID, err)
			postEsxiDiscoveryResult(client, resultURL, cfg.APIKey, job.ID, hostname, false, nil, nil, nil, err.Error())
			continue
		}
		credsBody, _ := io.ReadAll(credsResp.Body)
		credsResp.Body.Close()

		var creds EsxiDiscoveryCredentials
		if json.Unmarshal(credsBody, &creds) != nil || !creds.Success {
			errMsg := creds.Error
			if errMsg == "" {
				errMsg = "impossible de récupérer les identifiants"
			}
			log.Printf("esxi discovery job %d: %s", job.ID, errMsg)
			postEsxiDiscoveryResult(client, resultURL, cfg.APIKey, job.ID, hostname, false, nil, nil, nil, errMsg)
			continue
		}

		var hosts, vms, datastores []map[string]interface{}
		var errMsg string
		if job.HypervisorType == "proxmox" {
			hosts, vms, datastores, errMsg = discoverProxmox(job.Host, job.Port, creds.Username, creds.Password)
		} else {
			hosts, vms, datastores, errMsg = discoverEsxi(job.Host, job.Port, creds.Username, creds.Password)
		}
		if errMsg != "" {
			log.Printf("esxi discovery job %d (%s): %s", job.ID, job.EsxiName, errMsg)
			postEsxiDiscoveryResult(client, resultURL, cfg.APIKey, job.ID, hostname, false, hosts, vms, datastores, errMsg)
		} else {
			log.Printf("esxi discovery job %d (%s): OK, %d host(s), %d VM(s), %d datastore(s)", job.ID, job.EsxiName, len(hosts), len(vms), len(datastores))
			postEsxiDiscoveryResult(client, resultURL, cfg.APIKey, job.ID, hostname, true, hosts, vms, datastores, "")
		}
	}
}

func postEsxiDiscoveryResult(client *http.Client, resultURL, apiKey string, jobID int, hostname string, success bool, hosts, vms, datastores []map[string]interface{}, errMsg string) {
	payload := map[string]interface{}{
		"job_id":         jobID,
		"success":        success,
		"agent_hostname": hostname,
		"error_message":  errMsg,
	}
	if hosts != nil {
		payload["hosts"] = hosts
	}
	if vms != nil {
		payload["vms"] = vms
	}
	if datastores != nil {
		payload["datastores"] = datastores
	}
	body, _ := json.Marshal(payload)
	req, err := http.NewRequest("POST", resultURL, bytes.NewReader(body))
	if err != nil {
		return
	}
	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("X-Api-Key", apiKey)
	resp, err := client.Do(req)
	if err != nil {
		log.Printf("esxi discovery POST result: %v", err)
		return
	}
	resp.Body.Close()
}

func discoverEsxi(host string, port int, username, password string) (hosts []map[string]interface{}, vms []map[string]interface{}, datastores []map[string]interface{}, errMsg string) {
	if port <= 0 {
		port = 443
	}
	u, err := url.Parse(fmt.Sprintf("https://%s:%d/sdk", host, port))
	if err != nil {
		return nil, nil, nil, "URL invalide: " + err.Error()
	}
	u.User = url.UserPassword(username, password)

	ctx, cancel := context.WithTimeout(context.Background(), 60*time.Second)
	defer cancel()

	// Insecure pour certificats auto-signés (ESXi)
	client, err := govmomi.NewClient(ctx, u, true)
	if err != nil {
		return nil, nil, nil, "connexion impossible: " + err.Error()
	}
	defer client.Logout(ctx)

	// Lister toutes les VMs (view fonctionne sur ESXi standalone et vCenter)
	mgr := view.NewManager(client.Client)
	v, err := mgr.CreateContainerView(ctx, client.ServiceContent.RootFolder, []string{"VirtualMachine", "HostSystem", "Datastore"}, true)
	if err != nil {
		return nil, nil, nil, "création vue: " + err.Error()
	}
	defer v.Destroy(ctx)

	// Hosts (avec configManager pour AutoStart)
	var hostList []mo.HostSystem
	err = v.Retrieve(ctx, []string{"HostSystem"}, []string{"name", "hardware", "config", "configManager", "summary"}, &hostList)
	if err != nil {
		log.Printf("esxi retrieve hosts: %v", err)
	}
	for _, h := range hostList {
		he := map[string]interface{}{
			"name":    h.Name,
			"mo_ref":  h.Reference().String(),
			"model":   "",
			"cpu_model": "",
			"cpu_mhz": nil,
			"cpu_cores": nil,
			"ram_total_mb": nil,
			"ram_free_mb": nil,
		}
		if h.Summary.Hardware != nil {
			he["cpu_mhz"] = h.Summary.Hardware.CpuMhz
			he["cpu_model"] = h.Summary.Hardware.CpuModel
			he["cpu_cores"] = h.Summary.Hardware.NumCpuCores
			ramTotal := h.Summary.Hardware.MemorySize / (1024 * 1024)
			he["ram_total_mb"] = ramTotal
			if h.Summary.QuickStats.OverallMemoryUsage > 0 {
				he["ram_free_mb"] = ramTotal - int64(h.Summary.QuickStats.OverallMemoryUsage)
			}
		}
		if h.Summary.Hardware != nil && (h.Summary.Hardware.Vendor != "" || h.Summary.Hardware.Model != "") {
			he["model"] = strings.TrimSpace(h.Summary.Hardware.Vendor + " " + h.Summary.Hardware.Model)
		}
		if he["model"] == "" && h.Hardware != nil && h.Hardware.SystemInfo.Vendor != "" {
			he["model"] = strings.TrimSpace(h.Hardware.SystemInfo.Vendor + " " + h.Hardware.SystemInfo.Model)
		}
		hosts = append(hosts, he)
	}

	// Map host -> AutoStart powerInfo (vmRef -> StartAction)
	autostartByHost := make(map[string]map[string]string)
	pc := property.DefaultCollector(client.Client)
	for i := range hostList {
		h := &hostList[i]
		if h.ConfigManager.AutoStartManager == nil {
			continue
		}
		var mhas mo.HostAutoStartManager
		if err := pc.RetrieveOne(ctx, *h.ConfigManager.AutoStartManager, []string{"config"}, &mhas); err != nil {
			log.Printf("esxi autostart host %s: %v", h.Name, err)
			continue
		}
		hostKey := h.Reference().Value
		autostartByHost[hostKey] = make(map[string]string)
		for _, pi := range mhas.Config.PowerInfo {
			autostartByHost[hostKey][pi.Key.Value] = pi.StartAction
		}
	}

	// VMs (avec config pour devices/disques)
	var vmList []mo.VirtualMachine
	err = v.Retrieve(ctx, []string{"VirtualMachine"}, []string{"name", "config", "runtime", "summary"}, &vmList)
	if err != nil {
		return nil, nil, nil, "récupération VMs: " + err.Error()
	}
	for _, vm := range vmList {
		vmEntry := map[string]interface{}{
			"name":         vm.Name,
			"mo_ref":       vm.Reference().Value,
			"power_state": "",
			"guest_os":     "",
			"cpu_count":    0,
			"ram_mb":       0,
			"auto_start":   false,
			"disks":        []map[string]interface{}{},
		}
		if vm.Runtime.PowerState != "" {
			vmEntry["power_state"] = string(vm.Runtime.PowerState)
		}
		if vm.Config != nil {
			vmEntry["guest_os"] = vm.Config.GuestFullName
			vmEntry["cpu_count"] = vm.Config.Hardware.NumCPU
			vmEntry["ram_mb"] = vm.Config.Hardware.MemoryMB
			if vm.Config.Uuid != "" {
				vmEntry["uuid"] = normalizeVmwareUuid(vm.Config.Uuid)
			}
			// Disques (VirtualDisk)
			disks := extractVmDisks(vm.Config.Hardware.Device)
			if len(disks) > 0 {
				vmEntry["disks"] = disks
			}
		}
		// Démarrage automatique (depuis HostAutoStartManager)
		if vm.Runtime.Host != nil {
			hostRef := vm.Runtime.Host.Value
			if m, ok := autostartByHost[hostRef]; ok {
				if act, ok := m[vm.Reference().Value]; ok && act == string(types.AutoStartActionPowerOn) {
					vmEntry["auto_start"] = true
				}
			}
		}
		vms = append(vms, vmEntry)
	}

	// Datastores
	var dsList []mo.Datastore
	err = v.Retrieve(ctx, []string{"Datastore"}, []string{"name", "summary"}, &dsList)
	if err != nil {
		log.Printf("esxi retrieve datastores: %v", err)
	}
	for _, ds := range dsList {
		capBytes := ds.Summary.Capacity
		freeBytes := ds.Summary.FreeSpace
		dsEntry := map[string]interface{}{
			"name":       ds.Name,
			"capacity":   capBytes,
			"free":       freeBytes,
			"capacity_gb": capBytes / (1024 * 1024 * 1024),
			"free_gb":    freeBytes / (1024 * 1024 * 1024),
		}
		datastores = append(datastores, dsEntry)
	}

	return hosts, vms, datastores, ""
}

// normalizeVmwareUuid convertit l'UUID VMware (format ISO 11578) en format Windows/Linux standard
// VMware: premier 3 groupes en big-endian, Windows: little-endian
// Ex: VMware "33221100-5544-7766-8899-AABBCCDDEEFF" -> Windows "00112233-4455-6677-8899-AABBCCDDEEFF"
func normalizeVmwareUuid(uuid string) string {
	repl := strings.NewReplacer(" ", "", "-", "", ":", "")
	raw := strings.ToUpper(repl.Replace(uuid))
	if len(raw) != 32 || !isHex(raw) {
		return uuid
	}
	rev := func(s string) string {
		r := []rune(s)
		for i, j := 0, len(r)-2; i < j; i, j = i+2, j-2 {
			r[i], r[i+1], r[j], r[j+1] = r[j], r[j+1], r[i], r[i+1]
		}
		return string(r)
	}
	b0 := rev(raw[0:8])
	b1 := rev(raw[8:12])
	b2 := rev(raw[12:16])
	return b0 + "-" + b1 + "-" + b2 + "-" + raw[16:20] + "-" + raw[20:32]
}

func isHex(s string) bool {
	for _, c := range s {
		if (c < '0' || c > '9') && (c < 'A' || c > 'F') && (c < 'a' || c > 'f') {
			return false
		}
	}
	return true
}

// extractVmDisks extrait les disques virtuels (label, capacité Go, datastore, filename)
func extractVmDisks(devices []types.BaseVirtualDevice) []map[string]interface{} {
	var disks []map[string]interface{}
	for _, d := range devices {
		disk, ok := d.(*types.VirtualDisk)
		if !ok {
			continue
		}
		entry := map[string]interface{}{
			"label":        "",
			"capacity_gb": 0.0,
			"datastore":    "",
			"filename":     "",
		}
		if disk.DeviceInfo != nil {
			if desc := disk.DeviceInfo.GetDescription(); desc != nil {
				entry["label"] = desc.Label
			}
		}
		if disk.CapacityInKB > 0 {
			entry["capacity_gb"] = float64(disk.CapacityInKB) / 1024 / 1024
		} else if disk.CapacityInBytes > 0 {
			entry["capacity_gb"] = float64(disk.CapacityInBytes) / 1024 / 1024 / 1024
		}
		if disk.Backing != nil {
			if info, ok := disk.Backing.(types.BaseVirtualDeviceFileBackingInfo); ok {
				fbi := info.GetVirtualDeviceFileBackingInfo()
				entry["filename"] = fbi.FileName
				if fbi.Datastore != nil {
					entry["datastore"] = fbi.Datastore.Value
				}
				// fileName format: [datastore1] path/vm.vmdk -> extraire datastore du nom si besoin
				if s := fbi.FileName; len(s) >= 2 && s[0] == '[' {
					if end := strings.Index(s, "]"); end > 1 {
						entry["datastore"] = s[1:end]
					}
				}
			}
		}
		disks = append(disks, entry)
	}
	return disks
}
