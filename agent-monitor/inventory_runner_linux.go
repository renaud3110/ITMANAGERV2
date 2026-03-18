//go:build linux

package main

import (
	"time"
)

func runInventoryAgent(cfg *Config) {
	// Inventaire Windows uniquement, ne fait rien sur Linux
}

func shouldRunInventory(cfg *Config, lastInventory *time.Time) bool {
	return false
}
