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

/**
 * Represents a node in a directory tree
 */
class DirectoryTreeNode {
	private $pageId;
	private $pageName;
	private $children;
	private $pageTitle;

	private $permission;
	private $isRequired;



	/**
	 * Computes the permissions for one node, given the permissions for its parent
	 * @param $userid User id of the current user
	 * @param $groups Array containing the groups that the current user belongs to, sorted priority-wise
	 * @param $permId The perm id of the action, for which the node must check permissions
	 * @param $permSet Array containing the permissions for each of the groups on the parent node
	 * @return Boolean indicating whether the user has the permission indicated by $permId on the given page
	 */
	private function computePermission(& $userid, & $groups, & $permId, & $permSet) {
		$pid = $this->pageId;
		/// Assume the parent node has $parentPermission as its permission, and calculate permission for current node

		/// $permSet: Array containing permissions for each of the groups in group id
		/// For every group where the permission is unset, or Y, check whether it is set to no, or yes
		/// And update corresponding entry in the permSet array
		/// If the permission for a group is set to N, leave it as such

		$permListTable = MYSQL_DATABASE_PREFIX . 'permissionlist';
		$pagepermTable = MYSQL_DATABASE_PREFIX . 'userpageperm';

		$permQuery = "SELECT `perm_permission`, `usergroup_id` FROM `$pagepermTable` WHERE " .
									"((`usergroup_id` IN (" . join($groups, ', ') . ") AND `perm_type` = 'group') OR " .
									"(`usergroup_id` = '$userid' AND `perm_type` = 'user')) " .
									"AND `page_id` = '$pid' AND `perm_id` = '$permId'";

		/// SELECT perm_permission, usergroup_id FROM $pagepermTable WHERE ((usergroup_id IN join() AND perm_type='group')
		///		OR (usergroup_id = $userid AND perm_type = 'user')) AND page_id = $pid AND perm_id = $permId
		$permResult = mysql_query($permQuery) or die($permQuery . "<br />" . mysql_error());
		$groupCount = count($groups);

		while ($permResultRow = mysql_fetch_row($permResult)) {
			$index = array_search($permResultRow[1], $groups);

			if($index === false) {
				if($permResultRow[1] == $userid) {
					$index = $groupCount;
				}
			}

			if($index !== false) {
				if ($permResultRow[0] == 'Y') {
					if ($permSet[$index] == 'U') {
						$permSet[$index] = 'Y';
					}
				}
				elseif ($permResultRow[0] == 'N') {
					$permSet[$index] = 'N';
				}
			}
		}

		$permission = false;
		for ($i = 0; $i <= $groupCount && !$permission; $i++) {
			$permission = ($permission || ($permSet[$i] == 'Y' ? true : false));
		}

		return $permission;
	}



	/**
	 * Constructor for the DirectoryTreeNode object
	 * @param $pageId Id of the page, which the new Node object is to represent
	 * @param $userid User id of the current user
	 * @param $groups Array containing the group ids of the groups the user belongs to, sorted priority-wise
	 * @param $permId perm_id representing the module and the action
	 * @param $permSet Array of booleans representing the permission for each of the groups on the parent node
	 * @param $retrieveLinks Boolean indicating whether links should be retrieved
	 */
	public function __construct(& $pageId, & $userid, & $groups, &$permId, $permSet, $retrieveLinks = false) {
		$pageNameQuery = "SELECT `page_name`, `page_title` FROM `" . MYSQL_DATABASE_PREFIX . "pages` WHERE `page_id` = '$pageId'";
		$pageNameResult = mysql_query($pageNameQuery);
		$pageNameResultRow = mysql_fetch_row($pageNameResult);

		$this->pageId = $pageId;
		$this->pageName = $pageNameResultRow[0];
		$this->pageTitle = $pageNameResultRow[1];

		$this->permission = $this->computePermission($userid, $groups, $permId, $permSet);
		$this->isRequired = $this->permission;

		$this->children = array ();

		$childQuery = "SELECT `page_id`, `page_module` FROM `" . MYSQL_DATABASE_PREFIX . "pages` WHERE `page_parentid` = '$pageId' and `page_parentid` != `page_id` ORDER BY `page_menurank`";
		$childResult = mysql_query($childQuery);

		while ($childResultRow = mysql_fetch_assoc($childResult)) {
			if($childResultRow['page_module'] != "link" || $retrieveLinks == true) {
				$a = new DirectoryTreeNode($childResultRow['page_id'], $userid, $groups, $permId, $permSet);

				if($a->isRequired) {
					$this->isRequired = true;
				}

				$this->children[] = $a;
			}
		}
	}

