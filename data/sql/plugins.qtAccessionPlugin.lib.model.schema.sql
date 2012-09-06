
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

#-----------------------------------------------------------------------------
#-- accession
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `accession`;


CREATE TABLE `accession`
(
	`id` INTEGER  NOT NULL,
	`acquisition_type_id` INTEGER,
	`date` DATE,
	`identifier` VARCHAR(255),
	`processing_priority_id` INTEGER,
	`processing_status_id` INTEGER,
	`resource_type_id` INTEGER,
	`created_at` DATETIME  NOT NULL,
	`updated_at` DATETIME  NOT NULL,
	`source_culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `accession_U_1` (`identifier`),
	CONSTRAINT `accession_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `object` (`id`)
		ON DELETE CASCADE,
	INDEX `accession_FI_2` (`acquisition_type_id`),
	CONSTRAINT `accession_FK_2`
		FOREIGN KEY (`acquisition_type_id`)
		REFERENCES `term` (`id`)
		ON DELETE SET NULL,
	INDEX `accession_FI_3` (`processing_priority_id`),
	CONSTRAINT `accession_FK_3`
		FOREIGN KEY (`processing_priority_id`)
		REFERENCES `term` (`id`)
		ON DELETE SET NULL,
	INDEX `accession_FI_4` (`processing_status_id`),
	CONSTRAINT `accession_FK_4`
		FOREIGN KEY (`processing_status_id`)
		REFERENCES `term` (`id`)
		ON DELETE SET NULL,
	INDEX `accession_FI_5` (`resource_type_id`),
	CONSTRAINT `accession_FK_5`
		FOREIGN KEY (`resource_type_id`)
		REFERENCES `term` (`id`)
		ON DELETE SET NULL
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- accession_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `accession_i18n`;


CREATE TABLE `accession_i18n`
(
	`appraisal` TEXT,
	`archival_history` TEXT,
	`location_information` TEXT,
	`physical_characteristics` TEXT,
	`processing_notes` TEXT,
	`received_extent_units` TEXT,
	`scope_and_content` TEXT,
	`source_of_acquisition` TEXT,
	`title` VARCHAR(255),
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `accession_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `accession` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- deaccession
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `deaccession`;


CREATE TABLE `deaccession`
(
	`id` INTEGER  NOT NULL,
	`accession_id` INTEGER,
	`date` DATE,
	`identifier` VARCHAR(255),
	`scope_id` INTEGER,
	`created_at` DATETIME  NOT NULL,
	`updated_at` DATETIME  NOT NULL,
	`source_culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`),
	CONSTRAINT `deaccession_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `object` (`id`)
		ON DELETE CASCADE,
	INDEX `deaccession_FI_2` (`accession_id`),
	CONSTRAINT `deaccession_FK_2`
		FOREIGN KEY (`accession_id`)
		REFERENCES `accession` (`id`)
		ON DELETE CASCADE,
	INDEX `deaccession_FI_3` (`scope_id`),
	CONSTRAINT `deaccession_FK_3`
		FOREIGN KEY (`scope_id`)
		REFERENCES `term` (`id`)
		ON DELETE SET NULL
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- deaccession_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `deaccession_i18n`;


CREATE TABLE `deaccession_i18n`
(
	`description` TEXT,
	`extent` TEXT,
	`reason` TEXT,
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `deaccession_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `deaccession` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- donor
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `donor`;


CREATE TABLE `donor`
(
	`id` INTEGER  NOT NULL,
	PRIMARY KEY (`id`),
	CONSTRAINT `donor_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `actor` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
