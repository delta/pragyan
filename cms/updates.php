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
 * For more details, see README
 */
 
$newsItems = "<div style=\"display: none;\" id=\"newscontainer\">";

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
	
	
	for ($i = 0; $i < count($newsArray); $i++) {
		$newstitle = $newsArray[$i]['news_title'];
		$newsfeed = $newsArray[$i]['news_feed'];
		$newslink = $newsArray[$i]['news_link']; 
		$divopen = "<div class=\"news_style\" rel=\"$newstitle\" id=\"news$i\">";
		$heading = "<h4><a href='".$newslink."'>".$newstitle."</a></h4>";

		$content = "$newsfeed";
		$divclose = "</div>";

		$fulldiv = $divopen.$heading.$content.$divclose;
		$newsItems .= $fulldiv;
	}
	$newsItems .= "</div>";

/*

$newsItems = <<<NEWSITEMS
	<div style="display: none;" id="newscontainer">
		<div id="news1" rel="Ad - IT Registrations Open" class="news_style">
			<h4>Ad - IT Registrations Open</h4>
			<img src="/10/home/events/managing_technology/ad_it/Ad-IT-small.gif" alt="Ad - IT Registrations Open" align="left" style="margin: 0 6px" />
			<p>Registrations for Ad - IT have been opened. <a href="/10/home/events/managing_technology/ad_it/Ad-IT-small.gif">Register Now.</a></p>
		</div>
<!--		<div id="news2" rel="Anveshanam" class="news_style"></div> -->
		<div id="news3" rel="Workshop Registrations" class="news_style">
			<h4>Workshop Registrations</h4>
			<img src="/10/home/workshops/workshop-small.gif" alt="Workshop Registrations" align="left" style="margin: 0 6px" />
			<p>Registrations for <a href="/10/home/workshops/d3_photography/">3D Photography</a>, <a href="/10/home/workshops/ethical_hacking/">Ethical Hacking</a>, and <a href="/10/home/workshops/hexapod/">Hexapod</a> workshops will open soon. Be sure to claim your seat!</p>
		</div>

		<div id="news4" rel="Liminality" class="news_style">
			<h4>Liminality</h4>
			<img src="/10/home/events/chillpill/liminality/liminality-small.jpg" alt="Liminality" align="left" style="margin: 0 6px" />
			<p>Topics of the preliminary round have been updated for liminality.</p>
		</div> 

		<div id="news5" rel="Pragyan '10 Forum" class="news_style">
			<h4>Pragyan '10 Forum</h4>
			<img src="/10/cms/templates/falcon_blue/images/forum.png" alt="Forum" align="left" style="margin:6px" />
			<p>The <a href="/10/home/forum/">Pragyan '10 forums</a> are open! Feel free to post your questions, comments and suggestions.</p>
		</div>
	</div>
NEWSITEMS;

*/
