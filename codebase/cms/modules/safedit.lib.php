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
 * Safedit module can be used to maintain uniformity among pages even if different people are editing.
 * It prevents custom formatting in page by striping all html tags from user,
 * and It provides ways to define absolutely necessary tags like <ul>, <ol>, <img>, <a>
 * Also it provides editing interface that requires no knowledge about HTML.
 * 
 * It stores its contents as sections in database table:
 * safedit_sections:
 *     page_modulecomponentid - used to record with safedit page instance this section belongs to
 *     section_id - unique id to differentiate each sections within each instance of safedit
 *     section_heading - heading for the section
 *     section_type - type of section
 *         Different types of sections are ('para','ulist','olist','picture'):
 *             para - Nomal section
 *             ulist - Content are displayed in unordered list(<ul>)
 *             olist - Content are displayed in ordered list(<ol>)
 *             picture - Content points to a uploaded picture, which will be displayed
 *     section_show - used to hide section(if needed)
 *     section_priority - used for ordering of sections
 *     section_content - Content of the section (Rendered according to the section type)
 */

class safedit implements module, fileuploadable {
	private $userId;
	private $moduleComponentId;
	private $action;
	
	/**
	 * function getHtml:
	 * Gateway through which CMS interacts with module
	 * This function will be called from getContent function of cms/content.lib.php
	 */
	public function getHtml($gotuid, $gotmoduleComponentId, $gotaction) {
		$this->userId = $gotuid;
		$this->moduleComponentId = $gotmoduleComponentId;
		$this->action = $gotaction;
		if ($this->action == "edit")
			return $this->actionEdit();
		return $this->actionView();
	}
	
