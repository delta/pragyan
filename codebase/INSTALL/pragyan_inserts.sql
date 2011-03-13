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
(1, '<h1>\r\n	Welcome to Pragyan CMS v3 (Beta)!</h1>\r\n<p>\r\n	<strong>Note that this is the Beta release i.e. a testing version and is not a stable release. Although we&#39;ve tried our best pest-control, but still if you find any bugs crouching around, report it at :</strong></p>\r\n<p>\r\n	[tex]pragyan-support@integriti.org.in[/tex]</p>\r\n<p>\r\n	Also you can visit us at Pragyan CMS home page : <a href="http://sourceforge.net/projects/pragyan">http://sourceforge.net/projects/pragyan</a></p>\r\n<p>\r\n	Also you can visit the Pragyan CMS TRAC for development activity: <a href="http://sourceforge.net/apps/trac/pragyan">http://sourceforge.net/apps/trac/pragyan</a></p>\r\n<p>\r\n	For more details including license and credits, please read the README.html file in the docs folder.</p>\r\n<p>\r\n	And finally, a BIG thanks to you for using Pragyan CMS. Any kind of feedback will be greatly appreciated.</p>\r\n<p style="text-align: right;">\r\n	-&nbsp; <a href="./credits/">Pragyan CMS Team</a></p>\r\n', '2011-01-02 03:55:49', 0),
(4, '<h2>\r\n	How to use Pragyan CMS</h2>\r\n<p>\r\n	The operations are visible near the top of this page once you login as administrator.</p>\r\n<p>\r\n	<strong>Edit</strong> : You can edit the contents of the page, manage uploaded files to the page and see and even roll-back to previous revisions of this page. Also you can create draft copies and manage the comments on the page. <em>NOTE : These functionalities are module specific and its available in the &#39;article&#39; module. Different modules like Gallery or Form or Forum or Quiz will have different options under this section.</em></p>\r\n<p>\r\n	<strong>Admin :: Global Settings</strong> : Change the important settings like Website Name, Template, Footer, Email, Upload Limit, OpenID, Censored words and more.</p>\r\n<p>\r\n	<strong>Admin ::</strong> <strong>Template Management</strong> : Install and Uninstall templates.</p>\r\n<p>\r\n	<strong>Admin ::</strong> <span style="font-weight: bold;">Module</span><strong> Management</strong> : Install and Uninstall modules.</p>\r\n<p>\r\n	<strong>Admin ::</strong> <span style="font-weight: bold;">Widgets</span><strong> Management</strong> : Install, Disable and Uninstall widgets and also modify their global configurations.</p>\r\n<p>\r\n	<strong>Admin :: </strong><span style="font-weight: bold;">Icons</span><strong> Management</strong> : Add icons to your website pages from here. You can chose from a list of default icons or upload your own.</p>\r\n<p>\r\n	<strong>Admin :: Email Registrants </strong>: Send mass emails to users or groups of registered users. You can also save and load email templates.</p>\r\n<p>\r\n	<strong>Admin</strong> <strong>::</strong> <span style="font-weight: bold;">Group</span><strong> Management</strong> : You can create new group or delete a group and add or remove users from a group. Groups can be used for mass-allotment of permissions to a particular set of users belonging together as a group.</p>\r\n<p>\r\n	<strong>Admin :: Site Maintenance</strong> : Only for experts. Use it for fixing permissions, search indexes, templates and modules.</p>\r\n<p>\r\n	<strong>Admin</strong> <strong>:: User Management</strong> : Manage users registered to the website, activate or deactivate them, or even edit their profiles and create new users. You can also search for users based on any of their information.</p>\r\n<p>\r\n	<strong>Admin :: User Profile </strong>: Edit the user profile form and add extra information which every user must fill up to complete their profile page.</p>\r\n<p>\r\n	<strong>Permissions</strong> : Grant or remove permissions to users and groups. Permissions are page-specific and they automatically trickle-down to child pages also.</p>\r\n<p>\r\n	<strong>PDF </strong>: Convert the page into PDF and download it directly. You can also specify the depth level upto which the child pages will also be included. To download the main page only, set depth to 0.</p>\r\n<p>\r\n	<strong>Page Settings :: Page Information </strong>: Edit the page name, title, page-template and also enable or disable page comments. You can also select from the various menu options (Classic, Complete and Multidepth) and you can organize the child pages to be displayed in the page menu.</p>\r\n<p>\r\n	<strong>Page Settings :: Create New Page </strong>: You can create a Child page of the page. Use this for adding links to the page menu by selecting the module type as &quot;Link&quot; and &quot;External Link&quot; for pointing to other domains.</p>\r\n<p>\r\n	<strong>Page Settings :: Copy or Move Page </strong>: You can copy or move the page or the entire page-tree which includes its child pages also, to some other destination path in the same website.</p>\r\n<p>\r\n	<strong>Page Settings :: Page Inherited Information </strong>: Add here some content which will be inherited to all the child pages.</p>\r\n<p>\r\n	<strong>Widgets </strong>: Add and configure the widgets to the page. You can also change the widget location in the page and propagate the widget to all the child pages also.</p>\r\n<p>\r\n	<strong>Profile </strong>: Change your user profile information like profile image, password and name and any other fields which may have been added by the site administrator.</p>\r\n<p>\r\n	<strong>Search </strong>: Search the website using the inbuilt Sphider search engine.</p>\r\n<p>\r\n	Some points to note :&nbsp;</p>\r\n<ul>\r\n	<li>\r\n		A normal content page is of type article.</li>\r\n	<li>\r\n		You can create child-pages of a page from its &quot;Page Settings&quot;</li>\r\n	<li>\r\n		For page-specific templates to work, the &quot;Allow Page Specific Templates&quot; in the Global Settings under &quot;Admin&quot; must be selected.</li>\r\n</ul>\r\n', '2011-01-02 04:32:47', 0),
(2, '<h2>\r\n	Team behind Pragyan CMS</h2>\r\n<p>\r\n	Pragyan CMS is created by a group of engineering students of <a href="http://www.nitt.edu">National Institute of Technology Trichy (NIT-T)</a> in Tamil Nadu, India.&nbsp; The CMS is contributed to by a large number of students who are also members of the Central Webteam of NIT-T, also known as the &quot;Delta Force&quot;.</p>\r\n<p>\r\n	Pragyan CMS v2.6 was released officially by Sahil Ahuja of 2004 batch. The latest Pragyan CMS v3.0 is released officially by <span style="text-decoration: none;"><a href="http://abhishekdelta.wordpress.com" style="text-decoration: none;">Abhishek Shrivastava (abhishekdelta)</a> of 2007 batch.</span> Below is the list of all the co-developers and contributors since version 2.</p>\r\n<p>\r\n	Version 3 core developers :</p>\r\n<ul>\r\n	<li>\r\n		Abhishek Shrivastava [jereme]</li>\r\n	<li>\r\n		Chakradar Raju [chakradarraju]</li>\r\n	<li>\r\n		Balanivash [balanivash]</li>\r\n	<li>\r\n		Boopathi Rajaa[scriptle]</li>\r\n</ul>\r\n<p>\r\n	Version 3 contributors list (tickets, patches and bug-fixes):</p>\r\n<ul>\r\n	<li>\r\n		Aparna Chugh</li>\r\n	<li>\r\n		Shiva Nandan</li>\r\n	<li>\r\n		Siddhant Sanyam</li>\r\n	<li>\r\n		Mohnish Prasanna</li>\r\n</ul>\r\n<p>\r\n	Version 2 credits to :</p>\r\n<ul>\r\n	<li>\r\n		Abhilash R [abhithekid]</li>\r\n	<li>\r\n		Anshu Prateek [analyst]</li>\r\n	<li>\r\n		Ankit Srivastava</li>\r\n	<li>\r\n		Bharath Venkatesh [bhattu]</li>\r\n	<li>\r\n		Sahil Ahuja</li>\r\n	<li>\r\n		Jithin K.R [jithinkr]</li>\r\n	<li>\r\n		Shankarram [kulz]</li>\r\n	<li>\r\n		Mrinal Kumar</li>\r\n	<li>\r\n		Harini A</li>\r\n	<li>\r\n		Abhishek Verma</li>\r\n</ul>\r\n<p>\r\n	And also to the following people who have contributed minor changes,<br />\r\n	enhancements, bugfixes or support for a new language since version 2.1.0:<br />\r\n	M. Surya Sankar, Sapna Shukla, Shruti J, Ashwathi Krishnan, K.R Arvind [kra],<br />\r\n	T.V. Karthik</p>\r\n<p>\r\n	For more information about the Pragyan Team, read the CREDITS.html file inside the docs folder.</p>\r\n<p>\r\n	Feel free to contact at [tex]abhishek@integriti.org.in[/tex]</p>\r\n', '2011-01-02 04:02:15', 0),
(3, '<h2>\r\n	Features</h2>\r\n<p>\r\n	This version is an improvement over the previous version 2.6. Some of the new features of Pragyan v3.0 :</p>\r\n<ul>\r\n	<li>\r\n		Multi-user open source content management system</li>\r\n	<li>\r\n		Organize and facilitate collaborative content creation and updation</li>\r\n	<li>\r\n		Integrated CkEditor v3.1 for easy editing of pages from the web</li>\r\n	<li>\r\n		Choose from a number of pre-installed widgets and configure them as per your needs</li>\r\n	<li>\r\n		Handy tools to manage permissions for groups and users</li>\r\n	<li>\r\n		Quickly create pages of type article, gallery, quiz, form, forum, poll, etc.</li>\r\n	<li>\r\n		Support for OpenID</li>\r\n	<li>\r\n		Template installation module with compatibility check</li>\r\n	<li>\r\n		Have page-specific templates so every page can have its own template</li>\r\n	<li>\r\n		Easily convert pages into PDF</li>\r\n	<li>\r\n		Integrated Google Maps and Latex plugins in articles</li>\r\n	<li>\r\n		Powerful search engine powered by Sphider</li>\r\n	<li>\r\n		Send mass emails to site registrants</li>\r\n	<li>\r\n		Security and protection against hacking attempts ( like XSS and Injections )</li>\r\n	<li>\r\n		Use captcha (without internet) or ReCaptcha (with internet) for avoiding spams</li>\r\n	<li>\r\n		Associate icons with every page using icons management</li>\r\n	<li>\r\n		Save articles as drafts and edit them later</li>\r\n	<li>\r\n		Censor specific words in articles and forum posts</li>\r\n	<li>\r\n		Easily install new modules using the module management</li>\r\n	<li>\r\n		Edit the registration form and user profile information fields as per your needs</li>\r\n</ul>\r\n', '2011-01-02 04:34:13', 0);




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
-- Dumping data for table `pragyanV3_blacklist`
--

