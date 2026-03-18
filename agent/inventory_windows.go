//go:build windows

package main

import (
	"encoding/json"
	"fmt"
	"os"
	"os/exec"
	"path/filepath"
	"strconv"
	"strings"

	"github.com/shirou/gopsutil/v3/disk"
	"golang.org/x/sys/windows"
)

func getSystemSerialOS() string {
	cmd := exec.Command("powershell", "-NoProfile", "-Command",
		"(Get-WmiObject Win32_BIOS).SerialNumber")
	out, err := cmd.Output()
	if err != nil {
		return ""
	}
	return strings.TrimSpace(string(out))
}

// getMotherboardBiosInfo retourne (S/N carte mère, version BIOS)
func getMotherboardBiosInfo() (string, string) {
	psScript := `
		$mb = Get-WmiObject Win32_BaseBoard -ErrorAction SilentlyContinue | Select-Object -First 1
		$bios = Get-WmiObject Win32_BIOS -ErrorAction SilentlyContinue | Select-Object -First 1
		$mbSn = if ($mb -and $mb.SerialNumber) { $mb.SerialNumber.Trim() } else { '' }
		$biosVer = if ($bios -and $bios.SMBIOSBIOSVersion) { $bios.SMBIOSBIOSVersion.Trim() } else { '' }
		$mbSn + '|' + $biosVer
	`
	cmd := exec.Command("powershell", "-NoProfile", "-Command", psScript)
	out, err := cmd.Output()
	if err != nil {
		return "", ""
	}
	parts := strings.SplitN(strings.TrimSpace(string(out)), "|", 2)
	if len(parts) == 2 {
		mb := strings.TrimSpace(parts[0])
		bios := strings.TrimSpace(parts[1])
		// Ignorer les valeurs par défaut OEM
		if mb == "Default string" || mb == "To Be Filled By O.E.M." || mb == "None" {
			mb = ""
		}
		return mb, bios
	}
	return "", ""
}

// getVmUuid retourne l'UUID BIOS/SMBIOS (pour lien auto avec VM ESXi). Format standard hyphened.
func getVmUuid() string {
	cmd := exec.Command("powershell", "-NoProfile", "-Command",
		"(Get-WmiObject Win32_ComputerSystemProduct -ErrorAction SilentlyContinue | Select-Object -First 1).UUID")
	out, err := cmd.Output()
	if err != nil {
		return ""
	}
	return strings.TrimSpace(string(out))
}

// getWindowsOSInfo retourne (nom avec édition Pro/Home/etc., version) ex: "Windows 11 Pro", "23H2" ou "Windows Server 2019 Standard", "1809"
// Priorité: EditionID (registre) pour l'édition, puis ProductName/Caption en secours.
// Gère correctement Windows Server 2016/2019/2022/2025.
func getWindowsOSInfo() (string, string) {
	psScript := `
		$ErrorActionPreference = 'SilentlyContinue'
		$reg = Get-ItemProperty 'HKLM:\SOFTWARE\Microsoft\Windows NT\CurrentVersion' -ErrorAction SilentlyContinue
		$os = Get-WmiObject Win32_OperatingSystem -ErrorAction SilentlyContinue
		$build = if ($os) { [int]$os.BuildNumber } else { 0 }
		$ed = if ($reg -and $reg.EditionID) { [string]$reg.EditionID.Trim() } else { '' }
		$isServer = $ed -match '^Server'
		if ($isServer) {
			$serverBase = if ($build -ge 26100) { 'Windows Server 2025' } elseif ($build -ge 20348) { 'Windows Server 2022' } elseif ($build -ge 17763) { 'Windows Server 2019' } elseif ($build -ge 14393) { 'Windows Server 2016' } else { 'Windows Server' }
			$serverEd = ''; if ($ed -eq 'ServerStandard') { $serverEd = 'Standard' } elseif ($ed -eq 'ServerDatacenter') { $serverEd = 'Datacenter' } elseif ($ed -eq 'ServerStandardCore') { $serverEd = 'Standard' } elseif ($ed -eq 'ServerDatacenterCore') { $serverEd = 'Datacenter' } elseif ($ed -ne '') { $serverEd = $ed -replace '^Server', '' }
			$name = $serverBase; if ($serverEd -ne '') { $name = $serverBase + ' ' + $serverEd }
			$displayVer = if ($reg -and $reg.DisplayVersion) { [string]$reg.DisplayVersion.Trim() } else { '' }
			if (-not $displayVer) {
				if ($build -ge 26100) { $displayVer = '24H2' } elseif ($build -ge 20348) { $displayVer = '21H2' } elseif ($build -ge 17763) { $displayVer = '1809' } elseif ($build -ge 14393) { $displayVer = '1607' } else { $displayVer = [string]$build }
			}
			$name + '|' + $displayVer.Trim()
		} else {
			$base = if ($build -ge 22000) { 'Windows 11' } else { 'Windows 10' }
			$displayVer = if ($reg -and $reg.DisplayVersion) { [string]$reg.DisplayVersion.Trim() } else { '' }
			if (-not $displayVer) {
				if ($build -ge 26200) { $displayVer = '25H2' } elseif ($build -ge 26100) { $displayVer = '24H2' } elseif ($build -ge 22631) { $displayVer = '23H2' } elseif ($build -ge 22621) { $displayVer = '22H2' } elseif ($build -ge 22000) { $displayVer = '21H2' } elseif ($build -ge 19045) { $displayVer = '22H2' } elseif ($build -ge 19044) { $displayVer = '21H2' } elseif ($build -ge 19043) { $displayVer = '21H1' } elseif ($build -ge 19042) { $displayVer = '20H2' } elseif ($build -ge 18363) { $displayVer = '19H2' } else { $displayVer = [string]$build }
			}
			$edMap = @{}
			$edMap['Professional']='Pro'; $edMap['Professionnel']='Pro'; $edMap['ProfessionalWorkstation']='Pro for Workstations'
			$edMap['Core']='Home'; $edMap['CoreSingleLanguage']='Home Single Language'; $edMap['CoreCountrySpecific']='Home'
			$edMap['Enterprise']='Enterprise'; $edMap['Entreprise']='Enterprise'; $edMap['EnterpriseN']='Enterprise N'
			$edMap['Education']='Education'; $edMap['EducationN']='Education N'; $edMap['IoTEnterprise']='IoT Enterprise'
			$edName = ''; if ($ed -ne '') { if ($edMap.ContainsKey($ed)) { $edName = $edMap[$ed] } else { $edName = $ed } }
			$name = $base; if ($edName -ne '') { $name = $base + ' ' + $edName }
			$name + '|' + $displayVer.Trim()
		}
	`
	cmd := exec.Command("powershell", "-NoProfile", "-Command", psScript)
	out, err := cmd.Output()
	if err != nil {
		return "", ""
	}
	parts := strings.SplitN(strings.TrimSpace(string(out)), "|", 2)
	if len(parts) == 2 {
		return strings.TrimSpace(parts[0]), strings.TrimSpace(parts[1])
	}
	return "", ""
}

