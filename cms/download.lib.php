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

/**
 *
 * Gets the file that has been requested by mapping it to the proper location
 *
 * @param $pageId The page where the file is present in
 * @param $userId The user who has requested the file.
 * @param $fileName The name of the file that is required.
 *
 * @return mixed: nothing if there is an error and the file otherwise.
 */

function download($pageId, $userId, $fileName,$action="") {
	
        /// If page not found display error  
	if($pageId===false) {
		header("http/1.0 404 Not Found" );
		echo "<html><head><title>404 Not Found</title></head><body><h1>Not Found</h1>" .
			 "<p>The requested URL ".$_SERVER['SCRIPT_UR']." was not found on this server.</p><hr>" .
			 "$_SERVER[SERVER_SIGNATURE]</body></html>";
		disconnect();
		exit;
	}
	
	if($action=="") $action="view";
	// Profile Image exception added by Abhishek
	global $sourceFolder;
	global $moduleFolder;
	if($action!="profile")
	{
		$actualPageId = getDereferencedPageId($pageId);
		$moduleType = getPageModule($actualPageId);
		$moduleComponentId = getPageModuleComponentId($actualPageId);
		
		require_once ($sourceFolder . "/content.lib.php");
		require_once ($sourceFolder . "/" . $moduleFolder . "/" . $moduleType . ".lib.php");
		$moduleInstance = new $moduleType ();

		if (!($moduleInstance instanceof fileuploadable)) {
			echo "The module \"$moduleType\" does not implement the inteface upload.";
			return "";
		}
		if (!($moduleInstance->getFileAccessPermission($pageId,$moduleComponentId,$userId, $fileName))) {
			echo "Access Denied.";
			return "";
		}
		
	}
	else //Exception for 'profile' images as its not a module
	{
		$actualPageId = getDereferencedPageId($pageId);
		$moduleType = "profile";
		$moduleComponentId = $userId;
		
		// Since the moduleComponentId is equal to userId, the image could be retrieved only if the userId is valid, hence no need for security check for file access here :)
		
	}

	//return the file the particular page id.
	
	$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "uploads` WHERE  `upload_filename`= '". escape($fileName). "' AND `page_module` = '".escape($moduleType)."' AND `page_modulecomponentid` = '".escape($moduleComponentId)."'";
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
			 "$_SERVER[SERVER_SIGNATURE]</body></html>";
		exit();
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
	
	header("Expires: Sat, 23 Jan 2010 20:53:35 +0530"); // . date('r', strtotime('+1 year')));

	$last_modified_time = filemtime($file);
	header('Date: ' . date('r'));
	header('Last-Modified: ' . date('r', strtotime($row['upload_time'])));
	$etag = md5_file($file);
	header("ETag: $etag");
	if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time || 
	    (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag) ) {
	  header("HTTP/1.1 304 Not Modified");
	  exit();
	}
	
	

	echo @fread($filePointer, filesize($file));
	@fclose($filePointer);

}

