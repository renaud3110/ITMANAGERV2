//go:build !windows

package main

import (
	"os"
	"os/exec"
	"strconv"
	"strings"

	"github.com/shirou/gopsutil/v3/disk"
)

// getRAMDetails retourne (type, modèle, fréquence MHz) via dmidecode (Linux)
func getRAMDetails() (string, string, int) {
	cmd := exec.Command("dmidecode", "-t", "17")
	out, err := cmd.Output()
	if err != nil || len(out) == 0 {
		return "", "", 0
	}
	content := string(out)
	var ramType, ramModel string
	var speed int
	// Prendre le premier bloc Memory Device
	blocks := strings.Split(content, "Memory Device")
	for i, block := range blocks {
		if i == 0 {
			continue
		}
		lines := strings.Split(block, "\n")
		for _, line := range lines {
			line = strings.TrimSpace(line)
			if strings.HasPrefix(line, "Type:") {
				t := strings.TrimSpace(strings.TrimPrefix(line, "Type:"))
				if t != "Unknown" && t != "" && ramType == "" {
					ramType = t
				}
			}
			if strings.HasPrefix(line, "Speed:") {
				sp := strings.TrimSpace(strings.TrimPrefix(line, "Speed:"))
				if sp != "" && speed == 0 {
					if n, err := strconv.Atoi(strings.TrimSpace(strings.Fields(sp)[0])); err == nil {
						speed = n
					}
				}
			}
			if strings.HasPrefix(line, "Part Number:") {
				pn := strings.TrimSpace(strings.TrimPrefix(line, "Part Number:"))
				if pn != "" && pn != "Unknown" && pn != "Not Specified" && pn != "NO DIMM" && ramModel == "" {
					ramModel = pn
				}
			}
			if strings.HasPrefix(line, "Manufacturer:") && ramModel == "" {
				m := strings.TrimSpace(strings.TrimPrefix(line, "Manufacturer:"))
				if m != "" && m != "Unknown" && m != "NO DIMM" {
					ramModel = m
				}
			}
		}
		if ramType != "" || ramModel != "" || speed > 0 {
			break
		}
	}
	return ramType, ramModel, speed
}

// getMotherboardBiosInfo retourne (S/N carte mère, version BIOS) via sysfs DMI (Linux)
func getMotherboardBiosInfo() (string, string) {
	readDmi := func(name string) string {
		b, err := os.ReadFile("/sys/class/dmi/id/" + name)
		if err != nil {
			return ""
		}
		s := strings.TrimSpace(string(b))
		if s == "Default string" || s == "To Be Filled By O.E.M." || s == "None" || s == "" {
			return ""
		}
		return s
	}
	mbSerial := readDmi("board_serial")
	biosVer := readDmi("bios_version")
	return mbSerial, biosVer
}

// getVmUuid retourne l'UUID BIOS/SMBIOS (pour lien auto avec VM ESXi). Format standard.
func getVmUuid() string {
	b, err := os.ReadFile("/sys/class/dmi/id/product_uuid")
	if err != nil {
		return ""
	}
	return strings.TrimSpace(string(b))
}

// getRustDeskInfo récupère l'ID RustDesk (Linux/macOS)
func getRustDeskInfo() (id string, _ string) {
	cmd := exec.Command("rustdesk", "--get-id")
	out, err := cmd.Output()
	if err != nil {
		return "", ""
	}
	id = strings.TrimSpace(string(out))
	return id, ""
}

func getSystemSerialOS() string {
	return ""
}

func getManufacturerModel() (string, string) {
	return "", ""
}

func getNetworkInfo() *NetworkInfo {
	return nil
}

// collectNetworkAdaptersOS (Linux) via ip et iwgetid
func collectNetworkAdaptersOS() []NetworkAdapterInfo {
	// ip -4 -o addr show : "2: eth0    inet 192.168.1.10/24 ..."
	cmd := exec.Command("ip", "-4", "-o", "addr", "show")
	out, err := cmd.Output()
	if err != nil {
		return nil
	}
	// default gateway (one for the host)
	defaultGW := ""
	if gwOut, err := exec.Command("ip", "route", "show", "default").Output(); err == nil {
		fields := strings.Fields(string(gwOut))
		for i, f := range fields {
			if f == "via" && i+1 < len(fields) {
				defaultGW = strings.TrimSpace(fields[i+1])
				break
			}
		}
	}

	var result []NetworkAdapterInfo
	lines := strings.Split(string(out), "\n")
	for _, line := range lines {
		line = strings.TrimSpace(line)
		if line == "" {
			continue
		}
		fields := strings.Fields(line)
		if len(fields) < 4 {
			continue
		}
		// format: idx: name inet addr/prefix ...
		name := strings.TrimRight(fields[1], ":")
		inet := fields[3] // 192.168.1.10/24
		if !strings.Contains(inet, "/") {
			continue
		}
		adapterType := "Ethernet"
		if _, err := os.Stat("/sys/class/net/" + name + "/wireless"); err == nil {
			adapterType = "Wi-Fi"
		}
		ssid := ""
		if adapterType == "Wi-Fi" {
			if ssidOut, err := exec.Command("iwgetid", "-r", name).Output(); err == nil {
				ssid = strings.TrimSpace(string(ssidOut))
			}
			if ssid == "" {
				if ssidOut, err := exec.Command("iwgetid", "-r").Output(); err == nil {
					ssid = strings.TrimSpace(string(ssidOut))
				}
			}
		}
		result = append(result, NetworkAdapterInfo{
			Name:     name,
			Type:     adapterType,
			IPCidr:   inet,
			Gateway:  defaultGW,
			WifiSSID: ssid,
		})
	}
	return result
}

func getLoggedUser() string {
	return ""
}

