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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `disk_partitions`
--

LOCK TABLES `disk_partitions` WRITE;
/*!40000 ALTER TABLE `disk_partitions` DISABLE KEYS */;
INSERT INTO `disk_partitions` VALUES
(19,19,'C:',NULL,'NTFS',999159754752,827287740416,'2026-02-23 14:07:42','2026-02-23 14:07:42'),
(20,20,'D:',NULL,'NTFS',499203960832,308359278592,'2026-02-23 14:07:42','2026-02-23 14:07:42'),
(24,24,'C:',NULL,'NTFS',511095861248,452091592704,'2026-02-23 21:39:34','2026-02-23 21:39:34');
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
) ENGINE=InnoDB AUTO_INCREMENT=552 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `installed_software`
--

LOCK TABLES `installed_software` WRITE;
/*!40000 ALTER TABLE `installed_software` DISABLE KEYS */;
INSERT INTO `installed_software` VALUES
(370,1,1,'2026-02-10','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(371,1,2,'2026-02-10','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(372,1,3,NULL,'2026-02-23 14:07:42','2026-02-23 14:07:42'),
(373,1,4,NULL,'2026-02-23 14:07:42','2026-02-23 14:07:42'),
(374,1,5,NULL,'2026-02-23 14:07:42','2026-02-23 14:07:42'),
(375,1,6,NULL,'2026-02-23 14:07:42','2026-02-23 14:07:42'),
(376,1,7,'2026-02-10','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(377,1,8,NULL,'2026-02-23 14:07:42','2026-02-23 14:07:42'),
(378,1,9,'2026-02-10','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(379,1,10,'2026-02-10','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(380,1,11,'2026-02-10','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(381,1,12,'2026-02-10','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(382,1,13,'2026-02-10','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(383,1,14,'2026-02-10','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(384,1,15,'2026-02-10','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(385,1,16,'2026-02-10','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(386,1,17,'2026-02-21','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(387,1,18,'2026-02-21','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(388,1,19,'2026-02-10','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(389,1,20,'2026-02-10','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(390,1,21,'2026-02-10','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(391,1,22,'2026-02-10','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(392,1,23,'2026-02-10','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(393,1,24,'2026-02-10','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(394,1,25,'2026-02-10','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(395,1,26,'2026-02-10','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(396,1,27,'2026-02-10','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(397,1,28,'2026-02-10','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(398,1,29,'2026-02-22','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(399,1,30,'2026-02-22','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(400,1,31,NULL,'2026-02-23 14:07:42','2026-02-23 14:07:42'),
(401,1,32,NULL,'2026-02-23 14:07:42','2026-02-23 14:07:42'),
(402,1,33,'2026-02-10','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(403,1,34,NULL,'2026-02-23 14:07:42','2026-02-23 14:07:42'),
(404,1,35,'2026-02-10','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(405,1,36,'2026-02-18','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(406,1,37,'2026-02-10','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(407,1,38,'2026-02-10','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(408,1,39,NULL,'2026-02-23 14:07:42','2026-02-23 14:07:42'),
(409,1,40,'2026-02-10','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(410,1,41,'2026-02-16','2026-02-23 14:07:42','2026-02-23 14:07:42'),
(504,2,1,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(505,2,3,NULL,'2026-02-23 21:39:34','2026-02-23 21:39:34'),
(506,2,42,NULL,'2026-02-23 21:39:34','2026-02-23 21:39:34'),
(507,2,80,NULL,'2026-02-23 21:39:34','2026-02-23 21:39:34'),
(508,2,43,NULL,'2026-02-23 21:39:34','2026-02-23 21:39:34'),
(509,2,44,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(510,2,45,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(511,2,46,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(512,2,47,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(513,2,48,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(514,2,49,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(515,2,50,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(516,2,51,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(517,2,52,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(518,2,53,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(519,2,54,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(520,2,55,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(521,2,56,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(522,2,10,'2026-02-18','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(523,2,57,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(524,2,58,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(525,2,79,'2026-02-23','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(526,2,59,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(527,2,60,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(528,2,61,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(529,2,16,'2026-02-18','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(530,2,62,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(531,2,63,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(532,2,64,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(533,2,65,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(534,2,66,'2026-02-18','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(535,2,67,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(536,2,68,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(537,2,69,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(538,2,70,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(539,2,71,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(540,2,72,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(541,2,73,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(542,2,74,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(543,2,75,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(544,2,29,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(545,2,30,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(546,2,31,NULL,'2026-02-23 21:39:34','2026-02-23 21:39:34'),
(547,2,76,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(548,2,77,'2026-02-18','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(549,2,33,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(550,2,78,'2026-02-22','2026-02-23 21:39:34','2026-02-23 21:39:34'),
(551,2,39,NULL,'2026-02-23 21:39:34','2026-02-23 21:39:34');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ip_addresses`
--

LOCK TABLES `ip_addresses` WRITE;
/*!40000 ALTER TABLE `ip_addresses` DISABLE KEYS */;
INSERT INTO `ip_addresses` VALUES
(1,'192.168.0.5',NULL,'255.255.255.0','192.168.0.1',NULL,NULL,'109.88.203.3, 62.197.111.140, 8.8.8.8',NULL,2,1,'2026-02-23 14:00:08','2026-02-23 14:07:42'),
(2,'192.168.0.97',NULL,'255.255.255.0','192.168.0.1',NULL,NULL,'109.88.203.3, 62.197.111.140',NULL,2,1,'2026-02-23 14:43:17','2026-02-23 21:39:34');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `manufacturers`
--

LOCK TABLES `manufacturers` WRITE;
/*!40000 ALTER TABLE `manufacturers` DISABLE KEYS */;
INSERT INTO `manufacturers` VALUES
(1,'Hewlett-Packard','2026-02-23 13:14:33','2026-02-23 13:14:33'),
(2,'LENOVO','2026-02-23 14:43:17','2026-02-23 14:43:17');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `models`
--

LOCK TABLES `models` WRITE;
/*!40000 ALTER TABLE `models` DISABLE KEYS */;
INSERT INTO `models` VALUES
(1,'HP Z840 Workstation',1,'2026-02-23 13:14:33','2026-02-23 13:14:33'),
(2,'21BWS39V00',2,'2026-02-23 14:43:17','2026-02-23 14:43:17');
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
(7,'macOS','Sonoma','2026-02-23 11:27:05','2026-02-23 11:27:05'),
(8,'windows','10.0.26200.7840 Build 26200.7840','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(9,'Windows 11','24H2','2026-02-23 13:56:30','2026-02-23 13:56:30');
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
  `rustdesk_id` varchar(100) DEFAULT NULL,
  `rustdesk_password` varchar(100) DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pcs_laptops`
--

LOCK TABLES `pcs_laptops` WRITE;
/*!40000 ALTER TABLE `pcs_laptops` DISABLE KEYS */;
INSERT INTO `pcs_laptops` VALUES
(1,'DESKTOP-NV51RC0',2,1,9,1,'Intel(R) Xeon(R) CPU E5-2678 v3 @ 2.50GHz',NULL,NULL,NULL,1,'used',NULL,NULL,'DESKTOP-NV51RC0\\User','CZC5402K1C',120161181696,24431116288,'2026-02-23 13:02:24','2026-02-23 15:07:20'),
(2,'DESKTOP-RI218AB',2,1,9,2,'12th Gen Intel(R) Core(TM) i5-1235U',NULL,'37700904',NULL,2,'inventoried',NULL,NULL,'DESKTOP-RI218AB\\User','PF4DG5RV',16863059968,8269930496,'2026-02-23 14:43:17','2026-02-23 21:39:34');
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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `physical_disks`
--

LOCK TABLES `physical_disks` WRITE;
/*!40000 ALTER TABLE `physical_disks` DISABLE KEYS */;
INSERT INTO `physical_disks` VALUES
(19,1,'NTFS',NULL,NULL,999159754752,'2026-02-23 14:07:42','2026-02-23 14:07:42'),
(20,1,'NTFS',NULL,NULL,499203960832,'2026-02-23 14:07:42','2026-02-23 14:07:42'),
(24,2,'NTFS',NULL,NULL,511095861248,'2026-02-23 21:39:34','2026-02-23 21:39:34');
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
  `rustdesk_id` varchar(100) DEFAULT NULL,
  `rustdesk_password` varchar(100) DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `software`
--

LOCK TABLES `software` WRITE;
/*!40000 ALTER TABLE `software` DISABLE KEYS */;
INSERT INTO `software` VALUES
(1,'Git','2.53.0','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(2,'Greenshot 1.3.312','1.3.312','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(3,'Mozilla Firefox (x64 fr)','147.0.4','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(4,'Mozilla Maintenance Service','147.0.3','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(5,'Microsoft 365 Apps for business - fr-fr','16.0.19628.20214','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(6,'Microsoft OneDrive','26.017.0126.0002','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(7,'PerformanceTest 11','11.1.1008.0','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(8,'WinRAR 7.13 (64-bit)','7.13.0','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(9,'Python 3.14.3 Standard Library (64-bit)','3.14.3150.0','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(10,'Microsoft Visual C++ 2022 X64 Minimum Runtime - 14.44.35211','14.44.35211','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(11,'Oracle VirtualBox 7.2.6','7.2.6','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(12,'Python 3.14.3 Core Interpreter (64-bit)','3.14.3150.0','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(13,'Python 3.14.3 Tcl/Tk Support (64-bit)','3.14.3150.0','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(14,'Python 3.14.3 Documentation (64-bit)','3.14.3150.0','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(15,'Python 3.14.3 Development Libraries (64-bit)','3.14.3150.0','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(16,'Microsoft Visual C++ 2022 X64 Additional Runtime - 14.44.35211','14.44.35211','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(17,'Office 16 Click-to-Run Extensibility Component','16.0.19628.20214','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(18,'Office 16 Click-to-Run Localization Component','16.0.19628.20214','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(19,'Python 3.14.3 Test Suite (64-bit)','3.14.3150.0','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(20,'Google Chrome','145.0.7632.77','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(21,'NVIDIA Pilote graphique 582.16','582.16','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(22,'NVIDIA RTX Desktop Manager 205.38','205.38','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(23,'NVIDIA Pilote audio HD 1.4.5.0','1.4.5.0','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(24,'NVIDIA Install Application','2.1002.434.0','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(25,'NVIDIA USBC Driver 1.52.831.832','1.52.831.832','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(26,'Python 3.14.3 Executables (64-bit)','3.14.3150.0','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(27,'Python 3.14.3 pip Bootstrap (64-bit)','3.14.3150.0','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(28,'Python 3.14.3 Add to Path (64-bit)','3.14.3150.0','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(29,'Microsoft Edge','145.0.3800.70','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(30,'Microsoft Edge WebView2 Runtime','145.0.3800.70','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(31,'Opera Stable 127.0.5778.76','127.0.5778.76','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(32,'Synology Assistant (remove only)','7.0.6-50085','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(33,'WinSCP 6.5.5','6.5.5','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(34,'Microsoft Visual C++ 2015-2022 Redistributable (x86) - 14.44.35211','14.44.35211.0','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(35,'Microsoft Visual C++ 2022 X86 Minimum Runtime - 14.44.35211','14.44.35211','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(36,'Splashtop Business','3.8.4.0','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(37,'Microsoft Visual C++ 2022 X86 Additional Runtime - 14.44.35211','14.44.35211','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(38,'Python Launcher','3.14.3150.0','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(39,'Microsoft Visual C++ 2015-2022 Redistributable (x64) - 14.44.35211','14.44.35211.0','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(40,'Realtek Audio Driver','6.0.9013.1','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(41,'MobaXterm','26.0.0.5436','2026-02-23 13:02:24','2026-02-23 13:02:24'),
(42,'Mozilla Maintenance Service','147.0.4','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(43,'WinRAR 7.20 (64-bit)','7.20.0','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(44,'Microsoft .NET AppHost Pack - 10.0.3 (x64)','80.12.47319','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(45,'Google Chrome','145.0.7632.110','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(46,'Microsoft .NET 10.0 Templates 10.0.103 x64','40.10.52243','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(47,'Microsoft.NET.Workload.Mono.Toolchain.net8.Manifest-10.0.100 (x64)','81.156.47319','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(48,'Microsoft.NET.Sdk.Maui.Manifest-10.0.100 (x64)','10.0.0','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(49,'Microsoft .NET Toolset 10.0.103 (x64)','40.10.52243','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(50,'Microsoft.NET.Workload.Mono.Toolchain.net9.Manifest-10.0.100 (x64)','81.156.47319','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(51,'Microsoft Windows Desktop Runtime - 10.0.3 (x64)','80.12.47319','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(52,'Microsoft.NET.Workload.Mono.Toolchain.net6.Manifest-10.0.100 (x64)','81.156.47319','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(53,'Microsoft.NET.Workload.Emscripten.Current.Manifest-10.0.100 (x64)','81.156.47319','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(54,'Microsoft .NET Runtime - 10.0.3 (x64)','80.12.47319','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(55,'Microsoft.NET.Workload.Emscripten.net8.Manifest-10.0.100 (x64)','81.156.47319','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(56,'Microsoft.NET.Sdk.tvOS.Manifest-10.0.100 (x64)','26.0.11017','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(57,'Zoom Workplace (64-bit)','6.7.30439','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(58,'Microsoft .NET Targeting Pack - 10.0.3 (x64)','80.12.47319','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(59,'Microsoft ASP.NET Core Runtime - 10.0.3 (x64)','80.12.47319','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(60,'Microsoft.NET.Sdk.iOS.Manifest-10.0.100 (x64)','26.0.11017','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(61,'Microsoft.NET.Workload.Emscripten.net7.Manifest-10.0.100 (x64)','81.156.47319','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(62,'Microsoft .NET Host FX Resolver - 10.0.3 (x64)','80.12.47319','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(63,'Microsoft.NET.Workload.Mono.Toolchain.Current.Manifest-10.0.100 (x64)','81.156.47319','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(64,'Microsoft .NET Host - 10.0.3 (x64)','80.12.47319','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(65,'Microsoft Windows Desktop Targeting Pack - 10.0.3 (x64)','80.12.47319','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(66,'Microsoft Teams Meeting Add-in for Microsoft Office','1.25.28902','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(67,'Microsoft.NET.Workload.Mono.Toolchain.net7.Manifest-10.0.100 (x64)','81.156.47319','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(68,'Microsoft ASP.NET Core Targeting Pack - 10.0.3 (x64)','80.12.47319','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(69,'Microsoft.NET.Sdk.Android.Manifest-10.0.100 (x64)','36.1.2','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(70,'Microsoft.NET.Sdk.macOS.Manifest-10.0.100 (x64)','26.0.11017','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(71,'Microsoft.NET.Workload.Emscripten.net6.Manifest-10.0.100 (x64)','81.156.47319','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(72,'Cursor','2.5.20','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(73,'Microsoft.NET.Sdk.MacCatalyst.Manifest-10.0.100 (x64)','26.0.11017','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(74,'Microsoft.NET.Workload.Emscripten.net9.Manifest-10.0.100 (x64)','81.156.47319','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(75,'PuTTY release 0.83 (64-bit)','0.83.0.0','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(76,'Lenovo System Update','5.08.03.59','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(77,'Lenovo Vantage Service','4.2511.16.0','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(78,'Microsoft .NET SDK 10.0.103 (x64)','10.1.326.7603','2026-02-23 14:43:17','2026-02-23 14:43:17'),
(79,'ESET Security','19.0.14.0','2026-02-23 14:57:22','2026-02-23 14:57:22'),
(80,'RustDesk','1.4.5','2026-02-23 21:39:34','2026-02-23 21:39:34');
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

-- Dump completed on 2026-02-25 16:50:35
