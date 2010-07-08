-- phpMyAdmin SQL Dump
-- version 3.2.2.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 23, 2010 at 12:58 AM
-- Server version: 5.1.37
-- PHP Version: 5.2.10-2ubuntu6.4

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
  `article_lastupdated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `allowComments` tinyint(1) NOT NULL,
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
-- Table structure for table `article_comments`
--

CREATE TABLE IF NOT EXISTS `article_comments` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `page_modulecomponentid` int(11) NOT NULL,
  `user` varchar(200) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `comment` text NOT NULL,
  PRIMARY KEY (`comment_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `form_desc`
--

CREATE TABLE IF NOT EXISTS `form_desc` (
  `page_modulecomponentid` int(11) NOT NULL,
  `form_heading` varchar(200) NOT NULL,
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
  `form_elementname` varchar(100) NOT NULL,
  `form_elementdisplaytext` varchar(500) NOT NULL COMMENT 'Description of data held',
  `form_elementtype` enum('text','textarea','radio','checkbox','select','password','file','date','datetime') NOT NULL DEFAULT 'text',
  `form_elementsize` int(11) DEFAULT NULL,
  `form_elementtypeoptions` text,
  `form_elementdefaultvalue` varchar(400) DEFAULT NULL,
  `form_elementmorethan` varchar(400) DEFAULT NULL,
  `form_elementlessthan` varchar(400) DEFAULT NULL,
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
  `imagesPerPage` int(11) NOT NULL DEFAULT '6'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `gallery_pics`
--

CREATE TABLE IF NOT EXISTS `gallery_pics` (
  `upload_filename` varchar(200) NOT NULL,
  `page_modulecomponentid` int(11) NOT NULL,
  `gallery_filecomment` varchar(200) NOT NULL
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
  `hospi_projected_checkin` datetime NOT NULL,
  `hospi_actual_checkin` datetime NOT NULL,
  `hospi_projected_checkout` datetime NOT NULL,
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
  `hospi_room_no` int(11) NOT NULL DEFAULT '0'
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
  `news_title` varchar(150) NOT NULL,
  `news_description` varchar(1000) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `poll_answers`
--

CREATE TABLE IF NOT EXISTS `poll_answers` (
  `page_modulecomponentid` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `poll_answer` varchar(200) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
-- Table structure for table `pragyanV3_external`
--

CREATE TABLE IF NOT EXISTS `pragyanV3_external` (
  `page_modulecomponentid` int(11) NOT NULL,
  `page_extlink` varchar(500) NOT NULL,
  PRIMARY KEY  (`page_modulecomponentid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Used to store all external links';

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
-- Table structure for table `pragyanV3_pages`
--

CREATE TABLE IF NOT EXISTS `pragyanV3_pages` (
  `page_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Page identification number',
  `page_name` varchar(32) NOT NULL COMMENT 'Name of the page',
  `page_parentid` int(11) NOT NULL COMMENT 'ID of the parent of the page',
  `page_createdtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Time when the page was created',
  `page_lastaccesstime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Time when the page was last accessed',
  `page_title` varchar(128) NOT NULL DEFAULT 'New Page' COMMENT 'Title of the page',
  `page_module` enum('article','billing','external','form','forum','link','gallery','hospi','menu','news','poll','pr','qaos','quiz','scrolltext','sitemap','sqlquery','search') NOT NULL DEFAULT 'article' COMMENT 'Module type of the page',
  `page_modulecomponentid` int(11) NOT NULL COMMENT 'Component id used in the module',
  `page_template` varchar(50) NOT NULL,
  `page_menurank` int(11) NOT NULL COMMENT 'Rank for menu ordering',
  `page_inheritedinfoid` int(11) NOT NULL DEFAULT '-1' COMMENT 'Inherited info table mapping',
  `page_displayinmenu` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'To display in menu bar or not',
  `page_displaymenu` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Tells if menu should be displayed at all',
  `page_displaysiblingmenu` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Tells if sibling menu is displayed',
  `page_displaypageheading` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Determines whether page heading is displayed on the page',
  PRIMARY KEY (`page_id`),
  UNIQUE KEY `unique parent` (`page_parentid`,`page_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `pragyanV3_permissionlist`
--

CREATE TABLE IF NOT EXISTS `pragyanV3_permissionlist` (
  `perm_id` int(11) AUTO_INCREMENT NOT NULL,
  `page_module` enum('page','article','billing','form','forum','gallery','hospi','news','poll','pr','qaos','quiz','scrolltext','sitemap','sqlquery','search','newsletter','pdf') NOT NULL,
  `perm_action` varchar(100) NOT NULL,
  `perm_text` varchar(200) NOT NULL,
  `perm_rank` int(11) NOT NULL COMMENT 'The order of being shown in actionbar',
  `perm_description` varchar(200) NOT NULL,
  PRIMARY KEY (`perm_id`),
  UNIQUE KEY `permission type` (`page_module`,`perm_action`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='List of the available permissions';

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
-- Table structure for table `pragyanV3_uploads`
--

CREATE TABLE IF NOT EXISTS `pragyanV3_uploads` (
  `page_modulecomponentid` int(11) NOT NULL,
  `page_module` enum('article','quiz','form','gallery') NOT NULL,
  `upload_fileid` int(11) NOT NULL,
  `upload_filename` varchar(200) NOT NULL,
  `upload_filetype` varchar(300) NOT NULL,
  `upload_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` varchar(100) NOT NULL COMMENT 'The user who uploaded the file',
  PRIMARY KEY (`upload_fileid`),
  UNIQUE KEY `page_modulecomponentid` (`page_modulecomponentid`,`page_module`,`upload_filename`)
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
  `user_name` varchar(100) NOT NULL COMMENT 'user''s good name',
  `user_email` varchar(100) NOT NULL,
  `user_fullname` varchar(100) NOT NULL COMMENT 'User''s full name',
  `user_password` varchar(32) NOT NULL,
  `user_regdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_lastlogin` datetime NOT NULL,
  `user_activated` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Used for email verification',
  `user_loginmethod` enum('db','ldap','imap','ads') NOT NULL DEFAULT 'db' COMMENT 'Login Method',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

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
  `quiz_questionviewtime` datetime NOT NULL COMMENT 'When the user saw this question for the first time',
  `quiz_answersubmittime` datetime NOT NULL COMMENT 'When the user submitted his answer',
  `quiz_marksallotted` decimal(5,2) default NULL COMMENT 'Marks allotted for the given question',
  UNIQUE KEY `page_modulecomponentid` (`page_modulecomponentid`,`quiz_sectionid`,`quiz_questionid`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_descriptions`
--

CREATE TABLE IF NOT EXISTS `quiz_descriptions` (
  `page_modulecomponentid` int(11) NOT NULL auto_increment COMMENT 'Quiz ID',
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
  PRIMARY KEY  (`page_modulecomponentid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_objectiveoptions`
--

CREATE TABLE IF NOT EXISTS `quiz_objectiveoptions` (
  `page_modulecomponentid` int(11) NOT NULL COMMENT 'Quiz ID',
  `quiz_sectionid` int(11) NOT NULL COMMENT 'Section ID',
  `quiz_questionid` int(11) NOT NULL COMMENT 'Question ID',
  `quiz_optionid` int(11) NOT NULL auto_increment COMMENT 'Option ID',
  `quiz_optiontext` text NOT NULL COMMENT 'The option itself!',
  `quiz_optionrank` int(11) NOT NULL COMMENT 'A rank, according to which options will be ordered',
  PRIMARY KEY  (`page_modulecomponentid`,`quiz_sectionid`,`quiz_questionid`,`quiz_optionid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE IF NOT EXISTS `quiz_questions` (
  `page_modulecomponentid` int(11) NOT NULL COMMENT 'Quiz ID',
  `quiz_sectionid` int(11) NOT NULL COMMENT 'Section ID',
  `quiz_questionid` int(11) NOT NULL auto_increment COMMENT 'Question ID',
  `quiz_question` text NOT NULL COMMENT 'The question',
  `quiz_questiontype` enum('sso','mso','subjective') NOT NULL COMMENT 'Whether the question is single select objective, multiselect objective, or subjective',
  `quiz_questionrank` int(11) NOT NULL COMMENT 'A rank to determine the ordering of questions in a section',
  `quiz_questionweight` int(11) NOT NULL COMMENT 'Question difficulty level, based on which positive and negative marks may be alloted',
  `quiz_answermaxlength` int(11) NOT NULL COMMENT 'Maximum number of characters in the answer, in case it''s a subjective question',
  `quiz_rightanswer` text NOT NULL COMMENT 'The correct answer for the question. In case of sso, the option id, in case of mmo, a delimited set of options ids, and in case of subjective, a hint to the human correcting the quiz',
  PRIMARY KEY  (`page_modulecomponentid`,`quiz_sectionid`,`quiz_questionid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_sections`
--

CREATE TABLE IF NOT EXISTS `quiz_sections` (
  `page_modulecomponentid` int(11) NOT NULL COMMENT 'Quiz ID',
  `quiz_sectionid` int(11) NOT NULL auto_increment COMMENT 'Section ID',
  `quiz_sectiontitle` varchar(260) NOT NULL COMMENT 'Section Title',
  `quiz_sectionssocount` int(11) NOT NULL COMMENT 'Number of Single Select Objective questions to be taken from this section',
  `quiz_sectionmsocount` int(11) NOT NULL COMMENT 'Number of Multiselect Objective questions to be taken from this section',
  `quiz_sectionsubjectivecount` int(11) NOT NULL,
  `quiz_sectiontimelimit` time NOT NULL COMMENT 'Amount of time a user may spend on this section (taken from the time when he first opened this section)',
  `quiz_sectionquestionshuffled` tinyint(1) NOT NULL COMMENT 'Whether questions should be shuffled (1), or taken in the order given by question_rank (0)',
  `quiz_sectionrank` int(11) NOT NULL,
  PRIMARY KEY  (`page_modulecomponentid`,`quiz_sectionid`)
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
  `quiz_submissiontime` datetime default NULL COMMENT 'Time when the user submitted the quiz. If an entry exists here, with this field null, the user has started the quiz, but hasn''t completed it yet.',
  `quiz_marksallotted` decimal(5,2) default NULL COMMENT 'Total marks the person scored',
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
-- Table structure for table `sqlquery_desc`
--

CREATE TABLE IF NOT EXISTS `sqlquery_desc` (
  `page_modulecomponentid` int(11) NOT NULL COMMENT 'Module Component Id',
  `sqlquery_title` varchar(260) DEFAULT NULL COMMENT 'Title',
  `sqlquery_query` text NOT NULL COMMENT 'Query',
  UNIQUE KEY `page_modulecomponentid` (`page_modulecomponentid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