// getLoggedUserCreatedAt retourne la date de création du profil utilisateur (InstallDate Win32_UserProfile).
// Utilise le même utilisateur que getLoggedUser. Retourne une chaîne ISO 8601 ou vide.
func getLoggedUserCreatedAt() string {
	psScript := `
		$user = $env:USERNAME
		if ($user -eq 'SYSTEM' -or $user -eq '') {
			$proc = Get-CimInstance Win32_Process -Filter "Name='explorer.exe'" -ErrorAction SilentlyContinue | Select-Object -First 1
			if ($proc) {
				$r = $proc | Invoke-CimMethod -MethodName GetOwner -ErrorAction SilentlyContinue
				if ($r -and $r.User) { $user = $r.User }
			}
		}
		if ($user -eq 'SYSTEM' -or $user -eq '') { exit }
		$userPart = ($user -split '\\\\')[-1]
		$prof = Get-CimInstance Win32_UserProfile -ErrorAction SilentlyContinue | Where-Object { $_.LocalPath -and $_.LocalPath -match ('\\\\' + [regex]::Escape($userPart) + '$') } | Select-Object -First 1
		if ($prof -and $prof.InstallDate) {
			$prof.InstallDate.ToString('yyyy-MM-ddTHH:mm:ss')
		}
	`
	cmd := exec.Command("powershell", "-NoProfile", "-Command", psScript)
	out, err := cmd.Output()
	if err != nil {
		return ""
	}
	return strings.TrimSpace(string(out))
}

// getLoggedUser retourne l'utilisateur actuellement connecté ou le dernier utilisateur.
// Si l'agent tourne en tant que SYSTEM (planification), on prend l'owner d'explorer.exe.
func getLoggedUser() string {
	psScript := `
		$user = $env:USERNAME
		if ($user -eq 'SYSTEM' -or $user -eq '') {
			$proc = Get-CimInstance Win32_Process -Filter "Name='explorer.exe'" -ErrorAction SilentlyContinue | Select-Object -First 1
			if ($proc) {
				$r = $proc | Invoke-CimMethod -MethodName GetOwner -ErrorAction SilentlyContinue
				if ($r -and $r.User) {
					$user = $r.User
					if ($r.Domain) { $user = $r.Domain + '\' + $user }
				}
			}
		} else {
			$domain = $env:USERDOMAIN
			if ($domain) { $user = $domain + '\' + $user }
		}
		$user
	`
	cmd := exec.Command("powershell", "-NoProfile", "-Command", psScript)
	out, err := cmd.Output()
	if err != nil {
		return ""
	}
	return strings.TrimSpace(string(out))
}

// getNetworkInfo retourne la config réseau (IP principale IPv4)
func getNetworkInfo() *NetworkInfo {
	psScript := `
		$configs = Get-NetIPConfiguration -ErrorAction SilentlyContinue | Where-Object { $_.IPv4Address -and $_.IPv4Address.IPAddress -notmatch ':' }
		$config = $configs | Where-Object { $_.IPv4DefaultGateway } | Select-Object -First 1
		if (-not $config) { $config = $configs | Where-Object { $_.IPv4Address.IPAddress -match '^(192\.168|10\.)' } | Select-Object -First 1 }
		if (-not $config) { $config = $configs | Select-Object -First 1 }
		if (-not $config) { exit 1 }
		$ip = $config.IPv4Address.IPAddress
		$prefix = [string]$config.IPv4Address.PrefixLength
		$gw = ''; if ($config.IPv4DefaultGateway) { $gw = $config.IPv4DefaultGateway.NextHop }
		$dnsAddrs = (Get-DnsClientServerAddress -AddressFamily IPv4 -ErrorAction SilentlyContinue | Where-Object { $_.ServerAddresses } | ForEach-Object { $_.ServerAddresses }) | Select-Object -Unique
		$dns = ($dnsAddrs | Where-Object { $_ -notmatch ':' }) -join ', '
		$ip + '|' + $gw + '|' + $prefix + '|' + $dns
	`
	cmd := exec.Command("powershell", "-NoProfile", "-Command", psScript)
	out, err := cmd.Output()
	if err != nil {
		return nil
	}
	parts := strings.SplitN(strings.TrimSpace(string(out)), "|", 4)
	if len(parts) < 1 || strings.TrimSpace(parts[0]) == "" {
		return nil
	}
	ipStr := strings.TrimSpace(parts[0])
	if strings.Contains(ipStr, ":") {
		return nil
	}
	info := &NetworkInfo{IPAddress: ipStr}
	if len(parts) > 1 {
		gw := strings.TrimSpace(parts[1])
		if !strings.Contains(gw, ":") {
			info.Gateway = gw
		}
	}
	if len(parts) > 2 && parts[2] != "" {
		info.SubnetMask = strings.TrimSpace(parts[2])
		if len(info.SubnetMask) <= 2 {
			info.SubnetMask = prefixToMask(info.SubnetMask)
		}
	}
	if len(parts) > 3 {
		dns := filterIPv4List(strings.TrimSpace(parts[3]))
		if dns != "" {
			info.DNSServers = dns
		}
	}
	return info
}

