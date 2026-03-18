package main

import (
	"bytes"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"os"
	"runtime"
	"strconv"
	"strings"
	"time"

	"github.com/shirou/gopsutil/v3/cpu"
	"github.com/shirou/gopsutil/v3/disk"
	"github.com/shirou/gopsutil/v3/host"
	"github.com/shirou/gopsutil/v3/mem"
)

const agentVersion = "1.1"

// InventoryPayload structure envoyée à l'API IT Manager
type InventoryPayload struct {
	DeviceType      string         `json:"device_type,omitempty"` // "pc" ou "server"
	Hostname        string         `json:"hostname"`
	SerialNumber    string         `json:"serial_number,omitempty"`
	Manufacturer    string         `json:"manufacturer,omitempty"`
	Model           string         `json:"model,omitempty"`
	Processor       string         `json:"processor,omitempty"`
	ProcessorCores  int            `json:"processor_cores,omitempty"`
	ProcessorMhz    float64        `json:"processor_mhz,omitempty"`
	ProcessorVendor string         `json:"processor_manufacturer,omitempty"` // envoyé comme processor_manufacturer
	ProcessorFamily string         `json:"processor_family,omitempty"`
	OSName          string         `json:"os_name,omitempty"`
	OSVersion       string         `json:"os_version,omitempty"`
	RAMTotalBytes   uint64         `json:"ram_total_bytes,omitempty"`
	RAMUsedBytes    uint64         `json:"ram_used_bytes,omitempty"`
	RAMType         string         `json:"ram_type,omitempty"`
	RAMModel        string         `json:"ram_model,omitempty"`
	RAMFrequencyMhz int            `json:"ram_frequency_mhz,omitempty"`
	SiteID          int            `json:"site_id,omitempty"`
	TenantID        int            `json:"tenant_id,omitempty"`
	LastAccount           string         `json:"last_account,omitempty"`
	LastAccountCreatedAt  string         `json:"last_account_created_at,omitempty"`
	Network         *NetworkInfo   `json:"network,omitempty"`
	NetworkAdapters []NetworkAdapterInfo `json:"network_adapters,omitempty"`
	Disks           []DiskInfo     `json:"disks,omitempty"`
	GPUs             []GPUInfo      `json:"gpus,omitempty"`
	Monitors         []MonitorInfo  `json:"monitors,omitempty"`
	Printers         []PrinterInfo  `json:"printers,omitempty"`
	Software         []SoftwareInfo `json:"software,omitempty"`
	RustDeskID       string         `json:"rustdesk_id,omitempty"`
	WindowsUpdates    []WindowsUpdateInfo    `json:"windows_updates,omitempty"`
	WindowsServices   []WindowsServiceInfo  `json:"windows_services,omitempty"`
	WindowsStartup    []WindowsStartupInfo  `json:"windows_startup,omitempty"`
	WindowsShared     []WindowsSharedInfo   `json:"windows_shared,omitempty"`
	WindowsMapped     []WindowsMappedDriveInfo `json:"windows_mapped,omitempty"`
	WindowsUsers      []WindowsUserInfo     `json:"windows_users,omitempty"`
	WindowsUserGroups []string              `json:"windows_user_groups,omitempty"`
	WindowsLicense    *WindowsLicenseInfo   `json:"windows_license,omitempty"`
	AntivirusName       string         `json:"antivirus_name,omitempty"`
	AntivirusEnabled    *bool          `json:"antivirus_enabled,omitempty"`
	AntivirusUpdated    *bool          `json:"antivirus_updated,omitempty"`
	FirewallEnabled     *bool          `json:"firewall_enabled,omitempty"`
	MotherboardSerial   string         `json:"motherboard_serial,omitempty"`
	BiosVersion         string         `json:"bios_version,omitempty"`
	VmUuid              string         `json:"vm_uuid,omitempty"` // UUID BIOS/SMBIOS (pour lien auto avec VM ESXi)
	AgentVersion        string         `json:"agent_version,omitempty"`
	InventoryDate       string         `json:"inventory_date,omitempty"`
}

