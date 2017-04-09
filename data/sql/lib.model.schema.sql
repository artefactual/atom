
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

#-----------------------------------------------------------------------------
#-- access_log
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `access_log`;


CREATE TABLE `access_log`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`object_id` INTEGER  NOT NULL,
	`access_date` DATETIME,
	PRIMARY KEY (`id`),
	KEY `1`(`access_date`, `object_id`),
	INDEX `access_log_FI_1` (`object_id`),
	CONSTRAINT `access_log_FK_1`
		FOREIGN KEY (`object_id`)
		REFERENCES `object` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- actor
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `actor`;


CREATE TABLE `actor`
(
	`id` INTEGER  NOT NULL,
	`corporate_body_identifiers` VARCHAR(1024),
	`entity_type_id` INTEGER,
	`description_status_id` INTEGER,
	`description_detail_id` INTEGER,
	`description_identifier` VARCHAR(1024),
	`source_standard` VARCHAR(1024),
	`parent_id` INTEGER,
	`lft` INTEGER  NOT NULL,
	`rgt` INTEGER  NOT NULL,
	`source_culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`),
	CONSTRAINT `actor_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `object` (`id`)
		ON DELETE CASCADE,
	INDEX `actor_FI_2` (`entity_type_id`),
	CONSTRAINT `actor_FK_2`
		FOREIGN KEY (`entity_type_id`)
		REFERENCES `term` (`id`)
		ON DELETE SET NULL,
	INDEX `actor_FI_3` (`description_status_id`),
	CONSTRAINT `actor_FK_3`
		FOREIGN KEY (`description_status_id`)
		REFERENCES `term` (`id`)
		ON DELETE SET NULL,
	INDEX `actor_FI_4` (`description_detail_id`),
	CONSTRAINT `actor_FK_4`
		FOREIGN KEY (`description_detail_id`)
		REFERENCES `term` (`id`)
		ON DELETE SET NULL,
	INDEX `actor_FI_5` (`parent_id`),
	CONSTRAINT `actor_FK_5`
		FOREIGN KEY (`parent_id`)
		REFERENCES `actor` (`id`)
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- actor_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `actor_i18n`;


CREATE TABLE `actor_i18n`
(
	`authorized_form_of_name` VARCHAR(1024),
	`dates_of_existence` VARCHAR(1024),
	`history` TEXT,
	`places` TEXT,
	`legal_status` TEXT,
	`functions` TEXT,
	`mandates` TEXT,
	`internal_structures` TEXT,
	`general_context` TEXT,
	`institution_responsible_identifier` VARCHAR(1024),
	`rules` TEXT,
	`sources` TEXT,
	`revision_history` TEXT,
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `actor_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `actor` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- aip
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `aip`;


