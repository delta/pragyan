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
 * @copyright (c) 2012 Pragyan Team
 * @author balanivash<balanivash@gmail.com>
 * @author shriram<vshriram93@gmail.com>
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

function templateDesc($templateId,$templateName) {
		global $sourceFolder,$cmsFolder,$templateFolder,$moduleFolder,$urlRequestRoot;
		$imagePath = "$urlRequestRoot/$cmsFolder/$templateFolder";
		$sectionDesc = '';
		$sectionQuery = "SELECT * FROM `faculty_template` WHERE `template_id`=$templateId ORDER BY `template_sectionOrder` ASC";
		$sectionResult = mysql_query($sectionQuery) or displayerror(mysql_error());

		while($sectionRow = mysql_fetch_array($sectionResult)) {
			$sectionDesc .= convertToRow($sectionRow,$imagePath);
		}
		$formElementDescBody =<<<BODY
		<h2>Sections on template : {$templateName}</h2>
		<form id="formentries" action="./+edit&edittemplate" method="POST">
			<table cellpadding="1" cellspacing="1" border="1" style="width:75%;">
				<tr>
					<th style="width:15%;">Actions</th>
					
					<th>Name</th>
					<th style="width:15%;">Parent</th>
					<th style="width:15%;">Limit</th>
				</tr>
				<input type="hidden" name="templateid" value={$templateId} />
					$sectionDesc
				</tr>
			</table>

		<input type="submit" name="addSectionElement" value="Add More Sections">

		</form>
BODY;

		return $formElementDescBody;
}

function convertToRow($sectionRow,$imagePath){

	$rowString = <<<ROWSTRING
	<script language="javascript">
			function gotopage(pagepath) {
				if(confirm("Are you sure you want to delete this form section?"))
					window.location = pagepath;
			}
	    </script>
		<tr>
			<td>
				<a href="./+edit&subaction=moveUp&sectionId={$sectionRow[2]}">
					<img src="$imagePath/common/icons/16x16/actions/go-up.png" alt="Move Up" title="Move Up"/>
				</a>
			
				<a href="./+edit&subaction=moveDown&sectionId={$sectionRow[2]}">
					<img src="$imagePath/common/icons/16x16/actions/go-down.png" alt="Move Down" title="Move Down"/>
				</a>
			
				<a href="./+edit&subaction=editSection&sectionId={$sectionRow[2]}">
					<img src="$imagePath/common/icons/16x16/apps/accessories-text-editor.png" alt="Edit" title="Edit" />
				</a>
			
				<a style="cursor:pointer" onclick="return gotopage('./+edit&subaction=deleteSection&sectionId={$sectionRow[2]}')">
					<img src="$imagePath/common/icons/16x16/actions/edit-delete.png" alt="Delete" title="Delete" />
					</a>
			</td>
			<td>
				{$sectionRow[4]}
			</td>
			<td>
				{$sectionRow[3]}
			</td>
			<td>
				{$sectionRow[5]}
			</td>
		</tr>

ROWSTRING;

	return $rowString;		
}
function editSectionForm($sectionId,$templateId){
	$availableSectionsQuery = "SELECT `template_sectionId` FROM `faculty_template` WHERE `template_id`=$templateId AND `template_sectionId` != $sectionId";
	$availableSectionsResult = mysql_query($availableSectionsQuery) or displayerror("Unable to select available sections!!!");
	
		
	
}



/******Done By SHRIRAM */
function getTemplateDataFromModuleComponentId($moduleComponentId)
{
	$templateIdQuery="SELECT `templateId` FROM `faculty_module` WHERE `page_moduleComponentId`=$moduleComponentId";
	$templateIdResult=mysql_query($templateIdQuery) or displayerror("Unable to find required moduleComponentId");
	$template=mysql_fetch_assoc($templateIdResult);
	$templateId=$template["templateId"];
	$sectionDetailQuery="SELECT * FROM `faculty_template` WHERE `template_id`=$templateId AND `template_sectionParentId`=0";
	$sectionDetailResult=mysql_query($sectionDetailQuery) or displayerror("Unable to find required Template");
	return $sectionDetailResult;
}
function getTemplateId($moduleComponentId)
{
	$templateIdQuery="SELECT `templateId` FROM `faculty_module` WHERE `page_moduleComponentId`=$moduleComponentId";
	$templateIdResult=mysql_query($templateIdQuery) or displayerror("Unable to find required moduleComponentId");
	$template=mysql_fetch_assoc($templateIdResult);
	$templateId=$template["templateId"];
	
	return $templateId;
}
function printFacultyDataFaculty($sectionId,$moduleComponentId)
{
	$printData="";
	$sectionDataQuery="SELECT * FROM `faculty_data` WHERE `faculty_sectionId`=$sectionId AND `page_moduleComponentId`=$moduleComponentId";
	$sectionDataResult=mysql_query($sectionDataQuery) or displayerror("Unable to find required moduleComponentId");
	while($sectionDataArray=mysql_fetch_assoc($sectionDataResult))
	{
		$printData.=<<<details
			<div>
			<h2><div class="headerFirstSection" id="headerFirstSection{$sectionDataArray['faculty_dataId']}">{$sectionDataArray['faculty_data']}</div></h2>
			<div class="headerFirstSectionEdit" id="headerFirstSectionId{$sectionDataArray['faculty_dataId']}"  style="cursor:pointer;">edit</div>
			<div class="headerFirstSectionAdd" id="headerFirstSectionAdd{$sectionDataArray['faculty_dataId']}"  style="cursor:pointer;">add</div>
			<div class="headerFirstSectionGo" id="headerFirstSectionGo{$sectionDataArray['faculty_dataId']}"  style="cursor:pointer;">add</div>
			<div class="headerFirstSectionConfirm" id="headerFirstSectionConfirm{$sectionDataArray['faculty_dataId']}" style="cursor:pointer;">Confirm</div>
	</div><hr>
		
		
	
details;

	}
	return $printData;
}

