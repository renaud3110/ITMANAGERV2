# Scripts utilitaires

## Sauvegarde de la base de données

Le script `backup_database.sh` exporte toute la base MySQL (schéma + données) dans `database/backup.sql`.

### Installation

```bash
cp scripts/backup.conf.example scripts/backup.conf
# Éditer scripts/backup.conf avec vos identifiants MySQL
```

### Utilisation

```bash
./scripts/backup_database.sh
```

### Inclusion dans Git

Pour pousser la sauvegarde sur GitHub :

```bash
./scripts/backup_database.sh
git add database/backup.sql
git commit -m "Mise à jour backup base de données"
git push
```
