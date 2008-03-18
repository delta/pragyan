<?php
/*
 * Created on Oct 17, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
class gallery implements module, fileuploadable {
	private $userId;
	private $moduleComponentId;
	private $action;
	public function getHtml($gotuid, $gotmoduleComponentId, $gotaction) {
		$this->userId = $gotuid;
		$this->moduleComponentId = $gotmoduleComponentId;
		$this->action = $gotaction;
		if ($this->action == "view")
			return $this->actionView();
		if ($this->action == "create")
			return $this->createModule($this->moduleComponentId);
		if ($this->action == "edit")
			return $this->actionEdit($this->moduleComponentId);
	}

	/**
	 * Funtion which tells the cms uploaded file access is defined by which action
	 */
	public static function getFileAccessPermission($pageId, $moduleComponentId, $userId, $fileName) {
		return getPermissions($userId, $pageId, "view");
	}

	public static function getUploadableFileProperties(&$fileTypesArray,&$maxFileSizeInBytes) {
		$fileTypesArray = array('jpg','jpeg','png','gif');
		$maxFileSizeInBytes = 2*1024*1024;
	}

	public static function getFileAccessAction() {
		return "view";
	}

	public function actionView() {
		global $sourceFolder;
		global $templateFolder;
		global $urlRequestRoot;
		global $moduleFolder;
		global $uploadFolder;
		$content =<<<JS
			<script type="text/javascript" src="$urlRequestRoot/$sourceFolder/$moduleFolder/gallery/library/highslide.js"></script>
			<script type="text/javascript">
			hs.graphicsDir = "$urlRequestRoot/$sourceFolder/$moduleFolder/gallery/library/graphics/";
			</script>
			<link rel="stylesheet" href="$urlRequestRoot/$sourceFolder/$moduleFolder/gallery/library/default.css" />


JS;
		$gallQuery = "SELECT * from `gallery_name` where `page_modulecomponentid`=$this->moduleComponentId";
		$gallResult = mysql_query($gallQuery);
		$row = mysql_fetch_assoc($gallResult);
		$content .= "<style type=\"text/css\">.pragyangallery{margin:10px;padding:10px}</style><h2><center>{$row['gallery_name']}</center></h2><br/><center><h3>{$row['gallery_desc']}</center></h3>";
		include_once ("$sourceFolder/" . 'upload.lib.php');
		$arr = getUploadedFiles($this->moduleComponentId, 'gallery');

		for ($i = 0; $i < count($arr);$i++){
			$gallQuery2 = "SELECT * FROM `gallery_pics` where `upload_filename`='{$arr[$i]['upload_filename']}' AND `page_modulecomponentid`= $this->moduleComponentId";
			$gallResult2 = mysql_query($gallQuery2);
			$row2 = mysql_fetch_assoc($gallResult2);
			$content.="<span class=\"pragyangallery\"><div class='highslide-caption' id=\"caption$i\">{$row2['gallery_filecomment']}</div>";
			$content .= "<a href=\"./".$arr[$i]['upload_filename'].'"  class=\'highslide\' onclick="return hs.expand(this, {captionId: \'caption'.$i.'\'})">';
			$content .= "<img src=\"./".$arr[$i]['upload_filename']."\" alt='Image not available' title='Click to enlarge' height='100' width='136' /></a></span>";
		}
		return $content;
	}
	public function createModule(& $moduleComponentId) {
		$gallQuery = "SELECT MAX(`page_modulecomponentid`) AS max FROM `gallery_name`";
		$gallResult = mysql_query($gallQuery);
		$nextId = 1;
		if ($gallResultRow = mysql_fetch_row($gallResult)) {
			$nextId = $gallResultRow[0] + 1;
		}
		$gallQuery = "INSERT INTO `gallery_name` (`page_modulecomponentid`, `gallery_name`, `gallery_desc`) VALUES($nextId, 'New Gallery', 'Edit your new gallery')";
		$gallResult = mysql_query($gallQuery);
		if ($gallResult) {
			$moduleComponentId = $nextId;
		}
	}
	public function actionEdit($moduleComponentId) {
		global $sourceFolder;
		global $templateFolder;
		global $urlRequestRoot;
		global $uploadFolder;
		require_once ("$sourceFolder/$moduleFolder" . 'upload.lib.php');
		$arr=getUploadedFiles($moduleComponentId, 'gallery');
		for($i=0; $i<count($arr); $i++)
		{
		if (isset ($_POST['btnDeleteImage'])&& isset ($_POST['imagename']) && $_POST['imagename'] != '') {
			deleteFile($moduleComponentId, 'gallery', $_POST['imagename']);
			$gallQuery="DELETE FROM `gallery_pics` WHERE `upload_filename`='{$_POST['imagename']}'";
			$gallResult=mysql_query($gallQuery);
		} else
			if (isset ($_POST['btnEditComment']) && isset ($_POST['imagename']) && $_POST['imagename'] != '') {
				$gallQuery="UPDATE `gallery_pics` SET `gallery_filecomment`='{$_POST['desc']}' WHERE `upload_filename`='{$_POST['imagename']}'";
				$gallResult=mysql_query($gallQuery);
				}
			else
				if (isset($_POST['btnEditGallname']) && isset($_POST['gallName']) && isset($_POST['gallDesc']) && $_POST['gallName'] != '' && $_POST['gallDesc'] != ''){
					$gallQuery="UPDATE `gallery_name` SET `gallery_name`='{$_POST['gallName']}',`gallery_desc`='{$_POST['gallDesc']}' WHERE `page_modulecomponentid`=$moduleComponentId";
					$gallResult=mysql_query($gallQuery);
				}
				$content2 .=getFileUploadForm($this->moduleComponentId, "gallery",'./+edit',false,5);
				$allowableTypes = array (
					'jpeg',
					'jpg',
					'png',
					'gif'
				);

				$uploadSuccess = submitFileUploadForm($this->moduleComponentId, "gallery", $this->userId,false,$allowableTypes);
				if (is_array($uploadSuccess) && isset($uploadSuccess[0])) {
					$gallQuery3 = "INSERT INTO `gallery_pics` (`upload_filename`, `page_modulecomponentid`, `gallery_filecomment`) VALUES('$uploadSuccess[0]', $this->moduleComponentId, '{$_POST['desc']}')";
					$gallResult3 = mysql_query($gallQuery3);
				}
				$arr = getUploadedFiles($this->moduleComponentId, 'gallery');
		$content2 .="<table><tr><td>";

		for ($i = 0; $i < count($arr); $i++) {
			$gallQuery2 = "SELECT * FROM `gallery_pics` where `upload_filename`='{$arr[$i]['upload_filename']}' AND `page_modulecomponentid`= $this->moduleComponentId";
			$gallResult2 = mysql_query($gallQuery2);
			$row2 = mysql_fetch_assoc($gallResult2);
			$content2 .="<form name=\"edit\" method=\"POST\" action=\"./+edit\">";
			$content2 .="<p align=\"center\"> Edit gallery name and description<br/>Gallery Name<input type='text' name=\"gallName\"><br/>Gallery Description<input type='text' name=\"gallDesc\"><br/><input type=\"submit\" name=\"btnEditGallname\" value=\"Change Gallery Name\"><br/></p>";
			$content2 .= "<a href=\"./".$arr[$i]['upload_filename'].'">';
			$content2 .= "<img src=\"./".$arr[$i]['upload_filename']."\" alt='Image not available' title='Click on the image to delete it' height='100' width='136' /></a>";
			$content2 .="<div class='highslide-caption' id=\"caption$i\">{$row2['gallery_filecomment']}</div>";
			$content2 .= "<input type=\"hidden\" name=\"imagename\" value=\"{$row2['upload_filename']}\" />";
			$content2 .="<input type='text' name='desc'><input type=\"submit\" name=\"btnEditComment\" value=\"Edit comment\">";
			$content2 .="<br/><input type=\"submit\" name=\"btnDeleteImage\" value=\"Delete\" />";
			$content2 .="</form></td>";

			if(($i+1)% 5==0){
				$content2 .="</tr><tr><td>";
			}
			else
				$content2 .="<td><form name=\"edit\" method=\"POST\" action=\"./+edit\">";
		}
		$content2 .="</form></tr></table>";
		return $content2;
	}
}
	public function deleteModule($moduleComponentId) {
		$arr = getUploadedFiles($moduleComponentId, 'gallery');
		for ($c = 0; $c < count($arr); $c++) {
			$content = deleteFile($moduleComponentId, 'gallery', $arr[$c]['upload_filename']);
		}
		$gallQuery = "DELETE FROM `gall_name` where `page_modulecomponentid`=$moduleComponentId";
		$gallResult = mysql_query($gallQuery);
		$gallQuery2 = "DELETE FROM `gall_pics` where `page_modulecomponentid`=$moduleComponentId";
		$gallResult2 = mysql_query($gallQuery2);
		return $content;
	}
	public function copyModule($moduleComponentId) {
		$gallQuery = "SELECT * FROM `gallery_pics` WHERE page_modulecomponentid = " . $moduleComponentId;
		$gallResult = mysql_query($gallQuery);
		$gallRow = mysql_fetch_assoc($gallResult);
		$gallQuery2 = "SELECT MAX(`page_modulecomponentid`) AS 'max' from `gallery_name`";
		$gallResult2 = mysql_query($gallQuery2);
		$destinationPage_moduleComponentId = $gallResult2['max'] + 1;
		while ($gallRow) {
			fileCopy($moduleComponentId,'gallery', $gallRow['upload_filename'], $destinationPage_moduleComponentId, 'gallery', $gallRow['upload_filename'], $this->userId);
		}
	}
}
?>