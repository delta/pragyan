<?php
/**
 * @package pragyan
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

/*
 * DONE : Add a parent sibling menu which will be displayed in place of child menu if there are no children (or no
 *	permissions to view children)
 * DONE : dont show anything in child menu in case the user has permission to 0 child pages.
 * In its  place show parent sibling menu.
 *
 */

function findMenuIndex($menuArray, $pageId) {
	for ($i = 0; $i < count($menuArray); ++$i)
		if ($menuArray[$i][0] == $pageId)
			return $i;
	return -1;
}

///The third parameter indicates whether menu is obtained from / or the current page.
///true --> generate from / till depth
///false --> generate from current page till depth relatively.

function getMenu($userId, $pageIdArray, $complete = false, $image = false) {

	
	///This hostURL is to replace all ".(dot)s" with the current address, making the link absolute.	
	///@functions hostURL() common.lib.php - http://pragyan.org/11
	///@functions selfURI() common.lib.php - http://pragyan.org/11/home/how_to_use/mypage/mypage2
	$hostURL = ".";
	if(!$complete) {
		$pageId = $pageIdArray[count($pageIdArray) - 1];
		$hostURL=strstr(selfURI(),'+',true);
	}
	else {
		$pageId = 0;
		$hostURL = hostURL() . "/home";

	}
	$pageRow = getPageInfo($pageId);
	$depth = $pageRow['page_menudepth'];
	if ($depth == 0) $depth=1;
	if ($pageRow['page_displaymenu'] == 0)
		return '';
	$menutype=$pageRow['page_menutype'];
	
	$menuHtml = "";
	
	if($menutype=="classic")
	{
		$menuHtml =<<<MENUHTML
		<div id="menubar">
			<div id="menubarcontent">
MENUHTML;
		$childMenu = getChildren($pageId, $userId);

		///@note Not sure why $pageId = 0 ? Is this even correct ? $pageId is 0 when $complete=true, but then its only for drop-down style menu and not for classic style. But this code is within the classic section.
		///@reply This is because $COMPLETEMENU is called in the index.php. So the pageid is set to 0. Just check the lines of code above. I ll change this soon and delete this note. @author: BOOPATHI
		if ($pageId == 0) { 
			$menuHtml .= '<a href="'.$hostURL.'"><div class="cms-menuhead">' .  $pageRow['page_title'] . '</div></a>';
			$menuHtml .= htmlMenuRenderer($childMenu,-1,'',true);
		}
		else  {
			if ($pageRow['page_displaysiblingmenu']) {
				$siblingMenu = getChildren($pageIdArray[count($pageIdArray) - 2], $userId);
				$parentPageRow = getPageInfo($pageIdArray[count($pageIdArray) - 2]);
				$menuHtml .= '<a href="'.$hostURL.'../"><div class="cms-menuhead">' . $parentPageRow['page_title'] . '</div></a>';
				$menuHtml .= htmlMenuRenderer($siblingMenu, findMenuIndex($siblingMenu, $pageId), '../');
			}
			if (count($childMenu) > 0)
			{
				$menuHtml .= '<a href="'.$hostURL.'"><div class="cms-menuhead">' . $pageRow['page_title'] . '</div></a>';
				$menuHtml .= htmlMenuRenderer($childMenu);
			}
		}

		$menuHtml .= '</div></div>';
		
	}
	else
	{

		$rootUri = hostURL();
		
		$pageId = ($pageId!=0)?getParentPage($pageId):$pageId;
		
		$pageRow = getPageInfo($pageId);
			
		$menuHtml .= getChildList($pageId,$depth,$rootUri,$userId,1,$image);
				
	}
	return $menuHtml;

}

