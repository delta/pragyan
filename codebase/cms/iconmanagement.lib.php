<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	http_send_status(403);
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}
/**
 * @package pragyan
 * @copyright (c) 2010 Pragyan Team
 * @author boopathi
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
 

function handleIconManagement() {

	/*
	*	Upload a new icon
	*/
	if(isset($_POST['FileUploadForm'])){
		global $userId,$sourceFolder;
		require_once("$sourceFolder/upload.lib.php");
		$allowableTypes = array (
				'jpeg',
				'jpg',
				'png',
				'gif'
			);
		$result = submitFileUploadForm($userId, 'iconman', $userId, false, $allowableTypes, 'iconUpload');
		
	}

	/**
	 * If user is setting an icon to a page, then generate an ajax response
	 */
	if(isset($_GET['iconURL']))
	{
		$rootUri = hostURL();	
		global $cmsFolder,$templateFolder;
		if(isset($_GET["iconURL"]) && isset($_GET['targetId'])) {
			//Security checks
			$iconURL = $_GET["iconURL"];
			$iconURL = str_replace($rootUri, "", $iconURL);
			$parse = strstr($iconURL, "$cmsFolder/$templateFolder/common/icons/");
			if($parse == "")
				$parse = strstr($iconURL, "$cmsFolder/uploads/iconman/");
			$iconURL = $parse;
			$iconURL = escape($iconURL);
			$target = escape($_GET["targetId"]);
	
			mysql_query("UPDATE `".MYSQL_DATABASE_PREFIX."pages` SET `page_image`='$iconURL' WHERE `page_id`='$target'");
			$pageDetails = getPageInfo($target);
			if($pageDetails['page_image'] != NULL)
				echo "<img src=\"$rootUri/$cmsFolder/$templateFolder/common/icons/16x16/status/weather-clear.png\" /> ";
			else
				echo "<img src=\"$rootUri/$cmsFolder/$templateFolder/common/icons/16x16/status/dialog-error.png\" width=12 height=12/> ";
			echo $pageDetails["page_name"];
	
		}
		else if(isset($_GET['iconAction'])) {
			$action = $_GET['iconAction'];
			
		}
		else
		{
			die("Restricted access");
		}
		exit(0);
	}



	$iconForm = "";
	$iconForm .= <<<ICONFORM
		<style type="text/css">
		.myIconForm div {
			padding: 5px;
		}
		.myIconForm div a{
			text-decoration: none!important;
		}	
		</style>
		
ICONFORM;
	//Get data from Database
	global $cmsFolder;
	global $sourceFolder;
	global $templateFolder;
	global $userId;
	$myhostURL = hostURL();
	$iconForm .= "<script type=\"text/javascript\" src=\"$myhostURL/$cmsFolder/$templateFolder/common/scripts/icon.event.handler.js\"></script>";
	$iconForm .= <<<STYLES
		<style type="text/css">
		.myIconForm {
			margin:0;
			padding:0;
		}
		#iconTreeMenu {
			position:relative;
		}
		.myIconForm ul {
			margin: 5px;
			width: 100%;
			margin-left: 10px;
			padding: 0;
			border-left: solid 1px #333;
		}
		.myFormIcon ul li a {
			padding: 5px;
		}
		.myIconList {
			height:500px;
			overflow:scroll;
			max-width:100%;
		}
		</style>
STYLES;
	
	require_once("$sourceFolder/upload.lib.php");
	$fakeid = $userId;

	$imageUploadField = getMultipleFileUploadField('iconUpload','iconman',512*1024);
	
	//$iconForm .= $imageUploadField;
	$uploadForm .= <<<FORM
	<form method="POST" action="./+admin&subaction=icon" enctype="multipart/form-data">
	$imageUploadField
	<input type="submit" />
	</form>
	
FORM;
	
	$iconForm .= "<table class=\"myIconForm\"><tr><td id=\"iconTreeMenu\">";
	$iconForm .= getTreeView(0,-1,$myhostURL,$userId,1);
	$iconForm .= "</td>";
	$iconForm .="<td>";
	$selectionList = getIconList();

	$iconForm .= <<<SELECTION
		<div class="selection" id="targetIcon">
			<h3>Upload new icons</h3>
				<p align="left">
					{$uploadForm}<br/>
					- Select Multiple files
				</p>
			<h3>List of available icons</h3>
			<p align="left">
				Usage : <br />
				- Drag and drop<br />
				- Select an icon and then choose the target.
				</p>
			<div class="selectlist">
				{$selectionList}
			</div>
		</div>