	public function getPageId() {
		return $this->pageId;
	}

	public function getPageName() {
		return $this->pageName;
	}

	public function getPageTitle() {
		return $this->pageTitle;
	}

	public function getChild($index) {
		if ($index < count($this->children) && $index >= 0)
			return $this->children[$index];
	}

	public function getChildrenCount() {
		return count($this->children);
	}

	public function getPermission() {
		return $this->permission;
	}

	public function isRequired() {
		return $this->isRequired;
	}
}

class DirectoryTree {
	private $rootNode;

	private static function getTreeAsArray(DirectoryTreeNode $node) {
		$a = array ();
		$a[$node->getPageId()] = array ();

		$l = $node->getChildrenCount();

		for ($i = 0; $i < $l; $i++) {
			$tempNode = $node->getChild($i);
			$a[] = DirectoryTree :: getTreeAsArray($tempNode);
		}

		return $a;
	}

	private static function getTreeAsString(DirectoryTreeNode $node, $parentPath) {
		if(!$node->isRequired()) {
			return '';
		}

		$c = $node->getChildrenCount();
		$parentPath .= $node->getPageName() . '/';
		$perm = $node->getPermission() == true ? 'ddtree_accessible' : 'ddtree_inaccessible';

		$html = "<li><span title=\"$parentPath\" class=\"$perm\">" . $node->getPageTitle() . '</span>'; // onclick=\"showPagePath(this, '$parentPath')\" class=\"$perm\">" . $node->getPageTitle();

		if($c > 0) {
			$html .= "<ul>\n";
		}

		for ($i = 0; $i < $c; $i++) {
			$html .= DirectoryTree :: getTreeAsString($node->getChild($i), $parentPath) . "\n";
		}

		if($c > 0) {
			$html .= "</ul>\n";
		}

		return $html . "</li>\n";
	}

	public function __construct($pageId, $userId, $action, $module, $showLinks = false) {
		$groups = getGroupIds($userId);
		$permSet = array_fill(0, count($groups) + 1, 'U');

		$permId = getPermissionId($module, $action);
		if($permId < 0)
			return;

		$this->rootNode = new DirectoryTreeNode($pageId, $userId, $groups, $permId, $permSet, $showLinks);
	}

	public function toArray() {
		return DirectoryTree :: getTreeAsArray($this->rootNode);
	}

	/**
	 * Return the tree as an HTML snippet.
	 * @param $inputBoxId the name of the input box whose value the tree will change by default.
	 * 		If no parameter is specified, it has an input box of its own.
	 * @return string A string containing the generated HTML
	 */
	public function toHtml($treeContainerId, $treeId, $inputBoxId = "") {
		$script = '';
		if($inputBoxId=="") {
			$inputBoxId = "directoryBrowserPagePath";
			$script = '<input type="text" id="directoryBrowserPagePath" style="width: 100%" value="" />';
		}

		global $cmsFolder;
		global $urlRequestRoot;
		global $templateFolder;
		$imagesFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/images";
		$scriptsFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/scripts";

		$script .= '<script type="text/javascript" language="javascript" src="' . $scriptsFolder . '/treemenu.js"></script>';

		$activateScript = <<<ACTIVATESCRIPT
			<script type="text/javascript" language="javascript">
			<!--
			ddtreemenu = new JSTreeMenu('$treeId', '$inputBoxId', '$imagesFolder', false);
				//setupMenuDependencies("$imagesFolder", "$inputBoxId");
				//ddtreemenu.createTree("treemenu1", true, 5)

				//spanTags = document.getElementById('directorybrowser').getElementsByTagName('span');
			//	for(i = 0; i < spanTags.length; i++) {
			//		spanTags[i].onclick = showPagePath;
			//	}
			-->
			</script>
ACTIVATESCRIPT;

		return $script . '<div id="' . $treeContainerId . '"><ul class="treeview" id="' . $treeId . '">' . DirectoryTree :: getTreeAsString($this->rootNode, '') . '</ul></div>'. $activateScript;
	}
}