function getChildList($pageId,$depth,$rootUri,$userId,$curdepth,$image=false) {
  if($depth>0 || $depth==-1) {
  if($curdepth==1 || $pageId==0) $classname="topnav";
  else $classname="subnav";
  
  $pageRow = getChildren($pageId,$userId);
  $var = "<ul class='{$classname} depth{$curdepth}'>";
  for($i=0;$i<count($pageRow);$i+=1) {
  	$query = "SELECT `page_openinnewtab` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_id` = '{$pageRow[$i][0]}'";
		$result = mysql_query($query);
		$result = mysql_fetch_assoc($result);
		$opennewtab="";
		if($result['page_openinnewtab']=='1') 
			$opennewtab = ' target="_blank" ';
		
	  $newdepth=$curdepth+1;
	  $imageTag = '';
	  if($image) {
	  	if($pageRow[$i][3] != NULL)
	  		$imageTag = "<img width=32 height=32 src=\"{$pageRow[$i][3]}\" alt=\"{$pageRow[$i][1]}\" />";
	  	/*
	  	 *@usage: display a default folder icon if the table value is NULL
	  	 *@code:
	  	  else {
	  		global $cmsFolder;
	  		global $templateFolder;
	  		$imageTag = "<img src=\"{$hostt}/$cmsFolder/$templateFolder/common/images/folder.png\" alt=\"{$pageRow[$i][1]}\" width=\"16\" height=\"16\"/>";
	  	}*/
	  }
	  $var .= "\n<li><a href=\"".$rootUri.'/home'.getPagePath($pageRow[$i][0])."\" $opennewtab ><div class='cms-menuitem'>".$imageTag." ".$pageRow[$i][2]."</div></a>";
	  $var .= getChildList($pageRow[$i][0],($depth==-1)?$depth:($depth-1),$rootUri,$userId,$newdepth,true);
	  $var .= "</li>";
	}
  $var .= "</ul>";
  if(count($pageRow)==0) return "";
  return $var;
  }
}
function htmlMenuRenderer($menuArray, $currentIndex = -1, $linkPrefix = '', $image=false) {
	$menuHtml = '';
	$hostURL=strstr(selfURI(),'+',true);
	
	for ($i = 0; $i < count($menuArray); ++$i) {
			$query = "SELECT `page_openinnewtab` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_id` = '{$menuArray[$i][0]}'";
			$result = mysql_query($query);
			$result = mysql_fetch_assoc($result);
			
			if($result['page_openinnewtab']=='1') {
				$menuHtml .= "<a href=\"".$hostURL."{$linkPrefix}{$menuArray[$i][1]}/\" target=\"_blank\"";
			}
			else {
		$menuHtml .= "<a href=\"".$hostURL."{$linkPrefix}{$menuArray[$i][1]}/\"";
			}
		if ($i == $currentIndex) 
			$menuHtml .= ' class="currentpage"';
		$menuHtml .= '>';
		if ($image)
			$menuHtml .= "<img src=\"{$menuArray[$i][4]}\" />";
		$menuHtml .= "<div class='cms-menuitem'> {$menuArray[$i][2]} </div></a>\n";
	}
	

	return $menuHtml;
}

function imageMenuRenderer($menuArray, $currentIndex = -1, $linkPrefix = '') {
	$menuRows = array();
	$rowCount = -1;
	for ($i = 0; $i < count($menuArray); ++$i) {
		if ($i % 3 == 0) {
			if ($rowCount >= 0)
				$menuRows[$rowCount] .= '</div>';
			$menuRows[++$rowCount] = '<div class="menuitemrow">';
		}
		$menuRows[$rowCount] .= '<a href="' . $linkPrefix . $menuArray[$i][1] . '"><img src="' . $menuArray[$i][4] . '" alt="' . $menuArray[$i][2] . '"';
		if ($i == $currentIndex)
			$menuRows[$rowCount] .= ' class="currentpage"';
		$menuRows[$rowCount] .= ' /></a>';
	}

	if (count($menuRows))
		$menuRows[count($menuRows) - 1] .= "</div>";

	$menuHtml = '';
	for ($i = 0; $i < count($menuRows); ++$i) {
		if ($i % 3 == 0)
			$menuHtml .= '<div class="menuitemdescription">&nbsp;</div>';
		$menuHtml .= $menuRows[$i];
	}

	return $menuHtml;
}

/**
 * @return array Array of arrays of page id, page name, page title, large image and small image
 */
function getChildren($pageId, $userId) {
	$pageId=escape($pageId);
	$childrenQuery = 'SELECT `page_id`, `page_name`, `page_title`, `page_module`, `page_modulecomponentid`, `page_displayinmenu`, `page_image` FROM `' . MYSQL_DATABASE_PREFIX . 'pages` WHERE `page_parentid` = ' . $pageId . ' AND `page_id` != ' . $pageId . ' AND `page_displayinmenu` = 1 ORDER BY `page_menurank`';
	
	$childrenResult = mysql_query($childrenQuery);
	$children = array();
	while ($childrenRow = mysql_fetch_assoc($childrenResult))
		if ($childrenRow['page_displayinmenu'] == true && getPermissions($userId, $childrenRow['page_id'], 'view', $childrenRow['page_module']) == true)
			$children[] = array($childrenRow['page_id'], $childrenRow['page_name'], $childrenRow['page_title'], $childrenRow['page_image']);
			
		
	return $children;
}

?>