SELECTION;

	$iconForm .="</td>";

	$iconForm .= "</tr></table>";



	return $iconForm;
}
function getTreeView($pageId,$depth,$rootUri,$userId,$curdepth) {
	global $cmsFolder;
	global $templateFolder;
	require_once("menu.lib.php");
  if($depth>0 || $depth==-1) {
  if($curdepth==1 || $pageId==0) $classname="treeRoot";
  else $classname="treeItem";
  $pageRow = getChildren($pageId,$userId);
  $var = "<ul class='{$classname}'>";
  for($i=0;$i<count($pageRow);$i+=1) {
	  $newdepth=$curdepth+1;
	  $var .= "<li><a href=\"./\" class=\"dropme\" onclick=\"return selectItem(event,this)\"";
	  $var .= <<<DROPZONE
	  ondragenter="dragEnterHandler(event)" ondragover="dragOverHandler(event)" ondragleave="dragOutHandler(event)" ondrop="dropHandler(event)" id="p{$pageRow[$i][0]}">  
DROPZONE;
	  if($pageRow[$i][3] != NULL)
	  	$var .= "<img src=\"$rootUri/$cmsFolder/$templateFolder/common/icons/16x16/status/weather-clear.png\" />\n ";
	  else
	  	$var .= "<img src=\"$rootUri/$cmsFolder/$templateFolder/common/icons/16x16/status/dialog-error.png\" width=12 height=12/>\n ";
	  $var .= "{$pageRow[$i][1]}</a>";
	  $var .= getTreeView($pageRow[$i][0],($depth==-1)?$depth:($depth-1),$rootUri,$userId,$newdepth);
	  $var .= "</li>";
	}
  $var .= "</ul>";
  if(count($pageRow)==0) return "";
  return $var;
  }
}

function getIconList() {
	$iconList = "";
	$rootUri = hostURL();
	global $cmsFolder,$sourceFolder;
	global $templateFolder;
	$dir = "$cmsFolder/$templateFolder/common/icons/32x32/";
	$uploaded = "";
	if(is_dir("$sourceFolder/uploads/iconman/")) {
		$uploaded = "$cmsFolder/uploads/iconman/";
	}
	
	//$dir = "$cmsFolder/$templateFolder/trinity/images/events/";
	
	$handle = scandir($dir);
	$iconList .= <<<SCRIPTS
	<script type="text/javascript">
	var rootUri = "{$rootUri}";
	var cmsFolder = "{$cmsFolder}";
	var templateFolder = "{$templateFolder}";
	</script>
SCRIPTS;
	$iconList .= <<<STYLES
	<style type="text/css">
	.dragme{
		float:left;
	}
	.myIconList #noImage {
		width: 30px;
		height: 30px;
		border: solid 1px #000;
	}
	</style>
STYLES;
	$iconList .= "<div class='myIconList'>";
	
	$id=0;
	$iconList .= <<<NONE
		<div class="dragme" draggable="true" ondragstart="dragStartHandler(event,this)" id="noImage" onclick="selectIcon(event,this)">
		<img src="{$rootUri}/{$cmsFolder}/{$templateFolder}/common/images/erase_icon.jpg" width=30 height=30/>
		</div>
NONE;


	if($uploaded != "") {
		$iconList .= <<<HTMl
			<style type="text/css">
				.myUploadedIcons {
					clear: both;
				}
			</style>
			<div class="myUploadedIcons">
			<h3>My Uploads: </h3>
HTMl;
		$iconList .= getListOfFiles($uploaded, true);
		$iconList .= "</div><div class=\"clearer\"></div>";
	}
	
	$iconList .= "<h3>CMS icons</h3>";
	$iconList .= getListOfFiles($dir,true);
	
	
	$iconList .= "</div>";
	return $iconList;
}

/*
* @function "To generate File list given a folder"
* @param $dir Name of the directory : Relative path
* @param $isTopLevel This is to ensure that the $iconList doesnt get emptied when recursion occurs.
*	@usage Always call the function as getListOfFiles(<Directory>, true)
* @author boopathi
*/
function getListOfFiles($dir, $isTopLevel=false) {
	global $iconList;
	if(substr($dir,-1) != '/')
		$dir .= "/";
	$rootUri = hostURL();
	if($isTopLevel)
		$iconList = "";
	if(is_readable($dir)) {
		$handle = scandir($dir);
		foreach($handle as $item) {
			if($item != '.' && $item != '..' && $item[0]!=".") {
				if(is_dir($dir.$item))
					getListOfFiles($dir.$item);
				else {
					if(is_readable($dir.$item)) {
						$type = explode("/",mime_content_type($dir.$item));
						if($type[0] == "image") {
							$iconList .= "<div class=\"dragme\" draggable=\"true\" ondragstart=\"dragStartHandler(event,this)\" onclick=\"selectIcon(event,this)\">";
							$iconList .= "<img title='$item' alt='$item' src='{$rootUri}/{$dir}{$item}' width=32 height=32 /></div>\n";
						}
					}
				}
			}
		}
	}
	return $iconList;
}


?>
