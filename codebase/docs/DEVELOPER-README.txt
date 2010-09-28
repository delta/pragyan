Pragyan CMS v3.0 - Developer's Guide
=======================================================================

:::::::::::::::::::::::::::::::: Index ::::::::::::::::::::::::::::::::

(0) Downloading latest build
(1) Installation Instructions
(2) Reinstallation Instructions
(3) Coding Guidelines
	+ Security
	+ Portability
	+ Documentation
(4) CMS Working Overview
	+ Pages
	+ Page Path
	+ Modules
	+ Page ID
(5) Pretty URLS
(6) Permissions
	+ Overview
	+ Storing Permissions
	+ Calculating Permissions
	+ Granting Permissions
(7) Module
(8) Templates
	+ Creating Templates
	+ Installing Templates


______________________________________________________________________

(0)++++++++ DOWNLOADING LATEST BUILD +++++++++++++++++++++++++++++++++
______________________________________________________________________

Pragyan CMS latest build source code can be downloaded from the SVN 
repository using the following svn checkout command :

>svn co SVN-PATH pragyan

where replace SVN-PATH with anyone of the following :

	For only source-code and default templates :
https://pragyan.svn.sourceforge.net/svnroot/pragyan/trunk/codebase/

	For only Pragyan CMS templates repository :
https://pragyan.svn.sourceforge.net/svnroot/pragyan/trunk/templates/

	For both source-code and all the templates (incl. default) :
https://pragyan.svn.sourceforge.net/svnroot/pragyan/trunk/

The above command will create a folder with the name 'pragyan' in 
the current directory and download the latest sourcecode in there.

You can also use Eclipse Subversion if you don't have SVN installed.
______________________________________________________________________

(1)++++++++ INSTALLATION INSTRUCTIONS ++++++++++++++++++++++++++++++++
______________________________________________________________________

For those who have downloaded a nightly build of Pragyan CMS v3, and
doing a fresh reinstall please follow the following steps before you
install the CMS (Note that this is NOT for the users who have 
downloaded an official release from Pragyan CMS Sourceforge.net
home page, those users can directly refer to INSTALL/INSTALL.txt)  :

1) Delete all the contents of the cms/config.inc.php file
2) Delete the .htaccess file in the root directory of Pragyan CMS
3) Rename the htaccess-dist file to .htaccess in the root directory 
(If you're using Windows, use Notepad++ to save the file as .htaccess)
4) Follow the instructions in the INSTALL/install.txt file.


______________________________________________________________________

(2)++++++++ REINSTALLATION INSTRUCTIONS ++++++++++++++++++++++++++++++
______________________________________________________________________

To safely uninstall Pragyan CMS so that you can reinstall from the 
same files, do the following :

1) If you're in Linux-based operating system, go to the root directory
of Pragyan CMS and execute the following script like this :
>sh scripts/uninstall.sh
Next, delete the old database and create a new one.
2) If you're NOT in a Linux-based operating system, simply follow the 
steps mentioned in INSTALLATION INSTRUCTIONS.


______________________________________________________________________

