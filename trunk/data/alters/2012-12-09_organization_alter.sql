ALTER TABLE `organization`
ADD `logo` varchar(100) COLLATE 'utf8_general_ci' NULL,
ADD `fb_url` varchar(100) COLLATE 'utf8_general_ci' NULL AFTER `logo`,
COMMENT='';