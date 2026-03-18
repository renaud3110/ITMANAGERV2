package main

import (
	"encoding/json"
	"fmt"
	"os"
	"path/filepath"
	"strconv"
)

// Config configuration de l'agent moniteur (et inventaire si unifié)
type Config struct {
	APIURL               string `json:"api_url"`
	APIKey               string `json:"api_key"`
	SiteID               int    `json:"site_id"`
	Interval             int    `json:"interval_seconds,omitempty"`       // Télémétrie : secondes (défaut: 60)
	DeviceType           string `json:"device_type,omitempty"`             // "pc" ou "server" pour inventaire
	InventoryIntervalHrs int    `json:"inventory_interval_hours,omitempty"` // Inventaire : heures (0=désactivé, 2=défaut)
}

func loadConfig() (*Config, error) {
	paths := []string{"agent.json", "agent.conf.json"}
	if exePath, err := os.Executable(); err == nil {
		dir := filepath.Dir(exePath)
		paths = append([]string{filepath.Join(dir, "agent.json"), filepath.Join(dir, "agent.conf.json")}, paths...)
	}
	for _, p := range paths {
		if cfg := readConfigFile(p); cfg != nil {
			return cfg, nil
		}
	}
	return nil, fmt.Errorf("configuration introuvable: créez agent.json (même format que l'agent inventaire)")
}

func readConfigFile(path string) *Config {
	data, err := os.ReadFile(path)
	if err != nil {
		return nil
	}
	var cfg Config
	if err := json.Unmarshal(data, &cfg); err != nil {
		return nil
	}
	if cfg.APIKey == "" {
		return nil
	}
	if cfg.APIURL == "" {
		cfg.APIURL = "https://it.rgdsystems.be/api/inventory.php"
	}
	if cfg.Interval <= 0 {
		cfg.Interval = 60
	}
	if cfg.InventoryIntervalHrs < 0 {
		cfg.InventoryIntervalHrs = 0
	} else if cfg.InventoryIntervalHrs == 0 {
		cfg.InventoryIntervalHrs = 2
	}
	if cfg.DeviceType == "" {
		cfg.DeviceType = "pc"
	}
	return &cfg
}

func parseInt(s string) int {
	if s == "" {
		return 0
	}
	n, _ := strconv.Atoi(s)
	return n
}
