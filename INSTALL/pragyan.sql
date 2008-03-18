-- phpMyAdmin SQL Dump
-- version 2.11.0
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 18, 2008 at 11:25 PM
-- Server version: 5.0.45
-- PHP Version: 5.2.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `pragyan`
--

-- --------------------------------------------------------

--
-- Table structure for table `article_content`
--

CREATE TABLE IF NOT EXISTS `article_content` (
  `page_modulecomponentid` int(11) NOT NULL,
  `article_content` text NOT NULL,
  `article_lastupdated` timestamp NOT NULL default '0000-00-00 00:00:00' on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`page_modulecomponentid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `article_content`
--

INSERT INTO `article_content` (`page_modulecomponentid`, `article_content`, `article_lastupdated`) VALUES
(1, '<h1>Welcome to Pragyan CMS!</h1>\r\n<p>&nbsp;</p>\r\n<p>The permissions are visible near the top of this page. You can edit the content of this page, add or remove permissions to other users, create, copy, delete pages, forms,quizzes,etc. or change other settings, change your preferences, and do some more! A page is of type aricle.</p>', '2008-03-15 23:49:16'),
(2, 'Coming up Soon!!!', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `article_contentbak`
--

CREATE TABLE IF NOT EXISTS `article_contentbak` (
  `page_modulecomponentid` int(11) NOT NULL,
  `article_revision` int(11) NOT NULL,
  `article_diff` text NOT NULL,
  `article_updatetime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL,
  KEY `page_mdulecomponentid` (`page_modulecomponentid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `article_contentbak`
--

INSERT INTO `article_contentbak` (`page_modulecomponentid`, `article_revision`, `article_diff`, `article_updatetime`, `user_id`) VALUES
(1, 1, 'ds', '2008-03-15 23:48:11', 1),
(1, 2, '<h1>Welcome to Pragyan CMS!</h1>\r\n<p>&nbsp;</p>\r\n<p>The permissions are visible near the top of this page. You can edit the content of this page, add or remove permissions to other users, create, copy, delete or change other settings, change your preferences, and do some more!</p>', '2008-03-15 23:49:16', 1);

-- --------------------------------------------------------

--
-- Table structure for table `billing_article`
--

CREATE TABLE IF NOT EXISTS `billing_article` (
  `page_modulecomponentid` int(11) NOT NULL,
  `billing_articleid` int(11) NOT NULL,
  `billing_shopname` varchar(128) NOT NULL,
  `billing_shopcouponcolor` varchar(7) NOT NULL,
  `billing_articlename` varchar(100) NOT NULL,
  `billing_price` int(11) NOT NULL,
  `billing_availability` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`billing_articleid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `billing_article`
--


-- --------------------------------------------------------

--
-- Table structure for table `billing_transactiondetails`
--

CREATE TABLE IF NOT EXISTS `billing_transactiondetails` (
  `page_modulecomponentid` int(11) NOT NULL,
  `billing_transactionid` int(11) NOT NULL,
  `billing_articleid` int(11) NOT NULL,
  `billing_articlequantity` int(2) NOT NULL,
  `billing_articlecost` decimal(5,2) NOT NULL COMMENT 'Cost of the article at the time of purchase'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `billing_transactiondetails`
--


-- --------------------------------------------------------

--
-- Table structure for table `billing_transactions`
--

CREATE TABLE IF NOT EXISTS `billing_transactions` (
  `page_modulecomponentid` int(11) NOT NULL,
  `billing_transactionid` int(11) NOT NULL,
  `billing_sellerid` int(11) NOT NULL,
  `billing_buyer` varchar(10) NOT NULL,
  `billing_amountpaid` decimal(5,2) NOT NULL,
  `billing_paymentmethod` enum('messbill','cash') NOT NULL default 'messbill',
  `billing_transactiontime` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `billing_transaction_status` tinyint(1) NOT NULL default '1'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `billing_transactions`
--


-- --------------------------------------------------------

--
-- Table structure for table `food_transactions`
--

CREATE TABLE IF NOT EXISTS `food_transactions` (
  `billing_buyer` varchar(10) default NULL,
  `SUM( billing_amountpaid )` decimal(27,2) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `food_transactions`
--


-- --------------------------------------------------------

--
-- Table structure for table `form_desc`
--

CREATE TABLE IF NOT EXISTS `form_desc` (
  `page_modulecomponentid` int(11) NOT NULL,
  `form_heading` varchar(200) NOT NULL,
  `form_loginrequired` tinyint(1) NOT NULL default '1',
  `form_headertext` text,
  `form_footertext` text,
  `form_expirydatetime` datetime default NULL,
  `form_sendconfirmation` tinyint(1) NOT NULL default '0',
  `form_usecaptcha` tinyint(1) NOT NULL default '0',
  `form_allowuseredit` tinyint(1) NOT NULL default '1',
  `form_allowuserunregister` tinyint(1) NOT NULL default '0',
  `form_showuseremail` tinyint(1) NOT NULL default '1',
  `form_showuserfullname` tinyint(1) NOT NULL default '0',
  `form_showuserprofiledata` tinyint(1) NOT NULL default '0',
  `form_showregistrationdate` tinyint(1) NOT NULL default '1',
  `form_showlastupdatedate` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`page_modulecomponentid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `form_desc`
--


-- --------------------------------------------------------

--
-- Table structure for table `form_elementdata`
--

CREATE TABLE IF NOT EXISTS `form_elementdata` (
  `user_id` int(11) NOT NULL default '0',
  `page_modulecomponentid` int(11) NOT NULL default '0',
  `form_elementid` int(11) NOT NULL default '0',
  `form_elementdata` text NOT NULL,
  PRIMARY KEY  (`user_id`,`page_modulecomponentid`,`form_elementid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `form_elementdata`
--


-- --------------------------------------------------------

--
-- Table structure for table `form_elementdesc`
--

CREATE TABLE IF NOT EXISTS `form_elementdesc` (
  `page_modulecomponentid` int(11) NOT NULL default '0',
  `form_elementid` int(11) NOT NULL default '0',
  `form_elementname` varchar(100) NOT NULL,
  `form_elementdisplaytext` varchar(500) NOT NULL COMMENT 'Description of data held',
  `form_elementtype` enum('text','textarea','radio','checkbox','select','password','file','date','datetime') NOT NULL default 'text',
  `form_elementsize` int(11) default NULL,
  `form_elementtypeoptions` text,
  `form_elementdefaultvalue` varchar(400) default NULL,
  `form_elementmorethan` varchar(400) default NULL,
  `form_elementlessthan` varchar(400) default NULL,
  `form_elementcheckint` tinyint(1) NOT NULL default '0' COMMENT 'Check if it is int if 1',
  `form_elementtooltiptext` text NOT NULL,
  `form_elementisrequired` tinyint(1) NOT NULL default '0',
  `form_elementrank` int(11) NOT NULL default '0',
  PRIMARY KEY  (`page_modulecomponentid`,`form_elementid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `form_elementdesc`
--


-- --------------------------------------------------------

--
-- Table structure for table `form_regdata`
--

CREATE TABLE IF NOT EXISTS `form_regdata` (
  `user_id` int(11) NOT NULL,
  `page_modulecomponentid` int(11) NOT NULL,
  `form_firstupdated` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `form_lastupdated` timestamp NULL default NULL,
  `form_verified` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`user_id`,`page_modulecomponentid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `form_regdata`
--


-- --------------------------------------------------------

--
-- Table structure for table `forum_module`
--

CREATE TABLE IF NOT EXISTS `forum_module` (
  `page_modulecomponentid` int(11) NOT NULL,
  `forum_name` varchar(255) NOT NULL,
  `forum_description` text NOT NULL,
  `forum_moderated` tinyint(1) NOT NULL default '1' COMMENT '(1-Moderated & 0-Public)',
  `last_post_userid` int(11) NOT NULL,
  `last_post_datetime` datetime NOT NULL,
  PRIMARY KEY  (`page_modulecomponentid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `forum_module`
--


-- --------------------------------------------------------

--
-- Table structure for table `forum_posts`
--

CREATE TABLE IF NOT EXISTS `forum_posts` (
  `page_modulecomponentid` int(11) NOT NULL,
  `forum_thread_id` int(11) NOT NULL default '0' COMMENT 'thread_id from forum_question',
  `forum_post_id` int(11) NOT NULL default '0',
  `forum_post_user_id` int(11) NOT NULL,
  `forum_post_title` varchar(255) default NULL,
  `forum_post_content` longtext NOT NULL,
  `forum_post_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `forum_post_approve` int(1) NOT NULL default '0' COMMENT 'here approve is for the replies',
  PRIMARY KEY  (`page_modulecomponentid`,`forum_thread_id`,`forum_post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `forum_posts`
--


-- --------------------------------------------------------

--
-- Table structure for table `forum_threads`
--

CREATE TABLE IF NOT EXISTS `forum_threads` (
  `forum_thread_id` int(11) NOT NULL,
  `page_modulecomponentid` int(11) NOT NULL,
  `forum_thread_category` varchar(15) NOT NULL default 'general' COMMENT '(General/Sticky)',
  `forum_access_status` varchar(15) NOT NULL default 'moderated' COMMENT 'moderated/public',
  `forum_thread_topic` varchar(255) NOT NULL,
  `forum_detail` longtext NOT NULL,
  `forum_thread_user_id` int(11) NOT NULL,
  `forum_thread_datetime` varchar(50) NOT NULL,
  `forum_post_approve` int(1) NOT NULL default '0',
  `forum_thread_viewcount` int(11) NOT NULL default '0',
  `forum_thread_last_post_userid` int(11) NOT NULL,
  `forum_thread_lastpost_date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`forum_thread_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `forum_threads`
--


-- --------------------------------------------------------

--
-- Table structure for table `gallery_name`
--

CREATE TABLE IF NOT EXISTS `gallery_name` (
  `page_modulecomponentid` int(11) NOT NULL,
  `gallery_name` varchar(50) NOT NULL,
  `gallery_desc` varchar(200) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gallery_name`
--


-- --------------------------------------------------------

--
-- Table structure for table `gallery_pics`
--

CREATE TABLE IF NOT EXISTS `gallery_pics` (
  `upload_filename` varchar(200) NOT NULL,
  `page_modulecomponentid` int(11) NOT NULL,
  `gallery_filecomment` varchar(200) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gallery_pics`
--


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
  `hospi_projected_checkin` datetime NOT NULL,
  `hospi_actual_checkin` datetime NOT NULL,
  `hospi_projected_checkout` datetime NOT NULL,
  `hospi_actual_checkout` datetime NOT NULL,
  `hospi_checkedin_by` int(11) NOT NULL,
  `hospi_cash_collected` tinyint(1) NOT NULL default '0',
  `hospi_checkedout_by` int(11) NOT NULL,
  `hospi_cash_refunded` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `hospi_accomodation_status`
--


-- --------------------------------------------------------

--
-- Table structure for table `hospi_hostel`
--

CREATE TABLE IF NOT EXISTS `hospi_hostel` (
  `page_modulecomponentid` int(11) NOT NULL,
  `hospi_room_id` int(11) NOT NULL,
  `hospi_hostel_name` varchar(11) NOT NULL,
  `hospi_room_capacity` int(11) NOT NULL default '0',
  `hospi_room_no` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `hospi_hostel`
--


-- --------------------------------------------------------

--
-- Table structure for table `newsletter`
--

CREATE TABLE IF NOT EXISTS `newsletter` (
  `email` varchar(255) NOT NULL,
  `blocked` tinyint(1) NOT NULL default '0',
  `sent` tinyint(1) NOT NULL,
  `mail2` timestamp NULL default NULL,
  `mail3` timestamp NULL default NULL,
  `mail4` timestamp NULL default NULL,
  UNIQUE KEY `user_email_2` (`email`),
  KEY `user_email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=0;

--
-- Dumping data for table `newsletter`
--


-- --------------------------------------------------------

--
-- Table structure for table `newsletter_bc`
--

CREATE TABLE IF NOT EXISTS `newsletter_bc` (
  `email` varchar(100) NOT NULL,
  `mail1` timestamp NULL default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `newsletter_bc`
--


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
  `news_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `news_link` varchar(100) NOT NULL,
  KEY `news_id` (`news_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `news_data`
--


-- --------------------------------------------------------

--
-- Table structure for table `news_desc`
--

CREATE TABLE IF NOT EXISTS `news_desc` (
  `page_modulecomponentid` int(11) NOT NULL,
  `news_title` varchar(150) NOT NULL,
  `news_description` varchar(1000) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `news_desc`
--


-- --------------------------------------------------------

--
-- Table structure for table `poll_answers`
--

CREATE TABLE IF NOT EXISTS `poll_answers` (
  `page_modulecomponentid` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `poll_answer` varchar(200) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `poll_answers`
--


-- --------------------------------------------------------

--
-- Table structure for table `poll_questions`
--

CREATE TABLE IF NOT EXISTS `poll_questions` (
  `page_modulecomponentid` int(11) NOT NULL,
  `poll_question` varchar(500) NOT NULL,
  `poll_numberofoption` tinyint(4) NOT NULL,
  `poll_option1` varchar(100) NOT NULL,
  `poll_option2` varchar(100) NOT NULL,
  `poll_option3` varchar(100) NOT NULL,
  `poll_option4` varchar(100) NOT NULL,
  `poll_option5` varchar(100) NOT NULL,
  `poll_option6` varchar(100) NOT NULL,
  `poll_option7` varchar(100) NOT NULL,
  `poll_option8` varchar(100) NOT NULL,
  `poll_option9` varchar(100) NOT NULL,
  `poll_option10` varchar(100) NOT NULL,
  UNIQUE KEY `componentid` (`page_modulecomponentid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `poll_questions`
--


-- --------------------------------------------------------

--
-- Table structure for table `pragyanV2_external`
--

CREATE TABLE IF NOT EXISTS `pragyanV2_external` (
  `page_modulecomponentid` int(11) NOT NULL,
  `page_extlink` varchar(500) NOT NULL,
  PRIMARY KEY  (`page_modulecomponentid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Used to store all external links';

--
-- Dumping data for table `pragyanV2_external`
--


-- --------------------------------------------------------

--
-- Table structure for table `pragyanV2_groups`
--

CREATE TABLE IF NOT EXISTS `pragyanV2_groups` (
  `group_id` int(11) NOT NULL auto_increment COMMENT 'Identification number of the group',
  `group_name` varchar(100) NOT NULL COMMENT 'Group name',
  `group_description` varchar(200) NOT NULL COMMENT 'Group description',
  `group_priority` int(11) NOT NULL default '0' COMMENT 'Used for permissions',
  `form_id` int(11) NOT NULL default '0' COMMENT '0 if not associated with a form',
  PRIMARY KEY  (`group_id`),
  UNIQUE KEY `groupName` (`group_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `pragyanV2_groups`
--


-- --------------------------------------------------------

--
-- Table structure for table `pragyanV2_inheritedinfo`
--

CREATE TABLE IF NOT EXISTS `pragyanV2_inheritedinfo` (
  `page_inheritedinfoid` int(11) NOT NULL COMMENT 'Inherited info id from the pages table',
  `page_inheritedinfocontent` text NOT NULL COMMENT 'Inherited information (like banner), can be used anywhere desired in template'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pragyanV2_inheritedinfo`
--


-- --------------------------------------------------------

--
-- Table structure for table `pragyanV2_log`
--

CREATE TABLE IF NOT EXISTS `pragyanV2_log` (
  `log_no` int(11) NOT NULL,
  `log_datetime` timestamp NOT NULL default CURRENT_TIMESTAMP,
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

--
-- Dumping data for table `pragyanV2_log`
--

INSERT INTO `pragyanV2_log` (`log_no`, `log_datetime`, `user_email`, `user_id`, `page_path`, `page_id`, `perm_module`, `perm_action`, `user_accessipaddress`) VALUES
(1, '2008-03-15 23:31:59', '', 0, '', 0, '', '', ''),
(2, '2008-03-15 23:32:03', 'Anonymous', 0, '/', 0, 'article', 'settings', '10.0.0.126'),
(3, '2008-03-15 23:32:21', 'Anonymous', 0, '/', 0, 'article', 'settings', '10.0.0.126'),
(4, '2008-03-15 23:32:26', 'Anonymous', 0, '/', 0, 'article', 'view', '10.0.0.126'),
(5, '2008-03-15 23:35:07', 'Anonymous', 0, '/', 0, 'article', 'login', '10.0.0.126'),
(6, '2008-03-15 23:37:26', 'admin@cms.org', 1, '/', 0, 'article', 'logout', '10.0.0.126'),
(7, '2008-03-15 23:37:29', 'Anonymous', 0, '/', 0, 'article', 'logout', '10.0.0.126'),
(8, '2008-03-15 23:37:42', 'Anonymous', 0, '/', 0, 'article', 'login', '10.0.0.126'),
(9, '2008-03-15 23:39:01', 'admin@cms.org', 1, '/page1/', 1, 'article', 'view', '10.0.0.126'),
(10, '2008-03-15 23:39:05', 'admin@cms.org', 1, '/', 0, 'article', 'view', '10.0.0.126'),
(11, '2008-03-15 23:41:23', 'admin@cms.org', 1, '/page1/', 1, 'article', 'view', '10.0.0.126'),
(12, '2008-03-15 23:45:48', 'admin@cms.org', 1, '/', 0, 'article', 'view', '10.0.0.126'),
(13, '2008-03-15 23:45:54', 'admin@cms.org', 1, '/', 0, 'article', 'edit', '10.0.0.126'),
(14, '2008-03-15 23:48:11', 'admin@cms.org', 1, '/', 0, 'article', 'edit', '10.0.0.126'),
(15, '2008-03-15 23:48:19', 'admin@cms.org', 1, '/', 0, 'article', 'edit', '10.0.0.126'),
(16, '2008-03-15 23:49:16', 'admin@cms.org', 1, '/', 0, 'article', 'edit', '10.0.0.126'),
(17, '2008-03-15 23:49:28', 'admin@cms.org', 1, '/page1/', 1, 'article', 'view', '10.0.0.126'),
(18, '2008-03-15 23:49:31', 'admin@cms.org', 1, '/', 0, 'article', 'view', '10.0.0.126'),
(19, '2008-03-15 23:49:35', 'admin@cms.org', 1, '/', 0, 'article', 'settings', '10.0.0.126'),
(20, '2008-03-15 23:51:33', 'admin@cms.org', 1, '/', 0, 'article', 'settings', '10.0.0.126'),
(21, '2008-03-15 23:51:42', 'admin@cms.org', 1, '/', 0, 'article', 'view', '10.0.0.126'),
(22, '2008-03-15 23:52:19', 'admin@cms.org', 1, '/', 0, 'article', 'view', '10.0.0.126'),
(23, '2008-03-15 23:52:44', 'admin@cms.org', 1, '/hospitality/', -1, '', 'view', '10.0.0.126'),
(24, '2008-03-15 23:52:46', 'admin@cms.org', 1, '/', 0, 'article', 'view', '10.0.0.126'),
(25, '2008-03-15 23:52:49', 'admin@cms.org', 1, '/workshops/', -1, '', 'view', '10.0.0.126'),
(26, '2008-03-15 23:52:52', 'admin@cms.org', 1, '/', 0, 'article', 'view', '10.0.0.126'),
(27, '2008-03-15 23:53:09', 'admin@cms.org', 1, '/otherlinks/', -1, '', 'view', '10.0.0.126'),
(28, '2008-03-15 23:54:02', 'admin@cms.org', 1, '/otherlinks/', -1, '', 'view', '10.0.0.126');

-- --------------------------------------------------------

--
-- Table structure for table `pragyanV2_pages`
--

CREATE TABLE IF NOT EXISTS `pragyanV2_pages` (
  `page_id` int(11) NOT NULL auto_increment COMMENT 'Page identification number',
  `page_name` varchar(32) NOT NULL COMMENT 'Name of the page',
  `page_parentid` int(11) NOT NULL COMMENT 'ID of the parent of the page',
  `page_createdtime` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'Time when the page was created',
  `page_lastaccesstime` timestamp NOT NULL default '0000-00-00 00:00:00' COMMENT 'Time when the page was last accessed',
  `page_title` varchar(128) NOT NULL default 'New Page' COMMENT 'Title of the page',
  `page_module` enum('article','form','link','quiz','menu','external','poll','forum','gallery','news','sitemap','hospi','qaos','pr','billing') NOT NULL default 'article' COMMENT 'Module type of the page',
  `page_modulecomponentid` int(11) NOT NULL COMMENT 'Component id used in the module',
  `page_menurank` int(11) NOT NULL COMMENT 'Rank for menu ordering',
  `page_inheritedinfoid` int(11) NOT NULL default '-1' COMMENT 'Inherited info table mapping',
  `page_displayinmenu` tinyint(1) NOT NULL default '1' COMMENT 'To display in menu bar or not',
  `page_displaymenu` tinyint(1) NOT NULL default '1' COMMENT 'Tells if menu should be displayed at all',
  `page_displaysiblingmenu` tinyint(1) NOT NULL default '1' COMMENT 'Tells if sibling menu is displayed',
  PRIMARY KEY  (`page_id`),
  UNIQUE KEY `unique parent` (`page_parentid`,`page_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=842 ;

--
-- Dumping data for table `pragyanV2_pages`
--

INSERT INTO `pragyanV2_pages` (`page_id`, `page_name`, `page_parentid`, `page_createdtime`, `page_lastaccesstime`, `page_title`, `page_module`, `page_modulecomponentid`, `page_menurank`, `page_inheritedinfoid`, `page_displayinmenu`, `page_displaymenu`, `page_displaysiblingmenu`) VALUES
(0, '', 0, '2008-03-15 19:43:07', '2008-03-15 23:52:52', 'home', 'article', 1, 0, -1, 1, 1, 1),
(1, 'page1', 0, '2008-03-15 23:32:21', '2008-03-15 23:49:28', 'page1', 'article', 2, 1, -1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `pragyanV2_permissionlist`
--

CREATE TABLE IF NOT EXISTS `pragyanV2_permissionlist` (
  `perm_id` int(11) NOT NULL,
  `page_module` enum('page','article','billing','form','forum','gallery','hospi','news','poll','pr','quiz','qaos','sitemap') NOT NULL,
  `perm_action` varchar(100) NOT NULL,
  `perm_text` varchar(200) NOT NULL,
  `perm_rank` int(11) NOT NULL COMMENT 'The order of being shown in actionbar',
  `perm_description` varchar(200) NOT NULL,
  PRIMARY KEY  (`perm_id`),
  UNIQUE KEY `permission type` (`page_module`,`perm_action`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='List of the available permissions';

--
-- Dumping data for table `pragyanV2_permissionlist`
--

INSERT INTO `pragyanV2_permissionlist` (`perm_id`, `page_module`, `perm_action`, `perm_text`, `perm_rank`, `perm_description`) VALUES
(3, 'article', 'view', 'View', 0, 'View the article'),
(5, 'article', 'edit', 'Edit', 0, 'Edit the article'),
(1, 'page', 'grant', 'Permissions', 0, 'Grant Permissions'),
(2, 'page', 'settings', 'Settings', 0, 'Page Settings'),
(35, 'quiz', 'view', 'View', 0, 'Take the quiz'),
(11, 'form', 'view', 'Register', 0, 'Register to a form'),
(13, 'form', 'editform', 'Edit Form', 0, 'Edit the structure of the form'),
(14, 'form', 'viewregistrants', 'View Registrants', 0, 'View Registrants'),
(10, 'form', 'editregistrants', 'Edit Registrants', 0, 'Edit Registrants'' info'),
(29, 'news', 'create', 'Create News', 20, 'Create a new News'),
(31, 'poll', 'view', 'Vote', 0, 'Vote for a poll'),
(30, 'poll', 'create', 'Create Poll', 0, 'Create a Poll'),
(21, 'gallery', 'view', 'View Gallery', 0, 'View the Gallery'),
(20, 'gallery', 'create', 'Create Gallery', 18, 'Create a new Gallery'),
(12, 'form', 'create', 'Create Form', 19, 'Create a new Form'),
(26, 'news', 'view', 'View', 0, 'VIew'),
(28, 'news', 'edit', 'Edit the news', 0, 'Edit the news item'),
(27, 'news', 'rssview', 'RSS View', 0, 'Retrieve News as RSS'),
(36, 'quiz', 'create', 'Create', 0, 'Create a New Quiz'),
(4, 'article', 'create', 'Create', 8, 'Create an aritcle'),
(34, 'quiz', 'edit', 'Edit', 2, 'Edit the Quiz'),
(41, 'sitemap', 'create', 'Create', 0, 'Create a sitemap'),
(42, 'sitemap', 'view', 'View', 0, 'View a sitemap'),
(37, 'quiz', 'correct', 'Correct', 3, 'Correct the Quiz Attempts'),
(23, 'hospi', 'accomodate', 'accomodate', 1, 'accomodate the hospi'),
(24, 'hospi', 'addroom', 'addroom', 1, 'addroom the hospi'),
(25, 'hospi', 'view', 'view', 1, 'view the hospi'),
(22, 'hospi', 'create', 'create', 1, 'create the hospi'),
(17, 'forum', 'create', 'create', 0, 'Create a forum'),
(16, 'forum', 'moderate', 'moderate', 0, 'Moderate the forums'),
(18, 'forum', 'post', 'post', 0, 'Post in forums'),
(15, 'forum', 'view', 'view', 0, 'View the posts'),
(19, 'gallery', 'edit', 'edit', 0, 'Edit the gallery'),
(40, 'qaos', 'edit', 'edit', 0, 'Edit qaos'),
(39, 'qaos', 'view', 'view', 0, 'View qaos'),
(38, 'qaos', 'create', 'create', 0, 'create the qaos module'),
(32, 'pr', 'view', 'View', 0, 'View'),
(33, 'pr', 'create', 'Create', 0, 'Create'),
(7, 'billing', 'view', 'View', 0, 'View'),
(6, 'billing', 'create', 'Create', 0, 'Create'),
(9, 'billing', 'account', 'Account', 0, 'Account'),
(8, 'billing', 'edititem', 'Edit Items', 0, 'Edit Items'),
(0, 'page', 'admin', 'admin', 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `pragyanV2_uploads`
--

CREATE TABLE IF NOT EXISTS `pragyanV2_uploads` (
  `page_modulecomponentid` int(11) NOT NULL,
  `page_module` enum('article','quiz','form','gallery') NOT NULL,
  `upload_fileid` int(11) NOT NULL,
  `upload_filename` varchar(200) NOT NULL,
  `upload_filetype` varchar(300) NOT NULL,
  `upload_time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `user_id` varchar(100) NOT NULL COMMENT 'The user who uploaded the file',
  PRIMARY KEY  (`upload_fileid`),
  UNIQUE KEY `page_modulecomponentid` (`page_modulecomponentid`,`page_module`,`upload_filename`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pragyanV2_uploads`
--


-- --------------------------------------------------------

--
-- Table structure for table `pragyanV2_usergroup`
--

CREATE TABLE IF NOT EXISTS `pragyanV2_usergroup` (
  `user_id` int(11) NOT NULL COMMENT 'user id ...comes from the user''s table',
  `group_id` int(11) NOT NULL COMMENT 'group id ...comes from the group''s table',
  KEY `user_id` (`user_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pragyanV2_usergroup`
--


-- --------------------------------------------------------

--
-- Table structure for table `pragyanV2_userpageperm`
--

CREATE TABLE IF NOT EXISTS `pragyanV2_userpageperm` (
  `perm_type` enum('user','group') NOT NULL,
  `page_id` int(11) NOT NULL,
  `usergroup_id` int(11) NOT NULL,
  `perm_id` int(11) NOT NULL,
  `perm_permission` enum('Y','N') NOT NULL,
  UNIQUE KEY `Permissions` (`perm_type`,`page_id`,`usergroup_id`,`perm_id`),
  KEY `page_pageid` (`page_id`),
  KEY `user_id` (`usergroup_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pragyanV2_userpageperm`
--

INSERT INTO `pragyanV2_userpageperm` (`perm_type`, `page_id`, `usergroup_id`, `perm_id`, `perm_permission`) VALUES
('user', 0, 1, 0, 'Y'),
('user', 0, 1, 1, 'Y'),
('user', 0, 1, 2, 'Y'),
('user', 0, 1, 3, 'Y'),
('user', 0, 1, 4, 'Y'),
('user', 0, 1, 5, 'Y'),
('user', 0, 1, 6, 'Y'),
('user', 0, 1, 7, 'Y'),
('user', 0, 1, 8, 'Y'),
('user', 0, 1, 9, 'Y'),
('user', 0, 1, 10, 'Y'),
('user', 0, 1, 11, 'Y'),
('user', 0, 1, 12, 'Y'),
('user', 0, 1, 13, 'Y'),
('user', 0, 1, 14, 'Y'),
('user', 0, 1, 15, 'Y'),
('user', 0, 1, 16, 'Y'),
('user', 0, 1, 17, 'Y'),
('user', 0, 1, 18, 'Y'),
('user', 0, 1, 19, 'Y'),
('user', 0, 1, 20, 'Y'),
('user', 0, 1, 21, 'Y'),
('user', 0, 1, 22, 'Y'),
('user', 0, 1, 23, 'Y'),
('user', 0, 1, 24, 'Y'),
('user', 0, 1, 25, 'Y'),
('user', 0, 1, 26, 'Y'),
('user', 0, 1, 27, 'Y'),
('user', 0, 1, 28, 'Y'),
('user', 0, 1, 29, 'Y'),
('user', 0, 1, 30, 'Y'),
('user', 0, 1, 31, 'Y'),
('user', 0, 1, 32, 'Y'),
('user', 0, 1, 33, 'Y'),
('user', 0, 1, 34, 'Y'),
('user', 0, 1, 35, 'Y'),
('user', 0, 1, 36, 'Y'),
('user', 0, 1, 37, 'Y'),
('user', 0, 1, 38, 'Y'),
('user', 0, 1, 39, 'Y'),
('user', 0, 1, 40, 'Y'),
('user', 0, 1, 41, 'Y'),
('user', 0, 1, 42, 'Y'),
('group', 0, 0, 3, 'Y');

-- --------------------------------------------------------

--
-- Table structure for table `pragyanV2_userprofile_elementdata`
--

CREATE TABLE IF NOT EXISTS `pragyanV2_userprofile_elementdata` (
  `user_id` int(11) NOT NULL default '0',
  `profile_elementid` int(11) NOT NULL default '0',
  `profile_elementdata` text NOT NULL,
  PRIMARY KEY  (`user_id`,`profile_elementid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pragyanV2_userprofile_elementdata`
--


-- --------------------------------------------------------

--
-- Table structure for table `pragyanV2_userprofile_elementdesc`
--

CREATE TABLE IF NOT EXISTS `pragyanV2_userprofile_elementdesc` (
  `profile_elementid` int(11) NOT NULL default '0',
  `profile_elementname` varchar(100) NOT NULL,
  `profile_elementdisplaytext` varchar(500) NOT NULL COMMENT 'Description of data held',
  `profile_elementtype` enum('text','textarea','radio','checkbox','select','file') NOT NULL default 'text',
  `profile_elementsize` int(11) default NULL,
  `profile_elementtypeoptions` text,
  `profile_elementdefaultvalue` varchar(400) default NULL,
  `profile_elementmorethan` varchar(400) default NULL,
  `profile_elementlessthan` varchar(400) default NULL,
  `profile_elementcheckint` tinyint(1) NOT NULL default '0' COMMENT 'Check if it is int if 1',
  `profile_elementtooltiptext` text NOT NULL,
  `profile_elementisrequired` tinyint(1) NOT NULL default '0',
  `profile_elementrank` int(11) NOT NULL default '0',
  PRIMARY KEY  (`profile_elementid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pragyanV2_userprofile_elementdesc`
--


-- --------------------------------------------------------

--
-- Table structure for table `pragyanV2_users`
--

CREATE TABLE IF NOT EXISTS `pragyanV2_users` (
  `user_id` int(11) NOT NULL auto_increment COMMENT 'user identification number',
  `user_name` varchar(100) NOT NULL COMMENT 'user''s good name',
  `user_email` varchar(100) NOT NULL,
  `user_fullname` varchar(100) NOT NULL COMMENT 'User''s full name',
  `user_password` varchar(32) NOT NULL,
  `user_regdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `user_lastlogin` datetime NOT NULL,
  `user_activated` tinyint(1) NOT NULL default '0' COMMENT 'Used for email verification',
  PRIMARY KEY  (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4643 ;

--
-- Dumping data for table `pragyanV2_users`
--

INSERT INTO `pragyanV2_users` (`user_id`, `user_name`, `user_email`, `user_fullname`, `user_password`, `user_regdate`, `user_lastlogin`, `user_activated`) VALUES
(1, 'admin', 'admin@pragyan.sf.net', 'admin', '21232f297a57a5a743894a0e4a801fc3', '2008-03-15 19:38:53', '2008-03-15 23:37:42', 1);

-- --------------------------------------------------------

--
-- Table structure for table `pragyanV2_validrollnumbers`
--

CREATE TABLE IF NOT EXISTS `pragyanV2_validrollnumbers` (
  `rollnumber` varchar(12) NOT NULL,
  `name` varchar(256) NOT NULL,
  UNIQUE KEY `rollnumber` (`rollnumber`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pragyanV2_validrollnumbers`
--


-- --------------------------------------------------------

--
-- Table structure for table `qaos_designations`
--

CREATE TABLE IF NOT EXISTS `qaos_designations` (
  `page_modulecomponentid` int(11) NOT NULL,
  `qaos_designation_id` int(11) NOT NULL,
  `qaos_designation_name` varchar(50) NOT NULL,
  `qaos_designation_description` text,
  `qaos_designation_priority` mediumint(9) NOT NULL default '0' COMMENT 'tells the priority of the designaiton, by default it is 0, for chairman =5, core members =4, managers = 3, coordinators = 2 and volunteers =1',
  UNIQUE KEY `qaos_designation_id` (`qaos_designation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `qaos_designations`
--


-- --------------------------------------------------------

--
-- Table structure for table `qaos_scoring`
--

CREATE TABLE IF NOT EXISTS `qaos_scoring` (
  `page_modulecomponentid` int(11) NOT NULL,
  `user_id` int(11) NOT NULL default '0',
  `targetuser_id` int(11) NOT NULL default '0',
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

--
-- Dumping data for table `qaos_scoring`
--


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

--
-- Dumping data for table `qaos_teams`
--


-- --------------------------------------------------------

--
-- Table structure for table `qaos_tree`
--

CREATE TABLE IF NOT EXISTS `qaos_tree` (
  `page_modulecomponentid` int(11) NOT NULL,
  `qaos_unit_id` int(11) NOT NULL,
  `qaos_parentunit_id` int(11) NOT NULL,
  PRIMARY KEY  (`qaos_unit_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `qaos_tree`
--


-- --------------------------------------------------------

--
-- Table structure for table `qaos_units`
--

CREATE TABLE IF NOT EXISTS `qaos_units` (
  `page_modulecomponentid` int(11) NOT NULL,
  `qaos_unit_id` int(11) NOT NULL,
  `qaos_team_id` int(11) NOT NULL,
  `qaos_designation_id` int(11) NOT NULL,
  `score_team` tinyint(1) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `qaos_units`
--


-- --------------------------------------------------------

--
-- Table structure for table `qaos_users`
--

CREATE TABLE IF NOT EXISTS `qaos_users` (
  `page_modulecomponentid` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `qaos_unit_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `qaos_users`
--


-- --------------------------------------------------------

--
-- Table structure for table `qaos_version`
--

CREATE TABLE IF NOT EXISTS `qaos_version` (
  `page_modulecomponentid` int(11) NOT NULL,
  `qaos_version` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `qaos_version`
--


-- --------------------------------------------------------

--
-- Table structure for table `quiz_descriptions`
--

CREATE TABLE IF NOT EXISTS `quiz_descriptions` (
  `page_modulecomponentid` int(11) NOT NULL auto_increment COMMENT 'Quiz Id',
  `quiz_title` varchar(256) NOT NULL default 'New Quiz' COMMENT 'Quiz Title',
  `quiz_headertext` varchar(1024) NOT NULL default 'This is a new quiz, which apparently hasn''t been edited yet.' COMMENT 'Quiz Header Text',
  `quiz_submittext` varchar(1024) NOT NULL default 'Thank you for taking this quiz.' COMMENT 'Quiz Submit Text',
  `quiz_quiztype` enum('simple','gre') NOT NULL default 'simple' COMMENT 'Type of the quiz. Determines what functions are used for generating questions, submitting answers, etc.',
  `quiz_testduration` time default NULL COMMENT 'Duration of a Test',
  `quiz_questionspertest` int(11) NOT NULL default '20' COMMENT 'Number of questions per test',
  `quiz_questionsperpage` int(11) default NULL COMMENT 'Number of questions to show per page',
  `quiz_objectivecount` int(11) default NULL COMMENT 'Number of objective questions in the test. Number of subjective = total - this',
  `quiz_questiongrouping` enum('shuffle','objectivefirst','subjectivefirst') NOT NULL default 'shuffle' COMMENT 'How to group questions',
  `quiz_startdatetime` datetime default NULL COMMENT 'When the quiz opens',
  `quiz_enddatetime` datetime default NULL COMMENT 'When the quiz closes',
  `quiz_showtesttimer` tinyint(1) NOT NULL default '0' COMMENT 'Whether to show the time taken for the whole test',
  `quiz_showpagetimer` tinyint(1) NOT NULL default '0' COMMENT 'Whether to show the time taken by the user for the page he/she is on',
  `quiz_startweight` int(11) NOT NULL default '1',
  PRIMARY KEY  (`page_modulecomponentid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=21 ;

--
-- Dumping data for table `quiz_descriptions`
--


-- --------------------------------------------------------

--
-- Table structure for table `quiz_objectiveoptions`
--

CREATE TABLE IF NOT EXISTS `quiz_objectiveoptions` (
  `page_modulecomponentid` int(11) NOT NULL COMMENT 'Quiz Id',
  `quiz_questionid` int(11) NOT NULL COMMENT 'Question Id',
  `quiz_questionoptionid` int(11) NOT NULL COMMENT 'Option Id',
  `quiz_questionoption` text NOT NULL COMMENT 'Option Text',
  `quiz_questionoptionrank` int(11) NOT NULL COMMENT 'Option Rank',
  PRIMARY KEY  (`page_modulecomponentid`,`quiz_questionid`,`quiz_questionoptionid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `quiz_objectiveoptions`
--


-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE IF NOT EXISTS `quiz_questions` (
  `page_modulecomponentid` int(11) NOT NULL COMMENT 'Quiz Id',
  `quiz_questionid` int(11) NOT NULL COMMENT 'Question Id',
  `quiz_questiontitle` varchar(256) NOT NULL COMMENT 'Question Title',
  `quiz_question` text NOT NULL COMMENT 'Question Text',
  `quiz_questiontype` enum('subjective','singleselectobjective','multiselectobjective') NOT NULL default 'subjective' COMMENT 'Question Type',
  `quiz_questionweight` int(11) NOT NULL default '1' COMMENT 'Question Weight',
  `quiz_answermaxlength` int(11) default NULL COMMENT 'Answer''s Maximum Length',
  `quiz_rightanswer` text COMMENT 'Correct answer, used in scoring in case of objective, and given as a hint to the person correcting the paper in case of subjective',
  PRIMARY KEY  (`page_modulecomponentid`,`quiz_questionid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `quiz_questions`
--


-- --------------------------------------------------------

--
-- Table structure for table `quiz_quizattemptdata`
--

CREATE TABLE IF NOT EXISTS `quiz_quizattemptdata` (
  `page_modulecomponentid` int(11) NOT NULL COMMENT 'Quiz Id',
  `user_id` int(11) NOT NULL COMMENT 'User Id',
  `quiz_starttime` datetime NOT NULL COMMENT 'Time when the user started attempting the test',
  `quiz_submittime` datetime default NULL COMMENT 'Time when the user submitted the entire test',
  `quiz_marksallotted` decimal(4,2) default NULL COMMENT 'Marks secured by the user',
  PRIMARY KEY  (`page_modulecomponentid`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `quiz_quizattemptdata`
--


-- --------------------------------------------------------

--
-- Table structure for table `quiz_submittedanswers`
--

CREATE TABLE IF NOT EXISTS `quiz_submittedanswers` (
  `page_modulecomponentid` int(11) NOT NULL COMMENT 'Quiz Id',
  `quiz_questionid` int(11) NOT NULL COMMENT 'Question Id',
  `user_id` int(11) NOT NULL COMMENT 'User Id',
  `quiz_submittedanswer` text COMMENT 'Answer submitted by the user',
  `quiz_questionviewtime` datetime default NULL COMMENT 'Time when the question was shown to the user',
  `quiz_answersubmittime` datetime default NULL COMMENT 'Time when the user submitted the answer',
  `quiz_markssecured` float(3,2) default NULL,
  PRIMARY KEY  (`page_modulecomponentid`,`quiz_questionid`,`user_id`),
  KEY `INDEX` (`page_modulecomponentid`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `quiz_submittedanswers`
--


-- --------------------------------------------------------

--
-- Table structure for table `quiz_weightmarks`
--

CREATE TABLE IF NOT EXISTS `quiz_weightmarks` (
  `page_modulecomponentid` int(11) NOT NULL COMMENT 'Quiz Id',
  `quiz_questionweight` int(11) NOT NULL COMMENT 'Question Weight',
  `quiz_weightpositivemarks` decimal(3,2) NOT NULL COMMENT 'Marks to be granted if a question of this weight is correctly answered',
  `quiz_weightnegativemarks` decimal(3,2) NOT NULL COMMENT 'Marks to be deducted if a question of this weight is incorrectly answered',
  PRIMARY KEY  (`page_modulecomponentid`,`quiz_questionweight`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `quiz_weightmarks`
--

