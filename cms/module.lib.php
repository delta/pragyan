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
 * @author Chakradar Raju
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

function processUploaded($type) {
	global $sourceFolder;
	if(!file_exists($sourceFolder . "/uploads/{$type}/"))
		mkdir($sourceFolder . "/uploads/{$type}/");
	$zipFile = $sourceFolder ."/uploads/{$type}/".$_FILES['file']['name'];
	$ext = extension($zipFile);
	while(file_exists($zipFile))
		$zipFile = $sourceFolder . "/uploads/{$type}/" . rand() . $ext;
	move_uploaded_file($_FILES['file']['tmp_name'],$zipFile);

	$len = strlen($zipFile);
	$moduleName = name($zipFile,".");
	if(substr($zipFile,$len-4,4)==".zip") {
		$zip = new ZipArchive();
		if ($zip->open($zipFile) === TRUE) {
			$extractedPath = $sourceFolder . "/uploads/{$type}/" . $moduleName . "/";
			while(file_exists($extractedPath))
				$extractedPath = $sourceFolder . "/uploads/{$type}/". rand() . "/";
			$zip->extractTo($extractedPath);
			$zip->close();
		} else {
			displayerror("Error while opening archive");
			unlink($zipFile);
			return -1;
		}
	} else {
		displayinfo("Please upload a ZIP file");
		unlink($zipFile);
		return -1;
	}
	$function = "actual{$type}Path";
	$moduleActualPath = $function($extractedPath);
	
	if($moduleActualPath != NULL) {
		$function = "get{$type}Name";
		$moduleName = $function($moduleActualPath);
		if($type=="Module") {
			$colName = "module_name";
			$tableName = "modules";
		} else if($type=="Widget") {
			$colName = "widget_foldername";
			$tableName = "widgetsinfo";
		} else if($type=="Template") {
			$colName = "template_name";
			$tableName = "templates";
		}
		if(mysql_fetch_array(mysql_query("SELECT `{$colName}` FROM `".MYSQL_DATABASE_PREFIX."{$tableName}` WHERE `{$colName}` = '{$moduleName}'"))) {
			displayerror("A {$type} with name '{$moduleName}' already exist, Installation aborted");
			delDir($extractedPath);
			unlink($zipFile);
			return -1;
		}
		mysql_query("INSERT INTO `" . MYSQL_DATABASE_PREFIX . "tempuploads`(`filePath`,`info`) VALUES('{$zipFile}','{$extractedPath};{$moduleActualPath};{$moduleName}')");
		$result = mysql_fetch_assoc(mysql_query("SELECT `id` FROM `" . MYSQL_DATABASE_PREFIX . "tempuploads` WHERE `filePath` = '{$zipFile}'"));
		return $result['id'];
	}
	
	displayerror("{$type} file not found");
	delDir($extractedPath);
	unlink($zipFile);
	
	return -1;
}

