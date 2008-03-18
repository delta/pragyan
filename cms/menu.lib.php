<?php
/*
 * Created on Oct 8, 2007
 *
 * DONE : Add a parent sibling menu which will be displayed in place of child menu if there are no children (or no
 *	permissions to view children)
 * DONE : dont show anything in child menu in case the user has permission to 0 child pages.
 * In its  place show parent sibling menu.
 *
 */

 function getMenu($userId, $pageIdArray) {
 	$pageId = $pageIdArray[count($pageIdArray) - 1];
	$page_query = "SELECT page_displaymenu,page_displaysiblingmenu FROM ".MYSQL_DATABASE_PREFIX."pages WHERE page_id=".$pageId;
	$page_result = mysql_query($page_query);
	$page_row = mysql_fetch_assoc($page_result);
	if($page_row['page_displaymenu']==1) {
		if($page_row['page_displaysiblingmenu']==1) {
			$result.=getSiblingMenu($userId,$pageIdArray);
		}
		if($childmenu=getChildrenMenu($userId,$pageId))
			$result.=$childmenu;
		else {
			if(count($pageIdArray)>=3)
			$result=getParentSiblingMenu($userId,$pageIdArray[count($pageIdArray)-3]).$result;
		}
		$result="<div class=\"menu\">\n".$result."</div>\n";
	}
	return $result;
 }

 function getSiblingMenu($userid,$pageIdArray) {
 	$pId = $pageIdArray[count($pageIdArray) - 1];
 	if(count($pageIdArray)<=1)	return "";
 	$pageId = $pageIdArray[count($pageIdArray) - 2];
 	if($pageId==$pId) return "";
 	$page_query = "SELECT page_name,page_title,page_menurank FROM ".MYSQL_DATABASE_PREFIX."pages WHERE page_id=".$pageId." ORDER BY `page_menurank` ASC";
 	$page_result = mysql_query($page_query);
	$page_row = mysql_fetch_assoc($page_result);
	global $sourceFolder;
	global $urlRequestRoot;
	global $templateFolder;
	$imagesFolder = "$sourceFolder/$templateFolder/".TEMPLATE."/images";
	$goUpImageLeft = "$imagesFolder/go-upLeftMenu.gif";
	$goUpImageRight = "$imagesFolder/go-upRightMenu.gif";
	if(file_exists($goUpImageLeft)) $goUpImageLeft = "<img src=\"$urlRequestRoot/$goUpImageLeft\" />"; else $goUpImageLeft = "";
	if(file_exists($goUpImageRight)) $goUpImageRight = "<img src=\"$urlRequestRoot/$goUpImageRight\" />"; else $goUpImageRight = "";
	$resulthead="<a href=\"../\"><div class=\"siblingmenuhead menuhead\">$goUpImageLeft ".$page_row['page_title']." $goUpImageRight</div></a>\n";


	$previousImage = "$imagesFolder/go-previous.gif";
	$nextImage = "$imagesFolder/go-next.gif";
	if(file_exists($previousImage)) $previousImage = "<img src=\"$urlRequestRoot/$previousImage\" />"; else $previousImage = "";
	if(file_exists($nextImage)) $nextImage = "<img src=\"$urlRequestRoot/$nextImage\" />"; else $nextImage = "";

	$childrenarray = getMenuChildren($userid,$pageId);
	$resultmenuarray = array();
	if(count($childrenarray)==0) 	return "";
	else {
		foreach($childrenarray as $child) {
			if($child['page_module']!='external')
				$link = "../".$child['page_name'];
			else {
				$query = "SELECT `page_extlink` FROM `".MYSQL_DATABASE_PREFIX."external` WHERE `page_modulecomponentid` =
				(SELECT `page_modulecomponentid` FROM `pragyanV2_pages` WHERE `page_id`= ".$child['page_id'].")";
				$result = mysql_query($query);
				$values = mysql_fetch_array($result);
				$link=$values[0]."\" target=\"_blank\"";
			}
			if($pId!=$child['page_id'])
				$resultmenu = "<a href=\"$link\"><div class=\"siblingmenuitem menuitem\">".$child['page_title']."</div></a>\n";
			else
				$resultmenu = "<a href=\"$link\"><div class=\"siblingmenuitem menuitem\">$previousImage ".$child['page_title']." $nextImage</div></a>\n";
			array_push($resultmenuarray,$resultmenu);
		}
		if(count($resultmenuarray)==0) 	return "";
		return "<div class=\"siblingmenu\">".$resulthead.join($resultmenuarray)."</div>\n";
	}
 }


 function getChildrenMenu($userid,$pageId) {
	$page_query = "SELECT page_menurank,page_name,page_title FROM ".MYSQL_DATABASE_PREFIX."pages WHERE page_id=".$pageId." ORDER BY `page_menurank` ASC";
	$page_result = mysql_query($page_query);
	$page_row = mysql_fetch_assoc($page_result);

	global $sourceFolder;
	global $urlRequestRoot;
	global $templateFolder;
	$imagesFolder = "$sourceFolder/$templateFolder/".TEMPLATE."/images";
	$previousImageChild = "$imagesFolder/go-previousChild.gif";
	$nextImageChild = "$imagesFolder/go-nextChild.gif";
	if(file_exists($previousImageChild)) $previousImageChild = "<img src=\"$urlRequestRoot/$previousImageChild\" />"; else $previousImageChild = "";
	if(file_exists($nextImageChild)) $nextImageChild = "<img src=\"$urlRequestRoot/$nextImageChild\" />"; else $nextImageChild = "";
	$resulthead="<a href=\"./\"><div class=\"childmenuhead menuhead\">$previousImageChild".$page_row['page_title']." $nextImageChild</div></a>\n";

	$childrenarray = getMenuChildren($userid,$pageId);
	$resultmenuarray = array();
	if(count($childrenarray)==0) 	return "";
	else {
		foreach($childrenarray as $child) {
			if($child['page_module']!='external')
				$link = "./".$child['page_name'];
			else {
				$query = "SELECT `page_extlink` FROM `".MYSQL_DATABASE_PREFIX."external` WHERE `page_modulecomponentid` =
				(SELECT `page_modulecomponentid` FROM `pragyanV2_pages` WHERE `page_id`= ".$child['page_id'].")";
				$result = mysql_query($query);
				$values = mysql_fetch_array($result);
				$link=$values[0]."\" target=\"_blank\"";;
			}
				$resultmenu = "<a href=\"$link\"><div class=\"childmenuitem menuitem\">".$child['page_title']."</div></a>\n";
				array_push($resultmenuarray,$resultmenu);
		}
		if(count($resultmenuarray)==0) 	return "";
		return "<div class=\"childmenu\">".$resulthead.join($resultmenuarray)."</div>\n";
	}
 }

 function getParentSiblingMenu($userid,$pageId) {
	$page_query = "SELECT page_menurank,page_name,page_title FROM ".MYSQL_DATABASE_PREFIX."pages WHERE page_id=".$pageId." ORDER BY `page_menurank` ASC";
	$page_result = mysql_query($page_query);
	$page_row = mysql_fetch_assoc($page_result);

	global $sourceFolder;
	global $urlRequestRoot;
	global $templateFolder;
	$imagesFolder = "$sourceFolder/$templateFolder/".TEMPLATE."/images";
	$previousImageChild = "$imagesFolder/go-previousChild.gif";
	$nextImageChild = "$imagesFolder/go-nextChild.gif";
	if(file_exists($previousImageChild)) $previousImageChild = "<img src=\"$urlRequestRoot/$previousImageChild\" />"; else $previousImageChild = "";
	if(file_exists($nextImageChild)) $nextImageChild = "<img src=\"$urlRequestRoot/$nextImageChild\" />"; else $nextImageChild = "";
	$resulthead="<a href=\"./../../\"><div class=\"parentsiblinghead menuhead\">$previousImageChild".$page_row['page_title']." $nextImageChild</div></a>\n";

	$childrenarray = getMenuChildren($userid,$pageId);
	$resultmenuarray = array();
	if(count($childrenarray)==0) 	return "";
	else {
		foreach($childrenarray as $child) {
			if($child['page_module']!='external')
				$link = "./../../".$child['page_name'];
			else {
				$query = "SELECT `page_extlink` FROM `".MYSQL_DATABASE_PREFIX."external` WHERE `page_modulecomponentid` =
				(SELECT `page_modulecomponentid` FROM `pragyanV2_pages` WHERE `page_id`= ".$child['page_id'].")";
				$result = mysql_query($query);
				$values = mysql_fetch_array($result);
				$link=$values[0]."\" target=\"_blank\"";;
			}
			$resultmenu = "<a href=\"$link\"><div class=\"parentsiblingmenuitem menuitem\">".$child['page_title']."</div></a>\n";
			array_push($resultmenuarray,$resultmenu);
		}
		if(count($resultmenuarray)==0) 	return "";
		return "<div class=\"childmenu\">".$resulthead.join($resultmenuarray)."</div>\n";
	}
 }

/**
 * Find out the children of a pageId
 */
 function getMenuChildren($userid,$pageId) {
	$pageId=getDereferencedPageId($pageId);
	$pagechildren_query = "SELECT page_id,page_name,page_title,page_menurank,page_module FROM ".MYSQL_DATABASE_PREFIX."pages WHERE page_parentid=".$pageId." AND page_displayinmenu=1 AND page_parentid != page_id ORDER BY `page_menurank` ASC";
	$pagechildren_result = mysql_query($pagechildren_query);
	$children=array();
	while($pagechildren_row = mysql_fetch_assoc($pagechildren_result)) {
		if(getPermissions($userid, $pagechildren_row['page_id'], "view"))
			array_push($children,$pagechildren_row);
	}
	return $children;
 }








?>