// collectNetworkAdaptersOS liste les cartes (Ethernet, Wi-Fi) avec IP/prefix, gateway, SSID
func collectNetworkAdaptersOS() []NetworkAdapterInfo {
	// Utiliser [char]10 pour newline pour éviter backtick dans la chaîne Go
	psScript := "$adapters=@();$configs=Get-NetIPConfiguration -EA SilentlyContinue|Where-Object{$_.IPv4Address -and $_.IPv4Address.IPAddress -notmatch ':'};$wifiSSIDs=@{};try{$netsh=netsh wlan show interfaces 2>$null;$cur='';foreach($line in ($netsh -split [char]10)){$line=$line.Trim();if($line -match '^\\s*Name\\s*:\\s*(.+)$'){$cur=$matches[1].Trim()};if($cur -ne '' -and $line -match '^\\s*SSID\\s*:\\s*(.+)$'){$wifiSSIDs[$cur]=$matches[1].Trim();$cur=''}}}catch{};foreach($c in $configs){$alias=$c.InterfaceAlias -replace '\"','';$ip=$c.IPv4Address.IPAddress;$prefix=[int]$c.IPv4Address.PrefixLength;$gw='';if($c.IPv4DefaultGateway){$gw=$c.IPv4DefaultGateway.NextHop};$mediaType=0;$na=Get-NetAdapter -Name $alias -EA SilentlyContinue;if($na){$mediaType=[int]$na.MediaType};$typeStr='Ethernet';if($mediaType -eq 9 -or $mediaType -eq 71){$typeStr='Wi-Fi'};$ssid='';if($typeStr -eq 'Wi-Fi' -and $wifiSSIDs.ContainsKey($alias)){$ssid=$wifiSSIDs[$alias]};$adapters+=[PSCustomObject]@{Name=$alias;Type=$typeStr;IPCidr=\"$ip/$prefix\";Gateway=$gw;WifiSSID=$ssid}};$adapters|ConvertTo-Json"
	cmd := exec.Command("powershell", "-NoProfile", "-Command", psScript)
	out, err := cmd.Output()
	if err != nil {
		return nil
	}
	var list []struct {
		Name     string `json:"Name"`
		Type     string `json:"Type"`
		IPCidr   string `json:"IPCidr"`
		Gateway  string `json:"Gateway"`
		WifiSSID string `json:"WifiSSID"`
	}
	if err := json.Unmarshal(out, &list); err != nil {
		var single struct {
			Name     string `json:"Name"`
			Type     string `json:"Type"`
			IPCidr   string `json:"IPCidr"`
			Gateway  string `json:"Gateway"`
			WifiSSID string `json:"WifiSSID"`
		}
		if json.Unmarshal(out, &single) == nil {
			list = []struct {
				Name     string `json:"Name"`
				Type     string `json:"Type"`
				IPCidr   string `json:"IPCidr"`
				Gateway  string `json:"Gateway"`
				WifiSSID string `json:"WifiSSID"`
			}{single}
		} else {
			return nil
		}
	}
	result := make([]NetworkAdapterInfo, 0, len(list))
	for _, a := range list {
		name := strings.TrimSpace(a.Name)
		ipCidr := strings.TrimSpace(a.IPCidr)
		if name == "" && ipCidr == "" {
			continue
		}
		result = append(result, NetworkAdapterInfo{
			Name:     name,
			Type:     strings.TrimSpace(a.Type),
			IPCidr:   ipCidr,
			Gateway:  strings.TrimSpace(a.Gateway),
			WifiSSID: strings.TrimSpace(a.WifiSSID),
		})
	}
	return result
}

func filterIPv4List(list string) string {
	parts := strings.Split(list, ",")
	var v4 []string
	for _, p := range parts {
		addr := strings.TrimSpace(p)
		if addr != "" && !strings.Contains(addr, ":") {
			v4 = append(v4, addr)
		}
	}
	return strings.Join(v4, ", ")
}

func prefixToMask(prefix string) string {
	p, err := strconv.Atoi(prefix)
	if err != nil || p < 0 || p > 32 {
		return prefix
	}
	mask := uint32(0xFFFFFFFF << (32 - p))
	return fmt.Sprintf("%d.%d.%d.%d", (mask>>24)&0xFF, (mask>>16)&0xFF, (mask>>8)&0xFF, mask&0xFF)
}

// getManufacturerModel retourne (marque, modèle) via WMI Win32_ComputerSystem
// getRAMDetails retourne (type, modèle, fréquence MHz) des barrettes RAM
func getRAMDetails() (string, string, int) {
	psScript := `
		$sticks = Get-CimInstance Win32_PhysicalMemory -ErrorAction SilentlyContinue
		if (-not $sticks -or $sticks.Count -eq 0) { exit 1 }
		$first = $sticks[0]
		$typeNum = [int]$first.MemoryType
		$smbiosType = 0
		try { $smbiosType = [int]$first.SMBIOSMemoryType } catch {}
		$speed = [int]$first.Speed
		$partNum = if ($first.PartNumber) { ($first.PartNumber.Trim() -replace '\.+$','' -replace '\s+$','').Trim() } else { '' }
		$manufacturer = if ($first.Manufacturer) { ($first.Manufacturer.Trim() -replace '\.+$','').Trim() } else { '' }
		if ($partNum -eq 'Default string' -or $partNum -eq 'Not Specified' -or $partNum -eq 'None') { $partNum = '' }
		if ($partNum -eq '') { $partNum = $manufacturer }
		elseif ($manufacturer -ne '' -and $partNum -notmatch [regex]::Escape($manufacturer)) { $partNum = $manufacturer + ' ' + $partNum }
		if ($partNum -eq 'Default string' -or $partNum -eq 'Not Specified' -or $partNum -eq 'None') { $partNum = '' }
		$typeMap = @{
			18='DDR'; 19='DDR2'; 20='DDR2'; 24='DDR3'; 26='DDR4'; 27='LPDDR'; 28='LPDDR2'; 29='LPDDR3'; 30='LPDDR4'; 34='LPDDR5'; 35='DDR5'
			21='Other'; 22='DRAM'; 23='SDRAM'; 37='SDRAM'; 39='RDRAM'; 40='DDR'; 41='DDR2'; 49='DDR3'; 51='DDR4'; 52='LPDDR'
			53='LPDDR2'; 54='LPDDR3'; 55='LPDDR4'; 57='HBM'; 58='HBM2'; 59='DDR5'; 60='LPDDR5'
		}
		$typeStr = ''
		if ($typeMap.ContainsKey($smbiosType)) { $typeStr = $typeMap[$smbiosType] }
		elseif ($typeMap.ContainsKey($typeNum)) { $typeStr = $typeMap[$typeNum] }
		if ($typeStr -eq '' -or $typeStr -eq 'Unknown' -or $typeStr -eq 'Other') {
			if ($speed -ge 4400) { $typeStr = 'DDR5' }
			elseif ($speed -ge 2133 -and $speed -le 3200) { $typeStr = 'DDR4' }
			elseif ($speed -ge 800 -and $speed -le 2133) { $typeStr = 'DDR3' }
			elseif ($speed -ge 400 -and $speed -lt 800) { $typeStr = 'DDR2' }
			else { $typeStr = '' }
		}
		$typeStr + '|' + $partNum + '|' + $speed
	`
	cmd := exec.Command("powershell", "-NoProfile", "-Command", psScript)
	out, err := cmd.Output()
	if err != nil {
		return "", "", 0
	}
	parts := strings.SplitN(strings.TrimSpace(string(out)), "|", 3)
	if len(parts) < 3 {
		return "", "", 0
	}
	ramType := strings.TrimSpace(parts[0])
	ramModel := strings.TrimSpace(parts[1])
	speed, _ := strconv.Atoi(strings.TrimSpace(parts[2]))
	if ramType == "Unknown" || ramType == "Other" {
		ramType = ""
	}
	return ramType, ramModel, speed
}

