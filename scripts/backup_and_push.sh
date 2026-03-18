#!/bin/bash
# Script tout-en-un : Backup SQL + Commit + Push GitHub

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
BACKUP_FILE="$PROJECT_DIR/database/backup.sql"
REMOTE_URL="https://github.com/renaud3110/ITMANAGERV2.git"

cd "$PROJECT_DIR" || exit 1

echo "=== 1. Sauvegarde de la base de données ==="
"$SCRIPT_DIR/backup_database.sh" || exit 1

echo ""
echo "=== 2. Mise à jour Git ==="

git add database/backup.sql

# Vérifier s'il y a des changements à committer
if git diff --cached --quiet; then
    echo "Aucun changement dans backup.sql - rien à committer."
    exit 0
fi

git commit -m "Mise à jour backup base de données ($(date +%Y-%m-%d))"

echo ""
echo "=== 3. Push vers GitHub ==="

# Utiliser le token si le fichier existe
if [ -f "$SCRIPT_DIR/github_token" ]; then
    TOKEN=$(cat "$SCRIPT_DIR/github_token" | tr -d '[:space:]')
    git push "https://renaud3110:${TOKEN}@github.com/renaud3110/ITMANAGERV2.git" main
else
    git push origin main
fi

if [ $? -eq 0 ]; then
    echo ""
    echo "✓ Backup et push terminés avec succès."
else
    echo ""
    echo "Erreur lors du push."
    echo "Astuce : créez scripts/github_token avec votre token GitHub pour l'authentification automatique."
    exit 1
fi