func getLoggedUserCreatedAt() string {
	return ""
}

func getWindowsOSInfo() (string, string) {
	return "", ""
}

func collectInstalledSoftware() []SoftwareInfo {
	return nil
}

func getAntivirusInfo() (name string, enabled *bool, updated *bool) {
	return "", nil, nil
}

func getFirewallInfo() *bool {
	return nil
}

// collectGPUsOS (Linux) via lspci -v pour VGA/3D
func collectGPUsOS() []GPUInfo {
	cmd := exec.Command("lspci", "-v", "-mm")
	out, err := cmd.Output()
	if err != nil {
		return nil
	}
	var gpus []GPUInfo
	blocks := strings.Split(string(out), "\n\n")
	for _, block := range blocks {
		if !strings.Contains(block, "VGA") && !strings.Contains(block, "3D") {
			continue
		}
		var name, vendor, driver string
		lines := strings.Split(block, "\n")
		for _, line := range lines {
			line = strings.TrimSpace(line)
			if strings.HasPrefix(line, "Device:") {
				name = strings.TrimSpace(strings.TrimPrefix(line, "Device:"))
			}
			if strings.HasPrefix(line, "Vendor:") {
				vendor = strings.TrimSpace(strings.TrimPrefix(line, "Vendor:"))
			}
			if strings.HasPrefix(line, "Driver:") {
				driver = strings.TrimSpace(strings.TrimPrefix(line, "Driver:"))
			}
		}
		if name == "" {
			continue
		}
		if vendor != "" && name != "" {
			name = vendor + " " + name
		}
		gpus = append(gpus, GPUInfo{
			Model:         name,
			Vendor:        vendor,
			DriverVersion: driver,
		})
	}
	return gpus
}

// collectMonitorsOS (Linux) via xrandr ou /sys/class/drm
func collectMonitorsOS() []MonitorInfo {
	cmd := exec.Command("xrandr", "--query")
	out, err := cmd.Output()
	if err != nil {
		return listDrmMonitors()
	}
	var monitors []MonitorInfo
	lines := strings.Split(string(out), "\n")
	var currentName string
	for _, line := range lines {
		line = strings.TrimSpace(line)
		if strings.HasSuffix(line, " connected") {
			parts := strings.Fields(line)
			if len(parts) >= 1 {
				currentName = parts[0]
				res := ""
				for i, p := range parts {
					if p == "primary" && i+1 < len(parts) {
						res = parts[i+1]
						break
					}
					if strings.Contains(p, "x") && strings.Contains(p, "+") {
						res = p
						break
					}
				}
				monitors = append(monitors, MonitorInfo{Name: currentName, Resolution: res})
			}
		}
	}
	if len(monitors) > 0 {
		return monitors
	}
	return listDrmMonitors()
}

func listDrmMonitors() []MonitorInfo {
	ents, err := os.ReadDir("/sys/class/drm")
	if err != nil {
		return nil
	}
	var list []MonitorInfo
	for _, e := range ents {
		name := e.Name()
		if strings.HasPrefix(name, "card") && !strings.Contains(name, "-") {
			status, _ := os.ReadFile("/sys/class/drm/" + name + "/status")
			if strings.TrimSpace(string(status)) == "connected" {
				list = append(list, MonitorInfo{Name: name})
			}
		}
	}
	return list
}

// collectPrintersOS (Linux) via lpstat -p
func collectWindowsUpdatesOS() []WindowsUpdateInfo {
	return nil
}

func collectWindowsServicesOS() []WindowsServiceInfo     { return nil }
func collectWindowsStartupOS() []WindowsStartupInfo     { return nil }
func collectWindowsSharedOS() []WindowsSharedInfo       { return nil }
func collectWindowsMappedOS() []WindowsMappedDriveInfo  { return nil }
func collectWindowsUsersOS() []WindowsUserInfo          { return nil }
func collectWindowsUserGroupsOS() []string              { return nil }
func collectWindowsLicenseOS() *WindowsLicenseInfo      { return nil }

// fstypes réseau à exclure (disques locaux uniquement)
var networkFstypes = map[string]bool{
	"nfs": true, "nfs4": true, "nfs3": true,
	"cifs": true, "smb": true, "smb2": true, "smb3": true,
	"fuse.sshfs": true, "fuse.curlftpfs": true, "fuse.httpfs": true,
	"fuse.davfs": true, "fuse.gvfsd-fuse": true,
}

func collectDisksOS() []DiskInfo {
	partitions, err := disk.Partitions(true)
	if err != nil {
		return nil
	}
	var localPartitions []disk.PartitionStat
	for _, p := range partitions {
		fs := strings.ToLower(strings.TrimSpace(p.Fstype))
		if networkFstypes[fs] || strings.HasPrefix(fs, "nfs") || strings.HasPrefix(fs, "cifs") ||
			(strings.HasPrefix(fs, "fuse.") && (strings.Contains(fs, "ssh") || strings.Contains(fs, "curl") || strings.Contains(fs, "dav"))) {
			continue
		}
		localPartitions = append(localPartitions, p)
	}
	return collectDisksGeneric(localPartitions)
}

func collectPrintersOS() []PrinterInfo {
	cmd := exec.Command("lpstat", "-p")
	out, err := cmd.Output()
	if err != nil {
		return nil
	}
	var printers []PrinterInfo
	lines := strings.Split(string(out), "\n")
	for _, line := range lines {
		line = strings.TrimSpace(line)
		if !strings.HasPrefix(line, "printer ") {
			continue
		}
		// "printer NAME is idle.  enabled since ..."
		parts := strings.Fields(line)
		if len(parts) >= 2 {
			printers = append(printers, PrinterInfo{Name: parts[1]})
		}
	}
	return printers
}
