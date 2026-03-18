-- Migration: raid_json pour arrays RAID mdadm
ALTER TABLE nas_discovery ADD COLUMN raid_json TEXT NULL AFTER disks_json;
