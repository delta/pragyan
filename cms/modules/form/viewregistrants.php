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

/**
 * @uses getFormElementData
 * All elements will be simple text
 *
 * But only for uploads, the thing returned will be file id of uploaded file.
 * That has to be replaced by the appropriate file link.
 *
 * Also check which extra fields have to be appended and append them : useremail, userfullname, registrationdate, lastupdated
 */

/**
 * 13 December, 2007
 * TODO
 * To check for Uploads file condition
 * and add the file path in the array..in form of html text
 */


function getLastUpdateDate($moduleComponentId, $userId) {
	$query = 'SELECT `form_lastupdated` FROM `form_regdata` WHERE `page_modulecomponentid` = \'' . $moduleComponentId . '\' AND `user_id` = \'' . $userId."'";
	$result = mysql_query($query);
	$row = mysql_fetch_row($result);
	return $row[0];
}

function getRegistrationDate($moduleComponentId, $userId) {
	$query = 'SELECT `form_firstupdated` FROM `form_regdata` WHERE `page_modulecomponentid` = \'' . $moduleComponentId . '\' AND `user_id` = \'' . $userId."'";
	$result = mysql_query($query);
	$row = mysql_fetch_row($result);
	return $row[0];
}

function generateFormDataRow($moduleCompId, $userId, $columnList, $showProfileData = false) {
	$display = array();
	$elementRow = array();

	$elementDataQuery = 'SELECT `form_elementdata`, `form_elementdesc`.`form_elementid`, `form_elementdesc`.`form_elementtype` FROM `form_elementdesc`, `form_elementdata` WHERE ' .
					"`form_elementdata`.`page_modulecomponentid` = '$moduleCompId' AND `user_id` = '$userId' AND " .
					'`form_elementdata`.`page_modulecomponentid` = `form_elementdesc`.`page_modulecomponentid` AND ' .
					'`form_elementdata`.`form_elementid` = `form_elementdesc`.`form_elementid` ' .
					'ORDER BY `form_elementrank` ASC';
	$elementDataResult = mysql_query($elementDataQuery) or die($elementDataQuery . ' ' . mysql_error());
	while($elementDataRow = mysql_fetch_row($elementDataResult)) {
		$elementRow['elementid_' . $elementDataRow[1]] = $elementDataRow[0];
		if($elementDataRow[2] == 'file') {
			$elementRow['elementid_' . $elementDataRow[1]] = '<a href="./'.$elementDataRow[0].'">' . $elementDataRow[0] . '</a>';
		}
	}
	
	if($showProfileData) { 
		if($userId > 0) {
			$elementDataQuery = 'SELECT `form_elementdata`, `form_elementdesc`.`form_elementid`, `form_elementdesc`.`form_elementname`, `form_elementdesc`.`form_elementtype` FROM `form_elementdesc`, `form_elementdata` WHERE ' .
						"`form_elementdata`.`page_modulecomponentid` = 0 AND `user_id` = '$userId' AND " .
						"`form_elementdata`.`page_modulecomponentid` = `form_elementdesc`.`page_modulecomponentid` AND " .
						"`form_elementdata`.`form_elementid` = `form_elementdesc`.`form_elementid` ORDER BY `form_elementrank`";
			$elementDataResult = mysql_query($elementDataQuery) or die($elementDataQuery . '<br />' . mysql_error());
			while($elementDataRow = mysql_fetch_assoc($elementDataResult)) {
				$elementRow['form0_' . $elementDataRow['form_elementname']] = $elementDataRow['form_elementdata'];
				if($elementDataRow['form_elementtype'] == 'file') {
					$elementRow['form0_' . $elementDataRow['form_elementname']] = '<a href="./'.$elementDataRow['form_elementdata'].'">' . $elementDataRow['form_elementdata'] . '</a>';
				}
			}
			

		}
		else {
			$elementDataQuery = 'SELECT `form_elementname` FROM `form_elementdesc` WHERE `page_modulecomponentid` = 0';
			$elementDataResult = mysql_query($elementDataQuery);
			while($elementDataRow = mysql_fetch_row($elementDataResult)) {
				$elementDataRow['form0_' . $elementDataRow['form_elementname']] = '&nbsp;';
			}
		}
	}


	if(in_array('useremail', $columnList)) {
		$elementRow['useremail'] = getUserEmail($userId);
	}
	if(in_array('username', $columnList)) {
		$elementRow['username'] = getUserName($userId);
	}
	if(in_array('userfullname', $columnList)) {
		$elementRow['userfullname'] = getUserFullName($userId);
	}
	if(in_array('lastupdated', $columnList)) {
		$elementRow['lastupdated'] = getLastUpdateDate($moduleCompId, $userId);
	}
	if(in_array('registrationdate', $columnList)) {
		$elementRow['registrationdate'] = getRegistrationDate($moduleCompId, $userId);
	}

	$columnCount = count($columnList);
	for($i = 0; $i < count($columnList); $i++) {
		if(isset($elementRow[$columnList[$i]])) {
			$display[] = $elementRow[$columnList[$i]];
		}
		else {
			$display[] = ' ';
		}
	}

	return $display;
}




	function getColumnList($moduleCompId, $showUserEmail, $showUserFullName, $showRegistrationDate, $showLastUpdateDate, $showUserProfileData=false) {
		$columns = array();
		
		if($showUserEmail)
			$columns['useremail'] = 'User Email';
		if($showUserFullName)
			$columns['userfullname'] = 'User Full Name';
		if($showRegistrationDate)
			$columns['registrationdate'] = 'Registration Date';
		if($showLastUpdateDate)
			$columns['lastupdated'] = 'Last Updated';
		if($showUserProfileData) {
			$profileQuery = 'SELECT `form_elementname` FROM `form_elementdesc` WHERE `page_modulecomponentid` = 0 ORDER BY `form_elementrank`';
			$profileResult = mysql_query($profileQuery);
			while($profileRow = mysql_fetch_row($profileResult)) {
				$columns['form0_' . $profileRow[0]] = $profileRow[0];
			}
		}

		$columnQuery = 'SELECT `form_elementid`, `form_elementname` FROM `form_elementdesc` WHERE `page_modulecomponentid` = \'' .
									 $moduleCompId . '\' ORDER BY `form_elementrank` ASC';
		$columnResult = mysql_query($columnQuery);

		while($columnRow = mysql_fetch_assoc($columnResult)) {
			$columns['elementid_' . $columnRow['form_elementid']] = $columnRow['form_elementname'];
			
		}

		return $columns;
	}

	function getDistinctRegistrants($moduleCompId, $rowSortField, $rowSortOrder) {
		if($rowSortOrder != 'asc' && $rowSortOrder != 'desc') $rowSortOrder = 'asc';
		$users = array();
		$userQuery = '';

		if($rowSortField == 'useremail' || $rowSortField == 'userfullname' || $rowSortField == 'username') {
			$col = 'user_fullname';
			if($rowSortField == 'useremail') $col = 'user_email';
			elseif($rowSortField == 'username') $col = 'user_name';
			$userTable = MYSQL_DATABASE_PREFIX . 'users';

			$userQuery = "SELECT `form_regdata`.`user_id` FROM `$userTable`, `form_regdata` WHERE " .
					"`page_modulecomponentid` = '$moduleCompId' AND `$userTable`.`user_id` = `form_regdata`.`user_id` " .
					"ORDER BY `$col` $rowSortOrder";

///			$userQuery = "SELECT DISTINCT(`form_elementdata`.`user_id`) FROM `$userTable`, `form_elementdata` WHERE " .
///							 "`page_modulecomponentid` = $moduleCompId AND `$userTable`.`user_id` = `form_elementdata`.`user_id` " .
///							 "ORDER BY `$col` $rowSortOrder";
		}
		elseif($rowSortField == 'registrationdate' || $rowSortField == 'lastupdated') {
			$col = 'form_lastupdated';
			if($rowSortField == 'registrationdate') $col = 'form_firstupdated';

			$userQuery = "SELECT `user_id` FROM `form_regdata` WHERE " .
										"`page_modulecomponentid` = '$moduleCompId' ORDER BY `$col` $rowSortOrder";
		}
		elseif(substr($rowSortField, 0, 6) == 'form0_') {
			/// TODO: Implement the sort here.
		}
		else {
			$elementId = split('_', $rowSortField);
			$elementId = $elementId[1];
/**
 * SELECT *
FROM form_elementdesc des LEFT JOIN (form_regdata reg LEFT JOIN form_elementdata dat ON reg.page_modulecomponentid = dat.page_modulecomponentid AND reg.user_id = dat.user_id AND reg.page_modulecomponentid = 2)
ON des.page_modulecomponentid = dat.page_modulecomponentid AND des.form_elementid = dat.form_elementid
WHERE dat.`form_elementid` = 3 ORDER BY dat.form_elementdata
 *
 *
 */

			$userQuery = "SELECT `reg`.`user_id` FROM `form_elementdesc` des LEFT JOIN (form_regdata reg LEFT JOIN form_elementdata dat ON reg.page_modulecomponentid = dat.page_modulecomponentid AND reg.user_id = dat.user_id AND reg.page_modulecomponentid = '$moduleCompId') " .
					"ON des.page_modulecomponentid = dat.page_modulecomponentid AND des.form_elementid = dat.form_elementid " .
					"WHERE dat.`form_elementid` = '$elementId' ORDER BY dat.form_elementdata $rowSortOrder";
		}


		$userResult = mysql_query($userQuery) or die ($userQuery . ' ' . mysql_error());
		while($userRow = mysql_fetch_row($userResult)) {
			$users[] = $userRow[0];
		}

		return $users;
	}


