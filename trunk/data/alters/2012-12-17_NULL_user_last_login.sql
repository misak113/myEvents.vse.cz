ALTER TABLE `user`
CHANGE `last_login_date` `last_login_date` datetime NULL AFTER `last_name`,
CHANGE `last_login_ip` `last_login_ip` varchar(15) COLLATE 'utf8_general_ci' NULL AFTER `last_login_date`,
COMMENT='';