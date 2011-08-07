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
 * @author Balanivash<balanivash@gmail.com>
 * For more details, see README
 */


class share implements module, fileuploadable  {
	private $userId;
	private $moduleComponentId;
	private $action;

	public function getHtml($gotuid, $gotmoduleComponentId, $gotaction) {
		$this->userId = $gotuid;
		$this->moduleComponentId = $gotmoduleComponentId;
		$this->action = $gotaction;
		if ($this->action == "edit")
			return $this->actionEdit();
		if ($this->action == "moderate")
			return $this->actionModerate(); 
		return $this->actionView();	
	}
	public static function getFileAccessPermission($pageId,$moduleComponentId,$userId, $fileName) {
		return getPermissions($userId, $pageId, "view");
		}
	public static function getUploadableFileProperties(&$fileTypesArray,&$maxFileSizeInBytes) {
		$fileTypesArray = array('jpg','jpeg','png','doc','pdf','gif','bmp','css','js','html','xml','ods','odt','oft','pps','ppt','tex','tiff','txt','chm','mp3','mp2','wave','wav','mpg','ogg','mpeg','wmv','wma','wmf','rm','avi','gzip','gz','rar','bmp','psd','bz2','tar','zip','swf','fla','flv','eps','xcf','xls','exe','7z');
		$maxFileSizeInBytes = 30*1024*1024;
	}
	function renderComment($id,$user,$timestamp,$comment,$file,$action="view") {
	$delete = '';
	if($action=="moderate")
	{
		global $ICONS;
		$delete  = "<a class='commentdelete' href='./+moderate&file=$file&delComment=$id'>{$ICONS['Delete']['large']}</a>";
	}
	$name = getUserFullName($user);	
	$comment = censor_words($comment);
	$ret = <<<RET
<div class="share_comment">
<fieldset>
<span class="share_comment_info">
Posted By: $name on $timestamp
</span>
<br/><span class="share_comment_content">
$comment
</span>
$delete</fieldset>
</div>
RET;
		return $ret;
	}	
	function commentBox($file_id) {
		global $sourceFolder;
		require_once("$sourceFolder/common.lib.php");
		$user = getUserName($this->userId);
		$ret = <<<RET
<script type='text/javascript'>
$(document).ready(function() {
$('#comment').autoResize({
    // On resize:
    onResize : function() {
        $(this).css({opacity:0.8});
    },
    // After resize:
    animateCallback : function() {
        $(this).css({opacity:1});
    },
    // Quite slow animation:
    animateDuration : 300,
    // More extra space:
    extraSpace : 25,
    limit : 200
});
});
</script>
<fieldset><legend>New Comment</legend>
<form method=POST action='./+view&file=$file_id&subaction=postcomment'>
<table width=100%>
<tr><td>Posted By:</td><td><input type=text disabled="disabled" value="$user" style="color:#000;background:#ddd;"></td></tr>
<tr><td>Comment:</td><td><textarea name='comment' id='comment' style="width: 360px; padding-top: 10px; padding-right: 10px; padding-bottom: 10px; padding-left: 10px; font-family: sans-serif; font-size: 1.2em; resize: none; height: 50px; display: block; ">Enter your comment here...</textarea></td>
<input type="hidden" name="file_id" value="$file_id">
</table>
<input type=submit name=btnSubmit value=Post style="padding:3px 10px 3px 10px;font-family: sans-serif; font-size: 1.2em;">
</form>
</fieldset>
RET;
		return $ret;
	}
	function renderField($row,$action="view")
	{
		$username = getUserFullName($row['upload_userid']);
		$content = "<fieldset><div id='file'><b>{$row['file_name']}</b><br />Uploaded by: {$username} <br /> {$row['file_desc']}<br /><a href=\"./+$action&file={$row['file_id']}\"><input type='submit' value='Discussion'></a><a href=\"./{$row['upload_filename']}\" target='_blank'><input type='submit' value='Download'></a>";
		if($action=="moderate")
			$content .="<a href=\"./+moderate&delfile={$row['file_id']}\"><input type='submit' value='Delete'></a>";
		$content .="</div></fieldset>";
		return $content;	
	}
	public function actionView() {
	global $sourceFolder,$urlRequestRoot, $moduleFolder, $cmsFolder;
	$temp = $urlRequestRoot . "/" . $cmsFolder . "/" . $moduleFolder ."/share";
	require_once($sourceFolder."/upload.lib.php");		
	$module_ComponentId = $this->moduleComponentId;
	$userId = $this->userId;
	if(isset($_GET['subaction'])&&($_GET['subaction']=="add_file"))
	{
	if(!isset($_FILES['upload_file']))
		displayerror("No File Uploaded");
	else{
	$query = "SELECT * FROM `share` WHERE `page_modulecomponentid` = '$module_ComponentId'";
	$result = mysql_query($query) or displayerror("Error in view");
	$result = mysql_fetch_array($result) or displayerror("Error in view");
	$maxFileSizeInBytes = $result[3];
	if(trim($result[2])=="") $uploadableFileTypes = false;
		else {
			$uploadableFileTypes = explode( "|" , $result[2] );
			if(count($uploadableFileTypes)==0) $uploadableFileTypes = false;
		}
	$uploadFileName = upload($module_ComponentId, "share", $userId, 'upload_file', $maxFileSizeInBytes , $uploadableFileTypes);
	if (is_array($uploadFileName) && isset ($uploadFileName[0])) {
				if($_POST['file_name']=="")
					$file_name = $uploadFileName[0];
				else
					$file_name = safe_html($_POST['file_name']);
				$file_desc = safe_html($_POST['file_desc']);

				$uploadQuery = "INSERT INTO `share_files` (`page_modulecomponentid`, `upload_filename`, `file_name`, `file_desc`, `upload_userid`) VALUES('$module_ComponentId', '$uploadFileName[0]','$file_name','$file_desc','{$this->userId}')";
				$uploadResult = mysql_query($uploadQuery);
		if(mysql_affected_rows()>0)
			displayinfo("Successfully Uploaded ".$file_name);
		else
			displayerror("File Not Uploaded");

		}
	else displayerror("Sorry!! Some error has occured when uploading the file.");
	}
	}
	if(isset($_POST['btnSubmit'])) {
			$id = mysql_fetch_array(mysql_query("SELECT MAX(`comment_id`) AS MAX FROM `share_comments`"));
			$id = $id['MAX'] + 1;
			$user = $this->userId;
			$comment = escape(safe_html($_POST['comment']));
			$file_id = escape($_POST['file_id']);
			mysql_query("INSERT INTO `share_comments`(`comment_id`,`file_id`,`page_modulecomponentid`,`comment`,`userid`) VALUES('$id','$file_id','{$module_ComponentId}','$comment','$user')") or die(mysql_error());
			if(mysql_affected_rows())
				displayinfo("Post successful");
			else
				displayerror("Error in posting comment");
		}
	if(isset($_GET['file']))
	{
		$file_id = escape($_GET['file']);
		$query = "SELECT * FROM `share_files` WHERE `file_id` = '$file_id'";
		$result = mysql_query($query);
		if(mysql_num_rows($result)<0)
			{
			displayerror("Sorry!!! No such file found");
			}
		else
			{
			$result = mysql_fetch_array($result);
			$username = getUserFullName($this->userId);
			$content = "<script type=\"text/javascript\" languauge=\"javascript\" src=\"$temp/textarea_resize.js\"></script>";
			$content .= "<div id='file'><b>{$result[3]}</b><br/>{$result[4]}<br /><br />Uploaded by: $username<br /><br /><a href=\"./{$result[2]}\" target='_blank'><input type='submit' value='Download'></a></div> ";
			$comment_query = "SELECT * FROM `share_comments` WHERE `page_modulecomponentid` = '$module_ComponentId' AND `file_id` = '{$result[0]}'";
			$comment_result = mysql_query($comment_query);
			if(mysql_num_rows($comment_result)>0)
			$content .= "<fieldset><legend>Comments</legend>";
			while($row = mysql_fetch_array($comment_result))
				$content .= $this->renderComment($row['comment_id'],$row['userid'],$row['comment_datetime'],$row['comment'],$file_id);
			if(mysql_num_rows($comment_result)>0)
				$content .= "</fieldset>";
			$content .= $this->commentBox($file_id);
			return $content;
			}	
	}
	$query = "SELECT * FROM `share` WHERE `page_modulecomponentid` = '$module_ComponentId'";
	$result = mysql_query($query) or displayerror(mysql_error()." Error in share.lib.php L:187");
	$result = mysql_fetch_array($result);
	$file_types = preg_replace('/\|/',', ',$result['file_type']);
	$upload_form =<<<FORM
<script type="text/javascript" language="javascript">
function checkForm()
{
	var desc = document.add_file.file_desc.value;
	var length = desc.length;
	if(length<50)
	{
		document.getElementById('file_desc').focus();
		alert("Please enter File Description (min. 50 characters)");
		return false;
	}
	return true;
}
</script>
<fieldset id='upload_form'>
	<legend>Upload File</legend>
	<form name='add_file' method="POST" action="./+view&subaction=add_file" enctype="multipart/form-data">
	<table width='100%'>	
	<tr><td>Add new File</td><td><input type='file' name='upload_file' id='upload_file' /></td></tr>
	<tr><td>File Name </td><td><input type='text' name='file_name' id='file_name' /></td></tr>
	<tr><td>Description </td><td><textarea name='file_desc' id='file_desc' rows=4 cols=50 >Enter the file description here...</textarea></td></tr>
	<tr><td colspan='2' align='center'><input type='submit' name='add_file' value='Upload' onclick="return checkForm();"/></td></tr>
	</table>
	</form>
</fieldset>
FORM;
	$content = "<table width=100%><tr><td colspan='2'><b>{$result['page_desc']}</b><br /></td></tr><tr><td width=150px>Uploadable File Typles </td><td>{$file_types}</td></tr><tr><td>Max. file size </td><td> {$result['maxfile_size']} bytes</td></tr></table>";
	$content .= $upload_form;
	$content_query = "SELECT * FROM `share_files` WHERE `page_modulecomponentid` = '$module_ComponentId'";
	$content_result = mysql_query($content_query) or displayerror("Error is retriving info from database. Please try later..");
	if(mysql_num_rows($content_result)<=0)
		$content .= "No Files found..";
	else{

		$content .= "<div id='file_container'>";
		while($row = mysql_fetch_array($content_result))
			$content .= $this->renderField($row);		
		$content .= "</div>";
	}
	
	return $content;	
	}
	public function actionModerate() {
	$module_ComponentId = $this->moduleComponentId;
	global $sourceFolder;	
	require_once($sourceFolder."/upload.lib.php");
	if(isset($_GET['delfile']))
	{
		$file_id = escape($_GET['delfile']);
		$query = "SELECT * FROM `share_files` WHERE `file_id` = '$file_id'";
		$result = mysql_query($query);
		$result = mysql_fetch_array($result);
		if(deleteFile($module_ComponentId,"share",$result['upload_filename']))
			{
			$del_query = "DELETE FROM `share_files` WHERE `file_id` = '$file_id'";
			$del_result = mysql_query($del_query) or displayerror(mysql_error()."Error in share.lib.php L:240");
			$del_comment = "DELETE FROM `share_comments` WHERE `file_id` = '$file_id'";
			$del_comment_result = mysql_query($del_comment) or displayerror(mysql_error()."error in  L:242");
			if(!$del_result||!$del_comment_result)
				displayerror("Some data has not been deleted properly!!!");
			else
				displayinfo("File deleted Successfully!!!");
			}
		else
			displayerror("File not deleted. Try again later..." );
	}
	if(isset($_GET['delComment']))
	{
		$commentid = escape($_GET['delComment']);
		$query = "DELETE FROM `share_comments` WHERE `comment_id` = $commentid";
		$result = mysql_query($query);
		if(mysql_affected_rows()<0)
			displayerror("Error in deleting the comment");
		else
			displayinfo("Succesfully deleted comment");	
	}
	if(isset($_GET['file']))
	{
		$file_id = escape($_GET['file']);
		$query = "SELECT * FROM `share_files` WHERE `file_id` = '$file_id'";
		$result = mysql_query($query);
		if(mysql_num_rows($result)<0)
			{
			displayerror("Sorry!!! No such file found");
			}
		else
			{
			$result = mysql_fetch_array($result);
			$username = getUserFullName($this->userId);
			$content = "<div id='file'><b>{$result[3]}</b><br/>{$result[4]}<br /><br />Uploaded by: $username<br /><br /><a href=\"./{$result[2]}\" target='_blank'><input type='submit' value='Download'></a></div> ";
			$comment_query = "SELECT * FROM `share_comments` WHERE `page_modulecomponentid` = '$module_ComponentId' AND `file_id` = '{$result[0]}'";
			$comment_result = mysql_query($comment_query) or die(mysql_error());
			if(mysql_num_rows($comment_result)>0)
			$content .= "<fieldset><legend>Comments</legend>";
			while($row = mysql_fetch_array($comment_result))
				$content .= $this->renderComment($row['comment_id'],$row['userid'],$row['comment_datetime'],$row['comment'],$file_id,'moderate');
			if(mysql_num_rows($comment_result)>0)
				$content .= "</fieldset>";
			return $content;
			}	
	}
	$query = "SELECT * FROM `share` WHERE `page_modulecomponentid` = '$module_ComponentId'";
	$result = mysql_query($query) or displayerror(mysql_error()." Error in share.lib.php L:187");
	$result = mysql_fetch_array($result);
	$file_types = preg_replace('/\|/',', ',$result['file_type']);
	$content = "<table width=100%><tr><td colspan='2'><b>{$result['page_desc']}</b><br /></td></tr><tr><td width=150px>Uploadable File Typles </td><td>{$file_types}</td></tr><tr><td>Max. file size </td><td> {$result['maxfile_size']} bytes</td></tr></table>";
	$content_query = "SELECT * FROM `share_files` WHERE `page_modulecomponentid` = '$module_ComponentId'";
	$content_result = mysql_query($content_query) or displayerror("Error is retriving info from database. Please try later..");
	if(mysql_num_rows($content_result)<=0)
		$content .= "No Files found..";
	else{

		$content .= "<div id='file_container'>";
		while($row = mysql_fetch_array($content_result))
			$content .= $this->renderField($row,"moderate");		
		$content .= "</div>";
	}
	
	return $content;	
	}
	public function actionEdit()
	{
	$module_ComponentId = $this->moduleComponentId;
	if(isset($_POST['edit_share']))
	{
	$desc = safe_html($_POST['share_desc']);
	$ftype = escape($_POST['file_type']);
	if((strlen($desc)<50)||(strlen($ftype)==0))
		displayerror("Could not update the page. Either the share description or file type doesnot meet the requirements!!");
	else {	
	$max_size = escape($_POST['file_size']);
	$query = "UPDATE `share` SET `page_desc` = '$desc', `file_type` = '$ftype', `maxfile_size` = '$max_size' WHERE `page_modulecomponentid` = '$module_ComponentId'";
	$result = mysql_query($query);
	if(mysql_affected_rows()<0)
		displayerror("Error in updating the database. Please Try again later");
	else
		displayinfo("All settings updated successfully");
		}
	}
	$query = "SELECT * FROM `share` WHERE `page_modulecomponentid` = '$module_ComponentId'";
	$result = mysql_query($query) or displayerror(mysql_error()." Error in share.lib.php L:322");
	$result = mysql_fetch_array($result) or displayerror(mysql_error()."Error in share.lib.php L:323");
	$edit_form =<<<EDIT
<script type="text/javascript" language="javascript">
function checkForm()
{
	var desc = document.edit_share.share_desc.value;
	var length = desc.length;
	if(length<50)
	{
		document.getElementById('share_desc').focus();
		alert("Please enter the Share Description (min. 50 characters)");
		return false;
	}
	var type = document.edit_share.file_type.value;
	var tlength = type.length;
	if(tlength==0)
	{
		document.getElementById('file_type').focus();
		alert("Please enter the File types that can be uploaded");
		return false;
	}
	return true;
}
</script>
	<fieldset><legend>EDIT SHARE</legend>
	<form method="POST" name="edit_share" action="./+edit">
	<table>
	<tr><td>Share Description </td><td><textarea name="share_desc" id="share_desc" cols="50" rows="5" class="textbox" >{$result['page_desc']}</textarea></td></tr>
	<tr><td>Uploadable FIle types</td><td><input type='text' name="file_type" id="file_type" value={$result['file_type']}></td></tr>
	<tr><td>Max File Size(in bytes)</td><td><input type='text' name="file_size" id="file_size" value={$result['maxfile_size']}></td></tr>
	<tr><td colspan=2 style="text-align:center"><input type="submit" value="submit" name="edit_share" onclick="return checkForm();"><input type="reset" value="Reset"></td></tr>
	</table>	
	</form>	
	</fieldset>
EDIT;
	return $edit_form;
	}
	public function createModule($compId) {
		$query = "INSERT INTO `share` (`page_modulecomponentid`,`page_desc`,`file_type`,`maxfile_size` )VALUES ('$compId','Coming Soon!!!','doc|docx','2000000')";
		$result = mysql_query($query) or die(mysql_error() . " share.lib.php L:372");
	}

	public function deleteModule($moduleComponentId) {
		return true;
	}
	
	public function copyModule($moduleComponentId,$newId) {
		return true;
	}
}


?>
