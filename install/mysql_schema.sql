-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Dim 10 Janvier 2016 à 16:37
-- Version du serveur: 5.5.46-0ubuntu0.14.04.2
-- Version de PHP: 5.5.9-1ubuntu4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Base de données: `cloudSX`
--

-- --------------------------------------------------------

--
-- Structure de la table `owner`
--

CREATE TABLE IF NOT EXISTS `owner` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `dos_id` varchar(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `mode` enum('reader','writer') DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `DIDUID` (`user_id`,`dos_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=30 ;

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mail` varchar(128) NOT NULL,
  `name` varchar(128) NOT NULL,
  `gvname` varchar(128) NOT NULL,
  `password` varchar(128) NOT NULL,
  `status` enum('request','std','premium','admin') NOT NULL,
  `credate` timestamp NULL DEFAULT NULL,
  `paydate` timestamp NULL DEFAULT NULL,
  `logo` mediumblob,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_mel` (`mail`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 ;
