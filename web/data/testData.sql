﻿-- Adminer 3.6.1 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DELETE FROM `category`;
INSERT INTO `category` (`category_id`, `name`) VALUES
(1,	'Vzdělání'),
(2,	'Sport'),
(3,	'Disco'),
(4,	'Zábava');

DELETE FROM `organization`;
--
-- Dumping data for table `organization`
--

INSERT INTO `organization` (`organization_id`, `name`, `website`, `info`, `email`, `logo`, `fb_url`, `facebook_id`) VALUES
(1, 'BizIT', '', '', '', '', '', '93602669985'),
(2, 'Klub koučinku', '', '', '', '', '', '296052990420437'),
(3, 'Cashflow Klub', '', '', '', '', '', ''),
(4, 'AIESEC', '', '', '', '', '', '95679730987'),
(5, 'Sport.VSE.cz', '', '', '', '', '', ''),
(6, '180 Degrees Consulting', '', '', '', '', '', '345051448913246'),
(7, 'AEGEE-PRAHA', '', '', '', '', '', '176721839052'),
(9, 'Alfa fí AΦ', '', '', '', '', '', '205013259510059'),
(13, 'Club 307', '', '', '', '', '', '219079248152830'),
(14, 'Ekonom, Economix.cz', '', '', '', '', '', '359121546135'),
(15, 'ESN VŠE Praha - Buddy System', '', '', '', '', '', '358352857511810'),
(16, 'FRIENDS VŠE', '', '', '', '', '', '216388031716386'),
(17, 'Golf Networking Club', '', '', '', '', '', '173903462657747'),
(18, 'iKnow Club, iKnow.eu', '', '', '', '', '', '103296207540'),
(19, 'Indivisuals', '', '', '', '', '', '168458376537044'),
(20, 'Institut energetické ekonomie', '', '', '', '', '', '191589684197152'),
(21, 'Jeden svět na VŠE', '', '', '', '', '', '165736606778774'),
(22, 'Klub Investorů', '', '', '', '', '', ''),
(24, 'Klub mladých logistiků', '', '', '', '', '', ''),
(25, 'Klub mladých politologů', '', '', '', '', '', '118792701230'),
(26, 'KMM', '', '', '', '', '', '107800475917180'),
(27, 'Marketing and media club VŠE', '', '', '', '', '', '437624672941595'),
(28, 'Marketing club', '', '', '', '', '', '10150114835185591'),
(29, 'Model UN Prague', '', '', '', '', '', '477237812316043'),
(31, 'Studentský klub pro projektové řízení', NULL, NULL, NULL, NULL, NULL, NULL);


-- Adminer 3.6.1 MySQL dump

