-- Date de création du compte (utilisateur connecté ou dernier connecté)
-- Récupérée via Win32_UserProfile.InstallDate par l'agent d'inventaire

ALTER TABLE pcs_laptops ADD COLUMN last_account_created_at DATETIME NULL AFTER last_account;
