-- Vérifier si alt-bx-wks008 et alt-bx-wks010 existent dans pcs_laptops
-- Exécuter: mysql -u USER -p itmanager < scripts/check_pcs.sql

SELECT id, name, serial_number, site_id, tenant_id, created_at, updated_at
FROM pcs_laptops
WHERE name LIKE '%wks008%' OR name LIKE '%wks010%' OR name LIKE '%alt-bx%'
ORDER BY name;
