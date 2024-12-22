/*M!999999\- enable the sandbox mode */ 
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(191) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `subject_type` varchar(191) NOT NULL,
  `subject_id` bigint(20) unsigned NOT NULL,
  `type` varchar(191) NOT NULL,
  `status` enum('planifié','en_cours','terminé','annulé') NOT NULL,
  `description` text DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `prospect_id` bigint(20) unsigned DEFAULT NULL,
  `client_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activities_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  KEY `activities_user_id_foreign` (`user_id`),
  KEY `activities_prospect_id_foreign` (`prospect_id`),
  KEY `activities_client_id_foreign` (`client_id`),
  KEY `activities_created_by_foreign` (`created_by`),
  CONSTRAINT `activities_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `activities_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `activities_prospect_id_foreign` FOREIGN KEY (`prospect_id`) REFERENCES `prospects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clients` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `prospect_id` bigint(20) unsigned DEFAULT NULL,
  `client_number` varchar(191) NOT NULL,
  `first_name` varchar(191) NOT NULL,
  `last_name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(191) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `profession` varchar(191) DEFAULT NULL,
  `education_level` varchar(191) DEFAULT NULL,
  `current_location` varchar(191) DEFAULT NULL,
  `current_field` varchar(191) DEFAULT NULL,
  `desired_field` varchar(191) DEFAULT NULL,
  `desired_destination` varchar(191) DEFAULT NULL,
  `emergency_contact` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`emergency_contact`)),
  `status` enum('active','inactive','completed') NOT NULL DEFAULT 'active',
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `commercial_code` varchar(191) DEFAULT NULL,
  `partner_id` bigint(20) unsigned DEFAULT NULL,
  `last_action_at` timestamp NULL DEFAULT NULL,
  `contract_start_date` timestamp NULL DEFAULT NULL,
  `contract_end_date` timestamp NULL DEFAULT NULL,
  `passport_number` varchar(191) DEFAULT NULL,
  `passport_expiry` timestamp NULL DEFAULT NULL,
  `visa_status` enum('not_started','in_progress','obtained','rejected') DEFAULT NULL,
  `travel_preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`travel_preferences`)),
  `payment_status` enum('pending','partial','completed') NOT NULL DEFAULT 'pending',
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clients_client_number_unique` (`client_number`),
  KEY `clients_prospect_id_foreign` (`prospect_id`),
  KEY `clients_assigned_to_foreign` (`assigned_to`),
  KEY `clients_partner_id_foreign` (`partner_id`),
  CONSTRAINT `clients_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `clients_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `clients_prospect_id_foreign` FOREIGN KEY (`prospect_id`) REFERENCES `prospects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `documentable_type` varchar(191) NOT NULL,
  `documentable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(191) NOT NULL,
  `type` enum('passport','cv','diploma','other') NOT NULL,
  `path` varchar(191) NOT NULL,
  `size` bigint(20) NOT NULL,
  `status` enum('pending','validated','rejected') NOT NULL,
  `validated_by` bigint(20) unsigned DEFAULT NULL,
  `validation_date` timestamp NULL DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `documents_documentable_type_documentable_id_index` (`documentable_type`,`documentable_id`),
  KEY `documents_validated_by_foreign` (`validated_by`),
  KEY `documents_status_index` (`status`),
  KEY `documents_type_index` (`type`),
  CONSTRAINT `documents_validated_by_foreign` FOREIGN KEY (`validated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(191) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(191) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(191) NOT NULL,
  `notifiable_type` varchar(191) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`data`)),
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(125) NOT NULL,
  `guard_name` varchar(125) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(191) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `avatar` varchar(191) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(191) NOT NULL,
  `country` varchar(191) NOT NULL,
  `bio` text DEFAULT NULL,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `profiles_user_id_foreign` (`user_id`),
  CONSTRAINT `profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `prospects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prospects` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reference_number` varchar(191) NOT NULL,
  `first_name` varchar(191) NOT NULL,
  `last_name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(191) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `profession` varchar(191) DEFAULT NULL,
  `education_level` varchar(191) DEFAULT NULL,
  `current_location` varchar(191) DEFAULT NULL,
  `current_field` varchar(191) DEFAULT NULL,
  `desired_field` varchar(191) DEFAULT NULL,
  `desired_destination` varchar(191) DEFAULT NULL,
  `emergency_contact` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`emergency_contact`)),
  `status` varchar(20) NOT NULL DEFAULT 'new',
  `source` varchar(191) DEFAULT NULL,
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `commercial_code` varchar(191) DEFAULT NULL,
  `partner_id` bigint(20) unsigned DEFAULT NULL,
  `last_action_at` timestamp NULL DEFAULT NULL,
  `analysis_deadline` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prospects_reference_number_unique` (`reference_number`),
  KEY `prospects_assigned_to_foreign` (`assigned_to`),
  KEY `prospects_partner_id_foreign` (`partner_id`),
  CONSTRAINT `prospects_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `prospects_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER check_prospect_deadline
                AFTER UPDATE ON prospects
                FOR EACH ROW
                BEGIN
                    IF NEW.analysis_deadline < NOW() + INTERVAL 24 HOUR THEN
                        INSERT INTO notifications (id, type, notifiable_type, notifiable_id, data, created_at, updated_at)
                        VALUES (
                            UUID(),
                            "App\Notifications\ProspectDeadlineApproaching",
                            "prospect",
                            NEW.id,
                            JSON_OBJECT(
                                "prospect_id", NEW.id,
                                "reference_number", NEW.reference_number,
                                "deadline", NEW.analysis_deadline
                            ),
                            NOW(),
                            NOW()
                        );
                    END IF;
                END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(125) NOT NULL,
  `guard_name` varchar(125) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `steps` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint(20) unsigned NOT NULL,
  `type` enum('document_submission','visa_application','other') NOT NULL,
  `status` enum('pending','in_progress','completed','failed') NOT NULL,
  `start_date` timestamp NOT NULL,
  `due_date` timestamp NOT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `assigned_to` bigint(20) unsigned NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `steps_client_id_foreign` (`client_id`),
  KEY `steps_assigned_to_foreign` (`assigned_to`),
  CONSTRAINT `steps_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  CONSTRAINT `steps_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(191) NOT NULL DEFAULT 'user',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

/*M!999999\- enable the sandbox mode */ 
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2014_10_12_000000_create_users_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2014_10_12_100000_create_password_reset_tokens_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2019_08_19_000000_create_failed_jobs_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2019_12_14_000001_create_personal_access_tokens_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2023_11_09_093406_create_activities_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2023_11_09_093408_create_prospects_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2023_11_09_093409_create_clients_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2024_01_09_000001_add_user_id_to_activities_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2024_12_12_134500_create_permission_tables',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2024_12_12_143755_create_profiles_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2024_12_12_145426_create_documents_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2024_12_12_145602_create_notifications_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2024_12_12_145625_create_steps_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2024_12_12_220800_add_title_and_status_to_activities_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2024_12_12_221800_add_missing_columns_to_activities_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2024_12_12_224200_add_client_id_to_activities_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2024_12_12_224600_fix_activities_table_columns',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2024_12_12_224700_remove_duplicate_activities_columns',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2024_12_12_224800_update_activities_table_structure',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2024_12_12_224900_ensure_activities_table_structure',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2024_12_12_225000_finalize_activities_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2024_12_12_224500_add_subject_fields_to_activities_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2024_12_16_111200_fix_user_fields_in_activities',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2024_12_16_113500_add_role_to_users',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2024_12_16_113600_fix_subject_id_in_activities',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2024_12_16_135700_add_created_by_to_activities',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2024_12_19_094727_add_notes_to_prospects_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2024_12_19_114029_update_status_column_in_prospects_table',5);
