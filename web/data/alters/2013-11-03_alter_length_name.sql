ALTER TABLE `event`
CHANGE `name` `name` varchar(120) COLLATE 'utf8_general_ci' NOT NULL AFTER `event_id`,
COMMENT='';