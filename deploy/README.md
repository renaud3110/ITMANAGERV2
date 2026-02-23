# Déploiement IT Manager

## Option 1 : WSL ou Git Bash (recommandé)

```bash
cd deploy
chmod +x deploy.sh
./deploy.sh
```

**Prérequis** : `sshpass` (sudo apt install sshpass sur Ubuntu/WSL)

## Option 2 : PowerShell avec PuTTY

Installez PuTTY et ajoutez son répertoire au PATH :

```powershell
# Via winget (Windows 10+)
winget install PuTTY.PuTTY
# Puis ajoutez C:\Program Files\PuTTY au PATH

cd deploy
.\deploy.ps1 -UsePlink
```

## Option 3 : Déploiement manuel

1. **Installer PuTTY** ou utiliser **sshpass** (WSL/Git Bash)
2. **Connexion SSH** : `ssh root@it.rgdsystems.be`
3. **Sur le serveur** :
```bash
apt update && apt install -y mariadb-server apache2 php php-mysql php-json php-mbstring
mkdir -p /var/www/itmanager
```

4. **Depuis votre PC** : copiez les fichiers (config, controllers, models, views, assets, database, index.php, .htaccess) vers `/var/www/itmanager/`

5. **Sur le serveur** :
```bash
mysql < /var/www/itmanager/database/schema_complet.sql
mysql itmanager < /var/www/itmanager/database/seed_initial.sql
# Puis adapter config/Database.php et configurer Apache
```

## Après le déploiement

- **URL** : https://it.rgdsystems.be
- **Admin** : admin@itmanager.local
- **Mot de passe** : password
- **Identifiants MySQL** : `/root/itmanager_db_credentials.txt` sur le serveur (si déploiement auto)
