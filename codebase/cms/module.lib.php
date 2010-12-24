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
 * @author Chakradar Raju
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

function moduleManagementForm() {
	$modules = getAvailableModules();
	$modulesList = "<select id='modules'>";
	foreach($modules as $module)
		$modulesList .= "<option value='" . $module . "'>" . $module . "</option>";
	$modulesList .= "</select>";
	global $ICONS;
	displaywarning("Module Installation/Uninstallation has the potential to completely bring down the CMS, so Install only modules from trusted source");
	require_once("template.lib.php");
	$form=<<<FORM
	<script type="text/javascript">
	function delconfirm(obj) {
		if(confirm("Are you sure want to delete '" + document.getElementById('modules').value + "' module?"))
		{
			document.getElementById("file").value="";
			obj.form.action += "uninstall&delmodule=" + document.getElementById('modules').value;
			return true;
		}
		return false;
	
	}
	</script>
	<form name='module' method='POST' action='./+admin&subaction=module&subsubaction=' enctype="multipart/form-data">
	<fieldset>
	<legend>{$ICONS['Modules Management']['small']}Module Management</legend>
	Add new Module (select a ZIP file containing module): <input type='file' name='file' id='file'><input type='submit' name='btn_install' value='Upload' onclick='this.form.action+="install"'>
	<br/><br/>Delete Existing Module: {$modulesList}<input type='submit' name='btn_uninstall' value='Uninstall' onclick='return delconfirm(this);'>
	</fieldset>
	</form>
FORM;
	return $form;
}

