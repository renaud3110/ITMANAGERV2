# Scripts utilitaires

## Découverte planifiée (ESXi)

Le script `cron_discovery.php` crée des jobs de découverte pour les hôtes ESXi configurés avec une fréquence automatique (toutes les heures, 2h, etc.).

**Installation crontab** (exécution toutes les heures à la minute 0) :

```bash
crontab -e
# Ajouter :
0 * * * * cd /var/www/itmanager && php scripts/cron_discovery.php >> /var/log/itmanager_discovery.log 2>&1
```

Les hôtes avec « Découverte automatique » > « Manuel uniquement » (interval = 0) ne génèrent pas de jobs. Par défaut, les nouveaux hôtes sont en « Toutes les heures ».

---

## Backup + Push (tout-en-un)

Le script `backup_and_push.sh` fait tout en une fois : backup SQL → commit → push GitHub.

### Installation (une seule fois)

```bash
cp scripts/backup.conf.example scripts/backup.conf
# Éditer scripts/backup.conf avec vos identifiants MySQL

cp scripts/github_token.example scripts/github_token
# Coller votre token GitHub (ghp_xxx) dans scripts/github_token
```

### Utilisation

```bash
./scripts/backup_and_push.sh
```

---

## Sauvegarde uniquement

Le script `backup_database.sh` exporte la base MySQL dans `database/backup.sql`.

```bash
./scripts/backup_database.sh
```
