# Agent NAS pour IT Manager

Script à installer sur le NAS (Synology ou autre Linux) pour envoyer automatiquement l'audit (disques, SMART, RAID, volumes) vers IT Manager.

## Prérequis sur le NAS

- **Bash** (présent sur Synology DSM)
- **curl** (souvent installé ; sur Synology : Centre de paquets ou `ipkg install curl`)
- **smartctl** (optionnel mais recommandé pour le SMART : paquet Synology ou `ipkg install smartmontools`)
- Accès SSH au NAS (ou exécution via tâche planifiée DSM)
- **Exécution en root** : smartctl et l'accès réseau nécessitent les droits root sur Synology. Utilisez `sudo -i` puis lancez le script, ou `sudo ./nas_audit_agent.sh`.

## Installation

1. Créez un répertoire sur le NAS, par exemple `/volume1/scripts/itmanager-agent` (ou chez votre utilisateur).

2. Copiez sur le NAS :
   - `nas_audit_agent.sh`
   - `agent.conf.example` (renommé en `agent.conf`)

3. Éditez `agent.conf` :
   - **API_URL** : URL de votre IT Manager (ex. `https://itmanager.votredomaine.be`)
   - **NAS_ID** : l'ID de la fiche NAS dans IT Manager (Matériel > NAS > modifier l'URL ou l'ID en base)
   - **API_KEY** : la clé définie dans `config/api_config.php` (`API_INVENTORY_KEY`)

4. Rendez le script exécutable :
   ```bash
   chmod +x nas_audit_agent.sh
   ```

5. Test à la main (en root) :
   ```bash
   sudo -i
   cd /volume1/rgd/agentnas   # ou votre chemin
   ./nas_audit_agent.sh
   ```
   Un message du type « [Agent NAS] OK — Audit envoyé à IT Manager » confirme que l'envoi a réussi. Les partages, volumes et disques apparaissent dans la fiche NAS (Détail) dans IT Manager.

## Planification (cron)

Pour envoyer l'audit régulièrement (ex. une fois par jour à 3 h), configurez une tâche en root :

```bash
sudo crontab -e
```

Ajoutez par exemple (remplacez le chemin par le vôtre) :

```
0 3 * * * /volume1/rgd/agentnas/nas_audit_agent.sh >> /volume1/rgd/agentnas/agent.log 2>&1
```

Sous Synology, vous pouvez aussi utiliser **Planificateur de tâches** (Paramètres système) : créez une tâche planifiée de type « Script utilisateur » et exécutez le script en tant qu’utilisateur root, par ex. `su - root -c "/volume1/rgd/agentnas/nas_audit_agent.sh"`.

## Sécurité

- Ne commitez pas `agent.conf` (il contient la clé API). Seul `agent.conf.example` est versionné.
- Utilisez HTTPS pour `API_URL` afin que la clé et l'audit transitent de façon sécurisée.

## Dépannage

- **« curl est requis »** : installez curl (Centre de paquets Synology ou `ipkg`).
- **Erreur 401** : clé API incorrecte ou absente dans `agent.conf`.
- **Erreur 404** : `NAS_ID` ne correspond à aucun NAS dans IT Manager.
- **SMART / btrfs non disponibles** : normal si les outils ne sont pas installés ou si le script n’est pas lancé en root ; l’audit est tout de même envoyé (volumes, df, mdstat).
- **Could not resolve host** : le NAS ne résout pas le nom de domaine ; utilisez l’IP du serveur dans `API_URL` ou configurez le DNS.
