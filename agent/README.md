# IT Manager Agent - Inventaire Windows

Agent léger en Go qui collecte les informations matérielles et logicielles d'un poste Windows et les envoie à IT Manager.

## Données collectées

- **Système** : hostname, numéro de série BIOS, OS
- **Matériel** : processeur, RAM (total/usage)
- **Disques** : disques physiques, partitions, espace libre
- **Logiciels** : programmes installés (registre Windows)

## Compilation

### Pour Windows (depuis Linux/Mac)

```bash
GOOS=windows GOARCH=amd64 go build -o itmanager-agent.exe .
```

### Pour Windows 32 bits

```bash
GOOS=windows GOARCH=386 go build -o itmanager-agent-32.exe .
```

## Configuration

Copiez `agent.json.example` vers `agent.json` à côté de l'exécutable :

```json
{
  "api_url": "https://it.rgdsystems.be/api/inventory.php",
  "api_key": "votre-clé-api",
  "site_id": 1,
  "tenant_id": 1
}
```

| Champ | Description |
|-------|-------------|
| `api_key` | Clé API (obligatoire) |
| `api_url` | URL de l'API |
| `site_id` | ID du site IT Manager (le tenant est dérivé automatiquement) |
| `device_type` | `pc` (défaut) ou `server` — envoie l'inventaire vers la table PCs ou Serveurs |

L'agent cherche `agent.json` dans :
1. Le même dossier que l'exe
2. Le répertoire courant

### Variables d'environnement (alternative)

`ITMANAGER_API_KEY`, `ITMANAGER_API_URL`, `ITMANAGER_SITE_ID`, `ITMANAGER_TENANT_ID`

### Exemple

Placez `agent.json` et `itmanager-agent.exe` dans le même dossier, puis double-cliquez ou :

```cmd
itmanager-agent.exe
```

### Tâche planifiée

1. Ouvrir **Planificateur de tâches** (taskschd.msc)
2. Créer une tâche → Déclencheur : quotidien
3. Action : Démarrer un programme → `C:\chemin\inventory\itmanager-agent.exe`
4. Paramètres : définir `ITMANAGER_API_KEY` dans les variables d'environnement de la tâche

## Configuration côté serveur

1. Créer `config/api_config.php` à partir de `config/api_config.example.php`
2. Définir `API_INVENTORY_KEY` (doit correspondre à `ITMANAGER_API_KEY` de l'agent)
3. L'API est accessible à : `https://it.rgdsystems.be/api/inventory.php`
