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
 * @author Jack<chakradarraju@gmail.com>
 *
 * The purpose of book module is to contain its subpages and provide an client-side interface to switch between them.
 * It helps avoiding page reload for user, reduces number of page request for server,
 * and most important of all we can use javascript libraries give different effects for page switching.
 * Book module doesnt stores any content on itself,
 * it only stores information about which subpages are to be considered as pages inside book and
 * which subpages are to be shown as child pages on global menubar.
 * 
 * Book module uses one table to stores its data:
 * book_desc:
 *     page_modulecomponentid - unique id for each book instance
 *     initial - default page of book
 *     list - list of page_id s to be considered as page of book
 *     menu_hide - list of page_id s to be hidden from global menu
 * 
 * If the client browser is not capable of handling javascript workaround has been made using css
 */

class book implements module {
	private $userId;
	private $moduleComponentId;
	private $action;
	private $pageId;
	
	/**
	 * function getHtml:
	 * Gateway through which CMS interacts with module
	 * This function will be called from getContent function of cms/content.lib.php
	 */
	public function getHtml($gotuid, $gotmoduleComponentId, $gotaction) {
		$this->userId = $gotuid;
		$this->moduleComponentId = $gotmoduleComponentId;
		$this->action = $gotaction;
		$this->pageId = getPageIdFromModuleComponentId("book",$gotmoduleComponentId);
		$this->bookProps = mysql_fetch_assoc(mysql_query("SELECT * FROM `book_desc` WHERE `page_modulecomponentid` = '{$this->moduleComponentId}'"));
		$page_title = mysql_fetch_row(mysql_query("SELECT `page_title` FROM `" . MYSQL_DATABASE_PREFIX . "pages` WHERE `page_id` = '{$this->pageId}'"));
		$this->bookProps['page_title'] = $page_title[0];
		$this->hideInMenu();
		if ($this->action == "edit")
			return $this->actionEdit();
		return $this->actionView();
	}
	
	/**
	 * function actionView:
	 * @returns HTML View of the Book according to the properties set
	 */
	public function actionView() {
		global $INFOSTRING, $WARNINGSTRING, $ERRORSTRING;

		$childrenQuery = 'SELECT `page_title`, `page_id`, `page_module`, `page_modulecomponentid`, `page_name` FROM `' . MYSQL_DATABASE_PREFIX . 'pages` WHERE `page_parentid` = ' . $this->pageId . ' AND `page_id` IN (' . $this->bookProps['list'] . ') ORDER BY `page_menurank`';
		$result = mysql_query($childrenQuery);
		$ret = $this->tabScript();
		$ret .=<<<RET
<h2>{$this->bookProps['page_title']}</h2>
<div class='tabEnvelope'>
RET;
		$navigate = $this->bookProps['initial'];
		if(isset($_GET['navigate'])&&$this->isPresent($this->pageId,$_GET['navigate']))
			$navigate = escape($_GET['navigate']);
		$tabList = "<div id='tabList'>";
		$contentList = "";
		$backup_info = $INFOSTRING;
		$backup_warning = $WARNINGSTRING;
		$backup_error = $ERRORSTRING;
		while($row = mysql_fetch_assoc($result)) {
			if(getPermissions($this->userId, $row['page_id'], "view")) {
				$INFOSTRING = "";
				$WARNINGSTRING = "";
				$ERRORSTRING = "";
				$moduleType = $row['page_module'];
				$active = "";
				if($navigate == $row['page_id']||getPageModule($row['page_id'])=='book'&&$this->isPresent($row['page_id'],$navigate))
					$active = ' active';
				$tabList .= "<div class='tabElement'><a id='Content{$this->pageId}_{$row['page_id']}' href='./+view&navigate={$row['page_id']}'><span class='tabItem' id='cms-tabItem'>{$row['page_title']}</span></a></div>";
				$content = getContent($row['page_id'], "view", $this->userId, true);
				$content = preg_replace('/<a(.*)href=[\'"](.\/)+(.*)[\'"](.*)>(.*)<\/a>/i', '<a$1href="./' . $row['page_name'] . '/$3"$4>$5</a>', $content);
				$content = preg_replace('/<form(.*)action=[\'"](.\/)+(.*)[\'"](.*)>/i', '<form$1action="./' . $row['page_name'] . '/$3"$4>', $content);
				$content = preg_replace('/<img(.*)src=[\'"](.\/)+(.*)[\'"](.*)>/i', '<img$1src="./' . $row['page_name'] . '/$3"$4>', $content);
				$contentList .= "<div class='tabContent$active' id='tabContent{$this->pageId}_{$row['page_id']}'>" . $INFOSTRING . $WARNINGSTRING . $ERRORSTRING . $content . "</div>";
			}
		}
		if( $tabList=="" ) displaywarning("No child pages are selected to display in this book.<br/> To change book settings click <a href='./+edit'>here</a> and to create child pages for this book, click <a href='./+settings#childpageform'>here</a>.");
		$tabList .= "</div>";
		$ret .= $tabList . $contentList . "</div>";
		$INFOSTRING = $backup_info;
		$WARNINGSTRING = $backup_warning;
		$ERRORSTRING = $backup_error;
		return $ret;
	}
	