INSERT INTO `pragyanV3_blacklist` (`id`, `domain`, `ip`) VALUES
(1, 'mailinator.com', '66.135.37.96'),
(2, 'tempmail.net', '63.223.120.144'),
(3, 'mytrashmail.com', '75.126.181.138'),
(4, 'anonymbox.com','204.197.242.56'),
(5, 'mailexpire', '66.199.224.85');

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
('sqlquery', 'sqlquery_desc'),
('share','share;share_files;share_comments');

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
('censor_words',''),
('recaptcha','0'),
('recaptcha_public',''),
('recaptcha_private',''),
('deadline_notify','0');

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
(1, 'credits', 0, '2010-05-09 16:34:17', '2010-09-12 04:00:12', 'Credits', 'article', 2, 'crystalx', 3, -1, 1, 1, 1, 1, 'classic', 1),
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
('safedit', 'create', 'Create', 2, 'Create'),
('share', 'view', 'View', 0, 'View the share'),
('share', 'edit', 'Edit', 0, 'Edit the share description'),
('share', 'create', 'Create', 0, 'Create a Share'),
('share', 'moderate', 'Moderate', 0, 'Moderate a share page');
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
(1, 'Server Date and Time', 'serverDateTime', 'Display the current date and time in the website', '0.01', 'Abhishek Shrivastava', 'server_date_time'),
(2, 'Count Down', 'count_down', 'Display the countdown to a given date', '0.01', 'Balanivash', 'count_down'),
(3, 'Updates', 'updates', 'Display the news/updates', '0.01', 'Balanivash', 'updates'),
(4, 'Slider', 'slider', 'An image slider or display an image', '0.01', 'Balanivash', 'slider'),
(5, 'Facebook', 'facebook', 'To include facebook like option for a page in the site or the whole site or a facebook page', '0.01', 'Balanivash', 'facebook');


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

