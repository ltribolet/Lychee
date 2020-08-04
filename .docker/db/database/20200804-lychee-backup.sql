-- MariaDB dump 10.17  Distrib 10.5.4-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: lychee
-- ------------------------------------------------------
-- Server version	10.5.4-MariaDB-1:10.5.4+maria~focal

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

CREATE DATABASE IF NOT EXISTS `lychee` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON lychee.* TO 'lychee'@'%' WITH GRANT OPTION;
USE `lychee`;

--
-- Table structure for table `albums`
--

DROP TABLE IF EXISTS `albums`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `albums` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `owner_id` int(11) NOT NULL DEFAULT 0,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_takestamp` timestamp NULL DEFAULT NULL,
  `max_takestamp` timestamp NULL DEFAULT NULL,
  `public` tinyint(1) NOT NULL DEFAULT 0,
  `full_photo` tinyint(1) NOT NULL DEFAULT 1,
  `visible_hidden` tinyint(1) NOT NULL DEFAULT 1,
  `downloadable` tinyint(1) NOT NULL DEFAULT 0,
  `share_button_visible` tinyint(1) NOT NULL DEFAULT 0,
  `password` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `license` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `albums_parent_id_index` (`parent_id`),
  CONSTRAINT `albums_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `albums` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `albums`
--

