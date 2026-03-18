-- Migration: Ajouter disks_json pour la découverte NAS
-- mysql -u renaud -p itmanager < database/migration_discovery_disks.sql

USE itmanager;

-- nas_discovery
ALTER TABLE nas_discovery ADD COLUMN disks_json TEXT NULL AFTER volumes_json;

-- discovery_jobs (si la table existe avec cette structure)
-- ALTER TABLE discovery_jobs ADD COLUMN disks_json TEXT NULL AFTER volumes_json;