func getManufacturerModel() (string, string) {
	cmd := exec.Command("powershell", "-NoProfile", "-Command",
		"$cs = Get-WmiObject Win32_ComputerSystem; $cs.Manufacturer + '|' + $cs.Model")
	out, err := cmd.Output()
	if err != nil {
		return "", ""
	}
	parts := strings.SplitN(strings.TrimSpace(string(out)), "|", 2)
	if len(parts) == 2 {
		return strings.TrimSpace(parts[0]), strings.TrimSpace(parts[1])
	}
	return "", ""
}

func collectInstalledSoftware() []SoftwareInfo {
	psScript := `
		$regs = @(
			'HKLM:\Software\Microsoft\Windows\CurrentVersion\Uninstall\*',
			'HKLM:\Software\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall\*'
		)
		$results = @()
		foreach ($path in $regs) {
			Get-ItemProperty $path -ErrorAction SilentlyContinue | 
			Where-Object { $_.DisplayName } |
			ForEach-Object {
				$results += [PSCustomObject]@{
					Name = $_.DisplayName -replace '"', ''
					Version = $_.DisplayVersion -replace '"', ''
					InstallDate = $_.InstallDate
				}
			}
		}
		$results | ConvertTo-Json
	`
	cmd := exec.Command("powershell", "-NoProfile", "-ExecutionPolicy", "Bypass", "-Command", psScript)
	out, err := cmd.Output()
	if err != nil {
		return nil
	}

	type softEntry struct {
		Name        string `json:"Name"`
		Version     string `json:"Version"`
		InstallDate string `json:"InstallDate"`
	}

	var list []softEntry
	if err := json.Unmarshal(out, &list); err != nil {
		// Single object or different structure
		var single softEntry
		if json.Unmarshal(out, &single) == nil && strings.TrimSpace(single.Name) != "" {
			return []SoftwareInfo{{
				Name:        single.Name,
				Version:     single.Version,
				InstallDate: formatInstallDate(single.InstallDate),
			}}
		}
		return nil
	}

	result := make([]SoftwareInfo, 0, len(list))
	for _, s := range list {
		if strings.TrimSpace(s.Name) == "" {
			continue
		}
		result = append(result, SoftwareInfo{
			Name:        s.Name,
			Version:     s.Version,
			InstallDate: formatInstallDate(s.InstallDate),
		})
	}
	return result
}

// getRustDeskInfo récupère l'ID RustDesk (Windows, incl. RustDesk Pro / custom client / supportrgd)
func getRustDeskInfo() (id string, _ string) {
	// Chemins possibles: standard, Pro, custom client, supportrgd
	paths := []string{
		`C:\Program Files\supportrgd\supportrgd.exe`,
		`C:\Program Files (x86)\supportrgd\supportrgd.exe`,
		`C:\Program Files\RustDesk\rustdesk.exe`,
		`C:\Program Files (x86)\RustDesk\rustdesk.exe`,
		`C:\Program Files\RustDesk\RustDesk.exe`, // variante casse
		`C:\Program Files\RustDesk Pro\rustdesk.exe`,
		`C:\Program Files\RustDesk Pro\RustDesk.exe`,
		`C:\ProgramData\RustDesk\rustdesk.exe`,
	}
	var exePath string
	for _, p := range paths {
		if _, err := os.Stat(p); err == nil {
			exePath = p
			break
		}
	}
	if exePath == "" {
		for _, cmdName := range []string{"supportrgd", "rustdesk"} {
			if out, err := exec.Command("cmd", "/c", "where", cmdName).Output(); err == nil && len(out) > 0 {
				firstLine := strings.TrimSpace(strings.Split(string(out), "\n")[0])
				if firstLine != "" && !strings.HasPrefix(strings.ToLower(firstLine), "info:") {
					exePath = firstLine
					break
				}
			}
		}
	}
	if exePath == "" {
		// Fallback: lire l'ID depuis les fichiers de config
		return getRustDeskIDFromConfigs(), ""
	}

	cmd := exec.Command(exePath, "--get-id")
	out, err := cmd.Output()
	if err == nil {
		id = strings.TrimSpace(string(out))
		if id != "" && len(id) < 50 && !strings.Contains(id, " ") {
			return id, ""
		}
	}
	return getRustDeskIDFromConfigs(), ""
}

// getRustDeskIDFromConfigs lit l'ID depuis RustDesk2.toml (profils user, LocalService, System)
func getRustDeskIDFromConfigs() string {
	configPaths := []string{
		filepath.Join(os.Getenv("APPDATA"), "supportrgd", "config", "RustDesk2.toml"),
		`C:\Windows\ServiceProfiles\LocalService\AppData\Roaming\supportrgd\config\RustDesk2.toml`,
		`C:\Windows\System32\config\systemprofile\AppData\Roaming\supportrgd\config\RustDesk2.toml`,
		filepath.Join(os.Getenv("ProgramData"), "supportrgd", "config", "RustDesk2.toml"),
		filepath.Join(os.Getenv("APPDATA"), "RustDesk", "config", "RustDesk2.toml"),
		`C:\Windows\ServiceProfiles\LocalService\AppData\Roaming\RustDesk\config\RustDesk2.toml`,
		`C:\Windows\System32\config\systemprofile\AppData\Roaming\RustDesk\config\RustDesk2.toml`,
		filepath.Join(os.Getenv("ProgramData"), "RustDesk", "config", "RustDesk2.toml"),
	}
	for _, p := range configPaths {
		if p == "" || strings.Contains(p, "<nil>") {
			continue
		}
		if b, err := os.ReadFile(p); err == nil {
			if id := extractRustDeskIDFromToml(string(b)); id != "" {
				return id
			}
		}
	}
	return ""
}

// extractRustDeskIDFromToml extrait id = "..." du TOML
func extractRustDeskIDFromToml(content string) string {
	lines := strings.Split(content, "\n")
	for _, line := range lines {
		line = strings.TrimSpace(line)
		if strings.HasPrefix(line, `id = "`) {
			prefix := `id = "`
			start := len(prefix)
			end := strings.Index(line[start:], `"`)
			if end >= 0 {
				return strings.TrimSpace(line[start : start+end])
			}
		}
	}
	return ""
}

