#!/bin/bash

# Script d'installation pour IT Manager
# Debian/Ubuntu Apache Configuration

echo "================================================="
echo "   Installation du site IT Manager"
echo "================================================="

# Vérifier si on est root ou avec sudo
if [[ $EUID -ne 0 ]]; then
   echo "Ce script doit être exécuté en tant que root ou avec sudo"
   exit 1
fi

# Variables
SITE_NAME="itmanager"
SITE_DOMAIN="itmanager.local"
DOCUMENT_ROOT="/var/www/cmdb"
APACHE_SITES_DIR="/etc/apache2/sites-available"
APACHE_CONFIG_FILE="${APACHE_SITES_DIR}/${SITE_NAME}.conf"

echo "Configuration :"
echo "- Nom du site: ${SITE_NAME}"
echo "- Domaine: ${SITE_DOMAIN}"
echo "- Document Root: ${DOCUMENT_ROOT}"
echo ""

# 1. Vérifier si Apache est installé
if ! command -v apache2 &> /dev/null; then
    echo "Apache2 n'est pas installé. Installation..."
    apt update
    apt install -y apache2
fi

# 2. Vérifier si PHP est installé
if ! command -v php &> /dev/null; then
    echo "PHP n'est pas installé. Installation..."
    apt install -y php libapache2-mod-php php-mysql php-json php-mbstring
fi

# 3. Activer les modules Apache nécessaires
echo "Activation des modules Apache..."
a2enmod rewrite
a2enmod headers
a2enmod expires
a2enmod deflate

# 4. Copier la configuration du site
echo "Copie de la configuration Apache..."
cp apache-config/itmanager.conf ${APACHE_CONFIG_FILE}

# 5. Vérifier la configuration Apache
echo "Vérification de la configuration Apache..."
apache2ctl configtest

if [ $? -eq 0 ]; then
    echo "✅ Configuration Apache valide"
else
    echo "❌ Erreur dans la configuration Apache"
    exit 1
fi

# 6. Activer le site
echo "Activation du site..."
a2ensite ${SITE_NAME}

# 7. Désactiver le site par défaut (optionnel)
read -p "Voulez-vous désactiver le site Apache par défaut ? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    a2dissite 000-default
    echo "Site par défaut désactivé"
fi

# 8. Configurer les permissions
echo "Configuration des permissions..."
chown -R www-data:www-data ${DOCUMENT_ROOT}
chmod -R 755 ${DOCUMENT_ROOT}

# Permissions spéciales pour les fichiers sensibles
chmod 600 ${DOCUMENT_ROOT}/config/Database.php
chmod 644 ${DOCUMENT_ROOT}/.htaccess

# 9. Ajouter le domaine au fichier hosts si nécessaire
if ! grep -q "${SITE_DOMAIN}" /etc/hosts; then
    echo "Ajout du domaine au fichier /etc/hosts..."
    echo "127.0.0.1    ${SITE_DOMAIN}" >> /etc/hosts
    echo "127.0.0.1    www.${SITE_DOMAIN}" >> /etc/hosts
fi

# 10. Redémarrer Apache
echo "Redémarrage d'Apache..."
systemctl reload apache2

if [ $? -eq 0 ]; then
    echo "✅ Apache redémarré avec succès"
else
    echo "❌ Erreur lors du redémarrage d'Apache"
    systemctl status apache2
    exit 1
fi

# 11. Vérifier le statut
echo ""
echo "================================================="
echo "   Installation terminée !"
echo "================================================="
echo ""
echo "Votre site IT Manager est maintenant disponible à :"
echo "🌐 http://${SITE_DOMAIN}"
echo "🌐 http://www.${SITE_DOMAIN}"
echo ""
echo "Fichiers de logs :"
echo "📝 Erreurs: /var/log/apache2/itmanager_error.log"
echo "📝 Accès: /var/log/apache2/itmanager_access.log"
echo ""
echo "Pour vérifier le statut d'Apache :"
echo "   systemctl status apache2"
echo ""
echo "Pour voir les logs en temps réel :"
echo "   tail -f /var/log/apache2/itmanager_error.log"
echo ""

# 12. Test de connectivité
echo "Test de connectivité..."
if curl -s -o /dev/null -w "%{http_code}" http://${SITE_DOMAIN} | grep -q "200\|302"; then
    echo "✅ Site accessible"
else
    echo "⚠️  Site non accessible - Vérifiez la configuration"
fi

echo ""
echo "🎉 Installation terminée avec succès !" 