	/**
	 * function actionView:
	 * View interface for all safedit module instances
	 * will be called from $this->getHtml function
	 */
	public function actionView() {
		$ret = "";
		$val = mysql_fetch_assoc(mysql_query("SELECT `page_title` FROM `" . MYSQL_DATABASE_PREFIX . "pages` WHERE `page_module` = 'safedit' AND `page_modulecomponentid` = '{$this->moduleComponentId}'"));
		$ret .= "<h1>".$val['page_title']."</h1>";
		$result = mysql_query("SELECT `section_id`,`section_heading`,`section_type`,`section_content` FROM `safedit_sections` WHERE `page_modulecomponentid` = '{$this->moduleComponentId}' AND `section_show` = 1 ORDER BY `section_priority`");
		while($row = mysql_fetch_assoc($result)) {
			if($row['section_heading']!="")
				$ret .= "<h2>".$row['section_heading']."</h2>";
			$ret .= "<div class='safedit_section'>";
			$safeContent = safe_html($row['section_content']);
			$type = $row['section_type'];
			if($type=="ulist") {
				$ret .= "<ul class='safedit_ulist'>";
				$contents = explode("\n",$safeContent);
				foreach($contents as $line) {
					$line = trim($line);
					if($line!="")
						$ret .= "<li>".$this->processView($line)."</li>";
				}
				$ret .= "</ul>";
			} else if($type=="olist") {
				$ret .= "<ol class='safedit_olist'>";
				$contents = explode("\n",$safeContent);
				foreach($contents as $line) {
					$line = trim($line);
					if($line!="")
						$ret .= "<li>".$this->processView($line)."</li>";
				}
				$ret .= "<ol>";
			} else if($type=="para") {
				$ret .= "<p class='safedit_para'>";
				$contents = explode("\n",$safeContent);
				foreach($contents as $line) {
					$ret .= $this->processView($line)."<br />";
				}
				$ret .= "</p>";
			} else if($type=="picture") {
				$ret .= "<div align='center'><img src='{$safeContent}'></div>";
			}
			$ret .= "</div>";
		}
		return $ret;
	}
	
	
	/**
	 * function actionEdit:
	 * Edit interface for all safedit module instances
	 * will be called from $this->getHtml function
	 */
	public function actionEdit() {
		$ret =<<<RET
<style type="text/css">
textarea {
	font-size: 130%;
	background: white;
}
</style>
RET;
		global $sourceFolder,$ICONS;
		require_once($sourceFolder."/upload.lib.php");
		submitFileUploadForm($this->moduleComponentId,"safedit",$this->userId,UPLOAD_SIZE_LIMIT);
		$end = "<fieldset id='uploadFile'><legend>{$ICONS['Uploaded Files']['small']}File Upload</legend>Upload files : <br />".getFileUploadForm($this->moduleComponentId,"safedit",'./+edit',UPLOAD_SIZE_LIMIT,5).getUploadedFilePreviewDeleteForm($this->moduleComponentId,"safedit",'./+edit').'</fieldset>';
		$val = mysql_fetch_assoc(mysql_query("SELECT `page_title` FROM `" . MYSQL_DATABASE_PREFIX . "pages` WHERE `page_module` = 'safedit' AND `page_modulecomponentid` = '{$this->moduleComponentId}'"));
		$ret .= "<h1>Editing '".$val['page_title']."' page</h1>";
		if(isset($_GET['subaction'])) {
			if($_GET['subaction']=="addSection") {
				$show = isset($_POST['sectionShow']);
				$heading = escape($_POST['heading']);
				$result = mysql_query("SELECT MAX(`section_id`)+1 as `section_id` FROM `safedit_sections` WHERE `page_modulecomponentid` = '{$this->moduleComponentId}'") or die(mysql_error());
				$row = mysql_fetch_row($result);
				$sectionId = $row[0];
				$result = mysql_query("SELECT MAX(`section_priority`)+1 as `section_priority` FROM `safedit_sections` WHERE `page_modulecomponentid` = '{$this->moduleComponentId}'");
				$row = mysql_fetch_row($result);
				$priority = $row[0];
				$query = "INSERT INTO `safedit_sections`(`page_modulecomponentid`,`section_id`,`section_heading`,`section_type`,`section_show`,`section_priority`) VALUES ('{$this->moduleComponentId}','{$sectionId}','{$heading}','" . escape($_POST['type']) . "','{$show}','{$priority}')";
				mysql_query($query) or die($query . "<br>" . mysql_error());
				if(mysql_affected_rows()>0)
					displayinfo("Section: {$heading}, created");
				else
					displayerror("Couldn't create section");
			} else if($_GET['subaction']=='deleteSection') {
				$sectionId = escape($_GET['sectionId']);
				$query = "DELETE FROM `safedit_sections` WHERE `page_modulecomponentid` = '{$this->moduleComponentId}' AND `section_id` = '{$sectionId}'";
				mysql_query($query) or die($query . "<br>" . mysql_error());
				if(mysql_affected_rows()>0)
					displayinfo("Section deleted succesfully");
				else
					displayerror("Couldn't delete section");
			} else if($_GET['subaction']=='saveSection') {
				$sectionId = escape($_POST['sectionId']);
				$heading = escape($_POST['heading']);
				$typeUpdate = isset($_POST['type'])?", `section_type` = '{$_POST['type']}'":'';
				$show = ", `section_show` = '" . isset($_POST['sectionShow']) . "'";
				$result = mysql_query("SELECT `section_type` FROM `safedit_sections` WHERE `page_modulecomponentid` = '{$this->moduleComponentId}' AND `section_id` = '{$sectionId}'");
				$row = mysql_fetch_row($result);
				$type = $row[0];
				if($type=="para"||$type=="ulist"||$type=="olist")
					$sectionContent = escape($this->processSave($_POST['content']));
				else if($type=="picture")
					$sectionContent = escape($_POST['selectFile']);
				$query = "UPDATE `safedit_sections` SET `section_heading` = '{$heading}', `section_content` = '{$sectionContent}'{$typeUpdate}{$show} WHERE `page_modulecomponentid` = '{$this->moduleComponentId}' AND `section_id` = '{$sectionId}'";
				mysql_query($query) or die($query . "<br>" . mysql_error());
				if(mysql_affected_rows()>0)
					displayinfo("Section saved successfully");
			} else if($_GET['subaction']=='moveUp'||$_GET['subaction']=='moveDown') {
				$compare = $_GET['subaction']=='moveUp'?'<=':'>=';
				$arrange = $_GET['subaction']=='moveUp'?'DESC':'ASC';
				$sectionId = escape($_GET['sectionId']);
				$query = "SELECT `section_id`,`section_priority` FROM `safedit_sections` WHERE `page_modulecomponentid` = '{$this->moduleComponentId}' AND `section_priority` '{$compare}' (SELECT `section_priority` FROM `safedit_sections` WHERE `page_modulecomponentid` = '{$this->moduleComponentId}' AND `section_id` = '{$sectionId}') ORDER BY `section_priority` '{$arrange}' LIMIT 2";
				$result = mysql_query($query);
				$row = mysql_fetch_row($result);
				$sid = $row[0]; $spr = $row[1];
				if($row = mysql_fetch_row($result)) {
					mysql_query("UPDATE `safedit_sections` SET `section_priority` = '{$spr}' WHERE `page_modulecomponentid` = '{$this->moduleComponentId}' AND `section_id` = '{$row[0]}'");
					mysql_query("UPDATE `safedit_sections` SET `section_priority` = '{$row[1]}' WHERE `page_modulecomponentid` = '{$this->moduleComponentId}' AND `section_id` = '{$sid}'");
				}
			} else if($_GET['subaction']=='moveTop'||$_GET['subaction']=='moveBottom') {
				$sectionId = escape($_GET['sectionId']);
				$cpri = mysql_fetch_row(mysql_query("SELECT `section_priority` FROM `safedit_sections` WHERE `page_modulecomponentid` = '{$this->moduleComponentId}' AND `section_id` = '{$sectionId}'")) or die(mysql_error());
				if($_GET['subaction']=='moveTop') {
					$sign = '+';
					$cmpr = '<';
					$set = '0';
				} else {
					$sign = '-';
					$cmpr = '>';
					$set = mysql_fetch_row(mysql_query("SELECT MAX(`section_priority`) FROM `safedit_sections` WHERE `page_modulecomponentid` = '{$this->moduleComponentId}'")) or die(mysql_error());
					$set = isset($set[0])?$set[0]:'';
				}
				$cmpr = $_GET['subaction']=='moveTop'?'<':'>';
				$query = "UPDATE `safedit_sections` SET `section_priority` = `section_priority`{$sign}1 WHERE `page_modulecomponentid` = '{$this->moduleComponentId}' AND `section_priority` {$cmpr} '{$cpri[0]}'";
				mysql_query($query) or die(mysql_error());
				mysql_query("UPDATE `safedit_sections` SET `section_priority` = '{$set}' WHERE `page_modulecomponentid` = '{$this->moduleComponentId}' AND `section_id` = '{$sectionId}'") or die(mysql_error());
			}
		}
		
		$result = mysql_query("SELECT `section_id`,`section_heading`,`section_type`,`section_content`,`section_show` FROM `safedit_sections` WHERE `page_modulecomponentid` = '{$this->moduleComponentId}' ORDER BY `section_priority`");
		while($row = mysql_fetch_assoc($result)) {
			$show = $row['section_show']?'checked ':'';
			$type = $row['section_type'];
			$help = $type!="picture"?" <a href='#help' title='Only Plain text allowed, Click to know more'>{$ICONS['Help']['small']}</a>":'';
			$ret .= <<<RET
<form action='./+edit&subaction=saveSection' method=POST><input type=hidden value='{$row['section_id']}' name='sectionId' /><fieldset><legend><input type=checkbox name='sectionShow' {$show}/><input type=text name=heading value='{$row['section_heading']}' style='border:0;background:none;' /> <a href='./+edit&subaction=moveUp&sectionId={$row['section_id']}' title='Move one level Up'>{$ICONS['Up']['small']}</a> <a href='./+edit&subaction=moveDown&sectionId={$row['section_id']}' title='Move one level Down'>{$ICONS['Down']['small']}</a> <a href='./+edit&subaction=moveTop&sectionId={$row['section_id']}' title='Move to Top'>{$ICONS['Top']['small']}</a> <a href='./+edit&subaction=moveBottom&sectionId={$row['section_id']}' title='Move to Bottom'>{$ICONS['Bottom']['small']}</a> <a href='./+edit&subaction=deleteSection&sectionId={$row['section_id']}' title='Delete Section'>{$ICONS['Delete Section']['small']}</a>{$help}</legend><div class='safedit_section'>
RET;
			$safeContent = safe_html($row['section_content']);
			if($type=="ulist"||$type=="olist"||$type=="para") {
				$usel = $type=="ulist"?' selected':'';
				$osel = $type=="olist"?' selected':'';
				$psel = $type=="para"?' selected':'';
				$ret .=<<<PARA
<textarea name=content rows=7 style="width:100%">{$safeContent}</textarea>
<select name=type>
<option value="para"$psel>Paragraph</option>
<option value="ulist"$usel>List</option>
<option value="olist"$osel>Numbered List</option>
</select>
PARA;
			} else if($type=="picture") {
				$files = getUploadedFiles($this->moduleComponentId,"safedit");
				$ret .= "<a href='#uploadFile'>Upload File</a><br /><select name=selectFile><option value=''>No picture</option>";
				foreach($files as $currFile) {
					$select = $row['section_content']==$currFile['upload_filename']?' selected':'';
					$ret .="<option value='{$currFile['upload_filename']}'{$select}>{$currFile['upload_filename']}</option>";
				}
				$ret .= "</select>";
			}
			$ret .=<<<SUBMIT
<input type=submit value='Save section' /></div></fieldset></form>
SUBMIT;
		}
		
		$ret .= <<<RET
<fieldset>
<legend>{$ICONS['Add']['small']}Create New Section</legend>
<form action="./+edit&subaction=addSection" method=POST>
<select name='type'>
<option value="para">Paragraph</option>
<option value="ulist">List</option>
<option value="olist">Numbered List</option>
<option value="picture">Picture</option>
</select>
<input type=text name="heading" />
<input type=checkbox name="sectionShow" checked />
<input type=submit value="Add section" name="btnAddSection" />
</form>
</fieldset>
RET;
		
		$ret .= $end;
		$ret .= <<<RET
<small id="help"><ul><li>You can display only Plain text, any custom formatting will be prevented.<br />To make a link, enclose the text with '{' and '}' and add the target to the end of the line after '|'<br />For eg:<br />{This is a link}, and this is not a link|http://www.google.com<br />The above line will make a link to google.com</li><li>Leave section heading text box blank(without even spaces) to avoid displaying Heading</li></ul></small>
RET;
		return $ret;
	}
	
