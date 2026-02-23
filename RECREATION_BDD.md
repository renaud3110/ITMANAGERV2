# Reconstitution de la base de données IT Manager

Ce document explique comment recréer la base de données à partir de zéro après une perte de données.

## Fichiers utilisés

- **`database_schema_recreate.sql`** : Script complet de création des tables, vues et données initiales
- **`database_modifications_ports.sql`** : Modifications optionnelles pour la gestion avancée des ports (procédures stockées)

## Procédure de recréation

### 1. Créer l'utilisateur MySQL (si nécessaire)

```bash
sudo mysql -u root -p
```

```sql
CREATE USER 'renaud'@'localhost' IDENTIFIED BY 'Secure123$';
GRANT ALL PRIVILEGES ON itmanager.* TO 'renaud'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 2. Exécuter le script principal

**Option A - Avec création de la base :**
```bash
mysql -u renaud -p < database_schema_recreate.sql
```

**Option B - Base déjà créée :**
```bash
mysql -u renaud -p itmanager < database_schema_recreate.sql
```

**Option C - Depuis Windows (PowerShell) :**
```powershell
Get-Content database_schema_recreate.sql | mysql -u renaud -p
```

### 3. Appliquer les modifications des ports (optionnel)

Si vous utilisez la gestion détaillée des ports réseau :

```bash
mysql -u renaud -p itmanager < database_modifications_ports.sql
```

### 4. Configurer l'application

Vérifiez que `config/Database.php` correspond à votre environnement :

```php
private $host = 'localhost';
private $dbname = 'itmanager';
private $username = 'renaud';
private $password = 'VotreMotDePasse';
```

### 5. Première connexion

**Compte administrateur par défaut :**
- **Email :** admin@itmanager.local
- **Mot de passe :** `password` (hash bcrypt fourni dans le script)

⚠️ **IMPORTANT :** Changez immédiatement le mot de passe après la première connexion !

Pour définir un nouveau mot de passe admin via PHP :
```php
<?php
require_once 'config/Database.php';
$password = password_hash('VotreNouveauMotDePasse', PASSWORD_DEFAULT);
// Puis exécuter : UPDATE users SET password = '$password' WHERE email = 'admin@itmanager.local';
```

### 6. Créer un tenant par défaut

Pour utiliser l'application, créez au moins un tenant :

```sql
INSERT INTO tenants (name, description) VALUES ('Mon entreprise', 'Tenant par défaut');
```

## Structure recréée

Le script crée les tables suivantes :

| Catégorie | Tables |
|-----------|--------|
| **Base** | tenants, sites, users, manufacturers, models, operating_systems, login_services |
| **Réseau** | ip_addresses, network_equipments, network_ports |
| **Personnes & comptes** | persons, logins |
| **Matériel** | servers, pcs_laptops, physical_disks, disk_partitions |
| **Logiciels** | software, installed_software, cpu_temperatures |
| **Métier** | domains, licenses |
| **Backup** | nakivo_backup_reports, nakivo_backup_jobs, nakivo_backup_vms, nakivo_target_storage |
| **Facturation** | factures, licences_facture |
| **Microsoft 365** | m365_subscribed_skus, m365_user_licenses |

## Données initiales incluses

- 1 utilisateur administrateur
- 6 services de connexion (Windows, Microsoft 365, Google, SSH, VPN, Autre)
- 7 systèmes d'exploitation courants

## Dépannage

- **Erreur "Database exists"** : Le script utilise `CREATE DATABASE IF NOT EXISTS`, pas de problème
- **Erreur de clé étrangère** : Vérifiez que le script s'exécute dans l'ordre (pas de modification manuelle)
- **Erreur de connexion PHP** : Vérifiez les identifiants dans `config/Database.php`
