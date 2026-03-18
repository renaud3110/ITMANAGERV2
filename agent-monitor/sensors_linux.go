//go:build !windows

package main

import (
	"os"
	"os/exec"
	"strconv"
	"strings"

	"github.com/shirou/gopsutil/v3/host"
)

// getCPUTemperature utilise gopsutil sensors (Linux)
func getCPUTemperature() (float64, bool) {
	temps, err := host.SensorsTemperatures()
	if err != nil {
		return 0, false
	}
	// Chercher un sensor CPU (coretemp, k10temp, etc.)
	for _, t := range temps {
		key := strings.ToLower(t.SensorKey)
		if strings.Contains(key, "core") || strings.Contains(key, "cpu") ||
			strings.Contains(key, "package") || strings.Contains(key, "temp") {
			if t.Temperature > 0 && t.Temperature < 150 {
				return t.Temperature, true
			}
		}
	}
	// Fallback: premier sensor valide
	for _, t := range temps {
		if t.Temperature > 0 && t.Temperature < 150 {
			return t.Temperature, true
		}
	}
	return 0, false
}

// getGPUTemperature via nvidia-smi sur Linux
func getGPUTemperature() (float64, bool) {
	cmd := exec.Command("nvidia-smi", "--query-gpu=temperature.gpu", "--format=csv,noheader,nounits")
	cmd.Env = append(os.Environ(), "LC_ALL=C")
	out, err := cmd.Output()
	if err != nil {
		return 0, false
	}
	lines := strings.Split(strings.TrimSpace(string(out)), "\n")
	if len(lines) == 0 {
		return 0, false
	}
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
