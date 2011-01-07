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
 * @copyright (c) 2008 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
class article implements module, fileuploadable {
	private $userId;
	private $moduleComponentId;
	private $action;

	public function getHtml($gotuid, $gotmoduleComponentId, $gotaction) {
	
		$this->userId = $gotuid;
		$this->moduleComponentId = $gotmoduleComponentId;
		$this->action = $gotaction;

		if ($this->action == "view")
			return $this->actionView();
		if ($this->action == "edit")
			return $this->actionEdit();
	}

	/**
	 * Funtion which tells the cms uploaded file access is defined by which action
	 */
	public static function getFileAccessPermission($pageId,$moduleComponentId,$userId, $fileName) {
		return getPermissions($userId, $pageId, "view");
	}

	public static function getUploadableFileProperties(&$fileTypesArray,&$maxFileSizeInBytes) {
		$fileTypesArray = array('jpg','jpeg','png','doc','pdf','gif','bmp','css','js','html','xml','ods','odt','oft','pps','ppt','tex','tiff','txt','chm','mp3','mp2','wave','wav','mpg','ogg','mpeg','wmv','wma','wmf','rm','avi','gzip','gz','rar','bmp','psd','bz2','tar','zip','swf','fla','flv','eps','xcf','xls','exe','7z');
		$maxFileSizeInBytes = 30*1024*1024;
	}
	
	function isCommentsEnabled() {
		$result = mysql_fetch_array(mysql_query("SELECT `allowComments` FROM `article_content` WHERE `page_modulecomponentid` = '{$this->moduleComponentId}'"));
		return $result['allowComments'];
	}
	
	function setCommentEnable($val) {
		mysql_query("UPDATE `article_content` SET `allowComments` = $val WHERE `page_modulecomponentid` = '{$this->moduleComponentId}'");
	}
	
	function renderComment($id,$user,$timestamp,$comment,$delete=0) {
	global $ICONS;
	if($delete==1)
	{
		global $urlRequestRoot,$cmsFolder,$templateFolder;
		$delete  = "<a class='commentdelete' href='./+edit&delComment=$id'>{$ICONS['Delete']['large']}</a>";
	}
	else $delete="";
		
		$ret = <<<RET
<div class="articlecomment">
<span class="articlecomment_info">
Posted By: $user on $timestamp
</span>
<br/><span class="articlecomment_content">
$comment
</span>
$delete
</div>
RET;
		return $ret;
	}
	
	function commentBox() {
		global $sourceFolder;
		require_once("$sourceFolder/common.lib.php");
		$user = getUserName($this->userId);
		$ret = <<<RET
<fieldset><legend>New Comment</legend>
<form method=POST action='./+view&subaction=postcomment'>
<table width=100%>
<tr><td>Posted By:</td><td><input type=text disabled="disabled" value="$user"></td></tr>
<tr><td>Comment:</td><td><textarea name=comment rows=4 cols=50>Enter your comment here...</textarea></td>
</table>
<input type=submit name=btnSubmit value=Post>
</form>
</fieldset>
RET;
		return $ret;
	}
	
