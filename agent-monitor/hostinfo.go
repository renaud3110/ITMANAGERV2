package main

import (
	"os"
	"strings"
)

func getHostname() string {
	hostname, err := os.Hostname()
	if err != nil {
		return ""
	}
	return strings.TrimSpace(hostname)
}

func getSerialNumber() string {
	return getSerialNumberOS()
}
