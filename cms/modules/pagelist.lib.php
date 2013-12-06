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
 * @copyright (c) 2008 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 * @description
 * What it does:
 * Creates a page of type pagelist as a sibling of the pages you want to list 
 * i.e. if you want to list all the sub pages of a parent page named "home" ,create a page of type pagelist as a child page of 'home'. It
 * will   list all the child pages of page 'home'.
 * 2) The depth, upto which the pagelist should penetrate(list) the pages, can be adjusted by using 'edit' action of the pagelist. By setting the 
 * depth value as 3, 		the no. of sub pages of a particular page shown in the list becomes 3. (The Default value of depth set is 3 which 
 * can be changed by changing the file 'pagelist.lib.php' at line 249 currently) 
 * 3) By clicking on the go-icon (a skip forward image), the other pages are gone and you are left with a new pagelist with root as the page whose 
 * go-icon (skip forward image) was clicked. 
 * 4) The image and page name after go-icon(skip-forward image) let you go to respective page at any time (by clicking the link, :) ). 
 */
 
class pagelist implements module {

	public function getHtml($userId, $moduleComponentId, $action) {
		$this->userId = $userId;
		$this->moduleComponentId = $moduleComponentId;
		$this->action = $action;

		if($action == "view")
			return $this->actionView();
	 	if ($this->action == "edit")
			return $this->actionEdit();
	}

	public function actionView() {
   		global $sourceFolder; 
		require_once("$sourceFolder/common.lib.php");
		$pageid = getPageIdFromModuleComponentId("pagelist",$this->moduleComponentId);
		$pageid=getParentPage($pageid);
		$query = "SELECT `depth` FROM `list_prop` WHERE `page_modulecomponentid`='$this->moduleComponentId'";
		$result = mysql_query($query) or die(mysql_error());
		$row = mysql_fetch_assoc($result);
		$reqdepth=$row['depth'];
		$out=$this->generatePagelist($pageid, $this->userId, 0, 'view',$reqdepth+1);
		return $out;
	}
	function generatePagelist($pageId, $userId, $permId, $action = '',$depth) {
		global $cmsFolder, $urlRequestRoot ,$templateFolder;
		$treeData= '<div><ul id="sitemap">' ;
		if($action != '') {
			$treeData .= $this->getNodeHtmlforPagelist($pageId, $userId, '', $action, $urlRequestRoot.'/', $depth);
		}
		
		else {
			$permQuery = 'SELECT `page_module`, `perm_action` FROM `' . MYSQL_DATABASE_PREFIX . 'permissionlist` WHERE `perm_id` = \'' . $permId."'";
			$permResult = mysql_query($permQuery);
			$permRow = mysql_fetch_row($permResult);
			$module = $permRow[0];
			$action = $permRow[1];
			$treeData .= $this->getNodeHtmlforPagelist($pageId, $userId, $module, $action, $urlRequestRoot . '/', $depth);
		}
		
		$treeData .= '</ul></div>';
		return $treeData;
	}

