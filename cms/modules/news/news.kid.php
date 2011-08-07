<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}
function displayNews()
{
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
	$newsFeed = '';
	for ($i = 0; $i < sizeof($newsArray); $i++) {
		$newsTitle = str_replace("'","&#39;",$newsArray[$i]['news_title']);
		$newsBody = str_replace("'","&#39;",$newsArray[$i]['news_feed']);
		$newsTitle = rtrim($newsTitle);
		$newsBody = rtrim($newsBody);
		$days=6;
		if(time()<(strtotime($newsArray[$i]['news_date'])+($days*24*60*60))) {
			$newsBody .= '<font color="#f9dc72"><strong> NEW*!</strong></font>';
			
		}
		if($newsArray[$i]['news_link']=='')
			{
				$newsFeed .= '<li><a href=/09/home/news>'.$newsTitle.' '.$newsBody.'</a></li>';
			}
		else
			{
				$newsFeed .= '<li><a href='.$newsArray[$i]['news_link'].'>'.$newsTitle.' '.$newsBody.'</a></li>';
			}		
	}	
	$newsFeed = rtrim($newsFeed,',');
				/**
		if (strlen($newsFeed) >= 48) {
			$newsFeed = substr($newsFeed, 0, 48);
			$newsFeed = substr($newsFeed, 0, strrpos($newsFeed, " "));
			$newsFeed .= "...";
		}

$news =<<<NEWS
<script>
var pausecontent2=new Array($newsFeed)
</script>
NEWS;
*/
	return $newsFeed;

}

function displayNew()
{
	global $sourceFolder;
	global $moduleFolder;
	global $urlRequestRoot;
	global $pageIdArray;
	require_once ("$sourceFolder/$moduleFolder/news.lib.php");
	$tmpNewsObj = new news();
	$pageFullPath = "/whatsnew/"; ///<Replace with path of news page
	$pageId = parseUrlReal($pageFullPath, $pageIdArray);
	$pageInfo = getPageInfo($pageId);
	$newsArray = $tmpNewsObj->getNewsArray($pageInfo['page_modulecomponentid']);
	$newsFeed = '';
	for ($i = 0; $i < sizeof($newsArray); $i++) {
		$newsTitle = str_replace("'","&#39;",$newsArray[$i]['news_title']);
		$newsBody = str_replace("'","&#39;",$newsArray[$i]['news_feed']);
		$newsTitle = rtrim($newsTitle);
		$newsBody = rtrim($newsBody);
		$days=20;
//		if(time()<(strtotime($newsArray[$i]['news_date'])+($days*24*60*60))) {
//			$newsBody .= '<font color="#f9dc72"><strong> NEW!</strong></font>';
//		}
		if($newsArray[$i]['news_link']=='')
			{
				$newsFeed .= '\'<a href=/09/home/whatsnew>'.$newsTitle.' '.$newsBody.'</a>\',';
			}
		else
			{
				$newsFeed .= '\'<a href='.$newsArray[$i]['news_link'].'>'.$newsTitle.' '.$newsBody.'</a>\',';
			}		
	}	
	$newsFeed = rtrim($newsFeed,',');
				/**
		if (strlen($newsFeed) >= 48) {
			$newsFeed = substr($newsFeed, 0, 48);
			$newsFeed = substr($newsFeed, 0, strrpos($newsFeed, " "));
			$newsFeed .= "...";
		}
*/
$news =<<<NEWS
<script>
var pausecontent2=new Array($newsFeed)
</script>
NEWS;
	return $news;
}
