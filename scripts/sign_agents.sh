#!/bin/bash
# Signe les agents IT Manager avec osslsigncode (Linux)
#
# PRÉREQUIS:
#   apt install osslsigncode
#
# UTILISATION:
#   export SIGNING_PFX=/chemin/vers/certificat.pfx
#   export SIGNING_PASSWORD="mot_de_passe"
#   ./scripts/sign_agents.sh
#
# Ou en une ligne:
#   SIGNING_PFX=/chemin/cert.pfx SIGNING_PASSWORD="secret" ./scripts/sign_agents.sh

set -e
cd "$(dirname "$0")/.."

if ! command -v osslsigncode &>/dev/null; then
    echo "ERREUR: osslsigncode non installé. Exécutez: apt install osslsigncode"
    exit 1
fi

if [ -z "$SIGNING_PFX" ] || [ ! -f "$SIGNING_PFX" ]; then
    echo "ERREUR: Certificat PFX manquant."
    echo "Définissez SIGNING_PFX=/chemin/vers/certificat.pfx"
    exit 1
fi

if [ -z "$SIGNING_PASSWORD" ]; then
    echo "ERREUR: Mot de passe PFX manquant."
    echo "Définissez SIGNING_PASSWORD=\"votre_mot_de_passe\""
    exit 1
fi

TIMESTAMP_URL="${SIGNING_TIMESTAMP_URL:-http://timestamp.digicert.com}"

sign_exe() {
    local src="$1"
    local tmp="${src}.signed"
    if [ ! -f "$src" ]; then
        echo "  Ignoré (absent): $src"
        return
    fi
    echo "  Signature de $src..."
    osslsigncode sign \
        -pkcs12 "$SIGNING_PFX" \
        -pass "$SIGNING_PASSWORD" \
        -t "$TIMESTAMP_URL" \
        -in "$src" \
        -out "$tmp" && mv "$tmp" "$src" && echo "  ✓ $src"
}

echo "Signature des agents IT Manager..."
echo ""

sign_exe "agent-monitor/itmanager-monitor.exe"
sign_exe "agent-monitor/itmanager-monitor-32.exe"
sign_exe "agent/itmanager-agent.exe"

echo ""
echo "Terminé. Exécutez deploy_agents.sh pour copier vers agent-releases/"