function processUploaded() {
	global $sourceFolder;
	if(!file_exists($sourceFolder . "/uploads/modules/"))
		mkdir($sourceFolder . "/uploads/modules/");
	$zipFile = $sourceFolder ."/uploads/modules/".$_FILES['file']['name'];
	$ext = extension($zipFile);
	while(file_exists($zipFile))
		$zipFile = $sourceFolder . "/uploads/modules/" . rand() . $ext;
	move_uploaded_file($_FILES['file']['tmp_name'],$zipFile);

	$len = strlen($zipFile);
	$moduleName = name($zipFile,".");
	if(substr($zipFile,$len-4,4)==".zip") {
		$zip = new ZipArchive();
		if ($zip->open($zipFile) === TRUE) {
			$extractedPath = $sourceFolder . "/uploads/modules/" . $moduleName . "/";
			while(file_exists($extractedPath))
				$extractedPath = $sourceFolder . "/uploads/modules/". rand() . "/";
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

	$moduleActualPath = actualModulePath($extractedPath);
	
	if($moduleActualPath != NULL) {
		$moduleName = getModuleName($moduleActualPath);
		if(mysql_fetch_array(mysql_query("SELECT `module_name` FROM `".MYSQL_DATABASE_PREFIX."modules` WHERE `module_name` = '{$moduleName}'"))) {
			displayerror("A module with name '{$moduleName}' already exist, Installation aborted");
			delDir($extractedPath);
			unlink($zipFile);
			return -1;
		}
		mysql_query("INSERT INTO `" . MYSQL_DATABASE_PREFIX . "tempuploads`(`filePath`,`info`) VALUES('{$zipFile}','{$extractedPath};{$moduleActualPath};{$moduleName}')");
		$result = mysql_fetch_assoc(mysql_query("SELECT `id` FROM `" . MYSQL_DATABASE_PREFIX . "tempuploads` WHERE `filePath` = '{$zipFile}'"));
		return $result['id'];
	}
	
	displayerror("Module file not found");
	delDir($extractedPath);
	unlink($zipFile);
	
	return -1;
}

function finalizeInstallation($uploadId) {
	global $sourceFolder;
	$result = mysql_fetch_assoc(mysql_query("SELECT * FROM `" . MYSQL_DATABASE_PREFIX. "tempuploads` WHERE `id` = '{$uploadId}'"));
	if($result != NULL) {
		$zipFile = $result['filePath'];
		$temp = explode(";",$result['info']);
		$extractedPath = $temp[0];
		$moduleActualPath = $temp[1];
		$moduleName = $temp[2];
	}

	$issues = "";
	$ret = checkForIssues($moduleActualPath,$moduleName,$issues);
	if($ret[0] == 1) 
	{
		displayerror("Your module is still not compatible with Pragyan CMS. Please fix the reported issues during installation.");
		delDir($extractedPath);
		unlink($zipFile);
		mysql_query("DELETE FROM `" . MYSQL_DATABASE_PREFIX . "tempuploads` WHERE `id` = '{$uploadId}'") or displayerror(mysql_error());
		return "";
	}
		
	if(mysql_fetch_array(mysql_query("SELECT `module_name` FROM `" . MYSQL_DATABASE_PREFIX . "modules` WHERE `module_name` = '{$moduleName}'"))) 
	{
		displayerror("Template Installation failed : Module already exist");
		delDir($extractedPath);
		unlink($zipFile);
		mysql_query("DELETE FROM `" . MYSQL_DATABASE_PREFIX . "tempuploads` WHERE `id` = '{$uploadId}'") or displayerror(mysql_error());
		return "";
	}
	installModuleFiles($moduleActualPath, $sourceFolder . "/modules/", $moduleName);
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
		$notice = ", New module says:<br>" . file_get_contents($moduleActualPath . "moduleNotice.txt");
	delDir($extractedPath);
	unlink($zipFile);
	mysql_query("DELETE FROM `" . MYSQL_DATABASE_PREFIX . "tempuploads` WHERE `id` = '{$uploadId}'") or displayerror(mysql_error());
	displayinfo("Module installation complete" . $notice);
	return "";
}

function handleModuleManagement() {
	global $sourceFolder;
	if(isset($_POST['btn_install'])) {
		$uploadId = processUploaded();
		if($uploadId != -1)
			return installModule($uploadId);
	} else if(isset($_POST['btn_uninstall'])) {
		if(!isset($_GET['delmodule']) || $_GET['delmodule']=="") return "";
		
		if($_GET['delmodule']=='article') {
			displayerror("Article module can't be deleted for the home page itself is a article");
			return "";
		}
		$query = "SELECT `page_id` FROM `" . MYSQL_DATABASE_PREFIX . "pages` WHERE `page_module` = '" . escape($_GET['delmodule']) . "' LIMIT 10";
		$result = mysql_query($query) or displayerror(mysql_error());
		if(mysql_num_rows($result)==0)
			if(deleteModule(escape($_GET['delmodule']))) {
				displayinfo("Module ".safe_html($_GET['delmodule'])." uninstalled!");
				return "";
			} else {
				displayerror("Module uninstallation failed!");
				return "";
			}
		if(isset($_POST['confirm'])) {
			$query = "DELETE FROM `" . MYSQL_DATABASE_PREFIX . "pages` WHERE `page_module` = '" . escape($_GET['delmodule']) . "'";
			mysql_query($query) or displayerror(mysql_error());
			if(deleteModule(escape($_GET['delmodule']))) {
				displayinfo("Module ".safe_html($_GET['delmodule'])." uninstalled!");
				return "";
			} else {
				displayerror("Module uninstallation failed!");
				return "";
			}
		}
		
		$pageList = "";
		while($row = mysql_fetch_assoc($result))
			$pageList .= "/home" . getPagePath($row['page_id']) . "<br>";
		
		$modulename = safe_html($_GET['delmodule']);
		$ret=<<<RET
<fieldset>
<legend>{$ICONS['Modules Management']['small']}Module Management</legend>
Some of the page of type {$modulename} are:<br>
{$pageList}
<div class='cms-error'>These pages will be removed and cant be recovered, If you proceed deleting the module.</div>
<form method=POST action='./+admin&subaction=module&subsubaction=uninstall&delmodule={$modulename}'>
<input type=submit value='Delete module' name='btn_uninstall'>
<input type=hidden value='confirm' name='confirm'>
</form>
</fieldset>
RET;
		return $ret;
	} else if(isset($_GET['subsubaction']) && $_GET['subsubaction'] == 'finalize') {		
		return finalizeInstallation(escape($_POST['id']));
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
		$moduleDir = $sourceFolder . "/modules/" . escape($_GET['delmodule']) . "/";
		if(file_exists($moduleDir))
			delDir($moduleDir);
		$moduleFile = $sourceFolder . "/modules/" . escape($_GET['delmodule']) . ".lib.php";
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

function installModule($uploadId) {
	global $sourceFolder;
	$result = mysql_fetch_assoc(mysql_query("SELECT * FROM `" . MYSQL_DATABASE_PREFIX. "tempuploads` WHERE `id` = '{$uploadId}'"));
	if($result != NULL) {
		$zipFile = $result['filePath'];
		$temp = explode(";",$result['info']);
		$extractedPath = $temp[0];
		$moduleActualPath = $temp[1];
		$moduleName = $temp[2];
	}
	
	$issueType = checkForIssues($moduleActualPath,$moduleName,$issues);
	if($issues == "")
		return finalizeInstallation($uploadId);
	$issues ="
	<table name='issues_table'>
	<tr><th>S.No.</th><th>Issue Details</th><th>Issue Type</th><th>Ignore ?</th></tr>
	$issues
	</table>
	Installation cannot proceed for the above mentioned issues, fix them and try again.";
	delDir($extractedPath);
	unlink($zipFile);
	mysql_query("DELETE FROM `" . MYSQL_DATABASE_PREFIX . "tempuploads` WHERE `id` = '{$uploadId}'") or displayerror(mysql_error());
	return $issues;
}

function checkForIssues($modulePath,$moduleName,&$issues) {
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

function getModuleName($moduleActualPath) {
	$dirHandle = opendir($moduleActualPath);
	while($file = readdir($dirHandle)) {
		if(substr($file,-8) == ".lib.php")
			return substr($file,0,-8);
	}
	return NULL;
}
	
?>
