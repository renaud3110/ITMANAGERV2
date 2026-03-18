# IT Manager - Système de Gestion IT

## Description
Système de gestion IT développé en PHP avec architecture MVC pour la gestion des tenants, sites et utilisateurs.

## Fonctionnalités
- 🏢 **Gestion des Tenants** : Administration des différents clients/organisations
- 📍 **Gestion des Sites** : Gestion des sites par tenant
- 👥 **Gestion des Utilisateurs** : Administration des utilisateurs avec niveaux d'accès
- 🔐 **Authentification** : Système de connexion sécurisé
- 📊 **Tableau de bord** : Vue d'ensemble avec statistiques et contexte
- 🎯 **Contexte dynamique** : Sélection tenant/site avec filtrage automatique

## Architecture
- **MVC** : Architecture Model-View-Controller
- **PHP 8.2+** : Backend
- **MySQL** : Base de données
- **Apache** : Serveur web
- **Responsive Design** : Interface moderne et adaptative

## Installation

### Prérequis
- Apache 2.4+
- PHP 8.2+
- MySQL 5.7+

### Configuration Apache
```apache
<VirtualHost *:80>
    ServerName it.rgdsystems.be
    DocumentRoot /var/www/cmdb
    DirectoryIndex index.php
    
    <Directory /var/www/cmdb>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Base de données
La base de données `itmanager` doit contenir les tables :
- `users` : Utilisateurs du système
- `tenants` : Organisations/Clients
- `sites` : Sites par tenant

### Configuration
1. Copier le fichier de configuration : `cp config/Database.php.example config/Database.php`
2. Renseigner les paramètres MySQL dans `config/Database.php`
3. Importer le schéma : `mysql -u user -p < database_schema_recreate.sql`
4. Importer les procédures : `mysql -u user -p < database_modifications_ports_procedures_only.sql`
5. Créer un utilisateur admin via l'interface (ou utiliser admin@itmanager.local / password par défaut)
6. Configurer les permissions Apache

## Utilisation
1. Accéder à l'interface via http://it.rgdsystems.be
2. Se connecter avec les identifiants admin
3. Sélectionner un tenant/site dans le header
4. Naviguer via le menu latéral

## Développement
```bash
# Cloner le projet
git clone [URL_DU_REPO]

# Configurer les permissions
sudo chown -R www-data:www-data /var/www/cmdb
sudo chmod -R 755 /var/www/cmdb
```

## Auteur
Développé par **Renaud GERARD** - RGD Systems

## Licence
Propriétaire - RGD Systems 