LOCK TABLES `albums` WRITE;
/*!40000 ALTER TABLE `albums` DISABLE KEYS */;
/*!40000 ALTER TABLE `albums` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configs`
--

DROP TABLE IF EXISTS `configs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cat` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Config',
  `type_range` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0|1',
  `confidentiality` tinyint(4) NOT NULL DEFAULT 0,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configs`
--

LOCK TABLES `configs` WRITE;
/*!40000 ALTER TABLE `configs` DISABLE KEYS */;
INSERT INTO `configs` VALUES (1,'version','040006','Admin','int',0,''),(4,'check_for_updates','0','Admin','0|1',0,''),(5,'sorting_Photos_col','takestamp','Gallery','id|takestamp|title|description|public|star|type',2,''),(6,'sorting_Photos_order','ASC','Gallery','ASC|DESC',2,''),(7,'sorting_Albums_col','max_takestamp','Gallery','id|title|description|public|max_takestamp|min_takestamp|created_at',0,''),(8,'sorting_Albums_order','ASC','Gallery','ASC|DESC',0,''),(9,'imagick','1','Image Processing','0|1',2,''),(10,'dropbox_key','','Admin','string',3,''),(11,'skip_duplicates','0','Image Processing','0|1',2,''),(12,'small_max_width','0','Image Processing','int',2,''),(13,'small_max_height','360','Image Processing','int',2,''),(14,'medium_max_width','1920','Image Processing','int',2,''),(15,'medium_max_height','1080','Image Processing','int',2,''),(16,'lang','en','Gallery','',0,''),(17,'layout','1','Gallery','0|1|2',0,''),(18,'image_overlay','1','Gallery','0|1',0,''),(19,'image_overlay_type','desc','Gallery','exif|desc|takedate',0,''),(20,'default_license','none','Gallery','license',2,''),(21,'compression_quality','90','Image Processing','int',2,''),(22,'full_photo','1','Gallery','0|1',0,''),(23,'delete_imported','0','Image Processing','0|1',2,''),(24,'Mod_Frame','1','Mod Frame','0|1',0,''),(25,'Mod_Frame_refresh','30','Mod Frame','int',0,''),(26,'thumb_2x','1','Image Processing','0|1',2,''),(27,'small_2x','1','Image Processing','0|1',2,''),(28,'medium_2x','1','Image Processing','0|1',2,''),(29,'landing_page_enable','0','Mod Welcome','0|1',0,''),(30,'landing_owner','John Smith','Mod Welcome','string',2,''),(31,'landing_title','John Smith','Mod Welcome','string',2,''),(32,'landing_subtitle','Cats, Dogs & Humans Photography','Mod Welcome','string',2,''),(33,'landing_facebook','https://www.facebook.com/JohnSmith','Mod Welcome','string',2,''),(34,'landing_flickr','https://www.flickr.com/JohnSmith','Mod Welcome','string',2,''),(35,'landing_twitter','https://www.twitter.com/JohnSmith','Mod Welcome','string',2,''),(36,'landing_instagram','https://instagram.com/JohnSmith','Mod Welcome','string',2,''),(37,'landing_youtube','https://www.youtube.com/JohnSmith','Mod Welcome','string',2,''),(38,'landing_background','dist/cat.jpg','Mod Welcome','string',2,''),(39,'site_title','Lychee v4','config','string',0,''),(40,'site_copyright_enable','1','config','0|1',2,''),(41,'site_copyright_begin','2019','config','int',2,''),(42,'site_copyright_end','2019','config','int',2,''),(43,'api_key','','Admin','string',3,''),(44,'allow_online_git_pull','1','Admin','0|1',3,''),(45,'force_migration_in_production','0','Admin','0|1',3,''),(46,'additional_footer_text','','config','string',2,''),(47,'display_social_in_gallery','0','config','0|1',2,''),(48,'public_search','0','config','0|1',0,''),(49,'gen_demo_js','0','Admin','0|1',3,''),(50,'hide_version_number','0','config','0|1',3,''),(51,'SL_enable','0','Symbolic Link','0|1',2,''),(52,'SL_for_admin','0','Symbolic Link','0|1',2,''),(53,'SL_life_time_days','7','Symbolic Link','int',3,''),(54,'public_recent','0','Smart Albums','0|1',0,''),(55,'recent_age','1','Smart Albums','int',2,''),(56,'public_starred','0','Smart Albums','0|1',0,''),(57,'downloadable','0','config','0|1',0,''),(58,'photos_wraparound','1','Gallery','0|1',0,''),(59,'raw_formats','.tex','config','',3,''),(60,'map_display','0','Mod Map','0|1',0,''),(61,'zip64','1','config','0|1',0,''),(62,'map_display_public','0','Mod Map','0|1',0,''),(63,'map_provider','Wikimedia','Mod Map','Wikimedia|OpenStreetMap.org|OpenStreetMap.de|OpenStreetMap.fr|RRZE',0,''),(64,'force_32bit_ids','0','config','0|1',0,''),(65,'map_include_subalbums','0','Mod Map','0|1',0,''),(66,'update_check_every_days','3','config','int',2,''),(67,'has_exiftool','1','Image Processing','0|1|2',2,''),(68,'share_button_visible','0','config','0|1',0,''),(69,'import_via_symlink','0','Image Processing','0|1',2,''),(70,'has_ffmpeg','1','Image Processing','0|1|2',2,''),(71,'apply_composer_update','0','Admin','0|1',3,''),(72,'location_decoding','0','Mod Map','0|1',0,''),(73,'location_decoding_timeout','30','Mod Map','int',0,''),(74,'location_show','1','Mod Map','0|1',0,''),(75,'location_show_public','0','Mod Map','0|1',0,''),(76,'rss_enable','0','Mod RSS','0|1',0,''),(77,'rss_recent_days','7','Mod RSS','int',0,''),(78,'rss_max_items','100','Mod RSS','int',0,''),(79,'prefer_available_xmp_metadata','0','Image Processing','0|1',2,''),(80,'editor_enabled','1','Image Processing','0|1',2,''),(81,'lossless_optimization','1','Image Processing','0|1',2,'');
/*!40000 ALTER TABLE `configs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `function` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `line` int(11) NOT NULL,
  `text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs`
--

LOCK TABLES `logs` WRITE;
/*!40000 ALTER TABLE `logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2018_08_03_110935_create_albums_table',1),(3,'2018_08_03_110936_create_photos_table',1),(4,'2018_08_03_110942_create_configs_table',1),(5,'2018_08_03_111324_create_logs_table',1),(6,'2018_08_10_134924_move_settings',1),(7,'2018_08_15_102039_move_albums',1),(8,'2018_08_15_103716_move_photos',1),(9,'2018_10_30_135411_sharing',1),(10,'2019_02_21_114356_create_pages_table',1),(11,'2019_02_21_114408_create_page_contents_table',1),(12,'2019_06_21_180451_create_sym_links_table',1),(13,'2019_09_28_171753_config_fix',1),(14,'2019_09_28_190822_photos_fix',1),(15,'2019_10_01_add_livephoto_cols',1),(16,'2019_10_02_1400_config_map_display_public',1),(17,'2019_10_03_214750_frame_refresh_in_sec',1),(18,'2019_10_06_1400_config_map_providers',1),(19,'2019_10_06_152017_add_force_32bit_ids',1),(20,'2019_10_07_0900_config_map_include_sub_albums',1),(21,'2019_10_09_233402_config_map_mod',1),(22,'2019_10_11_093442_config_check_update_every',1),(23,'2019_12_02_2100_config_exiftool',1),(24,'2019_12_15_0700_add_share_button_visible_option',1),(25,'2019_12_15_1000_config_check_update_every_cat_fix',1),(26,'2019_12_25_0600_config_exiftool_ternary',1),(27,'2020_01_018_2300_config_import_via_symlink',1),(28,'2020_01_04_1200_config_has_ffmpeg',1),(29,'2020_01_26_1200_config_public_sorting',1),(30,'2020_01_28_133201_composer_update',1),(31,'2020_02_14_0600_location_decoding',1),(32,'2020_03_11_124417_increase_length_photo_type',1),(33,'2020_03_17_200000_unhide_configs',1),(34,'2020_04_19_122905_bump_version',1),(35,'2020_04_22_155712_bump_version040002',1),(36,'2020_04_29_000250_bump_version040003',1),(37,'2020_05_12_114228_rss',1),(38,'2020_05_12_161427_bump_version040005',1),(39,'2020_05_19_174233_config_prefer_available_xmp_metadata',1),(40,'2020_05_26_135052_bump_version040006',1),(41,'2020_06_04_104605_config_editor_enabled',1),(42,'2020_07_11_104605_config_lossless_optimization',1),(43,'2020_07_11_184605_update_licences',1),(44,'2020_08_03_162226_add_type_to_users_table',1),(45,'2020_08_03_162640_migrate_admin_to_users_table',1),(46,'2064_12_25_0000_generate_installed_log',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_contents`
--

DROP TABLE IF EXISTS `page_contents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_contents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `class` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('div','img') COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `page_contents_page_id_foreign` (`page_id`),
  CONSTRAINT `page_contents_page_id_foreign` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_contents`
--

LOCK TABLES `page_contents` WRITE;
/*!40000 ALTER TABLE `page_contents` DISABLE KEYS */;
/*!40000 ALTER TABLE `page_contents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `menu_title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `in_menu` tinyint(1) NOT NULL DEFAULT 0,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `link` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (1,'gallery','gallery',1,1,'/gallery',2,NULL,NULL);
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `photos`
--

DROP TABLE IF EXISTS `photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `photos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tags` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `public` tinyint(1) NOT NULL,
  `owner_id` int(11) NOT NULL DEFAULT 0,
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `size` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `iso` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `aperture` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `make` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `model` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `lens` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `shutter` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `focal` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `altitude` decimal(10,4) DEFAULT NULL,
  `imgDirection` decimal(10,4) DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `takestamp` timestamp NULL DEFAULT NULL,
  `star` tinyint(1) NOT NULL DEFAULT 0,
  `thumbUrl` varchar(37) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `livePhotoUrl` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `album_id` bigint(20) unsigned DEFAULT NULL,
  `checksum` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `livePhotoChecksum` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `license` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `medium` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `medium2x` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `small` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `small2x` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `thumb2x` tinyint(1) NOT NULL DEFAULT 0,
  `livePhotoContentID` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `photos_album_id_index` (`album_id`),
  CONSTRAINT `photos_album_id_foreign` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `photos`
--

LOCK TABLES `photos` WRITE;
/*!40000 ALTER TABLE `photos` DISABLE KEYS */;
/*!40000 ALTER TABLE `photos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sym_links`
--

DROP TABLE IF EXISTS `sym_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sym_links` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `photo_id` bigint(20) DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `medium` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `medium2x` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `small` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `small2x` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `thumbUrl` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `thumb2x` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sym_links`
--

LOCK TABLES `sym_links` WRITE;
/*!40000 ALTER TABLE `sym_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `sym_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_album`
--

DROP TABLE IF EXISTS `user_album`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_album` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `album_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_album_album_id_index` (`album_id`),
  KEY `user_album_user_id_index` (`user_id`),
  CONSTRAINT `user_album_album_id_foreign` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_album_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_album`
--

LOCK TABLES `user_album` WRITE;
/*!40000 ALTER TABLE `user_album` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_album` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `upload` tinyint(1) NOT NULL DEFAULT 0,
  `lock` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2020-08-04 17:58:31
