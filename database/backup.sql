/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.14-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: itmanager
-- ------------------------------------------------------
-- Server version	10.11.14-MariaDB-0+deb12u2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cpu_temperatures`
--

DROP TABLE IF EXISTS `cpu_temperatures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cpu_temperatures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pc_id` int(11) NOT NULL,
  `temperature` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ct_pc` (`pc_id`),
  CONSTRAINT `fk_ct_pc` FOREIGN KEY (`pc_id`) REFERENCES `pcs_laptops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cpu_temperatures`
--

LOCK TABLES `cpu_temperatures` WRITE;
/*!40000 ALTER TABLE `cpu_temperatures` DISABLE KEYS */;
/*!40000 ALTER TABLE `cpu_temperatures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `disk_partitions`
--

DROP TABLE IF EXISTS `disk_partitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `disk_partitions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `physical_disk_id` int(11) NOT NULL,
  `drive_letter` varchar(10) DEFAULT NULL,
  `label` varchar(255) DEFAULT NULL,
  `file_system` varchar(50) DEFAULT NULL,
  `total_size_bytes` bigint(20) DEFAULT NULL,
  `free_space_bytes` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_dp_disk` (`physical_disk_id`),
  CONSTRAINT `fk_dp_disk` FOREIGN KEY (`physical_disk_id`) REFERENCES `physical_disks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `disk_partitions`
--

LOCK TABLES `disk_partitions` WRITE;
/*!40000 ALTER TABLE `disk_partitions` DISABLE KEYS */;
/*!40000 ALTER TABLE `disk_partitions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `domains`
--

DROP TABLE IF EXISTS `domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `domains` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain_name` varchar(255) NOT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `is_managed` tinyint(1) DEFAULT 1,
  `expiry_date` date DEFAULT NULL,
  `hosting_provider` varchar(255) DEFAULT NULL,
  `auto_renewal` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_domain_tenant` (`domain_name`,`tenant_id`),
  KEY `idx_domains_tenant` (`tenant_id`),
  CONSTRAINT `fk_domains_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `domains`
--

LOCK TABLES `domains` WRITE;
/*!40000 ALTER TABLE `domains` DISABLE KEYS */;
/*!40000 ALTER TABLE `domains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `factures`
--

DROP TABLE IF EXISTS `factures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `factures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `received_date` date DEFAULT NULL,
  `subject` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `factures`
--

LOCK TABLES `factures` WRITE;
/*!40000 ALTER TABLE `factures` DISABLE KEYS */;
/*!40000 ALTER TABLE `factures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `installed_software`
--

DROP TABLE IF EXISTS `installed_software`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `installed_software` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pc_id` int(11) NOT NULL,
  `software_id` int(11) NOT NULL,
  `installation_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ins_pc` (`pc_id`),
  KEY `idx_ins_software` (`software_id`),
  CONSTRAINT `fk_ins_pc` FOREIGN KEY (`pc_id`) REFERENCES `pcs_laptops` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ins_software` FOREIGN KEY (`software_id`) REFERENCES `software` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `installed_software`
--

LOCK TABLES `installed_software` WRITE;
/*!40000 ALTER TABLE `installed_software` DISABLE KEYS */;
/*!40000 ALTER TABLE `installed_software` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ip_addresses`
--

DROP TABLE IF EXISTS `ip_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ip_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `subnet_mask` varchar(45) DEFAULT NULL,
  `gateway` varchar(45) DEFAULT NULL,
  `dns1` varchar(45) DEFAULT NULL,
  `dns2` varchar(45) DEFAULT NULL,
  `dns_servers` varchar(255) DEFAULT NULL COMMENT 'Alternatif: DNS combinés',
  `vlan_id` varchar(50) DEFAULT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `site_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ip_address` (`ip_address`),
  KEY `idx_ip_tenant` (`tenant_id`),
  KEY `idx_ip_site` (`site_id`),
  CONSTRAINT `fk_ip_site` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ip_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ip_addresses`
--

LOCK TABLES `ip_addresses` WRITE;
/*!40000 ALTER TABLE `ip_addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `ip_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `licences_facture`
--

DROP TABLE IF EXISTS `licences_facture`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `licences_facture` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `facture_id` int(11) NOT NULL,
  `client` varchar(255) DEFAULT NULL,
  `license_name` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `total_price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_lf_facture` (`facture_id`),
  KEY `idx_lf_client` (`client`),
  CONSTRAINT `fk_lf_facture` FOREIGN KEY (`facture_id`) REFERENCES `factures` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `licences_facture`
--

LOCK TABLES `licences_facture` WRITE;
/*!40000 ALTER TABLE `licences_facture` DISABLE KEYS */;
/*!40000 ALTER TABLE `licences_facture` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `licenses`
--

DROP TABLE IF EXISTS `licenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `licenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) DEFAULT NULL,
  `license_name` varchar(255) NOT NULL,
  `login` varchar(255) DEFAULT NULL,
  `password` text DEFAULT NULL COMMENT 'Chiffré AES-256',
  `license_count` int(11) DEFAULT 1,
  `expiry_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_licenses_tenant` (`tenant_id`),
  CONSTRAINT `fk_licenses_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `licenses`
--

LOCK TABLES `licenses` WRITE;
/*!40000 ALTER TABLE `licenses` DISABLE KEYS */;
/*!40000 ALTER TABLE `licenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_services`
--

DROP TABLE IF EXISTS `login_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_services`
--

LOCK TABLES `login_services` WRITE;
/*!40000 ALTER TABLE `login_services` DISABLE KEYS */;
INSERT INTO `login_services` VALUES
(1,'Windows','Comptes Windows / Active Directory',NULL,'2026-02-23 11:27:05','2026-02-23 11:27:05'),
(2,'Microsoft 365','Comptes Microsoft 365 / Azure AD',NULL,'2026-02-23 11:27:05','2026-02-23 11:27:05'),
(3,'Google Workspace','Comptes Google',NULL,'2026-02-23 11:27:05','2026-02-23 11:27:05'),
(4,'SSH','Accès SSH / Linux',NULL,'2026-02-23 11:27:05','2026-02-23 11:27:05'),
(5,'VPN','Connexions VPN',NULL,'2026-02-23 11:27:05','2026-02-23 11:27:05'),
(6,'Autre','Autres types de comptes',NULL,'2026-02-23 11:27:05','2026-02-23 11:27:05');
/*!40000 ALTER TABLE `login_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logins`
--

DROP TABLE IF EXISTS `logins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `logins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `person_id` int(11) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `password` text DEFAULT NULL COMMENT 'Mot de passe chiffré',
  `service_id` int(11) NOT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `site_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_logins_person` (`person_id`),
  KEY `idx_logins_service` (`service_id`),
  KEY `idx_logins_tenant` (`tenant_id`),
  KEY `idx_logins_site` (`site_id`),
  CONSTRAINT `fk_logins_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_logins_service` FOREIGN KEY (`service_id`) REFERENCES `login_services` (`id`),
  CONSTRAINT `fk_logins_site` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_logins_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logins`
--

LOCK TABLES `logins` WRITE;
/*!40000 ALTER TABLE `logins` DISABLE KEYS */;
/*!40000 ALTER TABLE `logins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `m365_subscribed_skus`
--

DROP TABLE IF EXISTS `m365_subscribed_skus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `m365_subscribed_skus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id_ref` int(11) NOT NULL,
  `sku_part_number` varchar(100) DEFAULT NULL,
  `commercial_name` varchar(255) DEFAULT NULL,
  `consumed_units` int(11) DEFAULT 0,
  `enabled_units` int(11) DEFAULT 0,
  `suspended_units` int(11) DEFAULT 0,
  `warning_units` int(11) DEFAULT 0,
  `renewal_date` date DEFAULT NULL,
  `last_updated` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_m365_tenant` (`tenant_id_ref`),
  CONSTRAINT `fk_m365_skus_tenant` FOREIGN KEY (`tenant_id_ref`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `m365_subscribed_skus`
--

LOCK TABLES `m365_subscribed_skus` WRITE;
/*!40000 ALTER TABLE `m365_subscribed_skus` DISABLE KEYS */;
/*!40000 ALTER TABLE `m365_subscribed_skus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `m365_user_licenses`
--

DROP TABLE IF EXISTS `m365_user_licenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `m365_user_licenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id_ref` int(11) NOT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `user_principal_name` varchar(255) DEFAULT NULL,
  `sku_part_number` varchar(100) DEFAULT NULL,
  `commercial_name` varchar(255) DEFAULT NULL,
  `assigned_date` date DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `last_updated` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_m365ul_tenant` (`tenant_id_ref`),
  KEY `idx_m365ul_user` (`user_id`),
  CONSTRAINT `fk_m365ul_tenant` FOREIGN KEY (`tenant_id_ref`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `m365_user_licenses`
--

LOCK TABLES `m365_user_licenses` WRITE;
/*!40000 ALTER TABLE `m365_user_licenses` DISABLE KEYS */;
/*!40000 ALTER TABLE `m365_user_licenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `manufacturers`
--

DROP TABLE IF EXISTS `manufacturers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `manufacturers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `manufacturers`
--

LOCK TABLES `manufacturers` WRITE;
/*!40000 ALTER TABLE `manufacturers` DISABLE KEYS */;
/*!40000 ALTER TABLE `manufacturers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `models`
--

DROP TABLE IF EXISTS `models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `models` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `manufacturer_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_models_manufacturer` (`manufacturer_id`),
  CONSTRAINT `fk_models_manufacturer` FOREIGN KEY (`manufacturer_id`) REFERENCES `manufacturers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `models`
--

LOCK TABLES `models` WRITE;
/*!40000 ALTER TABLE `models` DISABLE KEYS */;
/*!40000 ALTER TABLE `models` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nakivo_backup_jobs`
--

DROP TABLE IF EXISTS `nakivo_backup_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `nakivo_backup_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_id` int(11) NOT NULL,
  `started_at` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_nbj_report` (`report_id`),
  CONSTRAINT `fk_nbj_report` FOREIGN KEY (`report_id`) REFERENCES `nakivo_backup_reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nakivo_backup_jobs`
--

LOCK TABLES `nakivo_backup_jobs` WRITE;
/*!40000 ALTER TABLE `nakivo_backup_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `nakivo_backup_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nakivo_backup_reports`
--

DROP TABLE IF EXISTS `nakivo_backup_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `nakivo_backup_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_name` varchar(255) DEFAULT NULL,
  `report_date` date DEFAULT NULL,
  `total_jobs` int(11) DEFAULT 0,
  `total_vms` int(11) DEFAULT 0,
  `total_data_gb` decimal(10,2) DEFAULT NULL,
  `duration_seconds` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nakivo_backup_reports`
--

LOCK TABLES `nakivo_backup_reports` WRITE;
/*!40000 ALTER TABLE `nakivo_backup_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `nakivo_backup_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nakivo_backup_vms`
--

DROP TABLE IF EXISTS `nakivo_backup_vms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `nakivo_backup_vms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` int(11) NOT NULL,
  `vm_name` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `data_processed_gb` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_nbv_job` (`job_id`),
  CONSTRAINT `fk_nbv_job` FOREIGN KEY (`job_id`) REFERENCES `nakivo_backup_jobs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nakivo_backup_vms`
--

LOCK TABLES `nakivo_backup_vms` WRITE;
/*!40000 ALTER TABLE `nakivo_backup_vms` DISABLE KEYS */;
/*!40000 ALTER TABLE `nakivo_backup_vms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nakivo_target_storage`
--

DROP TABLE IF EXISTS `nakivo_target_storage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `nakivo_target_storage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_id` int(11) NOT NULL,
  `storage_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_nts_report` (`report_id`),
  CONSTRAINT `fk_nts_report` FOREIGN KEY (`report_id`) REFERENCES `nakivo_backup_reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nakivo_target_storage`
--

LOCK TABLES `nakivo_target_storage` WRITE;
/*!40000 ALTER TABLE `nakivo_target_storage` DISABLE KEYS */;
/*!40000 ALTER TABLE `nakivo_target_storage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `network_equipments`
--

DROP TABLE IF EXISTS `network_equipments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `network_equipments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` enum('router','switch','wifiAP','wifi infra','firewall','nas','other') DEFAULT 'switch',
  `model_id` int(11) DEFAULT NULL,
  `site_id` int(11) DEFAULT NULL,
  `manufacturer_id` int(11) DEFAULT NULL,
  `ip_address_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive','maintenance','retired') DEFAULT 'inactive',
  `login_id` int(11) DEFAULT NULL,
  `ports_count` int(11) DEFAULT 0 COMMENT 'Nombre total de ports',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ne_site` (`site_id`),
  KEY `idx_ne_model` (`model_id`),
  KEY `idx_ne_manufacturer` (`manufacturer_id`),
  KEY `idx_ne_ip` (`ip_address_id`),
  KEY `idx_ne_login` (`login_id`),
  CONSTRAINT `fk_ne_ip` FOREIGN KEY (`ip_address_id`) REFERENCES `ip_addresses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ne_login` FOREIGN KEY (`login_id`) REFERENCES `logins` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ne_manufacturer` FOREIGN KEY (`manufacturer_id`) REFERENCES `manufacturers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ne_model` FOREIGN KEY (`model_id`) REFERENCES `models` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ne_site` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `network_equipments`
--

LOCK TABLES `network_equipments` WRITE;
/*!40000 ALTER TABLE `network_equipments` DISABLE KEYS */;
/*!40000 ALTER TABLE `network_equipments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `network_ports`
--

DROP TABLE IF EXISTS `network_ports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `network_ports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `equipment_id` int(11) NOT NULL,
  `port_number` int(11) NOT NULL,
  `port_name` varchar(50) NOT NULL,
  `port_type` enum('ethernet','fiber','serial','console','management','power','sfp','qsfp') DEFAULT 'ethernet',
  `port_speed` varchar(20) DEFAULT NULL,
  `port_status` enum('active','inactive','disabled','error') DEFAULT 'inactive',
  `connected_to_equipment_id` int(11) DEFAULT NULL,
  `connected_to_port_id` int(11) DEFAULT NULL,
  `vlan_id` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_equipment_port` (`equipment_id`,`port_number`),
  UNIQUE KEY `unique_equipment_port_name` (`equipment_id`,`port_name`),
  KEY `idx_equipment_id` (`equipment_id`),
  KEY `idx_port_status` (`port_status`),
  KEY `idx_connected_equipment` (`connected_to_equipment_id`),
  KEY `fk_np_connected_port` (`connected_to_port_id`),
  CONSTRAINT `fk_np_connected_equipment` FOREIGN KEY (`connected_to_equipment_id`) REFERENCES `network_equipments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_np_connected_port` FOREIGN KEY (`connected_to_port_id`) REFERENCES `network_ports` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_np_equipment` FOREIGN KEY (`equipment_id`) REFERENCES `network_equipments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `network_ports`
--

LOCK TABLES `network_ports` WRITE;
/*!40000 ALTER TABLE `network_ports` DISABLE KEYS */;
/*!40000 ALTER TABLE `network_ports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operating_systems`
--

DROP TABLE IF EXISTS `operating_systems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `operating_systems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `version` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operating_systems`
--

LOCK TABLES `operating_systems` WRITE;
/*!40000 ALTER TABLE `operating_systems` DISABLE KEYS */;
INSERT INTO `operating_systems` VALUES
(1,'Windows','10','2026-02-23 11:27:05','2026-02-23 11:27:05'),
(2,'Windows','11','2026-02-23 11:27:05','2026-02-23 11:27:05'),
(3,'Windows Server','2019','2026-02-23 11:27:05','2026-02-23 11:27:05'),
(4,'Windows Server','2022','2026-02-23 11:27:05','2026-02-23 11:27:05'),
(5,'Ubuntu','22.04 LTS','2026-02-23 11:27:05','2026-02-23 11:27:05'),
(6,'Debian','12','2026-02-23 11:27:05','2026-02-23 11:27:05'),
(7,'macOS','Sonoma','2026-02-23 11:27:05','2026-02-23 11:27:05');
/*!40000 ALTER TABLE `operating_systems` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pcs_laptops`
--

DROP TABLE IF EXISTS `pcs_laptops`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pcs_laptops` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `site_id` int(11) DEFAULT NULL,
  `operating_system_id` int(11) DEFAULT NULL,
  `ip_address_id` int(11) DEFAULT NULL,
  `processor_model` varchar(255) DEFAULT NULL,
  `teamviewer_id` varchar(50) DEFAULT NULL,
  `model_id` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'free',
  `account_id` int(11) DEFAULT NULL,
  `person_id` int(11) DEFAULT NULL,
  `last_account` varchar(255) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `ram_total` bigint(20) DEFAULT NULL,
  `ram_used` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pc_tenant` (`tenant_id`),
  KEY `idx_pc_site` (`site_id`),
  KEY `idx_pc_os` (`operating_system_id`),
  KEY `idx_pc_ip` (`ip_address_id`),
  KEY `idx_pc_model` (`model_id`),
  KEY `idx_pc_account` (`account_id`),
  KEY `idx_pc_person` (`person_id`),
  CONSTRAINT `fk_pc_account` FOREIGN KEY (`account_id`) REFERENCES `logins` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pc_ip` FOREIGN KEY (`ip_address_id`) REFERENCES `ip_addresses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pc_model` FOREIGN KEY (`model_id`) REFERENCES `models` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pc_os` FOREIGN KEY (`operating_system_id`) REFERENCES `operating_systems` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pc_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pc_site` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pc_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pcs_laptops`
--

LOCK TABLES `pcs_laptops` WRITE;
/*!40000 ALTER TABLE `pcs_laptops` DISABLE KEYS */;
/*!40000 ALTER TABLE `pcs_laptops` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `persons`
--

DROP TABLE IF EXISTS `persons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `persons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_persons_tenant` (`tenant_id`),
  KEY `idx_persons_email` (`email`),
  CONSTRAINT `fk_persons_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `persons`
--

LOCK TABLES `persons` WRITE;
/*!40000 ALTER TABLE `persons` DISABLE KEYS */;
/*!40000 ALTER TABLE `persons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `physical_disks`
--

DROP TABLE IF EXISTS `physical_disks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `physical_disks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pc_id` int(11) NOT NULL,
  `model` varchar(255) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `interface_type` varchar(50) DEFAULT NULL,
  `size_bytes` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pd_pc` (`pc_id`),
  CONSTRAINT `fk_pd_pc` FOREIGN KEY (`pc_id`) REFERENCES `pcs_laptops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `physical_disks`
--

LOCK TABLES `physical_disks` WRITE;
/*!40000 ALTER TABLE `physical_disks` DISABLE KEYS */;
/*!40000 ALTER TABLE `physical_disks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `servers`
--

DROP TABLE IF EXISTS `servers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `type` varchar(50) DEFAULT 'Physique',
  `site_id` int(11) DEFAULT NULL,
  `model_id` int(11) DEFAULT NULL,
  `processor_model` varchar(255) DEFAULT NULL,
  `ram_total` bigint(20) DEFAULT NULL COMMENT 'En octets',
  `ram_used` bigint(20) DEFAULT NULL COMMENT 'En octets',
  `operating_system_id` int(11) DEFAULT NULL,
  `ip_address_id` int(11) DEFAULT NULL,
  `hostname` varchar(255) DEFAULT NULL,
  `teamviewer_id` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_servers_site` (`site_id`),
  KEY `idx_servers_model` (`model_id`),
  KEY `idx_servers_os` (`operating_system_id`),
  KEY `idx_servers_ip` (`ip_address_id`),
  CONSTRAINT `fk_servers_ip` FOREIGN KEY (`ip_address_id`) REFERENCES `ip_addresses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_servers_model` FOREIGN KEY (`model_id`) REFERENCES `models` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_servers_os` FOREIGN KEY (`operating_system_id`) REFERENCES `operating_systems` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_servers_site` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `servers`
--

LOCK TABLES `servers` WRITE;
/*!40000 ALTER TABLE `servers` DISABLE KEYS */;
/*!40000 ALTER TABLE `servers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sites`
--

DROP TABLE IF EXISTS `sites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_sites_tenant` (`tenant_id`),
  CONSTRAINT `fk_sites_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sites`
--

LOCK TABLES `sites` WRITE;
/*!40000 ALTER TABLE `sites` DISABLE KEYS */;
INSERT INTO `sites` VALUES
(1,'Bruxelles','',2,0,'2026-02-23 11:35:48','2026-02-23 11:35:48'),
(2,'Liège','',2,0,'2026-02-23 11:35:58','2026-02-23 11:35:58');
/*!40000 ALTER TABLE `sites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `software`
--

DROP TABLE IF EXISTS `software`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `software` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `version` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `software`
--

LOCK TABLES `software` WRITE;
/*!40000 ALTER TABLE `software` DISABLE KEYS */;
/*!40000 ALTER TABLE `software` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenants`
--

DROP TABLE IF EXISTS `tenants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `domain` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `nakivo_customer_name` varchar(255) DEFAULT NULL COMMENT 'Nom client dans Nakivo Backup',
  `dsd_customer_name` varchar(255) DEFAULT NULL COMMENT 'Nom client dans DSD Factures',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tenants_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenants`
--

LOCK TABLES `tenants` WRITE;
/*!40000 ALTER TABLE `tenants` DISABLE KEYS */;
INSERT INTO `tenants` VALUES
(2,'Altiplan',NULL,NULL,NULL,NULL,NULL,'2026-02-23 11:35:29','2026-02-23 11:35:29');
/*!40000 ALTER TABLE `tenants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `is_global_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_email` (`email`),
  KEY `idx_users_tenant` (`tenant_id`),
  CONSTRAINT `fk_users_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'Administrateur','admin@itmanager.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,1,'2026-02-23 11:27:05','2026-02-23 11:27:05');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `v_equipment_ports_summary`
--

DROP TABLE IF EXISTS `v_equipment_ports_summary`;
/*!50001 DROP VIEW IF EXISTS `v_equipment_ports_summary`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `v_equipment_ports_summary` AS SELECT
 1 AS `equipment_id`,
  1 AS `equipment_name`,
  1 AS `equipment_type`,
  1 AS `total_ports`,
  1 AS `configured_ports`,
  1 AS `active_ports`,
  1 AS `inactive_ports`,
  1 AS `connected_ports` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_network_connections`
--

DROP TABLE IF EXISTS `v_network_connections`;
/*!50001 DROP VIEW IF EXISTS `v_network_connections`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `v_network_connections` AS SELECT
 1 AS `port_id`,
  1 AS `equipment_name`,
  1 AS `port_name`,
  1 AS `port_status`,
  1 AS `connected_to_equipment`,
  1 AS `connected_to_port`,
  1 AS `vlan_id`,
  1 AS `description` */;