(3)++++++++ CODING GUIDELINES ++++++++++++++++++++++++++++++++++++++++
______________________________________________________________________
	
	Please follow the following rules strictly while coding for 
	Pragyan CMS
	______________________________________________________________
	
	::::::::::::::::::::::::: SECURITY :::::::::::::::::::::::::::
	______________________________________________________________
	
	
	* Dangerous Variables = $_GET, $_POST, $_REQUEST
	
	* Protection Function = escape(), safe_html() 
	(definitions are available in common.lib.php)

	===== RULE 1 =====
	
	Use the escape() function for every dangerous variable
	in any assignment operation i.e. when a PHP variable is being 
	assigned a value of a dangerous variable using '=' operator. 
	Something like $var=$_GET['hi'] should be made to 
	$var=escape($_GET['hi']). This check is to prevent attacks
	of type SQL Injection. 
	
	===== RULE 2 =====
	
	Be careful not to escape the same variable TWICE.

	===== RULE 3 =====
	
	Don't use escape() function when a dangerous variable 
	is used in a conditional operation i.e in a IF, FOR, WHILE or
	'?:' condition statement.

	===== RULE 4 =====
	
	In a MySQL query, put the table name and column names
	within ` ` escape quotes. e.g. "SELECT apple FROM tree WHERE 
	apple=2" should be 
	"SELECT `apple` FROM `tree` WHERE `apple`=2"

	===== RULE 5 =====
	
	Use the safe_html() function whenever a dangerous variable 
	is directly or indirectly being echoed or printed onto the 
	screen. This is to prevent XSS attack. e.g. echo $_GET['hi'];
	should be echo safe_html($_GET['hi']);

	===== EXAMPLES ===== 

	Vulnerable Code : 
	-----------------

	if(isset($_GET['hello']))
		$var = $_GET['hello'];
	$apple = $_POST['bye'];
	echo $var.$apple.$_GET['me'];
	$safe = $_REQUEST['me'];
	$query = "SELECT * FROM $var WHERE column = $safe 
	AND ".$_GET['wow']." = '{$_POST['next']}'";
	
	mysql_query($query);
	echo $query;


	Secure Code :
	------------- 

	if(isset($_GET['hello']))
		$var = escape($_GET['hello']);
	$apple = escape($_POST['bye']); 
	echo safe_html($var.$apple.$_GET['me']);
	$safe = escape($_REQUEST['me']);
	$query = "SELECT * FROM `$var` WHERE `column` = $safe AND `".
	escape($_GET['wow'])."` = '".escape($_POST['next'])."'";
	mysql_query($query);
	echo safe_html($query);
	
	
	______________________________________________________________
	
	::::::::::::::::::::::: PORTABILITY ::::::::::::::::::::::::::
	______________________________________________________________
	
	===== RULE 1 =====
	
	When creating Templates, don't use the PHP echo short form,
	use the entire echo statement. For example - <?=$HELLO?>
	should be converted to <? echo $HELLO; ?>
	
	===== RULE 2 =====
	
	Avoid using the OS-specific commands in functions like exec().
	In case its impossible to avoid it, try to find an alternative
	for Windows and/or Linux-bases OS also and implement it.
	
	______________________________________________________________
	
	::::::::::::::::::::::: DOCUMENTATION ::::::::::::::::::::::::
	______________________________________________________________
	
 	When writing code, make sure the documentation (comments, so 
 	to speak) follows the guidelines given below. This helps in 
 	generating documentation using tools like Doxygen. Note the
 	slashes, the stars, the dots and anything else typed here.
 

	===== RULE 1 =====
	
	GENERAL COMMENTS:
	
	Start comments using /** rather than /*, or if you prefer the
	C style single line comments, use three slashes in atleast two
	consecutive lines as in
	///
	///
	instead of 
	//
	
	If you use only a single /// line, then that qualifies as a
	brief description. Use it ONLY when you are sure your comment
	can be fit in one line. Otherwise, use multiple /// lines.
 
 	Comments, in general, must look like either
 	
	/**
  	 * Comment goes here
  	 */

	OR

  	///Comment goes here (this qualifies as a brief description)
  	
  	OR
  	
  	///Comment goes here (this qualifies as a detailed
  	///description, because it has 2 or more lines)	
  	
  	===== RULE 2 =====

 	VARIABLES or STATEMENTS:

 	/**
	 * A brief description here. (Note the period (full stop))
	 * More detailed description goes here.
	 */
 	$xyz = hello("world");
 	
 	Note that the documentation is BEFORE the statement.

	===== RULE 3 =====
	
	When it comes to variables or data members of a class,struct,
	union,file or enum, you can also put documentation after the
	declaration. Do:

 	private $myPrivateMember; ///< Description goes here (note 
 	/// the less than symbol)

	===== RULE 4 =====
	
 	FUNCTIONS:

	/**
	 * A description of the function goes here. Give a 
	 * description of each of the arguments that the function 
	 * requires after this, followed by an indication of what the 
	 * return values represent, as shown below:
	 * @param $param1 $param1 is the first parameter, and a
	 * description of the parameter goes here.
	 * @param $param2 The second parameter, and so on for as many 
	 * parameters as the function takes
	 * @return Describe what the function returns here
	 */
	function functionName($param1, $param2) {

	}

	===== RULE 5 =====
	
	NEW FILE :
	
	If you create a new file, make sure the following comment is
	on top of it :
	
	/**
	 * @package pragyan
	 * @file filename.php
	 * @brief Brief description of the file comes here.
	 * Detailed description here is optional.
	 * @author Abhishek Shrivastava <i.abhi27[at]gmail.com>
	 * @copyright (c) 2010 Pragyan Team
	 * @license http://www.gnu.org/licenses/ GNU Public License
	 * For more details, see README
	 */
	 
	 
	===== RULE 6 =====
	 
	CLASS :
	 
	/**
	 * @class myclass
	 * @brief This is the description of the class myclass.
	 * Here goes the detailed description, which is optional.
	 */
	class myclass
	{
	 ...
	}

	===== RULE 7 =====
	 
	BUGS or WARNINGS:
	 
	If there are any bugs in your code, or any warning which you
	may want the person who is going to change your code, to 
	know, or if you want to ask that person to refer to some 
	other fileyou can use the following tags within the comment 
	block:
	 
	/**
	 * ... Here are the usual stuff, description, author, etc
	 * @bug This is a bug in the code
	 * @bug This is another bug
	 * @warning This is a warning
	 * @warning This is another warning
	 */
	 
	===== RULE 8 =====
	
	USAGE:
	
	If you are documenting a function or a class and you want to
	also tell the user how to use that function or how to create
	an object of that class, you can use a similar comment :
	
	/**
	 * Here goes the detailed description.
	 * Usage :
	 * @code
	 * <?php
	 * 	$example=myfunction(1,2,3);
	 * ?>
	 * @endcode
	 */
	 
	Note that the example code should be within the @code, 
	@endcode sub-block and that should come right below the
	detail description of the code.
	
	===== RULE 8 =====
	
	REFERENCING:
	
	If you want the reader of the code to also refer to some other
	function, class, file, or a URL, you can use the following:
	
	/**
	 * ...
	 * @see a_function()
	 * @see a_class
	 * @see a_filename.php
	 * @see a_class::its_member_variable
	 */
	 
	===== RULE 9 =====
	
	NOTES and TODO:
	
	/**
	 *
	 * @note This is a note for you, or someone else, to remember.
	 * @todo This is a TODO for the code below.
	 * @todo Another TODO item for the code.
	 */
	 
	EXAMPLE 1:
	
	DOCUMENTATION OF FILE common.lib.php :
	
	/**
	 * @package pragyan
	 * @file common.lib.php
	 * @brief Contains the most frequently used functions by CMS
	 * It has functions related to database connectivity, user
	 * information retrieval, and other string manipulating
	 * functions also.
	 * @todo The templates related functions should be moved to 
	 * the template.lib.php instead of here.
	 * @author Abhishek Shrivastava <i.abhi27[at]gmail.com>
	 * @copyright (c) 2010 Pragyan Team
	 * @license http://www.gnu.org/licenses/ GNU Public License
	 * For more details, see README
	 */ 
	
	EXAMPLE 2:
	
	DOCUMENTATION OF CLASS googlemaps in GoogleMaps.class.php :
	
	/**
	 * @class googlemaps 
	 * @brief Class to render Google maps in article module
	 * It uses the GoogleMaps API and Geocoding technique to
	 * convert the location name entered by the user into the
	 * precise location in the map.
	 * Usage:
	 * @code
	 * 	$html = googlemaps::render($location_name);
	 * @endcode
	 * The $html will contain the HTML code for the map.
	 * @todo Add the option for the user to specify the default 
	 * zoom
	 * @warning This code requires internet connection to work. 
	 * So make sure you are connected to internet before 
	 * modifying and testing.
	 * @bug Sometimes, the location shown is not precise/correct.
	 */
	 
	EXAMPLE 3:
	
	DOCUMENTATION OF FUNCTION arraytostring($array)
	
	/**
	 * Convert an array to a string recursively
	 * @param $array Array to convert
	 * @return string containing the array information
	 */
	 
	NOTE : All the documentation blocks should appear BEFORE the 
	code for which documentation is done.
