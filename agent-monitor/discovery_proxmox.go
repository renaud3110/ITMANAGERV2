package main

import (
	"crypto/tls"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"net/url"
	"strconv"
	"strings"
	"time"
)

// discoverProxmox se connecte à l'API Proxmox VE et récupère nodes, VMs (QEMU), containers (LXC), storage
func discoverProxmox(host string, port int, username, password string) (hosts []map[string]interface{}, vms []map[string]interface{}, datastores []map[string]interface{}, errMsg string) {
	if port <= 0 {
		port = 8006
	}
	baseURL := fmt.Sprintf("https://%s:%d/api2/json", host, port)

	// Proxmox exige username@realm (ex: root@pam). Si absent, ajouter @pam
	authUser := strings.TrimSpace(username)
	if authUser != "" && !strings.Contains(authUser, "@") {
		authUser = authUser + "@pam"
	}

	// 1. Obtenir le ticket d'auth
	ticketBody := url.Values{}
	ticketBody.Set("username", authUser)
	ticketBody.Set("password", password)

	client := &http.Client{
		Timeout: 30 * time.Second,
		Transport: &http.Transport{
			TLSClientConfig: &tls.Config{InsecureSkipVerify: true},
		},
	}

	req, err := http.NewRequest("POST", baseURL+"/access/ticket", strings.NewReader(ticketBody.Encode()))
	if err != nil {
		return nil, nil, nil, "requête ticket: " + err.Error()
	}
	req.Header.Set("Content-Type", "application/x-www-form-urlencoded")

	resp, err := client.Do(req)
	if err != nil {
		return nil, nil, nil, "connexion impossible: " + err.Error()
	}
	defer resp.Body.Close()
	body, _ := io.ReadAll(resp.Body)

	var ticketResp struct {
		Data struct {
			Ticket             string `json:"ticket"`
			CSRFPreventionToken string `json:"CSRFPreventionToken"`
		} `json:"data"`
		Errors map[string]string `json:"errors"`
	}
	if json.Unmarshal(body, &ticketResp) != nil || ticketResp.Data.Ticket == "" {
		msg := "authentification échouée (identifiants ou accès API)"
		if len(ticketResp.Errors) > 0 {
			for _, v := range ticketResp.Errors {
				msg = "Proxmox: " + v
				break
			}
		}
		return nil, nil, nil, msg
	}

	cookie := &http.Cookie{Name: "PVEAuthCookie", Value: ticketResp.Data.Ticket}
	_ = ticketResp.Data.CSRFPreventionToken // utilisé pour POST/PUT/DELETE

	doGet := func(path string) ([]byte, error) {
		r, _ := http.NewRequest("GET", baseURL+path, nil)
		r.AddCookie(cookie)
		res, err := client.Do(r)
		if err != nil {
			return nil, err
		}
		defer res.Body.Close()
		return io.ReadAll(res.Body)
	}

	// 2. Lister les nodes (hosts)
	nodesBody, err := doGet("/nodes")
	if err != nil {
		return nil, nil, nil, "nodes: " + err.Error()
	}
	var nodesResp struct {
		Data []struct {
			Node   string `json:"node"`
			Status string `json:"status"`
			CPU    int    `json:"maxcpu"`
			Memory int64  `json:"maxmem"`
			Uptime int64  `json:"uptime"`
		} `json:"data"`
	}
	if json.Unmarshal(nodesBody, &nodesResp) == nil && len(nodesResp.Data) > 0 {
		for _, n := range nodesResp.Data {
			hosts = append(hosts, map[string]interface{}{
				"name":          n.Node,
				"model":         "Proxmox VE",
				"cpu_cores":     n.CPU,
				"ram_total_mb":  n.Memory / 1024 / 1024,
				"status":       n.Status,
			})
		}
	}

	// 3. Lister storage (datastores)
	allStorage := make(map[string]map[string]interface{})
	for _, n := range nodesResp.Data {
		stBody, err := doGet("/nodes/" + n.Node + "/storage")
		if err != nil {
			continue
		}
		var stResp struct {
			Data []struct {
				Storage string `json:"storage"`
				Type    string `json:"type"`
				Content string `json:"content"`
				Total   int64  `json:"total"`
				Used    int64  `json:"used"`
				Avail   int64  `json:"avail"`
			} `json:"data"`
		}
		if json.Unmarshal(stBody, &stResp) == nil {
			for _, s := range stResp.Data {
				if _, ok := allStorage[s.Storage]; !ok {
					totalGB := int64(0)
					usedGB := int64(0)
					freeGB := int64(0)
					if s.Total > 0 {
						totalGB = s.Total / 1024 / 1024 / 1024
					}
					if s.Used > 0 {
						usedGB = s.Used / 1024 / 1024 / 1024
					}
					if s.Avail > 0 {
						freeGB = s.Avail / 1024 / 1024 / 1024
					}
					allStorage[s.Storage] = map[string]interface{}{
						"name":       s.Storage,
						"type":       s.Type,
						"capacity_gb": totalGB,
						"free_gb":    freeGB,
						"used_gb":   usedGB,
					}
				}
			}
		}
	}
	for _, ds := range allStorage {
		datastores = append(datastores, ds)
	}

	// 4. Lister VMs QEMU et LXC par node
	for _, n := range nodesResp.Data {
		// QEMU VMs
		qemuBody, err := doGet("/nodes/" + n.Node + "/qemu")
		if err == nil {
			var qemuResp struct {
				Data []struct {
					Vmid   interface{} `json:"vmid"`
					Name   string     `json:"name"`
					Status string     `json:"status"`
					MaxMem int64      `json:"maxmem"`
					Cpus   int        `json:"cpus"`
				} `json:"data"`
			}
			if json.Unmarshal(qemuBody, &qemuResp) == nil {
				for _, vm := range qemuResp.Data {
					vmid := ""
					switch v := vm.Vmid.(type) {
					case float64:
						vmid = strconv.Itoa(int(v))
					case string:
						vmid = v
					}
					vmName := vm.Name
					if vmName == "" {
						vmName = n.Node + "/" + vmid
					}
					powerState := "poweredOff"
					if vm.Status == "running" {
						powerState = "poweredOn"
					}
					vms = append(vms, map[string]interface{}{
						"name":        vmName,
						"vmid":       vmid,
						"node":       n.Node,
						"type":       "qemu",
						"power_state": powerState,
						"guest_os":   "QEMU VM",
						"cpu_count":  vm.Cpus,
						"ram_mb":     vm.MaxMem / 1024 / 1024,
						"mo_ref":     "qemu/" + vmid,
					})
				}
			}
		}

		// LXC containers
		lxcBody, err := doGet("/nodes/" + n.Node + "/lxc")
		if err == nil {
			var lxcResp struct {
				Data []struct {
					Vmid   interface{} `json:"vmid"`
					Name   string     `json:"name"`
					Status string     `json:"status"`
					MaxMem int64      `json:"maxmem"`
					Cpus   int        `json:"cpus"`
				} `json:"data"`
			}
			if json.Unmarshal(lxcBody, &lxcResp) == nil {
				for _, ct := range lxcResp.Data {
					vmid := ""
					switch v := ct.Vmid.(type) {
					case float64:
						vmid = strconv.Itoa(int(v))
					case string:
						vmid = v
					}
					vmName := ct.Name
					if vmName == "" {
						vmName = n.Node + "/" + vmid
					}
					powerState := "poweredOff"
					if ct.Status == "running" {
						powerState = "poweredOn"
					}
					vms = append(vms, map[string]interface{}{
						"name":        vmName,
						"vmid":       vmid,
						"node":       n.Node,
						"type":       "lxc",
						"power_state": powerState,
						"guest_os":   "LXC container",
						"cpu_count":  ct.Cpus,
						"ram_mb":     ct.MaxMem / 1024 / 1024,
						"mo_ref":     "lxc/" + vmid,
					})
				}
			}
		}
	}

	return hosts, vms, datastores, ""
}
