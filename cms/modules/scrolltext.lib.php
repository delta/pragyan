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
				$result = mysql_query($query);
				$row = mysql_fetch_assoc($result);
				$articleid=$row['article_modulecomponentid'];
				$query = "SELECT article_content,article_lastupdated FROM article_content WHERE page_modulecomponentid=" . $articleid;
				$result = mysql_query($query);
				if($row = mysql_fetch_assoc($result)) {
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
			$result = mysql_query($query);
			$row = mysql_fetch_assoc($result);
			$articleId=$row['article_modulecomponentid'];
			return $this->scrollarticle->getHtml($this->userId,$articleId,"edit");
}
public function actionView(){

			$query = "SELECT article_modulecomponentid FROM scrolltext WHERE page_modulecomponentid=". $this->moduleComponentId;
			$result = mysql_query($query);
			$row = mysql_fetch_assoc($result);
			$articleId=$row['article_modulecomponentid'];
			return $this->scrollarticle->getHtml($this->userId,$articleId,"view");
}

public function createModule($scrollId) {
		include "article.lib.php";
		$article = new article();
		$articleId = createInstance('article');
		$article->createModule($articleId);
		$query=  "INSERT INTO `scrolltext` (`page_modulecomponentid` ,`article_modulecomponentid`)VALUES ('$scrollId','$articleId')";
		$result = mysql_query($query) or die(mysql_error());
		return true;
	}

public function deleteModule($moduleComponentId) {
		$query = "SELECT article_modulecomponentid FROM scrolltext WHERE page_modulecomponentid=". $moduleComponentId;
		$result = mysql_query($query);
		$row = mysql_fetch_assoc($result);
		$articleId=$row['article_modulecomponentid'];

		$query = "DELETE FROM `article_content` WHERE `page_modulecomponentid`=$articleId";
		$result = mysql_query($query);
		if ((mysql_affected_rows()) >= 1)
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
		$result = mysql_query($query) or die(mysql_error());
		
		$query = "SELECT article_modulecomponentid FROM scrolltext WHERE page_modulecomponentid='{$moduleComponentId}'";
		$result = mysql_query($query);
		if(!$result)
			return false;
		$row = mysql_fetch_assoc($result);
		$fromId=$row['article_modulecomponentid'];

		$query = "SELECT * FROM `article_content` WHERE `page_modulecomponentid`='$fromId'";
		$result = mysql_query($query);
		if(!$result)
			return false;
		
		$content = mysql_fetch_assoc($result);
		
		$query = "INSERT INTO `article_content` (`page_modulecomponentid` ,`article_content`)VALUES ('$articleId', '".mysql_escape_string($content['article_content'])."')";
		mysql_query($query) or displayerror(mysql_error()."scrolltext.lib L:104");
		return true;
	}



}
