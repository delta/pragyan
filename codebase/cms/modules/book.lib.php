<?php
/**
 * @package pragyan
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 * UNDER CONSTRUCTION
 */

class book implements module {
	private $userId;
	private $moduleComponentId;
	private $action;
	private $pageId;

	public function getHtml($gotuid, $gotmoduleComponentId, $gotaction) {
		$this->userId = $gotuid;
		$this->moduleComponentId = $gotmoduleComponentId;
		$this->action = $gotaction;
		$this->pageId = getPageIdFromModuleComponentId("book",$gotmoduleComponentId);
		$this->bookProps = mysql_fetch_assoc(mysql_query("SELECT * FROM `book_desc` WHERE `page_modulecomponentid` = '{$this->moduleComponentId}'"));
		$this->bookProps['list'] = explode(",",$this->bookProps['list']);
		$this->hideInMenu();
		if ($this->action == "edit")
			return $this->actionEdit();
		return $this->actionView();
	}
	
	public function actionView() {
		global $INFOSTRING, $WARNINGSTRING, $ERRORSTRING;

		$childrenQuery = 'SELECT `page_id`, `page_title`, `page_module`, `page_modulecomponentid`, `page_name` FROM `' . MYSQL_DATABASE_PREFIX . 'pages` WHERE `page_parentid` = ' . $this->pageId . ' AND `page_id` != ' . $this->pageId . ' ORDER BY `page_menurank`';
		$result = mysql_query($childrenQuery);
		$ret = $this->tabStyle() . $this->tabScript();
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
			if(!in_array($row['page_id'],$this->bookProps['list']))
				continue;
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
				$query = "UPDATE `book_desc` SET `page_title` = '" . escape($_POST['page_title']) . "', `initial` = '" . escape($_POST['optInitial']) . "', `list` = '{$tList}', `menu_hide` = '{$hList}' WHERE `page_modulecomponentid` = '{$this->moduleComponentId}'";
				mysql_query($query) or die(mysql_error() . ": book.lib L:5");
				displayinfo("Book Properties saved properly");
				$this->bookProps['page_title'] = escape($_POST['page_title']);
				$this->bookProps['initial'] = escape($_POST['optInitial']);
				$this->bookProps['list'] = explode(",",$tList);
				$this->bookProps['menu_hide'] = $hList;
				$this->hideInMenu();
			} else
				displayerror("You've choosen a hidden sub-page as default which is not possible, so the settings are not saved.");
		}
		$childrenQuery = 'SELECT `page_id`, `page_title`, `page_module`, `page_name`, `page_modulecomponentid` FROM `' . MYSQL_DATABASE_PREFIX . 'pages` WHERE `page_parentid` = ' . $this->pageId . ' AND `page_id` != ' . $this->pageId . ' ORDER BY `page_menurank`';
		$result = mysql_query($childrenQuery);
		$table = "";
		$hide_list = explode(",",$this->bookProps['menu_hide']);
		if(mysql_num_rows($result)) {
			$table = "<table><thead><td>Initial</td><td>Show in Tab</td><td>Hide in Menu</td><td>Page</td></thead>";
			while($row = mysql_fetch_assoc($result)) {
				$radio = "";
				if($row['page_id'] == $this->bookProps['initial'])
					$radio = "checked";
				$checkbox = "";
				$hide_checkbox = "";
				if(in_array($row['page_id'],$this->bookProps['list']))
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
			$table = "No child page available";
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
	
	public function createModule(&$moduleComponentId) {
		$query = "SELECT MAX(page_modulecomponentid) as MAX FROM `book_desc` ";
		$result = mysql_query($query) or die(mysql_error() . "book.lib L:1");
		$row = mysql_fetch_assoc($result);
		$compId = $row['MAX'] + 1;
		
		$query = "INSERT INTO `book_desc` (`page_modulecomponentid` ,`page_title`, `initial`, `list`,`menu_hide`)VALUES ('$compId', '" . escape($_POST['childpagename']) . "','0','','0')";
		$result = mysql_query($query) or die(mysql_error()."book.lib L:76");
		if (mysql_affected_rows()) {
			$moduleComponentId = $compId;
			return true;
		} else
			return false;
	}

	public function deleteModule($moduleComponentId) {
		$result = mysql_query("DELETE FROM `book_desc` WHERE `page_modulecomponentid` = '{$moduleComponentId}'");
		if(mysql_affected_rows())
			return true;
		else
			return false;
	}
	
	public function copyModule($moduleComponentId) {
		$result = mysql_fetch_assoc(mysql_query("SELECT * FROM `book_desc` WHERE `page_modulecomponentid` = '{$moduleComponentId}'"));
		$max = mysql_fetch_row(mysql_query("SELECT MAX(`page_modulecomponentid`) AS 'max' FROM `book_desc`"));
		$compId = $max[0] + 1;
		$query = mysql_query("INSERT INTO `book_desc`(`page_modulecomponentid` ,`page_title`, `initial`, `list`)VALUES ('$compId', '{$result['page_title']}','','')");
	}
	
	public function tabStyle() {
		global $tabStyleDone;
		$ret = "";
		if(!$tabStyleDone)
			$ret =<<<RET
<style>

</style>
RET;
		$tabStyleDone = true;
		return $ret;
	}
	
	public function tabScript() {
		global $urlRequestRoot, $cmsFolder, $tabScriptDone;
		$ret = "";
		if(!$tabScriptDone)
			$ret =<<<RET

<script type="text/javascript">
var delay = 500;
var initialInfo = new Object();
$(document).ready(function() {
	activate({$this->pageId});
});
$(document).ready(function() {
	$('.tabElement').find("a").click(function() {
		var selector = '#tab' + $(this).attr('id');
		var page = selector.substr(1,selector.indexOf('_')-1);
		var activeClasses = $('.active');
		for(i=0;i<activeClasses.length;i++) {
			var thisid = activeClasses.get(i).id; 
			if(page == thisid.substr(0,page.length)) {
				$('#' + thisid + ' .active').removeClass('active');
				$('#' + thisid).removeClass('active');
				$('#' + thisid).fadeOut(delay, function() {
			
					$(selector).fadeIn(delay).addClass('active');
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
		$ret .= "initialInfo[{$this->pageId}] = {$this->bookProps['initial']};</script>";
		$tabScriptDone = true;
		return $ret;
	}
	
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
	private function hideInMenu() {
		$cond = "";
		if($this->bookProps['menu_hide'] != "") {
			mysql_query("UPDATE `" . MYSQL_DATABASE_PREFIX . "pages` SET `page_displayinmenu` = 0 WHERE `page_parentid` = '{$this->pageId}' AND `page_id` IN ({$this->bookProps['menu_hide']})");
			$cond = " AND `page_id` NOT IN ({$this->bookProps['menu_hide']})";
		}
		mysql_query("UPDATE `" . MYSQL_DATABASE_PREFIX . "pages` SET `page_displayinmenu` = 1 WHERE `page_parentid` = '{$this->pageId}'{$cond}");
	}
}

 /**
  * -Jack
  */

?>