type DiskInfo struct {
	Model        string          `json:"model,omitempty"`
	SerialNumber string          `json:"serial_number,omitempty"`
	Interface    string          `json:"interface_type,omitempty"`
	SizeBytes    uint64          `json:"size_bytes,omitempty"`
	Partitions   []PartitionInfo `json:"partitions,omitempty"`
}

type PartitionInfo struct {
	DriveLetter    string `json:"drive_letter,omitempty"`
	Label          string `json:"label,omitempty"`
	FileSystem     string `json:"file_system,omitempty"`
	TotalSizeBytes uint64 `json:"total_size_bytes,omitempty"`
	FreeSpaceBytes uint64 `json:"free_space_bytes,omitempty"`
}

type SoftwareInfo struct {
	Name        string `json:"name"`
	Version     string `json:"version,omitempty"`
	InstallDate string `json:"install_date,omitempty"`
}

// GPUInfo carte graphique
type GPUInfo struct {
	Model        string `json:"model,omitempty"`
	Vendor       string `json:"vendor,omitempty"`
	DriverVersion string `json:"driver_version,omitempty"`
	VRAMBytes   uint64 `json:"vram_bytes,omitempty"`
	VideoProcessor string `json:"video_processor,omitempty"`
}

// MonitorInfo écran connecté
type MonitorInfo struct {
	Name        string `json:"name,omitempty"`
	Manufacturer string `json:"manufacturer,omitempty"`
	SerialNumber string `json:"serial_number,omitempty"`
	Resolution  string `json:"resolution,omitempty"`
}

// PrinterInfo imprimante disponible
type PrinterInfo struct {
	Name    string `json:"name"`
	Driver  string `json:"driver,omitempty"`
	Port    string `json:"port,omitempty"`
	Default bool   `json:"default,omitempty"`
	Shared  bool   `json:"shared,omitempty"`
}

// WindowsUpdateInfo mise à jour Windows (KB) installée
type WindowsUpdateInfo struct {
	HotFixID   string `json:"hotfix_id,omitempty"`
	Description string `json:"description,omitempty"`
	InstalledOn string `json:"installed_on,omitempty"`
}

// WindowsServiceInfo service Windows
type WindowsServiceInfo struct {
	Name        string `json:"name,omitempty"`
	DisplayName string `json:"display_name,omitempty"`
	Description string `json:"description,omitempty"`
	Status      string `json:"status,omitempty"`
	StartType   string `json:"start_type,omitempty"`
}

// WindowsStartupInfo programme de démarrage
type WindowsStartupInfo struct {
	Name     string `json:"name,omitempty"`
	Command  string `json:"command,omitempty"`
	Location string `json:"location,omitempty"`
}

// WindowsSharedInfo partage réseau (ce qui est partagé sur ce PC)
type WindowsSharedInfo struct {
	Name        string `json:"name,omitempty"`
	Path        string `json:"path,omitempty"`
	Description string `json:"description,omitempty"`
}

// WindowsMappedDriveInfo lecteur mappé sur ce PC
type WindowsMappedDriveInfo struct {
	DriveLetter string `json:"drive_letter,omitempty"`
	Path        string `json:"path,omitempty"`
	Label       string `json:"label,omitempty"`
}

// WindowsUserInfo utilisateur (local, AD, cloud) avec dernière connexion
type WindowsUserInfo struct {
	Username   string `json:"username,omitempty"`
	FullName   string `json:"full_name,omitempty"`
	LastLogin  string `json:"last_login,omitempty"`
	AccountType string `json:"account_type,omitempty"`
}

// WindowsLicenseInfo licence Windows
type WindowsLicenseInfo struct {
	Description string `json:"description,omitempty"`
	Status      string `json:"status,omitempty"`
}

