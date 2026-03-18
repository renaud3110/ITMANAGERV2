#!/bin/bash
# Script à exécuter sur une machine avec RustDesk client installé
# Affiche l'ID et permet de définir/récupérer le mot de passe
# Usage: sudo ./get_rustdesk_id.sh [mot_de_passe_optionnel]

echo "=== Récupération des informations RustDesk ==="

if ! command -v rustdesk &>/dev/null; then
    echo "ERREUR: RustDesk n'est pas installé sur cette machine."
    exit 1
fi

# Récupérer l'ID
RUSTDESK_ID=$(rustdesk --get-id 2>/dev/null | tr -d '[:space:]')

if [ -z "$RUSTDESK_ID" ]; then
    echo "ERREUR: Impossible de récupérer l'ID RustDesk."
    exit 1
fi

echo "RustDesk ID: $RUSTDESK_ID"

# Si un mot de passe est fourni en argument, le définir
if [ -n "$1" ]; then
    rustdesk --password "$1" &>/dev/null
    echo "RustDesk mot de passe: $1"
    echo "(Mot de passe défini avec succès)"
else
    echo ""
    echo "Pour définir un mot de passe permanent, exécutez:"
    echo "  sudo rustdesk --password VOTRE_MOT_DE_PASSE"
    echo ""
    echo "Puis ajoutez ces informations dans IT Manager."
fi

echo "..............................................."
