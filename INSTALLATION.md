# Guide d'Installation Apache pour IT Manager

## Installation Automatique (Recommandée)

```bash
# Rendre le script exécutable
chmod +x install.sh

# Lancer l'installation
sudo ./install.sh
```

## Installation Manuelle

### 1. Prérequis

```bash
# Mettre à jour le système
sudo apt update && sudo apt upgrade -y

# Installer Apache2
sudo apt install apache2 -y

# Installer PHP et modules nécessaires
sudo apt install php libapache2-mod-php php-mysql php-json php-mbstring -y

# Installer MySQL/MariaDB si nécessaire
sudo apt install mariadb-server -y
```

### 2. Configuration Apache

#### Activer les modules nécessaires
```bash
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod expires
sudo a2enmod deflate
```

#### Copier la configuration du site
```bash
# Copier le fichier de configuration
sudo cp apache-config/itmanager.conf /etc/apache2/sites-available/

# Ou créer manuellement le fichier
sudo nano /etc/apache2/sites-available/itmanager.conf
```

#### Activer le site
```bash
# Activer le nouveau site
sudo a2ensite itmanager

# Désactiver le site par défaut (optionnel)
sudo a2dissite 000-default

# Vérifier la configuration
sudo apache2ctl configtest

# Redémarrer Apache
sudo systemctl reload apache2
```

### 3. Configuration des permissions

```bash
# Définir les bonnes permissions
sudo chown -R www-data:www-data /var/www/cmdb
sudo chmod -R 755 /var/www/cmdb

# Sécuriser les fichiers sensibles
sudo chmod 600 /var/www/cmdb/config/Database.php
sudo chmod 644 /var/www/cmdb/.htaccess
```

### 4. Configuration DNS/Hosts

#### Option A: Fichier hosts local (développement)
```bash
# Ajouter au fichier hosts
echo "127.0.0.1    itmanager.local" | sudo tee -a /etc/hosts
echo "127.0.0.1    www.itmanager.local" | sudo tee -a /etc/hosts
```

#### Option B: Domaine réel (production)
Modifiez le fichier `apache-config/itmanager.conf` :
```apache
ServerName votre-domaine.com
ServerAlias www.votre-domaine.com
```

### 5. Configuration SSL (Production)

#### Installer Certbot pour Let's Encrypt
```bash
sudo apt install certbot python3-certbot-apache -y

# Obtenir un certificat SSL
sudo certbot --apache -d votre-domaine.com -d www.votre-domaine.com
```

#### Configuration SSL manuelle
Décommentez la section HTTPS dans `itmanager.conf` et ajustez les chemins des certificats.

### 6. Configuration Base de Données

#### Accès MySQL/MariaDB
```bash
sudo mysql -u root -p
```

#### Créer l'utilisateur (si nécessaire)
```sql
CREATE USER 'renaud'@'localhost' IDENTIFIED BY 'Secure123$';
GRANT ALL PRIVILEGES ON itmanager.* TO 'renaud'@'localhost';
FLUSH PRIVILEGES;
```

### 7. Tests et Vérifications

#### Vérifier le statut d'Apache
```bash
sudo systemctl status apache2
```

#### Vérifier les logs
```bash
# Logs d'erreur
sudo tail -f /var/log/apache2/itmanager_error.log

# Logs d'accès
sudo tail -f /var/log/apache2/itmanager_access.log
```

#### Test de connectivité
```bash
# Test local
curl -I http://itmanager.local

# Test avec navigateur
# Ouvrir http://itmanager.local dans votre navigateur
```

## Configuration Avancée

### 1. Performance

#### Cache PHP OpCache
```bash
# Installer OpCache
sudo apt install php-opcache -y

# Éditer la configuration PHP
sudo nano /etc/php/8.1/apache2/php.ini
```

Ajouter/modifier :
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

#### Configuration MySQL pour performance
```sql
-- Dans MySQL
SET GLOBAL innodb_buffer_pool_size = 128M;
SET GLOBAL max_connections = 200;
```

### 2. Sécurité

#### Firewall
```bash
# Installer UFW
sudo apt install ufw -y

# Configurer le firewall
sudo ufw allow ssh
sudo ufw allow 'Apache Full'
sudo ufw enable
```

#### Mise à jour automatique
```bash
# Installer unattended-upgrades
sudo apt install unattended-upgrades -y
sudo dpkg-reconfigure unattended-upgrades
```

### 3. Monitoring

#### Installer htop et autres outils
```bash
sudo apt install htop iotop nethogs -y
```

#### Configuration des logs rotatifs
```bash
# Configurer logrotate pour les logs du site
sudo nano /etc/logrotate.d/itmanager
```

Contenu :
```
/var/log/apache2/itmanager_*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 640 root adm
    postrotate
        systemctl reload apache2
    endscript
}
```

## Dépannage

### Problèmes courants

1. **Erreur 500** : Vérifiez les logs d'erreur et les permissions
2. **Site non accessible** : Vérifiez la configuration DNS/hosts
3. **Erreurs PHP** : Vérifiez la configuration PHP et les modules
4. **Problèmes de base de données** : Vérifiez les credentials dans config/Database.php

### Commandes utiles

```bash
# Redémarrer Apache
sudo systemctl restart apache2

# Recharger la configuration Apache
sudo systemctl reload apache2

# Vérifier la configuration Apache
sudo apache2ctl configtest

# Voir les sites activés
sudo a2ensite

# Voir les modules activés
sudo a2enmod
```

## Support

Pour plus d'aide, consultez :
- Logs Apache : `/var/log/apache2/`
- Configuration PHP : `php -i | grep php.ini`
- Tests de configuration : `apache2ctl -S` 