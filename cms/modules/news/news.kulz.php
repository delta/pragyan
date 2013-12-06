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
function displayNews2()
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
	$newsArray = $tmpNewsObj->getNewsArray(0);
	
	
	for ($i = 0; $i < sizeof($newsArray); $i++) {
	
		
		
			$divopen = "<div class=\"news_style\" rel=\"$newsArray[$i][news_title]\" id=\"news$i\">";
			$heading = "<h4>$newsArray[$i][news_title]</h4>";
			$content = "$newsArray[$i][news_feed]";
		  $divclose = "</div>";
		  
		  $fulldiv = $divopen.$heading.$content.$divclose;
		  
		  echo $fulldiv;		 
		
		
		
	}

return 1;

}

