//go:build windows

package main

import (
	"log"
	"os"
	"os/exec"
	"path/filepath"
	"time"
)

// runInventoryAgent lance itmanager-agent.exe (même dossier) pour l'inventaire.
// Utilisé quand inventory_interval_hours > 0 et device_type est défini.
func runInventoryAgent(cfg *Config) {
	if cfg.InventoryIntervalHrs <= 0 {
		return
	}
	exe, err := os.Executable()
	if err != nil {
		return
	}
	dir := filepath.Dir(exe)
	agentPath := filepath.Join(dir, "itmanager-agent.exe")
	if _, err := os.Stat(agentPath); os.IsNotExist(err) {
		return
	}
	cmd := exec.Command(agentPath)
	cmd.Dir = dir
	cmd.Stdout = nil
	cmd.Stderr = nil
	if err := cmd.Start(); err != nil {
		log.Printf("inventory: impossible de lancer agent: %v", err)
		return
	}
	go func() {
		cmd.Wait()
		log.Printf("inventory: terminé")
	}()
}

// shouldRunInventory retourne true si l'intervalle inventaire est écoulé
func shouldRunInventory(cfg *Config, lastInventory *time.Time) bool {
	if cfg.InventoryIntervalHrs <= 0 {
		return false
	}
	if lastInventory.IsZero() {
		return true
	}
	return time.Since(*lastInventory) >= time.Duration(cfg.InventoryIntervalHrs)*time.Hour
}
