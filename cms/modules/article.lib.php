<?php
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

	public function actionView($text="") {
		if($text=="") {
			$query = "SELECT article_content,article_lastupdated FROM article_content WHERE page_modulecomponentid=" . $this->moduleComponentId;
			$result = mysql_query($query);
			if($row = mysql_fetch_assoc($result)) {
				$text = $row['article_content'];
				global $PAGELASTUPDATED;
				$PAGELASTUPDATED = $row['article_lastupdated'];
			}
			else return "Article not yet created.";
		}
		global $sourceFolder;
		global $moduleFolder;
		require_once($sourceFolder."/latexRender.php");
		if (get_magic_quotes_gpc())
			$text = stripslashes($text);
		$render = new render();
		return $render->transform($text);
	}

	public function actionEdit() {
		global $sourceFolder;
		//require_once("$sourceFolder/diff.lib.php");
		require_once($sourceFolder."/upload.lib.php");
		submitFileUploadForm($this->moduleComponentId,"article",$this->userId,UPLOAD_SIZE_LIMIT);
		if (isset($_GET['preview']) && isset ($_POST['CKEditor1'])) {
			return "<div id=\"preview\" class=\"warning\"><a name=\"preview\">Preview</a></div>".$this->actionView(stripslashes($_POST[CKEditor1])).$this->getCkBody(stripslashes($_POST[CKEditor1]));
		}
		if (isset($_GET['version'])) {
			$revision = $this->getRevision($_GET['version']);
			return "<div id=\"preview\" class=\"warning\"><a name=\"preview\">Previewing Revision Number ".$_GET['version']."</a></div>".$this->actionView($revision).$this->getCkBody($revision);
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
			return $this->actionView();
		}
		return $this->getCkBody();

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

	public function getCkBody($content=""){
			global $sourceFolder;
			global $cmsFolder;
			global $moduleFolder;
			global $urlRequestRoot;
			require_once ("$sourceFolder/$moduleFolder/article/ckeditor/ckeditor.php");
			if($content=="") {
				$query = "SELECT * FROM `article_content` WHERE `page_modulecomponentid`= $this->moduleComponentId";
				$result = mysql_query($query);
				$temp = mysql_fetch_assoc($result);
				$content = $temp['article_content'];
			}

			$CkForm =<<<Ck
						<form action="./+edit" method="post">
						<input type="button" value="Cancel" onclick="submitarticleformCancel(this);"><input type="submit" value="Save"><input type="button" value="Preview" onclick="submitarticleformPreview(this)">
                        To upload files and images, go to the <a href="#files">files section</a>.
Ck;
			$oCKEditor = new CKeditor();
			$oCKEditor->basePath = "$urlRequestRoot/$cmsFolder/$moduleFolder/article/ckeditor/";
			$oCKEditor->config['width'] = '100%';
			$oCKEditor->config['height'] = '300';
			$oCKEditor->returnOutput = true;
			$Ckbody = $oCKEditor->editor('CKEditor1',$content);

			$CkFooter =<<<Ck1
					      <input type="button" value="Cancel" onclick="submitarticleformCancel(this);"><input type="submit" value="Save"><input type="button" value="Preview" onclick="submitarticleformPreview(this)">
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
					    </script><br />
					    <fieldset>
					        <legend><a name="files">Files :</a></legend>
							Uploaded Files : <br />
Ck1;
		$CkFooter .= getUploadedFilePreviewDeleteForm($this->moduleComponentId,"article",'./+edit');
		$CkFooter .= '<br />Upload files : <br />'.getFileUploadForm($this->moduleComponentId,"article",'./+edit',UPLOAD_SIZE_LIMIT,5).'</fieldset>';

		/* Revisions available */
		$revisionquery = "SELECT MAX(article_revision) AS MAX FROM `article_contentbak` where page_modulecomponentid = $this->moduleComponentId";
		$revisionresult = mysql_query($revisionquery);
		$revisionrow = mysql_fetch_assoc($revisionresult);
		$start = $revisionrow['MAX'] - 10;
		if(isset($_GET['revisionno']))
			$start = $_GET['revisionno'];
		if($start>$revisionrow['MAX']-9) $start = $revisionrow['MAX']-10;
		if($start<0) $start = 0;
		$count = 10;
		if(isset($_GET['count']))
			$count = $_GET['count'];
		if($count>($revisionrow['MAX']-$start+1)) $count = $revisionrow['MAX']-$start+1;
		$query = "SELECT article_revision,article_updatetime,user_id FROM `article_contentbak` where page_modulecomponentid = $this->moduleComponentId ORDER BY article_revision LIMIT $start,$count";
		$result = mysql_query($query);
		$revisionTable = "<fieldset>
					        <legend>Archive : </legend>" .
					        		"<table border='1'><tr><td>Revision Number</td><td>Date Updated</td><td>User Email</td></tr>";
		while ($row = mysql_fetch_assoc($result)) {
			$revisionTable .= "<tr><td><a href=\"./+edit&version=".$row['article_revision']."#preview\">".$row['article_revision']."</a></td><td>".$row['article_updatetime']."</td><td>".getUserEmail($row['user_id'])."</td></tr>";
		}
		$revisionTable .="</table>" .
				"<input type=\"button\" value=\"<<\" onclick=\"window.location='./+edit&revisionno=0'\" /> " .
				"<input type=\"button\" value=\"<\" onclick=\"window.location='./+edit&revisionno=".($start - 10)."'\" /> " .
				"<input type=\"button\" value=\">\" onclick=\"window.location='./+edit&revisionno=".($start + 10)."'\" /> " .
				"<input type=\"button\" value=\">>\" onclick=\"window.location='./+edit&revisionno=".($revisionrow['MAX']-10)."'\" /> " .
				"</fieldset>";

		/* Revisions end*/

		return  $CkForm . $Ckbody . $CkFooter.$revisionTable;
	}

	public function createModule(&$moduleComponentId) {
		$query = "SELECT MAX(page_modulecomponentid) as MAX FROM `article_content` ";
		$result = mysql_query($query) or die(mysql_error() . "article.lib L:73");
		$row = mysql_fetch_assoc($result);
		$compId = $row['MAX'] + 1;

		$query = "INSERT INTO `article_content` (`page_modulecomponentid` ,`article_content`)VALUES ('$compId', 'Coming up Soon!!!')";
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
			return true;
		else
			return false;

	}
	public function copyModule($moduleComponentId) {
		$query = "SELECT * FROM `article_content` WHERE `page_modulecomponentid`=$moduleComponentId";
		$result = mysql_query($query);
		$content = mysql_fetch_assoc($result);
		//['article_content']
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