function finalizeInstallation($uploadId,$type) {
	global $sourceFolder, $widgetFolder, $templateFolder;
	$result = mysql_fetch_assoc(mysql_query("SELECT * FROM `" . MYSQL_DATABASE_PREFIX. "tempuploads` WHERE `id` = '{$uploadId}'"));
	if($result != NULL) {
		$zipFile = $result['filePath'];
		$temp = explode(";",$result['info']);
		$extractedPath = $temp[0];
		$moduleActualPath = $temp[1];
		$moduleName = $temp[2];
	}
	
//	die("Zipfile: {$zipFile}<br />extratedPath: {$extractedPath}<br />moduleActualPath: {$moduleActualPath}<br />moduleName: {$moduleName}");
	$issues = "";
	$function = "checkFor{$type}Issues";
	$ret = $function($moduleActualPath,$moduleName,$issues);
	if($ret[0] == 1) 
	{
		displayerror("Your {$type} is still not compatible with Pragyan CMS. Please fix the reported issues during installation.");
		delDir($extractedPath);
		unlink($zipFile);
		mysql_query("DELETE FROM `" . MYSQL_DATABASE_PREFIX . "tempuploads` WHERE `id` = '{$uploadId}'") or displayerror(mysql_error());
		return "";
	}
	
 	if($type=="Module") {
 		$colName = "module_name";
 		$tableName = "modules";
 	} else if($type=="Widget") {
 		$colName = "widget_foldername";
 		$tableName = "widgetsinfo";
 	} else if($type=="Template") {
 		$colName = "template_name";
 		$tableName = "templates";
 	}
 	
 	if(mysql_fetch_array(mysql_query("SELECT `{$colName}` FROM `" . MYSQL_DATABASE_PREFIX . "{$tableName}` WHERE `{$colName}` = '{$moduleName}'"))) 
	{
		displayerror("{$type} Installation failed : {$type} already exist");
		delDir($extractedPath);
		unlink($zipFile);
		mysql_query("DELETE FROM `" . MYSQL_DATABASE_PREFIX . "tempuploads` WHERE `id` = '{$uploadId}'") or displayerror(mysql_error());
		return "";
	}

 	if($type=="Module")
 		installModuleFiles($moduleActualPath, $sourceFolder . "/modules/", $moduleName);
 	else if($type=="Widget") {
 		$destination = "$sourceFolder/$widgetFolder/$moduleName/";
 		if(!file_exists($destination))
 			mkdir($destination);
 		rename($moduleActualPath,$destination);
 	} else if($type=="Template") {
 		$destination = "$sourceFolder/$templateFolder/$moduleName/";
 		if(!file_exists($destination))
 			mkdir($destination);
 		rename($moduleActualPath,$destination);
 	}
	
	$notice = "";
	if($type=="Module") {
		$handle = @fopen($moduleActualPath."/moduleQueries.sql", "r");
		$query = "";
		if ($handle) {
			while (!feof($handle)) {
				$buffer = fgets($handle, 4096);
				if (strpos($buffer,"--")!==0)
					$query.=$buffer;
			}
			fclose($handle);
		}
		$query = str_replace("pragyanV3_",MYSQL_DATABASE_PREFIX,$query);
		$singlequeries = explode(";\n",$query);
		foreach ($singlequeries as $singlequery) {
			if (trim($singlequery)!="") {
				$result1 = mysql_query($singlequery);
				if (!$result1) {
		  			displayerror("<h3>Error:</h3><pre>".$singlequery."</pre>\n<br/>Unable to execute query. " . mysql_error());
				}
			}
		}
		mysql_query("INSERT INTO `" . MYSQL_DATABASE_PREFIX . "modules`(`module_name`,`module_tables`) VALUES('{$moduleName}','" . escape(file_get_contents($moduleActualPath . "moduleTables.txt")) . "')") or displayerror(mysql_error());
		$notice = "";
		if(file_exists($moduleActualPath . "moduleNotice.txt"))
			$notice = ", New module samoduleTablesys:<br>" . file_get_contents($moduleActualPath . "moduleNotice.txt");
	} else if($type=="Widget") {
	
 		$content = explode("|",file_get_contents($destination . "widget.info"));
 		$widgetName = '';
 		$widgetClassName = '';
 		$widgetDescription = '';
 		$widgetVersion = '';
 		$widgetAuthor = '';
 		$widgetFolder = $moduleName;
 		if(count($content)==5) {
 			$widgetName = escape($content[0]);
 			$widgetClassName = escape($content[1]);
 			$widgetDescription = escape($content[2]);
 			$widgetVersion = escape($content[3]);
 			$widgetAuthor = escape($content[4]);
 		} else
 			displaywarning("Widget information could not be read properly");
 		mysql_query("INSERT INTO `" . MYSQL_DATABASE_PREFIX . "widgetsinfo`(`widget_name`,`widget_classname`,`widget_description`,`widget_version`,`widget_author`,`widget_foldername`) VALUES ('{$widgetName}','{$widgetClassName}','{$widgetDescription}','{$widgetVersion}','{$widgetAuthor}','{$widgetFolder}')");
 		if(!mysql_affected_rows()) {
 			displayerror("Installation error, try again later");
 			delDir($sourceFolder . "/widgets/" . $moduleName);
 		}
	} else if($type=="Template") {
		mysql_query("INSERT INTO `" . MYSQL_DATABASE_PREFIX . "templates`(`template_name`) VALUES('{$moduleName}')");
		if(!mysql_affected_rows())
			displayerrro("Problem including uploaded template to database, try <a href='./+admin&subaction=reloadtemplates'>reload templates</a>");
	}
	delDir($extractedPath);
	unlink($zipFile);
	mysql_query("DELETE FROM `" . MYSQL_DATABASE_PREFIX . "tempuploads` WHERE `id` = '{$uploadId}'") or displayerror(mysql_error());
	displayinfo("{$type} installation complete" . $notice);
	return "";
}

