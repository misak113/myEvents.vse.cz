ALTER TABLE `organization` 
CHANGE `info` `info` LONGTEXT CHARACTER SET utf8 COLLATE utf8_czech_ci NULL DEFAULT NULL;

ALTER TABLE `organization`  
ADD `fb_url` VARCHAR(100) NULL DEFAULT NULL;