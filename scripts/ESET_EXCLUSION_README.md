# Exclusion ESET pour IT Manager Agent

ESET bloque parfois `itmanager-agent.exe` et `itmanager-monitor.exe`. Voici comment automatiser l'ajout de l'exclusion.

## Méthode 1 : Script PowerShell (avec ecmd)

### Prérequis

1. **Activer ESET CMD** (une fois par machine) :
   - Ouvrir ESET → F5 (Paramètres avancés)
   - Outils → ESET CMD → Cocher « Activer les commandes ecmd avancées »

2. **Exécuter en administrateur** :
   ```powershell
   powershell -ExecutionPolicy Bypass -File add-eset-exclusion.ps1
   ```

Le script exporte la config, ajoute `C:\Program Files\ITManager-Agent\*` aux exclusions, puis réimporte.

> Si la structure XML a changé, le script peut échouer. Utilisez la méthode 2.

## Méthode 2 : Import d'un fichier XML (fiable)

1. **Sur une machine de référence** (avec ESET déjà configuré) :
   - Ajouter l'exclusion manuellement : F5 → Moteur de détection → Exclusions → Exclusions de performance → Ajouter `C:\Program Files\ITManager-Agent\*`
   - Setup → Importer/Exporter paramètres → Exporter → sauvegarder `itmanager-eset-exclusion.xml`

2. **Sur les autres machines** :
   ```powershell
   # Copier le XML sur la machine, puis :
   & "C:\Program Files\ESET\ESET Security\ecmd.exe" /setcfg C:\chemin\itmanager-eset-exclusion.xml
   ```
   (Adapter le chemin selon votre version ESET : ESET Internet Security, ESET Security, etc.)

## Méthode 3 : GPO / Déploiement

Si vous utilisez **ESET PROTECT** (ou ERA) en entreprise, créez une politique avec l'exclusion et déployez-la sur les postes.

## Vérification

Pour vérifier que l'exclusion est active : F5 → Moteur de détection → Exclusions → Exclusions de performance. Le chemin `C:\Program Files\ITManager-Agent\*` doit apparaître.
