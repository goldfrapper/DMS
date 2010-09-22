-- phpMyAdmin SQL Dump
-- version 3.3.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 21, 2010 at 11:06 AM
-- Server version: 5.1.47
-- PHP Version: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `test_joomla`
--

-- --------------------------------------------------------

--
-- Table structure for table `dms_addressbook`
--

CREATE TABLE IF NOT EXISTS `dms_addressbook` (
  `address_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `street` varchar(128) NOT NULL,
  `nr` varchar(16) NOT NULL,
  `postalcode` smallint(5) unsigned NOT NULL,
  `city` varchar(64) NOT NULL,
  `county` varchar(64) NOT NULL,
  `country` varchar(64) NOT NULL,
  PRIMARY KEY (`address_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;

-- --------------------------------------------------------

--
-- Table structure for table `dms_groupperms`
--

CREATE TABLE IF NOT EXISTS `dms_groupperms` (
  `group_id` smallint(5) unsigned NOT NULL,
  `appID` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `dms_groups`
--

CREATE TABLE IF NOT EXISTS `dms_groups` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `dms_profiles`
--

CREATE TABLE IF NOT EXISTS `dms_profiles` (
  `user_id` int(10) unsigned NOT NULL,
  `name` varchar(128) NOT NULL,
  `bday` date NOT NULL,
  `sex` enum('M','F') NOT NULL,
  `lng` varchar(32) NOT NULL,
  `title` varchar(64) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `dms_sessions`
--

CREATE TABLE IF NOT EXISTS `dms_sessions` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `code` char(32) NOT NULL,
  `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `mdate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=242 ;

-- --------------------------------------------------------

--
-- Table structure for table `dms_useraddresses`
--

CREATE TABLE IF NOT EXISTS `dms_useraddresses` (
  `user_id` int(10) unsigned NOT NULL,
  `address_id` int(10) unsigned NOT NULL,
  `addressname` varchar(16) NOT NULL,
  UNIQUE KEY `user_id` (`user_id`,`addressname`),
  KEY `address_id` (`address_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `dms_usergroups`
--

CREATE TABLE IF NOT EXISTS `dms_usergroups` (
  `user_id` smallint(5) unsigned NOT NULL,
  `group_id` smallint(5) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `dms_users`
--

CREATE TABLE IF NOT EXISTS `dms_users` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `pass` char(40) NOT NULL,
  `fname` varchar(32) NOT NULL,
  `email` varchar(128) NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=53 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dms_profiles`
--
ALTER TABLE `dms_profiles`
  ADD CONSTRAINT `dms_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `dms_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `dms_useraddresses`
--
ALTER TABLE `dms_useraddresses`
  ADD CONSTRAINT `dms_useraddresses_ibfk_1` FOREIGN KEY (`address_id`) REFERENCES `dms_addressbook` (`address_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dms_useraddresses_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `dms_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