function handleModuleManagement() {
	global $sourceFolder;
	if(isset($_POST['btn_install'])) {
		$uploadId = processUploaded("Module");
		if($uploadId != -1)
			return installModule($uploadId,"Module");
	} else if(isset($_POST['btn_uninstall'])) {
		if(!isset($_POST['Module']) || $_POST['Module']=="") return "";
		
		if($_POST['Module']=='article') {
			displayerror("Article module can't be deleted for the home page itself is a article");
			return "";
		}
		$toDelete = escape($_POST['Module']);
		$query = "SELECT `page_id` FROM `" . MYSQL_DATABASE_PREFIX . "pages` WHERE `page_module` = '{$toDelete}' LIMIT 10";
		$result = mysql_query($query) or displayerror(mysql_error());
		if(mysql_num_rows($result)==0||isset($_POST['confirm']))
			if(deleteModule($toDelete)) {
				displayinfo("Module ".safe_html($_POST['Module'])." uninstalled!");
				return "";
			} else {
				displayerror("Module uninstallation failed!");
				return "";
			}
		if(isset($_POST['confirm'])) {
			$query = "DELETE FROM `" . MYSQL_DATABASE_PREFIX . "pages` WHERE `page_module` = '" . $toDelete . "'";
			mysql_query($query) or displayerror(mysql_error());
		}
		
		$pageList = "";
		while($row = mysql_fetch_assoc($result))
			$pageList .= "/home" . getPagePath($row['page_id']) . "<br>";
		
		$modulename = safe_html($_POST['Module']);
		$ret=<<<RET
<fieldset>
<legend>{$ICONS['Modules Management']['small']}Module Management</legend>
Some of the page of type {$modulename} are:<br>
{$pageList}
<div class='cms-error'>These pages will be removed and cant be recovered, If you proceed deleting the module.</div>
<form method=POST action='./+admin&subaction=module&subsubaction=uninstall'>
<input type=hidden value='{$modulename}' name='Module' />
<input type=submit value='Delete module' name='btn_uninstall' />
<input type=hidden value='confirm' name='confirm' />
</form>
</fieldset>
RET;
		return $ret;
	} else if(isset($_GET['subsubaction']) && $_GET['subsubaction'] == 'finalize') {		
		return finalizeInstallation(escape($_POST['id']),"Module");
	} 
	else if(isset($_GET['subsubaction']) && $_GET['subsubaction'] == 'cancel') 
	{
		$uploadId = escape($_POST['id']);
		$result = mysql_fetch_assoc(mysql_query("SELECT * FROM `" . MYSQL_DATABASE_PREFIX. "tempuploads` WHERE `id` = '{$uploadId}'"));
		if($result != NULL) {
			$zipFile = $result['filePath'];
			$temp = explode(";",$result['info']);
			$extractedPath = $temp[0];
			$moduleActualPath = $temp[1];
			$moduleName = $temp[2];
		}
		delDir($extractedPath);
		unlink($zipFile);
		mysql_query("DELETE FROM `" . MYSQL_DATABASE_PREFIX . "tempuploads` WHERE `id` = '{$uploadId}'") or displayerror(mysql_error());
		return "";
	}
}