type NetworkInfo struct {
	IPAddress   string `json:"ip_address,omitempty"`
	Gateway     string `json:"gateway,omitempty"`
	SubnetMask  string `json:"subnet_mask,omitempty"`
	DNSServers  string `json:"dns_servers,omitempty"`
}

// NetworkAdapterInfo une carte réseau (Ethernet, Wi-Fi) avec IP en CIDR et infos
type NetworkAdapterInfo struct {
	Name     string `json:"name,omitempty"`
	Type     string `json:"type,omitempty"`      // "Ethernet", "Wi-Fi", etc.
	IPCidr   string `json:"ip_cidr,omitempty"`   // ex: 192.168.1.10/24
	Gateway  string `json:"gateway,omitempty"`
	WifiSSID string `json:"wifi_ssid,omitempty"` // si Wi-Fi, nom du réseau
}

func main() {
	if len(os.Args) > 1 && (os.Args[1] == "-test-users" || os.Args[1] == "--test-users") {
		fmt.Println("=== Test collecte Users (Windows) ===")
		list := collectWindowsUsers()
		if list == nil {
			list = []WindowsUserInfo{}
		}
		fmt.Printf("Utilisateurs collectes: %d\n", len(list))
		jsonData, _ := json.MarshalIndent(list, "", "  ")
		fmt.Println(string(jsonData))
		os.Exit(0)
	}
	if len(os.Args) > 1 && (os.Args[1] == "-test-shared" || os.Args[1] == "--test-shared") {
		fmt.Println("=== Test collecte Shared (Windows) ===")
		list := collectWindowsShared()
		if list == nil {
			list = []WindowsSharedInfo{}
		}
		fmt.Printf("Partages collectes: %d\n", len(list))
		jsonData, _ := json.MarshalIndent(list, "", "  ")
		fmt.Println(string(jsonData))
		os.Exit(0)
	}
	if len(os.Args) > 1 && (os.Args[1] == "-test-startup" || os.Args[1] == "--test-startup") {
		fmt.Println("=== Test collecte Startup (Windows) ===")
		list := collectWindowsStartup()
		if list == nil {
			list = []WindowsStartupInfo{}
		}
		fmt.Printf("Entrees collectees: %d\n", len(list))
		jsonData, _ := json.MarshalIndent(list, "", "  ")
		fmt.Println(string(jsonData))
		os.Exit(0)
	}

	cfg, err := loadConfig()
	if err != nil {
		fmt.Printf("Erreur: %v\n", err)
		fmt.Println("Créez agent.json (copiez agent.json.example) avec api_key, site_id, tenant_id")
		os.Exit(1)
	}

	fmt.Println("IT Manager Agent - Inventaire")
	fmt.Println("=============================")
	fmt.Printf("Version: %s\n", agentVersion)
	fmt.Printf("Système: %s\n", runtime.GOOS)
	if cfg.SiteID > 0 {
		fmt.Printf("Site ID: %d\n", cfg.SiteID)
	}
	if cfg.TenantID > 0 {
		fmt.Printf("Tenant ID: %d\n", cfg.TenantID)
	}

	payload := collectInventory(cfg)
	payload.InventoryDate = time.Now().UTC().Format(time.RFC3339)
	payload.AgentVersion = agentVersion
	payload.SiteID = cfg.SiteID
	payload.TenantID = cfg.TenantID

	jsonData, err := json.Marshal(payload)
	if err != nil {
		fmt.Printf("Erreur sérialisation: %v\n", err)
		os.Exit(1)
	}

	fmt.Printf("Collecté: %d disques, %d logiciels, %d services Windows, %d startup\n",
		len(payload.Disks), len(payload.Software),
		len(payload.WindowsServices), len(payload.WindowsStartup))
	fmt.Printf("Envoi vers %s...\n", cfg.APIURL)

	resp, err := sendInventory(cfg.APIURL, cfg.APIKey, jsonData)
	if err != nil {
		fmt.Printf("Erreur envoi: %v\n", err)
		os.Exit(1)
	}

	body, _ := io.ReadAll(resp.Body)
	resp.Body.Close()

	if resp.StatusCode >= 200 && resp.StatusCode < 300 {
		var result map[string]interface{}
		json.Unmarshal(body, &result)
		idKey := "server_id"
		if _, ok := result["pc_id"]; ok {
			idKey = "pc_id"
		}
		if id, ok := result[idKey].(float64); ok {
			fmt.Printf("Succès: %s (ID: %.0f)\n", result["action"], id)
		} else {
			fmt.Printf("Succès: %s\n", result["action"])
		}
	} else {
		fmt.Printf("Erreur %d: %s\n", resp.StatusCode, string(body))
		os.Exit(1)
	}
}