func formatInstallDate(s string) string {
	s = strings.TrimSpace(s)
	if s == "" || len(s) < 8 {
		return ""
	}
	// Uniquement format YYYYMMDD (8 chiffres) -> YYYY-MM-DD
	for i := 0; i < 8 && i < len(s); i++ {
		if s[i] < '0' || s[i] > '9' {
			return ""
		}
	}
	y, my, d := s[0:4], s[4:6], s[6:8]
	if y >= "1900" && y <= "2100" && my >= "01" && my <= "12" && d >= "01" && d <= "31" {
		return fmt.Sprintf("%s-%s-%s", y, my, d)
	}
	return ""
}

// getAntivirusInfo récupère l'antivirus via WMI SecurityCenter2 (displayName, enabled, à jour)
// productState: byte 1 = 0x10 enabled, 0x01 disabled; byte 0 = 0x00 à jour
func getAntivirusInfo() (name string, enabled *bool, updated *bool) {
	psScript := `
		try {
			$av = Get-CimInstance -Namespace root/SecurityCenter2 -ClassName AntivirusProduct -ErrorAction Stop | Select-Object -First 1
			if (-not $av) { exit 1 }
			$displayName = $av.displayName -replace '"', ''
			$state = [int]$av.productState
			$byte2 = [byte](($state -shr 8) -band 0xFF)
			$byte0 = [byte]($state -band 0xFF)
			$enabled = ($byte2 -eq 0x10)
			$uptodate = ($byte0 -eq 0)
			$displayName + '|' + $enabled.ToString() + '|' + $uptodate.ToString()
		} catch { exit 1 }
	`
	cmd := exec.Command("powershell", "-NoProfile", "-Command", psScript)
	out, err := cmd.Output()
	if err != nil {
		return "", nil, nil
	}
	parts := strings.SplitN(strings.TrimSpace(string(out)), "|", 3)
	if len(parts) < 1 || strings.TrimSpace(parts[0]) == "" {
		return "", nil, nil
	}
	name = strings.TrimSpace(parts[0])
	if len(parts) >= 2 {
		e := strings.EqualFold(parts[1], "True")
		enabled = &e
	}
	if len(parts) >= 3 {
		u := strings.EqualFold(parts[2], "True")
		updated = &u
	}
	return name, enabled, updated
}

// getFirewallInfo vérifie si le pare-feu Windows est activé (au moins un profil)
func getFirewallInfo() *bool {
	psScript := `
		try {
			$profiles = Get-NetFirewallProfile -ErrorAction Stop
			$anyEnabled = ($profiles | Where-Object { $_.Enabled } | Measure-Object).Count -gt 0
			$anyEnabled.ToString()
		} catch { exit 1 }
	`
	cmd := exec.Command("powershell", "-NoProfile", "-Command", psScript)
	out, err := cmd.Output()
	if err != nil {
		return nil
	}
	s := strings.TrimSpace(string(out))
	if s == "" {
		return nil
	}
	b := strings.EqualFold(s, "True")
	return &b
}

// collectWindowsUpdatesOS liste les mises à jour Windows installées (Get-HotFix)
func collectWindowsUpdatesOS() []WindowsUpdateInfo {
	psScript := `
		Get-HotFix -ErrorAction SilentlyContinue |
			Sort-Object { try { [DateTime]$_.InstalledOn } catch { [DateTime]::MinValue } } -Descending |
			Select-Object -First 300 |
			ForEach-Object {
				$d = if ($_.InstalledOn) { $_.InstalledOn.ToString('yyyy-MM-dd') } else { '' }
				[PSCustomObject]@{ HotFixID=$_.HotFixID; Description=$_.Description; InstalledOn=$d }
			} | ConvertTo-Json
	`
	cmd := exec.Command("powershell", "-NoProfile", "-Command", psScript)
	out, err := cmd.Output()
	if err != nil {
		return nil
	}
	var list []struct {
		HotFixID    string `json:"HotFixID"`
		Description string `json:"Description"`
		InstalledOn string `json:"InstalledOn"`
	}
	if err := json.Unmarshal(out, &list); err != nil {
		var single struct {
			HotFixID    string `json:"HotFixID"`
			Description string `json:"Description"`
			InstalledOn string `json:"InstalledOn"`
		}
		if json.Unmarshal(out, &single) == nil {
			list = []struct {
				HotFixID    string `json:"HotFixID"`
				Description string `json:"Description"`
				InstalledOn string `json:"InstalledOn"`
			}{single}
		} else {
			return nil
		}
	}
	result := make([]WindowsUpdateInfo, 0, len(list))
	for _, u := range list {
		id := strings.TrimSpace(u.HotFixID)
		if id == "" {
			continue
		}
		result = append(result, WindowsUpdateInfo{
			HotFixID:    id,
			Description: strings.TrimSpace(u.Description),
			InstalledOn: strings.TrimSpace(u.InstalledOn),
		})
	}
	return result
}

// collectGPUsOS récupère les cartes graphiques via WMI Win32_VideoController
func collectGPUsOS() []GPUInfo {
	psScript := `
		Get-CimInstance Win32_VideoController -ErrorAction SilentlyContinue | ForEach-Object {
			$name = $_.Name -replace '"', ''
			$adapterRam = [uint64]$_.AdapterRAM
			if ($adapterRam -gt 0x7FFFFFFF) { $adapterRam = [uint64]0 }
			$driver = $_.DriverVersion -replace '"', ''
			$vp = $_.VideoProcessor -replace '"', ''
			[PSCustomObject]@{ Name=$name; AdapterRAM=$adapterRam; DriverVersion=$driver; VideoProcessor=$vp }
		} | ConvertTo-Json
	`
	cmd := exec.Command("powershell", "-NoProfile", "-Command", psScript)
	out, err := cmd.Output()
	if err != nil {
		return nil
	}
	var list []struct {
		Name          string `json:"Name"`
		AdapterRAM    uint64 `json:"AdapterRAM"`
		DriverVersion string `json:"DriverVersion"`
		VideoProcessor string `json:"VideoProcessor"`
	}
	if err := json.Unmarshal(out, &list); err != nil {
		var single struct {
			Name          string `json:"Name"`
			AdapterRAM    uint64 `json:"AdapterRAM"`
			DriverVersion string `json:"DriverVersion"`
			VideoProcessor string `json:"VideoProcessor"`
		}
		if json.Unmarshal(out, &single) == nil && strings.TrimSpace(single.Name) != "" {
			list = []struct {
				Name          string `json:"Name"`
				AdapterRAM    uint64 `json:"AdapterRAM"`
				DriverVersion string `json:"DriverVersion"`
				VideoProcessor string `json:"VideoProcessor"`
			}{single}
		} else {
			return nil
		}
	}
	result := make([]GPUInfo, 0, len(list))
	for _, g := range list {
		name := strings.TrimSpace(g.Name)
		if name == "" {
			continue
		}
		vendor := ""
		if strings.Contains(strings.ToLower(name), "nvidia") {
			vendor = "NVIDIA"
		} else if strings.Contains(strings.ToLower(name), "amd") || strings.Contains(strings.ToLower(name), "radeon") {
			vendor = "AMD"
		} else if strings.Contains(strings.ToLower(name), "intel") {
			vendor = "Intel"
		}
		result = append(result, GPUInfo{
			Model:          name,
			Vendor:          vendor,
			DriverVersion:   strings.TrimSpace(g.DriverVersion),
			VRAMBytes:       g.AdapterRAM,
			VideoProcessor:  strings.TrimSpace(g.VideoProcessor),
		})
	}
	return result
}

