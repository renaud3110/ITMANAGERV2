//go:build windows

package main

import (
	"os/exec"
	"strconv"
	"strings"
)

// getCPUTemperature tente de récupérer la température CPU via WMI
func getCPUTemperature() (float64, bool) {
	// MSAcpi_ThermalZoneTemperature - température en decikelvin
	psScript := `(Get-CimInstance MSAcpi_ThermalZoneTemperature -Namespace root/wmi -ErrorAction SilentlyContinue | Select-Object -First 1).CurrentTemperature`
	cmd := exec.Command("powershell", "-NoProfile", "-Command", psScript)
	out, err := cmd.Output()
	if err != nil {
		return 0, false
	}
	s := strings.TrimSpace(string(out))
	if s == "" || s == "null" {
		return 0, false
	}
	decikelvin, err := strconv.ParseInt(s, 10, 64)
	if err != nil {
		return 0, false
	}
	// Decikelvin -> Celsius: (value/10) - 273.15
	celsius := float64(decikelvin)/10.0 - 273.15
	if celsius < -40 || celsius > 150 {
		return 0, false
	}
	return celsius, true
}

// getGPUTemperature récupère la température GPU NVIDIA via nvidia-smi
func getGPUTemperature() (float64, bool) {
	paths := []string{
		`nvidia-smi`,
		`C:\Program Files\NVIDIA Corporation\NVSMI\nvidia-smi.exe`,
		`C:\Windows\System32\nvidia-smi.exe`,
	}
	var exe string
	for _, p := range paths {
		cmd := exec.Command("cmd", "/c", "where", p)
		if out, err := cmd.Output(); err == nil && len(out) > 0 {
			exe = strings.TrimSpace(strings.Split(string(out), "\n")[0])
			if exe != "" && !strings.HasPrefix(strings.ToLower(exe), "info:") {
				break
			}
		}
	}
	if exe == "" {
		exe = "nvidia-smi"
	}
	cmd := exec.Command(exe, "--query-gpu=temperature.gpu", "--format=csv,noheader,nounits")
	out, err := cmd.Output()
	if err != nil {
		return 0, false
	}
	lines := strings.Split(strings.TrimSpace(string(out)), "\n")
	if len(lines) == 0 {
		return 0, false
	}
	// Première GPU
	s := strings.TrimSpace(strings.Split(lines[0], ",")[0])
	temp, err := strconv.ParseFloat(s, 64)
	if err != nil {
		return 0, false
	}
	if temp < 0 || temp > 150 {
		return 0, false
	}
	return temp, true
}
