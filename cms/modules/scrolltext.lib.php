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
class scrolltext implements module{
    private $userId;
	private $moduleComponentId;
	private $action;
	private $scrollarticle;

	public function getHtml($gotuid, $gotmoduleComponentId, $gotaction) {
		$this->userId = $gotuid;
		$this->moduleComponentId = $gotmoduleComponentId;
		$this->action = $gotaction;
		include "article.lib.php";
		$this->scrollarticle = new article();
		if ($this->action == "view")
			return $this->actionView();
		if ($this->action == "scrollview")
			return $this->actionScrollview();
		if ($this->action == "edit")
			return $this->actionEdit();
	}
	
	public function actionScrollview($text="") {
			if($text=="") {
				$query = "SELECT article_modulecomponentid FROM scrolltext WHERE page_modulecomponentid=". $this->moduleComponentId;
				$result = mysqli_query($GLOBALS["___mysqli_ston"], $query);
				$row = mysqli_fetch_assoc($result);
				$articleid=$row['article_modulecomponentid'];
				$query = "SELECT article_content,article_lastupdated FROM article_content WHERE page_modulecomponentid=" . $articleid;
				$result = mysqli_query($GLOBALS["___mysqli_ston"], $query);
				if($row = mysqli_fetch_assoc($result)) {
					$text = $row['article_content'];
					global $PAGELASTUPDATED;
					$PAGELASTUPDATED = $row['article_lastupdated'];
				}
				else return "Article not yet created.";
			}
	       $content="<HEAD><META HTTP-EQUIV=REFRESH CONTENT=300></HEAD><body><div><div align=\"center\"><fieldset><marquee behavior=scroll scrollAmount=\"1\" scrolldelay=\"0\" onmouseover=\"this.stop()\" direction=\"up\" onmouseout=\"this.start()\" vspce=\"800px\"style=\"height:800px;width:800px;font-size:15px;color:#880000;\">".$text."</div></fieldset></marquee></body>";
			global $sourceFolder;
			global $moduleFolder;
			require_once($sourceFolder."/pngRender.class.php");
			if (get_magic_quotes_gpc())
				$content = stripslashes($content);
			$render = new pngrender();
			echo $render->transform($content);
			disconnect();
			exit();
		}

public function actionEdit(){

			$query = "SELECT article_modulecomponentid FROM scrolltext WHERE page_modulecomponentid=". $this->moduleComponentId;
			$result = mysqli_query($GLOBALS["___mysqli_ston"], $query);
			$row = mysqli_fetch_assoc($result);
			$articleId=$row['article_modulecomponentid'];
			return $this->scrollarticle->getHtml($this->userId,$articleId,"edit");
}
public function actionView(){

			$query = "SELECT article_modulecomponentid FROM scrolltext WHERE page_modulecomponentid=". $this->moduleComponentId;
			$result = mysqli_query($GLOBALS["___mysqli_ston"], $query);
			$row = mysqli_fetch_assoc($result);
			$articleId=$row['article_modulecomponentid'];
			return $this->scrollarticle->getHtml($this->userId,$articleId,"view");
}

public function createModule($scrollId) {
		include "article.lib.php";
		$article = new article();
		$articleId = createInstance('article');
		$article->createModule($articleId);
		$query=  "INSERT INTO `scrolltext` (`page_modulecomponentid` ,`article_modulecomponentid`)VALUES ('$scrollId','$articleId')";
		$result = mysqli_query($GLOBALS["___mysqli_ston"], $query) or die(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		return true;
	}

public function deleteModule($moduleComponentId) {
		$query = "SELECT article_modulecomponentid FROM scrolltext WHERE page_modulecomponentid=". $moduleComponentId;
		$result = mysqli_query($GLOBALS["___mysqli_ston"], $query);
		$row = mysqli_fetch_assoc($result);
		$articleId=$row['article_modulecomponentid'];

		$query = "DELETE FROM `article_content` WHERE `page_modulecomponentid`=$articleId";
		$result = mysqli_query($GLOBALS["___mysqli_ston"], $query);
		if ((mysqli_affected_rows($GLOBALS["___mysqli_ston"])) >= 1)
			return true;
		else
			return false;

	}
	public function copyModule($moduleComponentId,$newId) {
		include "article.lib.php";
		$article = new article();
		$articleId = createInstance('article');
		$article->createModule($articleId);
		$query=  "INSERT INTO `scrolltext` (`page_modulecomponentid` ,`article_modulecomponentid`)VALUES ('$newId','$articleId')";
		$result = mysqli_query($GLOBALS["___mysqli_ston"], $query) or die(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		
		$query = "SELECT article_modulecomponentid FROM scrolltext WHERE page_modulecomponentid='{$moduleComponentId}'";
		$result = mysqli_query($GLOBALS["___mysqli_ston"], $query);
		if(!$result)
			return false;
		$row = mysqli_fetch_assoc($result);
		$fromId=$row['article_modulecomponentid'];

		$query = "SELECT * FROM `article_content` WHERE `page_modulecomponentid`='$fromId'";
		$result = mysqli_query($GLOBALS["___mysqli_ston"], $query);
		if(!$result)
			return false;
		
		$content = mysqli_fetch_assoc($result);
		
		$query = "INSERT INTO `article_content` (`page_modulecomponentid` ,`article_content`)VALUES ('$articleId', '".((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $content['article_content']) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""))."')";
		mysqli_query($GLOBALS["___mysqli_ston"], $query) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))."scrolltext.lib L:104");
		return true;
	}
	public function moduleAdmin(){
		return "This is the Scrolltext module administration page. Options coming up soon!!!";
	}
	


}
