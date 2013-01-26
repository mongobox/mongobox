-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Client: 127.0.0.1
-- Généré le : Sam 26 Janvier 2013 à 17:36
-- Version du serveur: 5.5.16
-- Version de PHP: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Base de données: `mongobox`
--

--
-- Contenu de la table `users`
--

INSERT INTO `users` (`id`, `email`, `login`, `password`, `salt`, `lastname`, `firstname`, `avatar`, `actif`, `date_create`, `date_update`, `last_connect`) VALUES
(1, 'masterob1@free.fr', 'fma', '/amhZAn+y8jNqRPnwm4XSLEpHzJdcYA7uKy5jcsV51fJKdwaXPoLqa5xzc5WHPih3UrgcXIs0JjxVBQkmCePNg==', '7f0cd56327dbadca44d13ea12f573c7b91b14de4', 'fma', 'fma', NULL, 1, '2013-01-26 17:31:20', NULL, NULL),
(2, 'aja', 'aja', 'hg49SbEyjP0WYed1wr8yvNLvILq8Fv9yu5Z0pw6hKt8S25dsgH1mHiaFAwFGmwRxGpti6hzG10dx9rQNVyskWQ==', '9f69843ec3617c0836829ef256365fa7cfa3c808', 'aja', 'aja', NULL, 1, '2013-01-26 17:31:58', NULL, NULL),
(3, 'cg', 'cg', 'A7gio3eoxCmSuX22whfSzsApsjixbm8jsJso+gf3mPAtj6FRlQ35O+M3qN7yfpALqDlTBL52I1VkagQYEblLzw==', 'ea13acb531912124cb8aca5a1cedd0702daacd03', 'cg', 'cg', NULL, 1, '2013-01-26 17:32:19', NULL, NULL),
(4, 'dbo', 'dbo', 'bP6/6e+4T1HgvIa8L6uqrg+07AE4Yrhnh3jnC/86A62G5nbEzxqt7qh8PIXreGtR98GKv7zai1tstWj8Bc3Q2Q==', '13da8f2bd60d600577f80e62b7577396846f62ba', 'dbo', 'dbo', NULL, 1, '2013-01-26 17:32:36', NULL, NULL),
(5, 'pma', 'pma', 'OETTJjQwslfeeUDvMWzFz3z9o/kEwhQTllZmmwX9T7BtFenRjUbyqARc1NE3NdbgtF8R6AdTvEqAvxysUAgZdQ==', '96dea4c3086b53b09d47b47ecbef08713e0c45db', 'pma', 'pma', NULL, 1, '2013-01-26 17:33:11', NULL, NULL),
(6, 'jde', 'jde', 'ydwzvpSPAeN5vzH5vhI5eOKOrPfgp2MAlVr8IaLnxqC89DjBmyLhzVhSEpcWWUp9R9VHdNzc5eXRrPY78RgVGw==', 'dd8b245a6413045cac123dcac6d1f4416b6a9476', 'jde', 'jde', NULL, 1, '2013-01-26 17:33:49', NULL, NULL),
(7, 'ceb', 'ceb', 'LeqLMtpNWeM0m8DHrZySedCvQsZsdh3NTNdveIQ04p+l6uvIjuGH2ueOyIn357U389oRk5gx/LR2irv8N13DYw==', '89a3f8b4f411557302001e9b0a5fa7441d8fbc0d', 'ceb', 'ceb', NULL, 1, '2013-01-26 17:34:41', NULL, NULL),
(8, 'agi', 'agi', 'QpJPBYMn+aj9844mnCkmPJ4mA+S6FAIVaGFYJwdlAGABtniUGMUdUpX/XoYcUs5WozE90WF38HgBHGUZJTvM7g==', '0b7d6e95a2493f6fa4940f6f5791e13893418c7f', 'agi', 'agi', NULL, 1, '2013-01-26 17:34:53', NULL, NULL);

INSERT INTO `mongobox`.`groups` (
`id` ,
`title` ,
`private`
)
VALUES (
NULL , 'Mongo', '1'
);

INSERT INTO `users_groups` (`id_group`, `id_user`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8);