	public function actionView($text="") {
	
	if (isset($_GET['draft']) && isset ($_POST['CKEditor1'])){
				
				//$query = "UPDATE `article_draft` SET `draft_content` = '" . $_POST["CKEditor1"] . "' WHERE `page_modulecomponentid` =".$this->moduleComponentId;
				$query="SELECT MAX(draft_number) AS MAX FROM `article_draft` WHERE page_modulecomponentid =" . $this->moduleComponentId;
			$result = mysql_query($query);
			if(!$result) { displayerror(mysql_error() . "article.lib L:44"); return; }
			if(mysql_num_rows($result))
			{
				$drow = mysql_fetch_assoc($result);
				$draftId = $drow['MAX'] + 1;
			}
			else $draftId=1;
			
				$query = "INSERT INTO `article_draft` (`page_modulecomponentid`,`draft_number`,`draft_content`,`draft_lastsaved`,`user_id`) VALUES ('".$this->moduleComponentId."','".$draftId."','".$_POST['CKEditor1']."',now(),'".$this->userId."')";
				$result = mysql_query($query) or die(mysql_error());
					if(mysql_affected_rows() < 1)
					displayerror("Unable to draft the article");
				
				}
		if($this->isCommentsEnabled() && isset($_POST['btnSubmit'])) {
			$id = mysql_fetch_array(mysql_query("SELECT MAX(`comment_id`) AS MAX FROM `article_comments`"));
			$id = $id['MAX'] + 1;
			$user = getUserName($this->userId);
			$comment = escape(safe_html($_POST['comment']));
			mysql_query("INSERT INTO `article_comments`(`comment_id`,`page_modulecomponentid`,`user`,`comment`) VALUES('$id','{$this->moduleComponentId}','$user','$comment')");
			if(mysql_affected_rows())
				displayinfo("Post successful");
			else
				displayerror("Error in posting comment");
		}
		if($text==""){
			$query = "SELECT article_content,article_lastupdated FROM article_content WHERE page_modulecomponentid=" . $this->moduleComponentId;
			$result = mysql_query($query);
			if($row = mysql_fetch_assoc($result)) {
				$text = $row['article_content'];
				$text = censor_words($text);
				global $PAGELASTUPDATED;
				$PAGELASTUPDATED = $row['article_lastupdated'];
			}
			else return "Article not yet created.";
		}
		global $sourceFolder;
		global $moduleFolder;
		require_once($sourceFolder."/latexRender.class.php");
		if (get_magic_quotes_gpc())
			$text = stripslashes($text);
		$render = new latexrender();
		$ret = $render->transform($text);
		
		require_once($sourceFolder."/googleMaps.class.php");
		$maps = new googlemaps();
		$ret = $maps->render($ret);
		
		
		if($this->isCommentsEnabled()) {
			$comments = mysql_query("SELECT `comment_id`,`user`,`timestamp`,`comment` FROM `article_comments` WHERE `page_modulecomponentid` = '{$this->moduleComponentId}' ORDER BY `timestamp`");
			if(mysql_num_rows($comments)>0)
				$ret .= "<fieldset><legend>Comments</legend>";
			while($row = mysql_fetch_array($comments))
				$ret .= $this->renderComment($row['comment_id'],$row['user'],$row['timestamp'],$row['comment']);
			if(mysql_num_rows($comments)>0)
				$ret .= "</fieldset>";
			$ret .= $this->commentBox();
		}
		return $ret;
	}
	
	
	public function actionEdit() {
		global $sourceFolder,$ICONS;
		//require_once("$sourceFolder/diff.lib.php");
		require_once($sourceFolder."/upload.lib.php");
		
		if (isset($_GET['deldraft']))
		{
		$dno = escape($_GET['dno']);
		$query = "DELETE FROM `article_draft` WHERE `page_modulecomponentid`=". $this->moduleComponentId." AND `draft_number`=".$dno;
		$result = mysql_query($query) or die(mysql_error());
		}
		
		global $ICONS;
		$header = <<<HEADER
		<fieldset><legend><a name='topquicklinks'>Quicklinks</a></legend>
		<table class='iconspanel'>
		<tr>
		<td><a href='#editor'><div>{$ICONS['Edit Page']['large']}<br/>Edit Page</div></a></td>
		<td><a href='#files'><div>{$ICONS['Uploaded Files']['large']}<br/>Manage Uploaded Files</div></a></td>
		<td><a href='#drafts'><div>{$ICONS['Drafts']['large']}<br/>Saved Drafts</div></a></td>
		<td><a href='#revisions'><div>{$ICONS['Page Revisions']['large']}<br/>Page Revisions</div></a></td>
		<td><a href='#comments'><div>{$ICONS['Page Comments']['large']}<br/>Page Comments</div></a></td>
		</tr>
		</table>
	
        
		</fieldset><br/><br/>
HEADER;
		
		submitFileUploadForm($this->moduleComponentId,"article",$this->userId,UPLOAD_SIZE_LIMIT);
		if(isset($_GET['delComment']) && $this->userId == 1) {
			mysql_query("DELETE FROM `article_comments` WHERE `comment_id` = '".escape($_GET['delComment'])."'");
			if(mysql_affected_rows())
				displayinfo("Comment deleted!");
			else
				displayerror("Error in deleting comment");
		}
		if (isset($_GET['preview']) && isset ($_POST['CKEditor1'])) {
			return "<div id=\"preview\" class=\"warning\"><a name=\"preview\">Preview</a></div>".$this->actionView(stripslashes($_POST[CKEditor1])).$this->getCkBody(stripslashes($_POST[CKEditor1]));
		}
		if (isset($_GET['version'])) {
			$revision = $this->getRevision($_GET['version']);
			return "<div id=\"preview\" class=\"warning\"><a name=\"preview\">Previewing Revision Number ".$_GET['version']."</a></div>".$this->actionView($revision).$this->getCkBody($revision);
		}
		if (isset($_GET['dversion'])) {
			$draft = $this->getDraft($_GET['dversion']);
			displayinfo("Viewing Draft number ".$_GET['dversion']);
			return $header.$this->getCkBody($draft);
		}

		
		if (isset ($_POST['CKEditor1'])) {


			/*Save the diff :-*/
			$query = "SELECT article_content FROM article_content WHERE page_modulecomponentid=" . $this->moduleComponentId;
			$result = mysql_query($query);
			$row = mysql_fetch_assoc($result);
			$diff = mysql_escape_string($this->diff($_POST['CKEditor1'],$row['article_content']));
			$query="SELECT MAX(article_revision) AS MAX FROM `article_contentbak` WHERE page_modulecomponentid =" . $this->moduleComponentId;
			$result = mysql_query($query);
			if(!$result) { displayerror(mysql_error() . "article.lib L:44"); return; }
			if(mysql_num_rows($result))
			{
				$row = mysql_fetch_assoc($result);
				$revId = $row['MAX'] + 1;
			}
			else $revId=1;


			$query = "INSERT INTO `article_contentbak` (`page_modulecomponentid` ,`article_revision` ,`article_diff`,`user_id`)
VALUES ('$this->moduleComponentId', '$revId','$diff','$this->userId')";
			$result = mysql_query($query);
			if(!$result) { displayerror(mysql_error() . "article.lib L:44"); return; }

		/*Save the diff end.*/

			$query = "UPDATE `article_content` SET `article_content` = '" . $_POST["CKEditor1"] . "' WHERE `page_modulecomponentid` =$this->moduleComponentId ";
			$result = mysql_query($query);
			if(mysql_affected_rows() < 1)
				displayerror("Unable to update the article");
			else {
				
				/* Index the page by sphider */
				$page = replaceAction(selfURI(),"edit","view");
				global $sourceFolder,$moduleFolder;
				require_once("$sourceFolder/$moduleFolder/search/admin/spider.php");
				index_url($page, 0, 0, '', 0, 0, 1);
			}
			return $this->actionView();
		}
		$fulleditpage = $this->getCkBody();
		
		$commentsedit = "<fieldset><legend><a name='comments'>{$ICONS['Page Comments']['small']}Comments</a></legend>";
		
		if($this->isCommentsEnabled()) {
			$comments = mysql_query("SELECT `comment_id`,`user`,`timestamp`,`comment` FROM `article_comments` WHERE `page_modulecomponentid` = '{$this->moduleComponentId}' ORDER BY `timestamp`");
			if(mysql_num_rows($comments)==0)
				$commentsedit.= "No comments have been posted !";
			
			
			while($row = mysql_fetch_array($comments))
			{
				$commentsedit .= $this->renderComment($row['comment_id'],$row['user'],$row['timestamp'],$row['comment'],1);
				
			}

		}
		else $commentsedit .= "Comments are disabled for this page! You can allow comments from <a href='./+settings'>pagesettings</a>.";
		$commentsedit .="</fieldset>";
		$top="<a href='#topquicklinks'>Top</a>";
		$fulleditpage .= $commentsedit.$top;
		
		return $header.$fulleditpage;

	}