// collectMonitorsOS récupère les écrans via WMI (PnP Monitor + résolution depuis Win32_VideoController)
func collectMonitorsOS() []MonitorInfo {
	psScript := `
		$monitors = @()
		Get-CimInstance Win32_PnPEntity -Filter "PNPClass='Monitor'" -ErrorAction SilentlyContinue | ForEach-Object {
			$name = ($_.Name -replace '"', '').Trim()
			if ($name -eq '') { return }
			$monitors += [PSCustomObject]@{ Name=$name }
		}
		$resList = @()
		Get-CimInstance Win32_VideoController -ErrorAction SilentlyContinue | Where-Object { $_.CurrentHorizontalResolution -and $_.CurrentVerticalResolution } | ForEach-Object {
			$resList += $_.CurrentHorizontalResolution.ToString() + 'x' + $_.CurrentVerticalResolution.ToString()
		}
		$res = if ($resList.Count -gt 0) { $resList[0] } else { '' }
		for ($i = 0; $i -lt $monitors.Count; $i++) {
			$r = if ($i -lt $resList.Count) { $resList[$i] } else { $res }
			$monitors[$i] | Add-Member -NotePropertyName Resolution -NotePropertyValue $r -Force
		}
		if ($monitors.Count -eq 0 -and $resList.Count -gt 0) {
			$monitors = @([PSCustomObject]@{ Name='Display'; Resolution=($resList -join ', ') })
		}
		$monitors | ConvertTo-Json
	`
	cmd := exec.Command("powershell", "-NoProfile", "-Command", psScript)
	out, err := cmd.Output()
	if err != nil {
		return nil
	}
	var list []struct {
		Name       string `json:"Name"`
		Resolution string `json:"Resolution"`
	}
	if err := json.Unmarshal(out, &list); err != nil {
		var single struct {
			Name       string `json:"Name"`
			Resolution string `json:"Resolution"`
		}
		if json.Unmarshal(out, &single) == nil {
			list = []struct {
				Name       string `json:"Name"`
				Resolution string `json:"Resolution"`
			}{single}
		} else {
			return nil
		}
	}
	result := make([]MonitorInfo, 0, len(list))
	for _, m := range list {
		name := strings.TrimSpace(m.Name)
		if name == "" {
			continue
		}
		result = append(result, MonitorInfo{
			Name:       name,
			Resolution: strings.TrimSpace(m.Resolution),
		})
	}
	return result
}

// collectPrintersOS récupère les imprimantes via WMI Win32_Printer
func collectPrintersOS() []PrinterInfo {
	psScript := `
		Get-CimInstance Win32_Printer -ErrorAction SilentlyContinue | ForEach-Object {
			[PSCustomObject]@{
				Name    = $_.Name -replace '"', ''
				Driver  = $_.DriverName -replace '"', ''
				Port    = $_.PortName -replace '"', ''
				Default = $_.Default
				Shared  = $_.Shared
			}
		} | ConvertTo-Json
	`
	cmd := exec.Command("powershell", "-NoProfile", "-Command", psScript)
	out, err := cmd.Output()
	if err != nil {
		return nil
	}
	var list []struct {
		Name    string `json:"Name"`
		Driver  string `json:"Driver"`
		Port    string `json:"Port"`
		Default bool   `json:"Default"`
		Shared  bool   `json:"Shared"`
	}
	if err := json.Unmarshal(out, &list); err != nil {
		var single struct {
			Name    string `json:"Name"`
			Driver  string `json:"Driver"`
			Port    string `json:"Port"`
			Default bool   `json:"Default"`
			Shared  bool   `json:"Shared"`
		}
		if json.Unmarshal(out, &single) == nil {
			list = []struct {
				Name    string `json:"Name"`
				Driver  string `json:"Driver"`
				Port    string `json:"Port"`
				Default bool   `json:"Default"`
				Shared  bool   `json:"Shared"`
			}{single}
		} else {
			return nil
		}
	}
	result := make([]PrinterInfo, 0, len(list))
	for _, p := range list {
		name := strings.TrimSpace(p.Name)
		if name == "" {
			continue
		}
		result = append(result, PrinterInfo{
			Name:    name,
			Driver:  strings.TrimSpace(p.Driver),
			Port:    strings.TrimSpace(p.Port),
			Default: p.Default,
			Shared:  p.Shared,
		})
	}
	return result
}

func collectWindowsServicesOS() []WindowsServiceInfo {
	psScript := `[Console]::OutputEncoding = [System.Text.Encoding]::UTF8; Get-CimInstance Win32_Service -ErrorAction SilentlyContinue | Select-Object Name, DisplayName, Description, State, StartMode | ForEach-Object { [PSCustomObject]@{Name=$_.Name;DisplayName=$_.DisplayName;Description=$_.Description;Status=$_.State;StartType=$_.StartMode} } | ConvertTo-Json`
	cmd := exec.Command("powershell", "-NoProfile", "-Command", psScript)
	out, err := cmd.Output()
	if err != nil {
		return nil
	}
	return parseWindowsServiceList(out)
}

