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
 

 

function displayNews()
{

	$news =<<<NEWS
		<style type="text/css">
		a.tickl{font-family:Verdana,Arial,Helvetica,sans-serif;font-size:11px;line-height:12px;text-decoration:none;color:#fff;font-weight:bold;}
		.tickls{color:#666;}
		</style>
		<div id="newsbox" style="font-size:0.9em;position:absolute;right:45px;width:375px;top:80px;color:#fff;z-index:2;">
		<div class="ticki" >
		<a class="tickl" href="/08/home/news/"><span class="tickls">UPDATES</span></a>
		<a id="tickerAnchor" class="tickl" target="_top" href=""></a>
		</div>
		</div>
		<script type="text/javascript" language="JavaScript">
		 <!--
		 var theCharacterTimeout = 50;
		 var theStoryTimeout = 5000;
		 var theWidgetOne = "_";
		 var theWidgetTwo = "-";
		 var theWidgetNone = "";
		 var theLeadString = ":&nbsp;";
		 var theSummaries = new Array();
		 var theSiteLinks = new Array();
NEWS;
	global $sourceFolder;
	global $moduleFolder;
	global $urlRequestRoot;
	global $pageIdArray;
	require_once ("$sourceFolder/$moduleFolder/news.lib.php");
	$tmpNewsObj = new news();
	$pageFullPath = "/news/"; ///<Replace with path of news page
	$pageId = parseUrlReal($pageFullPath, $pageIdArray);
	$pageInfo = getPageInfo($pageId);
	$newsArray = $tmpNewsObj->getNewsArray($pageInfo['page_modulecomponentid']);
	$news .= "var theItemCount =" . sizeof($newsArray) . ";";
	for ($i = 0; $i < sizeof($newsArray); $i++) {
		$newsFeed = $newsArray[$i]['news_title'];
		$newsFeed .= " - " . $newsArray[$i]['news_feed'];
		$newsLink = $newsArray[$i]['news_link'];
		//		displayerror()
		if (strlen($newsFeed) >= 48) {
			$newsFeed = substr($newsFeed, 0, 48);
			$newsFeed = substr($newsFeed, 0, strrpos($newsFeed, " "));
			$newsFeed .= "...";
		}
		$news .= "theSummaries[$i] = \"$newsFeed\";";
		if ($newsLink == "") {
			$newsLink = $urlRequestRoot . $pageFullPath . "&id=" . $newsArray[$i]['news_id'];
		}
		$news .= "theSiteLinks[$i] = \"$newsLink\";";
	}

	$news .=<<<NEWS
		 startTicker();
		 //-->
		</script>
NEWS;
	return $news;

}

