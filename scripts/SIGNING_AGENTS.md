# Signature des agents IT Manager

Signer les exécutables (`itmanager-agent.exe`, `itmanager-monitor.exe`) permet à Windows et aux antivirus (ESET, etc.) de les reconnaître comme fiables.

## 1. Obtenir un certificat de signature de code

### Où acheter ?

| Fournisseur | OV | EV |
|-------------|-----|-----|
| Certum | ~50 €/an | ~300 €/an |
| DigiCert | ~300 €/an | ~400 €/an |
| Sectigo | ~80 €/an | ~300 €/an |
| SSL.com | ~75 €/an | ~300 €/an |

- **OV** : validation de l’entreprise, délai 2–5 jours. SmartScreen peut encore avertir au début.
- **EV** : validation renforcée, smartcard obligatoire. Réputation immédiate chez Microsoft.

### Format du certificat

Le certificat doit être exportable en **PFX** (PKCS#12) avec mot de passe. Pour un certificat EV sur smartcard, l’outil de signature doit être exécuté sur Windows.

## 2. Signer depuis Linux (osslsigncode)

### Installation

```bash
# Debian/Ubuntu
sudo apt install osslsigncode

# Ou depuis les sources : https://github.com/mtrojnar/osslsigncode
```

### Commande de base

```bash
osslsigncode sign \
  -pkcs12 /chemin/vers/certificat.pfx \
  -pass "mot_de_passe_pfx" \
  -in itmanager-monitor.exe \
  -out itmanager-monitor-signed.exe
```

### Avec horodatage (recommandé)

L’horodatage permet à la signature de rester valide après expiration du certificat.

```bash
osslsigncode sign \
  -pkcs12 /chemin/certificat.pfx \
  -pass "mot_de_passe" \
  -t http://timestamp.digicert.com \
  -in itmanager-monitor.exe \
  -out itmanager-monitor-signed.exe
```

### Vérifier la signature

```bash
osslsigncode verify -in itmanager-monitor-signed.exe
```

## 3. Signer depuis Windows (SignTool)

SignTool est fourni avec le SDK Windows.

### Emplacement

```
C:\Program Files (x86)\Windows Kits\10\bin\<version>\x64\signtool.exe
```

### Commande de base

```cmd
signtool sign /f C:\chemin\certificat.pfx /p mot_de_passe /t http://timestamp.digicert.com itmanager-monitor.exe
```

### Avec certificat dans le magasin Windows

```cmd
signtool sign /n "Nom de votre entreprise" /t http://timestamp.digicert.com itmanager-monitor.exe
```

## 4. Intégration dans le build

### Option A : Script manuel après compilation

```bash
cd /var/www/itmanager
./agent-monitor/build.sh
./agent/build.sh
./scripts/sign_agents.sh  # si le script existe et que le certificat est configuré
```

### Option B : Fichiers à signer

- `agent-monitor/itmanager-monitor.exe`
- `agent-monitor/itmanager-monitor-32.exe`
- `agent/itmanager-agent.exe`

Après signature, les exécutables signés remplacent les originaux ou sont copiés dans `agent-releases/`.

## 5. Sécurité

- Ne jamais commiter le fichier `.pfx` ni le mot de passe dans le dépôt.
- Stocker le certificat dans un coffre-fort (ex. HashiCorp Vault, Azure Key Vault) ou sur une machine dédiée.
- Pour un certificat EV, la smartcard reste sur une machine sécurisée ; la signature se fait uniquement depuis cette machine.

## 6. Serveurs d’horodatage

| Fournisseur | URL |
|-------------|-----|
| DigiCert | http://timestamp.digicert.com |
| Sectigo | http://timestamp.sectigo.com |
| GlobalSign | http://timestamp.globalsign.com |
