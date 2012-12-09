--
-- Table structure for table `organization`
--
DROP TABLE `organization`;

CREATE TABLE IF NOT EXISTS `organization` (
  `organization_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) COLLATE utf8_czech_ci NOT NULL,
  `website` varchar(45) COLLATE utf8_czech_ci DEFAULT NULL,
  `info` longtext COLLATE utf8_czech_ci,
  `email` varchar(45) COLLATE utf8_czech_ci DEFAULT NULL,
  `logo` varchar(100) COLLATE utf8_czech_ci DEFAULT NULL,
  `fb_url` varchar(100) COLLATE utf8_czech_ci DEFAULT NULL,
  PRIMARY KEY (`organization_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=6 ;

--
-- Dumping data for table `organization`
--

INSERT INTO `organization` (`organization_id`, `name`, `website`, `info`, `email`, `logo`, `fb_url`) VALUES
(1, 'Business IT', NULL, NULL, NULL, 'BizIT.png', NULL),
(2, 'Klub koučinku', NULL, NULL, NULL, 'KlubKoucinku.png', NULL),
(3, 'Klub mladých manažerů', 'http://www.kmm.cz/', 'Studentský univerzitní klub se zaměřením na management. Pořádáme setkání se zkušenými manažery a podnikateli z praxe.\r\n\r\n\r\nSpojujeme manažerské generace\r\n\r\n\r\nDíky setkáním s manažery a podnikateli předáváme informace, životní postoje a zkušenosti mezi potenciálními a začínajícími manažery a lidmi z praxe. Mimo jiné pořádáme pro členy klubu zajímavé workshopy a neformální akce. Klub mladých manažerů operuje pod záštitou České manažerské asociace. Více info na http://www.kmm.cz/', 'manazeri@man.cz', 'KMM.png', 'https://www.facebook.com/klubmladychmanazeru'),
(4, 'AIESEC', NULL, NULL, NULL, 'AIESEC.png', NULL),
(5, 'Sport.vse.cz', NULL, NULL, NULL, 'SportVSE.png', NULL);