	/**
	 * function processView:
	 * processView function will process the line and insert links, if any intended
	 * will be called from $this->actionView function
	 * it is assumed that only a single line is passed as argument, and if a link description exists, will be valid
	 * 
	 * Since all html tags in the page's content will be striped by the safe_html() function
	 * We'll be using the following scheme to describe a link a safedit pages:
	 * 
	 * some_content {some_content} some_content | link_target
	 * the some_content enclosed by {} will be converted to link with href=link_target
	 * if {} is not found, then the whole line is converted to link with href=link_target
	 */
	
	function processView($line) {
		$arr = explode("|",$line,2);
		if(isset($arr[1])) {
			$in = explode("{",$arr[0],2);
			if(isset($in[1])) {
				$inn = explode("}",$in[1],2);
				return $in[0] . "<a href='{$arr[1]}'>{$inn[0]}</a>" . $inn[1];
			} else {
				return "<a href='{$arr[1]}'>{$arr[0]}</a>";
			}
		}
		return $line;
	}
	
	/**
	 * function processSave:
	 * in safedit module {,},| are reserved to specify links
	 * so remove reserved chars if inappropriately placed
	 * Eg:
	 * something } something | link } something
	 * 
	 * a invalid link description as above has to be avoided
	 * actually it wont bother, the output of processView for the above input will be:
	 * <a href='link } something'>something } something</a>
	 * so warn for wrong description, give suggestion or remove it
	 */
	function processSave($content) {
		$arr = explode("\n",$content);
		$out = array();
		foreach($arr as $content) {
			if(strpos($content,"|")!=strrpos($content,"|"))
				$content = str_replace("|","",$content);
			if(strpos($content,"{")!=strrpos($content,"{"))
				$content = str_replace("{","",$content);
			if(strpos($content,"}")!=strrpos($content,"}"))
				$content = str_replace("}","",$content);
			if(strpos($content,"}")<strpos($content,"{")||strpos($content,"{")===FALSE) {
				$content = str_replace("{","",$content);
				$content = str_replace("}","",$content);
			}
			if(strpos($content,"}")>strpos($content,"|")) {
				$content = str_replace("|","",$content);
				$content = str_replace("{","",$content);
				$content = str_replace("}","",$content);
			}
			$out[] = $content;
		}
		$content = implode("\n",$out);
		return $content;
	}
	
	/**
	 * interface to upload.lib.php
	 * 
	 * getFileAccessPermission, getUploadableFileProperties
	 */
	public static function getFileAccessPermission($pageId,$moduleComponentId,$userId, $fileName) {
		return getPermissions($userId, $pageId, "view");
	}

	public static function getUploadableFileProperties(&$fileTypesArray,&$maxFileSizeInBytes) {
		$fileTypesArray = array('jpg','jpeg','png','gif','bmp','tiff');
		$maxFileSizeInBytes = 31457280;
	}
	
	/**
	 * function createModule:
	 * safedit module pages needs no initialization.
	 * will be called when safedit module instance is created.
	 */
	public function createModule($moduleComponentId) {
		///No initialization
	}
	
	/**
	 * function deleteModule:
	 * delete all sections with page_modulecomponentid = to the passed argument
	 * will be called when safedit module instance is getting deleted.
	 */
	public function deleteModule($moduleComponentId) {
		return true;
	}
	
	/**
	 * function copyModule:
	 * duplicates all sections with a new page_modulecomponentid
	 */
	public function copyModule($moduleComponentId,$newId) {
		return true;
	}
}
?>
