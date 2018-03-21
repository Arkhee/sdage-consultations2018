-- phpMyAdmin SQL Dump
-- version 2.10.1
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Ven 02 Janvier 2009 à 11:30
-- Version du serveur: 5.0.45
-- Version de PHP: 5.2.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `cfh_portail`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `mdtb_groupes`
-- 

CREATE TABLE `mdtb_groupes` (
  `group_ID` int(11) NOT NULL auto_increment,
  `group_Nom` text NOT NULL,
  PRIMARY KEY  (`group_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- 
-- Contenu de la table `mdtb_groupes`
-- 

INSERT INTO `mdtb_groupes` (`group_ID`, `group_Nom`) VALUES 
(3, 'AIH-BRGM');

-- --------------------------------------------------------

-- 
-- Structure de la table `mdtb_users`
-- 

CREATE TABLE `mdtb_users` (
  `user_ID` int(11) NOT NULL auto_increment,
  `group_ID` int(11) NOT NULL,
  `user_Login` varchar(20) NOT NULL,
  `user_Name` text NOT NULL,
  `user_Mail` text NOT NULL,
  `user_Password` varchar(40) NOT NULL,
  `user_Rank` varchar(5) NOT NULL,
  PRIMARY KEY  (`user_ID`),
  UNIQUE KEY `user_Login` (`user_Login`),
  KEY `group_ID` (`group_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

-- 
-- Contenu de la table `mdtb_users`
-- 

INSERT INTO `mdtb_users` (`user_ID`, `group_ID`, `user_Login`, `user_Name`, `user_Mail`, `user_Password`, `user_Rank`) VALUES 
(1, 3, 'Yannick', 'Yannick Bétemps', 'yannick@alternetic.com', '3965207478e6a58f7c87af5d49a0c165', 'admin');

-- --------------------------------------------------------

-- 
-- Structure de la table `mdtb_users_rights`
-- 

CREATE TABLE `mdtb_users_rights` (
  `usri_ID` int(11) NOT NULL auto_increment,
  `user_ID` int(11) NOT NULL,
  `group_ID` int(11) NOT NULL default '-1',
  `usri_Rights` int(4) NOT NULL,
  `usri_Table` varchar(20) NOT NULL,
  `usri_Record_ID` int(11) NOT NULL default '-1',
  `usri_SQLFilter` text NOT NULL,
  PRIMARY KEY  (`usri_ID`),
  KEY `usri_table` (`usri_Table`,`usri_Record_ID`),
  KEY `group_ID` (`group_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Contenu de la table `mdtb_users_rights`
-- 

