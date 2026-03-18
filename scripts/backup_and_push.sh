#!/bin/bash
# Script tout-en-un : Backup SQL + Commit TOUT + Push GitHub
#
# UTILISATION:
#   ./scripts/backup_and_push.sh                    # Commit auto avec date
#   ./scripts/backup_and_push.sh "Mon message"      # Commit avec message personnalisé
#   ./scripts/backup_and_push.sh --no-backup        # Sans backup SQL
#   ./scripts/backup_and_push.sh --no-backup "Msg"  # Sans backup + message

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
REMOTE_URL="https://github.com/renaud3110/ITMANAGERV2.git"

cd "$PROJECT_DIR" || exit 1

# Gestion des arguments
DO_BACKUP=true
COMMIT_MSG=""

for arg in "$@"; do
    if [ "$arg" == "--no-backup" ]; then
        DO_BACKUP=false
    elif [ -z "$COMMIT_MSG" ] && [ "$arg" != "--no-backup" ]; then
        COMMIT_MSG="$arg"
    fi
done

# Message par défaut si non fourni
if [ -z "$COMMIT_MSG" ]; then
    COMMIT_MSG="Mise à jour $(date +%Y-%m-%d\ %H:%M)"
fi

echo "╔══════════════════════════════════════════════════════════════╗"
echo "║           IT Manager - Synchronisation GitHub                ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo ""

# 1. Backup SQL (optionnel)
if [ "$DO_BACKUP" = true ] && [ -f "$SCRIPT_DIR/backup_database.sh" ]; then
    echo "=== 1. Sauvegarde de la base de données ==="
    "$SCRIPT_DIR/backup_database.sh" || echo "⚠ Backup échoué, on continue..."
    echo ""
fi

# 2. Ajouter TOUS les fichiers
echo "=== 2. Ajout des fichiers modifiés ==="
git add -A

# Afficher un résumé des changements
CHANGES=$(git diff --cached --stat | tail -1)
if [ -z "$CHANGES" ]; then
    echo "✓ Aucun changement à committer."
    exit 0
fi

echo "Changements détectés :"
git diff --cached --stat | head -20
echo ""

# 3. Commit
echo "=== 3. Commit ==="
echo "Message : $COMMIT_MSG"
git commit -m "$COMMIT_MSG"

if [ $? -ne 0 ]; then
    echo "✗ Erreur lors du commit."
    exit 1
fi
echo ""

# 4. Push vers GitHub
echo "=== 4. Push vers GitHub ==="

if [ -f "$SCRIPT_DIR/github_token" ]; then
    TOKEN=$(cat "$SCRIPT_DIR/github_token" | tr -d '[:space:]')
    git push "https://renaud3110:${TOKEN}@github.com/renaud3110/ITMANAGERV2.git" main
else
    git push origin main
fi

if [ $? -eq 0 ]; then
    echo ""
    echo "╔══════════════════════════════════════════════════════════════╗"
    echo "║  ✓ Synchronisation terminée avec succès !                    ║"
    echo "╚══════════════════════════════════════════════════════════════╝"
else
    echo ""
    echo "✗ Erreur lors du push."
    echo ""
    echo "Solutions possibles :"
    echo "  1. Créez scripts/github_token avec votre token GitHub"
    echo "  2. Vérifiez votre connexion internet"
    echo "  3. Faites d'abord : git pull origin main"
    exit 1
fi
