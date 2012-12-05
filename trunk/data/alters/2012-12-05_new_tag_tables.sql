


-- -----------------------------------------------------
-- Table `myevents`.`tag`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `myevents`.`tag` ;

CREATE  TABLE IF NOT EXISTS `myevents`.`tag` (
  `tag_id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NULL ,
  PRIMARY KEY (`tag_id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `myevents`.`event_has_tag`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `myevents`.`event_has_tag` ;

CREATE  TABLE IF NOT EXISTS `myevents`.`event_has_tag` (
  `tag_id` INT NOT NULL ,
  `event_id` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`tag_id`, `event_id`) ,
  INDEX `fk_tag_has_event_event1_idx` (`event_id` ASC) ,
  INDEX `fk_tag_has_event_tag1_idx` (`tag_id` ASC))
ENGINE = InnoDB;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