SET character_set_client = @saved_cs_client;

--
-- Dumping events for database 'itmanager'
--

--
-- Dumping routines for database 'itmanager'
--
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP FUNCTION IF EXISTS `GetAvailablePorts` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb3 */ ;
/*!50003 SET character_set_results = utf8mb3 */ ;
/*!50003 SET collation_connection  = utf8mb3_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`renaud`@`localhost` FUNCTION `GetAvailablePorts`(equipment_id INT) RETURNS int(11)
    READS SQL DATA
    DETERMINISTIC
BEGIN
    DECLARE available_count INT;
    
    SELECT COUNT(*) INTO available_count
    FROM network_ports 
    WHERE network_ports.equipment_id = equipment_id 
    AND port_status = 'inactive' 
    AND connected_to_equipment_id IS NULL;
    
    RETURN available_count;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `CreatePortsForEquipment` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb3 */ ;
/*!50003 SET character_set_results = utf8mb3 */ ;
/*!50003 SET collation_connection  = utf8mb3_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`renaud`@`localhost` PROCEDURE `CreatePortsForEquipment`(
    IN equipment_id INT,
    IN ports_count INT,
    IN port_type VARCHAR(20),
    IN port_speed VARCHAR(20)
)
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE port_prefix VARCHAR(10);
    
    SELECT 
        CASE 
            WHEN type = 'switch' THEN 'Gi'
            WHEN type = 'router' THEN 'Fa'
            WHEN type = 'wifiAP' THEN 'Port'
            WHEN type = 'wifi infra' THEN 'Port'
            ELSE 'Port'
        END INTO port_prefix
    FROM network_equipments 
    WHERE id = equipment_id;
    
    WHILE i <= ports_count DO
        INSERT INTO network_ports (
            equipment_id, 
            port_number, 
            port_name, 
            port_type, 
            port_speed,
            port_status
        ) VALUES (
            equipment_id, 
            i, 
            CONCAT(port_prefix, '0/', i),
            COALESCE(port_type, 'ethernet'),
            port_speed,
            'inactive'
        );
        SET i = i + 1;
    END WHILE;
    
    UPDATE network_equipments 
    SET ports_count = ports_count 
    WHERE id = equipment_id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Final view structure for view `v_equipment_ports_summary`
--

/*!50001 DROP VIEW IF EXISTS `v_equipment_ports_summary`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`renaud`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_equipment_ports_summary` AS select `ne`.`id` AS `equipment_id`,`ne`.`name` AS `equipment_name`,`ne`.`type` AS `equipment_type`,`ne`.`ports_count` AS `total_ports`,count(`np`.`id`) AS `configured_ports`,sum(case when `np`.`port_status` = 'active' then 1 else 0 end) AS `active_ports`,sum(case when `np`.`port_status` = 'inactive' then 1 else 0 end) AS `inactive_ports`,sum(case when `np`.`connected_to_equipment_id` is not null then 1 else 0 end) AS `connected_ports` from (`network_equipments` `ne` left join `network_ports` `np` on(`ne`.`id` = `np`.`equipment_id`)) group by `ne`.`id`,`ne`.`name`,`ne`.`type`,`ne`.`ports_count` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_network_connections`
--

/*!50001 DROP VIEW IF EXISTS `v_network_connections`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`renaud`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_network_connections` AS select `np1`.`id` AS `port_id`,`eq1`.`name` AS `equipment_name`,`np1`.`port_name` AS `port_name`,`np1`.`port_status` AS `port_status`,`eq2`.`name` AS `connected_to_equipment`,`np2`.`port_name` AS `connected_to_port`,`np1`.`vlan_id` AS `vlan_id`,`np1`.`description` AS `description` from (((`network_ports` `np1` join `network_equipments` `eq1` on(`np1`.`equipment_id` = `eq1`.`id`)) left join `network_equipments` `eq2` on(`np1`.`connected_to_equipment_id` = `eq2`.`id`)) left join `network_ports` `np2` on(`np1`.`connected_to_port_id` = `np2`.`id`)) where `np1`.`connected_to_equipment_id` is not null order by `eq1`.`name`,`np1`.`port_number` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-23 12:46:55
