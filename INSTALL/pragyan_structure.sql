-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 21, 2012 at 10:49 PM
-- Server version: 5.5.25a
-- PHP Version: 5.3.16

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `pragyan_org_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `article_comments`
--

CREATE TABLE IF NOT EXISTS `article_comments` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `page_modulecomponentid` int(11) NOT NULL,
  `user` varchar(200) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `comment` text NOT NULL,
  PRIMARY KEY (`comment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `article_content`
--

CREATE TABLE IF NOT EXISTS `article_content` (
  `page_modulecomponentid` int(11) NOT NULL,
  `article_content` text NOT NULL,
  `article_lastupdated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `allowComments` tinyint(1) NOT NULL,
  `default_editor` enum('ckeditor','plain') NOT NULL DEFAULT 'ckeditor',
  PRIMARY KEY (`page_modulecomponentid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `article_contentbak`
--

CREATE TABLE IF NOT EXISTS `article_contentbak` (
  `page_modulecomponentid` int(11) NOT NULL,
  `article_revision` int(11) NOT NULL,
  `article_diff` text NOT NULL,
  `article_updatetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL,
  KEY `page_mdulecomponentid` (`page_modulecomponentid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `article_draft`
--

CREATE TABLE IF NOT EXISTS `article_draft` (
  `page_modulecomponentid` int(11) NOT NULL,
  `draft_number` int(11) NOT NULL,
  `draft_content` text NOT NULL,
  `draft_lastsaved` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `book_desc`
--

CREATE TABLE IF NOT EXISTS `book_desc` (
  `page_modulecomponentid` int(11) NOT NULL,
  `initial` int(11) NOT NULL,
  `list` varchar(256) NOT NULL,
  `menu_hide` varchar(500) NOT NULL,
  PRIMARY KEY (`page_modulecomponentid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `faculty_data`
--

CREATE TABLE IF NOT EXISTS `faculty_data` (
  `faculty_dataId` int(11) NOT NULL AUTO_INCREMENT,
  `faculty_sectionId` int(11) NOT NULL,
  `faculty_data` text NOT NULL,
  `page_moduleComponentId` int(11) NOT NULL,
  PRIMARY KEY (`faculty_dataId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `faculty_module`
--

CREATE TABLE IF NOT EXISTS `faculty_module` (
  `page_moduleComponentId` int(11) NOT NULL,
  `photo` varchar(200) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `templateId` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`page_moduleComponentId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `faculty_template`
--

CREATE TABLE IF NOT EXISTS `faculty_template` (
  `template_id` int(11) NOT NULL,
  `template_name` varchar(128) NOT NULL,
  `template_sectionId` int(11) NOT NULL,
  `template_sectionParentId` int(11) NOT NULL DEFAULT '0',
  `template_sectionName` varchar(200) NOT NULL,
  `template_sectionLimit` int(11) NOT NULL,
  `template_sectionOrder` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `form_desc`
--

CREATE TABLE IF NOT EXISTS `form_desc` (
  `page_modulecomponentid` int(11) NOT NULL,
  `form_heading` varchar(1000) NOT NULL,
  `form_loginrequired` tinyint(1) NOT NULL DEFAULT '1',
  `form_headertext` text,
  `form_footertext` text,
  `form_expirydatetime` datetime DEFAULT NULL,
  `form_sendconfirmation` tinyint(1) NOT NULL DEFAULT '0',
  `form_usecaptcha` tinyint(1) NOT NULL DEFAULT '0',
  `form_allowuseredit` tinyint(1) NOT NULL DEFAULT '1',
  `form_allowuserunregister` tinyint(1) NOT NULL DEFAULT '0',
  `form_showuseremail` tinyint(1) NOT NULL DEFAULT '1',
  `form_showuserfullname` tinyint(1) NOT NULL DEFAULT '0',
  `form_showuserprofiledata` tinyint(1) NOT NULL DEFAULT '0',
  `form_showregistrationdate` tinyint(1) NOT NULL DEFAULT '1',
  `form_showlastupdatedate` tinyint(1) NOT NULL DEFAULT '0',
  `form_registrantslimit` int(11) NOT NULL DEFAULT '-1',
  `form_closelimit` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`page_modulecomponentid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `form_elementdata`
--

CREATE TABLE IF NOT EXISTS `form_elementdata` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `page_modulecomponentid` int(11) NOT NULL DEFAULT '0',
  `form_elementid` int(11) NOT NULL DEFAULT '0',
  `form_elementdata` text NOT NULL,
  PRIMARY KEY (`user_id`,`page_modulecomponentid`,`form_elementid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `form_elementdesc`
--

CREATE TABLE IF NOT EXISTS `form_elementdesc` (
  `page_modulecomponentid` int(11) NOT NULL DEFAULT '0',
  `form_elementid` int(11) NOT NULL DEFAULT '0',
  `form_elementname` varchar(1000) NOT NULL,
  `form_elementdisplaytext` varchar(5000) NOT NULL COMMENT 'Description of data held',
  `form_elementtype` enum('text','textarea','radio','checkbox','select','password','file','date','datetime','member') NOT NULL DEFAULT 'text',
  `form_elementsize` int(11) DEFAULT NULL,
  `form_elementtypeoptions` text,
  `form_elementdefaultvalue` varchar(4000) DEFAULT NULL,
  `form_elementmorethan` varchar(4000) DEFAULT NULL,
  `form_elementlessthan` varchar(4000) DEFAULT NULL,
  `form_elementcheckint` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Check if it is int if 1',
  `form_elementtooltiptext` text NOT NULL,
  `form_elementisrequired` tinyint(1) NOT NULL DEFAULT '0',
  `form_elementrank` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`page_modulecomponentid`,`form_elementid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `form_regdata`
--

CREATE TABLE IF NOT EXISTS `form_regdata` (
  `user_id` int(11) NOT NULL,
  `page_modulecomponentid` int(11) NOT NULL,
  `form_firstupdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `form_lastupdated` timestamp NULL DEFAULT NULL,
  `form_verified` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`user_id`,`page_modulecomponentid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `forum_like`
--

CREATE TABLE IF NOT EXISTS `forum_like` (
  `page_modulecomponentid` int(11) NOT NULL,
  `forum_thread_id` int(11) NOT NULL,
  `forum_post_id` int(11) NOT NULL,
  `forum_like_user_id` int(11) NOT NULL,
  `like_status` enum('0','1') NOT NULL COMMENT '(0-Dislike 1-like)'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `forum_module`
--

CREATE TABLE IF NOT EXISTS `forum_module` (
  `page_modulecomponentid` int(11) NOT NULL,
  `forum_name` varchar(255) NOT NULL,
  `forum_description` text NOT NULL,
  `forum_moderated` tinyint(1) NOT NULL DEFAULT '1' COMMENT '(1-Moderated & 0-Public)',
  `last_post_userid` int(11) NOT NULL,
  `last_post_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total_thread_count` int(11) DEFAULT '1',
  `allow_delete_posts` enum('0','1') NOT NULL DEFAULT '0' COMMENT '(1-Allow 0-Don''t Allow)',
  `allow_like_posts` enum('0','1') NOT NULL DEFAULT '0' COMMENT '(1-Allow 0-Don''t Allow)',
  PRIMARY KEY (`page_modulecomponentid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `forum_posts`
--

CREATE TABLE IF NOT EXISTS `forum_posts` (
  `page_modulecomponentid` int(11) NOT NULL,
  `forum_thread_id` int(11) NOT NULL DEFAULT '0' COMMENT 'thread_id from forum_question',
  `forum_post_id` int(11) NOT NULL DEFAULT '0',
  `forum_post_user_id` int(11) NOT NULL,
  `forum_post_title` varchar(255) DEFAULT NULL,
  `forum_post_content` longtext NOT NULL,
  `forum_post_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `forum_post_approve` int(1) NOT NULL DEFAULT '0' COMMENT 'here approve is for the replies',
  PRIMARY KEY (`page_modulecomponentid`,`forum_thread_id`,`forum_post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `forum_threads`
--

CREATE TABLE IF NOT EXISTS `forum_threads` (
  `forum_thread_id` int(11) NOT NULL,
  `page_modulecomponentid` int(11) NOT NULL,
  `forum_thread_category` varchar(15) NOT NULL DEFAULT 'general' COMMENT '(General/Sticky)',
  `forum_access_status` varchar(15) NOT NULL DEFAULT 'moderated' COMMENT 'moderated/public',
  `forum_thread_topic` varchar(255) NOT NULL,
  `forum_detail` longtext NOT NULL,
  `forum_thread_user_id` int(11) NOT NULL,
  `forum_thread_datetime` varchar(50) NOT NULL,
  `forum_post_approve` int(1) NOT NULL DEFAULT '0',
  `forum_thread_viewcount` int(11) NOT NULL DEFAULT '0',
  `forum_thread_last_post_userid` int(11) NOT NULL,
  `forum_thread_lastpost_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`forum_thread_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `forum_visits`
--

CREATE TABLE IF NOT EXISTS `forum_visits` (
  `page_modulecomponentid` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `last_visit` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `gallery_name`
--

CREATE TABLE IF NOT EXISTS `gallery_name` (
  `page_modulecomponentid` int(11) NOT NULL,
  `gallery_name` varchar(50) NOT NULL,
  `gallery_desc` varchar(200) NOT NULL,
  `imagesPerPage` int(11) NOT NULL DEFAULT '6',
  `allowViews` tinyint(1) NOT NULL DEFAULT '0',
  `allowRatings` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `gallery_pics`
--

CREATE TABLE IF NOT EXISTS `gallery_pics` (
  `upload_filename` varchar(200) NOT NULL,
  `page_modulecomponentid` int(11) NOT NULL,
  `gallery_filecomment` varchar(200) NOT NULL,
  `pic_rate` int(11) NOT NULL,
  `vote_avg` decimal(10,0) NOT NULL DEFAULT '0',
  `voters` mediumint(9) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `hospi_accomodation_status`
--

CREATE TABLE IF NOT EXISTS `hospi_accomodation_status` (
  `page_modulecomponentid` int(11) NOT NULL,
  `hospi_room_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hospi_guest_name` varchar(100) NOT NULL,
  `hospi_guest_college` varchar(100) NOT NULL,
  `hospi_guest_phone` bigint(11) NOT NULL,
  `hospi_guest_email` varchar(100) NOT NULL,
  `hospi_actual_checkin` datetime NOT NULL,
  `hospi_actual_checkout` datetime NOT NULL,
  `hospi_checkedin_by` int(11) NOT NULL,
  `hospi_cash_collected` tinyint(1) NOT NULL DEFAULT '0',
  `hospi_checkedout_by` int(11) NOT NULL,
  `hospi_cash_refunded` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `hospi_hostel`
--

CREATE TABLE IF NOT EXISTS `hospi_hostel` (
  `page_modulecomponentid` int(11) NOT NULL,
  `hospi_room_id` int(11) NOT NULL,
  `hospi_hostel_name` varchar(11) NOT NULL,
  `hospi_room_capacity` int(11) NOT NULL DEFAULT '0',
  `hospi_room_no` int(11) NOT NULL DEFAULT '0',
  `hospi_floor` int(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `list_images`
--

CREATE TABLE IF NOT EXISTS `list_images` (
  `page_id` int(11) NOT NULL,
  `page_name` varchar(51) NOT NULL,
  `page_image` varchar(51) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `list_prop`
--

CREATE TABLE IF NOT EXISTS `list_prop` (
  `page_modulecomponentid` int(11) NOT NULL,
  `depth` int(11) NOT NULL,
  UNIQUE KEY `page_modulecomponentid_2` (`page_modulecomponentid`),
  KEY `page_modulecomponentid` (`page_modulecomponentid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter`
--

CREATE TABLE IF NOT EXISTS `newsletter` (
  `email` varchar(255) NOT NULL,
  `blocked` tinyint(1) NOT NULL DEFAULT '0',
  `sent` tinyint(1) NOT NULL,
  `mail2` timestamp NULL DEFAULT NULL,
  `mail3` timestamp NULL DEFAULT NULL,
  `mail4` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `user_email_2` (`email`),
  KEY `user_email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_bc`
--

CREATE TABLE IF NOT EXISTS `newsletter_bc` (
  `email` varchar(100) NOT NULL,
  `mail1` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `news_data`
--

CREATE TABLE IF NOT EXISTS `news_data` (
  `page_modulecomponentid` int(10) NOT NULL,
  `news_id` int(11) NOT NULL,
  `news_title` varchar(150) NOT NULL,
  `news_feed` varchar(1000) NOT NULL,
  `news_rank` int(10) NOT NULL,
  `news_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `news_link` varchar(100) NOT NULL,
  KEY `news_id` (`news_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `news_desc`
--

CREATE TABLE IF NOT EXISTS `news_desc` (
  `page_modulecomponentid` int(11) NOT NULL,
  `news_title` varchar(150) DEFAULT NULL,
  `news_description` varchar(1000) DEFAULT NULL,
  `news_link` varchar(250) DEFAULT NULL,
  `news_copyright` varchar(1000) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `poll_content`
--

CREATE TABLE IF NOT EXISTS `poll_content` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `page_modulecomponentid` int(11) NOT NULL,
  `ques` longtext NOT NULL,
  `o1` longtext NOT NULL,
  `o2` longtext NOT NULL,
  `o3` longtext NOT NULL,
  `o4` longtext NOT NULL,
  `o5` longtext NOT NULL,
  `o6` longtext NOT NULL,
  `multiple_opt` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 indicates multiple options',
  `visibility` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `poll_log`
--

CREATE TABLE IF NOT EXISTS `poll_log` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `page_modulecomponentid` int(11) NOT NULL,
  `o1` int(11) NOT NULL DEFAULT '0',
  `o2` int(11) NOT NULL DEFAULT '0',
  `o3` int(11) NOT NULL DEFAULT '0',
  `o4` int(11) NOT NULL DEFAULT '0',
  `o5` int(11) NOT NULL DEFAULT '0',
  `o6` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=26 ;

-- --------------------------------------------------------

--
-- Table structure for table `poll_users`
--

CREATE TABLE IF NOT EXISTS `poll_users` (
  `pid` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `page_modulecomponentid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pragyanV3_blacklist`
--

CREATE TABLE IF NOT EXISTS `pragyanV3_blacklist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(50) DEFAULT NULL,
  `ip` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=19 ;

-- --------------------------------------------------------

--
-- Table structure for table `pragyanV3_external`
--

CREATE TABLE IF NOT EXISTS `pragyanV3_external` (
  `page_modulecomponentid` int(11) NOT NULL,
  `page_extlink` varchar(500) NOT NULL,
  PRIMARY KEY (`page_modulecomponentid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Used to store all external links';

-- --------------------------------------------------------

--
-- Table structure for table `pragyanV3_global`
--

CREATE TABLE IF NOT EXISTS `pragyanV3_global` (
  `attribute` varchar(50) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`attribute`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pragyanV3_groups`
--

CREATE TABLE IF NOT EXISTS `pragyanV3_groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identification number of the group',
  `group_name` varchar(100) NOT NULL COMMENT 'Group name',
  `group_description` varchar(200) NOT NULL COMMENT 'Group description',
  `group_priority` int(11) NOT NULL DEFAULT '0' COMMENT 'Used for permissions',
  `form_id` int(11) NOT NULL DEFAULT '0' COMMENT '0 if not associated with a form',
  PRIMARY KEY (`group_id`),
  UNIQUE KEY `groupName` (`group_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Table structure for table `pragyanV3_inheritedinfo`
--

CREATE TABLE IF NOT EXISTS `pragyanV3_inheritedinfo` (
  `page_inheritedinfoid` int(11) NOT NULL COMMENT 'Inherited info id from the pages table',
  `page_inheritedinfocontent` text NOT NULL COMMENT 'Inherited information (like banner), can be used anywhere desired in template'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pragyanV3_log`
--

CREATE TABLE IF NOT EXISTS `pragyanV3_log` (
  `log_no` int(11) NOT NULL,
  `log_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_email` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `page_path` varchar(500) NOT NULL,
  `page_id` int(11) NOT NULL,
  `perm_module` varchar(100) NOT NULL,
  `perm_action` varchar(100) NOT NULL,
  `user_accessipaddress` varchar(100) NOT NULL,
  UNIQUE KEY `log_no` (`log_no`),
  KEY `date` (`log_datetime`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pragyanV3_modules`
--

CREATE TABLE IF NOT EXISTS `pragyanV3_modules` (
  `module_name` varchar(128) NOT NULL,
  `module_tables` varchar(500) NOT NULL,
  `allow_uploads` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`module_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pragyanV3_openid_users`
--

CREATE TABLE IF NOT EXISTS `pragyanV3_openid_users` (
  `openid_id` int(11) NOT NULL AUTO_INCREMENT,
  `openid_url` varchar(2063) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`openid_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `pragyanV3_pages`
--

CREATE TABLE IF NOT EXISTS `pragyanV3_pages` (
  `page_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Page identification number',
  `page_name` varchar(32) NOT NULL COMMENT 'Name of the page',
  `page_parentid` int(11) NOT NULL COMMENT 'ID of the parent of the page',
  `page_createdtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Time when the page was created',
  `page_lastaccesstime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Time when the page was last accessed',
  `page_title` varchar(128) NOT NULL DEFAULT 'New Page' COMMENT 'Title of the page',
  `page_module` varchar(128) NOT NULL COMMENT 'Module type of the page',
  `page_modulecomponentid` int(11) NOT NULL COMMENT 'Component id used in the module',
  `page_template` varchar(50) NOT NULL,
  `page_image` varchar(300) DEFAULT NULL,
  `page_menurank` int(11) NOT NULL COMMENT 'Rank for menu ordering',
  `page_inheritedinfoid` int(11) NOT NULL DEFAULT '-1' COMMENT 'Inherited info table mapping',
  `page_displayinmenu` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'To display in menu bar or not',
  `page_displayinsitemap` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'To display in sitemap or not',
  `page_displaymenu` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Tells if menu should be displayed at all',
  `page_displaysiblingmenu` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Tells if sibling menu is displayed',
  `page_displaypageheading` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Determines whether page heading is displayed on the page',
  `page_displayicon` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - To display icon in menu 0 - Not to display icon in menu',
  `page_menutype` enum('classic','complete','multidepth') NOT NULL DEFAULT 'classic' COMMENT 'Type of the menu : Classic (normal) or Drop-down (with some depth)',
  `page_menudepth` int(11) NOT NULL DEFAULT '1',
  `page_openinnewtab` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Whether to open the page in a new tab when clicked',
  PRIMARY KEY (`page_id`),
  UNIQUE KEY `unique parent` (`page_parentid`,`page_name`),
  KEY `page_module` (`page_module`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table `pragyanV3_permissionlist`
--

CREATE TABLE IF NOT EXISTS `pragyanV3_permissionlist` (
  `perm_id` int(11) NOT NULL AUTO_INCREMENT,
  `page_module` varchar(128) NOT NULL,
  `perm_action` varchar(100) NOT NULL,
  `perm_text` varchar(200) NOT NULL,
  `perm_rank` int(11) NOT NULL COMMENT 'The order of being shown in actionbar',
  `perm_description` varchar(200) NOT NULL,
  PRIMARY KEY (`perm_id`),
  UNIQUE KEY `permission type` (`page_module`,`perm_action`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='List of the available permissions' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `pragyanV3_templates`
--

CREATE TABLE IF NOT EXISTS `pragyanV3_templates` (
  `template_name` varchar(50) NOT NULL,
  PRIMARY KEY (`template_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pragyanV3_tempuploads`
--

CREATE TABLE IF NOT EXISTS `pragyanV3_tempuploads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filePath` varchar(500) NOT NULL,
  `info` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Table structure for table `pragyanV3_uploads`
--

CREATE TABLE IF NOT EXISTS `pragyanV3_uploads` (
  `page_modulecomponentid` int(11) NOT NULL,
  `page_module` varchar(128) NOT NULL,
  `upload_fileid` int(11) NOT NULL,
  `upload_filename` varchar(200) NOT NULL,
  `upload_filetype` varchar(300) NOT NULL,
  `upload_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` varchar(100) NOT NULL COMMENT 'The user who uploaded the file',
  PRIMARY KEY (`upload_fileid`),
  UNIQUE KEY `page_modulecomponentid` (`page_modulecomponentid`,`page_module`,`upload_filename`),
  KEY `page_module` (`page_module`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pragyanV3_usergroup`
--

CREATE TABLE IF NOT EXISTS `pragyanV3_usergroup` (
  `user_id` int(11) NOT NULL COMMENT 'user id ...comes from the user''s table',
  `group_id` int(11) NOT NULL COMMENT 'group id ...comes from the group''s table',
  KEY `user_id` (`user_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pragyanV3_userpageperm`
--

CREATE TABLE IF NOT EXISTS `pragyanV3_userpageperm` (
  `perm_type` enum('user','group') NOT NULL,
  `page_id` int(11) NOT NULL,
  `usergroup_id` int(11) NOT NULL,
  `perm_id` int(11) NOT NULL,
  `perm_permission` enum('Y','N') NOT NULL,
  UNIQUE KEY `Permissions` (`perm_type`,`page_id`,`usergroup_id`,`perm_id`),
  KEY `page_pageid` (`page_id`),
  KEY `user_id` (`usergroup_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pragyanV3_users`
--

CREATE TABLE IF NOT EXISTS `pragyanV3_users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'user identification number',
  `user_name` varchar(100) NOT NULL COMMENT 'User''s good name',
  `user_email` varchar(100) NOT NULL,
  `user_fullname` varchar(100) NOT NULL COMMENT 'User''s full name',
  `user_password` varchar(32) NOT NULL,
  `user_regdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_lastlogin` datetime NOT NULL,
  `user_activated` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Used for email verification',
  `user_loginmethod` enum('openid','db','ldap','imap','ads') NOT NULL DEFAULT 'db' COMMENT 'Login Method',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Table structure for table `pragyanV3_widgets`
--

CREATE TABLE IF NOT EXISTS `pragyanV3_widgets` (
  `widget_id` int(11) NOT NULL,
  `widget_instanceid` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  `widget_location` int(11) NOT NULL,
  `widget_order` int(11) NOT NULL,
  `widget_propagate` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`widget_id`,`widget_instanceid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pragyanV3_widgetsconfig`
--

CREATE TABLE IF NOT EXISTS `pragyanV3_widgetsconfig` (
  `widget_id` int(11) NOT NULL,
  `widget_instanceid` int(11) NOT NULL,
  `config_name` varchar(128) NOT NULL,
  `config_value` longtext NOT NULL,
  PRIMARY KEY (`widget_id`,`widget_instanceid`,`config_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pragyanV3_widgetsconfiginfo`
--

CREATE TABLE IF NOT EXISTS `pragyanV3_widgetsconfiginfo` (
  `widget_id` int(11) NOT NULL,
  `config_name` varchar(128) NOT NULL,
  `config_type` enum('text','textarea','bool','integer','date','select','hidden','datetime','file','radio','checkbox','noinput') NOT NULL,
  `config_options` text NOT NULL,
  `config_displaytext` text NOT NULL,
  `config_default` longtext NOT NULL,
  `is_global` int(1) NOT NULL,
  `config_rank` int(10) NOT NULL,
  PRIMARY KEY (`widget_id`,`config_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pragyanV3_widgetsdata`
--

CREATE TABLE IF NOT EXISTS `pragyanV3_widgetsdata` (
  `widget_id` int(11) NOT NULL,
  `widget_instanceid` int(11) NOT NULL,
  `widget_datakey` varchar(500) NOT NULL,
  `widget_datavalue` longtext NOT NULL,
  PRIMARY KEY (`widget_id`,`widget_instanceid`,`widget_datakey`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pragyanV3_widgetsinfo`
--

CREATE TABLE IF NOT EXISTS `pragyanV3_widgetsinfo` (
  `widget_id` int(11) NOT NULL AUTO_INCREMENT,
  `widget_name` varchar(100) NOT NULL,
  `widget_classname` varchar(100) NOT NULL,
  `widget_description` mediumtext NOT NULL,
  `widget_version` varchar(27) NOT NULL,
  `widget_author` text,
  `widget_foldername` varchar(27) NOT NULL,
  PRIMARY KEY (`widget_id`),
  UNIQUE KEY `widget_foldername` (`widget_foldername`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------


-- --------------------------------------------------------

--
-- Table structure for table `prhospi_accomodation_status`
--

CREATE TABLE IF NOT EXISTS `prhospi_accomodation_status` (
  `page_modulecomponentid` int(11) NOT NULL,
  `hospi_room_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hospi_actual_checkin` datetime NOT NULL,
  `hospi_actual_checkout` datetime NOT NULL,
  `hospi_checkedin_by` int(11) NOT NULL,
  `hospi_cash_recieved` int(11) NOT NULL DEFAULT '0',
  `hospi_cash_refunded` int(1) NOT NULL,
  `hospi_printed` int(11) NOT NULL DEFAULT '0',
  `user_registered_by` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `prhospi_admin`
--

CREATE TABLE IF NOT EXISTS `prhospi_admin` (
  `page_modulecomponentid` int(11) NOT NULL,
  `page_getform_modulecomponentid` int(11) NOT NULL,
  `details_to_display` int(11) NOT NULL,
  `detail_required` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `prhospi_disclaimer`
--

CREATE TABLE IF NOT EXISTS `prhospi_disclaimer` (
  `page_modulecomponentid` int(11) NOT NULL,
  `disclaimer_team` varchar(30) NOT NULL,
  `disclaimer_desc` text NOT NULL,
  `team_cost` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `disclaimer_team` (`disclaimer_team`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `prhospi_hostel`
--

CREATE TABLE IF NOT EXISTS `prhospi_hostel` (
  `page_modulecomponentid` int(11) NOT NULL,
  `hospi_room_id` int(11) NOT NULL AUTO_INCREMENT,
  `hospi_hostel_name` varchar(11) NOT NULL,
  `hospi_room_capacity` int(11) NOT NULL DEFAULT '0',
  `hospi_room_no` int(11) NOT NULL DEFAULT '0',
  `hospi_floor` int(1) NOT NULL,
  `hospi_blocked` int(11) NOT NULL,
  PRIMARY KEY (`hospi_room_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
-- --------------------------------------------------------

--
-- Table structure for table `prhospi_pr_status`
--

CREATE TABLE IF NOT EXISTS `prhospi_pr_status` (
  `page_modulecomponentid` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hospi_checkin_time` datetime NOT NULL,
  `hospi_checkpout_time` datetime NOT NULL,
  `amount_recieved` int(11) NOT NULL,
  `amount_refunded` int(11) NOT NULL,
  `user_registered_by` int(11) NOT NULL DEFAULT '0'

) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `qaos1_bills`
--

CREATE TABLE IF NOT EXISTS `qaos1_bills` (
  `bill_no` int(11) NOT NULL AUTO_INCREMENT,
  `qaos1_eventid` int(11) NOT NULL,
  `page_modulecomponentid` int(11) NOT NULL,
  `qaos1_imgname` text NOT NULL,
  `userid` int(11) NOT NULL,
  `qaos1_cluster` varchar(100) NOT NULL,
  `qaos1_corp` varchar(100) NOT NULL,
  `qaos1_bill` varchar(100) NOT NULL,
  `qaos1_bill_date` date NOT NULL,
  `qaos1_amt` varchar(100) NOT NULL,
  `qaos1_tin` varchar(100) NOT NULL,
  PRIMARY KEY (`bill_no`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=30 ;


-- --------------------------------------------------------

--
-- Table structure for table `qaos1_disclaimer`
--

CREATE TABLE IF NOT EXISTS `qaos1_disclaimer` (
  `page_modulecomponentid` int(11) NOT NULL,
  `disclaimer_team` varchar(30) NOT NULL,
  `disclaimer_desc` text NOT NULL,
  UNIQUE KEY `disclaimer_team` (`disclaimer_team`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `qaos1_events`
--

CREATE TABLE IF NOT EXISTS `qaos1_events` (
  `events_id` int(11) NOT NULL AUTO_INCREMENT,
  `events_name` varchar(30) NOT NULL,
  `page_modulecomponentid` int(11) NOT NULL,
  PRIMARY KEY (`events_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=39 ;


-- --------------------------------------------------------

--
-- Table structure for table `qaos1_evtproc`
--

CREATE TABLE IF NOT EXISTS `qaos1_evtproc` (
  `evtproc_Id` int(11) NOT NULL AUTO_INCREMENT,
  `evtproc_Request` varchar(30) NOT NULL,
  `evtproc_Quantity` int(11) NOT NULL,
  `evtproc_Status` tinyint(4) NOT NULL,
  `evtproc_Desc` text NOT NULL,
  `evtproc_name` varchar(30) NOT NULL,
  `modulecomponentid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `evtproc_reason` text NOT NULL,
  `evtproc_date` text NOT NULL,
  PRIMARY KEY (`evtproc_Id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `qaos1_fundreq`
--

CREATE TABLE IF NOT EXISTS `qaos1_fundreq` (
  `fundreq_Id` int(11) NOT NULL AUTO_INCREMENT,
  `fundreq_Request` varchar(30) NOT NULL,
  `fundreq_Quantity` int(11) NOT NULL,
  `fundreq_Amount` int(11) NOT NULL,
  `fundreq_Status` tinyint(4) NOT NULL,
  `fundreq_Desc` text NOT NULL,
  `fundreq_name` varchar(30) NOT NULL,
  `modulecomponentid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `fundreq_reason` text NOT NULL,
  `fundreq_date` text NOT NULL,
  PRIMARY KEY (`fundreq_Id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Table structure for table `qaos_designations`
--

CREATE TABLE IF NOT EXISTS `qaos_designations` (
  `page_modulecomponentid` int(11) NOT NULL,
  `qaos_designation_id` int(11) NOT NULL,
  `qaos_designation_name` varchar(50) NOT NULL,
  `qaos_designation_description` text,
  `qaos_designation_priority` mediumint(9) NOT NULL DEFAULT '0' COMMENT 'tells the priority of the designaiton, by default it is 0, for chairman =5, core members =4, managers = 3, coordinators = 2 and volunteers =1',
  UNIQUE KEY `qaos_designation_id` (`qaos_designation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `qaos_scoring`
--

CREATE TABLE IF NOT EXISTS `qaos_scoring` (
  `page_modulecomponentid` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `targetuser_id` int(11) NOT NULL DEFAULT '0',
  `qaos_score1` mediumint(9) NOT NULL,
  `qaos_score2` mediumint(9) NOT NULL,
  `qaos_score3` mediumint(9) NOT NULL,
  `qaos_score4` mediumint(9) NOT NULL,
  `qaos_score5` mediumint(9) NOT NULL,
  `qaos_reason1` text,
  `qaos_reason2` text,
  `qaos_reason3` text,
  `qaos_reason4` text,
  `qaos_reason5` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `qaos_teams`
--

CREATE TABLE IF NOT EXISTS `qaos_teams` (
  `page_modulecomponentid` int(11) NOT NULL,
  `qaos_team_id` int(11) NOT NULL,
  `qaos_team_name` varchar(200) NOT NULL,
  `qaos_team_description` text,
  `qaos_representative_user_id` int(11) NOT NULL,
  UNIQUE KEY `qaos_team_id` (`qaos_team_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `qaos_tree`
--

CREATE TABLE IF NOT EXISTS `qaos_tree` (
  `page_modulecomponentid` int(11) NOT NULL,
  `qaos_unit_id` int(11) NOT NULL,
  `qaos_parentunit_id` int(11) NOT NULL,
  PRIMARY KEY (`qaos_unit_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `qaos_units`
--

CREATE TABLE IF NOT EXISTS `qaos_units` (
  `page_modulecomponentid` int(11) NOT NULL,
  `qaos_unit_id` int(11) NOT NULL,
  `qaos_team_id` int(11) NOT NULL,
  `qaos_designation_id` int(11) NOT NULL,
  `score_team` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `qaos_users`
--

CREATE TABLE IF NOT EXISTS `qaos_users` (
  `page_modulecomponentid` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `qaos_unit_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `qaos_version`
--

CREATE TABLE IF NOT EXISTS `qaos_version` (
  `page_modulecomponentid` int(11) NOT NULL,
  `qaos_version` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_answersubmissions`
--

CREATE TABLE IF NOT EXISTS `quiz_answersubmissions` (
  `page_modulecomponentid` int(11) NOT NULL COMMENT 'Quiz ID',
  `quiz_sectionid` int(11) NOT NULL COMMENT 'Section ID',
  `quiz_questionid` int(11) NOT NULL COMMENT 'Question ID',
  `user_id` int(11) NOT NULL COMMENT 'User ID',
  `quiz_questionrank` int(11) NOT NULL COMMENT 'The rank of the question in the page',
  `quiz_submittedanswer` text NOT NULL COMMENT 'Answer submitted by the user',
  `quiz_questionviewtime` datetime DEFAULT NULL COMMENT 'When the user saw this question for the first time',
  `quiz_answersubmittime` datetime DEFAULT NULL COMMENT 'When the user submitted his answer',
  `quiz_marksallotted` decimal(5,2) DEFAULT NULL COMMENT 'Marks allotted for the given question',
  UNIQUE KEY `page_modulecomponentid` (`page_modulecomponentid`,`quiz_sectionid`,`quiz_questionid`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_descriptions`
--

CREATE TABLE IF NOT EXISTS `quiz_descriptions` (
  `page_modulecomponentid` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Quiz ID',
  `quiz_title` varchar(260) NOT NULL,
  `quiz_headertext` text NOT NULL COMMENT 'Text shown before the user clicks Start Test',
  `quiz_submittext` text NOT NULL COMMENT 'Text shown once the user submits the test',
  `quiz_quiztype` enum('simple','gre') NOT NULL,
  `quiz_testduration` time NOT NULL,
  `quiz_questionspertest` int(11) NOT NULL,
  `quiz_questionsperpage` int(11) NOT NULL,
  `quiz_timeperpage` int(11) NOT NULL,
  `quiz_startdatetime` datetime NOT NULL COMMENT 'When the quiz should open to users',
  `quiz_enddatetime` datetime NOT NULL COMMENT 'When the quiz should close to users',
  `quiz_allowsectionrandomaccess` tinyint(1) NOT NULL COMMENT 'Whether sections can be accessed in any order by the user, or must be accessed in the same order they were created',
  `quiz_mixsections` tinyint(1) NOT NULL,
  `quiz_showquiztimer` tinyint(1) NOT NULL COMMENT 'Whether the quiz timer must be shown',
  `quiz_showpagetimer` tinyint(1) NOT NULL COMMENT 'Whether the page timer must be shown',
  PRIMARY KEY (`page_modulecomponentid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_objectiveoptions`
--

CREATE TABLE IF NOT EXISTS `quiz_objectiveoptions` (
  `page_modulecomponentid` int(11) NOT NULL COMMENT 'Quiz ID',
  `quiz_sectionid` int(11) NOT NULL COMMENT 'Section ID',
  `quiz_questionid` int(11) NOT NULL COMMENT 'Question ID',
  `quiz_optionid` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Option ID',
  `quiz_optiontext` text NOT NULL COMMENT 'The option itself!',
  `quiz_optionrank` int(11) NOT NULL COMMENT 'A rank, according to which options will be ordered',
  PRIMARY KEY (`page_modulecomponentid`,`quiz_sectionid`,`quiz_questionid`,`quiz_optionid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE IF NOT EXISTS `quiz_questions` (
  `page_modulecomponentid` int(11) NOT NULL COMMENT 'Quiz ID',
  `quiz_sectionid` int(11) NOT NULL COMMENT 'Section ID',
  `quiz_questionid` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Question ID',
  `quiz_question` text NOT NULL COMMENT 'The question',
  `quiz_questiontype` enum('sso','mso','subjective') NOT NULL COMMENT 'Whether the question is single select objective, multiselect objective, or subjective',
  `quiz_questionrank` int(11) NOT NULL COMMENT 'A rank to determine the ordering of questions in a section',
  `quiz_questionweight` int(11) NOT NULL COMMENT 'Question difficulty level, based on which positive and negative marks may be alloted',
  `quiz_answermaxlength` int(11) NOT NULL COMMENT 'Maximum number of characters in the answer, in case it''s a subjective question',
  `quiz_rightanswer` text NOT NULL COMMENT 'The correct answer for the question. In case of sso, the option id, in case of mmo, a delimited set of options ids, and in case of subjective, a hint to the human correcting the quiz',
  PRIMARY KEY (`page_modulecomponentid`,`quiz_sectionid`,`quiz_questionid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_sections`
--

CREATE TABLE IF NOT EXISTS `quiz_sections` (
  `page_modulecomponentid` int(11) NOT NULL COMMENT 'Quiz ID',
  `quiz_sectionid` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Section ID',
  `quiz_sectiontitle` varchar(260) NOT NULL COMMENT 'Section Title',
  `quiz_sectionssocount` int(11) NOT NULL COMMENT 'Number of Single Select Objective questions to be taken from this section',
  `quiz_sectionmsocount` int(11) NOT NULL COMMENT 'Number of Multiselect Objective questions to be taken from this section',
  `quiz_sectionsubjectivecount` int(11) NOT NULL,
  `quiz_sectiontimelimit` time NOT NULL COMMENT 'Amount of time a user may spend on this section (taken from the time when he first opened this section)',
  `quiz_sectionquestionshuffled` tinyint(1) NOT NULL COMMENT 'Whether questions should be shuffled (1), or taken in the order given by question_rank (0)',
  `quiz_sectionrank` int(11) NOT NULL,
  `quiz_sectionshowlimit` tinyint(4) NOT NULL DEFAULT '1' COMMENT 'Whether the section remaining time limit should be displayed(1) or not(0)',
  PRIMARY KEY (`page_modulecomponentid`,`quiz_sectionid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_userattempts`
--

CREATE TABLE IF NOT EXISTS `quiz_userattempts` (
  `page_modulecomponentid` int(11) NOT NULL COMMENT 'Quiz ID',
  `quiz_sectionid` int(11) NOT NULL COMMENT 'Section ID',
  `user_id` int(11) NOT NULL COMMENT 'User ID',
  `quiz_attemptstarttime` datetime NOT NULL COMMENT 'Time when the user started the quiz',
  `quiz_submissiontime` datetime DEFAULT NULL COMMENT 'Time when the user submitted the quiz. If an entry exists here, with this field null, the user has started the quiz, but hasn''t completed it yet.',
  `quiz_marksallotted` decimal(5,2) DEFAULT NULL COMMENT 'Total marks the person scored',
  UNIQUE KEY `page_modulecomponentid` (`page_modulecomponentid`,`quiz_sectionid`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_weightmarks`
--

CREATE TABLE IF NOT EXISTS `quiz_weightmarks` (
  `page_modulecomponentid` int(11) NOT NULL COMMENT 'Quiz ID',
  `question_weight` int(11) NOT NULL COMMENT 'Question Weight',
  `question_positivemarks` decimal(5,2) NOT NULL COMMENT 'Marks allotted in case a correct answer was submitted',
  `question_negativemarks` decimal(5,2) NOT NULL COMMENT 'Marks deducted in case a wrong answer was submitted',
  UNIQUE KEY `page_modulecomponentid` (`page_modulecomponentid`,`question_weight`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `safedit_sections`
--

CREATE TABLE IF NOT EXISTS `safedit_sections` (
  `page_modulecomponentid` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `section_heading` varchar(256) DEFAULT NULL,
  `section_type` varchar(64) DEFAULT NULL,
  `section_show` tinyint(4) NOT NULL,
  `section_priority` int(11) DEFAULT NULL,
  `section_content` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `share`
--

CREATE TABLE IF NOT EXISTS `share` (
  `page_modulecomponentid` int(11) NOT NULL,
  `page_desc` text NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `maxfile_size` int(11) NOT NULL,
  PRIMARY KEY (`page_modulecomponentid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `share_comments`
--

CREATE TABLE IF NOT EXISTS `share_comments` (
  `comment_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `page_modulecomponentid` int(11) NOT NULL,
  `comment` text NOT NULL,
  `userid` int(11) NOT NULL,
  `comment_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`comment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `share_files`
--

CREATE TABLE IF NOT EXISTS `share_files` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `page_modulecomponentid` int(11) NOT NULL,
  `upload_filename` varchar(50) NOT NULL,
  `file_name` varchar(50) NOT NULL,
  `file_desc` text NOT NULL,
  `upload_userid` int(11) NOT NULL,
  PRIMARY KEY (`file_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Table structure for table `sqlquery_desc`
--

CREATE TABLE IF NOT EXISTS `sqlquery_desc` (
  `page_modulecomponentid` int(11) NOT NULL COMMENT 'Module Component Id',
  `sqlquery_title` varchar(260) DEFAULT NULL COMMENT 'Title',
  `sqlquery_query` text NOT NULL COMMENT 'Query',
  UNIQUE KEY `page_modulecomponentid` (`page_modulecomponentid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
