#!/bin/bash
# Script de deploiement IT Manager (WSL, Git Bash, Linux, Mac)
# Usage: ./deploy.sh

SERVER="it.rgdsystems.be"
USER="root"
PASS="Hard2find$"
REMOTE_PATH="/var/www/itmanager"
PROJECT_ROOT="$(cd "$(dirname "$0")/.." && pwd)"

echo "=========================================="
echo "  Deploiement IT Manager vers $SERVER"
echo "=========================================="
echo ""

# Verifier sshpass pour l'auth par mot de passe
if ! command -v sshpass &> /dev/null; then
    echo "Installation de sshpass necessaire:"
    echo "  Ubuntu/Debian: sudo apt install sshpass"
    echo "  Mac: brew install sshpass"
    echo "  Ou configurez l'authentification par cle SSH"
    exit 1
fi

# Test connexion
echo "[1/4] Test de connexion..."
sshpass -p "$PASS" ssh -o StrictHostKeyChecking=no "${USER}@${SERVER}" "echo OK" || { echo "Connexion echouee"; exit 1; }
echo "      OK"

# Creer archive et copier
echo "[2/4] Creation de l'archive et copie..."
ARCHIVE="/tmp/itmanager_deploy_$$.tar.gz"
cd "$PROJECT_ROOT"
tar --exclude='.git' --exclude='node_modules' --exclude='*.log' --exclude='cookies.txt' --exclude='debug.php' -czf "$ARCHIVE" .
sshpass -p "$PASS" scp -o StrictHostKeyChecking=no "$ARCHIVE" "${USER}@${SERVER}:/tmp/itmanager.tar.gz"
rm -f "$ARCHIVE"
echo "      OK"

# Extraire et executer setup
echo "[3/4] Extraction et installation..."
sshpass -p "$PASS" ssh -o StrictHostKeyChecking=no "${USER}@${SERVER}" << 'REMOTECMD'
mkdir -p /var/www/itmanager
cd /tmp
rm -rf itmanager_extract
mkdir itmanager_extract
tar -xzf itmanager.tar.gz -C itmanager_extract
rm -rf /var/www/itmanager/*
cp -r itmanager_extract/* /var/www/itmanager/
rm -rf itmanager_extract itmanager.tar.gz
chmod +x /var/www/itmanager/deploy/remote_setup.sh
bash /var/www/itmanager/deploy/remote_setup.sh
REMOTECMD

echo ""
echo "[4/4] Termine!"
echo ""
echo "  Acces: https://$SERVER"
echo "  Admin: admin@itmanager.local / password"
echo ""