	public function diff($new,$old)
	{
/* diff new old > D
 * patch new D gives old
 */
 		/*global $sourceFolder;
		global $uploadFolder;


		$uploadDir = $sourceFolder."/".$uploadFolder;

 		 if (!file_exists($uploadDir."/tmp"))
		 {
		 	mkdir($uploadDir ."/tmp", 0755);

		 }
		// $new=htmlspecialchars($new, ENT_QUOTES);
		// $old=htmlspecialchars($old, ENT_QUOTES);
		 $fileNew = "newFile";
		 $fileOld="oldFile";
		$fpn = fopen($uploadDir ."/tmp/".$fileNew, 'w') or die("can't open new file for writing ARTICLE L:105");
		fwrite($fpn,$new);
		fclose($fpn);

		$fpo = fopen($uploadDir ."/tmp/".$fileOld, 'w') or die("can't open old file for writing ARTICLE L:109");
		fwrite($fpo,$old);
		fclose($fpo);

		$cmd="diff ".$uploadDir ."/tmp/".$fileNew.' '.$uploadDir ."/tmp/".$fileOld."";

		$diff = shell_exec(''.$cmd.'');

		$fpo = fopen($uploadDir ."/tmp/diffGenerated", 'w') or die("can't open old file for writing ARTICLE L:109");
		fwrite($fpo,$diff);
		fclose($fpo);
		$diff=addslashes($diff);*/
		return $old;
	}
	public function patch($article,$patch) {
/* patch article patch
 * * patch new D gives old
 *
 *  */
// 		$article=htmlspecialchars($article, ENT_QUOTES);
 	/*	global $sourceFolder;
		global $uploadFolder;
		$patch=stripslashes($patch);

		//$article=$article."\n";
		$uploadDir = $sourceFolder."/".$uploadFolder;

		 if (!file_exists($uploadDir."/tmp"))
		 {
		 	mkdir($uploadDir ."/tmp", 0755);

		 }



		$fileNew="newFile";
		$fileOld="patchFile";

		$fpn = fopen($uploadDir ."/tmp/".$fileNew, 'w') or die("can't open new file for writing ARTICLE L:149");
		fwrite($fpn,$article);
		fclose($fpn);

		$fpo = fopen($uploadDir ."/tmp/".$fileOld, 'w') or die("can't open old file for writing ARTICLE L:153");
		fwrite($fpo,$patch);
		fclose($fpo);
		$cmd="patch ". $uploadDir ."/tmp/".$fileNew.' '.$uploadDir ."/tmp/".$fileOld."";
		$p=shell_exec(''.$cmd.'');
		echo$p;
		$fpn = fopen($uploadDir ."/tmp/".$fileNew, 'r') or die("can't open new file for reading ARTICLE L:160");
		$originalArticle=fread($fpn,filesize($uploadDir ."/tmp/".$fileNew));
		fclose($fpn);
//		$originalArticle=htmlspecialchars_decode($originalArticle);*/
//		return $originalArticle;
		return $patch;
	}
	public function getRevision($revisionNo) {
		$currentquery = "SELECT article_content FROM article_content WHERE page_modulecomponentid=" . $this->moduleComponentId;
		$currentresult = mysql_query($currentquery);
		$currentrow = mysql_fetch_assoc($currentresult);
		$revision = $currentrow['article_content'];
		$diffquery = "SELECT * FROM `article_contentbak` WHERE `page_modulecomponentid`= $this->moduleComponentId AND article_revision >= '$revisionNo' ORDER BY article_revision DESC";
		$diffresult = mysql_query($diffquery);
		while($diffrow = mysql_fetch_assoc($diffresult)) {
			$revision = $this->patch($revision,$diffrow['article_diff']);
		}
		return $revision;
	}
	