function printFacultyData($sectionId,$moduleComponentId,$toPrint)
{
	$headPrint="";
	$printData="";
	$sectionDataQuery="SELECT * FROM `faculty_data` WHERE `faculty_sectionId`=$sectionId AND `page_moduleComponentId`={$moduleComponentId}";
	$sectionDataResult=mysql_query($sectionDataQuery) or displayerror("Unable to find required moduleComponentId");
	if($toPrint){
		$sectionDetailQuery="SELECT * FROM `faculty_template` WHERE `template_sectionId`=$sectionId ";
	$sectionDetailResult=mysql_query($sectionDetailQuery) or displayerror("Unable to find required Template");
	$resultantArray=mysql_fetch_assoc($sectionDetailResult);
	$headPrint.=<<<details
		
	
	<h3>{$resultantArray["template_sectionName"]}</h3>
		
details;
}
$headPrint.='<ul style="margin-left:25px;">';

	while($sectionDataArray=mysql_fetch_assoc($sectionDataResult))
	{
		$printData.=<<<details
<li>{$sectionDataArray['faculty_data']}</li>	
		
	
details;

	}
	if($printData=="") return "";
		$printData.=$headPrint."</ul>";

	return $printData;
}

function printFacultyDataWithLi($sectionId,$moduleComponentId)
{
	$printData="";
	$sectionDataQuery="SELECT * FROM `faculty_data` WHERE `faculty_sectionId`=$sectionId AND `page_moduleComponentId`=$moduleComponentId";
	$sectionDataResult=mysql_query($sectionDataQuery) or displayerror("Unable to find required moduleComponentId");
	
	
	
	while($sectionDataArray=mysql_fetch_assoc($sectionDataResult))
	{
		$printData.=<<<details
		<li>{$sectionDataArray['faculty_data']}</li>
details;

	}
	return $printData;
}



function printFacultyDataWithLiFaculty($sectionId,$moduleComponentId,$toPrint)
{
		global $urlRequestRoot,$sourceFolder,$cmsFolder,$templateFolder,$moduleFolder,$urlRequestRoot;
	
		$folder="$urlRequestRoot/$cmsFolder/$moduleFolder/faculty/images/";
	
	$printData="";
	$sectionDataQuery="SELECT * FROM `faculty_data` WHERE `faculty_sectionId`=$sectionId AND `page_moduleComponentId`=$moduleComponentId";
	$sectionDataResult=mysql_query($sectionDataQuery) or displayerror("Unable to find required moduleComponentId");
if($toPrint){

	$sectionDetailQuery="SELECT * FROM `faculty_template` WHERE `template_sectionId`=$sectionId ";
	$sectionDetailResult=mysql_query($sectionDetailQuery) or displayerror("Unable to find required Template");
	$resultantArray=mysql_fetch_assoc($sectionDetailResult);
				$printData.=<<<details
		
	
	<h3>{$resultantArray["template_sectionName"]}
		
details;
}
		$printData.=callAddData($sectionId, $moduleComponentId);
			$printData.=<<<details
	</h3>	
	<table cellspacing="1" cellpadding="1" border="1" style="width:75%">
	<tr>
		<th>Actions</th>
		<th>Data</th>
	</tr>
details;

	while($sectionDataArray=mysql_fetch_assoc($sectionDataResult))
	{
		
		$printData.=<<<details
				<tr >
				<div style="position:relative">
				<td width="15%">
				<img src="{$folder}edit.png" alt="edit" class="headerFirstSectionEdit" id="headerFirstSectionId{$sectionDataArray['faculty_dataId']}"  style="cursor:pointer;float:left;"/>&nbsp;
				<img src="{$folder}confirm.png" alt="confirm" class="headerFirstSectionConfirm" id="headerFirstSectionConfirm{$sectionDataArray['faculty_dataId']}" style="cursor:pointer;float:left;"/> &nbsp;
				<img src="{$folder}delete.png" alt="delete" class="headerFirstSectionDelete" id="headerFirstSectionDelete{$sectionDataArray['faculty_dataId']}"  style="cursor:pointer;display:inline;"/>&nbsp;
				</td>
				<td class="sectionDataInTable"><div class="headerFirstSection" id="headerFirstSection{$sectionDataArray['faculty_dataId']}" style="display:inline;">{$sectionDataArray['faculty_data']}</div></li>
				</td>
				</div>	
				</tr>
	
details;

	}
		$printData.=<<<details
</table>
details;

		return $printData;
}

function callAddData($sectionId,$moduleComponentId)
{
	
	
		global $urlRequestRoot,$sourceFolder,$cmsFolder,$templateFolder,$moduleFolder,$urlRequestRoot;
		$folder="$urlRequestRoot/$cmsFolder/$moduleFolder/faculty/images/";
		$printData=<<<details
				<div>
   					<img src="{$folder}add.png" class="addData" id="addData{$sectionId}" style="display:inline;cursor:pointer;">
					<table cellpadding="1" cellspacing="1" border="1" style="width:75%;display:none;">
						<tr>
							<th>Actions</th>
							<th>Data To Be Added</th>
						</tr>
					</table>
				</div>
details;
	return $printData;
}
function getEmailForFaculty($moduleComponentId)
{
	$moduleQuery="SELECT * FROM `faculty_module` WHERE `page_moduleComponentId`={$moduleComponentId}";
	$moduleResult=mysql_query($moduleQuery);
	$facultyResult=mysql_fetch_assoc($moduleResult);
	if($facultyResult["email"]) return $facultyResult["email"];
	return "";
}

