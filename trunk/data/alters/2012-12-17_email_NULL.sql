ALTER TABLE `user`
CHANGE `email` `email` varchar(100) COLLATE 'utf8_general_ci' NULL AFTER `user_id`,
COMMENT='';