func collectInventory(cfg *AgentConfig) InventoryPayload {
	payload := InventoryPayload{
		DeviceType: cfg.DeviceType,
	}

	// Hostname
	if hostname, err := host.Info(); err == nil {
		payload.Hostname = hostname.Hostname
		payload.OSName = hostname.OS
		payload.OSVersion = hostname.PlatformVersion
		if hostname.Platform == "windows" {
			payload.OSName, payload.OSVersion = getWindowsOSInfo()
			if payload.OSName == "" || payload.OSVersion == "" || strings.HasPrefix(payload.OSVersion, "10.0.") {
				// Fallback: parser PlatformVersion (ex: 10.0.26200.7840) si PowerShell échoue
				payload.OSName, payload.OSVersion = parseWindowsVersionFallback(hostname.PlatformVersion)
				if payload.OSName == "" {
					payload.OSName = "Windows"
				}
			}
		}
	}

	// Serial number (Bios) - platform specific
	payload.SerialNumber = getSystemSerial()

	// Carte mère (S/N) et version BIOS
	payload.MotherboardSerial, payload.BiosVersion = getMotherboardBiosInfo()

	// UUID VM (BIOS/SMBIOS) pour lien automatique avec VM ESXi inventoriée
	payload.VmUuid = getVmUuid()

	// Marque et modèle
	payload.Manufacturer, payload.Model = getManufacturerModel()

	// CPU
	if infos, err := cpu.Info(); err == nil && len(infos) > 0 {
		info := infos[0]
		payload.Processor = info.ModelName
		payload.ProcessorCores = int(info.Cores)
		if info.Mhz > 0 {
			payload.ProcessorMhz = info.Mhz
		}
		if info.VendorID != "" {
			payload.ProcessorVendor = mapCpuVendor(info.VendorID)
		}
		payload.ProcessorFamily = extractCpuFamily(info.ModelName)
	}

	// Mémoire
	if memInfo, err := mem.VirtualMemory(); err == nil {
		payload.RAMTotalBytes = memInfo.Total
		payload.RAMUsedBytes = memInfo.Used
	}
	// Détails RAM (type, modèle, fréquence)
	payload.RAMType, payload.RAMModel, payload.RAMFrequencyMhz = getRAMDetails()

	// Utilisateur connecté / dernier utilisateur (Windows)
	if runtime.GOOS == "windows" {
		payload.LastAccount = getLoggedUser()
		if payload.LastAccount != "" {
			payload.LastAccountCreatedAt = getLoggedUserCreatedAt()
		}
	}

	// Configuration réseau (Windows uniquement pour Network principal)
	if runtime.GOOS == "windows" {
		payload.Network = getNetworkInfo()
	}
	// Liste des cartes réseau (Ethernet, Wi-Fi) avec IP/prefix, gateway, SSID
	payload.NetworkAdapters = collectNetworkAdapters()

	// Disques et partitions (locaux uniquement, pas les lecteurs réseau)
	payload.Disks = collectDisks()

	// Cartes graphiques, moniteurs, imprimantes
	payload.GPUs = collectGPUs()
	payload.Monitors = collectMonitors()
	payload.Printers = collectPrinters()

	// Logiciels installés (Windows)
	payload.Software = collectInstalledSoftware()

	// RustDesk (ID uniquement)
	payload.RustDeskID, _ = getRustDeskInfo()

	// Antivirus et Firewall (Windows uniquement)
	if runtime.GOOS == "windows" {
		payload.AntivirusName, payload.AntivirusEnabled, payload.AntivirusUpdated = getAntivirusInfo()
		payload.FirewallEnabled = getFirewallInfo()
		payload.WindowsUpdates = collectWindowsUpdates()
		payload.WindowsServices = collectWindowsServices()
		payload.WindowsStartup = collectWindowsStartup()
		payload.WindowsShared = collectWindowsShared()
		payload.WindowsMapped = collectWindowsMapped()
		payload.WindowsUsers = collectWindowsUsers()
		payload.WindowsUserGroups = collectWindowsUserGroups()
		payload.WindowsLicense = collectWindowsLicense()
	}

	return payload
}

