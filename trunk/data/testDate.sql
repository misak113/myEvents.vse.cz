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