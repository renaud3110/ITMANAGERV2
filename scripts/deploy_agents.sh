#!/bin/bash
# Copie les agents compilés vers agent-releases/ pour le téléchargement
# À exécuter après ./build.sh dans agent/ et agent-monitor/

cd "$(dirname "$0")/.."
mkdir -p agent-releases

echo "Déploiement des agents vers agent-releases/"

if [ -f agent-monitor/itmanager-monitor.exe ]; then
    cp agent-monitor/itmanager-monitor.exe agent-releases/
    echo "  ✓ itmanager-monitor.exe"
fi
if [ -f agent-monitor/itmanager-monitor-32.exe ]; then
    cp agent-monitor/itmanager-monitor-32.exe agent-releases/
    echo "  ✓ itmanager-monitor-32.exe"
fi
if [ -f agent-monitor/itmanager-unified.exe ]; then
    cp agent-monitor/itmanager-unified.exe agent-releases/
    echo "  ✓ itmanager-unified.exe"
fi
if [ -f agent/itmanager-agent.exe ]; then
    cp agent/itmanager-agent.exe agent-releases/
    echo "  ✓ itmanager-agent.exe"
fi
# supportrgd.exe (RustDesk personnalisé) : placer le binaire ici ou dans supportrgd/
if [ -f supportrgd/supportrgd.exe ]; then
    cp supportrgd/supportrgd.exe agent-releases/
    echo "  ✓ supportrgd.exe"
elif [ -f agent-releases/supportrgd.exe ]; then
    echo "  ✓ supportrgd.exe (déjà présent)"
fi

echo "Téléchargement: https://it.rgdsystems.be/api/download_agent.php?file=itmanager-monitor.exe"
echo "Done."
