//go:build !windows

package main

import (
	"os"
	"strings"
)

func getLoggedUser() string {
	return "" // non implémenté sous Linux pour l'instant
}

func getSerialNumberOS() string {
	data, err := os.ReadFile("/sys/class/dmi/id/product_serial")
	if err != nil {
		return ""
	}
	return strings.TrimSpace(string(data))
}
