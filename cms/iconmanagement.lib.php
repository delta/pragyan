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
 * @author boopathi
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
 

/**
 * Function handleIconManagement 
 * @description Returns the Icon Admin page html and handles AJAX requests for page /+admin$subaction=i
 * 
 * @return HTML of the FORM
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
		
			///Security checks
			$iconURL = $_GET["iconURL"];
			$iconURL = str_replace($rootUri, "", $iconURL);
			$parse = strstr($iconURL, "$cmsFolder/$templateFolder/common/icons/");
			if($parse == "")
				$parse = strstr($iconURL, "$cmsFolder/uploads/iconman/");
			$iconURL = $parse;
			$iconURL = escape($iconURL);
			$target = escape($_GET["targetId"]);
			
			/**
			 * Save the Icon in Database - The following entries are saved
			 * icon URL - path relative to the website installation folder on the server
			 */
			mysql_query("UPDATE `".MYSQL_DATABASE_PREFIX."pages` SET `page_image`='$iconURL' WHERE `page_id`='$target'");
			$pageDetails = getPageInfo($target);
			if($pageDetails['page_image'] != NULL)
				echo "<img src=\"$rootUri/$cmsFolder/$templateFolder/common/icons/16x16/status/weather-clear.png\" /> ";
			else
				echo "<img src=\"$rootUri/$cmsFolder/$templateFolder/common/icons/16x16/status/dialog-error.png\" width=12 height=12/> ";
			echo $pageDetails["page_name"];
	
		}
		
		/**
		 * Handler for icon subaction.
		 * TODO: implement icon size variations, icon resize Options, 
		 */
		else if(isset($_GET['iconAction'])) {
			$action = $_GET['iconAction'];
			
		}
		///Security Check
		else
		{
			die("Restricted access");
		}
		exit(0);
	}


	/**
	 * @description Icon Management Form Generation Code Starts here
	 */
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

	global $cmsFolder;
	global $sourceFolder;
	global $templateFolder;
	global $userId;
	$myhostURL = hostURL();
	
	///Ajax handler functions, drag and drop handlers defined in icon.event.handler.js
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
	
	///This contains file upload functions of CMS. Look into upload.lib.php documentation for more
	require_once("$sourceFolder/upload.lib.php");
	
	///Just a duplicate copy for sending it to the upload functions.
	$fakeid = $userId;
	
	///get the html for upload - input tag
	$imageUploadField = getMultipleFileUploadField('iconUpload','iconman',512*1024);
	
	//$iconForm .= $imageUploadField;
	$uploadForm = <<<FORM
	<form method="POST" action="./+admin&subaction=icon" enctype="multipart/form-data">
	$imageUploadField
	<input type="submit" />
	</form>
	
FORM;
	
	///Display Icons
	$iconForm .= "<table class=\"myIconForm\"><tr><td id=\"iconTreeMenu\">";
	
	///Fetch the site's complete tree structure of pages. 
	///The elements here are the ones on which icons are dropped.
	$iconForm .= getTreeView(0,-1,$myhostURL,$userId,1);
	$iconForm .= "</td>";
	$iconForm .="<td>";
	
	///Fetch Icon file list and get as html
	$selectionList = getIconList();

	///Gather the html and append the iconform html
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

/**
 * Function getTreeView
 * 
 * @description Similar to menu generation code. It recursively fetches pages according to the sitemap, and generates a ul list with drop handlers defined.
 *
 * @param pageId The current Page the function is operation on
 * @param depth depth of the child list to be fetched at each level. Here it is always -1 to fetch till the last element is reached
 * @param rootUri here it is /. Look into Menu documentation for implementation of the same elsewhere.
 * @param userId This is just to check the permission.
 * @param curdepth Current Depth of the recursion
 *
 * @return HTML - UL list of the tree structure of pages
 */
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

/**
 * Function getIconList
 * @description Get the complete list of icons using getListOfFiles - integrate it with HTML and assign drag handlers, and click handlers
 * @return html of the icon list categorized
 */
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
				.cms-common-icons {
					clear: both;
				}
			</style>
			<div class="myUploadedIcons">
			<h3>My Uploads: </h3>
HTMl;
		$iconList .= getListOfFiles($uploaded, true);
		$iconList .= "</div><div class=\"clearer\"></div>";
	}
	
	$iconList .= "<div class=\"cms-common-icons\"><h3>CMS icons</h3>";
	$iconList .= getListOfFiles($dir,true);
	$iconList .= "</div>";
	
	$iconList .= "</div>";
	return $iconList;
}

/**
* Function getListOfFiles
* @description "To generate File list given a folder"
* @param dir Name of the directory : Relative path
* @param isTopLevel This is to ensure that the $iconList doesnt get emptied when recursion occurs.
* @usage Always call the function as getListOfFiles(<Directory>, true)
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

/**
 * Function mime_content_type
 * @description This is depricated in the latest PHP version. So redefining the same.
 */
if(!function_exists('mime_content_type')) {

    function mime_content_type($filename) {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.',$filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
        else {
            return 'application/octet-stream';
        }
    }
}

?>
