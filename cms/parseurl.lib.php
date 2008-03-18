<?php
/*
 * Created on Sep 29, 2007, 1:39:45 AM
 *
 * abhilash
 */



/**
 * parseUrlReal: Takes a url as string, and retrieves the pageid of the requested page.
 *
 * @param $url String denoting the url of the page.
 * @param $pageids An array to hold the list of page ids.
 * @return Integer denoting the page id of the page.
 */
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

?>
