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

INSERT IGNORE INTO `article_content` (`page_modulecomponentid`, `article_content`, `article_lastupdated`, `allowComments`) VALUES
(1, '<h1>\r\n	Welcome to Pragyan CMS v3.0 (Beta Release)!</h1>\r\n<p>\r\n	<strong>Note that this is the Beta release i.e. a testing version and is not a stable release. More features and improvements are being added everyday while the existing bugs are being eliminated.<br />\r\n	</strong></p>\r\n<p>\r\n	Pragyan CMS home page : <a href="http://pragyan.sourceforge.net">http://pragyan.sourceforge.net</a></p>\r\n<p>\r\n	For more details, read the README file in the root directory.</p>\r\n<p>\r\n	For license and copyright information, view the LICENSE file inside the docs folder in the root directory.</p>\r\n<p>\r\n	Copyright &copy; 2010 Pragyan CMS Team.</p>\r\n', '2010-09-12 03:55:16', 0),
(4, '<h2>\r\n	How to use Pragyan CMS</h2>\r\n<p>\r\n	The operations are visible near the top of this page once you login as administrator.</p>\r\n<p>\r\n	<strong>Edit</strong> : You can edit the contents of the page, upload files to this page and see and even roll-back to previous revisions of this page.</p>\r\n<p>\r\n	<strong>Admin</strong> :<strong> Global Settings</strong> : Change the settings like Website Name, Email, Upload Limit, Default Template ,etc.</p>\r\n<p>\r\n	<strong>Admin</strong> : <strong>User Management</strong> : Manage users registered to the website, activate or deactivate them, or even edit their profiles and create new users.</p>\r\n<p>\r\n	<strong>Admin</strong> : <strong>Template Management</strong> : Install and Uninstall templates.</p>\r\n<p>\r\n	<strong>Admin : User Profile </strong>: Edit the user profile form and add extra information which every user must fill up to complete their profile page.</p>\r\n<p>\r\n	<strong>Admin : Email Registrants </strong>: Send mass emails to users or groups of registered users. You can also save and load email templates.</p>\r\n<p>\r\n	<strong>Permissions</strong> : Grant or remove permissions to users and groups, create groups and organize them.</p>\r\n<p>\r\n	<strong>Page Settings</strong> : Change the settings which are specific to this page like the page-specific template, create new child pages and copy or move or delete pages.</p>\r\n<p>\r\n	<strong>PDF </strong>: Convert this page into PDF and download it directly.</p>\r\n<p>\r\n	<strong>Profile </strong>: Change your user profile information like passwords and names.</p>\r\n<p>\r\n	Some points to note :&nbsp;</p>\r\n<ul>\r\n	<li>\r\n		A normal content page is of type article.</li>\r\n	<li>\r\n		You can create child-pages of a page from its &quot;Page Settings&quot;</li>\r\n	<li>\r\n		For page-specific templates to work, the &quot;Allow Page Specific Templates&quot; in the Global Settings under &quot;Admin&quot; must be checked.</li>\r\n	<li>\r\n		After installation, goto &quot;Admin&quot; and click on reindex the site for Sphider.</li>\r\n</ul>\r\n', '2010-09-12 03:48:00', 0),
(2, '<h2>\r\n	Team behind Pragyan CMS</h2>\r\n<p>\r\n	Pragyan CMS is created by engineering students of <a href="http://www.nitt.edu">National Institute of Technology Trichy (NIT-T)</a> in Tamil Nadu, India.&nbsp; The CMS is contributed to by a large number of students who are also members of the Central Webteam of NIT-T, also known as the &quot;Delta Force&quot;.</p>\r\n<p>\r\n	Pragyan CMS v2.6 was released officially by Sahil Ahuja of 2004 batch. The latest Pragyan CMS v3.0 is released officially by <span style="text-decoration: none;"><a href="http://abhishekdelta.wordpress.com" style="text-decoration: none;">Abhishek Shrivastava (abhishekdelta)</a> of 2007 batch.</span> Below is the list of all the co-developers and contributors since version 2.</p>\r\n<p>\r\n	Version 3 credits to :</p>\r\n<ul>\r\n	<li>\r\n		Abhishek Shrivastava [jereme]</li>\r\n	<li>\r\n		Chakradar Raju [chakradarraju]</li>\r\n	<li>\r\n		Balanivash [balanivash]</li>\r\n<li>\r\n		Boopathi [scriptle]</li>\r\n</ul>\r\n<p>\r\n	Version 3 contributors list (tickets, patches and bug-fixes):</p>\r\n<ul>\r\n	<li>\r\n		Mohnish Prasanna</li>\r\n	<li>\r\n		Shiva Nandan</li>\r\n</ul>\r\n<p>\r\n	Version 2 credits to :</p>\r\n<ul>\r\n	<li>\r\n		Abhilash R [abhithekid]</li>\r\n	<li>\r\n		Anshu Prateek [analyst]</li>\r\n	<li>\r\n		Ankit Srivastava</li>\r\n	<li>\r\n		Bharath Venkatesh [bhattu]</li>\r\n	<li>\r\n		Sahil Ahuja</li>\r\n	<li>\r\n		Jithin K.R [jithinkr]</li>\r\n	<li>\r\n		Shankarram [kulz]</li>\r\n	<li>\r\n		Mrinal Kumar</li>\r\n	<li>\r\n		Harini A</li>\r\n	<li>\r\n		Abhishek Verma</li>\r\n</ul>\r\n<p>\r\n	And also to the following people who have contributed minor changes,<br />\r\n	enhancements, bugfixes or support for a new language since version 2.1.0:<br />\r\n	M. Surya Sankar, Sapna Shukla, Shruti J, Ashwathi Krishnan, K.R Arvind [kra],<br />\r\n	T.V. Karthik</p>\r\n<p>\r\n	For more information about the Pragyan Team, read the CREDITS file inside the root directory.</p>\r\n<p>\r\n	Feel free to contact at <a href="mailto:i.abhi27@gmail.com"><span style="text-decoration: underline;">i.abhi27 [at] gmail [dot] com<br />\r\n	</span></a></p>\r\n', '2010-09-12 03:50:37', 0),
(3, '<h2>\r\n	Features</h2>\r\n<p>\r\n	This version is an improvement over the previous version 2.6. Some of the new features of Pragyan v3.0 :</p>\r\n<ul>\r\n	<li>\r\n		Lots of security checks to protect against SQL Injection and XSS attacks.</li>\r\n	<li>\r\n		New and improved CKEditor 3.1 for better editing of articles</li>\r\n	<li>\r\n		Template Installation Module with compatibility</li>\r\n	<li>\r\n		PDF Plugin to easily convert pages into PDF</li>\r\n	<li>\r\n		Page-specific templates with child propagation</li>\r\n	<li>\r\n		Improved Sphider powered Search Engine</li>\r\n	<li>\r\n		Send mass emails to site registrants</li>\r\n	<li>\r\n		Allow comments by users in article type pages</li>\r\n	<li>\r\n		AJAX-based interface for Permissions management</li>\r\n	<li>\r\n		Google Maps and Latex Plugin for ckEditor in article type pages</li>\r\n	<li>\r\n		Profile images and customizable information for every registered user</li>\r\n	<li>\r\n		Multi-depth menu style with customizable depth</li>\r\n	<li>\r\n		Change minute site details like keywords and descriptions for SEO</li>\r\n	<li>\r\n		Completely re-written user management with improved features</li>\r\n	<li>\r\n		Improved Gallery, Forum, Form and Quiz modules with added features</li>\r\n	<li>\r\n		Change the website template, title and email directly from the web-interface</li>\r\n	<li>\r\n		Change global settings like Site Template directly from the &#39;Admin&#39; page.</li>\r\n	<li>\r\n		More secure with lots of bugs removed, code optimizations and secure queries to the database.</li>\r\n</ul>\r\n<p>\r\n	For more details, view the ChangeLog file in the CMS root folder.</p>\r\n', '2010-09-12 03:52:13', 0);



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
-- Dumping data for table `pragyanV3_modules`
--

