
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

#-----------------------------------------------------------------------------
#-- acl_group
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `acl_group`;


CREATE TABLE `acl_group`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`parent_id` INTEGER,
	`lft` INTEGER  NOT NULL,
	`rgt` INTEGER  NOT NULL,
	`created_at` DATETIME  NOT NULL,
	`updated_at` DATETIME  NOT NULL,
	`source_culture` VARCHAR(7)  NOT NULL,
	`serial_number` INTEGER default 0 NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `acl_group_FI_1` (`parent_id`),
	CONSTRAINT `acl_group_FK_1`
		FOREIGN KEY (`parent_id`)
		REFERENCES `acl_group` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- acl_group_i18n
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `acl_group_i18n`;


CREATE TABLE `acl_group_i18n`
(
	`name` VARCHAR(255),
	`description` TEXT,
	`id` INTEGER  NOT NULL,
	`culture` VARCHAR(7)  NOT NULL,
	PRIMARY KEY (`id`,`culture`),
	CONSTRAINT `acl_group_i18n_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `acl_group` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- acl_permission
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `acl_permission`;


CREATE TABLE `acl_permission`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`user_id` INTEGER,
	`group_id` INTEGER,
	`object_id` INTEGER,
	`action` VARCHAR(255),
	`grant_deny` INTEGER default 0 NOT NULL,
	`conditional` TEXT,
	`constants` TEXT,
	`created_at` DATETIME  NOT NULL,
	`updated_at` DATETIME  NOT NULL,
	`serial_number` INTEGER default 0 NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `acl_permission_FI_1` (`user_id`),
	CONSTRAINT `acl_permission_FK_1`
		FOREIGN KEY (`user_id`)
		REFERENCES `user` (`id`)
		ON DELETE CASCADE,
	INDEX `acl_permission_FI_2` (`group_id`),
	CONSTRAINT `acl_permission_FK_2`
		FOREIGN KEY (`group_id`)
		REFERENCES `acl_group` (`id`)
		ON DELETE CASCADE,
	INDEX `acl_permission_FI_3` (`object_id`),
	CONSTRAINT `acl_permission_FK_3`
		FOREIGN KEY (`object_id`)
		REFERENCES `object` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

#-----------------------------------------------------------------------------
#-- acl_user_group
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `acl_user_group`;


CREATE TABLE `acl_user_group`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`user_id` INTEGER  NOT NULL,
	`group_id` INTEGER  NOT NULL,
	`serial_number` INTEGER default 0 NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `acl_user_group_FI_1` (`user_id`),
	CONSTRAINT `acl_user_group_FK_1`
		FOREIGN KEY (`user_id`)
		REFERENCES `user` (`id`)
		ON DELETE CASCADE,
	INDEX `acl_user_group_FI_2` (`group_id`),
	CONSTRAINT `acl_user_group_FK_2`
		FOREIGN KEY (`group_id`)
		REFERENCES `acl_group` (`id`)
		ON DELETE CASCADE
)Engine=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