CREATE TABLE `aip`
(
	`id` INTEGER  NOT NULL,
	`type_id` INTEGER,
	`uuid` VARCHAR(36),
	`filename` VARCHAR(1024),
	`size_on_disk` BIGINT,
	`digital_object_count` INTEGER,
	`created_at` DATETIME,
	`part_of` INTEGER,
	PRIMARY KEY (`id`),
	CONSTRAINT `aip_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `object` (`id`)
		ON DELETE CASCADE,
	INDEX `aip_FI_2` (`type_id`),
	CONSTRAINT `aip_FK_2`
		FOREIGN KEY (`type_id`)
		REFERENCES `term` (`id`)
		ON DELETE SET NULL,
	INDEX `aip_FI_3` (`part_of`),
	CONSTRAINT `aip_FK_3`
		FOREIGN KEY (`part_of`)
		REFERENCES `object` (`id`)
		ON DELETE SET NULL
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- job
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `job`;


CREATE TABLE `job`
(
	`id` INTEGER  NOT NULL,
	`name` VARCHAR(255),
	`download_path` TEXT,
	`completed_at` DATETIME,
	`user_id` INTEGER,
	`object_id` INTEGER,
	`status_id` INTEGER,
	`output` TEXT,
	PRIMARY KEY (`id`),
	CONSTRAINT `job_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `object` (`id`)
		ON DELETE CASCADE,
	INDEX `job_FI_2` (`user_id`),
	CONSTRAINT `job_FK_2`
		FOREIGN KEY (`user_id`)
		REFERENCES `user` (`id`)
		ON DELETE SET NULL,
	INDEX `job_FI_3` (`object_id`),
	CONSTRAINT `job_FK_3`
		FOREIGN KEY (`object_id`)
		REFERENCES `object` (`id`)
		ON DELETE SET NULL,
	INDEX `job_FI_4` (`status_id`),
	CONSTRAINT `job_FK_4`
		FOREIGN KEY (`status_id`)
		REFERENCES `term` (`id`)
		ON DELETE SET NULL
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- contact_information
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `contact_information`;


CREATE TABLE `contact_information`
(
	`actor_id` INTEGER  NOT NULL,
	`primary_contact` TINYINT,
	`contact_person` VARCHAR(1024),
	`street_address` TEXT,
	`website` VARCHAR(1024),
	`email` VARCHAR(255),
	`telephone` VARCHAR(255),
	`fax` VARCHAR(255),
	`postal_code` VARCHAR(255),
	`country_code` VARCHAR(255),
	`longitude` FLOAT,
	`latitude` FLOAT,
	`created_at` DATETIME  NOT NULL,
	`updated_at` DATETIME  NOT NULL,
	`source_culture` VARCHAR(7)  NOT NULL,
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`serial_number` INTEGER default 0 NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `contact_information_FI_1` (`actor_id`),
	CONSTRAINT `contact_information_FK_1`
		FOREIGN KEY (`actor_id`)
		REFERENCES `actor` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- contact_information_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `contact_information_i18n`;


CREATE TABLE `contact_information_i18n`
(
	`contact_type` VARCHAR(1024),
	`city` VARCHAR(1024),
	`region` VARCHAR(1024),
	`note` TEXT,
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `contact_information_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `contact_information` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- digital_object
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `digital_object`;


CREATE TABLE `digital_object`
(
	`id` INTEGER  NOT NULL,
	`information_object_id` INTEGER,
	`usage_id` INTEGER,
	`mime_type` VARCHAR(255),
	`media_type_id` INTEGER,
	`name` VARCHAR(1024)  NOT NULL,
	`path` VARCHAR(1024)  NOT NULL,
	`sequence` INTEGER,
	`byte_size` INTEGER,
	`checksum` VARCHAR(255),
	`checksum_type` VARCHAR(50),
	`parent_id` INTEGER,
	PRIMARY KEY (`id`),
	CONSTRAINT `digital_object_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `object` (`id`)
		ON DELETE CASCADE,
	INDEX `digital_object_FI_2` (`information_object_id`),
	CONSTRAINT `digital_object_FK_2`
		FOREIGN KEY (`information_object_id`)
		REFERENCES `information_object` (`id`),
	INDEX `digital_object_FI_3` (`usage_id`),
	CONSTRAINT `digital_object_FK_3`
		FOREIGN KEY (`usage_id`)
		REFERENCES `term` (`id`)
		ON DELETE SET NULL,
	INDEX `digital_object_FI_4` (`media_type_id`),
	CONSTRAINT `digital_object_FK_4`
		FOREIGN KEY (`media_type_id`)
		REFERENCES `term` (`id`)
		ON DELETE SET NULL,
	INDEX `digital_object_FI_5` (`parent_id`),
	CONSTRAINT `digital_object_FK_5`
		FOREIGN KEY (`parent_id`)
		REFERENCES `digital_object` (`id`)
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- event
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `event`;


CREATE TABLE `event`
(
	`id` INTEGER  NOT NULL,
	`start_date` DATE,
	`start_time` TIME,
	`end_date` DATE,
	`end_time` TIME,
	`type_id` INTEGER  NOT NULL,
	`object_id` INTEGER,
	`actor_id` INTEGER,
	`source_culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`),
	CONSTRAINT `event_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `object` (`id`)
		ON DELETE CASCADE,
	INDEX `event_FI_2` (`type_id`),
	CONSTRAINT `event_FK_2`
		FOREIGN KEY (`type_id`)
		REFERENCES `term` (`id`)
		ON DELETE CASCADE,
	INDEX `event_FI_3` (`object_id`),
	CONSTRAINT `event_FK_3`
		FOREIGN KEY (`object_id`)
		REFERENCES `object` (`id`)
		ON DELETE CASCADE,
	INDEX `event_FI_4` (`actor_id`),
	CONSTRAINT `event_FK_4`
		FOREIGN KEY (`actor_id`)
		REFERENCES `actor` (`id`)
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- event_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `event_i18n`;


CREATE TABLE `event_i18n`
(
	`name` VARCHAR(1024),
	`description` TEXT,
	`date` VARCHAR(1024),
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `event_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `event` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- function
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `function`;


CREATE TABLE `function`
(
	`id` INTEGER  NOT NULL,
	`type_id` INTEGER,
	`parent_id` INTEGER,
	`description_status_id` INTEGER,
	`description_detail_id` INTEGER,
	`description_identifier` VARCHAR(1024),
	`source_standard` VARCHAR(1024),
	`lft` INTEGER,
	`rgt` INTEGER,
	`source_culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`),
	CONSTRAINT `function_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `object` (`id`)
		ON DELETE CASCADE,
	INDEX `function_FI_2` (`type_id`),
	CONSTRAINT `function_FK_2`
		FOREIGN KEY (`type_id`)
		REFERENCES `term` (`id`),
	INDEX `function_FI_3` (`parent_id`),
	CONSTRAINT `function_FK_3`
		FOREIGN KEY (`parent_id`)
		REFERENCES `function` (`id`),
	INDEX `function_FI_4` (`description_status_id`),
	CONSTRAINT `function_FK_4`
		FOREIGN KEY (`description_status_id`)
		REFERENCES `term` (`id`),
	INDEX `function_FI_5` (`description_detail_id`),
	CONSTRAINT `function_FK_5`
		FOREIGN KEY (`description_detail_id`)
		REFERENCES `term` (`id`)
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- function_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `function_i18n`;


CREATE TABLE `function_i18n`
(
	`authorized_form_of_name` VARCHAR(1024),
	`classification` VARCHAR(1024),
	`dates` VARCHAR(1024),
	`description` TEXT,
	`history` TEXT,
	`legislation` TEXT,
	`institution_identifier` TEXT,
	`revision_history` TEXT,
	`rules` TEXT,
	`sources` TEXT,
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `function_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `function` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- information_object
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `information_object`;


CREATE TABLE `information_object`
(
	`id` INTEGER  NOT NULL,
	`identifier` VARCHAR(1024),
	`oai_local_identifier` INTEGER  NOT NULL AUTO_INCREMENT,
	`level_of_description_id` INTEGER,
	`collection_type_id` INTEGER,
	`repository_id` INTEGER,
	`parent_id` INTEGER,
	`description_status_id` INTEGER,
	`description_detail_id` INTEGER,
	`description_identifier` VARCHAR(1024),
	`source_standard` VARCHAR(1024),
	`display_standard_id` INTEGER,
	`lft` INTEGER  NOT NULL,
	`rgt` INTEGER  NOT NULL,
	`source_culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `information_object_U_1` (`oai_local_identifier`),
	KEY `lft`(`lft`),
	CONSTRAINT `information_object_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `object` (`id`)
		ON DELETE CASCADE,
	INDEX `information_object_FI_2` (`level_of_description_id`),
	CONSTRAINT `information_object_FK_2`
		FOREIGN KEY (`level_of_description_id`)
		REFERENCES `term` (`id`)
		ON DELETE SET NULL,
	INDEX `information_object_FI_3` (`collection_type_id`),
	CONSTRAINT `information_object_FK_3`
		FOREIGN KEY (`collection_type_id`)
		REFERENCES `term` (`id`),
	INDEX `information_object_FI_4` (`repository_id`),
	CONSTRAINT `information_object_FK_4`
		FOREIGN KEY (`repository_id`)
		REFERENCES `repository` (`id`),
	INDEX `information_object_FI_5` (`parent_id`),
	CONSTRAINT `information_object_FK_5`
		FOREIGN KEY (`parent_id`)
		REFERENCES `information_object` (`id`),
	INDEX `information_object_FI_6` (`description_status_id`),
	CONSTRAINT `information_object_FK_6`
		FOREIGN KEY (`description_status_id`)
		REFERENCES `term` (`id`)
		ON DELETE SET NULL,
	INDEX `information_object_FI_7` (`description_detail_id`),
	CONSTRAINT `information_object_FK_7`
		FOREIGN KEY (`description_detail_id`)
		REFERENCES `term` (`id`)
		ON DELETE SET NULL,
	INDEX `information_object_FI_8` (`display_standard_id`),
	CONSTRAINT `information_object_FK_8`
		FOREIGN KEY (`display_standard_id`)
		REFERENCES `term` (`id`)
		ON DELETE SET NULL
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- information_object_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `information_object_i18n`;


CREATE TABLE `information_object_i18n`
(
	`title` VARCHAR(1024),
	`alternate_title` VARCHAR(1024),
	`edition` VARCHAR(1024),
	`extent_and_medium` TEXT,
	`archival_history` TEXT,
	`acquisition` TEXT,
	`scope_and_content` TEXT,
	`appraisal` TEXT,
	`accruals` TEXT,
	`arrangement` TEXT,
	`access_conditions` TEXT,
	`reproduction_conditions` TEXT,
	`physical_characteristics` TEXT,
	`finding_aids` TEXT,
	`location_of_originals` TEXT,
	`location_of_copies` TEXT,
	`related_units_of_description` TEXT,
	`institution_responsible_identifier` VARCHAR(1024),
	`rules` TEXT,
	`sources` TEXT,
	`revision_history` TEXT,
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `information_object_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `information_object` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- keymap
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `keymap`;


CREATE TABLE `keymap`
(
	`source_id` TEXT,
	`target_id` INTEGER,
	`source_name` TEXT,
	`target_name` TEXT,
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`serial_number` INTEGER default 0 NOT NULL,
	PRIMARY KEY (`id`)
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- menu
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `menu`;


CREATE TABLE `menu`
(
	`parent_id` INTEGER,
	`name` VARCHAR(255),
	`path` VARCHAR(255),
	`lft` INTEGER  NOT NULL,
	`rgt` INTEGER  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	`updated_at` DATETIME  NOT NULL,
	`source_culture` VARCHAR(7)  NOT NULL,
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`serial_number` INTEGER default 0 NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `menu_FI_1` (`parent_id`),
	CONSTRAINT `menu_FK_1`
		FOREIGN KEY (`parent_id`)
		REFERENCES `menu` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- menu_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `menu_i18n`;


CREATE TABLE `menu_i18n`
(
	`label` VARCHAR(255),
	`description` TEXT,
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `menu_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `menu` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- note
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `note`;


CREATE TABLE `note`
(
	`object_id` INTEGER  NOT NULL,
	`type_id` INTEGER,
	`scope` VARCHAR(1024),
	`user_id` INTEGER,
	`source_culture` VARCHAR(7)  NOT NULL,
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`serial_number` INTEGER default 0 NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `note_FI_1` (`object_id`),
	CONSTRAINT `note_FK_1`
		FOREIGN KEY (`object_id`)
		REFERENCES `object` (`id`)
		ON DELETE CASCADE,
	INDEX `note_FI_2` (`type_id`),
	CONSTRAINT `note_FK_2`
		FOREIGN KEY (`type_id`)
		REFERENCES `term` (`id`)
		ON DELETE SET NULL,
	INDEX `note_FI_3` (`user_id`),
	CONSTRAINT `note_FK_3`
		FOREIGN KEY (`user_id`)
		REFERENCES `user` (`id`)
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- note_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `note_i18n`;


CREATE TABLE `note_i18n`
(
	`content` TEXT,
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `note_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `note` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- oai_harvest
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `oai_harvest`;


CREATE TABLE `oai_harvest`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`oai_repository_id` INTEGER  NOT NULL,
	`start_timestamp` DATETIME,
	`end_timestamp` DATETIME,
	`last_harvest` DATETIME,
	`last_harvest_attempt` DATETIME,
	`metadataPrefix` VARCHAR(255),
	`set` VARCHAR(1024),
	`created_at` DATETIME  NOT NULL,
	`serial_number` INTEGER default 0 NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `oai_harvest_FI_1` (`oai_repository_id`),
	CONSTRAINT `oai_harvest_FK_1`
		FOREIGN KEY (`oai_repository_id`)
		REFERENCES `oai_repository` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- oai_repository
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `oai_repository`;


CREATE TABLE `oai_repository`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(1024),
	`uri` VARCHAR(1024),
	`admin_email` VARCHAR(255),
	`earliest_timestamp` DATETIME,
	`created_at` DATETIME  NOT NULL,
	`updated_at` DATETIME  NOT NULL,
	`serial_number` INTEGER default 0 NOT NULL,
	PRIMARY KEY (`id`)
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- object
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `object`;


CREATE TABLE `object`
(
	`class_name` VARCHAR(255),
	`created_at` DATETIME  NOT NULL,
	`updated_at` DATETIME  NOT NULL,
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`serial_number` INTEGER default 0 NOT NULL,
	PRIMARY KEY (`id`)
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- object_term_relation
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `object_term_relation`;


CREATE TABLE `object_term_relation`
(
	`id` INTEGER  NOT NULL,
	`object_id` INTEGER  NOT NULL,
	`term_id` INTEGER  NOT NULL,
	`start_date` DATE,
	`end_date` DATE,
	PRIMARY KEY (`id`),
	CONSTRAINT `object_term_relation_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `object` (`id`)
		ON DELETE CASCADE,
	INDEX `object_term_relation_FI_2` (`object_id`),
	CONSTRAINT `object_term_relation_FK_2`
		FOREIGN KEY (`object_id`)
		REFERENCES `object` (`id`)
		ON DELETE CASCADE,
	INDEX `object_term_relation_FI_3` (`term_id`),
	CONSTRAINT `object_term_relation_FK_3`
		FOREIGN KEY (`term_id`)
		REFERENCES `term` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- other_name
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `other_name`;


CREATE TABLE `other_name`
(
	`object_id` INTEGER  NOT NULL,
	`type_id` INTEGER,
	`start_date` DATE,
	`end_date` DATE,
	`source_culture` VARCHAR(7)  NOT NULL,
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`serial_number` INTEGER default 0 NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `other_name_FI_1` (`object_id`),
	CONSTRAINT `other_name_FK_1`
		FOREIGN KEY (`object_id`)
		REFERENCES `object` (`id`)
		ON DELETE CASCADE,
	INDEX `other_name_FI_2` (`type_id`),
	CONSTRAINT `other_name_FK_2`
		FOREIGN KEY (`type_id`)
		REFERENCES `term` (`id`)
		ON DELETE SET NULL
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- other_name_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `other_name_i18n`;


CREATE TABLE `other_name_i18n`
(
	`name` VARCHAR(1024),
	`note` VARCHAR(1024),
	`dates` TEXT,
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `other_name_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `other_name` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- physical_object
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `physical_object`;


CREATE TABLE `physical_object`
(
	`id` INTEGER  NOT NULL,
	`type_id` INTEGER,
	`parent_id` INTEGER,
	`lft` INTEGER  NOT NULL,
	`rgt` INTEGER  NOT NULL,
	`source_culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`),
	CONSTRAINT `physical_object_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `object` (`id`)
		ON DELETE CASCADE,
	INDEX `physical_object_FI_2` (`type_id`),
	CONSTRAINT `physical_object_FK_2`
		FOREIGN KEY (`type_id`)
		REFERENCES `term` (`id`)
		ON DELETE SET NULL,
	INDEX `physical_object_FI_3` (`parent_id`),
	CONSTRAINT `physical_object_FK_3`
		FOREIGN KEY (`parent_id`)
		REFERENCES `physical_object` (`id`)
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- physical_object_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `physical_object_i18n`;


CREATE TABLE `physical_object_i18n`
(
	`name` VARCHAR(1024),
	`description` TEXT,
	`location` TEXT,
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `physical_object_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `physical_object` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- premis_object
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `premis_object`;


CREATE TABLE `premis_object`
(
	`id` INTEGER  NOT NULL,
	`information_object_id` INTEGER,
	`puid` VARCHAR(255),
	`filename` VARCHAR(1024),
	`last_modified` DATETIME,
	`date_ingested` DATE,
	`size` INTEGER,
	`mime_type` VARCHAR(255),
	PRIMARY KEY (`id`),
	CONSTRAINT `premis_object_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `object` (`id`)
		ON DELETE CASCADE,
	INDEX `premis_object_FI_2` (`information_object_id`),
	CONSTRAINT `premis_object_FK_2`
		FOREIGN KEY (`information_object_id`)
		REFERENCES `information_object` (`id`)
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- property
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `property`;


CREATE TABLE `property`
(
	`object_id` INTEGER  NOT NULL,
	`scope` VARCHAR(1024),
	`name` VARCHAR(1024),
	`source_culture` VARCHAR(7)  NOT NULL,
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`serial_number` INTEGER default 0 NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `property_FI_1` (`object_id`),
	CONSTRAINT `property_FK_1`
		FOREIGN KEY (`object_id`)
		REFERENCES `object` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- property_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `property_i18n`;


CREATE TABLE `property_i18n`
(
	`value` TEXT,
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `property_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `property` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- relation
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `relation`;


CREATE TABLE `relation`
(
	`id` INTEGER  NOT NULL,
	`subject_id` INTEGER  NOT NULL,
	`object_id` INTEGER  NOT NULL,
	`type_id` INTEGER,
	`start_date` DATE,
	`end_date` DATE,
	`source_culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`),
	CONSTRAINT `relation_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `object` (`id`)
		ON DELETE CASCADE,
	INDEX `relation_FI_2` (`subject_id`),
	CONSTRAINT `relation_FK_2`
		FOREIGN KEY (`subject_id`)
		REFERENCES `object` (`id`),
	INDEX `relation_FI_3` (`object_id`),
	CONSTRAINT `relation_FK_3`
		FOREIGN KEY (`object_id`)
		REFERENCES `object` (`id`),
	INDEX `relation_FI_4` (`type_id`),
	CONSTRAINT `relation_FK_4`
		FOREIGN KEY (`type_id`)
		REFERENCES `term` (`id`)
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- relation_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `relation_i18n`;


CREATE TABLE `relation_i18n`
(
	`description` TEXT,
	`date` VARCHAR(1024),
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `relation_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `relation` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- repository
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `repository`;


CREATE TABLE `repository`
(
	`id` INTEGER  NOT NULL,
	`identifier` VARCHAR(1024),
	`desc_status_id` INTEGER,
	`desc_detail_id` INTEGER,
	`desc_identifier` VARCHAR(1024),
	`upload_limit` FLOAT,
	`source_culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`),
	CONSTRAINT `repository_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `actor` (`id`)
		ON DELETE CASCADE,
	INDEX `repository_FI_2` (`desc_status_id`),
	CONSTRAINT `repository_FK_2`
		FOREIGN KEY (`desc_status_id`)
		REFERENCES `term` (`id`)
		ON DELETE SET NULL,
	INDEX `repository_FI_3` (`desc_detail_id`),
	CONSTRAINT `repository_FK_3`
		FOREIGN KEY (`desc_detail_id`)
		REFERENCES `term` (`id`)
		ON DELETE SET NULL
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- repository_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `repository_i18n`;


CREATE TABLE `repository_i18n`
(
	`geocultural_context` TEXT,
	`collecting_policies` TEXT,
	`buildings` TEXT,
	`holdings` TEXT,
	`finding_aids` TEXT,
	`opening_times` TEXT,
	`access_conditions` TEXT,
	`disabled_access` TEXT,
	`research_services` TEXT,
	`reproduction_services` TEXT,
	`public_facilities` TEXT,
	`desc_institution_identifier` VARCHAR(1024),
	`desc_rules` TEXT,
	`desc_sources` TEXT,
	`desc_revision_history` TEXT,
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `repository_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `repository` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- rights
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `rights`;


CREATE TABLE `rights`
(
	`id` INTEGER  NOT NULL,
	`start_date` DATE,
	`end_date` DATE,
	`basis_id` INTEGER,
	`rights_holder_id` INTEGER,
	`copyright_status_id` INTEGER,
	`copyright_status_date` DATE,
	`copyright_jurisdiction` VARCHAR(1024),
	`statute_determination_date` DATE,
	`statute_citation_id` INTEGER,
	`source_culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`),
	CONSTRAINT `rights_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `object` (`id`)
		ON DELETE CASCADE,
	INDEX `rights_FI_2` (`basis_id`),
	CONSTRAINT `rights_FK_2`
		FOREIGN KEY (`basis_id`)
		REFERENCES `term` (`id`)
		ON DELETE SET NULL,
	INDEX `rights_FI_3` (`rights_holder_id`),
	CONSTRAINT `rights_FK_3`
		FOREIGN KEY (`rights_holder_id`)
		REFERENCES `actor` (`id`)
		ON DELETE SET NULL,
	INDEX `rights_FI_4` (`copyright_status_id`),
	CONSTRAINT `rights_FK_4`
		FOREIGN KEY (`copyright_status_id`)
		REFERENCES `term` (`id`)
		ON DELETE SET NULL,
	INDEX `rights_FI_5` (`statute_citation_id`),
	CONSTRAINT `rights_FK_5`
		FOREIGN KEY (`statute_citation_id`)
		REFERENCES `term` (`id`)
		ON DELETE SET NULL
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- granted_right
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `granted_right`;


CREATE TABLE `granted_right`
(
	`rights_id` INTEGER  NOT NULL,
	`act_id` INTEGER,
	`restriction` TINYINT default 1,
	`start_date` DATE,
	`end_date` DATE,
	`notes` TEXT,
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`serial_number` INTEGER default 0 NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `granted_right_FI_1` (`rights_id`),
	CONSTRAINT `granted_right_FK_1`
		FOREIGN KEY (`rights_id`)
		REFERENCES `rights` (`id`)
		ON DELETE CASCADE,
	INDEX `granted_right_FI_2` (`act_id`),
	CONSTRAINT `granted_right_FK_2`
		FOREIGN KEY (`act_id`)
		REFERENCES `term` (`id`)
		ON DELETE SET NULL
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- rights_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `rights_i18n`;


CREATE TABLE `rights_i18n`
(
	`rights_note` TEXT,
	`copyright_note` TEXT,
	`identifier_value` TEXT,
	`identifier_type` TEXT,
	`identifier_role` TEXT,
	`license_terms` TEXT,
	`license_note` TEXT,
	`statute_jurisdiction` TEXT,
	`statute_note` TEXT,
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `rights_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `rights` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- rights_holder
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `rights_holder`;


CREATE TABLE `rights_holder`
(
	`id` INTEGER  NOT NULL,
	PRIMARY KEY (`id`),
	CONSTRAINT `rights_holder_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `actor` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- setting
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `setting`;


CREATE TABLE `setting`
(
	`name` VARCHAR(255),
	`scope` VARCHAR(255),
	`editable` TINYINT default 0,
	`deleteable` TINYINT default 0,
	`source_culture` VARCHAR(7)  NOT NULL,
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`serial_number` INTEGER default 0 NOT NULL,
	PRIMARY KEY (`id`)
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- setting_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `setting_i18n`;


CREATE TABLE `setting_i18n`
(
	`value` TEXT,
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `setting_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `setting` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- slug
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `slug`;


CREATE TABLE `slug`
(
	`object_id` INTEGER  NOT NULL,
	`slug` VARCHAR(255)  NOT NULL,
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`serial_number` INTEGER default 0 NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `slug_U_1` (`object_id`),
	UNIQUE KEY `slug_U_2` (`slug`),
	CONSTRAINT `slug_FK_1`
		FOREIGN KEY (`object_id`)
		REFERENCES `object` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- static_page
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `static_page`;


CREATE TABLE `static_page`
(
	`id` INTEGER  NOT NULL,
	`source_culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`),
	CONSTRAINT `static_page_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `object` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- static_page_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `static_page_i18n`;


CREATE TABLE `static_page_i18n`
(
	`title` VARCHAR(1024),
	`content` TEXT,
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `static_page_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `static_page` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- status
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `status`;


CREATE TABLE `status`
(
	`object_id` INTEGER  NOT NULL,
	`type_id` INTEGER,
	`status_id` INTEGER,
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`serial_number` INTEGER default 0 NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `status_FI_1` (`object_id`),
	CONSTRAINT `status_FK_1`
		FOREIGN KEY (`object_id`)
		REFERENCES `object` (`id`)
		ON DELETE CASCADE,
	INDEX `status_FI_2` (`type_id`),
	CONSTRAINT `status_FK_2`
		FOREIGN KEY (`type_id`)
		REFERENCES `term` (`id`)
		ON DELETE CASCADE,
	INDEX `status_FI_3` (`status_id`),
	CONSTRAINT `status_FK_3`
		FOREIGN KEY (`status_id`)
		REFERENCES `term` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- taxonomy
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `taxonomy`;


CREATE TABLE `taxonomy`
(
	`id` INTEGER  NOT NULL,
	`usage` VARCHAR(1024),
	`parent_id` INTEGER,
	`lft` INTEGER  NOT NULL,
	`rgt` INTEGER  NOT NULL,
	`source_culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`),
	CONSTRAINT `taxonomy_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `object` (`id`)
		ON DELETE CASCADE,
	INDEX `taxonomy_FI_2` (`parent_id`),
	CONSTRAINT `taxonomy_FK_2`
		FOREIGN KEY (`parent_id`)
		REFERENCES `taxonomy` (`id`)
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- taxonomy_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `taxonomy_i18n`;


CREATE TABLE `taxonomy_i18n`
(
	`name` VARCHAR(1024),
	`note` TEXT,
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `taxonomy_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `taxonomy` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- term
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `term`;


CREATE TABLE `term`
(
	`id` INTEGER  NOT NULL,
	`taxonomy_id` INTEGER  NOT NULL,
	`code` VARCHAR(1024),
	`parent_id` INTEGER,
	`lft` INTEGER  NOT NULL,
	`rgt` INTEGER  NOT NULL,
	`source_culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `lft`(`lft`),
	CONSTRAINT `term_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `object` (`id`)
		ON DELETE CASCADE,
	INDEX `term_FI_2` (`taxonomy_id`),
	CONSTRAINT `term_FK_2`
		FOREIGN KEY (`taxonomy_id`)
		REFERENCES `taxonomy` (`id`)
		ON DELETE CASCADE,
	INDEX `term_FI_3` (`parent_id`),
	CONSTRAINT `term_FK_3`
		FOREIGN KEY (`parent_id`)
		REFERENCES `term` (`id`)
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- term_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `term_i18n`;


CREATE TABLE `term_i18n`
(
	`name` VARCHAR(1024),
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `term_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `term` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- user
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `user`;


CREATE TABLE `user`
(
	`id` INTEGER  NOT NULL,
	`username` VARCHAR(255),
	`email` VARCHAR(255),
	`sha1_password` VARCHAR(255),
	`salt` VARCHAR(255),
	`active` TINYINT default 1,
	PRIMARY KEY (`id`),
	CONSTRAINT `user_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `actor` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