func parseWindowsServiceList(out []byte) []WindowsServiceInfo {
	var list []struct {
		Name        string `json:"Name"`
		DisplayName string `json:"DisplayName"`
		Description string `json:"Description"`
		Status      string `json:"Status"`
		StartType   string `json:"StartType"`
	}
	if err := json.Unmarshal(out, &list); err != nil {
		var single struct {
			Name        string `json:"Name"`
			DisplayName string `json:"DisplayName"`
			Description string `json:"Description"`
			Status      string `json:"Status"`
			StartType   string `json:"StartType"`
		}
		if json.Unmarshal(out, &single) == nil {
			list = []struct {
				Name        string `json:"Name"`
				DisplayName string `json:"DisplayName"`
				Description string `json:"Description"`
				Status      string `json:"Status"`
				StartType   string `json:"StartType"`
			}{single}
		} else {
			return nil
		}
	}
	result := make([]WindowsServiceInfo, 0, len(list))
	for _, s := range list {
		result = append(result, WindowsServiceInfo{
			Name:        strings.TrimSpace(s.Name),
			DisplayName: strings.TrimSpace(s.DisplayName),
			Description: strings.TrimSpace(s.Description),
			Status:      strings.TrimSpace(s.Status),
			StartType:   strings.TrimSpace(s.StartType),
		})
	}
	return result
}

func collectWindowsStartupOS() []WindowsStartupInfo {
	psScript := `$ErrorActionPreference='SilentlyContinue';[Console]::OutputEncoding=[System.Text.Encoding]::UTF8;$r=@();$paths=@('HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Run','HKLM:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Run','HKCU:\SOFTWARE\Microsoft\Windows\CurrentVersion\Run','HKCU:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Run');foreach($k in $paths){if(Test-Path $k){$p=Get-ItemProperty -Path $k -EA 0;if($p){$p.PSObject.Properties|Where-Object{$_.Name -notmatch '^PS' -and $_.Name -ne '(default)'}|ForEach-Object{$r+=[PSCustomObject]@{Name=$_.Name;Command=[string]$_.Value;Location=$k}}}}};if($r.Count -eq 0){'[]'}else{$r|ConvertTo-Json}`
	cmd := exec.Command("powershell", "-NoProfile", "-ExecutionPolicy", "Bypass", "-Command", psScript)
	out, err := cmd.Output()
	if err != nil {
		return nil
	}
	out = []byte(strings.TrimSpace(string(out)))
	if len(out) == 0 || string(out) == "[]" {
		return []WindowsStartupInfo{}
	}
	var list []struct {
		Name     string `json:"Name"`
		Command  string `json:"Command"`
		Location string `json:"Location"`
	}
	if err := json.Unmarshal(out, &list); err != nil {
		var single struct {
			Name     string `json:"Name"`
			Command  string `json:"Command"`
			Location string `json:"Location"`
		}
		if json.Unmarshal(out, &single) == nil {
			list = []struct {
				Name     string `json:"Name"`
				Command  string `json:"Command"`
				Location string `json:"Location"`
			}{single}
		} else {
			return nil
		}
	}
	result := make([]WindowsStartupInfo, 0, len(list))
	for _, s := range list {
		result = append(result, WindowsStartupInfo{
			Name:     strings.TrimSpace(s.Name),
			Command:  strings.TrimSpace(s.Command),
			Location: strings.TrimSpace(s.Location),
		})
	}
	return result
}

func collectWindowsSharedOS() []WindowsSharedInfo {
	psScript := `$ErrorActionPreference='SilentlyContinue';[Console]::OutputEncoding=[System.Text.Encoding]::UTF8;$r=@();Get-SmbShare -EA 0|Where-Object{$_.Special -eq $false}|ForEach-Object{$r+=[PSCustomObject]@{Name=$_.Name;Path=$_.Path;Description=$_.Description}};if($r.Count -eq 0){Get-WmiObject Win32_Share -EA 0|Where-Object{$_.Name -notlike '*$'}|ForEach-Object{$r+=[PSCustomObject]@{Name=$_.Name;Path=$_.Path;Description=$_.Description}}};if($r.Count -eq 0){'[]'}else{$r|ConvertTo-Json}`
	cmd := exec.Command("powershell", "-NoProfile", "-ExecutionPolicy", "Bypass", "-Command", psScript)
	out, err := cmd.Output()
	if err != nil {
		return nil
	}
	var list []struct {
		Name        string `json:"Name"`
		Path        string `json:"Path"`
		Description string `json:"Description"`
	}
	if err := json.Unmarshal(out, &list); err != nil {
		var single struct {
			Name        string `json:"Name"`
			Path        string `json:"Path"`
			Description string `json:"Description"`
		}
		if json.Unmarshal(out, &single) == nil {
			list = []struct {
				Name        string `json:"Name"`
				Path        string `json:"Path"`
				Description string `json:"Description"`
			}{single}
		} else {
			return nil
		}
	}
	result := make([]WindowsSharedInfo, 0, len(list))
	for _, s := range list {
		result = append(result, WindowsSharedInfo{
			Name:        strings.TrimSpace(s.Name),
			Path:        strings.TrimSpace(s.Path),
			Description: strings.TrimSpace(s.Description),
		})
	}
	return result
}

func collectWindowsMappedOS() []WindowsMappedDriveInfo {
	psScript := `
		[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
		Get-WmiObject Win32_MappedLogicalDisk -ErrorAction SilentlyContinue | ForEach-Object {
			$p = $_.ProviderName; $d = $_.DeviceID
			[PSCustomObject]@{DriveLetter=$d;Path=$p;Label=''}
		} | ConvertTo-Json
	`
	cmd := exec.Command("powershell", "-NoProfile", "-Command", psScript)
	out, err := cmd.Output()
	if err != nil {
		return nil
	}
	var list []struct {
		DriveLetter string `json:"DriveLetter"`
		Path        string `json:"Path"`
		Label       string `json:"Label"`
	}
	if err := json.Unmarshal(out, &list); err != nil {
		var single struct {
			DriveLetter string `json:"DriveLetter"`
			Path        string `json:"Path"`
			Label       string `json:"Label"`
		}
		if json.Unmarshal(out, &single) == nil {
			list = []struct {
				DriveLetter string `json:"DriveLetter"`
				Path        string `json:"Path"`
				Label       string `json:"Label"`
			}{single}
		} else {
			return nil
		}
	}
	result := make([]WindowsMappedDriveInfo, 0, len(list))
	for _, m := range list {
		result = append(result, WindowsMappedDriveInfo{
			DriveLetter: strings.TrimSpace(m.DriveLetter),
			Path:        strings.TrimSpace(m.Path),
			Label:       strings.TrimSpace(m.Label),
		})
	}
	return result
}

