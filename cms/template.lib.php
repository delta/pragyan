<?php
function templateReplace(&$TITLE,&$MENUBAR,&$ACTIONBARMODULE,&$ACTIONBARPAGE,&$BREADCRUMB,&$MENUBAR,&$CONTENT,&$DEBUGINFO,&$ERRORSTRING,&$WARNINGSTRING,&$INFOSTRING,&$STARTSCRIPTS) {
	global $sourceFolder;
	global $templateFolder;
	global $moduleFolder;
	global $urlRequestRoot;
	global $TEMPLATEBROWSERPATH;
	global $TEMPLATECODEPATH;
	$TEMPLATEBROWSERPATH = "$urlRequestRoot/$sourceFolder/$templateFolder/".TEMPLATE;
	$TEMPLATECODEPATH = "$sourceFolder/$templateFolder/".TEMPLATE;
	include ($TEMPLATECODEPATH."/index.php");
}

?>