	public function getDraft($draftNo) {
		$currentquery = "SELECT draft_content FROM article_draft WHERE page_modulecomponentid=" . $this->moduleComponentId;
		$currentresult = mysql_query($currentquery);
		$currentrow = mysql_fetch_assoc($currentresult);
		$draft = $currentrow['draft_content'];
		$diffquery = "SELECT * FROM `article_draft` WHERE `page_modulecomponentid`= $this->moduleComponentId AND draft_number >= '$draftNo' ORDER BY draft_number DESC";
		$diffresult = mysql_query($diffquery);
		while($diffrow = mysql_fetch_assoc($diffresult)) {
			$draft = $this->patch($draft,$diffrow['draft_content']);
		}
		return $draft;
	}
	
	public function getCkBody($content=""){
			global $sourceFolder;
			global $cmsFolder;
			global $moduleFolder;
			global $urlRequestRoot;
			global $ICONS;
			require_once ("$sourceFolder/$moduleFolder/article/ckeditor/ckeditor.php");
			if($content=="") {
				$query = "SELECT * FROM `article_content` WHERE `page_modulecomponentid`= $this->moduleComponentId";
				$result = mysql_query($query);
				$temp = mysql_fetch_assoc($result);
				$content = $temp['article_content'];
			}

			$CkForm =<<<Ck
						<form action="./+edit" method="post">
						<a name="editor"></a>
						<input type="button" value="Cancel" onclick="submitarticleformCancel(this);"><input type="submit" value="Save"><input type="button" value="Preview" onclick="submitarticleformPreview(this)"><input type="button" value="Draft" onclick="submitarticleformDraft(this);">
                        To upload files and images, go to the <a href="#files">files section</a>.
Ck;
			$top ="<a href='#topquicklinks'>Top</a>";
			$oCKEditor = new CKeditor();
			$oCKEditor->basePath = "$urlRequestRoot/$cmsFolder/$moduleFolder/article/ckeditor/";
			$oCKEditor->config['width'] = '100%';
			$oCKEditor->config['height'] = '300';
			$oCKEditor->returnOutput = true;
			$Ckbody = $oCKEditor->editor('CKEditor1',$content);

			$CkFooter =<<<Ck1
					      <input type="button" value="Cancel" onclick="submitarticleformCancel(this);"><input type="submit" value="Save"><input type="button" value="Preview" onclick="submitarticleformPreview(this)"><input type="button" value="Draft" onclick="submitarticleformDraft(this);">
					   		 </form>
					   	 <script language="javascript">
					    	function submitarticleformPreview(butt) {
					    		butt.form.action = "./+edit&preview=yes#preview";
					    		butt.form.submit();
					    	}
					    	function submitarticleformCancel(butt) {
					    		butt.form.action="./+view";
					    		butt.form.submit();
					    	}
					    	function submitarticleformDraft(butt) {
					    		butt.form.action="./+view&draft=yes";
					    		butt.form.submit();
					    	}
					    </script><br />
					    $top
					    <fieldset>
					        <legend><a name="files">{$ICONS['Uploaded Files']['small']}Uploaded Files</a></legend>
							
Ck1;
		$CkFooter .= getUploadedFilePreviewDeleteForm($this->moduleComponentId,"article",'./+edit');
		$CkFooter .= '<br />Upload files : <br />'.getFileUploadForm($this->moduleComponentId,"article",'./+edit',UPLOAD_SIZE_LIMIT,5).'</fieldset>';

		/* Revisions available */
		$revisionquery = "SELECT MAX(article_revision) AS MAX FROM `article_contentbak` where page_modulecomponentid = $this->moduleComponentId";
		$revisionresult = mysql_query($revisionquery);
		$revisionrow = mysql_fetch_assoc($revisionresult);
		$start = $revisionrow['MAX'] - 10;
		if(isset($_GET['revisionno']))
			$start = escape($_GET['revisionno']);
		if($start>$revisionrow['MAX']-9) $start = $revisionrow['MAX']-10;
		if($start<0) $start = 0;
		$count = 10;
		if(isset($_GET['count']))
			$count = escape($_GET['count']);
		if($count>($revisionrow['MAX']-$start+1)) $count = $revisionrow['MAX']-$start+1;
		$query = "SELECT article_revision,article_updatetime,user_id FROM `article_contentbak` where page_modulecomponentid = $this->moduleComponentId ORDER BY article_revision LIMIT $start,$count";
		$result = mysql_query($query);
		$revisionTable = "<fieldset>
					        <legend><a name='revisions'>{$ICONS['Page Revisions']['small']}Page Revisions : </a></legend>" .
					        		"<table border='1'><tr><td>Revision Number</td><td>Date Updated</td><td>User Fullname</td><td>User Email</td></tr>";
		while ($row = mysql_fetch_assoc($result)) {
			$revisionTable .= "<tr><td><a href=\"./+edit&version=".$row['article_revision']."#preview\">".$row['article_revision']."</a></td><td>".$row['article_updatetime']."</td><td>".getUserFullName($row['user_id'])."</td><td>".getUserEmail($row['user_id'])."</td></tr>";
		}
		$revisionTable .="</table>" .
				"<input type=\"button\" value=\"<<\" onclick=\"window.location='./+edit&revisionno=0'\" /> " .
				"<input type=\"button\" value=\"<\" onclick=\"window.location='./+edit&revisionno=".($start - 10)."'\" /> " .
				"<input type=\"button\" value=\">\" onclick=\"window.location='./+edit&revisionno=".($start + 10)."'\" /> " .
				"<input type=\"button\" value=\">>\" onclick=\"window.location='./+edit&revisionno=".($revisionrow['MAX']-10)."'\" /> " .
				"</fieldset>";
				
			/* Drafts available */
		$draftquery = "SELECT MAX(draft_number) AS MAX FROM `article_draft` where page_modulecomponentid = $this->moduleComponentId";
		$draftresult = mysql_query($draftquery);
		$draftrow = mysql_fetch_assoc($draftresult);
		$dstart = $draftrow['MAX'] - 10;
		if(isset($_GET['draftno']))
			$dstart = escape($_GET['draftno']);
		if($dstart>$draftrow['MAX']-9) $dstart = $draftrow['MAX']-10;
		if($dstart<0) $dstart = 0;
		$dcount = 10;
		if(isset($_GET['dcount']))
			$dcount = escape($_GET['dcount']);
		if($dcount>($draftrow['MAX']-$dstart+1)) $dcount = $draftrow['MAX']-$dstart+1;
		
		$query = "SELECT `draft_lastsaved`,`draft_number`,`user_id` FROM `article_draft` where `page_modulecomponentid` = $this->moduleComponentId ORDER BY `draft_lastsaved` LIMIT $dstart,$dcount";
		$result = mysql_query($query);
		$draftTable = "<fieldset>
					        <legend><a name='drafts'>{$ICONS['Page Revisions']['small']}Drafts Saved : </a></legend>" .
					        		"<table border='1'><tr><td>Draft Number</td><td>Date Drafted</td><td>User Fullname</td><td>User Email</td><td>Delete</td></tr>";
					    
		while ($row = mysql_fetch_assoc($result)) {
			$draftTable .= "<tr><td><a href=\"./+edit&dversion=".$row['draft_number']."#preview\">".$row['draft_number']."</a></td><td>".$row['draft_lastsaved']."</td><td>".getUserFullName($row['user_id'])."</td><td>".getUserEmail($row['user_id'])."</td><td><form action='./+edit&deldraft=yes&dno=".$row['draft_number']."' method='post'><input type='button' value='Delete' onclick='submitarticleformDeldraft(this);'></form>
		<script language='javascript'>
					    	function submitarticleformDeldraft(butt) {
					   		if(confirm('Are you sure you want to delete this draft ? '))
					    		butt.form.submit();
					    	}
		</script></td></tr>";
		}
		$draftTable .="</table>" .
				"<input type=\"button\" value=\"<<\" onclick=\"window.location='./+edit&draftnno=0'\" /> " .
				"<input type=\"button\" value=\"<\" onclick=\"window.location='./+edit&draftno=".($dstart - 10)."'\" /> " .
				"<input type=\"button\" value=\">\" onclick=\"window.location='./+edit&draftno=".($dstart + 10)."'\" /> " .
				"<input type=\"button\" value=\">>\" onclick=\"window.location='./+edit&draftno=".($draftrow['MAX']-10)."'\" /> " .
				"</fieldset>";

		/* Drafts end*/ 
		
		

		
		return  $CkForm . $Ckbody . $CkFooter.$draftTable.$top.$revisionTable.$top;
	}