/**
 * @uses generateFormDataRow($moduleCompId,$userId)
 * @uses getFormElementInfo($moduleCompId) (once it is made in formelementdescclass.php)
 *
 * @param $rowSortOrder "asc" or "desc"
 * @param $rowSortField "registrationdate" or "lastupdated" or "useremail" or "userfullname" or "elementid_".$i
 * @param $showEditButtons Whether to show edit and delete buttons or not: helps with editregistrants
 */
function generateFormDataTable($moduleComponentId, $sortField, $sortOrder, $action = 'viewregistrants') {
	global $sourceFolder, $templateFolder, $urlRequestRoot, $cmsFolder, $moduleFolder;

	$formDescQuery = 'SELECT  `form_showuseremail`, `form_showuserfullname`, `form_showregistrationdate`, `form_showlastupdatedate`, `form_showuserprofiledata`,`form_heading` FROM `form_desc` ' .
					'WHERE `page_modulecomponentid` = \'' . $moduleComponentId."'";
	$formDescResult = mysql_query($formDescQuery);
	
	$showUserEmail = $showUserFullName = false;
	$showRegistrationDate = $showLastUpdateDate = true;
	$showUserProfileData = false;
	$formName='';
	if($formDescRow = mysql_fetch_row($formDescResult)) {
		
		$showUserEmail = $formDescRow[0] == 1;
		$showUserFullName = $formDescRow[1] == 1;
		$showRegistrationDate = $formDescRow[2] == 1;
		$showLastUpdateDate = $formDescRow[3] == 1;
		$showUserProfileData = $formDescRow[4] == 1;
		$formName = $formDescRow[5];
	}
	$showEditButtons = $action == 'editregistrants';

	$columnList = getColumnList($moduleComponentId, $showUserEmail, $showUserFullName, $showRegistrationDate, $showLastUpdateDate, $showUserProfileData);
	$columnNames = array();

	/*$normalImage = "<img alt=\"Sort by this field\" height=\"12\" width=\"12\" style=\"padding:0px\" src=\"$urlRequestRoot/$cmsFolder/$templateFolder/common/icons/16x16/actions/view-refresh.png\" />";
	$orderedImage = "<img alt=\"Sort by this field\" height=\"12\" width=\"12\" style=\"padding:0px\" src=\"$urlRequestRoot/$cmsFolder/$templateFolder/common/icons/16x16/actions/go-" . ($sortOrder == 'asc' ? 'up' : 'down') . ".png\" />";

	$tableCaptions = "<thead><tr>\n<th nowrap=\"nowrap\" class=\"sortable-numeric\">S. No.</th>\n";
	if($showEditButtons) {
		$tableCaptions .= '<th nowrap="nowrap">Edit</th><th nowrap="nowrap">Delete</th>';
	}
	foreach($columnList as $columnName => $columnTitle) {
		$tableCaptions .= "<th nowrap=\"nowrap\" class=\"sortable-text\">$columnTitle</th>\n";
		$columnNames[] = $columnName;
	}
	$tableCaptions .= "</tr></thead>\n";*/
	$tableCaptions = "<thead><tr>\n<th>S. No.</th>\n";
	$toggleColumns = "<fieldset><legend>Select Columns</legend><table><tr>";
	$tableJqueryStuff = "";
	$c=0;
	$d=0;
	if($showEditButtons) {
		$tableCaptions .= '<th>Actions</th>';
		$tableJqueryStuff = "null,";
		$c++;
	}
	foreach($columnList as $columnName => $columnTitle) {
		$tableCaptions .= "<th>$columnTitle</th>\n";
		$columnNames[] = $columnName;
		
		$c=$c+1;
		$checked="checked";
		if(!($columnName=="useremail" || $columnName=="registrationdate" || $columnName=="lastupdated"))
		{
			$tableJqueryStuff.="/* $columnTitle */ { \"bVisible\": false },";
			$checked="";
		}
		else $tableJqueryStuff.="null,";
		
		if($d!=0 && $d%5==0)
		 $toggleColumns.="</tr><tr>";
		$d=$d+1;
		$toggleColumns.="<td><input type='checkbox' onclick='fnShowHide($c);' $checked />$columnTitle <br/></td>";
	}
	
	$tableCaptions .= "</tr></thead>\n";
	$toggleColumns .= "</tr></table></fieldset>";
	
	$userIds = getDistinctRegistrants($moduleComponentId, $sortField, $sortOrder);
	$userCount = count($userIds);

	global $ICONS;
	$editImage = $ICONS['Edit']['small'];
	$deleteImage = $ICONS['Delete']['small'];

	$tableBody = '<tbody>';
	for($i = 0; $i < $userCount; $i++) {
		$tableBody .= '<tr><td>'.($i + 1).'</td>';
		if($showEditButtons) {
			if($userIds[$i] <= 0) {
				$tableBody .= '<td align="center">&nbsp;</td>';
			}
			else {
				$tableBody .= '<td align="center"><a title="Edit" href="./+editregistrants&subaction=edit&useremail='.getUserEmail($userIds[$i]).'" />' . $editImage . '</a>&nbsp;';
			}
			if($userIds[$i] <= 0) {
				$tableBody .= '<a style="cursor:pointer" title="Delete" onclick="return gotopage(\'./+editregistrants&subaction=delete&&useremail='.getUserEmail($userIds[$i]).'&registrantid='.$userIds[$i].'\',\''.getUserEmail($userIds[$i]).'\')" />' . $deleteImage . '</a></td>';
			}
			else {
				$tableBody .= '<a style="cursor:pointer" title="Delete" onclick="return gotopage(\'./+editregistrants&subaction=delete&useremail='.getUserEmail($userIds[$i]).'\',\''.getUserEmail($userIds[$i]).'\')" />' . $deleteImage . '</a></td>';
			}
		}
		$tableBody .= '<td>' . join(generateFormDataRow($moduleComponentId, $userIds[$i], $columnNames, $showUserProfileData), '</td><td>') . "</td></tr>\n";
	}
	$tableBody.="</tbody>";
	
	$tableJqueryStuff = "/* S. No. */ null, $tableJqueryStuff";
	
	$smarttable = smarttable::render(array('registrantstable'),array('registrantstable'=>array('aoColumns'=>"$tableJqueryStuff")));
	global $STARTSCRIPTS;
	$STARTSCRIPTS.="initSmartTable();";
	$javascriptBody = <<<JAVASCRIPTBODY
		$smarttable
		<script>
			function fnShowHide( iCol )
			{
				var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
				oTable.fnSetColumnVis( iCol, bVis ? false : true );
			}

		</script>
		<script language="javascript">
			function gotopage(pagepath,useremail) {
				if(confirm("Are you sure you want to remove "+useremail+" from this form?"))
					window.location = pagepath;
			}
	    </script>
JAVASCRIPTBODY;
	$imagesFolder=$urlRequestRoot."/".$cmsFolder."/templates/common/images";
	$editRegistrantsView = '<br />';
	if($action == 'editregistrants') {
		$editRegistrantsView .= <<<EDITREGISTRANTSVIEW
			<form name="addusertoformform" method="POST" action="./+editregistrants" style="float:left">
				<script type="text/javascript" language="javascript" src="$urlRequestRoot/$cmsFolder/$templateFolder/common/scripts/ajaxsuggestionbox.js">
				</script>

				<input type="text" name="useremail" id="userEmail" autocomplete="off" style="width: 256px" />
				<div id="suggestionsBox" class="suggestionbox"></div>

				
				<input type="submit" name="btnAddUserToForm" value="Add User to Form" />
				<script language="javascript" type="text/javascript">
				<!--
					var userBox = new SuggestionBox(document.getElementById('userEmail'), document.getElementById('suggestionsBox'), "./+editregistrants&subaction=getsuggestions&forwhat=%pattern%");
					userBox.loadingImageUrl = '$imagesFolder/ajaxloading.gif';
				-->
				</script>
			</form>
			<br /><br />
EDITREGISTRANTSVIEW;
	}
	//$editRegistrantsView .= $javascriptBody.'<table border="1" id="registrantstable" class="paginate-20 max-pages-5 no-arrow rowstyle-alt colstyle-alt sortable display">' . $tableCaptions . $tableBody . '</table><br />';
	$editRegistrantsView .= $javascriptBody.$toggleColumns.'<table border="1" id="registrantstable" class="display">' . $tableCaptions . $tableBody . '</table><br />';
	
	if(!$showEditButtons && isset($_POST['save_as_excel']))
	{
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false); 
		header("Content-Type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=\"$formName.xls\";" );
		header("Content-Transfer-Encoding: binary");
		echo '<table>' . $tableCaptions . $tableBody . '</table>';
		exit(1);
	}
	
	if($action == 'editregistrants') {
		$editRegistrantsView .= '<form name="emptyregistrants" method="POST" action="./+editregistrants" onsubmit="return confirm(\'Are you sure you wish to remove all registrants from this form? This will also remove the users from any groups associated with this form.\')">' .
			'<input type="submit" name="btnEmptyRegistrants" value="Delete All Registrants" title="Deletes all registrations to this form" />' .
			'</form>';
	}
	return $editRegistrantsView;
}


