-- ============================================================
-- IT Manager System - Données de démarrage
-- À exécuter APRÈS schema_complet.sql
-- ============================================================

USE itmanager;

-- Admin global par défaut
-- Email: admin@itmanager.local | Mot de passe: password
-- IMPORTANT: Changez ce mot de passe après la première connexion !
INSERT INTO users (name, email, password, tenant_id, is_global_admin, created_at)
VALUES (
    'Administrateur',
    'admin@itmanager.local',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    NULL,
    1,
    NOW()
) ON DUPLICATE KEY UPDATE id=id;

-- Types de comptes (login_types)
INSERT IGNORE INTO login_types (id, name) VALUES
    (1, 'Standard'),
    (2, 'Administrateur'),
    (3, 'Technique');

-- Optionnel: un tenant exemple (commenté par défaut)
-- INSERT INTO tenants (name, domain, description) 
-- VALUES ('Mon Entreprise', 'monentreprise.local', 'Tenant par défaut');