INSERT IGNORE INTO `pragyanV3_modules` (`module_name`, `module_tables`) VALUES
('article', 'article_comments;article_content;article_contentbak'),
('book', 'book_desc'),
('contest', ''),
('form', 'form_desc;form_elementdata;form_elementdesc;form_regdata'),
('forum', 'forum_like;forum_module;forum_posts;forum_threads;forum_visits'),
('gallery', 'gallery_name;gallery_pics'),
('hospi', 'hospi_accomodation_status;hospi_hostel'),
('news', 'news_data;news_desc'),
('newsletter', 'newsletter;newsletter_bc'),
('pagelist', 'list_images;list_prop'),
('poll', 'poll_answers;poll_questions'),
('pr', ''),
('qaos', 'qaos_designations;qaos_scoring;qaos_teams;qaos_tree;qaos_units;qaos_users;qaos_version'),
('scrolltext', ''),
('sitemap', ''),
('quiz', 'quiz_answersubmissions;quiz_descriptions;quiz_objectiveoptions;quiz_questions;quiz_sections;quiz_userattempts;quiz_weightmarks'),
('sqlquery', 'sqlquery_desc');

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
('reindex_frequency', '7'),
('censor_words','');

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

INSERT IGNORE INTO `pragyanV3_pages` (`page_id`, `page_name`, `page_parentid`, `page_createdtime`, `page_lastaccesstime`, `page_title`, `page_module`, `page_modulecomponentid`, `page_template`, `page_menurank`, `page_inheritedinfoid`, `page_displayinmenu`, `page_displaymenu`, `page_displaysiblingmenu`, `page_displaypageheading`, `page_menutype`, `page_menudepth`) VALUES
(0, 'home', 0, '2010-05-09 16:34:17', '2010-09-12 04:00:13', 'Home', 'article', 1, 'crystalx', 0, -1, 1, 1, 1, 0, 'classic', 1),
(1, 'credits', 0, '2010-05-09 16:34:17', '2010-09-12 04:00:12', 'Pragyan Team', 'article', 2, 'crystalx', 3, -1, 1, 1, 1, 1, 'classic', 1),
(2, 'features', 0, '2010-09-12 03:59:03', '2010-09-12 04:00:11', 'Features', 'article', 3, 'crystalx', 2, -1, 1, 1, 1, 1, 'classic', 1),
(3, 'how_to_use', 0, '2010-09-12 03:59:16', '2010-09-12 04:00:11', 'How to use', 'article', 4, 'crystalx', 1, -1, 1, 1, 1, 1, 'classic', 1);