______________________________________________________________________

(4)++++++++ CMS WORKING OVERVIEW +++++++++++++++++++++++++++++++++++++
______________________________________________________________________


	______________________________________________________________
	
	::::::::::::::::::::::: PAGES ::::::::::::::::::::::::::::::::
	______________________________________________________________
	
	The Pragyan CMS works with the concept of pages. Each page on 
	the CMS, is called a page (!).
	All pages have the following properties : 
		page id, parent page id, page name, module name, 
		module component id.
	
	______________________________________________________________
	
	::::::::::::::::::::::: PAGE PATH ::::::::::::::::::::::::::::
	______________________________________________________________
	
	If pages are arranged in the form of a tree, with each page's 
	parent node  being the page having page id same as that page's
	parent page id, we get the complete website tree. 
	
	Using this website tree the page paths are formed. To find the
	page of a particular page, keep going up until the root page 
	is encountered. 
	
	The root page is the page with both page id and the parent 
	page id equal to 0.
	
	For example, in the website tree:

		root
		+---classes
		|	+---fir
		|	|	+---a
		|	|	+---b
		|	|	+---c
		|	+---sec
		|	|	+---d
		|	|	+---e
		|	|	+---f
		|	+---thi
		|		+---g
		|		+---h
		|		+---i
		+---rules
		|	+---gen
		|	+---spe
		+---tests
		|	+---fir
		|	+---sec
		|	+---forum
		+---departments
			+---comsc
			+---ece
			+---civil

	The page path of the page named a will be: /classes/fir/a
	The page path of the second page named b will be: 
	/classes/thi/b
	______________________________________________________________
	
	::::::::::::::::::::::: MODULE :::::::::::::::::::::::::::::::
	______________________________________________________________
	
	All pages have a property called module name. This module name
	specifies the type of the page. Each module must come complete
	with its own set of actions that provide an interface for the 
	user to interact with the module. Each action may use several
	subactions privately to work effectively.

	For example, Article is a module that supports viewing 
	(action "View") and editing (action "Edit"); and Form is a 
	module that supports viewing (to register), editing, and 
	viewing and editing of registrants.

	Users or groups can be granted permissions for each action 
	(but not each subaction) separately, this implies that a 
	module must divide the different tasks that can be performed
	on it in such a way that tasks that might require different 
	levels of authorization are implemented as different actions. 
	For example, an article must be viewable by mostly all 
	visitors to a web site, but it must be editable only by a 
	select few. Hence, View and Edit are different actions.

	Modules may be plugged into and out of the Pragyan CMS as 
	required.
	______________________________________________________________
	
	::::::::::::::::::::::: PAGE ID ::::::::::::::::::::::::::::::
	______________________________________________________________
	
	All pages have a unique page id. 
	The root of Pragyan CMS starts from a /home directory. This 
	can however be changed through the .htaccess file.
	
	The part of the path specified after /home is called the page 
	path.
	
	For example, in 
	http://pragyan.org/myinstallfolder/home/events/forum, 
	the page path is /events/forum
	
	Using the page path, the page id is computed.

