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
			require_once($sourceFolder."/latexRender.class.php");
			if (get_magic_quotes_gpc())
				$content = stripslashes($content);
			$render = new latexrender();
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

public function createModule(&$moduleComponentId) {
		include "article.lib.php";
		$article = new article();
		$newModuleComponentId=-1;
		$article->createModule($newModuleComponentId);
		if($newModuleComponentId==-1)
			displayerror("Unable to create a new page of type $moduleType");
		else { 
			$articleId = $newModuleComponentId;
			$query = "SELECT MAX(page_modulecomponentid) as MAX FROM `scrolltext` ";
			$result = mysql_query($query) or die(mysql_error());
			$row = mysql_fetch_assoc($result);
			$scrollId = $row['MAX'] + 1;

			$query=  "INSERT INTO `scrolltext` (`page_modulecomponentid` ,`article_modulecomponentid`)VALUES ('$scrollId','$articleId')";
			$result = mysql_query($query) or die(mysql_error());
			if (mysql_affected_rows()) {
				$moduleComponentId = $scrollId;
				return true;
			} else
				return false;
		}
	}

public function deleteModule($moduleComponentId) {
		echo $this->moduleComponentId;
		$query = "SELECT article_modulecomponentid FROM scrolltext WHERE page_modulecomponentid=". $moduleComponentId;
		echo $query;
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
	public function copyModule($moduleComponentId) {
		$query = "SELECT article_modulecomponentid FROM scrolltext WHERE page_modulecomponentid=". $this->moduleComponentId;
		$result = mysql_query($query);
		$row = mysql_fetch_assoc($result);
		$articleId=$row['article_modulecomponentid'];

		$query = "SELECT * FROM `article_content` WHERE `page_modulecomponentid`=$articleId";
		$result = mysql_query($query);
		$content = mysql_fetch_assoc($result);
		//['article_content']
		$query = "SELECT MAX(page_modulecomponentid) as MAX FROM `article_content` ";
		$result = mysql_query($query) or displayerror(mysql_error() . "article.lib L:98");
		$row = mysql_fetch_assoc($result);
		$compId = $row['MAX'] + 1;

		$query = "INSERT INTO `article_content` (`page_modulecomponentid` ,`article_content`)VALUES ('$compId', '".mysql_escape_string($content['article_content'])."')";
		mysql_query($query) or displayerror(mysql_error()."article.lib L:104");

		$query = "SELECT MAX(page_modulecomponentid) as MAX FROM `scrolltext` ";
		$result = mysql_query($query) or displayerror(mysql_error());
		$row = mysql_fetch_assoc($result);
		$scrollId = $row['MAX'] + 1;

		$query = "INSERT INTO `scrolltext` (`page_modulecomponentid` ,`article_modulecomponentid`)VALUES ('$scrollId', '$compId')";
		mysql_query($query) or displayerror(mysql_error());

		if (mysql_affected_rows()) {
			return $scrollId;
		} else
			return false;
	}



}

