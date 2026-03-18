# Add-ESET-Exclusion.ps1
# Ajoute l'exclusion ITManager-Agent dans ESET Internet Security
# Exécuter en tant qu'administrateur
#
# PRÉREQUIS : Dans ESET, activer "ESET CMD" :
#   F5 (Paramètres avancés) -> Outils -> ESET CMD -> Activer les commandes ecmd avancées

$ErrorActionPreference = "Stop"
$ExclusionPath = "C:\Program Files\ITManager-Agent\*"

# Trouver ecmd.exe (ESET Internet Security / Endpoint)
$ecmdPaths = @(
    "C:\Program Files\ESET\ESET Security\ecmd.exe",
    "C:\Program Files\ESET\ESET Internet Security\ecmd.exe",
    "C:\Program Files\ESET\ESET NOD32 Antivirus\ecmd.exe",
    "C:\Program Files\ESET\ESET Endpoint Antivirus\ecmd.exe",
    "C:\Program Files\ESET\ESET Endpoint Security\ecmd.exe",
    "C:\Program Files\ESET\ecmd.exe"
)

$ecmd = $null
foreach ($p in $ecmdPaths) {
    if (Test-Path $p) { $ecmd = $p; break }
}

if (-not $ecmd) {
    Write-Host "ERREUR: ecmd.exe introuvable. ESET est-il installe ?" -ForegroundColor Red
    Write-Host "Chemins verifies:" -ForegroundColor Yellow
    $ecmdPaths | ForEach-Object { Write-Host "  $_" }
    exit 1
}

Write-Host "ESET ecmd trouve: $ecmd" -ForegroundColor Green

$tempExport = [System.IO.Path]::GetTempFileName() + ".xml"
$tempDir = [System.IO.Path]::GetDirectoryName($tempExport)
if (-not (Test-Path $tempDir)) { New-Item -ItemType Directory -Path $tempDir -Force | Out-Null }

try {
    # 1. Exporter la config actuelle
    Write-Host "Export de la configuration ESET..." -ForegroundColor Yellow
    & $ecmd /getcfg $tempExport 2>&1 | Out-Null
    if (-not (Test-Path $tempExport)) {
        Write-Host "ERREUR: Export echoue. Verifiez que ESET CMD est active (F5 -> Outils -> ESET CMD)" -ForegroundColor Red
        exit 1
    }

    # 2. Charger et modifier le XML
    [xml]$xml = Get-Content $tempExport -Encoding UTF8

    # Chercher le noeud des exclusions (structure variable selon version ESET)
    $listNode = $null
    $nodes = $xml.SelectNodes("//*")
    foreach ($n in $nodes) {
        $name = $n.LocalName
        $parentName = if ($n.ParentNode) { $n.ParentNode.LocalName } else { "" }
        $grandParent = if ($n.ParentNode -and $n.ParentNode.ParentNode) { $n.ParentNode.ParentNode.LocalName } else { "" }
        if (($name -eq "list" -or $name -eq "items") -and ($parentName -match "performance|exclusion" -or $grandParent -match "exclusion")) {
            $listNode = $n
            break
        }
    }

    if (-not $listNode) {
        # Essayer sans namespace
        $listNode = $xml.SelectSingleNode("//list") ?? $xml.SelectSingleNode("//*[local-name()='list']")
    }

    if ($listNode) {
        # Verifier si l'exclusion existe deja
        $exists = $false
        foreach ($child in $listNode.ChildNodes) {
            $val = if ($child.InnerText) { $child.InnerText.Trim() } else { $child.'#text' }
            if ($val -eq $ExclusionPath) { $exists = $true; break }
        }
        if (-not $exists) {
            $item = $xml.CreateElement("item")
            $item.InnerText = $ExclusionPath
            [void]$listNode.AppendChild($item)
            Write-Host "Exclusion ajoutee au XML: $ExclusionPath" -ForegroundColor Green
        } else {
            Write-Host "Exclusion deja presente." -ForegroundColor Gray
        }
    } else {
        Write-Host "ATTENTION: Structure XML non reconnue. Utilisez la methode manuelle ci-dessous." -ForegroundColor Yellow
        Write-Host "Fichier exporte: $tempExport" -ForegroundColor Gray
        Write-Host ""
        Write-Host "METHODE MANUELLE:" -ForegroundColor Cyan
        Write-Host "1. Dans ESET: Parametres avances (F5) -> Moteur de detection -> Exclusions -> Exclusions de performance"
        Write-Host "2. Ajouter: $ExclusionPath"
        Write-Host "3. Setup -> Importer/Exporter parametres -> Exporter"
        Write-Host "4. Sur les autres PC: Importer ce fichier XML"
        exit 0
    }

    # 3. Importer la config modifiee
    Write-Host "Import de la configuration..." -ForegroundColor Yellow
    $xml.Save($tempExport)
    & $ecmd /setcfg $tempExport 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "OK: Exclusion ESET configuree." -ForegroundColor Green
    } else {
        Write-Host "Import peut avoir echoue. Verifiez manuellement dans ESET." -ForegroundColor Yellow
    }
} finally {
    if (Test-Path $tempExport) { Remove-Item $tempExport -Force -ErrorAction SilentlyContinue }
}