func collectDisks() []DiskInfo {
	return collectDisksOS()
}

func collectDisksGeneric(partitions []disk.PartitionStat) []DiskInfo {
	var disks []DiskInfo

	// Grouper par device (Linux: /dev/sda, Windows: chaque lecteur)
	deviceMap := make(map[string]*DiskInfo)
	for _, p := range partitions {
		baseDevice := p.Device
		// Linux: /dev/sda1 -> /dev/sda
		if len(baseDevice) > 1 && baseDevice[len(baseDevice)-1] >= '0' && baseDevice[len(baseDevice)-1] <= '9' {
			for i := len(baseDevice) - 1; i >= 0; i-- {
				if baseDevice[i] < '0' || baseDevice[i] > '9' {
					baseDevice = baseDevice[:i+1]
					break
				}
			}
		}

		if _, ok := deviceMap[baseDevice]; !ok {
			deviceMap[baseDevice] = &DiskInfo{
				Model:      p.Fstype,
				Partitions: []PartitionInfo{},
			}
		}

		usage, _ := disk.Usage(p.Mountpoint)
		part := PartitionInfo{
			DriveLetter:    getDriveLetter(p.Device, p.Mountpoint),
			FileSystem:     p.Fstype,
			TotalSizeBytes: 0,
			FreeSpaceBytes: 0,
		}
		if usage != nil {
			part.TotalSizeBytes = usage.Total
			part.FreeSpaceBytes = usage.Free
			part.Label = usage.Fstype
			deviceMap[baseDevice].SizeBytes += usage.Total
		}
		deviceMap[baseDevice].Partitions = append(deviceMap[baseDevice].Partitions, part)
	}

	for _, d := range deviceMap {
		disks = append(disks, *d)
	}
	return disks
}

// mapCpuVendor convertit VendorID en nom lisible
func mapCpuVendor(vendorID string) string {
	switch strings.ToUpper(vendorID) {
	case "GENUINEINTEL":
		return "Intel Corporation"
	case "AUTHENTICAMD":
		return "AMD"
	case "HYGONHYGON":
		return "Hygon"
	case "  VIA  VIA  ":
		return "VIA"
	default:
		if vendorID != "" {
			return strings.TrimSpace(vendorID)
		}
		return ""
	}
}