	/**
	 * function actionEdit:
	 * @returns HTML Edit interface for book module's properties
	 */
	public function actionEdit() {
		if(isset($_POST['page_title'])) {
			$tList = "";
			$hList = "";
			$found = false;
			foreach($_POST as $key=>$val)
				if(substr($key,0,7) == "chkPage") {
					$tList .= substr($key,7) . ",";
					if(substr($key,7) == $_POST['optInitial'])
						$found = true;
				} elseif(substr($key,0,8) == "hidePage") {
					$hList .= substr($key,8) . ",";
				}
			$tList = rtrim($tList,",");
			$hList = rtrim($hList,",");
			if($found) {
				$this->bookProps['page_title'] = escape($_POST['page_title']);
				$this->bookProps['initial'] = escape($_POST['optInitial']);
				$this->bookProps['list'] = $tList;
				$this->bookProps['menu_hide'] = $hList;
				$this->hideInMenu();
				$query = "UPDATE `book_desc` SET `initial` = '" . escape($_POST['optInitial']) . "', `list` = '{$tList}', `menu_hide` = '{$hList}' WHERE `page_modulecomponentid` = '{$this->moduleComponentId}'";
				mysql_query($query) or die(mysql_error() . ": book.lib.php L:131");
				$query = "UPDATE `" . MYSQL_DATABASE_PREFIX . "pages` SET `page_title` = '" . $this->bookProps['page_title'] . "' WHERE `page_id` = '{$this->pageId}'";
				mysql_query($query) or die(mysql_error() . ": book.lib.php L:133");
				displayinfo("Book Properties saved properly");
			} else
				displayerror("You've choosen a hidden sub-page as default which is not possible, so the settings are not saved.");
		}
		$childrenQuery = 'SELECT `page_id`, `page_title`, `page_module`, `page_name`, `page_modulecomponentid` FROM `' . MYSQL_DATABASE_PREFIX . 'pages` WHERE `page_parentid` = '."'" . $this->pageId ."'". ' AND `page_id` != \'' . $this->pageId . '\' ORDER BY `page_menurank`';
		$result = mysql_query($childrenQuery);
		$table = "";
		$hide_list = explode(",",$this->bookProps['menu_hide']);
		$show_list = explode(",",$this->bookProps['list']);
		if(mysql_num_rows($result)) {
			$table = "<table><thead><td>Initial</td><td>Show in Tab</td><td>Hide in Menu</td><td>Page</td></thead>";
			while($row = mysql_fetch_assoc($result)) {
				$radio = "";
				if($row['page_id'] == $this->bookProps['initial'])
					$radio = "checked";
				$checkbox = "";
				$hide_checkbox = "";
				if(in_array($row['page_id'],$show_list))
					$checkbox = "checked=checked ";
				if(in_array($row['page_id'],$hide_list))
					$hide_checkbox = "checked=checked ";
				$table .= "<tr><td><input type='radio' name='optInitial' value='{$row['page_id']}' {$radio}></td><td><input type=checkbox name='chkPage{$row['page_id']}' {$checkbox}></td><td><input type=checkbox name='hidePage{$row['page_id']}' {$hide_checkbox}></td>";
				if(getPermissions($this->userId, $row['page_id'], "edit"))
					$table .= "<td><a href='{$row['page_name']}/+edit'>{$row['page_title']}</a></td></tr>";
				else
					$table .= "<td>{$row['page_title']}</td></tr>";
			}
			$table .= "</table>";
		} else {
			$table = "No child page available<br />";
		}
		$ret =<<<RET
<form action='./+edit' method=POST>
Title: <input type=text name="page_title" value="{$this->bookProps['page_title']}"><br />
{$table}
<input type=submit value=Save>
</form>
RET;
		return $ret;
	}

