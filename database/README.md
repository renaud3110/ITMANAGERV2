# Réinstallation de la base de données IT Manager

Ce dossier contient les scripts pour recréer la base de données à partir du schéma dérivé des sources PHP.

## Prérequis

- MySQL 5.7+ ou MariaDB 10.2+
- Utilisateur MySQL avec droits CREATE et INSERT

## Étapes d'installation

### 1. Créer le schéma

```bash
mysql -u votre_utilisateur -p < database/schema_complet.sql
```

Sous Windows (PowerShell) :

```powershell
Get-Content database\schema_complet.sql | mysql -u votre_utilisateur -p
```

### 2. Insérer les données de démarrage (admin par défaut)

```bash
mysql -u votre_utilisateur -p < database/seed_initial.sql
```

### 3. Configurer la connexion PHP

Modifiez `config/Database.php` avec les identifiants de votre nouveau serveur :

- `$host` : adresse du serveur MySQL (ex. localhost)
- `$dbname` : `itmanager`
- `$username` : votre utilisateur MySQL
- `$password` : votre mot de passe MySQL

### 4. Connexion par défaut

- **Email** : `admin@itmanager.local`
- **Mot de passe** : `password`

**Important** : changez ce mot de passe dès la première connexion.

## Tables créées

| Table | Description |
|-------|-------------|
| tenants | Locataires / organisations |
| users | Utilisateurs de l'application |
| sites | Sites géographiques |
| persons | Personnes (employés, contacts) |
| manufacturers | Fabricants de matériel |
| models | Modèles de matériel |
| operating_systems | Systèmes d'exploitation |
| ip_addresses | Adresses IP |
| login_services | Services de connexion (comptes) |
| login_types | Types de comptes |
| logins | Comptes / identifiants stockés |
| servers | Serveurs |
| pcs_laptops | PC et laptops |
| physical_disks | Disques physiques |
| disk_partitions | Partitions de disques |
| cpu_temperatures | Températures CPU |
| software | Logiciels |
| installed_software | Logiciels installés par PC |
