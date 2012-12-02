-- Adminer 3.6.1 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

INSERT INTO `category` (`category_id`, `name`) VALUES
(1,	'Vzdělání'),
(2,	'Sport'),
(3,	'Disco'),
(4,	'Zábava');

INSERT INTO `organization` (`organization_id`, `name`, `website`, `info`, `email`) VALUES
(1,	'Business IT',	NULL,	NULL,	NULL),
(2,	'Klub koučinku',	NULL,	NULL,	NULL),
(3,	'Klub mladých manažerů',	NULL,	NULL,	NULL),
(4,	'AIESEC',	NULL,	NULL,	NULL),
(5,	'Sport.vse.cz',	NULL,	NULL,	NULL);

-- 2012-12-01 00:06:46
-- Adminer 3.6.1 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

INSERT INTO `event` (`event_id`, `name`, `location`, `timestart`, `timeend`, `shortinfo`, `longinfo`, `active`, `public`, `url`, `fburl`, `category_id`, `capacity`) VALUES
(1,	'Sraz mladých filatelistů',	'NB203',	'2012-12-02 14:26:45',	NULL,	'Your bones don\'t break, mine do. That\'s clear. Your cells react to bacteria and viruses differently than mine.\r\n',	'Your bones don\'t break, mine do. That\'s clear. Your cells react to bacteria and viruses differently than mine. You don\'t get sick, I do. That\'s also clear. But for some reason, you and I react the exact same way to water. We swallow it too fast, we choke. We get some in our lungs, we drown. However unreal it may seem, we are connected, you and I. We\'re on the same curve, just on opposite ends.\r\n',	1,	1,	'sport.vse.cz',	'facebook.com',	1,	NULL),
(2,	'Sraz sportovců',	'VŠE',	'2012-11-22 12:00:00',	'2012-11-22 13:00:00',	'',	'',	1,	1,	NULL,	'',	2,	100),
(3,	'Testovací akce 1',	'U mě doma',	'2012-12-01 12:00:00',	'2012-12-01 13:00:00',	NULL,	'',	1,	1,	NULL,	'',	1,	NULL);

-- 2012-12-02 14:47:54