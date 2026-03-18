# Test script - executez ceci manuellement dans PowerShell sur le PC Windows
# pour verifier que la collecte startup fonctionne
$ErrorActionPreference='SilentlyContinue'
[Console]::OutputEncoding=[System.Text.Encoding]::UTF8
$r=@()
$paths=@(
    'HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Run',
    'HKLM:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Run',
    'HKCU:\SOFTWARE\Microsoft\Windows\CurrentVersion\Run',
    'HKCU:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Run'
)
foreach($k in $paths) {
    if(Test-Path $k) {
        $p=Get-ItemProperty -Path $k -EA 0
        if($p) {
            $p.PSObject.Properties | Where-Object {$_.Name -notmatch '^PS' -and $_.Name -ne '(default)'} | ForEach-Object {
                $r+=[PSCustomObject]@{Name=$_.Name;Command=[string]$_.Value;Location=$k}
            }
        }
    }
}
Write-Host "Nombre d'entrees trouvees: $($r.Count)"
$r | Format-Table -AutoSize
if($r.Count -eq 0){'[]'}else{$r|ConvertTo-Json}