______________________________________________________________________

(5)++++++++ PRETTY URLS ++++++++++++++++++++++++++++++++++++++++++++++
______________________________________________________________________
	
	Everything after /home in the $_SERVER['REQUEST_URI'] variable
	is called the page path. This page is found using this page 
	path.
	
	Example : 
	In the request 
	http://pragyan.org/myinstallfolder/home/events/forum,
	
		the CMS install folder is : myinstallfolder
		the CMS root is : home
		the page path is : /events/forum
		
	In case any action is passed to the page, it is passed by 
	appending a + symbol followed by the action. 
	
	Example :
	To perform the action edit on a page /events, the REQUEST_URI 
	would be : http://pragyan.org/myinstallfolder/home/events+edit
	
	For passing any GET variables, the format is the same as 
	normal PHP file, except that instead of the first ? symbol, an
	& symbol is to be used. 
	
	Example :
	To pass the get variable id with value 2, the REQUEST_URI 
	would be : http://pragyan.org/myinstallfolder/home/events&id=2
	
	An example with multiple GET variables and an action :
	http://pragyan.org/home/events/forum+edit&threadid=56&postid=1
	Here,
		Page path is : /events/forum
		Action is : edit
		GET variables are : threadid = 56 and postid = 1

______________________________________________________________________

