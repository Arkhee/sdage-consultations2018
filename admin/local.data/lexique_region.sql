-- phpMyAdmin SQL Dump
-- version 3.1.1
-- http://www.phpmyadmin.net
--
-- Serveur: localhost
-- Généré le : Mer 11 Novembre 2009 à 17:16
-- Version du serveur: 5.1.30
-- Version de PHP: 5.2.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `diren_pcb_16102009`
--

-- --------------------------------------------------------

--
-- Structure de la table `lexique_region`
--

CREATE TABLE IF NOT EXISTS `lexique_region` (
  `id_region` int(11) NOT NULL AUTO_INCREMENT,
  `num_region` varchar(2) DEFAULT NULL,
  `nom_region` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id_region`),
  UNIQUE KEY `clef_unique` (`num_region`),
  KEY `num_region` (`num_region`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Contenu de la table `lexique_region`
--

INSERT INTO `lexique_region` (`id_region`, `num_region`, `nom_region`) VALUES
(1, '83', 'AUVERGNE'),
(2, '91', 'LANGUEDOC-ROUSSILLON'),
(3, '93', 'PROVENCE-ALPES-COTE-D''AZUR'),
(4, '94', 'CORSE'),
(5, '41', 'LORRAINE'),
(6, '42', 'ALSACE'),
(7, '43', 'FRANCHE-COMTE'),
(8, '52', 'PAYS-DE-LA-LOIRE'),
(9, '53', 'BRETAGNE'),
(10, '54', 'POITOU-CHARENTE'),
(11, '72', 'AQUITAINE'),
(12, '1', 'GUADELOUPE'),
(13, '2', 'MARTINIQUE'),
(14, '3', 'GUYANE'),
(15, '4', 'REUNION'),
(16, '11', 'ILE-DE-FRANCE'),
(17, '21', 'CHAMPAGNE-ARDENNE'),
(18, '22', 'PICARDIE'),
(19, '23', 'HAUTE-NORMANDIE'),
(20, '24', 'CENTRE'),
(21, '25', 'BASSE-NORMANDIE'),
(22, '26', 'BOURGOGNE'),
(23, '31', 'NORD-PAS-DE-CALAIS'),
(24, '73', 'MIDI-PYRENEES'),
(25, '74', 'LIMOUSIN'),
(26, '82', 'RHONE-ALPES');
