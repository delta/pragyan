<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}
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

/**
 * Now $COMPLETEMENU AND $MENUBAR mean the same in /index.php
 * 3rd type of menu added in database - completemenu
 */

/**
 * Function getMenu
 * @param userId user Id of the current user logged in.
 * @param pageIdArray generate menu for the request page.
 * @return HTML element of the menu. An unordered list.
 **/
function getMenu($userId, $pageIdArray) {


	///This hostURL is to replace all ".(dot)s" with the current address, making the link absolute.	
	///@functions hostURL() common.lib.php - http://pragyan.org/11
	///@functions selfURI() common.lib.php - http://pragyan.org/11/home/how_to_use/mypage/mypage2
	
	$hostURL = ".";
	$pageId = $pageIdArray[count($pageIdArray) - 1];
	$hostURL = hostURL();
	
	/// A duplicate entry just a fix for changing $hostURL.
	$MYHOST = hostURL();
	
	/// Returns an array
	$pageRow = getPageInfo($pageId);
	$depth = $pageRow['page_menudepth'];
	
	/// Default depth is 1 - Should always show atleast one level of sub menu to enable navigation within site.
	if ($depth == 0) $depth=1;

	if ($pageRow['page_displaymenu'] == 0)
		return '';
	$menutype=$pageRow['page_menutype'];
	
	$menuHtml = "";
	
	/// Classic Menu type - No branches are displayed in this type
	if($menutype=="classic")
	{
		$pageId = $pageIdArray[count($pageIdArray) - 1];
		$depth = 1;
		$hostURL = strstr(selfURI(), '+', true);
		
		/// Get parent page details
		$parentPage = getParentPage($pageId);
		$parentPageRow = getPageInfo($parentPage);
		
		$childListGenerated = getChildList($pageId, $depth, hostURL(), $userId, 1);
		
		/// Display the sibling menu of the same depth
		if($pageRow['page_displaysiblingmenu']) {
			if($pageId != 0) {
				$imageTag = "";
				if($parentPageRow['page_displayicon'] == 1 && $parentPageRow['page_image'] != NULL) {
					/// Icon for the menu if present
					$imageTag = "<img width=32 height=32 src=\"{$MYHOST}/{$parentPageRow['page_image']}\" alt=\"{$parentPageRow['page_image']}\" />";
	  		}
				$menuHtml .= '<a href="'.$hostURL.'../"><div class="cms-menuhead">'.$imageTag.$parentPageRow["page_title"].'</div></a>';
				$siblingMenu = getChildList($parentPage,1,hostURL(),$userId,1);
				$menuHtml .= $siblingMenu;
			}
		}
		
		/// If nothing is generated, then display atleast one item - the current Menu
		if($pageRow['page_displaysiblingmenu']==0 && $childListGenerated == null) {
			$imageTag = "";
			$pageR = getPageInfo($pageId);
			if($pageR['page_displayicon'] == 1) {
				if($pageR['page_image'] != NULL)
					$imageTag = "<img width=32 height=32 src=\"{$MYHOST}/{$pageR['page_image']}\" alt=\"{$pageR['page_image']}\" />";
	  	}
			$menuHtml .= <<<MENU
				<ul class="topnav">
				<li>
					<a href="./"><div class="cms-menuitem">{$imageTag} {$pageRow['page_title']}</div></a>
				</li>
				</ul>
MENU;
		}
		
		///If the childList is generated, then display the child items
		if($childListGenerated != "") {
			$imageTag = "";
				if($pageRow['page_displayicon'] == 1 && $pageRow['page_image'] != NULL) {
					$imageTag = "<img width=32 height=32 src=\"{$MYHOST}/{$pageRow['page_image']}\" alt=\"{$pageRow['page_image']}\" />";
	  	}
			$menuHtml .= '<a href="'.$hostURL.'"><div class="cms-menuhead">'.$imageTag.$pageRow["page_title"].'</div></a>';
			$menuHtml .= $childListGenerated;
		}
	}
	else
	{
		///Multi Depth Menu Code starts from here
		if($menutype == "multidepth") {
		$pageId = $pageIdArray[count($pageIdArray) - 1];
		}
		else {
			$pageId = 0;
		}
	
		$rootUri = hostURL();
		///Get page information through pageId		
		$pageRow = getPageInfo($pageId);
			
		///Get the Children of the page.
		$childListGenerated = getChildList($pageId,$depth,$rootUri,$userId,1);
		if($childListGenerated != "")
			$menuHtml .= $childListGenerated;
		///If nothing generated, then display default - The current page name
		else {
			$imageTag = "";
			$pageR = getPageInfo($pageId);
			if($pageR['page_displayicon'] == 1) {
				if($pageR['page_image'] != NULL)
					$imageTag = "<img width=32 height=32 src=\"{$rootUri}/{$pageR['page_image']}\" alt=\"{$pageR['page_image']}\" />";
	  	}
			$menuHtml .= <<<MENU
				<ul class="topnav">
				<li>
					<a href="./"><div class="cms-menuitem">{$imageTag} {$pageRow['page_title']}</div></a>
				</li>
				</ul>
MENU;
		}
	}
	
	// return the final generated HTML
	return $menuHtml;

}