(6)++++++++ PERMISSIONS ++++++++++++++++++++++++++++++++++++++++++++++
______________________________________________________________________

	______________________________________________________________
	
	::::::::::::::::::::::: OVERVIEW :::::::::::::::::::::::::::::
	______________________________________________________________
  
The permissions module handles the responsibility of resolving the 
actions that any given user can perform on any given page. The module
is used during every page access to determine if the user has the 
rights to perform the requested operation on that page.

A permission may be a page-level permission, or a module-level 
permission. A page-level permission is one that does not depend on the
module of the given page, that is, the permission is available 
regardless of the page module. A module-level permission is specific
to a module. Different modules may have their own set of permissions
defined. For example, grant and settings are page-level permissions,
while view is necessarily a module-level permission, and there may
even be modules that do not support viewing at all. 

All the functions required to calculate, and manipulate permissions
are packaged inside permission.lib.php

	______________________________________________________________
	
	::::::::::::::::::::::: STORING PERMISSIONS ::::::::::::::::::
	______________________________________________________________
	
The permissionlist Table:
The permissionlist table maps the actions available for each module 
to a permission id (perm_id).
The actions are displayed in the action bar on each page in an order
determined by the permission rank (perm_rank).
The userpageperm Table:
The userpageperm table stores all the set permissions, for each user
on each page. The permission may be yes (Y) or no (N).

Only the permissions that have been set explicitly are
stored. Effective permissions are calculated based on rules described 
in the next section.

	______________________________________________________________
	
	::::::::::::::::::::::: CALCULATING PERMISSIONS ::::::::::::::
	______________________________________________________________

To determine whether a given group has permissions to perform a 
certain operation on a given page, the following procedure is used:
+ Move up the page path, one level at a time, and check for any set
permissions for the group for the correct module and action.
+ On encountering a no, the result is no. Quit the procedure.
+ If the root is reached without encountering any set permissions, the
result is again no.
+ If the root was reached after encountering at least one yes, the 
result is yes.

To determine whether a given user can perform a given operation on a 
given page, the following algorithm is used:
+ Find all the groups the user belongs to.
+ Calculate the permissions of each of the groups, in increasing order
of priority. If any group has an effective permission of Y, the user 
is also given the permission.
+ If all groups have permission unset or N, calculate the permission 
for the user himself using exactly the same procedure as that for a 
group, and the result is the permission thus calculated.  

	______________________________________________________________
	
	::::::::::::::::::::::: GRANTING PERMISSIONS :::::::::::::::::
	______________________________________________________________

* add() is defined as :
* 	start from top, if you find a no on the way, answer is no,
*		if you find no nos and at least one yes, answer is yes
*		if you find nothing, (all unsets) answer is no
*
* All groups have a certain priority assigned to them.
* Possible permissions are  : Y, N, unset (i.e. no value in the table)
* To find the permission for a particular action on a particular page:
* 	Find all groups that he belongs to.
*  	Take all the groups priority wise and start from the lowest 
	priority group and do this for each group:
