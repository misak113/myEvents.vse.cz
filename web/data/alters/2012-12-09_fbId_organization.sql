ALTER TABLE `organization`
ADD `facebook_id` varchar(45) COLLATE 'utf8_general_ci' NULL,
COMMENT='';

ALTER TABLE `event`
ADD `external_id` varchar(45) NULL,
ADD `source_type` varchar(20) NULL AFTER `external_id`,
COMMENT='';

ALTER TABLE `event`
CHANGE `url` `url` varchar(200) COLLATE 'utf8_general_ci' NULL AFTER `public`,
CHANGE `fburl` `fburl` varchar(200) COLLATE 'utf8_general_ci' NULL AFTER `url`,
COMMENT='';