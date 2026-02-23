# Script de deploiement IT Manager vers it.rgdsystems.be
# Necessite: PuTTY (plink, pscp) ou OpenSSH

param(
    [string]$Server = "it.rgdsystems.be",
    [string]$User = "root",
    [string]$Password = "Hard2find$",
    [switch]$UsePlink
)

$ErrorActionPreference = "Stop"
$ProjectRoot = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
$RemotePath = "/var/www/itmanager"

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "  Deploiement IT Manager vers $Server" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# Fonction pour executer une commande SSH
function Invoke-SSH {
    param([string]$Command)
    if ($UsePlink -and (Get-Command plink -ErrorAction SilentlyContinue)) {
        $result = echo y | plink -ssh "${User}@${Server}" -pw $Password -batch $Command 2>&1
    } else {
        $result = ssh -o StrictHostKeyChecking=no -o BatchMode=yes "${User}@${Server}" $Command 2>&1
    }
    return $result
}

# Tester la connexion SSH
Write-Host "[1/4] Test de connexion SSH..." -ForegroundColor Yellow
try {
    if ($UsePlink -and (Get-Command plink -ErrorAction SilentlyContinue)) {
        echo y | plink -ssh "${User}@${Server}" -pw $Password -batch "echo OK" | Out-Null
    } else {
        ssh -o StrictHostKeyChecking=no -o ConnectTimeout=5 "${User}@${Server}" "echo OK" 2>$null | Out-Null
    }
    Write-Host "      Connexion OK" -ForegroundColor Green
} catch {
    Write-Host "      ERREUR: Impossible de se connecter. Verifiez:" -ForegroundColor Red
    Write-Host "        - Serveur accessible (ping $Server)" -ForegroundColor Red
    Write-Host "        - SSH actif sur le port 22" -ForegroundColor Red
    Write-Host "        - Identifiants corrects" -ForegroundColor Red
    Write-Host ""
    Write-Host "      Si vous utilisez PuTTY, lancez avec: .\deploy.ps1 -UsePlink" -ForegroundColor Yellow
    exit 1
}

# Creer le repertoire distant
Write-Host "[2/4] Preparation du serveur..." -ForegroundColor Yellow
if ($UsePlink -and (Get-Command plink -ErrorAction SilentlyContinue)) {
    echo y | plink -ssh "${User}@${Server}" -pw $Password -batch "mkdir -p $RemotePath"
} else {
    ssh -o StrictHostKeyChecking=no "${User}@${Server}" "mkdir -p $RemotePath"
}
Write-Host "      Repertoire $RemotePath cree" -ForegroundColor Green

# Copier les fichiers via archive tar
Write-Host "[3/4] Copie des fichiers de l'application..." -ForegroundColor Yellow
$archivePath = Join-Path $env:TEMP "itmanager_deploy_$(Get-Random).tar.gz"

try {
    Push-Location $ProjectRoot
    # Creer une archive en excluant .git et autres fichiers inutiles
    tar --exclude='.git' --exclude='node_modules' --exclude='*.log' --exclude='cookies.txt' --exclude='debug.php' -czvf $archivePath . 2>$null
    Pop-Location
    
    if ($UsePlink -and (Get-Command pscp -ErrorAction SilentlyContinue)) {
        pscp -pw $Password -batch $archivePath "${User}@${Server}:/tmp/itmanager.tar.gz"
    } else {
        scp -o StrictHostKeyChecking=no $archivePath "${User}@${Server}:/tmp/itmanager.tar.gz"
    }
    
    # Extraire et deplacer sur le serveur
    $extractCmd = "cd /tmp && rm -rf itmanager_extract && mkdir itmanager_extract && tar -xzf itmanager.tar.gz -C itmanager_extract && rm -rf $RemotePath/* && cp -r itmanager_extract/* $RemotePath/ && rm -rf itmanager_extract itmanager.tar.gz"
    if ($UsePlink -and (Get-Command plink -ErrorAction SilentlyContinue)) {
        echo y | plink -ssh "${User}@${Server}" -pw $Password -batch $extractCmd
    } else {
        ssh -o StrictHostKeyChecking=no "${User}@${Server}" $extractCmd
    }
} finally {
    Remove-Item $archivePath -Force -ErrorAction SilentlyContinue
}
Write-Host "      Fichiers copies" -ForegroundColor Green

# Executer le script d'installation distant
Write-Host "[4/4] Execution de l'installation sur le serveur..." -ForegroundColor Yellow
$RemoteScript = "$RemotePath/deploy/remote_setup.sh"
if ($UsePlink -and (Get-Command plink -ErrorAction SilentlyContinue)) {
    $output = echo y | plink -ssh "${User}@${Server}" -pw $Password -batch "chmod +x $RemoteScript && bash $RemoteScript"
} else {
    $output = ssh -o StrictHostKeyChecking=no "${User}@${Server}" "chmod +x $RemoteScript && bash $RemoteScript"
}
Write-Host $output
Write-Host ""
Write-Host "==========================================" -ForegroundColor Green
Write-Host "  Deploiement termine !" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Green
Write-Host ""
Write-Host "  Acces: https://$Server" -ForegroundColor Cyan
Write-Host "  Admin: admin@itmanager.local / password" -ForegroundColor Cyan
Write-Host ""