DELETE FROM `event`;
INSERT INTO `event` (`event_id`, `name`, `location`, `timestart`, `timeend`, `shortinfo`, `longinfo`, `active`, `public`, `url`, `fburl`, `category_id`, `capacity`) VALUES
(1,	'Sraz mladých filatelistů',	'NB203',	'2012-12-22 14:26:45',	NULL,	'Your bones don\'t break, mine do. That\'s clear. Your cells react to bacteria and viruses differently than mine.\r\n',	'Your bones don\'t break, mine do. That\'s clear. Your cells react to bacteria and viruses differently than mine. You don\'t get sick, I do. That\'s also clear. But for some reason, you and I react the exact same way to water. We swallow it too fast, we choke. We get some in our lungs, we drown. However unreal it may seem, we are connected, you and I. We\'re on the same curve, just on opposite ends.\r\n',	1,	1,	'sport.vse.cz',	'facebook.com',	1,	113),
(2,	'Sraz sportovců',	'VŠE',	'2012-12-22 12:00:00',	'2012-12-22 13:00:00',	'mine do. That\'s clear. Your cells react to bacteria and viruses differently than mine.\r\n',	'You think water moves fast? You should see ice. It moves like it has a mind. Like it knows it killed the world once and got a taste for murder. After the avalanche, it took us a week to climb out. Now, I don\'t know exactly when we turned on each other, but I know that seven of us survived the slide... and only five made it ',	1,	1,	'stranky.com',	'facebook.com/akce',	2,	100),
(3,	'Testovací akce 1',	'U mě doma',	'2012-12-11 12:00:00',	'2012-12-01 13:00:00',	'That\'s clear. Your cells react to bacteria and viruses differently than mine.\r\n',	'Look, just because I don\'t be givin\' no man a foot massage don\'t make it right for Marsellus to throw Antwone into a glass motherfuckin\' house, fuckin\' up the way the nigger talks. Motherfucker do that shit to me, he better paralyze my ass, \'cause I\'ll kill the motherfucker, know what I\'m sayin\'?',	1,	1,	'jou.hd',	'facebook.com/akcicky',	3,	20),
(4,	'4it445 sraz Programátorů',	'SB226',	'2012-12-04 18:00:00',	'2012-12-04 19:30:00',	'My money\'s in that office, right? If she start giving me some bullshit about it ain\'t there, and we got to go someplace else and get it, I\'m gonna shoot you in the head then and there. Then I\'m gonna shoot that bitch in th',	'My money\'s in that office, right? If she start giving me some bullshit about it ain\'t there, and we got to go someplace else and get it, I\'m gonna shoot you in the head then and there. Then I\'m gonna shoot that bitch in the kneecaps, find out where my goddamn money is. She gonna tell me too. Hey, look at me when I\'m talking to you, motherfucker. You listen: we go in there, and that nigga Winston or anybody else is in there, you the first motherfucker to get shot. You understand?',	1,	1,	'https://docs.google.com/spreadsheet/viewform?',	'http://www.facebook.com/groups/16245624387818',	4,	41),
(7,	'Posezení u Kafe',	'SB 101',	'2012-12-09 12:30:00',	'2012-12-09 14:30:00',	'the people who make shows, and on the strength of that one show they decide if they\'re going to make more shows. Some pilots get picked and become television programs. Some don\'t, become nothing. She starred in one of the ones that became nothing',	'Well, the way they make shows is, they make one show. That show\'s called a pilot. Then they show that show to the people who make shows, and on the strength of that one show they decide if they\'re going to make more shows. Some pilots get picked and become television programs. Some don\'t, become nothing. She starred in one of the ones that became nothing',	1,	1,	'http://slipsum.com/',	'http://www.facebook.com/groups/1624',	4,	12),
(8,	'Ústav pro Disco',	'LeClub',	'2012-12-15 21:30:00',	'2012-12-15 23:30:00',	'Now that we know who you are, I know who I am. I\'m not a mistake! It all makes sense! In a comic, you know h',	'<!-- start slipsum code -->\r\n\r\nNow that we know who you are, I know who I am. I\'m not a mistake! It all makes sense! In a comic, you know how you can tell who the arch-villain\'s going to be? He\'s the exact opposite of the hero. And most times they\'re friends, like you and me! I should\'ve known way back when... You know why, David? Because of the kids. They called me Mr Glass.\r\n\r\n<!-- please do not remove this line -->\r\n\r\n<div style=\"display:none;\">\r\n<a href=\"http://slipsum.com\">lorem ipsum</a></div>\r\n\r\n<!-- end slipsum code -->\r\n',	1,	1,	'http://localhost.myevents.vse.cz/udalosti?',	NULL,	1,	200),
(9,	'Rozmarné léto',	'Vensovského aula',	'2012-12-04 15:00:00',	'2012-12-04 16:00:00',	'Look, just because I don\'t be givin\' no man a foot massage don\'t make it right for Marsellus to throw Antwone into a glass motherfuckin\' house, fuckin\' up the way the nigger talks. Motherfucker do that shit to me, he better paralyze my ass, \'cause I\'ll kill the motherfucker, know what I\'m sayin\'?\r\n',	'Look, just because I don\'t be givin\' no man a foot massage don\'t make it right for Marsellus to throw Antwone into a glass motherfuckin\' house, fuckin\' up the way the nigger talks. Motherfucker do that shit to me, he better paralyze my ass, \'cause I\'ll kill the motherfucker, know what I\'m sayin\'?\r\nLook, just because I don\'t be givin\' no man a foot massage don\'t make it right for Marsellus to throw Antwone into a glass motherfuckin\' house, fuckin\' up the way the nigger talks. Motherfucker do that shit to me, he better paralyze my ass, \'cause I\'ll kill the motherfucker, know what I\'m sayin\'?\r\n',	1,	1,	'https://trello.com/board/portal-studentskych-',	NULL,	2,	15);


