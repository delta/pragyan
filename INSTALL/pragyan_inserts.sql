-- phpMyAdmin SQL Dump
-- version 3.2.2.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 23, 2010 at 01:10 AM
-- Server version: 5.1.37
-- PHP Version: 5.2.10-2ubuntu6.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `pragyan`
--

--
-- Dumping data for table `article_content`
--

INSERT IGNORE INTO `article_content` (`page_modulecomponentid`, `article_content`, `article_lastupdated`) VALUES
(1, '<h1>\r\n	Welcome to Pragyan CMS v3.0 (Alpha Release)!</h1>\r\n<p>\r\n	<strong>Note that this is the Alpha release i.e. a developmental version and is not a stable release. More features and improvements are being added everyday.</strong></p>\r\n<p>\r\n	This version is an improvement over the previous version 2.6. Some of the new features of Pragyan v3.0 :</p>\r\n<ul>\r\n	<li>\r\n		New and improved CKEditor 3.1 for better editing of articles</li>\r\n 	<li>\r\n		Template Installation Module</li>\r\n	<li>\r\n		Every page can have its own template</li>\r\n	<li>\r\n		Propagate page settings to all child pages</li>\r\n	<li>\r\n		Set your own administrator account during Installation</li>\r\n	<li>\r\n		Completely re-written user management with user-friendly features</li>\r\n	<li>\r\n		Change the website template and title directly from the CMS itself</li>\r\n	<li>\r\n		Change global settings like Upload Limit, Activate user on registration from the &#39;Admin&#39; page.</li>\r\n	<li>\r\n		More secure with lots of bugs removed, code optimizations and secure queries to the database.</li>\r\n</ul>\r\n<p>\r\n	The operations are visible near the top of this page once you login as administrator.</p>\r\n<p>\r\n	<strong>Edit</strong> : You can edit the contents of the page, upload files to this page and see and even roll-back to previous revisions of this page.</p>\r\n<p>\r\n	<strong>Admin</strong> :<strong> Global Settings</strong> : Change the settings like Website Name, Email, Upload Limit, Default Template ,etc.</p>\r\n<p>\r\n	<strong>Admin</strong> : <strong>User Management</strong> : Manage users registered to the website, activate or deactivate them, or even edit their profiles and create new users.</p>\r\n<p>\r\n	<strong>Admin</strong> : <strong>Template Management</strong> : Install and Uninstall templates.</p>\r\n<p>\r\n	<strong>Permissions</strong> : Grant or remove permissions to users and groups, create groups and organize them.</p>\r\n<p>\r\n	<strong>Settings</strong> : Change the settings which are specific to this page like the page-specific template, create new child pages and copy or move or delete pages.</p>\r\n<p>\r\n	Some points to note :&nbsp;</p>\r\n<ul>\r\n	<li>\r\n		A page is of type article.</li>\r\n	<li>\r\n		For page-specific templates to work, the &quot;Allow Page Specific Templates&quot; in the Global Settings under &quot;Admin&quot; must be checked.</li>\r\n</ul>\r\n', '2010-05-23 21:01:17'),
(2, '<h2>\r\n	Team behind Pragyan CMS</h2>\r\n<p>\r\n	Pragyan CMS v3.0 released by <a href="http://abhishekdelta.wordpress.com" style="text-decoration: none;">Abhishek Shrivastava (abhishekdelta)</a><br />\r\n	Version 2 credits to :</p>\r\n<ul>\r\n	<li>\r\n		Abhilash R (abhithekid)</li>\r\n	<li>\r\n		Anshu Prateek (analyst)</li>\r\n	<li>\r\n		Ankit Srivastava</li>\r\n	<li>\r\n		Bharath (bhattu)</li>\r\n	<li>\r\n		Sahil Ahuja</li>\r\n	<li>\r\n		Jithin K.R (jithinkr)</li>\r\n	<li>\r\n		Shankarram (kulz)</li>\r\n	<li>\r\n		Mrinal Kumar</li>\r\n	<li>\r\n		Harini A</li>\r\n	<li>\r\n		Abhishek Verma</li>\r\n</ul>\r\n<p>\r\n	And also to the following people who have contributed minor changes,<br />\r\n	enhancements, bugfixes or support for a new language since version 2.1.0:<br />\r\n	M. Surya Sankar, Sapna Shukla, Shruti J, Ashwathi Krishnan, K.R Arvind,<br />\r\n	T.V. Karthik<br />\r\n	<br />\r\n	Feel free to contact at <a href="mailto:pragyancms@gmail.com">pragyancms@gmail.com</a></p>\r\n', '2010-05-23 20:53:48');

