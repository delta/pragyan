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
 * pagesettings.lib.php: Everything to do with page settings, creation, and moving or copying of pages
 */

/**
 * Retrieves the module types a person can create at a given pageid
 * @param $userid Integer indicating the user id of the person
 * @param $pageid Integer indicating the page id of the page at which the user is trying to create the page
 * @return List of modules the person has permission to create at (as a child of) the given page
 */
function getCreatablePageTypes($userid, $pageid) {
	$moduleQuery = "SELECT `page_module` FROM `" . MYSQL_DATABASE_PREFIX . "permissionlist` WHERE `perm_action` = 'create'";
	$moduleResult = mysql_query($moduleQuery);
	$creatableModules = array();

	while ($moduleResultRow = mysql_fetch_row($moduleResult))
		if (getPermissions($userid, $pageid, "create", $moduleResultRow[0]))
			$creatableModules[] = $moduleResultRow[0];
	return $creatableModules;
}

/**
 * Generate HTML for a form to help edit settings for a given page
 * @param $pageId Page id of the requested page
 * @param $userId User id of the current user
 * @return String containing HTML of the generated form, or a null string if required data could not be found
 */
function getSettingsForm($pageId, $userId) {
	$pageId=escape($pageId);
	$page_query = "SELECT `page_name`, `page_title`, `page_displaymenu`, `page_displayinmenu`, `page_displaysiblingmenu` , `page_module`, `page_displaypageheading`,`page_template`,`page_modulecomponentid`, `page_menutype`, `page_menudepth` , `page_displayinsitemap` ,`page_displayicon`" .
	"FROM `" . MYSQL_DATABASE_PREFIX . "pages` WHERE `page_id`='" . $pageId."'";
	$page_result = mysql_query($page_query);
	$page_values = mysql_fetch_assoc($page_result);
	
	
	
	$chkquery="SELECT `value` FROM `".MYSQL_DATABASE_PREFIX."global` WHERE `attribute`='allow_pagespecific_template'";
	$row=mysql_fetch_row(mysql_query($chkquery));
 	$allow_pagespecific_templates=$row[0]; // 0 if disabled, 1 if enabled
	
	if (!$page_values) {
		return '';
	}
	global $ICONS;
	$modifiers = '';
	$showInMenuBox = '';
	$showInSiteMap = '';
	if ($pageId == 0) 
		$modifiers = 'disabled="disabled" ';
		$showInMenuBox = '<tr><td ><label for="showinmenu">Show page in menu bar</td></label><td><input type="checkbox" name="showinmenu" id="showinmenu" ' . ($page_values['page_displayinmenu'] == 1 ? 'checked="checked" ' : '') . '/></td></tr>';
		$showInSiteMap = '<tr><td ><label for="showinsitemap">Show page in site map</td></label><td><input type="checkbox" name="showinsitemap" id="showinsitemap" ' . ($page_values['page_displayinsitemap'] == 1 ? 'checked="checked" ' : '') . '/></td></tr>';

	$showmenubar = ($page_values['page_displaymenu'] == 1 ? 'checked="checked" ' : '');
	$showsiblingmenu = $page_values['page_displaysiblingmenu'] == 1 ? 'checked="checked" ' : '';
	$showheading = ($page_values['page_displaypageheading'] == 1 ? 'checked="checked"' : '');
	$dbPageTemplate = $page_values['page_template']; 
	$modulecomponentid=$page_values['page_modulecomponentid'];
	$displayicon = ($page_values['page_displayicon'] == 1 ? 'checked="checked" ' : '');
	$templates = getAvailableTemplates();
	$page_query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "pages` WHERE `page_parentid` = '$pageId' AND `page_parentid` != `page_id` ORDER BY `page_menurank` ASC  ";
	$page_result = mysql_query($page_query) or die(mysql_error());
	$childList ="";
	$isLeaf = false;
	if(mysql_num_rows($page_result)==0){
	    $isLeaf = true;
		$childList = "There are no child pages associated with this page.";
	}
	else
		$childList = "<table border=\"1\" width=\"100%\"><tr><th>Child Pages</th><th>Display in menu bar</th><th>Display in Sitemap</th><th>Display Icon in menu</th><th>Move page up</th><th>Move page down</th><th>Delete</th></tr>";
	while ($page_result_row = mysql_fetch_assoc($page_result)) {
		$childList .= '<tr><td><a href="./'.$page_result_row['page_name'].'+settings">' . $page_result_row['page_title'] . '</a></td>' .
				'<td><input type="checkbox" name="menubarshowchildren[]" id="'.$page_result_row['page_name'].'" value="' . $page_result_row['page_name'] . '" ' . ($page_result_row['page_displayinmenu'] == 1 ? 'checked="yes" ' : '') . '/></td>'.
				'<td><input type="checkbox" name="sitemapshowchildren[]" id="'.$page_result_row['page_name'].'" value="' . $page_result_row['page_name'] . '" ' . ($page_result_row['page_displayinsitemap'] == 1 ? 'checked="yes" ' : '') . '/></td>'.
				'<td><input type="checkbox" name="childrenshowicon[]" id="'.$page_result_row['page_name'].'" value="' . $page_result_row['page_name'] . '" ' . ($page_result_row['page_displayicon'] == 1 ? 'checked="yes" ' : '') . '/></td>'.
				'<td align="center"><input type="submit" name="moveUp" onclick="this.form.action+=\''.$page_result_row['page_name'].'\'" value="Move Up" /></td>' .
				'<td align="center"><input type="submit" name="moveDn" onclick="this.form.action+=\''.$page_result_row['page_name'].'\'" value="Move Down" /></td>' .
				'<td align="center"><input type="submit" name="deletePage" onclick="javascript:if(checkDelete(this,\''.$page_result_row['page_name'].'\'))this.form.action+=\''.$page_result_row['page_name'].'\'"  value="Delete" /></td></tr>';
	}
	if(!mysql_num_rows($page_result)==0)
		$childList .= "</table>";

/* PAGE INHERITED INFO */
	$inheritedInfo = $inheritedPagePath = $inheritedInfoEncoded = '';
	$inheritedPageId = getPageInheritedInfo($pageId, $inheritedInfo);
	if($inheritedPageId == $pageId)
		$inheritedInfoEncoded = htmlentities($inheritedInfo);
	if($inheritedPageId >= 0) {
		$inheritedPagePath = getPagePath($inheritedPageId);
		global $urlRequestRoot;
		$inheritedPagePath = "<a href=\"$urlRequestRoot$inheritedPagePath+settings\">$inheritedPagePath</a>";
		if($inheritedPageId != $pageId)
			$inheritedPagePath .= ' (Browse to this page to edit the inherited information.)';
	}
	$inheritedInfoText = <<<INHERITEDINFO
	<a name="inheritinfoform"></a>
		<fieldset style="padding: 8px">
			<legend>{$ICONS['Page Inherited Info']['small']}Inherited Information</legend>
			
			<form name="pagesettings" action="./+settings&subaction=editinheritedinfo" method="POST">
				<table>
					<tr>
						<td>Inherited Information:</td>
						<td>
							<div>
								$inheritedInfo
							</div>
						</td>
					</tr>
					<tr>
						<td>Inherited From:</td>
						<td>$inheritedPagePath</td>
					</tr>
					<tr>
						<td>Add/Edit inherited information for this page:</td>
						<td>
							<textarea name="txtInheritedInfo" style="width:98%;" rows="8" cols="80" wrap="virtual">$inheritedInfoEncoded</textarea>
						</td>
					</tr>
				</table>
				<input type="submit" name="btnSubmit" value="Submit" />
			</form>
		</fieldset>
INHERITEDINFO;


/* PAGE CREATE TEXT*/
	$createdPageSettingsText = "";
	$dbPageTemplateDisabled="";
	$dbPageDefTemplate="";
	if($dbPageTemplate==DEF_TEMPLATE)
	{
		$dbPageDefTemplate="checked";
		$dbPageTemplateDisabled="disabled=true";
	}

	if(getPageModule($pageId)=="link") {
		$dereferencePagePathIds = array();
		parseUrlDereferenced($pageId, $dereferencePagePathIds);
		$dereferencePagePath = "";
		foreach ($dereferencePagePathIds as $page) {
			$info = getPageInfo($page);
			$dereferencePagePath .= $info['page_name']."/";
		}
		global $urlRequestRoot;
		$createdPageSettingsText = "<a name=\"childpageform\"></a> <fieldset>
        <legend>{$ICONS['Create New Page']['small']}Create Child Page</legend>Please use the <a href='".$urlRequestRoot."/".$dereferencePagePath."+settings'>linked page settings</a> to create a child page.</fieldset>";
	}
	else if(getPageModule($pageId)=="external") {
		$createdPageSettingsText = "<a name=\"childpageform\"></a> <fieldset>
        <legend>{$ICONS['Create New Page']['small']}Create Child Page</legend>You cannot create a child page of a page of an \"external link\".</fieldset>";
	}
	else {
		$generatedTree = generateDirectoryTree($userId, "page", "settings", 0)->toHtml('childPageTreeContainer', 'childPageTree', 'childpagelink');
		$creatableTypesText = '<option value=""> </option><option value="menu">Menu</option><option value="link">Link</option><option value="external">External Link</option>';
		$createblePageTypes = getCreatablePageTypes($userId, $pageId);
		foreach ( $createblePageTypes as $creatableType) {
			$creatableTypesText .= "<option value=\"$creatableType\">".ucfirst($creatableType)."</option>";
		}
		
		$createdPageSettingsText =<<<CREATE
		    <form name="pagesettings" action="./+settings&subaction=create" onsubmit="return childOnSubmit();" method="POST">
		    <script type="text/javascript" language="javascript">
			<!--
				function childOnSubmit(){
					if(document.getElementById("childpagetype").selectedIndex==0) { alert("Please select a page type."); return false;}
					if(document.getElementById("childpagename").value=="") {alert("Please fill the page name"); return false;}
					if(document.getElementById("childpagelink").value=="" && document.getElementById("childpagetype").selectedIndex==2) {alert("Please select the linked page path"); return false;}
					if(document.getElementById("externallink").value=="" && document.getElementById("childpagetype").selectedIndex==3) {alert("Please enter the external page path"); return false;}
				}
				function childShowTree(obj) {

					if(obj.selectedIndex==2) {
						document.getElementById("childlinktree").style.display="";
						document.getElementById("childlinkentry").style.display="";
						document.getElementById("childlinkentry1").style.display="";
					}
					else {
						document.getElementById("childlinktree").style.display="none";
						document.getElementById("childlinkentry").style.display="none";	
						document.getElementById("childlinkentry1").style.display="none";
					}
					if(obj.selectedIndex==3) {
						document.getElementById("externallinktr").style.display="";
						document.getElementById("externallinktr1").style.display="";
					}
					else {
						document.getElementById("externallinktr").style.display="none";
						document.getElementById("externallinktr1").style.display="none";
					}

					if(obj.selectedIndex==2 || obj.selectedIndex==3)
					{
						document.getElementById("fieldsetTemplate").style.display="none";	
					}
					else document.getElementById("fieldsetTemplate").style.display="";
				}
				function toggleSelTemplate1()
				{
					var obj=document.getElementsByName('page_template')[1];
					obj.disabled=(obj.disabled==true?false:true);
		
				}
			-->
		</script>
		  <a name="childpageform"></a>
	 <fieldset>
        <legend>{$ICONS['Create New Page']['small']}Create Child Page</legend>
      
        <table>
        	<tr>
        		<td valign="top">
					<table border="1">
				        <tr><td>Page type:</td><td><select name="childpagetype" id="childpagetype" onchange="childShowTree(this);">$creatableTypesText</select></td></tr>
				        <tr><td>Page name:</td><td><input type="text" name="childpagename" id="childpagename" /></td></tr>
				        <tr id="childlinkentry" style="display:none"><td>Page link:</td><td><input type="text" name="childpagelink" id="childpagelink" /></td></tr>
						<tr id="childlinkentry1" style="display:none"><td>Open the child page in:</td><td><select name="linkselect" id="linktype"><Option>Same Tab</option><option>New Tab</option></select></td></tr>
				        <tr id="externallinktr" style="display:none"><td>External link:</td><td><input type="text" name="externallink" id="externallink" /></td></tr>
						<tr id="externallinktr1" style="display:none"><td>Open the child page in:</td><td><select name="linkselectex" id="linktypeex"><Option>Same Tab</option><option>New Tab</option></select></td></tr>
					</table>
				</td>
				<td id="childlinktree" style="display:none">Click to select link path :
					$generatedTree
				</td>
			</tr>
		</table>
CREATE;
	
		
			$createdPageSettingsText.=<<<CREATE
			<fieldset id="fieldsetTemplate">
			<legend>Template</legend>
CREATE;
	if($allow_pagespecific_templates==1)
	{
		$createdPageSettingsText.=<<<CREATE
			<table>
			<tr>
			<td>Use Default Template ?</td>
			<td><input type='checkbox' name='default_template' value='yes' onchange="toggleSelTemplate1()" $dbPageDefTemplate /></td>
			</tr>
			<tr>
			<td>Select Template</td>
			<td><select name='page_template' $dbPageTemplateDisabled>
CREATE;
			for($i=0; $i<count($templates); $i++)
			{
				if($templates[$i]==$dbPageTemplate)
				$createdPageSettingsText.="<option value='".$templates[$i]."' selected >".ucwords($templates[$i])."</option>";
				else
				$createdPageSettingsText.="<option value='".$templates[$i]."' >".ucwords($templates[$i])."</option>";
			}
			$createdPageSettingsText.="
			</select>
	
			</tr>
	
			</table>";
	}
	else 
	$createdPageSettingsText.="Page-specific templates are disabled. Please enable it from Global Settings in Admin or click <a href='./+admin&subaction=global'>here</a>";
			
		
		
		
		$createdPageSettingsText.=<<<CREATE
		</fieldset><br/>
	   	<input type="submit" name="btnSubmit2" value="Submit" />&nbsp;&nbsp;<input type="reset" name="btnReset" value="Reset" />
      </fieldset>
      </form>
CREATE;
	}
/* PAGE CREATE TEXT ENDS*/


/* PAGE MOVE COPY TEXT BEGINS */
	$generatedTree = generateDirectoryTree($userId, "page", "settings", 0)->toHtml('fileCopyTreeContainer', 'fileCopyTree', "parentpagepath");
	$movecopyPageSettingsText =<<<MOVECOPY
		<script type="text/javascript" language="javascript">
			function moveOnSubmit(){
				if(document.getElementById("parentpagepath").value=="") {alert("Please fill the page path"); return false;}
				if(document.getElementById("destinationpagetitle").value=="") { alert("Please select a page title."); return false;}
				if(document.getElementById("destinationpagename").value=="") {alert("Please fill the page name"); return false;}
			}
			function movecopyChange(obj){
				if(obj.checked==true)
					document.getElementById("recursivelycopypage").disabled=true;
				else
					document.getElementById("recursivelycopypage").disabled=false;
			}
		-->
	</script>
	<form name="pagesettings" action="./+settings&subaction=move" onsubmit="return moveOnSubmit()" method="POST">
	  <a name="copymovepageform"></a>
	 <fieldset>
        <legend>{$ICONS['Copy or Move Page']['small']}Copy or Move Page</legend>
      
		<table border="1">
			<tr>
				<td valign="top">
			        <table border="1" cellpadding="2px" cellspacing="2px">
			        	<tr><td colspan="2">Click on the generated page tree to select the parent page path : </td></tr>
			          <tr><td>Path of the distination parent page :</td><td><input type="text" id="parentpagepath" name="parentpagepath"/></td></tr>
			          <tr><td>Destination page title:</td><td><input type="text" name="destinationpagetitle" id="destinationpagetitle" value="{$page_values['page_title']}"/></td></tr>
			          <tr><td>Destination page name:</td><td><input type="text" name="destinationpagename" id="destinationpagename" value="{$page_values['page_name']}"/></td></tr>
 			          <tr><td><label for="deleteoriginalpage">Delete original entry (Move instead of Copy)</label></td><td><input type="checkbox" name="deleteoriginalpage" id="deleteoriginalpage" checked="true" onclick="movecopyChange(this);"/></td></tr>
 			          <tr><td><label for="recusivelycopypage">Copy recursively? (in case of Copy)</label></td><td><input type="checkbox" name="recursivelycopypage" id="recursivelycopypage" disabled="true" /></td></tr>
 			        </table>
 			        Legend:
 			        <table cellpadding="2px" cellspacing="2px">
 			        	<tr><td style="border: 1px solid black; width: 18px; background-color: #E8FFE8"></td><td>Accessible Items</td></tr>
 			        	<tr><td style="border: 1px solid black; width: 18px; background-color: #FFE8E8"></td><td>Inaccessible Items</td></tr>
 			        </table>
			    </td>
			    <td valign="top">
					<div id="pathtree">Click to select destination path : $generatedTree</div>
			    </td>
			</tr>
		</table>

	    	<input type="submit" name="btnSubmit2" value="Submit" />&nbsp;&nbsp;<input type="reset" name="btnReset" value="Reset" />
      </fieldset>
      </form>

MOVECOPY;
/* PAGE MOVE COPY TEXT ENDS */

	global $pageFullPath;
	global $STARTSCRIPTS;
	$STARTSCRIPTS.="toggleMenuType();";
	$parentPath = ($pageId==0?'':'<a href="../+settings">Parent page link.</a>');
	$pageType=ucfirst($page_values['page_module']);
	$menuType=$page_values['page_menutype'];
	$menudepth=$page_values['page_menudepth'];
	$classictype="";
	$multidepthtype="";
	$completetype="";
	$changeLink="";
	$name="";
	$generatedTree = "";
	$linkmcid = getDereferencedPageId($pageId);
	if($pageType=="Link"){
	$link = getPagePath($linkmcid);
	$generatedTree = "<tr><td colspan=2><div>Choose a link:".generateDirectoryTree($userId, "page", "settings", 0)->toHtml('linkTreeContainer', 'linkTree', 'link')."</div></td></tr>";
	$changeLink = "<tr><td>Internally Linked To:</td><td><input type=text name='link' id='link' value=$link></td></tr>"; 
	}
	if($pageType == "External"){
	$linkquery = "SELECT `page_extlink` FROM `" . MYSQL_DATABASE_PREFIX . "external` WHERE page_modulecomponentid = ".$linkmcid;
	$linkres = mysql_fetch_row(mysql_query($linkquery));
	$link = $linkres[0];
	$changeLink = "<tr><td>Externally Linked To:</td><td><input type=text name='exlink' id='link' value=$link></td></tr>"; 
	}
	if($menuType=="classic") $classictype="selected";
	else if($menuType=="multidepth") $multidepthtype="selected";
	else $completetype="selected";
	
	$row = mysql_fetch_array(mysql_query("SELECT `allowComments` FROM `article_content` WHERE `page_modulecomponentid` = '{$modulecomponentid}'"));
	$allowComments = $row['allowComments'] == 1 ? 'checked="checked" ' : '';
	
	$formDisplay =<<<FORMDISPLAY

	<div id="page_settings">
    <form name="pagesettings" action="./+settings&subaction=pagesettings&pageName=" method="POST" onsubmit="return settingsOnSubmit();">
		<script type="text/javascript" language="javascript">
			function settingsOnSubmit(){
				if(!document.getElementById("pagename").disabled) {
				 	if(document.getElementById("pagename").value=="") {alert("Please fill the page name."); return false;}
				}
				if(document.getElementById("pagetitle").value=="") { alert("Please fill the page title."); return false;}
			}
			function checkDelete(butt,fileName)
			{
				if(confirm('Are you sure you want to delete '+fileName+'?'))
				{
				return true;
//					butt.form.action+=fileName;
//					butt.form.submit();
				}
				else return false;
			}
			function toggleSelTemplate2()
				{
					var obj=document.getElementsByName('page_template')[0];
					obj.disabled=(obj.disabled==true?false:true);
		
				}
			function toggleMenuType()
				{
					var obj=document.getElementById('menutype');
					if(obj.value=="classic")
					{
						document.getElementById('showsiblingmenu').disabled=false;
						document.getElementById('menudepth').disabled=true;
					}
					else 
					{
						document.getElementById('showsiblingmenu').disabled=true;
						document.getElementById('menudepth').disabled=false;
					}
				}
		</script>


        	<br />
        <a name="topquicklinks"></a>
        <fieldset>
        <legend>{$ICONS['Page Settings']['small']}Page Settings</legend>
        <table class='iconspanel'>
        <tr>
        <td><a href='#pageinfoform'><div>{$ICONS['Page Information']['large']}<br/>Page Information</div></a></td>
        <td><a href='#childpageform'><div>{$ICONS['Create New Page']['large']}<br/>Create New Page</div></a></td>
        <td><a href='#copymovepageform'><div>{$ICONS['Copy or Move Page']['large']}<br/>Copy or Move Page</div></a></td>
        <td><a href='#inheritinfoform'><div>{$ICONS['Page Inherited Info']['large']}<br/>Page Inherited Information</div></a></td>
        </tr>
        </table>   
        </fieldset>
        
        <a name="pageinfoform"></a>
      	<fieldset>
        	<legend>{$ICONS['Page Information']['small']}Page Information</legend>
        	
	        <table border="1" cellpadding="2px" cellspacing="2px">
				<tr><td>Page path:</td><td>$pageFullPath</td></tr>
	        	<tr><td>Page name:</td><td><input type="text" id="pagename" name="pagename" value="{$page_values['page_name']}" $modifiers/></td></tr>
	  			<tr><td>Page title:</td><td><input type="text" id="pagetitle" name="pagetitle" value="{$page_values['page_title']}" $modifiers/></td></tr>
	  			<tr><td >Page type: </td><td>$pageType</td></tr>
	  			$changeLink$generatedTree
				<tr><td>Allow comments: </td><td><input type='checkbox' id='allowComments' name='allowComments' $allowComments></td></tr>
				$showInMenuBox
			<tr><td><label for="showheading">Show page heading</label></td><td><input type="checkbox" id="showheading" name="showheading" $showheading /></td></tr>
	  				
	  			$showInSiteMap	
	  			
		</table>
		<fieldset><legend>Menu Settings</legend>
		<table border="1" cellpadding="2px" cellspacing="2px">
		
			
				<tr>
					<td><label for='menutype'>Menu type</label></td>
					<td>
					<select name="menutype" id="menutype" onchange="toggleMenuType();">
						<option value='classic' $classictype>Classic</option>
						<option value='complete' $completetype>Complete</option>
						<option value='multidepth' $multidepthtype>Multi-Depth</option>
					</select>
					</td>
					<td rowspan="4"><input type="checkbox" name='menustyle_propogate' value='yes' checked="checked" />Propogate Menu settings to all child pages <br /><br /> Menu Depth = -1 : Generate Complete Menu till the last child page.</td>
				<tr>
					<td><label for='showmenubar'>Show menu bar in page</label></td>
					<td><input type='checkbox' id='showmenubar' name='showmenubar' $showmenubar/></td>
				</tr><tr>
					<td><label for='showsiblingmenu'> Show sibling menu in page</label></td>
					<td><input type='checkbox' name='showsiblingmenu' id='showsiblingmenu' $showsiblingmenu /></td>
				</tr>
				<tr>
					<td><label for='menudepth'>Menu Depth</label></td>
					<td><input type='text' name='menudepth' id='menudepth' value='$menudepth'/>  </td>
				</tr><tr>
					<td><label for='displayicon'> Show icon in menu </label></td>
					<td><input type='checkbox' name='displayicon' id='displayicon' $displayicon /></td>
					<td rowspan="4"><input type="checkbox" name='icon_propogate' value='yes' />Propogate Icon settings to all child pages </td>
				</tr>
				
	        </table>
	        </fieldset>
	        <fieldset><legend>Template</legend>
FORMDISPLAY;
	if($allow_pagespecific_templates==1)
	{
		$formDisplay.=<<<FORMDISPLAY
	        <table border="1" cellpadding="2px" cellspacing="2px">
				
				<tr>
					<td>Use Default Template ?</td>
					<td><input type='checkbox' name='default_template' value='yes' onchange="toggleSelTemplate2()" $dbPageDefTemplate /></td>
					<td rowspan=2><input type="checkbox" name='template_propogate' value='yes' />Propogate Template setting to all child pages
					</td>
					</tr>
					<tr>
					<td>Select Template</td>
					<td><select name='page_template' $dbPageTemplateDisabled>
FORMDISPLAY;
					for($i=0; $i<count($templates); $i++)
					{
						if($templates[$i]==$dbPageTemplate)
						$formDisplay.="<option value='".$templates[$i]."' selected >".ucwords($templates[$i])."</option>";
						else
						$formDisplay.="<option value='".$templates[$i]."' >".ucwords($templates[$i])."</option>";
					}
					$formDisplay.="
					</select>
	
				</tr>
		</table>";
	}
	else $formDisplay.="Page-specific templates are disabled. Please enable it from Global Settings in Admin or click <a href='./+admin&subaction=global'>here</a>";
	
	$formDisplay.=<<<FORMDISPLAY
		</fieldset>
		
		<fieldset><legend>Child Pages</legend>
		Child pages: (Click on links for children's settings.) $parentPath <br />
		$childList
	        </fieldset>
	     

    		<input type="submit" name="btnSubmit" value="Submit"/>&nbsp;&nbsp;<input type="reset" name="btnReset" value="Reset" />
      	</fieldset>
      	<a href="#topquicklinks">Top</a>
    </form>
    	<br/><br/>
		$createdPageSettingsText
		<a href="#topquicklinks">Top</a>
	<br/><br/>
		$movecopyPageSettingsText
		<a href="#topquicklinks">Top</a>
<br/><br/>
    	$inheritedInfoText
    	<a href="#topquicklinks">Top</a>
	</div>
FORMDISPLAY;
	return $formDisplay;
}

/**
 * Updates the settings for a given page
 * @param $pageId Page id of the requested page
 * @param $userId User id of the current user
 * @param $pageName New name (page link) for the page
 * @param $pageTitle New title (page heading) for the page
 * @param $showInMenu Boolean indicating whether the page is to be shown in the menu
 * @param $showMenuBar Boolean indicating whether the page shows its menubar
 * @return String containing a description of any errors encountered, a null string indicating success
 */
function updateSettings($pageId, $userId, $pageName, $pageTitle, $showInMenu, $showHeading, $showMenuBar, $showSiblingMenu, $visibleChildList,$visiblesChildList, $visibleiChildList, $page_template, $template_propogate, $menu_type, $menu_depth, $menu_propogate, $showInSiteMap, $displayicon,$icon_propogate) {

	$updateQuery = '';
	$updates = array ();
	$errors = '';
	$pageId=escape($pageId);
	$userId=escape($userId);
	$pageName=escape($pageName);
	$pageTitle=escape($pageTitle);
	$page_template=escape($page_template);
	
	if ($pageId == 0) {
		if (is_bool($showInMenu)) {if($template_propogate==true)
	{
		setChildTemplateFromParentID($pageId,$page_template);	
	}
			$updates[] = '`page_displayinmenu` = ' . ($showInMenu == true ? 1 : 0);
		}
	}

	if (is_bool($showMenuBar)) {
		$updates[] = '`page_displaymenu` = ' . ($showMenuBar == true ? 1 : 0);
	}

	if (is_bool($showInSiteMap)) {
		$updates[] = '`page_displayinsitemap` = ' . ($showInSiteMap == true ? 1 : 0);
	}

	if (is_bool($showHeading)) {
		$updates[] = '`page_displaypageheading` = ' . ($showHeading == true ? 1 : 0);
	}
	if (is_bool($displayicon)) {
		$updates[] = '`page_displayicon` = ' . ($displayicon == true ? 1 : 0);
	}

	if ($pageId != 0 && isset ($pageName) && $pageName!="" && isset ($pageTitle) && $pageTitle!="") {
		if(preg_match('/^[a-zA-Z][\_a-zA-Z0-9]*$/', $pageName)) {
			$query = "SELECT `page_id` FROM `" . MYSQL_DATABASE_PREFIX . "pages` WHERE `page_name` = '$pageName' AND `page_id` != '$pageId' AND `page_parentid` = " .
								"(SELECT `page_parentid` FROM `" . MYSQL_DATABASE_PREFIX . "pages` WHERE `page_id` = '$pageId')";
			$result = mysql_query($query);
			if (mysql_num_rows($result) > 0) {
				$errors = 'A page with the same name already exists in the folder.<br />';
			} else {
				$updates[] = "`page_name` = '$pageName'";
			}
		}
		else {
			$errors = 'Page name can contain only alphabets (a-z, and A-Z), digits (0-9) and underscores.';
		}
		$updates[] = "`page_title` = '$pageTitle'";
	}

	if($page_template!=NULL)
	{
		$updates[] = "`page_template` = '$page_template'";
	}
	if($template_propogate==true)
	{
		setChildTemplateFromParentID($pageId,$page_template);	
	}
	if($menu_propogate==true)
	{
		setChildMenuStyleFromParentID($pageId,$menu_type,$showMenuBar,$showSiblingMenu,$menu_depth);	
	}
	if($icon_propogate==true)
	{
		setChildIconFromParentID($pageId,$displayicon);	
	}
	if($menu_type!=NULL)
	{
		$updates[] = "`page_menutype` = '$menu_type'";
		
		if(($menu_type=="multidepth" || $menu_type=="complete")&& $menu_depth!=NULL)
		{
			$updates[] = "`page_menudepth` = $menu_depth";
		}
		else if($menu_type=="classic" && is_bool($showSiblingMenu))
		{
			$updates[] = '`page_displaysiblingmenu` = ' . ($showSiblingMenu == true ? 1 : 0);
		}
	}
	if (count($updates) > 0) {
		$updateQuery = 'UPDATE `' . MYSQL_DATABASE_PREFIX . 'pages` SET ' . join($updates, ', ') . " WHERE `page_id` = '$pageId';";
		mysql_query($updateQuery);
	}

	if (is_array($visibleChildList) && count($visibleChildList) > 0) {
		$visibleChildList = "'" . join($visibleChildList, "', '") . "'";
		$updateQuery = 'UPDATE `' . MYSQL_DATABASE_PREFIX . 'pages` SET `page_displayinmenu` = 1 WHERE ' .
		"`page_name` IN ($visibleChildList) AND `page_parentid` = '$pageId' AND `page_parentid` != `page_id`";
		mysql_query($updateQuery);
		$updateQuery = 'UPDATE `' . MYSQL_DATABASE_PREFIX . 'pages` SET `page_displayinmenu` = 0 WHERE ' .
		"`page_name` NOT IN ($visibleChildList) AND `page_parentid` = '$pageId' AND `page_parentid` != `page_id`";
	} else {
		$updateQuery = 'UPDATE `' . MYSQL_DATABASE_PREFIX . 'pages` SET `page_displayinmenu` = 0 WHERE ' .
		"`page_parentid` = '$pageId' AND `page_parentid` != `page_id`";
	}
	mysql_query($updateQuery);
	if (is_array($visiblesChildList) && count($visiblesChildList) > 0) {
		$visiblesChildList = "'" . join($visiblesChildList, "', '") . "'";
		$updateQuery = 'UPDATE `' . MYSQL_DATABASE_PREFIX . 'pages` SET `page_displayinsitemap` = 1 WHERE ' .
		"`page_name` IN ($visiblesChildList) AND `page_parentid` = '$pageId' AND `page_parentid` != `page_id`";
		mysql_query($updateQuery);
		$updateQuery = 'UPDATE `' . MYSQL_DATABASE_PREFIX . 'pages` SET `page_displayinsitemap` = 0 WHERE ' .
		"`page_name` NOT IN ($visiblesChildList) AND `page_parentid` = '$pageId' AND `page_parentid` != `page_id`";
	} else {
		$updateQuery = 'UPDATE `' . MYSQL_DATABASE_PREFIX . 'pages` SET `page_displayinsitemap` = 0 WHERE ' .
		"`page_parentid` = '$pageId' AND `page_parentid` != `page_id`";
	}
	if(!$icon_propogate)
	{
	if (is_array($visibleiChildList) && count($visibleiChildList) > 0) {
		$visibleiChildList = "'" . join($visibleiChildList, "', '") . "'";
		$updateQuery = 'UPDATE `' . MYSQL_DATABASE_PREFIX . 'pages` SET `page_displayicon` = 1 WHERE ' .
		"`page_name` IN ($visibleiChildList) AND `page_parentid` = '$pageId' AND `page_parentid` != `page_id`";
		mysql_query($updateQuery);
		$updateQuery = 'UPDATE `' . MYSQL_DATABASE_PREFIX . 'pages` SET `page_displayicon` = 0 WHERE ' .
		"`page_name` NOT IN ($visibleiChildList) AND `page_parentid` = '$pageId' AND `page_parentid` != `page_id`";
	} else {
		$updateQuery = 'UPDATE `' . MYSQL_DATABASE_PREFIX . 'pages` SET `page_displayicon` = 0 WHERE ' .
		"`page_parentid` = '$pageId' AND `page_parentid` != `page_id`";
	}
	}
	mysql_query($updateQuery);

	return $errors;
}
function setChildTemplateFromParentID($parentId,$page_template)
{
	$parentId=escape($parentId);
	$query= "SELECT `page_id` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_parentid`='".$parentId."' AND NOT `page_id`=0";
	$result=mysql_query($query);
	while($row=mysql_fetch_row($result))
	{
		$childPageId=$row[0];
		$query="UPDATE `".MYSQL_DATABASE_PREFIX."pages` SET `page_template`='$page_template' WHERE `page_id`='$childPageId'";
		mysql_query($query);
		setChildTemplateFromParentID($childPageId,$page_template);
	}
}
function setChildMenuStyleFromParentID($parentId,$menu_type,$showMenuBar,$showSiblingMenu,$menu_depth)
{
	$parentId=escape($parentId);
	$menu_type=escape($menu_type);
	$menu_depth=escape($menu_depth);
	$bshowMenuBar=($showMenuBar==true)?1:0;
	$bshowSiblingMenu=($showSiblingMenu==true)?1:0;
	$query= "SELECT `page_id` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_parentid`='".$parentId."' AND NOT `page_id`=0";
	$result=mysql_query($query);
	$menu="";
	$sibling="";
	if($menu_depth!=NULL)
		$menu=", `page_menudepth`='$menu_depth' ";
	
	if(is_bool($showSiblingMenu))
		$sibling=", `page_displaysiblingmenu`='$bshowSiblingMenu' ";
	
	while($row=mysql_fetch_row($result))
	{
		$childPageId=$row[0];
		$query="UPDATE `".MYSQL_DATABASE_PREFIX."pages` SET `page_displaymenu`='$bshowMenuBar', `page_menutype`='$menu_type' $sibling  $menu WHERE `page_id`='$childPageId'";
		mysql_query($query);
		
		setChildMenuStyleFromParentID($childPageId,$menu_type,$showMenuBar,$showSiblingMenu,$menu_depth);
	}
}
function setChildIconFromParentID($parentId,$displayicon)
{if($displayicon=='')$displayicon=0;
	$parentId=escape($parentId);
	$query= "SELECT `page_id` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_parentid`='".$parentId."' AND NOT `page_id`=0";
	$result=mysql_query($query);
	while($row=mysql_fetch_row($result))
	{
		$childPageId=$row[0];
		$query="UPDATE `".MYSQL_DATABASE_PREFIX."pages` SET `page_displayicon`='$displayicon' WHERE `page_id`='$childPageId'";
		mysql_query($query);
		setChildIconFromParentID($childPageId,$displayicon);
	}
}


/**
 * Displays a page settings form, as well as handles its submission, given a page id and a user id
 * @param $pageId Page id of the page where the user is trying to modify settings
 * @param $userId User id of the current user
 * @return HTML content for the page (the form, or the notifications after the form handling)
 */
function pagesettings($pageId, $userId) {
	
	$pageId=escape($pageId);
	$userId=escape($userId);
	global $sourceFolder;
	$chkquery="SELECT `value` FROM `".MYSQL_DATABASE_PREFIX."global` WHERE `attribute`='allow_pagespecific_template'";
	$row=mysql_fetch_row(mysql_query($chkquery));
 	$allow_pagespecific_templates=$row[0]; // 0 if disabled, 1 if enabled
 	
	require_once($sourceFolder."/tree.lib.php");

	if(isset($_GET['displayinfo'])) {
		displayinfo(safe_html($_GET['displayinfo']));
	}
	if(isset($_GET['displayerror'])) {
		displayerror(safe_html($_GET['displayerror']));
	}
	if (isset ($_GET['subaction']))	 {
		if($_GET['subaction']=="pagesettings") {

			$childPageName=escape($_GET['pageName']);
			if(isset($_POST['btnSubmit'])) {
				global $sourceFolder;
				require_once($sourceFolder."/parseurl.lib.php");
				if(isset($_POST['link']))
				{
					$lpageIdArray = array();
					$linkpageid = parseUrlReal(escape($_POST['link']), $lpageIdArray);
				}
				else if(isset($_POST['exlink']))
				{
					$exlink=escape($_POST['exlink']);
				}
				$visibleChildList = array();
				$visibleiChildList = array();
				$visiblesChildList = array();
				if(isset($_POST['menubarshowchildren']) && is_array($_POST['menubarshowchildren'])) {
					for($i = 0; $i < count($_POST['menubarshowchildren']); $i++) {
						$visibleChildList[] = escape($_POST['menubarshowchildren'][$i]);
					}
				}
				if(isset($_POST['sitemapshowchildren']) && is_array($_POST['sitemapshowchildren'])) {
					for($i = 0; $i < count($_POST['sitemapshowchildren']); $i++) {
						$visiblesChildList[] = escape($_POST['sitemapshowchildren'][$i]);
					}
				}
				if(isset($_POST['childrenshowicon']) && is_array($_POST['childrenshowicon'])) {
					for($i = 0; $i < count($_POST['childrenshowicon']); $i++) {
						$visibleiChildList[] = escape($_POST['childrenshowicon'][$i]);
					}
				}

				$pageInfoRow = getPageInfo($pageId);
				if(isset($_POST['default_template']) || $allow_pagespecific_templates==0)
					$page_template=DEF_TEMPLATE;
				else $page_template=escape($_POST['page_template']);
	
				$template_propogate=isset($_POST['template_propogate'])?true:false;
				$menu_propogate=isset($_POST['menustyle_propogate'])?true:false;
				$icon_propogate=isset($_POST['icon_propogate'])?true:false;
				$_POST['pagename']=isset($_POST['pagename'])?$_POST['pagename']:"";
				$_POST['pagetitle']=isset($_POST['pagetitle'])?$_POST['pagetitle']:"";
				$var = (isset($_POST['allowComments'])?1:0);
				$modulecomponentid = mysql_fetch_array(mysql_query("SELECT `page_modulecomponentid` FROM `" . MYSQL_DATABASE_PREFIX . "pages` WHERE `page_id` = '{$pageId}'"));
				$modulecomponentid = $modulecomponentid['page_modulecomponentid'];
				mysql_query("UPDATE `article_content` SET `allowComments` = $var WHERE `page_modulecomponentid` = '{$modulecomponentid}'");
				if(isset($_POST['exlink']))
					mysql_query("UPDATE `" . MYSQL_DATABASE_PREFIX . "external` SET `page_extlink` = '{$exlink}' WHERE `page_modulecomponentid`= '{$modulecomponentid}'");
				else if(isset($_POST['link']))				
					mysql_query("UPDATE `" . MYSQL_DATABASE_PREFIX . "pages` SET `page_modulecomponentid` = '{$linkpageid}' WHERE `page_id`= '$pageId'");
				$updateErrors = updateSettings($pageId, $userId, escape($_POST['pagename']), escape($_POST['pagetitle']), isset($_POST['showinmenu']), isset($_POST['showheading']), isset($_POST['showmenubar']), isset($_POST['showsiblingmenu']), $visibleChildList,$visiblesChildList,$visibleiChildList, $page_template, $template_propogate, escape($_POST['menutype']),isset($_POST['menudepth'])?escape($_POST['menudepth']):NULL,$menu_propogate,isset($_POST['showinsitemap']),isset($_POST['displayicon']),$icon_propogate);

				

				$pageInfoRow = getPageInfo($pageId);
				if($updateErrors == '') {
					//disconnect();
					//header("Location: ../{$pageInfoRow['page_name']}+settings&displayinfo=".rawurlencode('Page settings updated successfully!'));
					displayinfo("Page settings updated successfully!");
				}
				else {
					//disconnect();
					//header("Location: ../{$pageInfoRow['page_name']}+settings&displayerror=".rawurlencode($updateErrors));
					displayerror("Could not update page settings : ".$updateErrors);
				}
			}
			if(isset($_POST['moveUp'])||isset($_POST['moveDn'])) {
				if(isset($_POST['moveUp']))
				{
					$comparison="<=";
					$sortOrder="DESC";
				}
				else
				{
					$comparison=">=";
					$sortOrder="ASC";
				}
				$childPageName=escape($_GET['pageName']);
				$query="SELECT `page_menurank`,`page_id` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_parentid`='$pageId' AND `page_name`='$childPageName' AND `page_id` != '$pageId' ORDER BY `page_menurank` $sortOrder LIMIT 0,1 ";
				$result=mysql_query($query);
				$temp=mysql_fetch_assoc($result);
				$childPageId=$temp['page_id'];
				$query="SELECT `page_menurank`,`page_id` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_parentid`=$pageId AND `page_menurank` $comparison(SELECT `page_menurank` FROM  `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_parentid`='$pageId' AND `page_name`='$childPageName') AND `page_id` != '$childPageId'  AND `page_parentid` != `page_id` ORDER BY `page_menurank` $sortOrder LIMIT 0,1 ";
				$result=mysql_query($query) or displayinfo(mysql_error());
				if(mysql_num_rows($result)==0){
					displayerror("You cannot move up/down the first/last page in menu");

				}
				$tempTarg=mysql_fetch_assoc($result);
				$query="SELECT `page_menurank`,`page_parentid` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_id`='$childPageId'";
				$result=mysql_query($query);
				$tempSrc=mysql_fetch_assoc($result);
				if(($tempTarg['page_menurank'])==($tempSrc['page_menurank']))
				{
					$query="UPDATE `".MYSQL_DATABASE_PREFIX."pages` SET `page_menurank` = `page_id` WHERE `page_parentid`='$tempSrc[page_parentid]'";
		 			mysql_query($query);
		 			displayinfo("Error in menu rank corrected. Please reorder the pages");
				}
				else{
				$query="UPDATE `".MYSQL_DATABASE_PREFIX."pages`  SET `page_menurank` ='$tempSrc[page_menurank]' WHERE `page_id` = '$tempTarg[page_id]' ";
				mysql_query($query);
				$query="UPDATE `".MYSQL_DATABASE_PREFIX."pages`  SET `page_menurank` ='$tempTarg[page_menurank]' WHERE `page_id` = '$childPageId' ";
				mysql_query($query);
				}
			}
			if(isset($_POST['deletePage']))
			{
				if(isset($_GET['pageName']) || $_GET['pageName']=="") {
					$childPageName=escape($_GET['pageName']);
					$query="SELECT `page_id` FROM  `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_parentid`='$pageId' AND `page_name`='$childPageName'";
					$result=mysql_query($query);
					$temp=mysql_fetch_assoc($result);
					$childPageId=$temp['page_id'];
					if(deletePage($childPageId,$userId))
						displayinfo("Page deleted successfully.");
				}
				else
					displayerror("Not enough information available");
			}
		}
		elseif($_GET['subaction']=="move") {

			if ($_POST['parentpagepath'] == '')
				$updateErrors = "Null page id";
			else {
				global $sourceFolder;
				require_once($sourceFolder."/parseurl.lib.php");
				$pageIdArray = array();
				$parentId = parseUrlReal(escape($_POST['parentpagepath']), $pageIdArray);
				$updateErrors = move_page($userId,$pageId, $parentId, escape($_POST['destinationpagetitle']), escape($_POST['destinationpagename']),isset( $_POST['deleteoriginalpage']));
			}

			if ($updateErrors != '') {
				displayerror($updateErrors);
			}
		}
		elseif($_GET['subaction']=="create") {
			/**
			 * Have page rank equal to page id to ensure unique ranks
			 * for links, rank equals to page id of the target sure, coz we can sure the pageid of parent page is unique at destination
			 * check if there is any child page with same name
			 * page name should not contain any special characters. (esp space)
			 * ask for page name only and page title = ucfirst(pagename)
			 * check if the guy has permission to create the page of that type
			 * call modules createModule function
			 */
			if(isset($_POST['childpagetype'])&&isset($_POST['childpagename'])) {

				if(isset($_POST['default_template']) || $allow_pagespecific_templates==0)
					$page_template=DEF_TEMPLATE;
				else $page_template=escape($_POST['page_template']);

				$maxquery="SELECT MAX( page_id ) AS MAX FROM ".MYSQL_DATABASE_PREFIX."pages";
				$maxqueryresult = mysql_query($maxquery);
				$maxqueryrow = mysql_fetch_array($maxqueryresult);
				$maxpageid = $maxqueryrow[0]+1;

				$menutypequery="SELECT `page_menutype`,`page_menudepth`,`page_displaysiblingmenu`,`page_displayicon`,`page_displayinmenu` FROM ".MYSQL_DATABASE_PREFIX."pages WHERE page_id=".$pageId;
				$menutyperesult = mysql_query($menutypequery);
				$menutyperow = mysql_fetch_array($menutyperesult);
				$alreadyexistquery="SELECT page_name FROM ".MYSQL_DATABASE_PREFIX."pages WHERE page_parentid='$pageId' AND page_name='".escape($_POST['childpagename'])."'";
				$alreadyexistqueryresult = mysql_query($alreadyexistquery);
				$alreadyexistquerynumrows = mysql_num_rows($alreadyexistqueryresult);
				$childPageName = str_replace(' ', '_', escape(strtolower($_POST['childpagename'])));
				$childPageTitle = escape($_POST['childpagename']);
				if(!preg_match('/^[a-z][\_a-z0-9]*$/',  $childPageName))
					displayerror("Invalid page name.");
				elseif($alreadyexistquerynumrows>=1)
					displayerror("A page with the given name already exists at this location.");
				elseif($_POST['childpagetype']=="menu") {
					$menuquery = "INSERT INTO `".MYSQL_DATABASE_PREFIX."pages` (`page_id` ,`page_name` ,`page_parentid` ,`page_title` ,`page_module` ,`page_modulecomponentid` , `page_template`, `page_menurank`, `page_menutype`,`page_menudepth`,`page_displaysiblingmenu`,`page_displayicon`,`page_displayinmenu`) " .
							"VALUES ('$maxpageid', '".$childPageName."', '$pageId', '".$childPageTitle."', '".escape($_POST['childpagetype'])."', '0', '$page_template', '$maxpageid', '$menutyperow[0]','$menutyperow[1]','$menutyperow[2]','$menutyperow[3]','$menutyperow[4]')";
					mysql_query($menuquery);
						if (mysql_affected_rows() != 1)
							displayerror( 'Unable to create a new page');
						else displayinfo("Menu successfully created! <a href='./$childPageName+settings'>Click here</a> to go to its page-settings and start creating links in the menu.");
				}
				elseif($_POST['childpagetype']=="link") {
					global $sourceFolder;
					require_once($sourceFolder."/parseurl.lib.php");
					$pageIdArray = array();
					$parentId = parseUrlReal(escape($_POST['childpagelink']), $pageIdArray);
					if(getPermissions($userId, $parentId, "settings")) {
					if($_POST['linkselect']=="Same Tab"){
					$parentId = parseUrlReal(escape($_POST['childpagelink']), $pageIdArray);
					$linkquery = "INSERT INTO `".MYSQL_DATABASE_PREFIX."pages` (`page_id` ,`page_name` ,`page_parentid` ,`page_title` ,`page_module` ,`page_modulecomponentid` , `page_template`, `page_menurank`, `page_openinnewtab`, `page_menutype`,`page_menudepth`,`page_displaysiblingmenu`,`page_displayicon`,`page_displayinmenu`) " .
							"VALUES ('$maxpageid', '$childPageName', '$pageId', '$childPageTitle', '".escape($_POST['childpagetype'])."', '$parentId', '$page_template', '$maxpageid', '0', '$menutyperow[0]','$menutyperow[1]','$menutyperow[2]','$menutyperow[3]','$menutyperow[4]')";
					mysql_query($linkquery);
						if (mysql_affected_rows() != 1)
							displayerror( 'Unable to create a new page');
					}
					if ($_POST['linkselect']=="New Tab"){
					$parentId = parseUrlReal(escape($_POST['childpagelink']), $pageIdArray);
					$linkquery = "INSERT INTO `".MYSQL_DATABASE_PREFIX."pages` (`page_id` ,`page_name` ,`page_parentid` ,`page_title` ,`page_module` ,`page_modulecomponentid` , `page_template`, `page_menurank`, `page_openinnewtab`, `page_menutype`,`page_menudepth`,`page_displaysiblingmenu`,`page_displayicon`,`page_displayinmenu`) " .
							"VALUES ('$maxpageid', '$childPageName', '$pageId', '$childPageTitle', '".escape($_POST['childpagetype'])."', '$parentId', '$page_template', '$maxpageid', '1', '$menutyperow[0]','$menutyperow[1]','$menutyperow[2]','$menutyperow[3]','$menutyperow[4]')";
					mysql_query($linkquery);
						if (mysql_affected_rows() != 1)
							displayerror( 'Unable to create a new page');
					}
					}
					else
						displayerror("Not enough permission to create a link for that location.");
				}
				elseif($_POST['childpagetype']=="external") {
					$extquery="SELECT MAX( page_modulecomponentid ) AS MAX FROM ".MYSQL_DATABASE_PREFIX."external";
					$extqueryresult = mysql_query($extquery);
					$extqueryrow = mysql_fetch_array($extqueryresult);
					$extpageid = $extqueryrow[0]+1;
					$query="INSERT INTO `".MYSQL_DATABASE_PREFIX."external` (`page_modulecomponentid`,`page_extlink`) " .
							"VALUES('$extpageid','".escape($_POST['externallink'])."')";
					if(!($result = mysql_query($query))) {
						displayerror("Unable to create an external link.");
						return false;
					}
					
					if($_POST['linkselectex']=="New Tab") 
					{
						$linkquery = "INSERT INTO `".MYSQL_DATABASE_PREFIX."pages` (`page_id` ,`page_name` ,`page_parentid` ,`page_title` ,`page_module` ,`page_modulecomponentid` , `page_template`, `page_menurank`,`page_openinnewtab`, `page_menutype`,`page_menudepth`,`page_displaysiblingmenu`,`page_displayicon`,`page_displayinmenu`) " .
							"VALUES ('$maxpageid', '".escape($_POST['childpagename'])."', '$pageId', '".escape(ucfirst(escape($_POST['childpagename'])))."', '".escape($_POST['childpagetype'])."', '$extpageid', '$page_template' ,'$maxpageid','1', '$menutyperow[0]','$menutyperow[1]','$menutyperow[2]','$menutyperow[3]','$menutyperow[4]')";
						
						mysql_query($linkquery);
						if (mysql_affected_rows() != 1)
						{
							displayerror( 'Unable to create a new page');
							return false;
						}
					}
					else {
						
					$linkquery = "INSERT INTO `".MYSQL_DATABASE_PREFIX."pages` (`page_id` ,`page_name` ,`page_parentid` ,`page_title` ,`page_module` ,`page_modulecomponentid` , `page_template`, `page_menurank`,`page_openinnewtab`, `page_menutype`,`page_menudepth`,`page_displaysiblingmenu`,`page_displayicon`,`page_displayinmenu`) " .
						"VALUES ('$maxpageid', '".escape($_POST['childpagename'])."', '$pageId', '".escape(ucfirst(escape($_POST['childpagename'])))."', '".escape($_POST['childpagetype'])."', '$extpageid', '$page_template' ,'$maxpageid','0', '$menutyperow[0]','$menutyperow[1]','$menutyperow[2]','$menutyperow[3]','$menutyperow[4]')";
					mysql_query($linkquery);
					if (mysql_affected_rows() != 1)
						{
						displayerror( 'Unable to create a new page');
						return false;
						}
					}
					displayinfo("External link has been created!");
				}
				else {
					$moduleType = escape($_POST['childpagetype']);
					global $sourceFolder;
					global $moduleFolder;
					require_once($sourceFolder."/".$moduleFolder."/".$moduleType.".lib.php");
					$page = new $moduleType();
					$newId=createInstance($moduleType);
					$page->createModule($newId);
					$createquery = "INSERT INTO `".MYSQL_DATABASE_PREFIX."pages` (`page_id` ,`page_name` ,`page_parentid` ,`page_title` ,`page_module` ,`page_modulecomponentid` , `page_template`, `page_menurank`, `page_menutype`,`page_menudepth`,`page_displaysiblingmenu`,`page_displayicon`,`page_displayinmenu`) " .
							"VALUES ('$maxpageid', '$childPageName', '$pageId', '$childPageTitle', '".escape($_POST['childpagetype'])."', '$newId', '$page_template', '$maxpageid', '$menutyperow[0]','$menutyperow[1]','$menutyperow[2]','$menutyperow[3]','$menutyperow[4]')";
					mysql_query($createquery);
						if (mysql_affected_rows() != 1)
							displayerror( 'Unable to create a new page.');
				}
			}
			else
				displayerror("One or more parameters not set.");
		}
		else if($_GET['subaction'] == 'editinheritedinfo') {
			updatePageInheritedInfo($pageId, escape($_POST['txtInheritedInfo']));
		}
	}
	if ($settingsForm = getSettingsForm($pageId, $userId))
		return $settingsForm;
	else {
		displayerror('Could not find page settings for the requested page.');
		return '';
	}
}

/**
 * function copyInstance:
 * @moduleType - type of the module
 * @fromId - page_modulecomponentid of the instance to copy from
 * @toId - page_modulecomponentid of the instance to copy to
 */
function copyInstance($moduleType,$fromId,$toId) {
	$error = false;
	if($result = mysql_query("SELECT `module_tables` FROM `".MYSQL_DATABASE_PREFIX."modules` WHERE `module_name` = '{$moduleType}'")) {
		$row = mysql_fetch_assoc($result);
		$tables = explode(";",$row['module_tables']);
		foreach($tables as $table) {
			$result = mysql_query("SELECT * FROM `{$table}` WHERE `page_modulecomponentid` = '{$fromId}'");
			$row = mysql_fetch_assoc($result);
			if($row) {
				$prefix = "INSERT INTO `{$table}`";
				$cols = array(); $vals = array();
				foreach($row as $key => $val) {
					$cols[] = $key;
					if($key!='page_modulecomponentid')
						$vals[] = $val;
					else
						$vals[] = $toId;
				}
				$prefix .= "(`".implode("`,`",$cols)."`)";
				$query = $prefix." VALUES ('".implode("','",$vals)."')";
				if(!mysql_query($query))
					$error = true;
				while($row = mysql_fetch_assoc($result)) {
					$vals = array();
					foreach($row as $key => $val) {
						if($key!='page_modulecomponentid')
							$vals[] = $val;
						else
							$vals[] = $toId;
					}
					$query = $prefix." VALUES ('".implode("','",$vals)."')";
					if(!mysql_query($query))
						$error = true;
				}
			}
		}
	} else
		$error = true;
	if($error)
		return false;
	return true;
}

/**
 * function deleteInstance:
 * @param module - module name
 * @param page_modulecomponentid - module instance id
 * @returns boolean indicating deletion status
 */
function deleteInstance($moduleType,$page_modulecomponentid) {
	if($result = mysql_query("SELECT `module_tables` FROM `".MYSQL_DATABASE_PREFIX."modules` WHERE `module_name` = '{$moduleType}'")) {
		$row = mysql_fetch_assoc($result);
		$tables = explode(";",$row['module_tables']);
		foreach($tables as $table) {
			if(!mysql_query("DELETE FROM `{$table}` WHERE `page_modulecomponentid` = '{$page_modulecomponentid}'"))
				return false;
		}
	} else
		return false;
	return true;
}

/**
 * function createInstance:
 * @param moduleType - specifies module type
 * @returns a new module component id for the given module type
 */
function createInstance($moduleType) {
	$result = mysql_query("SELECT MAX(page_modulecomponentid) as MAX FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_module` = '{$moduleType}'");
	if(!$result)
		return 0;
	$row = mysql_fetch_assoc($result);
	return (int)$row['MAX']+1;
}

/**
 * Generates HTML (and javascript, and some CSS) code for a foldable tree representation of the given node
 * @param $userId User id of the current user
 * @param $module Name of the module, depending on which the tree must check permissions
 * @param $action Name of the action, depending on which the tree must check permissions
 * @param $pageId Page id of the root of the tree, if omitted, it is taken as 0 (the home directory)
 * @return HTML code for the generated tree
 */


function deletePage($pageId,$userId){
 	$query="SELECT `page_id` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_parentid`='$pageId' AND `page_id`!=`page_parentid` ";
 	$result=mysql_query($query);
 	$deleteAll = true;
 	while($temp=mysql_fetch_assoc($result))
 	{

  		if(getPermissions($userId,$pageId,"settings"))
	 		$deleteAll = deletePage($temp['page_id'],$userId);
	 	else
	 		$deleteAll = false;
 	}

 	if($deleteAll == true) {
	 	$pageInfo = getPageInfo($pageId);
		$moduleType = $pageInfo['page_module'];
		$deleted = true;
		if($moduleType!="menu" && $moduleType != "link") {
			if($moduleType!="external") {
				global $sourceFolder;
				global $moduleFolder;
				require_once($sourceFolder."/".$moduleFolder."/".$moduleType.".lib.php");
				$page = new $moduleType();
				$deleted = $page->deleteModule($pageInfo['page_modulecomponentid']);
				if($deleted) {
					if(!deleteInstance($moduleType,$pageInfo['page_modulecomponentid']))
						displayerror("There was an error in deleting the page at module level.");
				} else
					displayerror("There was an error in deleting the page at module level.");
			}
			else {
				$query="DELETE FROM `".MYSQL_DATABASE_PREFIX."external` WHERE `page_modulecomponentid`='".$pageInfo['page_modulecomponentid']."'";
				mysql_query($query);
				if (mysql_affected_rows()>0) $deleted=true;
				else {
					$deleted=false;
					displayerror("There was an error in deleting the external link");
				}

			}
		}
		//query to delete page row itself
		if($deleted) {
			$query="DELETE FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_id`='$pageId'";
			mysql_query($query);
			if (mysql_affected_rows()>0) return true;
			else return false;
		}
		else return false;
 	}
 	else {
 		displayerror("You don't have permission to delete one or more of the children pages.");
 		return false;
 	}



 }

function generateDirectoryTree($userId, $module, $action, $pageId = 0) {
	global $sourceFolder;
	require_once("$sourceFolder/tree.lib.php");
	$dirTree = new DirectoryTree($pageId, $userId, $action, $module);

	return $dirTree;
}



function move_page($userId,$pageId, $parentId, $pagetitle,$pagename,$deleteoriginalentry) {
/**
 * return true or false.
 * First check if page with same name exists in destination parent. If it does, and the parent is different from
 * current parent, dont copy or move and return false
 *
 */
	//var_dump($str);
	$query = "SELECT `page_id` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_parentid` = '$parentId' AND `page_name` = '$pagename'";
	$result = mysql_query($query);
	if(mysql_num_rows($result) > 0)
		return "Error: There exists a page with the same name in the destination path.";
	$parentInfo = getPageInfo($parentId);
	if(!getPermissions($userId, $parentId, "settings"))
		return "Error: You do not have permission to copy or move to the destination page.";
	if($parentInfo['page_module']=="link")
		return "Error: Cannot move or copy a page to a page of the type link.";
	$str = array();
	parseUrlDereferenced($parentId,$str);
	$arrlen = count($str);
	for($i = 0; $i<count($str);$i++){
		if($pageId==$str[$i])
			return 'Error : You are trying to copy a parent to a child page. This will create a loop';
	}
	//if the deleteoriginal entry is set then the page is MOVED from the original location to the new location.
	if ($deleteoriginalentry == true) {
		if ($pageId != 0) {
			$query = "UPDATE `" . MYSQL_DATABASE_PREFIX . "pages` SET `page_parentid` = '" . $parentId . "' , `page_title` = '" . $pagetitle . "' , `page_name` = '" . $pagename . "' WHERE `page_id` ='$pageId' ;";
			$result = mysql_query($query);
				if (mysql_affected_rows() != 1)
					return 'Unable to perform the required action';
			global $urlRequestRoot;
			header("location:".$urlRequestRoot.getPagePath($pageId)."+settings&displayinfo=".rawurlencode("The page has been successfully moved."));
		} else
			return 'Error : You do not have permission to move the root page.';
	}
//if the deleteoriginal entry is not set then the page is COPIED from the original location to the new location.
	else {
		$recursive= false;
		if(isset($_POST['recursivelycopypage'])) $recursive = true;
		if(copyPage($userId,$pageId,$parentId,$pagetitle,$pagename,$recursive))
			displayinfo("Page copied successfully!");
	}
}

function copyPage($userId,$pageId,$parentId, $pagetitle,$pagename,$recursive) {
	if(!getPermissions($userId, $parentId, "settings"))
		return false;
	$parentInfo = getPageInfo($parentId);
	$parentmoduleType =$parentInfo['page_module'];
	if($parentmoduleType=="link")
		return false;
	$pageInfo = getPageInfo($pageId);
	$moduleType =$pageInfo['page_module'];
	if($moduleType=="link")
		return false;
	$newmodulecomponentid = 0;
	if($moduleType!="menu" && $moduleType!="external") {
		global $sourceFolder;
		global $moduleFolder;
		require_once($sourceFolder."/".$moduleFolder."/".$moduleType.".lib.php");
		$page = new $moduleType();
		$newmodulecomponentid = createInstance($moduleType);
		copyInstance($moduleType,$pageInfo['page_modulecomponentid'],$newmodulecomponentid);
		$page->copyModule($pageInfo['page_modulecomponentid'],$newId);
	}
	if($moduleType=="external"){
		$extquery="SELECT MAX( page_modulecomponentid ) AS MAX FROM ".MYSQL_DATABASE_PREFIX."external";
		$extqueryresult = mysql_query($extquery);
		$extqueryrow = mysql_fetch_array($extqueryresult);
		$extpageid = $extqueryrow[0]+1;
		$linkquery="SELECT page_extlink FROM ".MYSQL_DATABASE_PREFIX."external WHERE page_modulecomponentid='".$pageInfo['page_modulecomponentid']."'";
		$linkqueryresult = mysql_query($linkquery);
		$linkqueryrow = mysql_fetch_array($linkqueryresult);
		$link = $linkqueryrow[0];

		$query="INSERT INTO `".MYSQL_DATABASE_PREFIX."external` (`page_modulecomponentid`,`page_extlink`) " .
				"VALUES('$extpageid','$link')";
		if(!($result = mysql_query($query))) {
			displayerror("Unable to copy the page.");
			return false;
		}
	}

	$maxquery="SELECT MAX( page_id ) AS MAX FROM ".MYSQL_DATABASE_PREFIX."pages";
	$maxqueryresult = mysql_query($maxquery);
	$maxqueryrow = mysql_fetch_array($maxqueryresult);
	$maxpageid = $maxqueryrow[0]+1;

	$query = "INSERT INTO `".MYSQL_DATABASE_PREFIX."pages` (`page_id`,`page_name`,`page_title`,`page_parentid`,`page_module`,`page_modulecomponentid`,`page_displayinmenu`, `page_displaymenu`, `page_displaysiblingmenu`,`page_menurank`) " .
			"VALUES('$maxpageid','$pagename','$pagetitle','$parentId','{$pageInfo['page_module']}','$newmodulecomponentid','{$pageInfo['page_displayinmenu']}','{$pageInfo['page_displaymenu']}','{$pageInfo['page_displaysiblingmenu']}','$maxpageid')";
	if(!($result = mysql_query($query))) {
		displayerror("Unable to copy the page.");
		return false;
	}
	if($recursive) {
		$childrenquery="SELECT `page_id`,`page_name`,`page_title` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_parentid`='$pageId' ";
		$childrenresult=mysql_query($childrenquery);
		while($temp=mysql_fetch_assoc($childrenresult))
		{
	 		copyPage($userId,$temp['page_id'],$maxpageid, $temp['page_title'],$temp['page_name'],$recursive);
		}
	}
	return true;
}

function updatePageInheritedInfo($pageId, $inheritedInfo) {
	$prevInheritedInfo = '';
	$inheritedPageId = getPageInheritedInfo($pageId, $prevInheritedInfo);

	if($inheritedPageId == $pageId) {
		if($inheritedInfo == '') {
			$deleteQuery = 'DELETE FROM `' . MYSQL_DATABASE_PREFIX . 'inheritedinfo` WHERE `page_inheritedinfoid` = (SELECT `page_inheritedinfoid` FROM `' . MYSQL_DATABASE_PREFIX . 'pages` WHERE `page_id` = \'' . $pageId . '\')';
			mysql_query($deleteQuery);
			$updateQuery = 'UPDATE `' . MYSQL_DATABASE_PREFIX . 'pages` SET `page_inheritedinfoid` = -1 WHERE `page_id` = \'' . $pageId."'";
			if(!mysql_query($updateQuery))
				displayerror('Could not remove the current page\'s inherited information. Database error.');
		}
		else {
			$updateQuery = 'UPDATE `' . MYSQL_DATABASE_PREFIX . 'inheritedinfo` SET `page_inheritedinfocontent` = \'' . $inheritedInfo . '\' WHERE `page_inheritedinfoid` = (SELECT `page_inheritedinfoid` FROM `' . MYSQL_DATABASE_PREFIX . 'pages` WHERE `page_id` = \'' . $pageId . '\')';
			if(!mysql_query($updateQuery))
				displayerror('Could not update the current page\'s inherited information. Database error.');
		}
	}
	else if($inheritedInfo != '' && $inheritedInfo != $prevInheritedInfo) {
		/// Original inherited info came from a different page
		$newIdQuery = 'SELECT MAX(`page_inheritedinfoid`) FROM `' . MYSQL_DATABASE_PREFIX . 'inheritedinfo`';
		$newIdResult = mysql_query($newIdQuery);
		$newIdRow = mysql_fetch_row($newIdResult);
		$newId = 1;
		if(!is_null($newIdRow[0]))
			$newId = $newIdRow[0] + 1;

		$insertQuery = 'INSERT INTO `' . MYSQL_DATABASE_PREFIX . 'inheritedinfo`(`page_inheritedinfoid`, `page_inheritedinfocontent`) VALUES (\'' . $newId . '\', \'' . $inheritedInfo . '\')';
		if(!mysql_query($insertQuery))
			displayerror('Could not add inherited information to the current page. Database error.');
		$updateQuery = 'UPDATE `' . MYSQL_DATABASE_PREFIX . 'pages` SET `page_inheritedinfoid` = \'' . $newId . '\' WHERE `page_id` = \'' . $pageId."'";
		if(!mysql_query($updateQuery))
			displayerror('Could not add inherited information to the current page. Database error.');
	}
}

function getPageInheritedInfo($pageId, &$inheritedInfoBuf) {
	if($pageId < 0) return $pageId;

	$curPageId = $pageId;
	$inheritedInfoBuf = '';

	do {
		$inheritedInfoIdQuery = 'SELECT `page_inheritedinfoid`, `page_parentid` FROM `' . MYSQL_DATABASE_PREFIX . 'pages` WHERE `page_id` = \'' . $curPageId."'";
		$inheritedInfoIdResult = mysql_query($inheritedInfoIdQuery);
		if(!$inheritedInfoIdResult) echo mysql_error() . '<br />' . $inheritedInfoIdQuery . '<br />';
		$inheritedInfoIdRow = mysql_fetch_row($inheritedInfoIdResult);
		if(!is_null($inheritedInfoIdRow[0]) && $inheritedInfoIdRow[0] >= 0) {
			$inheritedInfoQuery = 'SELECT `page_inheritedinfocontent` FROM `' . MYSQL_DATABASE_PREFIX . 'inheritedinfo` WHERE `page_inheritedinfoid` = \'' . $inheritedInfoIdRow[0]."'";
			$inheritedInfoResult = mysql_query($inheritedInfoQuery);
			$inheritedInfoBuf = mysql_fetch_row($inheritedInfoResult);
			$inheritedInfoBuf = $inheritedInfoBuf[0];
			return $curPageId;
		}
		if($curPageId == 0) $curPageId = -1;
		else $curPageId = $inheritedInfoIdRow[1];
	} while($curPageId >= 0);

	return -1;
}

