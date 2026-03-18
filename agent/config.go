package main

import (
	"encoding/json"
	"fmt"
	"os"
	"path/filepath"
	"strconv"
)

// AgentConfig configuration du client
type AgentConfig struct {
	APIURL     string `json:"api_url"`
	APIKey     string `json:"api_key"`
	SiteID     int    `json:"site_id"` // Le tenant est dérivé automatiquement du site
	TenantID   int    `json:"tenant_id,omitempty"`
	DeviceType string `json:"device_type,omitempty"` // "pc" (défaut) ou "server"
}

func loadConfig() (*AgentConfig, error) {
	// 1. Fichier à côté de l'exécutable
	exePath, err := os.Executable()
	if err == nil {
		dir := filepath.Dir(exePath)
		if cfg := readConfigFile(filepath.Join(dir, "agent.json")); cfg != nil {
			return cfg, nil
		}
		if cfg := readConfigFile(filepath.Join(dir, "agent.conf.json")); cfg != nil {
			return cfg, nil
		}
	}

	// 2. Répertoire courant
	if cfg := readConfigFile("agent.json"); cfg != nil {
		return cfg, nil
	}
	if cfg := readConfigFile("agent.conf.json"); cfg != nil {
		return cfg, nil
	}

	// 3. Variables d'environnement
	cfg := &AgentConfig{
		APIURL:     os.Getenv("ITMANAGER_API_URL"),
		APIKey:     os.Getenv("ITMANAGER_API_KEY"),
		SiteID:     parseInt(os.Getenv("ITMANAGER_SITE_ID")),
		TenantID:   parseInt(os.Getenv("ITMANAGER_TENANT_ID")),
		DeviceType: os.Getenv("ITMANAGER_DEVICE_TYPE"),
	}
	if cfg.APIKey != "" {
		if cfg.APIURL == "" {
			cfg.APIURL = "https://it.rgdsystems.be/api/inventory.php"
		}
		return cfg, nil
	}

	return nil, fmt.Errorf("configuration introuvable: créez agent.json ou définissez ITMANAGER_API_KEY")
}

func readConfigFile(path string) *AgentConfig {
	data, err := os.ReadFile(path)
	if err != nil {
		return nil
	}
	var cfg AgentConfig
	if err := json.Unmarshal(data, &cfg); err != nil {
		return nil
	}
	if cfg.APIKey == "" {
		return nil
	}
	if cfg.APIURL == "" {
		cfg.APIURL = "https://it.rgdsystems.be/api/inventory.php"
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
