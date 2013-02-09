ALTER TABLE  `event`
ADD `timestart` datetime DEFAULT NULL,
ADD `timeend` datetime DEFAULT NULL
ADD `ready_to_approve` bool DEFAULT false NOT NULL;

INSERT INTO `myevents`.`role` (`role_id`, `name`, `uri_code`, `description`, `level`) VALUES 
(NULL, 'Kontrolor akcí', 'controller', 'Zamìstnanec VŠE provádìjící kontrolu akcí.', '70'), 
(NULL, 'Schvalovaè akcí', 'approver', 'Zamìstnanec VŠE provádìjící schvalování akcí', '70') ;