--
-- Dumping data for table `article_contentbak`
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
-- Dumping data for table `pragyanV3_external`
--


--
-- Dumping data for table `pragyanV3_global`
--

INSERT IGNORE INTO `pragyanV3_global` (`attribute`, `value`) VALUES
('cms_title', 'Pragyan CMS v3'),
('cms_desc', 'This website is powered by Pragyan CMS'),
('cms_keywords', 'Pragyan CMS v3.0, Sourceforge, Abhishek Shrivastava'),
('cms_email', 'no-reply@localhost'),
('allow_pagespecific_header', '0'),
('allow_pagespecific_template', '0'),
('default_template', 'crystalx'),
('upload_limit', '50000000'),
('default_user_activate', '1'),
('default_mail_verify', '0'),
('breadcrumb_submenu', '0'),
('reindex_frequency', '7');

--
-- Dumping data for table `pragyanV3_groups`
-- 

INSERT IGNORE INTO `pragyanV3_groups` (`group_id`, `group_name`, `group_description`, `group_priority`, `form_id`) VALUES
(2, 'admin', 'The Administrators', 100, 0);

--
-- Dumping data for table `pragyanV3_inheritedinfo`
--


--
-- Dumping data for table `pragyanV3_log`
--

INSERT IGNORE INTO `pragyanV3_log` (`log_no`, `log_datetime`, `user_email`, `user_id`, `page_path`, `page_id`, `perm_module`, `perm_action`, `user_accessipaddress`) VALUES
(1, NOW(), '', 0, '', 0, '', '', '');

--
-- Dumping data for table `pragyanV3_pages`
--

INSERT IGNORE INTO `pragyanV3_pages` (`page_id`, `page_name`, `page_parentid`, `page_createdtime`, `page_lastaccesstime`, `page_title`, `page_module`, `page_modulecomponentid`, `page_template`, `page_menurank`, `page_inheritedinfoid`, `page_displayinmenu`, `page_displaymenu`, `page_displaysiblingmenu`, `page_displaypageheading`) VALUES
(0, 'home', 0, '2010-05-09 16:34:17', '2010-05-23 21:02:11', 'Home', 'article', 1, 'crystalx', 0, -1, 1, 1, 1, 0),
(1, 'credits', 0, '2010-05-09 16:34:17', '2010-05-23 21:01:37', 'Pragyan Team', 'article', 2, 'crystalx', 1, -1, 1, 1, 1, 1);

--
-- Dumping data for table `pragyanV3_permissionlist`
--