* 		In the page path, starting from the root node going
		towards the leaf node, do add and get the result
* 		Group all results of same priority, and make it yes,
		if even a single yes is available (OR)
* 		Now, we ll have an array of yeses and nos arranged by
		priority.
*    +-----------+---+---+---+---+
* 		| Priority: | 0 | 1 | 2 | 3 |
*    +-----------+---+---+---+---+
* 		| Perm    : | N | N | Y | N |
*    ------------+---+---+---+---+
* 		i.e Do an OR to get result (regardless of priority).
*
* -- Condition: To give a user the perm_grant permission, he has to
		belong to a group. --
*
* While giving permission: (grant)
* 	For one module, for one page:
* 	Find all groups that he belongs to.
* 	If he gets perm_grant individually : find the highest 
	priority group that he belongs to . save it in A
* 	If he get perm_grant from group(s) :
* 		Find groups which give him perm_grant on that page
* 		Find the highest priority among those. save it in B
* 		Allow him to give or take permission only from those 
		groups with priority <= max(A,B)
* 		Allow him to give or take permission from all 
		individual users.
______________________________________________________________________

(7)++++++++ MODULE +++++++++++++++++++++++++++++++++++++++++++++++++++
______________________________________________________________________

Refer module.lib.php file.

______________________________________________________________________

(8)++++++++ TEMPLATES ++++++++++++++++++++++++++++++++++++++++++++++++
______________________________________________________________________

All the templates are stored in the cms/templates folder. Every
template folder must have a index.php file which contains the template
source code. The associated css and image files can be put in css and
images directories inside the cms/templates/template-name folder.
	______________________________________________________________
	
	::::::::::::::::::::::: CREATING TEMPLATES :::::::::::::::::::
	______________________________________________________________
	
	Prepend all css paths by <?php echo $TEMPLATEBROWSERPATH; ?>
	The following are the list of labels which are recognised by
	Pragyan CMS and replaced during template HTML rendering :
	$TITLE : Title of the page
	$MENUBAR : Vertical side Menubar of the page
	$ACTIONBARMODULE : Module-defined actions
	$ACTIONBARPAGE : Actions which are NOT module-specific
	$BREADCRUMB : Shows the current page path in a link-able way
	$CONTENT : Main HTML content generated by the CMS or Module
	$DEBUGINFO : Debugging information (only used by Developers)
	$ERRORSTRING : Errors encountered by the CMS
	$WARNINGSTRING : Warnings associated with the action done
	$INFOSTRING : Information associated with the action done
	$STARTSCRIPTS : List of Javascript functions to be executed
			upon page load. i.e. <body onload="foo();">
			
	$SITEDESCRIPTION : Site META description, useful for SEO
	$SITEKEYWORDS : META Keywords, used for indexing by spiders
	$FOOTER : Footer text e.g. "Copyright (c) 2010" 
	
	The following must be included in the index.php file:
	<?php echo $ERRORSTRING?>
	<?php echo $INFOSTRING?> 
	<?php echo $WARNINGSTRING?>
	<?php echo $ERRORSTRING?>
	<?php echo $INFOSTRING?>
	<?php echo $WARNINGSTRING?>
	<?php echo $CONTENT?>
	<?php echo $ACTIONBARPAGE?>
	<?php echo $ACTIONBARMODULE?>
	<?php echo $MENUBAR?>
	<?php echo $STARTSCRIPTS?>
	
	______________________________________________________________
	
	::::::::::::::::::::::: INSTALLING TEMPLATES :::::::::::::::::
	______________________________________________________________
	
	ZIP the index.php file along with other files and folders and 
	name the ZIP file as the name of the template. Then use the 
	Template Installation feature inside "Admin" action and upload
	your ZIP file and follow the instructions.
	
