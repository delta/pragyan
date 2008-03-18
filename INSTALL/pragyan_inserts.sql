-- phpMyAdmin SQL Dump
-- version 2.11.0
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 18, 2008 at 11:44 PM
-- Server version: 5.0.45
-- PHP Version: 5.2.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `pragyan`
--

--
-- Dumping data for table `article_content`
--

INSERT INTO `article_content` (`page_modulecomponentid`, `article_content`, `article_lastupdated`) VALUES
(1, '<h1>Welcome to Pragyan CMS!</h1>\r\n<p>&nbsp;</p>\r\n<p>The permissions are visible near the top of this page. You can edit the content of this page, add or remove permissions to other users, create, copy, delete pages, forms,quizzes,etc. or change other settings, change your preferences, and do some more! A page is of type aricle.</p>', '2008-03-15 23:49:16'),
(2, 'Coming up Soon!!!', '0000-00-00 00:00:00');

--
-- Dumping data for table `article_contentbak`
--

INSERT INTO `article_contentbak` (`page_modulecomponentid`, `article_revision`, `article_diff`, `article_updatetime`, `user_id`) VALUES
(1, 1, 'ds', '2008-03-15 23:48:11', 1),
(1, 2, '<h1>Welcome to Pragyan CMS!</h1>\r\n<p>&nbsp;</p>\r\n<p>The permissions are visible near the top of this page. You can edit the content of this page, add or remove permissions to other users, create, copy, delete or change other settings, change your preferences, and do some more!</p>', '2008-03-15 23:49:16', 1);

--
-- Dumping data for table `billing_article`
--


--
-- Dumping data for table `billing_transactiondetails`
--


--
-- Dumping data for table `billing_transactions`
--


--
-- Dumping data for table `food_transactions`
--


--
-- Dumping data for table `form_desc`
--


--
-- Dumping data for table `form_elementdata`
--


--
-- Dumping data for table `form_elementdesc`
--


--
-- Dumping data for table `form_regdata`
--


--
-- Dumping data for table `forum_module`
--


--
-- Dumping data for table `forum_posts`
--


--
-- Dumping data for table `forum_threads`
--


--
-- Dumping data for table `gallery_name`
--


--
-- Dumping data for table `gallery_pics`
--


--
-- Dumping data for table `hospi_accomodation_status`
--


--
-- Dumping data for table `hospi_hostel`
--


--
-- Dumping data for table `newsletter`
--


--
-- Dumping data for table `newsletter_bc`
--


--
-- Dumping data for table `news_data`
--


--
-- Dumping data for table `news_desc`
--


--
-- Dumping data for table `poll_answers`
--


--
-- Dumping data for table `poll_questions`
--


--
-- Dumping data for table `pragyanV2_external`
--


--
-- Dumping data for table `pragyanV2_groups`
--


--
-- Dumping data for table `pragyanV2_inheritedinfo`
--


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

--
-- Dumping data for table `pragyanV2_pages`
--

INSERT INTO `pragyanV2_pages` (`page_id`, `page_name`, `page_parentid`, `page_createdtime`, `page_lastaccesstime`, `page_title`, `page_module`, `page_modulecomponentid`, `page_menurank`, `page_inheritedinfoid`, `page_displayinmenu`, `page_displaymenu`, `page_displaysiblingmenu`) VALUES
(0, '', 0, '2008-03-15 19:43:07', '2008-03-15 23:52:52', 'home', 'article', 1, 0, -1, 1, 1, 1),
(1, 'page1', 0, '2008-03-15 23:32:21', '2008-03-15 23:49:28', 'page1', 'article', 2, 1, -1, 1, 1, 1);

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

--
-- Dumping data for table `pragyanV2_uploads`
--


--
-- Dumping data for table `pragyanV2_usergroup`
--


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

--
-- Dumping data for table `pragyanV2_userprofile_elementdata`
--


--
-- Dumping data for table `pragyanV2_userprofile_elementdesc`
--


--
-- Dumping data for table `pragyanV2_users`
--

INSERT INTO `pragyanV2_users` (`user_id`, `user_name`, `user_email`, `user_fullname`, `user_password`, `user_regdate`, `user_lastlogin`, `user_activated`) VALUES
(1, 'admin', 'admin@pragyan.sf.net', 'admin', '21232f297a57a5a743894a0e4a801fc3', '2008-03-15 19:38:53', '2008-03-15 23:37:42', 1);

--
-- Dumping data for table `pragyanV2_validrollnumbers`
--


--
-- Dumping data for table `qaos_designations`
--


--
-- Dumping data for table `qaos_scoring`
--


--
-- Dumping data for table `qaos_teams`
--


--
-- Dumping data for table `qaos_tree`
--


--
-- Dumping data for table `qaos_units`
--


--
-- Dumping data for table `qaos_users`
--


--
-- Dumping data for table `qaos_version`
--


--
-- Dumping data for table `quiz_descriptions`
--


--
-- Dumping data for table `quiz_objectiveoptions`
--


--
-- Dumping data for table `quiz_questions`
--


--
-- Dumping data for table `quiz_quizattemptdata`
--


--
-- Dumping data for table `quiz_submittedanswers`
--


--
-- Dumping data for table `quiz_weightmarks`
--