function deleteModule($module) {
	$result = mysql_query("SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "modules` WHERE `module_name` = '" . $module . "'") or displayerror(mysql_error());
	global $sourceFolder;
	if($row = mysql_fetch_array($result)) {
		$tables = preg_split("/[\s,;]+/",$row['module_tables']);
		$i = 1;
		foreach($tables as $table)
			if($table != "")
				mysql_query("DROP TABLE `{$table}`") or displayerror(mysql_error());
		mysql_query("DELETE FROM `" . MYSQL_DATABASE_PREFIX . "modules` WHERE `module_name` = '" . $module . "'") or displayerror(mysql_error());
		$result = mysql_query("SELECT `perm_id` FROM `" . MYSQL_DATABASE_PREFIX . "permissionlist` WHERE `page_module` = '{$module}'") or displayerror(mysql_error());
		$perms = "";
		while($row = mysql_fetch_assoc($result))
			$perms .= $row['perm_id'] . ",";
		$perms = rtrim($perms, ",");
		mysql_query("DELETE FROM `" . MYSQL_DATABASE_PREFIX . "userpageperm` WHERE `perm_id` IN ({$perms})") or displayerror(mysql_error());
		mysql_query("DELETE FROM `" . MYSQL_DATABASE_PREFIX . "permissionlist` WHERE `page_module` = '" . $module . "'") or displayerror(mysql_error());
		$moduleDir = $sourceFolder . "/modules/" . $module . "/";
		if(file_exists($moduleDir))
			delDir($moduleDir);
		$moduleFile = $sourceFolder . "/modules/" . $module . ".lib.php";
		if(file_exists($moduleFile))
			unlink($moduleFile);
		return true;
	}
	return false;
}

function installModuleFiles($from, $to, $module) {
	if(file_exists($from . "/" . $module . ".lib.php"))
		rename($from . "/" . $module . ".lib.php", $to . "/" . $module . ".lib.php");
	if(is_dir($from . "/" . $module . "/"))
		rename($from . "/" . $module . "/", $to . "/" . $module . "/");
	return true;
}

function installModule($uploadId,$type) {
	global $sourceFolder;
	$result = mysql_fetch_assoc(mysql_query("SELECT * FROM `" . MYSQL_DATABASE_PREFIX. "tempuploads` WHERE `id` = '{$uploadId}'"));
	if($result != NULL) {
		$zipFile = $result['filePath'];
		$temp = explode(";",$result['info']);
		$extractedPath = $temp[0];
		$moduleActualPath = $temp[1];
		$moduleName = $temp[2];
	}
	
 	$function = "checkFor{$type}Issues";
 	$issueType = $function($moduleActualPath,$moduleName,$issues);
	if($issues == "")
		return finalizeInstallation($uploadId,$type);
	$issues ="
	<table name='issues_table'>
	<tr><th>S.No.</th><th>Issue Details</th><th>Issue Type</th><th>Ignore ?</th></tr>
	$issues
	</table>
	<b>Installation cannot proceed for the above mentioned issues, fix them and <a href='./+admin&subaction=widgets&subsubaction=installwidget'>try again</a>.</b>";
	delDir($extractedPath);
	unlink($zipFile);
	mysql_query("DELETE FROM `" . MYSQL_DATABASE_PREFIX . "tempuploads` WHERE `id` = '{$uploadId}'") or displayerror(mysql_error());
	return $issues;
}

function checkForModuleIssues($modulePath,$moduleName,&$issues) {
	$id = 1;
	$i = 0;
	$j = 0;
	if(!file_exists($modulePath . "moduleTables.txt")) {
		addFatalIssue($issues,"Module Info file is missing",$id++);
		$i = 1;
	}
	if(!file_exists($modulePath . $moduleName . ".lib.php")) {
		addFatalIssue($issues,"The module file is corrupt, Please download a fresh copy of the module",$id++);
		$i = 1;
	} else {
		$content = file_get_contents($modulePath . $moduleName . ".lib.php");
		$reqd = array("class ".$moduleName." implements module","public function getHtml","public function createModule","public function deleteModule","public function copyModule");
		foreach($reqd as $var)
			switch(mycount($content,$var)) {
				case 0:
					addFatalIssue($issues,"$var is missing",$id);
					$i = 1;
					$id++;
					break;
				case 1:
					break;
				default:
					addFatalIssue($issues,"$var is more than once",$id);
					$i = 1;
					$id++;
			}
	}
	if(!file_exists($modulePath . $moduleName . ".sql")) {
		addIssue($issue,"No sql file found",$id++);
		$j = 1;
	}

	return array($i,$j);
}
// To be modified for widget
function checkForWidgetIssues($modulePath,$moduleName,&$issues) {
	$id = 1;
	$i = 0;
	$j = 0;
	if(!file_exists($modulePath . "widget.info")) {
		addFatalIssue($issues,"'widget.info' file is missing in the archive!",$id++);
		$i = 1;
	}
	if(!file_exists($modulePath . "widget.class.php")) {
		addFatalIssue($issues,"'widget.class.php' file is missing in the archive!",$id++);
		$i = 1;
	}
	/*
	if(!file_exists($modulePath . $moduleName . ".lib.php")) {
		addFatalIssue($issues,"The module file is corrupt, Please download a fresh copy of the module",$id++);
		$i = 1;
	} else {
		$content = file_get_contents($modulePath . $moduleName . ".lib.php");
		$reqd = array("class ".$moduleName." implements module","public function getHtml","public function createModule","public function deleteModule","public function copyModule");
		foreach($reqd as $var)
			switch(mycount($content,$var)) {
				case 0:
					addFatalIssue($issues,"$var is missing",$id);
					$i = 1;
					$id++;
					break;
				case 1:
					break;
				default:
					addFatalIssue($issues,"$var is more than once",$id);
					$i = 1;
					$id++;
			}
	}
	if(!file_exists($modulePath . $moduleName . ".sql")) {
		addIssue($issue,"No sql file found",$id++);
		$j = 1;
	}
*/
	return array($i,$j);
}
function actualModulePath($modulePath) {
	$moduleActualPath = $modulePath;
	$dirHandle = opendir($modulePath);
	while($file = readdir($dirHandle)) {
		if(substr($file,-8) == ".lib.php")
			return $modulePath;
		elseif(is_dir($modulePath . $file) && $file != '.' && $file != '..') {
			$return = actualModulePath($modulePath . $file . "/");
			if($return != NULL)
				return $return;
		}
	}
	return NULL;
}


function actualWidgetPath($modulePath) {
	$moduleActualPath = $modulePath;
	$dirHandle = opendir($modulePath);
	while($file = readdir($dirHandle)) {
		if($file=="widget.class.php")
			return $modulePath;
		elseif(is_dir($modulePath . $file) && $file != '.' && $file != '..') {
			$return = actualWidgetPath($modulePath . $file . "/");
			if($return != NULL)
				return $return;
		}
	}
	return NULL;
}
function getModuleName($moduleActualPath) {
	$dirHandle = opendir($moduleActualPath);
	while($file = readdir($dirHandle)) {
		if(substr($file,-8) == ".lib.php")
			return substr($file,0,-8);
	}
	return NULL;
}
	
?>
