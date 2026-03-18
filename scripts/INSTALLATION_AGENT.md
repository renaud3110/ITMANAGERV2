# Installation des agents IT Manager

## Résumé

- **Un seul service Windows** (`ITManagerMonitor`) qui gère :
  - Télémétrie (températures, statut en ligne) : toutes les 20 secondes
  - Découverte ESXi/Proxmox : toutes les 40 secondes
  - Découverte NAS : toutes les 40 secondes  
  - Inventaire (PC/serveur) : toutes les 2 heures (via itmanager-agent.exe)

## Installation rapide

1. **Sur le serveur** : après compilation, exécuter :
   ```bash
   cd /var/www/itmanager
   ./build.sh   # dans agent/ et agent-monitor/
   bash scripts/deploy_agents.sh
   ```

2. **Sur la machine Windows** : 
   - Télécharger `install-itmanager.ps1` et `install-itmanager.cmd`
   - Double-cliquer sur `install-itmanager.cmd` (en tant qu'administrateur)
   - Répondre aux questions : Site ID, PC ou Serveur, clé API

## Questions posées par l'installeur

| Question | Défaut | Description |
|---------|--------|-------------|
| ID du site | - | Obligatoire (1, 2, 3...) |
| Type | PC | 1=PC, 2=Serveur |
| Clé API | itmanager-agent-2024-secure-key-change-me | Doit correspondre à `API_INVENTORY_KEY` |
| URL API | https://it.rgdsystems.be/api/inventory.php | URL de base |

## Fichiers téléchargés

L'installeur récupère depuis `https://it.rgdsystems.be/api/download_agent.php` :
- `itmanager-monitor.exe` (obligatoire)
- `itmanager-agent.exe` (optionnel, pour l'inventaire)
- `supportrgd.exe` (optionnel, si « Installer RustDesk » = o)

### Où placer supportrgd.exe (RustDesk personnalisé)

Pour que l'installeur puisse télécharger et installer supportrgd en même temps que IT Manager :

1. Copiez `supportrgd.exe` dans `supportrgd/supportrgd.exe` à la racine du projet, **ou**
2. Placez-le directement dans `agent-releases/`
3. Exécutez `bash scripts/deploy_agents.sh` (copie vers agent-releases si dans supportrgd/)

L'installateur exécute : `supportrgd.exe --silent-install`

## Mise à jour automatique — Avis

**Recommandation : pas de mise à jour automatique par défaut.**

- **Risques** : une mise à jour automatique peut introduire un bug et casser toutes les installations.
- **Alternative** : vérification au démarrage avec notification :
  - L'agent interroge une URL du type `/api/agent_version.php`
  - Si une version plus récente existe, log ou notification à l'utilisateur
  - L'utilisateur décide manuellement de mettre à jour
- **Script de mise à jour** : fournir `update-itmanager.ps1` que l'admin lance quand il le souhaite

Pour un déploiement critique, garder le contrôle manuel des mises à jour.