--
-- Dumping data for table `pragyanV3_permissionlist`
--

INSERT IGNORE INTO `pragyanV3_permissionlist` (`page_module`, `perm_action`, `perm_text`, `perm_rank`, `perm_description`) VALUES
('page', 'admin', 'Admin', 0, ''),
('page', 'grant', 'Permissions', 0, 'Grant Permissions'),
('page', 'settings', 'Page Settings', 0, 'Page Settings'),
('page', 'widgets', 'Widgets', 0, 'Add or configure widgets'),
('page', 'pdf', 'PDF', 0, 'Convert into PDF'),
('article', 'create', 'Create', 8, 'Create an aritcle'),
('article', 'view', 'View', 0, 'View the article'),
('article', 'edit', 'Edit', 0, 'Edit the article'),
('book', 'create', 'Create',  5, 'Create a book'),
('book', 'edit', 'Edit',  0, 'Edit the book page and properties'),
('book', 'view', 'View',  0, 'View the book'),
('form', 'create', 'Create Form', 19, 'Create a new Form'),
('form', 'view', 'Register', 0, 'Register to a form'),
('form', 'viewregistrants', 'View Registrants', 0, 'View Registrants'),
('form', 'editregistrants', 'Edit Registrants', 0, 'Edit Registrants'' info'),
('form', 'editform', 'Edit Form', 0, 'Edit the structure of the form'),
('forum', 'create', 'Create', 0, 'Create a forum'),
('forum', 'view', 'View', 0, 'View the posts'),
('forum', 'moderate', 'Moderate', 0, 'Moderate the forums'),
('forum', 'post', 'New Topic', 0, 'Create new topic in forums'),
('forum', 'forumsettings', 'Forum Settings',0,'Change forum settings'),
('gallery', 'create', 'Create Gallery', 18, 'Create a new Gallery'),
('gallery', 'view', 'View Gallery', 0, 'View the Gallery'),
('gallery', 'edit', 'Edit', 0, 'Edit the Gallery'),
('hospi', 'create', 'Create', 1, 'Create the hospi module'),
('hospi', 'view', 'View', 1, 'View the hospi module'),
('hospi', 'accomodate', 'Accomodate', 1, 'Accomodate into hostel'),
('hospi', 'addroom', 'Add Room', 1, 'Add Room to a hostel'),
('news', 'create', 'Create News', 20, 'Create a new News'),
('news', 'view', 'View', 0, 'VIew'),
('news', 'rssview', 'RSS View', 0, 'Retrieve News as RSS'),
('news', 'edit', 'Edit News', 0, 'Edit the news item'),
('pagelist', 'create', 'Create', 0, 'Create a Pagelist'),
('pagelist', 'view', 'View', 0, 'View the pagelist'),
('pagelist', 'edit', 'Edit', 0, 'Edit the pagelist'),
('poll', 'create', 'Create Poll', 0, 'Create a Poll'),
('poll', 'cast', 'Cast', 0, 'Cast your vote'),
('poll', 'manage', 'Manage', 0, 'Manage the poll'),
('poll', 'viewstats', 'Stats', 0, 'View poll statistics'),
('quiz', 'create', 'Create', 0, 'Create a New Quiz'),
('quiz', 'view', 'View', 0, 'Take the quiz'),
('quiz', 'edit', 'Edit', 2, 'Edit the Quiz'),
('quiz', 'correct', 'Correct', 3, 'Correct the Quiz Attempts'),
('sitemap', 'create', 'Create', 0, 'Create a sitemap'),
('sitemap', 'view', 'View', 0, 'View a sitemap'),
('sqlquery', 'view', 'View', 1, 'View'),
('sqlquery', 'edit', 'Edit', 2, 'Edit'),
('sqlquery', 'create', 'Create', 0, 'Create'),
('safedit', 'view', 'View', 0, 'View'),
('safedit', 'edit', 'Edit', 1, 'Edit'),
('safedit', 'create', 'Create', 2, 'Create');
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
-- Non-admin users by default have permissions to View Article, book, gallery, news, sitemap and convert page into PDF.

INSERT IGNORE INTO `pragyanV3_userpageperm` (`perm_type`, `page_id`, `usergroup_id`, `perm_id`, `perm_permission`) SELECT 
'group', 0, 0, `perm_id`, 'Y' FROM `pragyanV3_permissionlist` WHERE `perm_action` IN ('view','pdf') AND `page_module` IN ('page','article','book','gallery','news','sitemap');
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
-- Dumping data for table `pragyanV3_widgetsinfo`
--

INSERT IGNORE INTO `pragyanV3_widgetsinfo` (`widget_id`, `widget_name`, `widget_classname`, `widget_description`, `widget_version`, `widget_author`, `widget_foldername`) VALUES
(1, 'Server Date and Time', 'serverDateTime', 'Display the current date and time in the website', '0.01', 'Abhishek Shrivastava', 'server_date_time');


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