DELETE FROM `organization_own_event`;
INSERT INTO `organization_own_event` (`event_id`, `organization_id`) VALUES
(7,	1),
(1,	2),
(8,	2),
(3,	3),
(9,	3),
(2,	4),
(4,	5);


-- Users

DELETE FROM `user`;
INSERT INTO  `user` (
`user_id` ,
`email` ,
`first_name` ,
`last_name`
)
VALUES
(1 ,  'admin@adminov.cz',  'Admin',  'Administrátorovič'),
(2 ,  'user123',  'Admin',  'God')
;

DELETE FROM `role`;
INSERT INTO `role`
(`role_id`, `name`, `uri_code`, `description`, `level`)
VALUES (1, 'Správce stud. org.', 'orgAdmin', 'Člen studentské organizace s oprávněním', 50)
,(2, 'Administrátor', 'sysAdmin', 'Administrátor systému', 100);

DELETE FROM `user_has_role`;
INSERT INTO `user_has_role`
(`user_id`, `role_id`)
VALUES (1, 2)
,(2, 2);


DELETE FROM `authenticate_provides`;
INSERT INTO `authenticate_provides` (`authenticate_provides_id`, `active`, `name`, `description`) VALUES
(1,	1,	'email',	'Přihlášení pomocí emailu a hesla'),
(2,	1,	'username',	'Přihlášení pomocí username a hesla');

DELETE FROM `authenticate`;
INSERT INTO `authenticate`
(`authenticate_id`, `active`, `created`, `identity`, `verification`, `user_id`, `authenticate_provides_id`)
VALUES
(1, 1, '2012-12-3 18:09:45', 'admin@adminov.cz', 'c8ce042f13dac812d2858d4adf613nd0703o10a2500fea47a1540db06fb22e44427e629', 1, 1) -- Heslo: 12345
,(2, 1, '2012-12-3 18:09:45', 'user123', '6e8a91a1f18ce2aebe629465e75a3pb5j1waff22aa1d5e834237167711be989cee1e72f', 2, 2) -- Heslo: thegod
;

DELETE FROM `organization_has_user`;
INSERT INTO `organization_has_user` (`user_id`, `organization_id`, `member`, `position`) VALUES
(1,	2,	0,	'Administrátor'),
(2,	3,	0,	'Moderátor');

DELETE FROM `tag`;
INSERT INTO `tag` (`tag_id`, `name`) VALUES
(1, 'Osobnost'),
(2, 'Diskuze'),
(3, 'Přednáška'),
(4, 'Prezentační dovednosti'),
(5, 'Z praxe'),
(6, 'Podnikání');



INSERT INTO `permission` (`role_id`, `resource_id`, `privilege_id`) VALUES
(2,	1,	1),
(2,	2,	1),
(2,	3,	1),
(2,	4,	1);

INSERT INTO `privilege` (`privilege_id`, `name`, `uri_code`) VALUES
(1,	'Index',	'index');

INSERT INTO `resource` (`resource_id`, `name`, `uri_code`, `description`) VALUES
(1,	'Event',	'event',	NULL),
(2,	'User',	'user',	NULL),
(3,	'Index',	'index',	NULL),
(4,	'System',	'system',	NULL);

-- 2012-12-09 20:23:57