func collectWindowsUsersOS() []WindowsUserInfo {
	// Uniquement les utilisateurs ayant un profil sur cette machine (qui s'y sont connectés)
	// Basé sur Win32_UserProfile. Récupère FullName (Win32_UserAccount par SID) et LastUseTime (conversion WMI)
	psScript := `
		$ErrorActionPreference='SilentlyContinue';[Console]::OutputEncoding=[System.Text.Encoding]::UTF8
		Add-Type -AssemblyName System.Management
		$r=@()
		$exclude=@('Default','Public','Default User','All Users','WDAGUtilityAccount','defaultuser0')
		Get-CimInstance Win32_UserProfile -EA 0 | Where-Object {
			-not $_.Special -and $_.LocalPath -and $_.LocalPath -match '\\Users\\([^\\]+)$' -and $exclude -notcontains $matches[1]
		} | ForEach-Object {
			$u=$matches[1]
			$last=''
			if($_.LastUseTime){
				try{
					$dt=[Management.ManagementDateTimeConverter]::ToDateTime($_.LastUseTime)
					$last=$dt.ToString('yyyy-MM-dd HH:mm:ss')
				}catch{}
			}
			$full=''
			try{
				$acc=Get-CimInstance Win32_UserAccount -Filter "SID='$($_.SID)'" -EA 0 | Select-Object -First 1
				if($acc -and $acc.FullName){$full=[string]$acc.FullName.Trim()}
			}catch{}
			if($full -eq ''){
				try{
					$lu=Get-LocalUser -Name $u -EA 0
					if($lu -and $lu.FullName){$full=[string]$lu.FullName.Trim()}
				}catch{}
			}
			$at='Local'
			try{
				$obj=[System.Security.Principal.SecurityIdentifier]::new($_.SID).Translate([System.Security.Principal.NTAccount])
				if($obj.Value -match '^[^\\]+\\.+$'){$at='AD'}
			}catch{}
			$r+=[PSCustomObject]@{Username=$u;FullName=$full;LastLogin=$last;AccountType=$at}
		}
		$r=$r|Sort-Object -Property LastLogin -Descending
		if($r.Count -eq 0){'[]'}else{$r|ConvertTo-Json}
	`
	cmd := exec.Command("powershell", "-NoProfile", "-Command", psScript)
	out, err := cmd.Output()
	if err != nil {
		return nil
	}
	var list []struct {
		Username    string `json:"Username"`
		FullName    string `json:"FullName"`
		LastLogin   string `json:"LastLogin"`
		AccountType string `json:"AccountType"`
	}
	if err := json.Unmarshal(out, &list); err != nil {
		var single struct {
			Username    string `json:"Username"`
			FullName    string `json:"FullName"`
			LastLogin   string `json:"LastLogin"`
			AccountType string `json:"AccountType"`
		}
		if json.Unmarshal(out, &single) == nil {
			list = []struct {
				Username    string `json:"Username"`
				FullName    string `json:"FullName"`
				LastLogin   string `json:"LastLogin"`
				AccountType string `json:"AccountType"`
			}{single}
		} else {
			return nil
		}
	}
	result := make([]WindowsUserInfo, 0, len(list))
	seen := make(map[string]bool)
	for _, u := range list {
		un := strings.TrimSpace(u.Username)
		if un == "" || seen[un] {
			continue
		}
		seen[un] = true
		result = append(result, WindowsUserInfo{
			Username:    un,
			FullName:    strings.TrimSpace(u.FullName),
			LastLogin:   strings.TrimSpace(u.LastLogin),
			AccountType: strings.TrimSpace(u.AccountType),
		})
	}
	return result
}

func collectWindowsUserGroupsOS() []string {
	psScript := `whoami /groups 2>$null | Where-Object { $_ -match '^\s+\*' } | ForEach-Object { ($_ -split '\s+',4)[3].Trim() } | Where-Object { $_ -ne '' -and $_ -notmatch '^Mandatory' } | ConvertTo-Json`
	cmd := exec.Command("powershell", "-NoProfile", "-Command", psScript)
	out, err := cmd.Output()
	if err != nil {
		return nil
	}
	var list []string
	if err := json.Unmarshal(out, &list); err != nil {
		var s string
		if json.Unmarshal(out, &s) == nil && s != "" {
			list = []string{s}
		} else {
			return nil
		}
	}
	result := make([]string, 0, len(list))
	for _, g := range list {
		g = strings.TrimSpace(g)
		if g != "" {
			result = append(result, g)
		}
	}
	return result
}

func collectWindowsLicenseOS() *WindowsLicenseInfo {
	psScript := `[Console]::OutputEncoding = [System.Text.Encoding]::UTF8; (Get-WmiObject -Class SoftwareLicensingProduct -ErrorAction SilentlyContinue | Where-Object { $_.ApplicationId -eq '55c92734-d682-4d71-983e-d6ec3f16059f' -and $_.LicenseStatus -ne 0 } | Select-Object -First 1) | ForEach-Object { [PSCustomObject]@{Description=$_.Description;Status=$_.LicenseStatus} } | ConvertTo-Json`
	cmd := exec.Command("powershell", "-NoProfile", "-Command", psScript)
	out, err := cmd.Output()
	if err != nil {
		return nil
	}
	var v struct {
		Description string `json:"Description"`
		Status      int    `json:"Status"`
	}
	if err := json.Unmarshal(out, &v); err != nil {
		return nil
	}
	statusStr := ""
	switch v.Status {
	case 1: statusStr = "Licensed"
	case 2: statusStr = "Out-of-box grace"
	case 3: statusStr = "Out-of-tolerance grace"
	case 4: statusStr = "Non-genuine grace"
	case 5: statusStr = "Notification"
	case 6: statusStr = "Extended grace"
	default: statusStr = fmt.Sprintf("%d", v.Status)
	}
	return &WindowsLicenseInfo{
		Description: strings.TrimSpace(v.Description),
		Status:      statusStr,
	}
}

// collectDisksOS récupère les disques locaux uniquement (exclut les lecteurs réseau DRIVE_REMOTE)
func collectDisksOS() []DiskInfo {
	partitions, err := disk.Partitions(true)
	if err != nil {
		return nil
	}
	var localPartitions []disk.PartitionStat
	for _, p := range partitions {
		path := p.Mountpoint
		if path == "" {
			continue
		}
		// GetDriveType attend un chemin racine type "C:\"
		if len(path) == 2 && path[1] == ':' {
			path = path + "\\"
		} else if !strings.HasSuffix(path, "\\") && !strings.HasSuffix(path, "/") {
			path = path + "\\"
		}
		pathPtr, _ := windows.UTF16PtrFromString(path)
		driveType := windows.GetDriveType(pathPtr)
		if driveType == windows.DRIVE_REMOTE {
			continue // Exclure les lecteurs réseau
		}
		localPartitions = append(localPartitions, p)
	}
	return collectDisksGeneric(localPartitions)
}