INSERT IGNORE INTO `pragyanV3_permissionlist` (`page_module`, `perm_action`, `perm_text`, `perm_rank`, `perm_description`) VALUES
('page', 'admin', 'Admin', 0, ''),
('page', 'grant', 'Permissions', 0, 'Grant Permissions'),
('page', 'settings', 'Page Settings', 0, 'Page Settings'),
('page', 'pdf', 'PDF', 0, 'Convert into PDF'),
('article', 'create', 'Create', 8, 'Create an aritcle'),
('article', 'view', 'View', 0, 'View the article'),
('article', 'edit', 'Edit', 0, 'Edit the article'),
('form', 'create', 'Create Form', 19, 'Create a new Form'),
('form', 'view', 'Register', 0, 'Register to a form'),
('form', 'viewregistrants', 'View Registrants', 0, 'View Registrants'),
('form', 'editregistrants', 'Edit Registrants', 0, 'Edit Registrants'' info'),
('form', 'editform', 'Edit Form', 0, 'Edit the structure of the form'),
('forum', 'create', 'Create', 0, 'Create a forum'),
('forum', 'view', 'View', 0, 'View the posts'),
('forum', 'moderate', 'Moderate', 0, 'Moderate the forums'),
('forum', 'post', 'New Post', 0, 'Post in forums'),
('forum', 'forumsettings', 'Forum Settings',0,'Change forum settings'),
('gallery', 'create', 'Create Gallery', 18, 'Create a new Gallery'),
('gallery', 'view', 'View Gallery', 0, 'View the Gallery'),
('gallery', 'edit', 'Edit', 0, 'Edit the gallery'),
('hospi', 'create', 'Create', 1, 'Create the hospi module'),
('hospi', 'view', 'View', 1, 'View the hospi module'),
('hospi', 'accomodate', 'Accomodate', 1, 'Accomodate the hospi module'),
('hospi', 'addroom', 'Add Room', 1, 'Add Room to the hospi module'),
('news', 'create', 'Create News', 20, 'Create a new News'),
('news', 'view', 'View', 0, 'VIew'),
('news', 'rssview', 'RSS View', 0, 'Retrieve News as RSS'),
('news', 'edit', 'Edit News', 0, 'Edit the news item'),
('poll', 'create', 'Create Poll', 0, 'Create a Poll'),
('poll', 'view', 'Vote', 0, 'Vote for a poll'),
('pr', 'create', 'Create', 0, 'Create a PR module'),
('pr', 'view', 'View', 0, 'View a PR module'),
('quiz', 'create', 'Create', 0, 'Create a New Quiz'),
('quiz', 'view', 'View', 0, 'Take the quiz'),
('quiz', 'edit', 'Edit', 2, 'Edit the Quiz'),
('quiz', 'correct', 'Correct', 3, 'Correct the Quiz Attempts'),
('qaos', 'create', 'Create', 0, 'Create the QAOS module'),
('qaos', 'view', 'View', 0, 'View QAOS'),
('qaos', 'edit', 'Edit', 0, 'Edit QAOS'),
('qaos', 'qaosadmin', 'Qaos Admin', 1, 'Admin the QAOS'),
('qaos', 'score', 'Score', 2, 'Score the QAOS'),
('sitemap', 'create', 'Create', 0, 'Create a sitemap'),
('sitemap', 'view', 'View', 0, 'View a sitemap'),
('scrolltext', 'scrollview', 'Scrollview', 1, 'scrollview the scrolltext'),
('scrolltext', 'edit', 'Edit', 1, 'edit the scrolltext'),
('scrolltext', 'view', 'View', 1, 'view the scrolltext'),
('scrolltext', 'create', 'Create', 1, 'create the scrolltext'),
('sqlquery', 'view', 'View', 1, 'View'),
('sqlquery', 'edit', 'Edit', 2, 'Edit'),
('sqlquery', 'create', 'Create', 0, 'Create');
--
-- Dumping data for table `pragyanV3_templates`
--

INSERT IGNORE INTO `pragyanV3_templates` (`template_name`) VALUES
('crystalx');

--
-- Dumping data for table `pragyanV3_uploads`
--


--
-- Dumping data for table `pragyanV3_usergroup`
--

INSERT IGNORE INTO `pragyanV3_usergroup` (`user_id`, `group_id`) VALUES
(1, 2);

--
-- Dumping data for table `pragyanV3_userpageperm`
-- Non-admin users by default have permissions to View Article and convert page into PDF.

INSERT IGNORE INTO `pragyanV3_userpageperm` (`perm_type`, `page_id`, `usergroup_id`, `perm_id`, `perm_permission`) SELECT 
'group', 0, 0, `perm_id`, 'Y' FROM `pragyanV3_permissionlist` WHERE `perm_action` IN ('view','pdf') AND `page_module` IN ('page','article','gallery','news','sitemap');
INSERT IGNORE INTO `pragyanV3_userpageperm` (`perm_type`, `page_id`, `usergroup_id`, `perm_id`, `perm_permission`) SELECT 
'group', 0, 2, `perm_id`, 'Y' FROM `pragyanV3_permissionlist` WHERE 1;

--
-- Dumping data for table `pragyanV3_userprofile_elementdata`
--


--
-- Dumping data for table `pragyanV3_userprofile_elementdesc`
--


--
-- Dumping data for table `pragyanV3_users`
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


--
-- Dumping data for table `sqlquery_desc`
--

