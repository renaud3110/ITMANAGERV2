# IT Manager - Agent Moniteur

Service qui tourne en permanence et envoie les températures CPU/GPU ainsi que le statut en ligne vers IT Manager.

**Découverte NAS** : L'agent interroge régulièrement le serveur pour des jobs de découverte NAS. S'il en trouve pour son site, il se connecte au NAS (Synology) et envoie les partages/volumes au serveur. Les identifiants sont récupérés via l'API (chiffrés côté serveur).

## Configuration

1. Copiez `agent.json.example` vers `agent.json` (même fichier que l'agent d'inventaire si partagé)
2. Renseignez :
   - `api_url` : URL de l'API (ex: https://it.rgdsystems.be/api/inventory.php)
   - `api_key` : Clé API (identique à l'agent inventaire)
   - `site_id` : ID du site
   - `interval_seconds` : Intervalle d'envoi en secondes (défaut: 60)

## Dépendances

- **CPU** : WMI (Windows) / capteurs Linux
- **GPU** : `nvidia-smi` (NVIDIA) - doit être dans le PATH ou chemin standard

## Installation Windows (service)

```cmd
# Copier itmanager-monitor.exe et agent.json dans un dossier (ex: C:\Program Files\ITManager-Monitor\)

# Installer le service (en tant qu'administrateur)
itmanager-monitor.exe install

# Démarrer le service
sc start ITManagerMonitor
```

## Désinstallation

```cmd
sc stop ITManagerMonitor
itmanager-monitor.exe uninstall
```

## Mode console (test)

Lancez simplement `itmanager-monitor.exe` pour exécuter en mode console (sans service). Appuyez sur Ctrl+C pour arrêter.

## Linux

L'agent s'exécute en mode foreground. Utilisez systemd ou un autre gestionnaire de processus pour le faire tourner en permanence.
