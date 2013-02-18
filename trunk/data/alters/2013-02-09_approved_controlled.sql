ALTER TABLE  `event`
ADD `approved` datetime DEFAULT NULL,
ADD `controlled` datetime DEFAULT NULL,
ADD `ready_to_approve` bool DEFAULT false NOT NULL;

INSERT INTO `myevents`.`role` (`role_id`, `name`, `uri_code`, `description`, `level`) VALUES 
(NULL, 'Kontrolor akc�', 'controller', 'Zam�stnanec V�E prov�d�j�c� kontrolu akc�.', '70'), 
(NULL, 'Schvalova� akc�', 'approver', 'Zam�stnanec V�E prov�d�j�c� schvalov�n� akc�', '70') ;