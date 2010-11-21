<?php
/**
 * @package pragyan
 * @copyright (c) 2010 Pragyan Team
 * @author boopathi
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
 

function handleIconManagement() {
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
		<fieldset>
		<legend>Icon Management</legend>
ICONFORM;
	//Get data from Database
	global $cmsFolder;
	global $templateFolder;
	global $userId;
	$myhostURL = hostURL();
	$iconForm .= "<script type=\"text/javascript\" src=\"$myhostURL/$cmsFolder/iconmanagement/icon.event.handler.js\"></script>";
	$iconForm .= <<<STYLES
		<style type="text/css">
		.myIconForm {
			margin:0;
			padding:0;
		}
		.myIconForm ul {
			margin: 5px;
			margin-left: 10px;
			padding: 0;
			border-left: solid 1px #DDD;
		}
		.myFormIcon ul li a {
			padding: 5px;
		}
		</style>
STYLES;
	$iconForm .= "<table class=\"myIconForm\"><tr><td>";
	$iconForm .= getTreeView(0,-1,$myhostURL,$userId,1);
	$iconForm .= "</td>";
	$iconForm .="<td>";
	$selectionList = getIconList();

	$iconForm .= <<<SELECTION
		<div class="selection" id="targetIcon">
				<p align="right">Drag and Drop the Icons over the menu.</p>
			<h3>List of available icons</h3>
			<div class="selectlist">
				{$selectionList}
			</div>
		</div>
SELECTION;

	$iconForm .="</td>";

	$iconForm .= "</tr></table>";

	$iconForm .= "</fieldset>";

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
	  $var .= "<li><a href=\"./\" class=\"dropme\"";
	  $var .= <<<DROPZONE
	  ondragenter="dragEnterHandler(event)" ondragover="dragOverHandler(event)" ondragleave="dragOutHandler(event)" ondrop="dropHandler(event)" id="p{$pageRow[$i][0]}">  
DROPZONE;
	  if($pageRow[$i][3] != NULL)
	  	$var .= "<img src=\"$rootUri/$cmsFolder/$templateFolder/common/icons/16x16/status/weather-clear.png\" /> ";
	  else
	  	$var .= "<img src=\"$rootUri/$cmsFolder/$templateFolder/common/icons/16x16/status/dialog-error.png\" width=12 height=12/> ";
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
	global $cmsFolder;
	global $templateFolder;
	$dir = "$cmsFolder/$templateFolder/common/icons/32x32/";
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
	</style>
STYLES;
	$iconList .= "<div class='myIconList' style='height:300px;overflow:scroll;max-width:100%;'>";
	foreach($handle as $item) {
		if($item != '.' && $item != '..' && $item[0]!="." ) {
			if(is_dir($dir.$item)) {
				$h = scandir($dir.$item);
				foreach($h as $i)
					if($i != "." && $i != ".." && $i[0] != ".")
						$iconList .= "<div class=\"dragme\" id=\"d$i\" draggable=\"true\" ondragstart=\"dragStartHandler(event,this)\"><img src='{$rootUri}/{$dir}{$item}/{$i}' width=32 height=32/></div>";
			}
			else {
			$iconList .= $item;
			$iconList .= "<img src='{$rootUri}/{$dir}{$item}' />";
			}
		}
	}
	$iconList .= "</div>";
	return $iconList;
}

?>
