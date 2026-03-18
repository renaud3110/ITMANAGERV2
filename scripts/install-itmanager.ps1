# IT Manager - Script d'installation agent + moniteur
# Exécuter en tant qu'administrateur : clic droit -> Exécuter en tant qu'administrateur
# Ou : powershell -ExecutionPolicy Bypass -File install-itmanager.ps1

$ErrorActionPreference = "Stop"
$BaseUrl = "https://it.rgdsystems.be"
$DefaultApiKey = "itmanager-agent-2024-secure-key-change-me"
$InstallDir = "$env:ProgramFiles\ITManager-Agent"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  IT Manager - Installation Agent" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Vérifier les droits admin
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $isAdmin) {
    Write-Host "ATTENTION: Exécutez ce script en tant qu'administrateur (clic droit -> Exécuter en tant qu'administrateur)" -ForegroundColor Yellow
    $cont = Read-Host "Continuer quand meme ? (o/N)"
    if ($cont -ne "o" -and $cont -ne "O") { exit 1 }
}

# Questions
Write-Host "ID du site (obligatoire, ex: 1, 2, 3) : " -NoNewline
$siteId = Read-Host
if ([string]::IsNullOrWhiteSpace($siteId) -or [int]$siteId -le 0) {
    Write-Host "Erreur: L'ID du site est obligatoire." -ForegroundColor Red
    exit 1
}

Write-Host "Type de machine : (1) PC  (2) Serveur [1] : " -NoNewline
$typeInput = Read-Host
$deviceType = if ($typeInput -eq "2") { "server" } else { "pc" }
Write-Host "  -> $deviceType" -ForegroundColor Gray

Write-Host "Cle API [${DefaultApiKey}] : " -NoNewline
$apiKey = Read-Host
if ([string]::IsNullOrWhiteSpace($apiKey)) { $apiKey = $DefaultApiKey }
Write-Host "  -> utilise" -ForegroundColor Gray

Write-Host "URL API [${BaseUrl}/api/inventory.php] : " -NoNewline
$apiUrl = Read-Host
if ([string]::IsNullOrWhiteSpace($apiUrl)) { $apiUrl = "$BaseUrl/api/inventory.php" }
$apiUrl = $apiUrl.TrimEnd('/')

Write-Host "Installer RustDesk (supportrgd) en meme temps ? (o/N) : " -NoNewline
$installRustdesk = Read-Host
$installRustdesk = ($installRustdesk -eq "o" -or $installRustdesk -eq "O")

Write-Host ""
Write-Host "Installation dans: $InstallDir" -ForegroundColor Gray
Write-Host ""

# Créer le dossier
New-Item -ItemType Directory -Force -Path $InstallDir | Out-Null

# Arrêter le service AVANT le téléchargement (évite "fichier utilisé par un autre processus" lors d'une mise à jour)
$svc = Get-Service -Name "ITManagerMonitor" -ErrorAction SilentlyContinue
if ($svc -and $svc.Status -eq 'Running') {
    Write-Host "Arret du service existant (mise a jour)..." -ForegroundColor Yellow
    Stop-Service ITManagerMonitor -Force -ErrorAction SilentlyContinue
    Start-Sleep -Seconds 3
}

# Télécharger monitor (obligatoire) + agent inventaire (optionnel, pour inventaire toutes les 2h)
$monitorUrl = "$BaseUrl/api/download_agent.php?file=itmanager-monitor.exe"
$agentUrl = "$BaseUrl/api/download_agent.php?file=itmanager-agent.exe"

# Forcer TLS 1.2 (obligatoire pour HTTPS)
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12