/**
 * Get the ChildList for the current item in menu.
 * @param $pageId the page Id of the current Page
 * @param $depth The number of levels of menu that should be generated
 * @param $userId The viewers id - To check for permissions.
 * @return Associative Array containing the child pages informaion.;
*/

function getChildList($pageId,$depth,$rootUri,$userId,$curdepth) {
  if($depth>0 || $depth==-1) {
  if($curdepth==1 || $pageId==0) $classname="topnav";
  else $classname="subnav";
  $MYHOST = hostURL();
  $pageRow = getChildren($pageId,$userId);

  $var = "<div class='div_{$classname}'><ul class='{$classname} depth{$curdepth}'>";
  for($i=0;$i<count($pageRow);$i+=1) {
  	$query = "SELECT `page_openinnewtab` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_id` = '{$pageRow[$i][0]}'";
		$result = mysql_query($query);
		$result = mysql_fetch_assoc($result);
		$opennewtab="";
		if($result['page_openinnewtab']=='1') 
			$opennewtab = ' target="_blank" ';
		
	  $newdepth=$curdepth+1;
	  $imageTag = '';
	  if($pageRow[$i][4]) {
	  	if($pageRow[$i][3] != NULL)
	  		$imageTag = "<img width=32 height=32 src=\"{$MYHOST}/{$pageRow[$i][3]}\" alt=\"{$pageRow[$i][1]}\" />";
	  	/*
	  	 *@usage: display a default folder icon if the table value is NULL
	  	 *@code:
	  	  else {
	  		global $cmsFolder;
	  		global $templateFolder;
	  		$imageTag = "<img src=\"{$hostt}/$cmsFolder/$templateFolder/common/images/folder.png\" alt=\"{$pageRow[$i][1]}\" width=\"16\" height=\"16\"/>";
	  	}*/
	  }
	  $var .= "\n<li id=\"cms-menu-item$i\"><a href=\"".$rootUri.'/home'.getPagePath($pageRow[$i][0])."\" $opennewtab ><div class='cms-menuitem'>".$imageTag." ".$pageRow[$i][2]."</div></a>";
	  $var .= getChildList($pageRow[$i][0],($depth==-1)?$depth:($depth-1),$rootUri,$userId,$newdepth,true);
	  $var .= "</li>";
	}
  $var .= "</ul></div>";
  if(count($pageRow)==0) return "";
  return $var;
  }
}
function htmlMenuRenderer($menuArray, $currentIndex = -1, $linkPrefix = '') {
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
		if (($menuArray[$i][4]) && ($menuArray[$i][3] != ''))
			$menuHtml .= "<img src=\"{$menuArray[$i][3]}\" width=32 height=32 />";
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
 * @return Array of arrays of page id, page name, page title, large image and small image
 */
function getChildren($pageId, $userId) {
	$pageId=escape($pageId);
	$childrenQuery = 'SELECT `page_id`, `page_name`, `page_title`, `page_module`, `page_modulecomponentid`, `page_displayinmenu`, `page_image` , `page_displayicon` FROM `' . MYSQL_DATABASE_PREFIX . 'pages` WHERE `page_parentid` = \'' . $pageId . '\' AND `page_id` != \'' . $pageId . '\' AND `page_displayinmenu` = 1 ORDER BY `page_menurank`';
	
	$childrenResult = mysql_query($childrenQuery);
	$children = array();
	while ($childrenRow = mysql_fetch_assoc($childrenResult))
		if ($childrenRow['page_displayinmenu'] == true && getPermissions($userId, $childrenRow['page_id'], 'view', $childrenRow['page_module']) == true)
			$children[] = array($childrenRow['page_id'], $childrenRow['page_name'], $childrenRow['page_title'], $childrenRow['page_image'],$childrenRow['page_displayicon']);
			
		
	return $children;
}

?>
