//go:build windows

package main

import (
	"os/exec"
	"strings"
)

// getLoggedUser retourne l'utilisateur actuellement connecté (owner d'explorer.exe).
// Si vide ou SYSTEM = personne connectée.
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

func getSerialNumberOS() string {
	cmd := exec.Command("powershell", "-NoProfile", "-Command",
		"(Get-WmiObject Win32_BIOS).SerialNumber")
	out, err := cmd.Output()
	if err != nil {
		return ""
	}
	return strings.TrimSpace(string(out))
}