	function getNodeHtmlforPagelist($pageId, $userId, $module, $action, $parentPath, $depth)
	 {
		global $cmsFolder, $urlRequestRoot ,$templateFolder;
		$tempFolder="$urlRequestRoot/$cmsFolder/$templateFolder";
		$imagesFolder = "$tempFolder/common/icons/32x32";
		$imagesFolder2 = "$tempFolder/common/images/pagethumbs";
		
		$goimage="$tempFolder/common/icons/16x16/actions/media-skip-forward.png";
		if($depth!=0){ 
		$htmlOut = '';
		
		if(getPermissions($userId, $pageId, $action, $module)) {
		
		if(isset($_POST['hell']))
		{ 		
				$pageId=escape($_POST['hell']);
			 	unset($_POST['hell']);
			  	$htmlOut.=$this->generatePagelist($pageId, $userId, $permId, $action = '',$depth);
			
		}
		else
		{
			$pageInfo = getPageInfo($pageId);
			if(isset($_POST['hell2']))
			{ 
				$pagePath=escape($_POST['hell2']);
				unset($_POST['hell2']);

			}
			else
			{ 
				$pagePath = $parentPath;
				if($pageInfo['page_name']!= '')
					$pagePath.=$pageInfo['page_name'].'/'; 
			}
			$pagename=$pageInfo['page_name'];
			
			$htmlOut .= "<li><form method ='POST' action='./'><input type='image' src=\"$goimage\" name='pagename' alt='Go' title='Click to list pages from here'><input type='hidden' name='hell' value='$pageId' /><input type='hidden' name='hell2' value='$pagePath' /><a href=\"$pagePath\">";
/** **************************************************************************************************************************************************************
		The following lines are for thumb images of each page listed in the page of type pagelist :
		By Default: the home icon is set as default thumb image for each page. This can be changed by doing following actions:
		a) Create a folder called 'pagethumbs' in folder '/cms/templates/common' 
		b) put all the images (size preferably 32x32 ) with the name same as the name of the page.
			e.g. for a page whose name is 'hello' in table _pages the name of the image in the above said folder should be 'hello.png'
		c) Add comment symbol i.e. // in front of line saying : $thumbname="$imagesFolder/actions/go-home.png"; (currently it is line 159 if not changed)
						THAT'S IT 
************************************************************************************************************************************************************* */
			
			$thumbname="$imagesFolder/actions/go-home.png";
			
			$htmlOut.="<span class='list'><img src='$thumbname' alt=' !sorry! '>" . getPageTitle($pageId) . "</span></a>\n</form>";

			$childrenQuery = 'SELECT `page_id`, `page_displayinmenu` FROM `' . MYSQL_DATABASE_PREFIX  . 'pages` WHERE `page_parentid` <> `page_id` AND `page_parentid` = ' . $pageId;
			$childrenResult = mysql_query($childrenQuery);
			$childrenHtml = '';
			while($childrenRow = mysql_fetch_row($childrenResult)) {
				if($childrenRow[1] == 1&&$depth!=0) {
					$childrenHtml .= $this->getNodeHtmlforPagelist($childrenRow[0], $userId, $module, $action, $pagePath, $depth-1);
				
				}
			}
			if($childrenHtml != '') {
				$htmlOut .= "<ul>$childrenHtml</ul>\n";
			}

			$htmlOut .= "</li>\n";
			}
		}
		return $htmlOut;
	}
	}

	public function actionEdit() {
	
		if(isset($_POST['depth'])) 
		{ 		
			$query = "UPDATE `list_prop` SET `depth`='".escape($_POST['depth'])."' WHERE `page_modulecomponentid`='$this->moduleComponentId'";
			$result = mysql_query($query);
			if (mysql_affected_rows()) 	 
				$ret.="<div class='cms-info'>Depth value updated.</div>";
			else $ret.="<div class='cms-info'>ok. updated. (Its already set)</div>";	 
		}	

		$query = "SELECT `depth` FROM `list_prop` WHERE `page_modulecomponentid`='$this->moduleComponentId'";
		$result = mysql_query($query) or die(mysql_error());
		$row = mysql_fetch_assoc($result);
		
		$ret.="<form action='./+edit&subaction=submit' method=POST>
<table>
<caption>Edit the Depth of pagelisting:</caption>
<tr>
<td>Enter the Depth to be set:</td>
<td><input type='text' name='depth' value={$row['depth']}></td>
</tr>
<tr>
<td><input type='submit' value='Submit' name'submitdepth'></td>
</tr>
</table>
</form>";

		return $ret;
	}
	 	
	public function createModule($compId) {
		
		$defaultdepth=3;
		$query = "INSERT INTO `list_prop` (`page_modulecomponentid`, `depth`) VALUES ('$compId', '$defaultdepth')";
		$result = mysql_query($query) or die(mysql_error());
	}
	
	
	public function deleteModule($moduleComponentId) {
		/*
		 * This is also a necessary function
		 * it'll be called when an instance of your module is going to get deleted
		 * you can do your clean up works for the module instance here
		 * return true in case of successful deletion, else false
		 */
		 return true;
	}
	
	public function copyModule($moduleComponentId,$newId) {
		/*
		 * This is also a necessary function
		 * it'll be called when a module is to be copied
		 * return true when copied successfully, else false
		 */
		 return true;
	}
}
?>