	public function createModule(&$moduleComponentId) {
		$query = "SELECT MAX(page_modulecomponentid) as MAX FROM `article_content` ";
		$result = mysql_query($query) or die(mysql_error() . "article.lib L:73");
		$row = mysql_fetch_assoc($result);
		$compId = $row['MAX'] + 1;

		$query = "INSERT INTO `article_content` (`page_modulecomponentid` ,`article_content`, `allowComments`)VALUES ('$compId', 'Coming up Soon!!!','0')";
		$result = mysql_query($query) or die(mysql_error()."article.lib L:76");
		if (mysql_affected_rows()) {
			$moduleComponentId = $compId;
			return true;
		} else
			return false;
	}
	public function deleteModule($moduleComponentId) {
		$query = "DELETE FROM `article_content` WHERE `page_modulecomponentid`=$moduleComponentId";
		$result = mysql_query($query);
		if ((mysql_affected_rows()) >= 1)
		{
			$query = "DELETE FROM `article_comments` WHERE `page_modulecomponentid`=$moduleComponentId";
			$result = mysql_query($query);
			
		}
		else
			return false;
		
		/* Remove the indexing from sphider // Abhishek */
		$pageId=getPageIdFromModuleComponentId("article",$moduleComponentId);
		$path=getPagePath($pageId);
		global $urlRequestRoot;
		$delurl = "http://".$_SERVER['HTTP_HOST'].$urlRequestRoot."/home".$path;
		$query="SELECT link_id FROM `links` WHERE url='$delurl'";
		
		$result=mysql_query($query);
		if(mysql_num_rows($result)==0) return true; //Nothing to delete 
		$delids="";
		while($row=mysql_fetch_row($result))
			$delids.=$row[0].",";
		
		$delids=rtrim($delids,",");
		
		$query="DELETE FROM `links` WHERE url='$delurl'";
		
		mysql_query($query);
		for ($i=0;$i<=15; $i++) 
		{
			$char = dechex($i);
			$query="DELETE FROM `link_keyword$char` WHERE link_id IN ($delids)";
			
			mysql_query($query) or die(mysql_error()." article.lib.php L:441");
			
		}
		return true;
		

	}
	public function copyModule($moduleComponentId) {
		$query = "SELECT * FROM `article_content` WHERE `page_modulecomponentid`=$moduleComponentId";
		$result = mysql_query($query);
		$content = mysql_fetch_assoc($result);
		
		$query = "SELECT MAX(page_modulecomponentid) as MAX FROM `article_content` ";
		$result = mysql_query($query) or displayerror(mysql_error() . "article.lib L:98");
		$row = mysql_fetch_assoc($result);
		$compId = $row['MAX'] + 1;

		$query = "INSERT INTO `article_content` (`page_modulecomponentid` ,`article_content`)VALUES ('$compId', '".mysql_escape_string($content['article_content'])."')";
		mysql_query($query) or displayerror(mysql_error()."article.lib L:104");
		if (mysql_affected_rows()) {
			return $compId;
		} else
			return false;
	}

}

