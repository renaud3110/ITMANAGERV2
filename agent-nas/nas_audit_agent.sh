#!/bin/bash
# Agent NAS : collecte complète (smartctl -i/-H, mdadm, partages, volumes) et envoie vers IT Manager
# À installer sur le NAS (Synology ou autre Linux), configurer agent.conf puis cron
# IMPORTANT : exécuter en root (sudo -i ou sudo ./nas_audit_agent.sh)
# Usage : ./nas_audit_agent.sh   ou   ./nas_audit_agent.sh --dry-run (test sans envoi)

set -e
DRY_RUN=""
[ "$1" = "--dry-run" ] && DRY_RUN=1
echo "[Agent NAS] Démarrage..."
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
CONF="${SCRIPT_DIR}/agent.conf"

if [ ! -f "$CONF" ]; then
    echo "Fichier de configuration absent : $CONF" >&2
    echo "Copiez agent.conf.example vers agent.conf et renseignez API_URL, NAS_ID, API_KEY." >&2
    exit 1
fi
. "$CONF"

if [ -z "$API_URL" ] || [ -z "$NAS_ID" ] || [ -z "$API_KEY" ]; then
    echo "Configurez API_URL, NAS_ID et API_KEY dans $CONF" >&2
    exit 1
fi

collect_audit() {
    echo "===== INFORMATIONS SYSTEME ====="
    uname -a
    echo ""
    echo "===== PARTAGES ====="
    for vol in /volume*; do
        [ -d "$vol" ] || continue
        ls -1 "$vol" 2>/dev/null | grep -v '^@' | grep -v '^#' | grep -v '^$' | while read -r name; do
            [ -n "$name" ] && echo "$name|$vol/$name"
        done
    done
    echo ""
    echo "===== DISQUES DETECTES ====="
    for disk in /dev/sd?; do
        [ -b "$disk" ] || continue
        echo "---- $disk ----"
        smartctl -i "$disk" 2>/dev/null || echo "smartctl -i non disponible"
        echo "--- SMART HEALTH ---"
        smartctl -H "$disk" 2>/dev/null || echo "smartctl -H non disponible"
        echo ""
    done
    echo "===== RAID MDADM ====="
    for md in /dev/md*; do
        if [ -b "$md" ] 2>/dev/null && mdadm --detail "$md" &>/dev/null; then
            echo "---- $md ----"
            mdadm --detail "$md" | grep -E "Raid Level|Array Size|State|Active Devices"
            echo ""
        fi
    done
    echo "===== VOLUMES ====="
    df -h | grep -E "volume|Filesystem" || df -h
    echo ""
    echo "===== FIN ====="
}

echo "[Agent NAS] Collecte des infos (disques, RAID, volumes)..."
AUDIT_TEXT=$(collect_audit)

if [ -n "$DRY_RUN" ]; then
    echo "[Agent NAS] Mode test --dry-run : affichage (sans envoi)"
    echo "--- DEBUT AUDIT ---"
    echo "$AUDIT_TEXT"
    echo "--- FIN AUDIT ($(echo "$AUDIT_TEXT" | wc -c) octets) ---"
    exit 0
fi

echo "[Agent NAS] Envoi vers $API_URL ..."
URL="${API_URL%/}/api/nas_audit.php"
TMPFILE=$(mktemp)
trap "rm -f '$TMPFILE'" EXIT
printf '%s' "$AUDIT_TEXT" > "$TMPFILE"

if command -v curl >/dev/null 2>&1; then
    HTTP_CODE=$(curl -s -o /tmp/nas_audit_response.txt -w "%{http_code}" -X POST "$URL" \
        -H "X-Api-Key: $API_KEY" \
        -F "nas_id=$NAS_ID" \
        -F "audit_text=@$TMPFILE")
    if [ "$HTTP_CODE" = "200" ]; then
        echo "[Agent NAS] OK — Audit envoyé à IT Manager."
        cat /tmp/nas_audit_response.txt
    else
        echo "[Agent NAS] Erreur HTTP $HTTP_CODE"
        echo "[Agent NAS] Réponse du serveur :"
        cat /tmp/nas_audit_response.txt
        exit 1
    fi
    rm -f /tmp/nas_audit_response.txt
else
    echo "curl est requis." >&2
    exit 1
fi