// extractCpuFamily extrait la famille (ex: Core i7) du ModelName
func extractCpuFamily(modelName string) string {
	// Patterns: "Core(TM) i7-1165G7", "Ryzen 7", "Xeon(R) E5-2678"
	if idx := strings.Index(modelName, "Core(TM)"); idx >= 0 {
		rest := modelName[idx+9:]
		if i := strings.IndexAny(rest, " -@"); i > 0 {
			return "Core " + strings.TrimSpace(rest[:i])
		}
		return "Core " + strings.TrimSpace(rest)
	}
	if idx := strings.Index(modelName, "Ryzen"); idx >= 0 {
		rest := modelName[idx:]
		if i := strings.IndexAny(rest, " @"); i > 0 {
			return strings.TrimSpace(rest[:i])
		}
		return strings.TrimSpace(rest)
	}
	if idx := strings.Index(modelName, "Xeon(R)"); idx >= 0 {
		return "Xeon"
	}
	if idx := strings.Index(modelName, "Celeron"); idx >= 0 {
		return "Celeron"
	}
	if idx := strings.Index(modelName, "Pentium"); idx >= 0 {
		return "Pentium"
	}
	if idx := strings.Index(modelName, "Athlon"); idx >= 0 {
		return "Athlon"
	}
	return ""
}

func getDriveLetter(device, mountpoint string) string {
	// Windows: C:\ ou \\.\C:
	if len(mountpoint) >= 2 && mountpoint[1] == ':' {
		return mountpoint[0:2]
	}
	if len(device) >= 2 && device[1] == ':' {
		return device[0:2]
	}
	for i, c := range device {
		if c == ':' && i > 0 {
			return device[i-1:i+1]
		}
	}
	return ""
}

func getSystemSerial() string {
	return getSystemSerialOS()
}

func collectGPUs() []GPUInfo {
	return collectGPUsOS()
}

func collectMonitors() []MonitorInfo {
	return collectMonitorsOS()
}

func collectPrinters() []PrinterInfo {
	return collectPrintersOS()
}

func collectNetworkAdapters() []NetworkAdapterInfo {
	return collectNetworkAdaptersOS()
}

func collectWindowsUpdates() []WindowsUpdateInfo {
	return collectWindowsUpdatesOS()
}

func collectWindowsServices() []WindowsServiceInfo   { return collectWindowsServicesOS() }
func collectWindowsStartup() []WindowsStartupInfo   { return collectWindowsStartupOS() }
func collectWindowsShared() []WindowsSharedInfo      { return collectWindowsSharedOS() }
func collectWindowsMapped() []WindowsMappedDriveInfo { return collectWindowsMappedOS() }
func collectWindowsUsers() []WindowsUserInfo         { return collectWindowsUsersOS() }
func collectWindowsUserGroups() []string             { return collectWindowsUserGroupsOS() }
func collectWindowsLicense() *WindowsLicenseInfo    { return collectWindowsLicenseOS() }

// parseWindowsVersionFallback extrait build de "10.0.26200.7840" et retourne ("Windows 11", "24H2")
func parseWindowsVersionFallback(platformVersion string) (string, string) {
	parts := strings.Split(platformVersion, ".")
	if len(parts) < 3 {
		return "", ""
	}
	build, err := strconv.Atoi(parts[2])
	if err != nil {
		return "", ""
	}
	version := ""
	if build >= 26200 {
		version = "25H2"
	} else if build >= 26100 {
		version = "24H2"
	} else if build >= 22631 {
		version = "23H2"
	} else if build >= 22621 {
		version = "22H2"
	} else if build >= 22000 {
		version = "21H2"
	} else if build >= 19045 {
		version = "22H2"
	} else if build >= 19044 {
		version = "21H2"
	} else if build >= 19043 {
		version = "21H1"
	} else if build >= 19042 {
		version = "20H2"
	} else if build >= 18363 {
		version = "19H2"
	} else {
		return "", ""
	}
	name := "Windows 10"
	if build >= 22000 {
		name = "Windows 11"
	}
	return name, version
}

func sendInventory(url, apiKey string, data []byte) (*http.Response, error) {
	req, err := http.NewRequest("POST", url, bytes.NewBuffer(data))
	if err != nil {
		return nil, err
	}
	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("X-Api-Key", apiKey)
	req.Header.Set("User-Agent", "ITManager-Agent/"+agentVersion)

	client := &http.Client{Timeout: 30 * time.Second}
	return client.Do(req)
}
