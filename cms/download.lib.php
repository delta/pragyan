<?php

/*
 * Created on Oct 18, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
function download($pageId, $userId, $fileName) {
	if($pageId===false) {
		header("http/1.0 404 Not Found" );
		echo "<html><head><title>404 Not Found</title></head><body><h1>Not Found</h1>" .
			 "<p>The requested URL ".$_SERVER['SCRIPT_URL']." was not found on this server.</p><hr>" .
			 "<address>Apache/2.2.4 (Fedora) Server at localhost Port 80</address></body></html>";
		disconnect();
		exit;
	}
	$actualPageId = getDereferencedPageId($pageId);
	$moduleType = getPageModule($actualPageId);
	$moduleComponentId = getPageModuleComponentId($actualPageId);
	/**
	 * TODO: the following is only until PHP 2.3 comes
	 * In php 2.3 we can call static function directly without making an instance of the class.
	 */
	global $sourceFolder;
	global $moduleFolder;
	require_once ($sourceFolder . "/content.lib.php");
	require_once ($sourceFolder . "/" . $moduleFolder . "/" . $moduleType . ".lib.php");
	$moduleInstance = new $moduleType ();

	if (!($moduleInstance instanceof fileuploadable)) {
		echo "The module \"$moduleType\" does not implement the inteface upload";
		return "";
	}
	if (!($moduleInstance->getFileAccessPermission($pageId,$moduleComponentId,$userId, $fileName))) {
		echo "Access Denied.";
		return "";
	}

	//return the file the particular page id.
	$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "uploads` WHERE  `upload_filename`= '$fileName' AND `page_module` = '$moduleType' AND `page_modulecomponentid` = '$moduleComponentId'";
	$result = mysql_query($query) or die(mysql_error() . "upload L:85");
	$row = mysql_fetch_assoc($result);

	$fileType = $row['upload_filetype'];
	/**
	 * Not checking if filetype adheres to uploadable filetype list beacuse this check can be
	 * performed in $moduleInstance->getFileAccessPermission.
	 */

	$uploadFolder = 'uploads';
	$upload_fileid = $row['upload_fileid'];
	$filename = str_repeat("0", (10 - strlen((string) $upload_fileid))) . $upload_fileid . "_" . $fileName;
	$file = $sourceFolder . "/" . $uploadFolder . "/" . $moduleType . "/" . $filename;
	disconnect();

	$filePointer = @fopen($file, 'r') ;
	if($filePointer==FALSE){
		header("http/1.0 404 Not Found" );
		echo "<html><head><title>404 Not Found</title></head><body><h1>Not Found</h1>" .
			 "<p>The requested URL ".$_SERVER['SCRIPT_URL']." was not found on this server.</p><hr>" .
			 "<address>Apache/2.2.4 (Fedora) Server at localhost Port 80</address></body></html>";
		exit;
	}
	elseif ($fileType == 'image/jpeg')
		header("Content-Type: image/jpg");
	elseif ($fileType == 'image/gif')
		header("Content-Type: image/gif");
	elseif ($fileType == 'image/png')
		header("Content-Type: image/png");
	elseif ($fileType == 'image/bmp')
		header("Content-Type: image/bmp");
	elseif ($fileType == 'image/svg+xml')
		header("Content-Type: image/svg+xml");
	else
		header("Content-Type: application/force-download");

/*	$fileSize = filesize($file);
	$numOfDownloads = $fileSize/10240;
	$lastDownloadBytes = $fileSize%10240;
	for($i=0;$i<$numOfDownloads;$i++)
		echo @fread($filePointer, 10240);
	echo @fread($filePointer, $lastDownloadBytes);*/
	echo @fread($filePointer, filesize($file));
	@fclose($filePointer);

}
?>
