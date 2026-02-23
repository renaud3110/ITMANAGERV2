#!/bin/bash
# Script de sauvegarde de la base de données IT Manager
# Génère database/backup.sql (schéma + données)

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
CONF_FILE="$SCRIPT_DIR/backup.conf"
BACKUP_FILE="$PROJECT_DIR/database/backup.sql"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Vérifier si la config existe
if [ ! -f "$CONF_FILE" ]; then
    echo "Erreur: Fichier $CONF_FILE introuvable."
    echo "Créez-le à partir de backup.conf.example :"
    echo "  cp $SCRIPT_DIR/backup.conf.example $CONF_FILE"
    echo "  Puis éditez backup.conf avec vos identifiants MySQL."
    exit 1
fi

# Charger la configuration
source "$CONF_FILE"

# Vérifier que mysqldump est disponible
if ! command -v mysqldump &> /dev/null; then
    echo "Erreur: mysqldump n'est pas installé."
    exit 1
fi

# Créer le dossier database s'il n'existe pas
mkdir -p "$(dirname "$BACKUP_FILE")"

# Option: backup avec horodatage (décommenter pour garder l'historique)
# BACKUP_FILE="$PROJECT_DIR/database/backup_${TIMESTAMP}.sql"

echo "Sauvegarde de la base $DB_NAME vers $BACKUP_FILE ..."

mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
    --single-transaction \
    --routines \
    --triggers \
    --events \
    --default-character-set=utf8mb4 \
    > "$BACKUP_FILE"

if [ $? -eq 0 ]; then
    echo "Sauvegarde terminée : $BACKUP_FILE"
    echo "Taille : $(du -h "$BACKUP_FILE" | cut -f1)"
else
    echo "Erreur lors de la sauvegarde."
    exit 1
fi
