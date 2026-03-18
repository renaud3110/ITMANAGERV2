package main

import (
	"bytes"
	"encoding/json"
	"fmt"
	"io"
	"log"
	"net/http"
	"strings"
	"time"
)

// TelemetryPayload données envoyées à l'API
type TelemetryPayload struct {
	Hostname       string  `json:"hostname"`
	SerialNumber   string  `json:"serial_number"`
	SiteID         int     `json:"site_id"`
	DeviceType     string  `json:"device_type,omitempty"` // "pc" ou "server" — permet la création auto du serveur côté API
	CPUTemp        float64 `json:"cpu_temp,omitempty"`
	GPUTemp        float64 `json:"gpu_temp,omitempty"`
	Online         bool    `json:"online"`
	Timestamp      string  `json:"timestamp"`
	MonitorAgent   string  `json:"monitor_agent,omitempty"`
	LoggedIn       *bool   `json:"logged_in,omitempty"`
	LoggedInUser   string  `json:"logged_in_user,omitempty"` // Nom de l'utilisateur connecté
}

// SendTelemetry envoie les températures et le statut en ligne à l'API
func SendTelemetry(cfg *Config) {
	cpuTemp, cpuOk := getCPUTemperature()
	gpuTemp, gpuOk := getGPUTemperature()

	payload := TelemetryPayload{
		Hostname:     getHostname(),
		SerialNumber: getSerialNumber(),
		SiteID:       cfg.SiteID,
		DeviceType:   cfg.DeviceType,
		Online:       true,
		Timestamp:    time.Now().UTC().Format(time.RFC3339),
		MonitorAgent: "1.0",
	}
	if cpuOk {
		payload.CPUTemp = cpuTemp
	}
	if gpuOk {
		payload.GPUTemp = gpuTemp
	}

	// Utilisateur connecté (Windows: owner d'explorer.exe)
	if u := getLoggedUser(); u != "" && !strings.EqualFold(u, "SYSTEM") {
		ok := true
		payload.LoggedIn = &ok
		payload.LoggedInUser = u
	} else {
		ok := false
		payload.LoggedIn = &ok
	}

	jsonData, err := json.Marshal(payload)
	if err != nil {
		log.Printf("marshal: %v", err)
		return
	}

	apiURL := strings.TrimRight(cfg.APIURL, "/")
	if strings.Contains(apiURL, "inventory.php") {
		apiURL = strings.TrimSuffix(apiURL, "inventory.php") + "monitor_telemetry.php"
	} else if !strings.HasSuffix(apiURL, "monitor_telemetry.php") {
		apiURL = apiURL + "/monitor_telemetry.php"
	}

	req, err := http.NewRequest("POST", apiURL, bytes.NewBuffer(jsonData))
	if err != nil {
		log.Printf("request: %v", err)
		return
	}
	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("X-Api-Key", cfg.APIKey)
	req.Header.Set("User-Agent", "ITManager-Monitor/1.0")

	client := &http.Client{Timeout: 15 * time.Second}
	resp, err := client.Do(req)
	if err != nil {
		log.Printf("http: %v", err)
		return
	}
	defer resp.Body.Close()

	if resp.StatusCode >= 200 && resp.StatusCode < 300 {
		msg := "Télémétrie envoyée"
		if cpuOk || gpuOk {
			msg = fmt.Sprintf("Télémétrie envoyée")
			if cpuOk {
				msg += fmt.Sprintf(" CPU: %.1f°C", payload.CPUTemp)
			}
			if gpuOk {
				msg += fmt.Sprintf(" GPU: %.1f°C", payload.GPUTemp)
			}
		}
		log.Print(msg)
	} else {
		body, _ := io.ReadAll(resp.Body)
		log.Printf("API %d: %s", resp.StatusCode, string(body))
	}
}

// RunLoop exécute la boucle d'envoi périodique + inventaire (toutes les N heures)
func RunLoop(cfg *Config) {
	ticker := time.NewTicker(time.Duration(cfg.Interval) * time.Second)
	defer ticker.Stop()

	var lastInventory time.Time
	SendTelemetry(cfg)
	RunEsxiDiscovery(cfg)
	RunDiscovery(cfg)
	if shouldRunInventory(cfg, &lastInventory) {
		runInventoryAgent(cfg)
		lastInventory = time.Now()
	}
	cycle := 0
	for range ticker.C {
		cycle++
		SendTelemetry(cfg)
		if cycle%2 == 0 {
			RunEsxiDiscovery(cfg)
			RunDiscovery(cfg)
		}
		if shouldRunInventory(cfg, &lastInventory) {
			runInventoryAgent(cfg)
			lastInventory = time.Now()
		}
	}
}

