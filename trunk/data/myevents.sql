SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE SCHEMA IF NOT EXISTS `myevents` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `myevents` ;

-- -----------------------------------------------------
-- Table `myevents`.`category`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `myevents`.`category` (
  `category_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NULL ,
  PRIMARY KEY (`category_id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `myevents`.`event`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `myevents`.`event` (
  `event_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL ,
  `location` VARCHAR(45) NULL ,
  `timestart` DATETIME NULL ,
  `timeend` DATETIME NULL ,
  `shortinfo` TEXT NULL ,
  `longinfo` LONGTEXT NULL ,
  `active` TINYINT(1) NOT NULL DEFAULT true ,
  `public` TINYINT(1) NOT NULL DEFAULT true ,
  `url` VARCHAR(45) NULL ,
  `fburl` VARCHAR(45) NULL ,
  `category_id` INT UNSIGNED NOT NULL ,
  `capacity` INT NULL ,
  PRIMARY KEY (`event_id`) ,
  INDEX `fk_event_category1_idx` (`category_id` ASC) ,
  CONSTRAINT `fk_event_category1`
    FOREIGN KEY (`category_id` )
    REFERENCES `myevents`.`category` (`category_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `myevents`.`user`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `myevents`.`user` (
  `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `email` VARCHAR(100) NOT NULL ,
  `first_name` VARCHAR(100) NULL ,
  `last_name` VARCHAR(100) NULL ,
  `last_login_date` DATETIME NULL ,
  `last_login_ip` VARCHAR(15) NULL ,
  PRIMARY KEY (`user_id`) ,
  UNIQUE INDEX `email_UNIQUE` (`email` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `myevents`.`attendance`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `myevents`.`attendance` (
  `user_id` INT UNSIGNED NOT NULL ,
  `event_id` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`user_id`, `event_id`) ,
  INDEX `fk_users_has_events_events1_idx` (`event_id` ASC) ,
  INDEX `fk_users_has_events_users_idx` (`user_id` ASC) ,
  CONSTRAINT `fk_users_has_events_users`
    FOREIGN KEY (`user_id` )
    REFERENCES `myevents`.`user` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_has_events_events1`
    FOREIGN KEY (`event_id` )
    REFERENCES `myevents`.`event` (`event_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `myevents`.`organization`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `myevents`.`organization` (
  `organization_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL ,
  `website` VARCHAR(45) NULL ,
  `info` VARCHAR(45) NULL ,
  `email` VARCHAR(45) NULL ,
  PRIMARY KEY (`organization_id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `myevents`.`organization_own_event`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `myevents`.`organization_own_event` (
  `event_id` INT UNSIGNED NOT NULL ,
  `organization_id` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`event_id`, `organization_id`) ,
  INDEX `fk_events_has_organizations_organizations1_idx` (`organization_id` ASC) ,
  INDEX `fk_events_has_organizations_events1_idx` (`event_id` ASC) ,
  CONSTRAINT `fk_events_has_organizations_events1`
    FOREIGN KEY (`event_id` )
    REFERENCES `myevents`.`event` (`event_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_events_has_organizations_organizations1`
    FOREIGN KEY (`organization_id` )
    REFERENCES `myevents`.`organization` (`organization_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `myevents`.`organization_has_user`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `myevents`.`organization_has_user` (
  `user_id` INT UNSIGNED NOT NULL ,
  `organization_id` INT UNSIGNED NOT NULL ,
  `member` TINYINT(1) NOT NULL DEFAULT false ,
  `position` VARCHAR(45) NULL ,
  PRIMARY KEY (`user_id`, `organization_id`) ,
  INDEX `fk_users_has_organizations_organizations1_idx` (`organization_id` ASC) ,
  INDEX `fk_users_has_organizations_users1_idx` (`user_id` ASC) ,
  CONSTRAINT `fk_users_has_organizations_users1`
    FOREIGN KEY (`user_id` )
    REFERENCES `myevents`.`user` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_has_organizations_organizations1`
    FOREIGN KEY (`organization_id` )
    REFERENCES `myevents`.`organization` (`organization_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `myevents`.`sponsor`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `myevents`.`sponsor` (
  `sponsor_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL ,
  `url` VARCHAR(45) NULL ,
  PRIMARY KEY (`sponsor_id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `myevents`.`event_has_sponsor`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `myevents`.`event_has_sponsor` (
  `event_id` INT UNSIGNED NOT NULL ,
  `sponsor_id` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`event_id`, `sponsor_id`) ,
  INDEX `fk_event_has_sponsor_sponsor1_idx` (`sponsor_id` ASC) ,
  INDEX `fk_event_has_sponsor_event1_idx` (`event_id` ASC) ,
  CONSTRAINT `fk_event_has_sponsor_event1`
    FOREIGN KEY (`event_id` )
    REFERENCES `myevents`.`event` (`event_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_event_has_sponsor_sponsor1`
    FOREIGN KEY (`sponsor_id` )
    REFERENCES `myevents`.`sponsor` (`sponsor_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `myevents`.`authenticate_provides`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `myevents`.`authenticate_provides` (
  `authenticate_provides_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `active` TINYINT(1) NOT NULL ,
  `name` VARCHAR(45) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`authenticate_provides_id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `myevents`.`authenticate`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `myevents`.`authenticate` (
  `authenticate_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `active` TINYINT(1) NOT NULL ,
  `created` DATETIME NOT NULL ,
  `identity` VARCHAR(128) NOT NULL ,
  `verification` VARCHAR(255) NULL ,
  `user_id` INT UNSIGNED NOT NULL ,
  `authenticate_provides_id` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`authenticate_id`, `user_id`, `authenticate_provides_id`) ,
  UNIQUE INDEX `identity_UNIQUE` (`identity` ASC) ,
  INDEX `fk_authenticate_user1_idx` (`user_id` ASC) ,
  INDEX `fk_authenticate_authenticate_provides1_idx` (`authenticate_provides_id` ASC) ,
  CONSTRAINT `fk_authenticate_user1`
    FOREIGN KEY (`user_id` )
    REFERENCES `myevents`.`user` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_authenticate_authenticate_provides1`
    FOREIGN KEY (`authenticate_provides_id` )
    REFERENCES `myevents`.`authenticate_provides` (`authenticate_provides_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `myevents`.`role`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `myevents`.`role` (
  `role_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL ,
  `uri_code` VARCHAR(45) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`role_id`) ,
  UNIQUE INDEX `uri_code_UNIQUE` (`uri_code` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `myevents`.`resource`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `myevents`.`resource` (
  `resource_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL ,
  `uri_code` VARCHAR(45) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`resource_id`) ,
  UNIQUE INDEX `uri_code_UNIQUE` (`uri_code` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `myevents`.`privilege`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `myevents`.`privilege` (
  `privilege_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL ,
  `uri_code` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`privilege_id`) ,
  UNIQUE INDEX `uri_code_UNIQUE` (`uri_code` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `myevents`.`permission`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `myevents`.`permission` (
  `role_id` INT UNSIGNED NOT NULL ,
  `resource_id` INT UNSIGNED NOT NULL ,
  `privilege_id` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`role_id`, `resource_id`, `privilege_id`) ,
  INDEX `fk_permission_role1_idx` (`role_id` ASC) ,
  INDEX `fk_permission_resource1_idx` (`resource_id` ASC) ,
  INDEX `fk_permission_privilege1_idx` (`privilege_id` ASC) ,
  CONSTRAINT `fk_permission_role1`
    FOREIGN KEY (`role_id` )
    REFERENCES `myevents`.`role` (`role_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_permission_resource1`
    FOREIGN KEY (`resource_id` )
    REFERENCES `myevents`.`resource` (`resource_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_permission_privilege1`
    FOREIGN KEY (`privilege_id` )
    REFERENCES `myevents`.`privilege` (`privilege_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `myevents`.`email`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `myevents`.`email` (
  `email_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `email` VARCHAR(100) NOT NULL ,
  `remote_addr` VARCHAR(100) NOT NULL ,
  `user_agent` VARCHAR(500) NOT NULL ,
  `registered` DATETIME NOT NULL ,
  `meta_data` TEXT NULL ,
  PRIMARY KEY (`email_id`) ,
  UNIQUE INDEX `email_UNIQUE` (`email` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `myevents`.`user_has_role`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `myevents`.`user_has_role` (
  `user_id` INT UNSIGNED NOT NULL ,
  `role_id` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`user_id`, `role_id`) ,
  INDEX `fk_user_has_role_role1_idx` (`role_id` ASC) ,
  INDEX `fk_user_has_role_user1_idx` (`user_id` ASC) ,
  CONSTRAINT `fk_user_has_role_user1`
    FOREIGN KEY (`user_id` )
    REFERENCES `myevents`.`user` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_user_has_role_role1`
    FOREIGN KEY (`role_id` )
    REFERENCES `myevents`.`role` (`role_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