function Download-File {
    param($Url, $Dest)
    # Méthode 1 : WebClient (rapide)
    try {
        $wc = New-Object System.Net.WebClient
        $wc.Headers.Add("User-Agent", "ITManager-Installer/1.0")
        $wc.DownloadFile($Url, $Dest)
        return $true
    } catch {
        Write-Host "  WebClient echoue: $($_.Exception.Message)" -ForegroundColor Yellow
    }
    # Méthode 2 : Invoke-WebRequest (souvent plus robuste avec certains serveurs/proxy)
    try {
        $ProgressPreference = "SilentlyContinue"
        Invoke-WebRequest -Uri $Url -OutFile $Dest -UseBasicParsing -UserAgent "ITManager-Installer/1.0"
        return $true
    } catch {
        Write-Host "  Telechargement echoue: $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

Write-Host "Telechargement des agents..." -ForegroundColor Yellow
Write-Host "  URL: $monitorUrl" -ForegroundColor Gray
if (-not (Download-File $monitorUrl "$InstallDir\itmanager-monitor.exe")) {
    Write-Host "Erreur: Impossible de telecharger itmanager-monitor.exe" -ForegroundColor Red
    Write-Host "Si 'fichier utilise par un autre processus': arretez le service manuellement:" -ForegroundColor Yellow
    Write-Host "  sc stop ITManagerMonitor" -ForegroundColor Gray
    Write-Host "Puis relancez ce script." -ForegroundColor Yellow
    Write-Host "Autres causes: reseau, pare-feu, proxy, certificat SSL." -ForegroundColor Yellow
    Write-Host "Testez l'URL dans un navigateur: $monitorUrl" -ForegroundColor Yellow
    exit 1
}
Write-Host "  OK: Moniteur (telemétrie + ESXi/Proxmox)" -ForegroundColor Green

$hasAgent = Download-File $agentUrl "$InstallDir\itmanager-agent.exe"
if ($hasAgent) {
    Write-Host "  OK: Agent inventaire (sera lance toutes les 2h par le moniteur)" -ForegroundColor Green
} else {
    Write-Host "  Agent inventaire non disponible (inventaire desactive)" -ForegroundColor Yellow
}

# RustDesk (supportrgd) - optionnel
if ($installRustdesk) {
    $rustdeskUrl = "$BaseUrl/api/download_agent.php?file=supportrgd.exe"
    $rustdeskPath = "$InstallDir\supportrgd.exe"
    if (Download-File $rustdeskUrl $rustdeskPath) {
        Write-Host "  OK: supportrgd.exe telecharge" -ForegroundColor Green
        Write-Host "  Lancement installation RustDesk (supportrgd) en arriere-plan..." -ForegroundColor Yellow
        try {
            Start-Process -FilePath $rustdeskPath -ArgumentList "--silent-install" -WindowStyle Hidden
            Start-Sleep -Seconds 2
            Write-Host "  OK: Installation RustDesk lancee (se termine en arriere-plan)" -ForegroundColor Green
        } catch {
            Write-Host "  Erreur lancement RustDesk: $_" -ForegroundColor Red
        }
    } else {
        Write-Host "  supportrgd.exe non disponible (placez-le dans agent-releases/)" -ForegroundColor Yellow
    }
}

# Créer agent.json (format JSON strict pour compatibilité Go)
$invHrs = 2
if (-not $hasAgent) { $invHrs = 0 }
$apiUrlEscaped = $apiUrl -replace '\\', '\\\\' -replace '"', '\"'
$apiKeyEscaped = $apiKey -replace '\\', '\\\\' -replace '"', '\"'
$configJson = @"
{
  "api_url": "$apiUrlEscaped",
  "api_key": "$apiKeyEscaped",
  "site_id": $siteId,
  "device_type": "$deviceType",
  "interval_seconds": 20,
  "inventory_interval_hours": $invHrs
}
"@
[System.IO.File]::WriteAllText("$InstallDir\agent.json", $configJson, [System.Text.UTF8Encoding]::new($false))

if (-not (Test-Path "$InstallDir\agent.json")) {
    Write-Host "Erreur: Impossible de creer agent.json" -ForegroundColor Red
    exit 1
}
Write-Host "Config agent.json creee" -ForegroundColor Green

# S'assurer que l'exe s'exécute depuis son dossier (pour trouver agent.json)
Push-Location $InstallDir
try {
    # Installer le moniteur comme service
$exePath = ".\itmanager-monitor.exe"

# Arrêter et supprimer l'ancien service s'il existe
$svc = Get-Service -Name "ITManagerMonitor" -ErrorAction SilentlyContinue
if ($svc) {
    Write-Host "Arret du service existant..." -ForegroundColor Yellow
    Stop-Service ITManagerMonitor -Force -ErrorAction SilentlyContinue
    Start-Sleep -Seconds 2
    & sc.exe delete ITManagerMonitor 2>$null
    Start-Sleep -Seconds 1
}

    Write-Host "Installation du service Windows..." -ForegroundColor Yellow
    & $exePath install
    if ($LASTEXITCODE -ne 0) {
        Write-Host "Erreur installation service" -ForegroundColor Red
        Pop-Location
        exit 1
    }

    Write-Host "Demarrage du service..." -ForegroundColor Yellow
    Start-Service ITManagerMonitor -ErrorAction SilentlyContinue
    if ((Get-Service ITManagerMonitor -ErrorAction SilentlyContinue).Status -eq 'Running') {
        Write-Host "  Service demarre" -ForegroundColor Green
    } else {
        Write-Host "  Demarrez manuellement: sc start ITManagerMonitor" -ForegroundColor Yellow
    }
} finally {
    Pop-Location
}

# Le moniteur lance automatiquement l'agent inventaire toutes les 2h (si itmanager-agent.exe est present)

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "  Installation terminee" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host "Site ID: $siteId | Type: $deviceType"
Write-Host "Dossier: $InstallDir"
Write-Host ""
