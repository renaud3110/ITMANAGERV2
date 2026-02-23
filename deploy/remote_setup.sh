#!/bin/bash
# Script d'installation serveur IT Manager - Exécuté SUR LE SERVEUR
# Domain: it.rgdsystems.be

set -e
export DEBIAN_FRONTEND=noninteractive

echo "=========================================="
echo "  Installation IT Manager - it.rgdsystems.be"
echo "=========================================="

# Variables
DOCUMENT_ROOT="/var/www/itmanager"
DB_NAME="itmanager"
DB_USER="itmanager"
DB_PASS="ItManager_$(openssl rand -hex 8)"
SITE_DOMAIN="it.rgdsystems.be"

# 1. Mise à jour système
echo "[1/8] Mise à jour du système..."
apt-get update -qq
apt-get upgrade -y -qq

# 2. Installation MariaDB (compatible MySQL)
echo "[2/8] Installation de MariaDB..."
if ! command -v mysql &> /dev/null; then
    apt-get install -y mariadb-server
    systemctl start mariadb
    systemctl enable mariadb
fi

# 3. Création base et utilisateur MySQL
echo "[3/8] Configuration de la base de données..."
# MariaDB/MySQL : sudo mysql pour root sur Debian/Ubuntu
MYSQL_CMD="sudo mysql"
mysql -e "SELECT 1" 2>/dev/null && MYSQL_CMD="mysql" || true

$MYSQL_CMD -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
$MYSQL_CMD -e "DROP USER IF EXISTS '${DB_USER}'@'localhost';"
$MYSQL_CMD -e "CREATE USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
$MYSQL_CMD -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
$MYSQL_CMD -e "FLUSH PRIVILEGES;"

# 4. Import du schéma (les fichiers doivent être dans $DOCUMENT_ROOT)
if [ -f "${DOCUMENT_ROOT}/database/schema_complet.sql" ]; then
    echo "      Import du schéma..."
    $MYSQL_CMD ${DB_NAME} < "${DOCUMENT_ROOT}/database/schema_complet.sql"
    if [ -f "${DOCUMENT_ROOT}/database/seed_initial.sql" ]; then
        $MYSQL_CMD ${DB_NAME} < "${DOCUMENT_ROOT}/database/seed_initial.sql"
    fi
    echo "      Base créée avec succès."
else
    echo "      ATTENTION: Fichiers SQL non trouvés. Exécutez manuellement après le déploiement."
fi

# 5. Installation Apache et PHP
echo "[4/8] Installation d'Apache et PHP..."
apt-get install -y apache2 libapache2-mod-php php php-mysql php-json php-mbstring php-xml

# 6. Activation des modules Apache
echo "[5/8] Configuration d'Apache..."
a2enmod rewrite headers expires deflate

# 7. Configuration du VirtualHost
echo "[6/8] Création du VirtualHost..."
cat > /etc/apache2/sites-available/itmanager.conf << APACHECONF
<VirtualHost *:80>
    ServerName ${SITE_DOMAIN}
    ServerAlias www.${SITE_DOMAIN}
    DocumentRoot ${DOCUMENT_ROOT}
    DirectoryIndex index.php
    
    ErrorLog \${APACHE_LOG_DIR}/itmanager_error.log
    CustomLog \${APACHE_LOG_DIR}/itmanager_access.log combined
    LogLevel warn
    
    <Directory ${DOCUMENT_ROOT}>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        <IfModule mod_php.c>
            php_admin_value upload_max_filesize 10M
            php_admin_value post_max_size 10M
            php_admin_value memory_limit 256M
        </IfModule>
    </Directory>
    
    <Directory ${DOCUMENT_ROOT}/config>
        Require all denied
    </Directory>
</VirtualHost>
APACHECONF

a2ensite itmanager.conf
a2dissite 000-default.conf 2>/dev/null || true

# 8. Configuration Database.php
echo "[7/8] Configuration de l'application..."
if [ -f "${DOCUMENT_ROOT}/config/Database.php" ]; then
    sed -i "s/private \$host = '.*';/private \$host = 'localhost';/" "${DOCUMENT_ROOT}/config/Database.php"
    sed -i "s/private \$username = '.*';/private \$username = '${DB_USER}';/" "${DOCUMENT_ROOT}/config/Database.php"
    sed -i "s/private \$password = '.*';/private \$password = '${DB_PASS}';/" "${DOCUMENT_ROOT}/config/Database.php"
fi

# 9. Permissions
echo "[8/8] Configuration des permissions..."
mkdir -p ${DOCUMENT_ROOT}
chown -R www-data:www-data ${DOCUMENT_ROOT}
chmod -R 755 ${DOCUMENT_ROOT}
chmod 600 ${DOCUMENT_ROOT}/config/Database.php 2>/dev/null || true

# Redémarrer Apache
systemctl restart apache2

# Sauvegarder les identifiants DB
echo "${DB_USER}:${DB_PASS}" > /root/itmanager_db_credentials.txt
chmod 600 /root/itmanager_db_credentials.txt

echo ""
echo "=========================================="
echo "  Installation terminée !"
echo "=========================================="
echo ""
echo "  Application: https://${SITE_DOMAIN}"
echo "  Email admin: admin@itmanager.local"
echo "  Mot de passe: password"
echo ""
echo "  Identifiants MySQL sauvegardés dans: /root/itmanager_db_credentials.txt"
echo ""
