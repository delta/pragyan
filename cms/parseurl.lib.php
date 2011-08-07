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
 * @author Abhilash R
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

/**
 * parseUrlReal: Takes a url as string, and retrieves the pageid of the requested page.
 *
 * @param $url String denoting the url of the page.
 * @param $pageids An array to hold the list of page ids.
 * @return Integer denoting the page id of the page.
 */
 
 function parseUrlReal($url, &$pageids) { 
	$url = rtrim($url, '/');
	$urlPieces = explode('/', $url); 
	//$printer = print_r($pageids,true);
	//$printer2 = print_r($urlPieces,true);
	$pagesTable = MYSQL_DATABASE_PREFIX."pages";

	/**
	 *	Build query string:
	 * 		SELECT IF(node[J].module = "link", node[J].page_modulecomponentid, node[J].page_id) as pageid[J]
	 * 		FROM pagestable AS node[J]
	 * 		WHERE node[J].page_parentid = node[J-1].page_id;
	 *	J from 0 to count(urlPieces) - 1
	 */

	$selectString = "SELECT node0.page_id AS pageid0";
  $fromString=" FROM `$pagesTable` AS node0";
  $whereString=" WHERE node0.page_id=node0.page_parentid";
  for($i = 1; $i < count($urlPieces); $i++) {
		if($urlPieces[$i] != "") {
			$selectString.=", node".$i.".page_id AS pageid".$i;
			$fromString.=", `$pagesTable` as node".$i;
			$whereString.=" and node".$i.".page_parentid = IF(node".($i - 1).".page_module = 'link', node".($i - 1).".page_modulecomponentid, node".($i - 1).".page_id) and node".$i.".page_name='".$urlPieces[$i]."'";
	  }
	}
  	$pageid_query = $selectString.$fromString.$whereString;
	if($pageid_result = mysql_query($pageid_query)) {
		if(!($pageids = mysql_fetch_row($pageid_result))) {
			displayerror("The requested page does not exist.");
			return false;
		}
	}
	return $pageids[count($pageids) - 1];
}

 /* The following fails to work in Mysql 4.1.10 : theyaga's mysql account*/
 /* (The AS node0 gives an error)
function parseUrlReal($url, &$pageIdArray) {
	$url = trim($url, '/');
	$urlPieces = array();
	if($url != '') {
  	$urlPieces = explode('/', $url);
	}
	$pieceCount = count($urlPieces);

	$pages[] = '`node0`.`page_id` AS `pageid0`';
	$nodes[] = '(SELECT `page_id`, `page_parentid`, `page_module`, `page_modulecomponentid` FROM `'.MYSQL_DATABASE_PREFIX.'pages` WHERE `page_id` = `page_parentid`) AS `node0`';
	$conditions = array();
	
	for($i = 1; $i <= $pieceCount; $i++) {
		$pages[] = '`node'.$i.'`.`page_id` AS `pageid'.$i.'`';
		$nodes[] = '(SELECT `page_id`, `page_parentid`, `page_module`, `page_modulecomponentid` FROM `'.MYSQL_DATABASE_PREFIX.'pages` WHERE `page_name` = \''.$urlPieces[$i - 1].'\') AS `node'.$i.'`';
		$conditions[] = 'IF(`node'.($i - 1).'`.`page_module` = \'link\', `node'.($i - 1).'`.`page_modulecomponentid`, `node'.($i - 1).'`.`page_id`) = `node'.$i.'`.`page_parentid`';
	}

 	$pageIdQuery = 'SELECT ' . join($pages, ', ') . ' FROM (' . join($nodes, ', ') . ')';
 	if(count($conditions) > 0)
 		$pageIdQuery .= ' WHERE ' . join($conditions, ' AND ');

	$pageIdResult = mysql_query($pageIdQuery);

	if(!$pageIdResult || !($pageIdArray = mysql_fetch_row($pageIdResult))) {
		displayerror("The requested page does not exist.");
		return false;
	}

	return $pageIdArray[count($pageIdArray) - 1];
}
*/


/**
 * parseUrlDereferenced: Takes a url as string, and retrieves the path to the page, dereferencing any links.
 *
 * @param $pageid Page id of the requested page.
 * @param $pageids An array to hold the list of page ids.
 */
function parseUrlDereferenced($pageid, &$pageids) {
	$dereferencedPageId = getDereferencedPageId($pageid);
	$parentId = getParentPage($dereferencedPageId);
	$pageids = array($dereferencedPageId);

	while($parentId != $dereferencedPageId) {
		$pageids[] = $parentId;
		$dereferencedPageId = getDereferencedPageId($parentId);
		$parentId = getParentPage($dereferencedPageId);
		if($dereferencedPageId==0)	break;
	}
	if($parentId != 0)
		displayerror("Looping condition detected!!");
	$pageids = array_reverse($pageids);
}

 /*
  * If there is a link /events/dalalstreet/information at /events/dalalinfo
  * And a page /events/dalalstreet/information/a exists, then even /events/dalalinfo exists
  *
  * For parseUrl :
  * 	For breadcrumb and menubar: (Real)
  * 		will follow the actual textual path ids. But children of all links will be found out from the
  * 		linked page. i.e. while going from root to child page, if we encounter any links, to find
  * 		its children we look for children of "linked" page and not the "link" page.
  *
  * 	For permissions : (Dereferenced)
  * 		we need a path array to find permission of a page. To find the permission of a page,
  * 		first we dereference all links to actual page and then find the permissions. One way of
  * 		doing it is to find the target page id and keep recursively going up until we reach root.
  */


