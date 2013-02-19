CREATE TABLE `gcm_registration` (
`gcm_registration_id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`reg_id` VARCHAR( 255 ) NOT NULL
) ENGINE = INNODB;
ALTER TABLE `gcm_registration`
ADD UNIQUE `reg_id` (`reg_id`);