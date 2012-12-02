-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Počítač: localhost
-- Vygenerováno: Sobota 01. prosince 2012, 15:32
-- Verze MySQL: 5.1.63
-- Verze PHP: 5.3.3-7+squeeze14

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Databáze: `zs1213_c`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `attendance`
--

CREATE TABLE IF NOT EXISTS `attendance` (
  `user_id` int(10) unsigned NOT NULL,
  `event_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`event_id`),
  KEY `fk_users_has_events_events1_idx` (`event_id`),
  KEY `fk_users_has_events_users_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `attendance`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `authenticate`
--

CREATE TABLE IF NOT EXISTS `authenticate` (
  `authenticate_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL,
  `created` datetime NOT NULL,
  `identity` varchar(128) COLLATE utf8_czech_ci NOT NULL,
  `verification` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `authenticate_provides_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`authenticate_id`,`user_id`,`authenticate_provides_id`),
  UNIQUE KEY `identity_UNIQUE` (`identity`),
  KEY `fk_authenticate_user1_idx` (`user_id`),
  KEY `fk_authenticate_authenticate_provides1_idx` (`authenticate_provides_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

--
-- Vypisuji data pro tabulku `authenticate`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `authenticate_provides`
--

CREATE TABLE IF NOT EXISTS `authenticate_provides` (
  `authenticate_provides_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL,
  `name` varchar(45) COLLATE utf8_czech_ci NOT NULL,
  `description` text COLLATE utf8_czech_ci,
  PRIMARY KEY (`authenticate_provides_id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

--
-- Vypisuji data pro tabulku `authenticate_provides`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `category`
--

CREATE TABLE IF NOT EXISTS `category` (
  `category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) COLLATE utf8_czech_ci DEFAULT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=5 ;

--
-- Vypisuji data pro tabulku `category`
--

INSERT INTO `category` (`category_id`, `name`) VALUES
(1, 'Vzdělání'),
(2, 'Sport'),
(3, 'Disco'),
(4, 'Zábava');

-- --------------------------------------------------------

--
-- Struktura tabulky `email`
--

CREATE TABLE IF NOT EXISTS `email` (
  `email_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `remote_addr` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `user_agent` varchar(500) COLLATE utf8_czech_ci NOT NULL,
  `registered` datetime NOT NULL,
  `meta_data` text COLLATE utf8_czech_ci,
  PRIMARY KEY (`email_id`),
  UNIQUE KEY `email_UNIQUE` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

--
-- Vypisuji data pro tabulku `email`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `event`
--

CREATE TABLE IF NOT EXISTS `event` (
  `event_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) COLLATE utf8_czech_ci NOT NULL,
  `location` varchar(45) COLLATE utf8_czech_ci DEFAULT NULL,
  `timestart` datetime DEFAULT NULL,
  `timeend` datetime DEFAULT NULL,
  `shortinfo` text COLLATE utf8_czech_ci,
  `longinfo` longtext COLLATE utf8_czech_ci,
  `capacity` int(10) unsigned DEFAULT NULL,  
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `public` tinyint(1) NOT NULL DEFAULT '1',
  `url` varchar(45) COLLATE utf8_czech_ci DEFAULT NULL,
  `fburl` varchar(45) COLLATE utf8_czech_ci DEFAULT NULL,
  `category_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`event_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=2 ;

--
-- Vypisuji data pro tabulku `event`
--

INSERT INTO `event` (`event_id`, `name`, `location`, `timestart`, `timeend`, `shortinfo`, `longinfo`, `capacity`, `active`, `public`, `url`, `fburl`, `category_id`) VALUES
(1, 'Testovací akce 1', 'U mě doma', '2012-12-01 12:00:00', '2012-12-01 13:00:00', NULL, '', 150, 1, 1, NULL, '', 0);

--
-- Struktura tabulky `event_has_sponsor`
--

CREATE TABLE IF NOT EXISTS `event_has_sponsor` (
  `event_id` int(10) unsigned NOT NULL,
  `sponsor_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`event_id`,`sponsor_id`),
  KEY `fk_event_has_sponsor_sponsor1_idx` (`sponsor_id`),
  KEY `fk_event_has_sponsor_event1_idx` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `event_has_sponsor`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `organization_own_event`
--

CREATE TABLE IF NOT EXISTS `organization_own_event` (
  `event_id` int(10) unsigned NOT NULL,
  `organization_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`event_id`,`organization_id`),
  KEY `fk_events_has_organizations_organizations1_idx` (`organization_id`),
  KEY `fk_events_has_organizations_events1_idx` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `organization_own_event`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `organization`
--

CREATE TABLE IF NOT EXISTS `organization` (
  `organization_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) COLLATE utf8_czech_ci NOT NULL,
  `website` varchar(45) COLLATE utf8_czech_ci DEFAULT NULL,
  `info` varchar(45) COLLATE utf8_czech_ci DEFAULT NULL,
  `email` varchar(45) COLLATE utf8_czech_ci DEFAULT NULL,
  PRIMARY KEY (`organization_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=6 ;

--
-- Vypisuji data pro tabulku `organization`
--

INSERT INTO `organization` (`organization_id`, `name`, `website`, `info`, `email`) VALUES
(1, 'Business IT', NULL, NULL, NULL),
(2, 'Klub koučinku', NULL, NULL, NULL),
(3, 'Klub mladých manažerů', NULL, NULL, NULL),
(4, 'AIESEC', NULL, NULL, NULL),
(5, 'Sport.vse.cz', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktura tabulky `organization_has_user`
--

CREATE TABLE IF NOT EXISTS `organization_has_user` (
  `user_id` int(10) unsigned NOT NULL,
  `organization_id` int(10) unsigned NOT NULL,
  `member` tinyint(1) NOT NULL DEFAULT '0',
  `position` varchar(45) COLLATE utf8_czech_ci DEFAULT NULL,
  PRIMARY KEY (`user_id`,`organization_id`),
  KEY `fk_users_has_organizations_organizations1_idx` (`organization_id`),
  KEY `fk_users_has_organizations_users1_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `organization_has_user`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `permission`
--

CREATE TABLE IF NOT EXISTS `permission` (
  `role_id` int(10) unsigned NOT NULL,
  `resource_id` int(10) unsigned NOT NULL,
  `privilege_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`resource_id`,`privilege_id`),
  KEY `fk_permission_role1_idx` (`role_id`),
  KEY `fk_permission_resource1_idx` (`resource_id`),
  KEY `fk_permission_privilege1_idx` (`privilege_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `permission`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `privilege`
--

CREATE TABLE IF NOT EXISTS `privilege` (
  `privilege_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) COLLATE utf8_czech_ci NOT NULL,
  `uri_code` varchar(45) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`privilege_id`),
  UNIQUE KEY `uri_code_UNIQUE` (`uri_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

--
-- Vypisuji data pro tabulku `privilege`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `resource`
--

CREATE TABLE IF NOT EXISTS `resource` (
  `resource_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) COLLATE utf8_czech_ci NOT NULL,
  `uri_code` varchar(45) COLLATE utf8_czech_ci NOT NULL,
  `description` text COLLATE utf8_czech_ci,
  PRIMARY KEY (`resource_id`),
  UNIQUE KEY `uri_code_UNIQUE` (`uri_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

--
-- Vypisuji data pro tabulku `resource`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `role`
--

CREATE TABLE IF NOT EXISTS `role` (
  `role_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) COLLATE utf8_czech_ci NOT NULL,
  `uri_code` varchar(45) COLLATE utf8_czech_ci NOT NULL,
  `description` text COLLATE utf8_czech_ci,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `uri_code_UNIQUE` (`uri_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

--
-- Vypisuji data pro tabulku `role`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `sponsor`
--

CREATE TABLE IF NOT EXISTS `sponsor` (
  `sponsor_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) COLLATE utf8_czech_ci NOT NULL,
  `url` varchar(45) COLLATE utf8_czech_ci DEFAULT NULL,
  PRIMARY KEY (`sponsor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

--
-- Vypisuji data pro tabulku `sponsor`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8_czech_ci DEFAULT NULL,
  `last_name` varchar(100) COLLATE utf8_czech_ci DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email_UNIQUE` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

--
-- Vypisuji data pro tabulku `user`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `user_has_role`
--

CREATE TABLE IF NOT EXISTS `user_has_role` (
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `fk_user_has_role_role1_idx` (`role_id`),
  KEY `fk_user_has_role_user1_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `user_has_role`
--

