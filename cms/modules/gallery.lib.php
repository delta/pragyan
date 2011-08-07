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
 * @author Harini A
 * @copyright (c) 2008 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
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

	public static function getUploadableFileProperties(& $fileTypesArray, & $maxFileSizeInBytes) {
		$fileTypesArray = array (
			'jpg',
			'jpeg',
			'png',
			'gif'
		);
		$maxFileSizeInBytes = 2 * 1024 * 1024;
	}

	public static function getFileAccessAction() {
		return "view";
	}

	public function actionView() {
		global $sourceFolder,$cmsFolder;
		global $templateFolder;
		global $urlRequestRoot;
		global $moduleFolder;
		global $uploadFolder;
		// Ajax request for returning the views of the image
		if(isset($_GET['subaction'])&&$_GET['subaction']=='ajax') {
		if($_GET['ref']){
			$arr=explode("/",$_GET['ref']);
			$arr = $arr[sizeof($arr)-1];
			$query="SELECT* FROM `gallery_pics` WHERE upload_filename='".$arr."' AND page_modulecomponentid='$this->moduleComponentId' LIMIT 1";
			$result=mysql_query($query);
			if($result){
				$newrate = mysql_result($result,0,'pic_rate')+1;
				$query="UPDATE `gallery_pics` SET `pic_rate`='".$newrate."' WHERE upload_filename='".$arr."' AND page_modulecomponentid='$this->moduleComponentId'";
				mysql_query($query);
			}}
		else if($_GET['getView']){
			$arr1=explode("/",$_GET['getView']);
			$arr1 = $arr1[sizeof($arr1)-1];
			$query="SELECT* FROM `gallery_pics` WHERE upload_filename='".$arr1."' AND page_modulecomponentid='$this->moduleComponentId' LIMIT 1";
			$result1=mysql_query($query);
			if($result1){
				$view = mysql_result($result1,0,'pic_rate');
				echo $view;
			}
			}
		else if($_GET['rateIt']){
			$arr3 = $_GET['rateRef'];
			$query="SELECT `vote_avg`,`voters` FROM `gallery_pics` WHERE upload_filename='".$arr3."' AND page_modulecomponentid='$this->moduleComponentId' LIMIT 1";
			$result3=mysql_query($query);
			if($result3){
				$voteAvg = mysql_result($result3,0,'vote_avg');
				$voters = mysql_result($result3,0,'voters');
				$newAvg = (($voters*$voteAvg)+$_GET['rateIt'])/($voters+1);
				$voters=$voters+1;
				$query="UPDATE `gallery_pics` SET `vote_avg`='".$newAvg."',`voters`='".$voters."' WHERE upload_filename='".$arr3."' AND page_modulecomponentid='$this->moduleComponentId'";
				$result = mysql_query($query);
				if (!$result){echo "a";}
				else{
					$query="SELECT* FROM `gallery_pics` WHERE upload_filename='".$arr3."' AND page_modulecomponentid='$this->moduleComponentId' LIMIT 1";
					$result3 = mysql_query($query);
					if($result3){
						$rating = mysql_result($result3,0,'vote_avg');
						$voters = mysql_result($result3,0,'voters');
						echo $rating."-".$voters;
					}
					else{
						echo "b";
					}
				}
				}
			}
			disconnect();
			exit(0);
		}
		// Ajax request for views ends here
		$content =<<<JS
			<script type="text/javascript" src="$urlRequestRoot/$cmsFolder/$moduleFolder/gallery/highslide-with-gallery.js"></script>
			<link rel="stylesheet" type="text/css" href="$urlRequestRoot/$cmsFolder/$moduleFolder/gallery/highslide.css" />
			<script type="text/javascript">
				hs.graphicsDir = '$urlRequestRoot/$cmsFolder/$moduleFolder/gallery/graphics/';
				hs.align = 'center';
				hs.transitions = ['expand', 'crossfade'];
				hs.fadeInOut = true;
				hs.dimmingOpacity = 0.8;
				hs.outlineType = 'rounded-white';
				hs.captionEval = 'this.thumb.alt';
				hs.marginBottom = 105;
				hs.numberPosition = 'caption';

				hs.addSlideshow({
					interval: 5000,
					repeat: false,
					useControls: true,
					overlayOptions: {
						className: 'text-controls',
						position: 'bottom center',
						relativeTo: 'viewport',
						offsetY: -60
					},
					thumbstrip: {
						position: 'bottom center',
						mode: 'horizontal',
						relativeTo: 'viewport'
					}
				});
			</script>
JS;
		$gallQuery = "SELECT * from `gallery_name` where `page_modulecomponentid`='$this->moduleComponentId'";
		$gallResult = mysql_query($gallQuery);
		$row = mysql_fetch_assoc($gallResult);
		$content .= "<h2><center>{$row['gallery_name']}</center></h2><br/><center><h3>{$row['gallery_desc']}</center></h3>";
		$perPage = $row['imagesPerPage'];
		$viewCheck = $row['allowViews'];
		$ratingCheck = $row['allowRatings'];
		include_once ("$sourceFolder/" . 'upload.lib.php');
		$query = "SELECT `upload_filename` FROM `gallery_pics` WHERE `page_modulecomponentid` ='". $this->moduleComponentId."'";
		$pic_result = mysql_query($query) or die(mysql_error());
		$arr = array ();
		while ($row = mysql_fetch_assoc($pic_result))
			$arr[] = $row;
		$numPic = count($arr);
		if(isset($_GET['gallerypage']))
			$page = (int)escape($_GET['gallerypage']) - 1;
		else
			$page = 0;
		$start = $page * $perPage;
		if($start > $numPic) {
			$start = 0;
			$page = 0;
		}
		$end = $start + $perPage;
		if($end > $numPic)
			$end = $numPic;
		$content .= '<div class="highslide-gallery" style="width: 100%; margin: auto">';
		for ($i = $start; $i < $end; $i++) {
			$gallQuery2 = "SELECT * FROM `gallery_pics` where `upload_filename`='{$arr[$i]['upload_filename']}' AND `page_modulecomponentid`= '$this->moduleComponentId'";
			$gallResult2 = mysql_query($gallQuery2);
			$row2 = mysql_fetch_assoc($gallResult2);
			if ($row2) {
				$content .= "<input type=\"hidden\" id=\""."thumb_"."{$row2['upload_filename']}\" value=\"{$row2['pic_rate']}\" />";
				$content .= "<input type=\"hidden\" id=\""."thumb1_"."{$row2['upload_filename']}\" value=\"{$row2['vote_avg']}\" />";
				$content .= "<input type=\"hidden\" id=\""."thumb2_"."{$row2['upload_filename']}\" value=\"{$row2['voters']}\" />";
				$content .= "<input type=\"hidden\" id=\""."thumb3_"."{$row2['upload_filename']}\" value=\"0\" />";
				$content .= "<a href=\"./" . $arr[$i]['upload_filename'] . '"  class=\'highslide\' onclick="return hs.expand(this,0,0,0,document.getElementById(\'thumb_' .$row2['upload_filename'].'\'),'.$viewCheck.',document.getElementById(\'thumb1_' .$row2['upload_filename'].'\'),document.getElementById(\'thumb2_' .$row2['upload_filename'].'\'),'.$ratingCheck.',document.getElementById(\'thumb3_' .$row2['upload_filename'].'\'))">';
				$content .= "<img src=\"./thumb_" . $arr[$i]['upload_filename'] . "\" alt='{$row2['gallery_filecomment']}' title='Click to enlarge' /></a>   &nbsp;";
			}
		}
		$content .= '</div>';
		$nextVal = $page + 2;
		if($start == 0)
			$prevButton = "&lt;&lt;Prev ";
		else
			$prevButton = "<a href='./+view&gallerypage=" . $page . "'> &lt;&lt;Prev</a> ";
		if($end == $numPic)
			$nextButton = " Next&gt;&gt;";
		else
			$nextButton = " <a href='./+view&gallerypage=" . $nextVal . "'> Next&gt;&gt; </a>";
		$pages = "";
		$pageStart = 1;
		$pageEnd = ceil($numPic/$perPage);
		if($page > 4) {
			$pageStart = $page - 3;
			$pages .= "... ";
		}
		if($pageEnd - $page > 5)
			$pageEnd = $page + 5;
		$pageVal = $page + 1;
		for($i = $pageStart; $i <= $pageEnd; $i++)
			if($i == $pageVal)
				$pages .= " $pageVal ";
			else
				$pages .= " <a href='./+view&gallerypage={$i}'>{$i}</a>&nbsp;";
		if(ceil($numPic/$perPage) - $page > 5)
			$pages .= " ...";
		$content .= "<p>" . $prevButton . $pages . $nextButton . "</p>";
		return $content;
	}
	public function createModule($nextId) {
		$gallQuery = "INSERT INTO `gallery_name` (`page_modulecomponentid`, `gallery_name`, `gallery_desc`) VALUES('$nextId', 'New Gallery', 'Edit your new gallery')";
		$gallResult = mysql_query($gallQuery);
	}
	public function actionEdit($moduleComponentId) {
		global $sourceFolder;
		global $templateFolder;
		global $urlRequestRoot;
		global $uploadFolder;
		require_once ("$sourceFolder/upload.lib.php");
		$arr = getUploadedFiles($moduleComponentId, 'gallery');
		if (isset ($_POST['btnDeleteImage']) && isset ($_POST['imagename']) && $_POST['imagename'] != '') {
			deleteFile($moduleComponentId, 'gallery', $_POST['imagename']);
			$gallQuery = "DELETE FROM `gallery_pics` WHERE `upload_filename`='".escape($_POST['imagename'])."'";
			$gallResult = mysql_query($gallQuery);
		} 
		else if (isset ($_POST['btnEditComment']) && isset ($_POST['imagename']) && $_POST['imagename'] != '') {
			$imageName =  escape($_POST['imagename']);
			$comment = escape($_POST['desc']);
			$gallQuery = "UPDATE `gallery_pics` SET `gallery_filecomment`=\"$comment\" WHERE `upload_filename`=\"$imageName\"";
			$gallResult = mysql_query($gallQuery);
		}
		if (isset ($_POST['btnEditGallname']) && isset ($_POST['gallName']) && isset ($_POST['gallDesc']) && $_POST['gallName'] != '' && $_POST['gallDesc'] != '') {
			if(is_numeric($_POST['imagesPerPage']))
				$perPage = (int)escape($_POST['imagesPerPage']);
				$viewCount = ( $_POST['allowViews'] ? 1 : 0 );
				$ratingCount = ( $_POST['allowRatings'] ? 1 : 0 );
			$gallQuery = "UPDATE `gallery_name` SET `gallery_name`='".escape($_POST['gallName'])."',`gallery_desc`='".escape($_POST['gallDesc'])."', `imagesPerPage`='".$perPage."',`allowViews`='".$viewCount."',`allowRatings`='".$ratingCount."' WHERE `page_modulecomponentid`='$moduleComponentId'";
			$gallResult = mysql_query($gallQuery);
		}

		$content2 = getFileUploadForm($this->moduleComponentId, "gallery", './+edit', 10000000, 5);
		$allowableTypes = array (
			'jpeg',
			'jpg',
			'png',
			'gif'
		);

		$uploadSuccess = submitFileUploadForm($this->moduleComponentId, "gallery", $this->userId, false, $allowableTypes);
		if (is_array($uploadSuccess) && isset ($uploadSuccess[0])) {
			for($i=0;$i<count($uploadSuccess);$i++){
				$gallQuery3 = "INSERT INTO `gallery_pics` (`upload_filename`, `page_modulecomponentid`, `gallery_filecomment`) VALUES('$uploadSuccess[$i]', '$this->moduleComponentId', 'No Comment')";
				$gallResult3 = mysql_query($gallQuery3);
			}
		}
		$arr = getUploadedFiles($this->moduleComponentId, 'gallery');
		global $ICONS;
		$content2="<fieldset><legend>{$ICONS['Gallery Edit']['small']}Edit Gallery</legend>".$content2;
		
		$result = mysql_fetch_array(mysql_query("SELECT * FROM `gallery_name` WHERE `page_modulecomponentid` = '{$this->moduleComponentId}'"));
		if($result){
			$checkViews = ($result['allowViews'] == 1 ? 'checked="checked" ': '' );
			$checkRatings = ($result['allowRatings'] == 1 ? 'checked="checked" ': '' );
		}
		$content2 .=<<<GALFORM
					<br /><br />
					<script type="text/javascript">
						<!--
						function validate() {
							var strValidChars = "0123456789.-";
							var strString = document.getElementById('perPage').value;

							if (strString.length == 0)
								alert("Empty Images Per Page will be taken as default value(10).");

							for (i = 0; i < strString.length; i++) {
								if (strValidChars.indexOf(strString.charAt(i)) == -1) {
									alert("The value in the Images Per Page field doesn't seems to be valid number. An invalid number will be replaced by default value(10).");
									break;							  	
								}
							}
						}
						-->
					</script>
					<form name="edit" method="POST" action="./+edit">
					<table>
						<tr><th colspan=2>Edit gallery name and description</th></tr>
						<tr>
							<td>New Gallery Name</td>
							<td><input type='text' name="gallName" value='{$result['gallery_name']}'></td>
						</tr>
						<tr>
							<td>New Gallery Description</td>
							<td><input type='text' name="gallDesc" value='{$result['gallery_desc']}'></td>
						</tr>
						<tr>
							<td>Show Gallery views ?</td>
							<td><input type="checkbox" name="allowViews" $checkViews></td>
						</tr>
						<tr>
							<td>Show Gallery rating ?</td>
							<td><input type="checkbox" name="allowRatings" $checkRatings></td>
						</tr>
						<tr>
							<td>Images Per Page</td>
							<td><input type="text" id=perPage name="imagesPerPage" value='{$result['imagesPerPage']}'></td>
						</tr>
						<tr>
							<td><input type="submit" name="btnEditGallname" value="Save Settings"></td>
						</tr>
					</table>
					</form>
					<br /><br />
GALFORM;
		$gallQuery2 = "SELECT * FROM `gallery_pics` where `page_modulecomponentid`= '$this->moduleComponentId'";
		$gallResult2 = mysql_query($gallQuery2);
		$fileArray = array ();
		while ($row2 = mysql_fetch_assoc($gallResult2))
			$fileArray[] = $row2;
		if ($fileArray) {
			for ($i = 0; $i < count($fileArray); $i++) {
				$galleryFilename = $fileArray[$i]['upload_filename'];
				$galleryComment = $fileArray[$i]['gallery_filecomment'];
				$galleryComment = $galleryComment!=""?$galleryComment:"< No Comments >";
				
				$content2 .= "<div class='galleryimagebox'><form name=\"edit\" method=\"POST\" action=\"./+edit\">";
				$content2 .=<<<IMGFORM
				<span style="float:left">
					<center>
						<img src="thumb_$galleryFilename" alt="$galleryFilename" title="Click on the image to delete it"/>
					</center>
					<div class="highslide-caption" id="caption$i">$galleryComment</div>
					<input type="hidden" name="imagename" value="$galleryFilename" />
					<input type="text" name="desc">
					<br/><input type="submit" name="btnEditComment" value="Update comment">
					<input type="submit" name="btnDeleteImage" value="Delete" />
				</span>
IMGFORM;
				$content2 .= "</form></div>";
			}
		}
		return $content2."</fieldset>";
	}
	public function deleteModule($moduleComponentId) {
		global $sourceFolder;
		require_once("$sourceFolder/upload.lib.php");
		$arr = getUploadedFiles($moduleComponentId, 'gallery');
		$content = true;
		for ($c = 0; $c < count($arr); $c++) {
			$content = deleteFile($moduleComponentId, 'gallery', $arr[$c]['upload_filename']) && $content;
		}
		$gallQuery = "DELETE FROM `gall_name` where `page_modulecomponentid`='$moduleComponentId'";
		$gallResult = mysql_query($gallQuery);
		$gallQuery2 = "DELETE FROM `gall_pics` where `page_modulecomponentid`='$moduleComponentId'";
		$gallResult2 = mysql_query($gallQuery2);
		return $content;
	}
	public function copyModule($moduleComponentId,$newId) {
		$gallQuery = "SELECT * FROM `gallery_pics` WHERE page_modulecomponentid = '" . $moduleComponentId."'";
		$gallResult = mysql_query($gallQuery);
		$gallRow = mysql_fetch_assoc($gallResult);
		$destinationPage_moduleComponentId = $newId;
		while ($gallRow) {
			fileCopy($moduleComponentId, 'gallery', $gallRow['upload_filename'], $destinationPage_moduleComponentId, 'gallery', $gallRow['upload_filename'], $this->userId);
			$thumb ="thumb_".$gallRow['upload_filename'];
			fileCopy($moduleComponentId, 'gallery', $thumb, $destinationPage_moduleComponentId, 'gallery', $gallRow['upload_filename'], $this->userId);
		}
		return true;
	}
}