	/**
	 * function createModule:
	 * safedit module pages needs no initialization.
	 * will be called when safedit module instance is created.
	 */	
	public function createModule($compId) {
		$query = "INSERT INTO `book_desc` (`page_modulecomponentid` , `initial`, `list`,`menu_hide`)VALUES ('$compId','','','')";
		$result = mysql_query($query) or die(mysql_error()."book.lib L:187");
	}
	
	/**
	 * function deleteModule:
	 * delete all book module data corresponding to the passed page_modulecomponentid
	 * will be called when safedit module instance is getting deleted.
	 */
	public function deleteModule($moduleComponentId) {
		return true;
	}
	
	/**
	 * function copyModule:
	 * duplicates book with a new moduleComponentId
	 */
	public function copyModule($moduleComponentId,$newId) {
		displayinfo('The new copy of book module has to be configured manually');
		return true;
	}
	
	/**
	 * function tabScript:
	 * @returns Javascript which takes care of client-side page switching
	 */
	public function tabScript() {
		global $urlRequestRoot, $cmsFolder, $tabScriptDone;
		$ret = "";
		if(!$tabScriptDone)
			$ret =<<<RET

<script type="text/javascript">
<!--
var delay = 100;
var initialInfo = new Object();
$(document).ready(function() {
	$('.active').removeClass('active');
	activate({$this->pageId});
});
$(document).ready(function() {
	$('.tabElement').find("a").click(function() {
		var selid=$(this).attr('id');
		var selector = '#tab' + selid;
		var page = selector.substr(1,selector.indexOf('_')-1);
		var activeClasses = $('.active');
		
		for(i=0;i<activeClasses.length;i++) {
			var thisid = activeClasses.get(i).id; 
			if(page == thisid.substr(0,page.length)) {
				$('#' + thisid + ' .active').removeClass('active');
				$('#' + thisid).removeClass('active');
				$('#' + thisid.substr(3,thisid.length) + ' > span').removeClass('tabitemactive');
				$('#' + thisid).fadeOut(delay, function() {
			
					$(selector).fadeIn(delay).addClass('active');
					$('#'+selector.substr(4,selector.length)+' > span').addClass('tabitemactive');
					activate(selector.substr(selector.indexOf('_')+1));
				});
			}
		}
		return false;
	});
});
function activate(id) {
	if(initialInfo[id]) {
		$('#tabContent' + id + '_' + initialInfo[id]).fadeIn(delay);
		$('#tabContent' + id + '_' + initialInfo[id]).addClass('active');
	}
}
RET;
		else
			$ret = "<script type=\"text/javascript\">";
		$ret .=<<<RET
initialInfo[{$this->pageId}] = {$this->bookProps['initial']};
//-->
</script>
RET;
		$tabScriptDone = true;
		return $ret;
	}
	
	/**
	 * function isPresent:
	 * recursive function used to find if a page identified by $pageId is inside book identified by $parentId
	 * $parentId is page_id of the book(where we're searching) and not its page_moduleComponentId
	 */
	public function isPresent($parentId,$pageId) {
		$moduleComponentId = getModuleComponentIdFromPageId($parentId,'book');
		$list = mysql_fetch_assoc(mysql_query("SELECT `list` FROM `book_desc` WHERE `page_modulecomponentid` = '{$moduleComponentId}'"));
		$list = explode(",",$list['list']);
		foreach($list as $element) {
			if($pageId == $element)
				return true;
			if(getPageModule($element)=='book')
				return $this->isPresent($element,$pageId);
		}
		return false;
	}
	
	/**
	 * function hideInMenu:
	 * This function hides the specified child pages from global menu
	 */
	private function hideInMenu() {
		$cond = "";
		if($this->bookProps['menu_hide'] != "") {
			mysql_query("UPDATE `" . MYSQL_DATABASE_PREFIX . "pages` SET `page_displayinmenu` = 0 WHERE `page_parentid` = '{$this->pageId}' AND `page_id` IN ({$this->bookProps['menu_hide']})");
			$cond = " AND `page_id` NOT IN ({$this->bookProps['menu_hide']})";
		}
		mysql_query("UPDATE `" . MYSQL_DATABASE_PREFIX . "pages` SET `page_displayinmenu` = 1 WHERE `page_parentid` = '{$this->pageId}'{$cond}");
	}
}
?>
