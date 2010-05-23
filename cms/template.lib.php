<?php
/**
 * @package pragyan
 * @copyright (c) 2008 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
function getPageTemplate($pageId)
{
 	
 	$query="SELECT `allow_pagespecific_template` FROM `".MYSQL_DATABASE_PREFIX."global`";
	$result=mysql_query($query);
	$row=mysql_fetch_row($result);
 	if($row[0]==0)
 		return DEF_TEMPLATE;

 	$query="SELECT `page_template` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_id`=$pageId";
	$result=mysql_query($query);
	$row=mysql_fetch_row($result);
 	if($row[0]=="")
 		return DEF_TEMPLATE;
 	return $row[0];
}

function templateReplace(&$TITLE,&$MENUBAR,&$ACTIONBARMODULE,&$ACTIONBARPAGE,&$BREADCRUMB,&$INHERITEDINFO,&$CONTENT,&$DEBUGINFO,&$ERRORSTRING,&$WARNINGSTRING,&$INFOSTRING,&$STARTSCRIPTS) {
	global $cmsFolder;
	global $sourceFolder;
	global $templateFolder;
	global $moduleFolder;
	global $urlRequestRoot;
	global $TEMPLATEBROWSERPATH;
	global $TEMPLATECODEPATH;
	$TEMPLATEBROWSERPATH = "$urlRequestRoot/$cmsFolder/$templateFolder/".TEMPLATE;
	$TEMPLATECODEPATH = "$sourceFolder/$templateFolder/".TEMPLATE;
	include ($TEMPLATECODEPATH."/index.php");
}

