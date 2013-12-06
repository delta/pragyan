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
  * Qaos - This module lets you create an organisational tree structure for your organisation and rate people 
  * on certail parameters. You can have a parent-child relationship which can be defined by the admin user.
  * A demonstration of this can be seen at http://www.pragyan.org/08/home/qaos/
  * 
  * Features of this module 
  * 
  * 1. A proper tree structure of your organisation can be created with any number of levels. There are no 
  * restrictions on the number of levels which can occur for any member starting from the root.
  * 
  * 2. It lets you rate the members of your organisation depending upon certail parameters which can be changed 
  * depending upon the needs of the admin user.
  * 
  * TODO : To make the structure more generic and to write the code for copy and delete of this module.
  * 
  * Actions: Create		Edit		View
  *   		 Copy	  	Delete		Score
  * 
  * Create : Create a new module of Qaos.
  * View : displays the tree structure of the organisation
  * Edit : Add members to the tree depending upon your permissions and admin can add members in any team of 
  * 	   the organisation.
  * Score: Score people of your team and 
  * 
  * 
  */
class qaos implements module {
	private $userId;
	private $moduleComponentId;
	private $action;
	public function getHtml($gotuid, $gotmoduleComponentId, $gotaction) {
		$this->userId = $gotuid;
		$this->moduleComponentId = $gotmoduleComponentId;
		$this->action = $gotaction;
		if ($this->action == "view")
			return $this->actionView();
		if ($this->action == "edit")
			return $this->actionEdit($this->moduleComponentId);
		if ($this->action == "create")
			return $this->createModule($this->moduleComponentId);
		if ($this->action == "delete")
			return $this->deleteModule($this->moduleComponentId);
		if ($this->action == "copy")
			return $this->copyModule($this->moduleComponentId);
		if ($this->action == "score")
			return $this->actionScore($this->moduleComponentId);
		if ($this->action == "qaosadmin")
			return $this->actionQaosadmin();
	}
	public function actionView(){
		$score = false;
		return $this->generateTree($this->moduleComponentId,$score);
	}
	public function actionQaosadmin(){
		$moduleComponentId = $this->moduleComponentId;
		$score = true;
		if($_GET['subaction']=='viewscore'){
			if(isset($_GET['useremail'])){
				$userId = getUserIdFromEmail($_GET['useremail']);
				$htmlOut = "<br /><br />People who has been rated by this person:<br /><br />";
				$query = "SELECT * FROM `qaos_scoring` WHERE user_id = $userId";
				$result = mysql_query($query);
				$htmlOut .= "<table border=\"1\">";
				$htmlOut .= "<tr><th>Target User Full Name</th><th>Target User Team</th><th>Target User Designation</th><th>Target User Score1</th><th>Target User Reason1</th><th>Target User Score2</th><th>Target User Reason2</th><th>Target User Score3</th><th>Target User Reason3</th><th>Target User Score4</th><th>Target User Reason4</th><th>Target User Score5</th><th>Target User Reason5</th>";
				while($row = mysql_fetch_assoc($result)){
					$targetUserId = $row['targetuser_id'];
					$targetUserFullName = getUserFullName($targetUserId);
					$targetUserTeam = $this->getTeamNameFromTeamId($this->getTeamId($targetUserId));
					$targetUserDesignation = $this->getDesignationNameFromDesignationId($this->getDesignationId($targetUserId));
					$score1 = $row['qaos_score1'];$score2 = $row['qaos_score2'];$score3 = $row['qaos_score3'];$score4 = $row['qaos_score4'];$score5 = $row['qaos_score5'];
					$reason1 = $row['qaos_reason1'];$reason2 = $row['qaos_reason2'];$reason3 = $row['qaos_reason3'];$reason4 = $row['qaos_reason4'];$reason5 = $row['qaos_reason5'];
					$htmlOut .=<<<USERDATA
								<tr>
									<td> $targetUserFullName </td>
									<td> $targetUserTeam </td>
									<td> $targetUserDesignation </td>
									<td> $score1 </td>
									<td> $reason1 </td>
									<td> $score2 </td>
									<td> $reason2 </td>
									<td> $score3 </td>
									<td> $reason3 </td>
									<td> $score4 </td>
									<td> $reason4 </td>
									<td> $score5 </td>
									<td> $reason5 </td>
												
									
								</tr>
									 			
USERDATA;
				}
				$query = "SELECT count(*) as count FROM `qaos_scoring` WHERE user_id = '$userId'";
				$result = mysql_query($query);
				$row = mysql_fetch_assoc($result);
				$htmlOut .= "</table> <br />Total No of persons this guy has rated : ".$row['count'];
				 
				$htmlOut .= "<br /><br /><br />People who has rated this person:<br /><br />";
				$query = "SELECT * FROM `qaos_scoring` WHERE targetuser_id = '$userId'";
				$result = mysql_query($query);
				$htmlOut .= "<table border=\"1\">";
				$htmlOut .= "<tr><th>User Full Name</th><th>User Team</th><th>User Designation</th><th>User Score1</th><th>User Reason1</th><th>User Score2</th><th>User Reason2</th><th>User Score3</th><th>User Reason3</th><th>User Score4</th><th>User Reason4</th><th>User Score5</th><th>User Reason5</th>";
				while($row2 = mysql_fetch_assoc($result)){
					$targetUserId = $row2['targetuser_id'];
					$targetUserFullName = getUserFullName($userId);
					$targetUserTeam = $this->getTeamNameFromTeamId($this->getTeamId($userId));
					$targetUserDesignation = $this->getDesignationNameFromDesignationId($this->getDesignationId($userId));
					$score1 = $row2['qaos_score1'];$score2 = $row2['qaos_score2'];$score3 = $row2['qaos_score3'];$score4 = $row2['qaos_score4'];$score5 = $row2['qaos_score5'];
					$reason1 = $row2['qaos_reason1'];$reason2 = $row2['qaos_reason2'];$reason3 = $row2['qaos_reason3'];$reason4 = $row2['qaos_reason4'];$reason5 = $row2['qaos_reason5'];
					$htmlOut .=<<<USERDATA
								<tr>
									<td> $targetUserFullName </td>
									<td> $targetUserTeam </td>
									<td> $targetUserDesignation </td>
									<td> $score1 </td>
									<td> $reason1 </td>
									<td> $score2 </td>
									<td> $reason2 </td>
									<td> $score3 </td>
									<td> $reason3 </td>
									<td> $score4 </td>
									<td> $reason4 </td>
									<td> $score5 </td>
									<td> $reason5 </td>
								</tr>
USERDATA;
				}
				$query = "SELECT count(*) as count FROM `qaos_scoring` WHERE targetuser_id = '$userId'";
				$result = mysql_query($query);
				$row = mysql_fetch_assoc($result);
				$htmlOut .= "</table><br />Total number of person who have rated this person : ".$row['count']; 
				
				return $htmlOut;
			}
			return $this->generateTree($this->moduleComponentId,$score);
		}
		
		$htmlOut .=<<<ADMIN
					<a href="./+qaosadmin&subaction=addteam">Add Teams</a><br />
					<a href="./+qaosadmin&subaction=adddesignation">Add Designation</a><br />
					<a href="./+qaosadmin&subaction=changeteam">Change teams for the user</a><br />
					<a href="./+qaosadmin&subaction=viewscore">View Scores</a><br />
ADMIN;
		return $htmlOut;
	}
	public function actionEdit($moduleComponentId){
	global $urlRequestRoot;
	global $sourceFolder,$cmsFolder;
	global $templateFolder;
	$scriptsFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/scripts";
	$imagesFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/images";

		if(isset($_GET['subaction'])){
			if($_GET['subaction']=='addteam'){
				if(isset($_POST['btnAddTeam'])){
					$query = "SELECT MAX(`qaos_team_id`)  AS max FROM `qaos_teams`";
					$result = mysql_query($query);
					$resultArray = mysql_fetch_assoc($result);
					$max = $resultArray['max'];
					for($i=1; $i<6;$i++){
						if($teamName=$_POST["qaos_team".$i.""]){
							$query = "SELECT * FROM `qaos_teams` WHERE `qaos_team_name` LIKE '$teamName%'";
							$result = mysql_query($query);
							if(mysql_num_rows($result)>1){
								displayerror("The $teamName team already exists in the database.");
								continue;
							}
							$teamId = $max + $i;
							$teamDesc = $_POST["team_desc".$i.""];
							$query = "INSERT INTO `qaos_teams` (`page_modulecomponentid`,`qaos_team_id`,`qaos_team_name`,`qaos_team_description`) VALUES ('$moduleComponentId','$teamId','$teamName','$teamDesc')";
							$result = mysql_query($query);
							if(!$result)
								displayerror("The team '$teamName' could not be added. Please try again.");

						}
					}
				}
			}

			elseif($_GET['subaction']=='changeversion'){
				if(isset($_POST['btnSubmitVersion'])){
					$query = "UPDATE `qaos_version` SET `qaos_version` = '".escape($_POST[qaos_version])."' WHERE `page_modulecomponentid` = '$moduleComponentId'";
					$result = mysql_query($query);
					if(mysql_query($query))
						displayinfo("The version has been successfully updated.");
					else
						displayinfo("There was some error while updating the version. Please check your query once.");
					}
			}
			elseif($_GET['subaction']=='addteammember'){
				if(isset($_POST['btnAddTeamMember'])){
					$emailName = $_POST['useremail'];
					$input = explode(" - ",$emailName);
					$email = $input[0];
					$designation = $_POST['userdesignation'];
					$team = $_POST['userteam'];
					$parentTeam =$_POST['userparentteam'];
					$parentDesignation = $_POST['userparentdesignation'];
					$name = $this->addTeamMember($email,$designation,$team,$parentTeam,$parentDesignation);
					if($team = "Qaos"){
						$this->addQaosTeamMember(getUserIdFromEmail($email),$_POST['qaosteam1'],$_POST['qaosteam2'],$_POST['qaosteam3'],$_POST['qaosteam4']);
					}
				}
			}
			elseif($_GET['subaction'] == 'getsuggestions' && isset($_GET['forwhat'])) {
				echo $this->getSuggestions($_GET['forwhat'], $_GET['suggestiontype']);
				exit();
			}
		}


		$queryVersion = "SELECT `qaos_version` FROM `qaos_version` WHERE `page_modulecomponentid` = '$moduleComponentId'";
		$resultVersion = mysql_query($queryVersion);
		$row = mysql_fetch_row($resultVersion);
		$version = $row[0];
		$html .= "<h2>$version</h2>	<br />";
		if(getPermissions($this->userId,getPageIdFromModuleComponentId("qaos",$this->moduleComponentId),"create")){
			$html .= <<<EDITQAOS
			<div class="changeqaosversion">
				<form id="changeqaosversion" method="POST" onsubmit="return checkProfileForm(this)" action="./+edit&subaction=changeversion">
					<fieldset style="width:80%">
						<legend><b>Change the Version</b></legend>
						<table>
							<tr>
								<td>
									Changer Qaos version:
								</td>
								<td>
									<input name="qaos_version" id="qaos_version" value="$version" type="text">
								</td>
								<td><input type="submit" name="btnSubmitVersion" id="submitbutton" value="Save Version"></td>
							</tr>
						</table>
					</fieldset>
				</form>
			</div>
EDITQAOS;
		}
		$html .= "<br /><h3>Teams in Pragyan 2008: </h3><br />";
		$queryTeam = "SELECT * FROM `qaos_teams` WHERE `page_modulecomponentid`='$moduleComponentId' ORDER BY `qaos_team_name`";
		$resultTeam = mysql_query($queryTeam);
		$html.= "<table border=\"1\"><tr><td><b>Team Name</b></td><td><b>Team Description</b></td><td><b>Team Representative</b></td></tr>";
		while($row = mysql_fetch_row($resultTeam)){
			$team = $row[2];
			$desc = $row[3];
			$repr = $row[4];
			$html .= "<tr><td>$team</td><td>$desc</td><td>$repr</td></tr>";
		}
		$html .= "</table><br /><br />";
		$userTeamId = $this->getTeamId($this->userId);
		if($userTeamId ==1){
		$html .=<<<ADDTEAMS
		<div class="registrationform">
			<div class="addteam">
				<form id="addteam" method="POST" onsubmit="return checkProfileForm(this)" action="./+edit&subaction=addteam">
					<fieldset style="width:80%">
						<legend><b>Add Teams</b></legend>
						<table>
							<tr>
									<tr>
										<td>
											Enter the Team Name:
										</td>
										<td>
											<input name="qaos_team1" id="qaos_team" type="text">
										</td>
									</tr>
									<tr>
										<td>
											Enter the Team Description:
										</td>
										<td>
											<input name="team_desc1" id="team_desc" type="text">
										</td>
									</tr>
							</tr>
						</table>
						<input value="Add more teams" onclick="javascript:toggleuploadfiles(this);" type="button">
					<span class="hiddenteams"><table>
							<tr>
										<td>
										Enter the Team Name:
										</td>
										<td>
											<input name="qaos_team2" id="qaos_team" type="text">
										</td>
									</tr>
									<tr>
										<td>
											Enter the Team Description:
										</td>
										<td>
											<input name="team_desc2" id="team_desc" type="text">
										</td>
									</tr>
									<tr><td><br /></td></tr>

									<tr>
										<td>
											Enter the Team Name:
										</td>
										<td>
											<input name="qaos_team3" id="qaos_team" type="text">
										</td>
									</tr>
									<tr>
										<td>
											Enter the Team Description:
										</td>
										<td>
											<input name="team_desc3" id="team_desc" type="text">
										</td>
									</tr>
		<tr><td><br /></td></tr>
								<tr>
										<td>
											Enter the Team Name:
										</td>
										<td>
											<input name="qaos_team4" id="qaos_team" type="text">
										</td>
									</tr>
									<tr>
										<td>
											Enter the Team Description:
										</td>
										<td>
											<input name="team_desc4" id="team_desc" type="text">
										</td>
									</tr>
		<tr><td><br /></td></tr>
								<tr>
										<td>
											Enter the Team Name:
										</td>
										<td>
											<input name="qaos_team5" id="qaos_team" type="text">
										</td>
									</tr>
									<tr>
										<td>
											Enter the Team Description:
										</td>
										<td>
											<input name="team_desc5" id="team_desc" type="text">
										</td>
									</tr>

							</table>
					</span>
							<tr>
								<td>
									<input type="submit" name="btnAddTeam" id="submitbutton" value="Submit">
								</td>
							</tr>

					</fieldset>
				</form>
			</div>
		</div>
		<style type="text/css">
			.hiddenteams{display:none;}
			.shownteams{display:block;}
		</style>
		<script language="javascript" type="text/javascript">
			function toggleuploadfiles(gett) {
				if(gett.nextSibling.nextSibling.className != "shownteams")
				{
					gett.nextSibling.nextSibling.className = "shownteams";
					gett = gett.nextSibling.nextSibling;
				}
				else
				{
					gett.nextSibling.nextSibling.className = "hiddenteams";
					gett = gett.nextSibling.nextSibling;
				}
			}
		</script>
ADDTEAMS;
		}
		$html .= "<br />";
		$html .=<<<ADDPERSON
		<script type="text/javascript" language="javascript">
		<!--
			imgAjaxLoading = new Image();
			imgAjaxLoading.src = '$imagesFolder/ajaxloading.gif';
		-->
		</script>
		<style type="text/css">
		<!--
			span.suggestion {
				padding: 2px 4px 2px 4px;
				display: block;
				background-color: white;
				cursor: pointer;
			}
			span.suggestion:hover {
				background-color: #DEDEDE;
			}
		-->
		</style>
		<script type="text/javascript" language="javascript" src="$scriptsFolder/ajaxsuggestionbox.js"></script>
		<div class="registrationform">
			<div class="addteammember">

				<form id="addteammember" method="POST" onsubmit="return checkProfileForm(this)" action="./+edit&subaction=addteammember">
					<fieldset style="width:80%">
						<legend><b>Add Team Members</b></legend>
							<table>
								<tr>
									<td>
										Enter the Team Member Name:
									</td>
									<td>
										<input type="text" name="useremail" id="userEmail" autocomplete="off" style="width: 256px" />
										<div id="suggestionsName" class="suggestionbox"></div>
									</td>
								</tr>
								<tr>
									<td>
										Enter the User Designation:
									</td>
									<td>
										<input type="text" name="userdesignation" id="userDesignation" autocomplete="off" style="width: 256px" />
									<div id="suggestionsDesignation" style="background-color: white; width: 260px; border: 1px solid black; position: absolute; overflow-y: scroll; max-height: 180px; display: none"></div>
									</td>
								</tr>
								<tr>
									<td>
										Enter the Team Name:
									</td>
									<td>
										<input type="text" name="userteam" id="userTeam" autocomplete="off" style="width: 256px" />
										<div id="suggestionsTeam" style="background-color: white; width: 260px; border: 1px solid black; position: absolute; overflow-y: scroll; max-height: 180px; display: none"></div>
									</td>
								</tr>
								<tr>
									<td>
										Enter the Parent Team Name:
									</td>
									<td>
										<input type="text" name="userparentteam" id="userParentTeam" autocomplete="off" style="width: 256px" />
										<div id="suggestionsParentTeam" style="background-color: white; width: 260px; border: 1px solid black; position: absolute; overflow-y: scroll; max-height: 180px; display: none"></div>
									</td>
								</tr>
								<tr>
									<td>
										Enter the Parent Designation:
									</td>
									<td>
										<input type="text" name="userparentdesignation" id="userParentDesignation" autocomplete="off" style="width: 256px" />
										<div id="suggestionsParentDesignation" style="background-color: white; width: 260px; border: 1px solid black; position: absolute; overflow-y: scroll; max-height: 180px; display: none"></div>
									</td>
								</tr>
								<tr>
									<td>
										Enter Qaos Team1:
									</td>
									<td>
										<input type="text" name="qaosteam1" id="qaosTeam1" autocomplete="off" style="width: 256px" />
										<div id="suggestionsQaosTeam1" style="background-color: white; width: 260px; border: 1px solid black; position: absolute; overflow-y: scroll; max-height: 180px; display: none"></div>
									</td>
								</tr>
								<tr>
									<td>
										Enter Qaos Team2:
									</td>
									<td>
										<input type="text" name="qaosteam2" id="qaosTeam2" autocomplete="off" style="width: 256px" />
										<div id="suggestionsQaosTeam2" style="background-color: white; width: 260px; border: 1px solid black; position: absolute; overflow-y: scroll; max-height: 180px; display: none"></div>
									</td>
								</tr>
								<tr>
									<td>
										Enter Qaos Team3:
									</td>
									<td>
										<input type="text" name="qaosteam3" id="qaosTeam3" autocomplete="off" style="width: 256px" />
										<div id="suggestionsQaosTeam3" style="background-color: white; width: 260px; border: 1px solid black; position: absolute; overflow-y: scroll; max-height: 180px; display: none"></div>
									</td>
								</tr>
								<tr>
									<td>
										Enter Qaos Team4:
									</td>
									<td>
										<input type="text" name="qaosteam4" id="qaosTeam4" autocomplete="off" style="width: 256px" />
										<div id="suggestionsQaosTeam4" style="background-color: white; width: 260px; border: 1px solid black; position: absolute; overflow-y: scroll; max-height: 180px; display: none"></div>
									</td>
								</tr>
								
								<tr>
									<td><input type="submit" name="btnAddTeamMember" id="submitbutton" value="Add Team Member"></td>
								</tr>

							<script language="javascript" type="text/javascript">
							<!--
								nameSuggestionBox = new SuggestionBox(document.getElementById('userEmail'), document.getElementById('suggestionsName'), './+edit&subaction=getsuggestions&forwhat=%pattern%&suggestiontype=username');
								nameSuggestionBox.loadingImageUrl = '$imagesFolder/ajaxloading.gif';
								designationSuggestionBox = new SuggestionBox(document.getElementById('userDesignation'), document.getElementById('suggestionsDesignation'), './+edit&subaction=getsuggestions&forwhat=%pattern%&suggestiontype=designation');
								designationSuggestionBox.loadingImageUrl = '$imagesFolder/ajaxloading.gif';
								designationSuggestionBox = new SuggestionBox(document.getElementById('userTeam'), document.getElementById('suggestionsTeam'), './+edit&subaction=getsuggestions&forwhat=%pattern%&suggestiontype=team');
								designationSuggestionBox.loadingImageUrl = '$imagesFolder/ajaxloading.gif';
								designationSuggestionBox = new SuggestionBox(document.getElementById('userParentTeam'), document.getElementById('suggestionsParentTeam'), './+edit&subaction=getsuggestions&forwhat=%pattern%&suggestiontype=team');
								designationSuggestionBox.loadingImageUrl = '$imagesFolder/ajaxloading.gif';
								designationSuggestionBox = new SuggestionBox(document.getElementById('userParentDesignation'), document.getElementById('suggestionsParentDesignation'), './+edit&subaction=getsuggestions&forwhat=%pattern%&suggestiontype=designation');
								designationSuggestionBox.loadingImageUrl = '$imagesFolder/ajaxloading.gif';
								designationSuggestionBox = new SuggestionBox(document.getElementById('qaosTeam1'), document.getElementById('suggestionsQaosTeam1'), './+edit&subaction=getsuggestions&forwhat=%pattern%&suggestiontype=team');
								designationSuggestionBox.loadingImageUrl = '$imagesFolder/ajaxloading.gif';
								designationSuggestionBox = new SuggestionBox(document.getElementById('qaosTeam2'), document.getElementById('suggestionsQaosTeam2'), './+edit&subaction=getsuggestions&forwhat=%pattern%&suggestiontype=team');
								designationSuggestionBox.loadingImageUrl = '$imagesFolder/ajaxloading.gif';
								designationSuggestionBox = new SuggestionBox(document.getElementById('qaosTeam3'), document.getElementById('suggestionsQaosTeam3'), './+edit&subaction=getsuggestions&forwhat=%pattern%&suggestiontype=team');
								designationSuggestionBox.loadingImageUrl = '$imagesFolder/ajaxloading.gif';
								designationSuggestionBox = new SuggestionBox(document.getElementById('qaosTeam4'), document.getElementById('suggestionsQaosTeam4'), './+edit&subaction=getsuggestions&forwhat=%pattern%&suggestiontype=team');
								designationSuggestionBox.loadingImageUrl = '$imagesFolder/ajaxloading.gif';
								
							-->
							</script>

							</table>
					</fieldset>
				</form>
			</div>
		</div>

ADDPERSON;
// if the user team is core, then display the parent team name and designation field, otherwise disable it!

		if($userTeamId == $this->getTeamIdFromTeamName("Core")){
			$html .=<<<DISABLEPARENTFIELD
				<script language="javascript" type="text/javascript">
					document.getElementById("userParentTeam").disabled=false;
					document.getElementById("userParentDesignation").disabled=false;
				</script>
DISABLEPARENTFIELD;
		}
		else if($userTeamId == $this->getTeamIdFromTeamName("Qaos")){
			$html .=<<<DISABLEPARENTFIELD
				<script language="javascript" type="text/javascript">
					document.getElementById("qaosTeam1").disabled=false;
					document.getElementById("qaosTeam2").disabled=false;
					document.getElementById("qaosTeam3").disabled=false;
					document.getElementById("qaosTeam4").disabled=false;
				</script>
DISABLEPARENTFIELD;
		}
	
		else 
			$html .=<<<DISABLEPARENTFIELD
				<script language="javascript" type="text/javascript">
						document.getElementById("userParentTeam").disabled=true;
						document.getElementById("userParentDesignation").disabled=true;
						document.getElementById("qaosTeam1").disabled=true;
						document.getElementById("qaosTeam2").disabled=true;
						document.getElementById("qaosTeam3").disabled=true;
						document.getElementById("qaosTeam4").disabled=true;
				</script>
DISABLEPARENTFIELD;
			return $html;

	}

	function getSuggestions($pattern, $patternType = 'username') {
		if($patternType == 'username') {
			return getSuggestions($pattern);
		}
		else if($patternType == 'designation'){
			$suggestionsQuery = "SELECT `qaos_designation_name` FROM `qaos_designations` WHERE `qaos_designation_name` LIKE \"%$pattern%\"" ;
			$suggestionsResult = mysql_query($suggestionsQuery) or die(mysql_error());
			$suggestions = array($pattern);
			while($suggestionsRow = mysql_fetch_row($suggestionsResult)) {
				$suggestions[] = $suggestionsRow[0];
			}
			return join($suggestions, ',');
		}
		else if($patternType == 'team'){
			$suggestionsQuery = "SELECT `qaos_team_name` FROM `qaos_teams` WHERE `qaos_team_name` LIKE \"%$pattern%\"" ;
			$suggestionsResult = mysql_query($suggestionsQuery) or die(mysql_error());
			$suggestions = array($pattern);
			while($suggestionsRow = mysql_fetch_row($suggestionsResult)) {
				$suggestions[] = $suggestionsRow[0];
			}
			return join($suggestions, ',');

		}
	}
	function getTeamId($userId){
		$query = "SELECT `qaos_teams`.`qaos_team_id` FROM `qaos_teams`,`qaos_units`,`qaos_users` WHERE `qaos_users`.`user_id`='$userId' AND `qaos_users`.`qaos_unit_id` = `qaos_units`.`qaos_unit_id` AND `qaos_units`.`qaos_team_id`=`qaos_teams`.`qaos_team_id`";
		$result = mysql_query($query);
		$row = mysql_fetch_assoc($result);
		$teamId = $row['qaos_team_id'];
		return $teamId;
	}
	function getDesignationId($userId){
		$query = "SELECT `qaos_designations`.`qaos_designation_id` FROM `qaos_designations`,`qaos_units`,`qaos_users` WHERE `qaos_users`.`user_id`='$userId' AND `qaos_users`.`qaos_unit_id` = `qaos_units`.`qaos_unit_id` AND `qaos_units`.`qaos_designation_id`=`qaos_designations`.`qaos_designation_id`";
		$result = mysql_query($query);
		$row = mysql_fetch_assoc($result);
		$designationId = $row['qaos_designation_id'];
		return $designationId;
	}
	function getDesignationPriority($designationId){
		$query = "SELECT `qaos_designation_priority` FROM `qaos_designations` WHERE `qaos_designation_id`='$designationId'";
		$result = mysql_query($query);
		$row = mysql_fetch_assoc($result);
		$designationPriority = $row['qaos_designation_priority'];
		return $designationPriority;
	}
	function getUnitId($teamId,$designationId){
		$query = "SELECT `qaos_unit_id` FROM `qaos_units` WHERE `qaos_team_id`='$teamId' AND `qaos_designation_id`='$designationId'";
		$result = mysql_query($query);
		$row = mysql_fetch_assoc($result);
		$unitId = $row['qaos_unit_id'];
		return $unitId;
	}
	function getUnitIdFromUserId($userId){
		$query = "SELECT `qaos_unit_id` FROM `qaos_users` WHERE `user_id`='$userId'";
		$result = mysql_query($query);
		$row = mysql_fetch_assoc($result);
		$unitId = $row['qaos_unit_id'];
		return $unitId;
		
	}
	function getDesignationIdFromDesignationName($designation){
		$query = "SELECT `qaos_designation_id` FROM `qaos_designations` WHERE `qaos_designation_name`='$designation'";
		$result = mysql_query($query);
		$row = mysql_fetch_row($result);
		$designationId = $row[0];
		return $designationId;
	}
	function getDesignationNameFromDesignationId($designationId){
		$query = "SELECT `qaos_designation_name` FROM `qaos_designations` WHERE `qaos_designation_id`='$designationId'";
		$result = mysql_query($query);
		$row = mysql_fetch_row($result);
		$designationName = $row[0];
		return $designationName;
	}
	function getTeamIdFromTeamName($teamName){
		$query = "SELECT `qaos_team_id` FROM `qaos_teams` WHERE `qaos_team_name`='$teamName'";
		$result = mysql_query($query);
		$row = mysql_fetch_row($result);
		$teamId = $row[0];
		return $teamId;
	}
	function getTeamNameFromTeamId($teamId){
		$query = "SELECT `qaos_team_name` FROM `qaos_teams` WHERE `qaos_team_id`='$teamId'";
		$result = mysql_query($query);
		$row = mysql_fetch_row($result);
		$teamName = $row[0];
		return $teamName;
	}
	function addQaosTeamMember($userId,$qaosTeam1,$qaosTeam2,$qaosTeam3,$qaosTeam4){
		
		if($qaosTeam1){
			$memberadd = false;
			$query = "SELECT `qaos_representative_user_id1` as user1,`qaos_representative_user_id2` as user2 FROM `qaos_teams` WHERE `qaos_team_name`='$qaosTeam1'";
			$result = mysql_query($query);
			$row = mysql_fetch_assoc($result);
			$userRep11 = $row['user1'];
			$userRep12 = $row['user2'];
			if($userRep11 && $userRep12){
				displayerror("Sorry, this qaos member can not be added to the team. Already two members has been assigned to this team.");
			}
			else if(!$userRep11 && !$memberadd){
				$query = "UPDATE `qaos_teams` SET `qaos_representative_user_id1` = '$userId' WHERE `qaos_team_name` = '$qaosTeam1'";
				if($result = mysql_query($query)){
					displayinfo("User Successfully added in $qaosTeam1");
					$memberadd = true;
				}
				
			}
			else if(!$userRep12 && !$memberadd){
				$query = "UPDATE `qaos_teams` SET `qaos_representative_user_id2` = '$userId' WHERE `qaos_team_name` = '$qaosTeam1'";
				if($result = mysql_query($query)){
					displayinfo("User Successfully added in $qaosTeam1");
					$memberadd = true;
				}
			}
		}
		if($qaosTeam2){
			$memberadd = false;
			$userRep1 = NULL;
			$userRep2 = NULL;
			$query2 = "SELECT `qaos_representative_user_id1` as user1,`qaos_representative_user_id2` as user2 FROM `qaos_teams` WHERE `qaos_team_name`='$qaosTeam2'";
			$result2 = mysql_query($query2);
			$row2 = mysql_fetch_assoc($result2);
			$userRep1 = $row2['user1'];
			$userRep2 = $row2['user2'];
			if($userRep1 && $userRep2){
				displayerror("Sorry, this qaos member can not be added to $qaosTeam2. Already two members has been assigned to this team.");
			}
			else if(!$userRep1 && !$memberadd){
				$query = "UPDATE `qaos_teams` SET `qaos_representative_user_id1` = '$userId' WHERE `qaos_team_name` = '$qaosTeam2'";
				if($result = mysql_query($query)){
					displayinfo("User Successfully added in $qaosTeam2");
					$memberadd = true;
				}
				
			}
			else if(!$userRep2 && !$memberadd){
				$query = "UPDATE `qaos_teams` SET `qaos_representative_user_id2` = '$userId' WHERE `qaos_team_name` = '$qaosTeam2'";
				if($result = mysql_query($query)){
					displayinfo("User Successfully added in $qaosTeam2");
					$memberadd = true;
				}
			}
		}
		if($qaosTeam3){
			$memberadd = false;
			$userRep1 = NULL;
			$userRep2 = NULL;
			$query3 = "SELECT `qaos_representative_user_id1` as user1,`qaos_representative_user_id2` as user2 FROM `qaos_teams` WHERE `qaos_team_name`='$qaosTeam3'";
			$result3 = mysql_query($query3);
			$row3 = mysql_fetch_assoc($result3);
			$userRep1 = $row3['user1'];
			$userRep2 = $row3['user2'];
			if($userRep1 && $userRep2){
				displayerror("Sorry, this qaos member can not be added to $qaosTeam3. Already two members has been assigned to this team.");
			}
			else if(!$userRep1 && !$memberadd){
				$query = "UPDATE `qaos_teams` SET `qaos_representative_user_id1` = '$userId' WHERE `qaos_team_name` = '$qaosTeam3'";
				if($result = mysql_query($query)){
					displayinfo("User Successfully added in $qaosTeam3");
					$memberadd = true;
				}
				
			}
			else if(!$userRep2 && !$memberadd){
				$query = "UPDATE `qaos_teams` SET `qaos_representative_user_id2` = '$userId' WHERE `qaos_team_name` = '$qaosTeam3'";
				if($result = mysql_query($query)){
					displayinfo("User Successfully added in $qaosTeam3");
					$memberadd = true;
				}
			}
		}
		if($qaosTeam4){
			$memberadd = false;
			$userRep1 = NULL;
			$userRep2 = NULL;
			$query4 = "SELECT `qaos_representative_user_id1` as user1,`qaos_representative_user_id2` as user2 FROM `qaos_teams` WHERE `qaos_team_name`='$qaosTeam4'";
			$result4 = mysql_query($query4);
			$row4 = mysql_fetch_assoc($result4);
			$userRep1 = $row4['user1'];
			$userRep2 = $row4['user2'];
			if($userRep1 && $userRep2){
				displayerror("Sorry, this qaos member can not be added to $qaosTeam4. Already two members has been assigned to this team.");
			}
			else if(!$userRep1 && !$memberadd){
				$query = "UPDATE `qaos_teams` SET `qaos_representative_user_id1` = '$userId' WHERE `qaos_team_name` = '$qaosTeam4'";
				if($result = mysql_query($query)){
					displayinfo("User Successfully added in $qaosTeam4");
					$memberadd = true;
				}
				
			}
			else if(!$userRep2 && !$memberadd){
				$query = "UPDATE `qaos_teams` SET `qaos_representative_user_id2` = '$userId' WHERE `qaos_team_name` = '$qaosTeam4'";
				if($result = mysql_query($query)){
					displayinfo("User Successfully added in $qaosTeam4");
					$memberadd = true;
				}
			}
		}
		
		return;
	}
	function addTeamMember($email,$designation,$team,$newparentTeam,$newparentDesignation){

		$userId = getUserIdFromEmail($email);
		$parentUserId = $this->userId;

/**		Get the unit id, the team id and the designation id and
 * 		the designation priority of the parent.
 */
		$parentTeamId = $this->getTeamId($parentUserId);
		$parentDesignationId = $this->getDesignationId($parentUserId);
		$parentUnitId = $this->getUnitId($parentTeamId,$parentDesignationId);
		$parentDesignationPriority = $this->getDesignationPriority($parentDesignationId);

//		get the team id of the user.

		$teamId = $this->getTeamIdFromTeamName($team);

/** 	Check whether the parent has authority to create the child entry in the specified team. In case
 * 		of a manager and other lower members of the team, they wont be allowed to enter any other member
 * 		in their team. The core team has rights to add anyone in any team.
 * 		So checks will be either of these
 * 		1. same team
 * 		2. check for designation priority, if there is anyone above the manager level, give him rights to add
 * 		anyone in any team.
 *
 */

		if($teamId != $parentTeamId && $parentDesignationPriority <= 3){
			displayerror("You cannot add members into some other team.");
			return $name;
		}
// get the designation id of the user being added from the designation name.
		$designationId = $this->getDesignationIdFromDesignationName($designation);
// now check if the parent has higher priority or not.If parent has lower priority, give an error and leave.
		$designationPriority = $this->getDesignationPriority($designationId);
		if($designationPriority > $parentDesignationPriority){
			displayerror("You can not add members higher than your level");
			return $name;
		}
		if(($newparentDesignation)&&($newparentTeam)){
			$parentTeamId = $this->getTeamIdFromTeamName($newparentTeam);
			$parentDesignationId = $this->getDesignationIdFromDesignationName($newparentDesignation);
			$parentUnitId = $this->getUnitId($parentTeamId,$parentDesignationId);
		}
// now check if the user exists or not
		$query = "SELECT `user_id`,`qaos_unit_id` FROM `qaos_users` WHERE `user_id`='$userId'";
		$result = mysql_query($query);
		if($row = mysql_fetch_assoc($result)){
			$unitId = $row['qaos_unit_id'];
			$queryTeam = "SELECT `qaos_teams`.`qaos_team_name` FROM `qaos_teams`, `qaos_units` WHERE `qaos_teams`.`qaos_team_id`=`qaos_units`.`qaos_team_id` AND `qaos_units`.`qaos_unit_id`='$unitId'";
			$resultTeam = mysql_query($queryTeam);
			$row = mysql_fetch_assoc($resultTeam);
			var_dump($row);
			$teamName = $row['qaos_team_name'];
			displayerror("Sorry, the user can not be added. The person already exists in the ".$teamName." team.");
		}
		else {
// Check whether the unit already exists in the database or not. If not add a new unit, and then add the user otherwise directly add the user
			$queryUnits = "SELECT `qaos_unit_id` FROM `qaos_units` WHERE `qaos_team_id`='$teamId' AND `qaos_designation_id`='$designationId'";
			$resultUnits = mysql_query($queryUnits);
//if the unit exist, just add the user to the qaos_user table.
			if($rowUnits = mysql_fetch_assoc($resultUnits)){
				$unitId = $rowUnits['qaos_unit_id'];
				$queryUsers = "INSERT INTO `qaos_users` (`page_modulecomponentid`,`user_id`,`qaos_unit_id`) VALUES ('$this->moduleComponentId','$userId','$unitId')";
				$resultUsers = mysql_query($queryUsers);
				if($resultUsers)
					displayinfo("The User was successfully added to the team");
				else
					displayerror("There was some error in adding the user to the table");
			}
//if the unit does not exist, add a new unit, add the unit in the tree and add the new user.
			else{
				$queryMaxUnitid = "SELECT MAX(`qaos_unit_id`) AS MAX FROM `qaos_units`";
				$resultMaxUnitid = mysql_query($queryMaxUnitid);
				$rowMaxUnitid = mysql_fetch_assoc($resultMaxUnitid);
				$unitId = 1 + $rowMaxUnitid['MAX'];
				$queryInsertUnit = "INSERT INTO `qaos_units` (`page_modulecomponentid`,`qaos_unit_id`,`qaos_team_id`,`qaos_designation_id`) VALUES ('$this->moduleComponentId','$unitId','$teamId','$designationId')";
				$resultInsertUnit = mysql_query($queryInsertUnit);
				//echo $parentUnitId."two";
				$queryInsertTree = "INSERT INTO `qaos_tree` (`page_modulecomponentid`,`qaos_unit_id`,`qaos_parentunit_id`) VALUES ('$this->moduleComponentId','$unitId','$parentUnitId')";
				$resultInsertTree = mysql_query($queryInsertTree);

				$queryInsertUser = "INSERT INTO `qaos_users` (`page_modulecomponentid`,`user_id`,`qaos_unit_id`) VALUES ('$this->moduleComponentId','$userId','$unitId')";
				$resultInsertUser = mysql_query($queryInsertUser);
				displayinfo("The User was successfully added to the team.");
			}
		}
		return $name;
	}
	function generateTree($moduleComponentId,$score) {
		global $sourceFolder,$cmsFolder;
		global $urlRequestRoot;
		global $templateFolder;
		$imagesFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/images";
		$scriptsFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/scripts";
		$queryVersion = "SELECT `qaos_version` FROM `qaos_version` WHERE `page_modulecomponentid` = '$moduleComponentId'";
		$resultVersion = mysql_query($queryVersion);
		$row = mysql_fetch_row($resultVersion);
		$version = $row[0];
		$treeData .= "<h2>$version</h2>	<br />";
		$treeData .= '<div id="directorybrowser"><ul class="treeview" id="qaos">' .
				'<script type="text/javascript" language="javascript" src="'.$scriptsFolder.'/ddtree.js"></script>';
// for all those unit id's which have no parent.. like core team!
		$unitId = 0;
		$treeData .= $this->getNodeHtml($unitId,$score);
		$treeData .= '</ul></div>';
		$treeData .= <<<TREEDATA
			<style type="text/css">
			#qaos li{background-color:#F7F4FF;border:#88AAEA solid 1px;margin:10px;}
			</style>
			<script type="text/javascript" language="javascript">
			<!--
				siteMapLinks = document.getElementById('qaos').getElementsByTagName('a');
				for(i = 0; i < siteMapLinks.length; i++) {
					siteMapLinks[i].onclick = treeLinkClicked;
				}

				setupMenuDependencies("$imagesFolder", '');
				ddtreemenu.createTree("qaos", true, 5);
			-->
			</script>

TREEDATA;

		return $treeData;
	}
	function getNodeHtml($unitId,$score) {
		$htmlOut = '';
		$query = "SELECT `user_id`,us.`qaos_unit_id`,d.`qaos_designation_name`,tm.`qaos_team_name` FROM `qaos_users` us,`qaos_designations` d,`qaos_units` un,`qaos_tree` t,`qaos_teams` tm WHERE t.`qaos_parentunit_id`='$unitId' AND us.`qaos_unit_id` = t.`qaos_unit_id` AND un.`qaos_unit_id`=us.`qaos_unit_id` AND d.`qaos_designation_id` = un.`qaos_designation_id` AND tm.`qaos_team_id`=un.`qaos_team_id` ORDER BY d.`qaos_designation_name`,tm.`qaos_team_name`";
		$queryResult = mysql_query($query);
		$arrayUsers = array();
		$arrayUnits = array();
		$arr = array();
		$designation = array();
		$team = array();
		while($queryArray = mysql_fetch_assoc($queryResult))
		{
			//$arrayUsers[] = $queryArray['user_id'];
			//$arrayUnits[] = $queryArray['qaos_unit_id'];
			$designation[$queryArray['qaos_unit_id']] = $queryArray['qaos_designation_name'];
			$team[$queryArray['qaos_unit_id']] = $queryArray['qaos_team_name'];
			$arr[$queryArray['qaos_unit_id']][] = $queryArray['user_id'];
		}
		foreach($arr as $unitId=>$userId)
		{
			$htmlOut .= "<li><i>".$team[$unitId]." -</i> <b>".$designation[$unitId]."</b> : <br />";
			$userFullNameArray= array();
			foreach($userId as $i)
			{
				if($score){
				$userEmail = getUserEmail($i);
				$userFullName = getUserFullName($i);
				$userFullNameArray[] .=<<<USERNAME
				 	<a href="./+qaosadmin&subaction=viewscore&useremail=$userEmail">$userFullName</a> 
USERNAME;
				}
				else 
					$userFullNameArray[] .= getUserFullName($i);
			}
			$htmlOut .= join($userFullNameArray,", ");
			$childHtml = $this->getNodeHtml($unitId,$score);
			if($childHtml != "") {
				$htmlOut .= "<ul>" . $childHtml . '</ul>';
			}
			$htmlOut .= "</li>";
		}


		return $htmlOut;
/*
		if(getPermissions($userId, $pageId, $action, $module)) {
			$pageInfo = getPageInfo($pageId);
			$pagePath = $parentPath;
			if($pageInfo['page_name'] != '')
				$pagePath .= $pageInfo['page_name'] . '/';

			$htmlOut .= "<li><a href=\"$pagePath\">" . getPageTitle($pageId) . '</a>';

			$childrenQuery = 'SELECT `page_id` FROM `' . MYSQL_DATABASE_PREFIX  . 'pages` WHERE `page_parentid` <> `page_id` AND `page_parentid` = ' . $pageId;
			$childrenResult = mysql_query($childrenQuery);
			if(mysql_num_rows($childrenResult) > 0) {
				$htmlOut .= '<ul>';
			}
			while($childrenRow = mysql_fetch_row($childrenResult)) {
				$htmlOut .= $this->getNodeHtml($childrenRow[0], $userId, $module, $action, $pagePath);
			}
			if(mysql_num_rows($childrenResult) > 0)
				$htmlOut .= '</ul>';
			$htmlOut .= '</li>';
		}
		return $htmlOut;
	*/
	}
	
	/*createModule just creates an empty qaos module*/
	public function createModule($newId){
		$query = "INSERT INTO `qaos_version` (`page_modulecomponentid`) VALUES ('$newId')";
		$result = mysql_query($query) or die(mysql_error()."qaos.lib L:76");
	}
	
	public function actionScore($moduleComponentId){
		$moduleComponentId = $this->moduleComponentId;
		$userId = $this->userId;
		$userEmail = getUserEmail($userId);
		$designationId = $this->getDesignationId($userId);
		$designationName = $this->getDesignationNameFromDesignationId($designationId);
		$teamId = $this->getTeamId($userId);
		$htmlOut = '';
		if(isset($_GET['subaction'])){
			if($_GET['subaction']=='scoringUserDone'){
				if(isset($_POST['btnSubmitScore'])){
					$targetUserEmail =$_GET['targetUserEmail'];
					$userEmail = $_GET['userEmail'];
					$targetUserId = getUserIdFromEmail($targetUserEmail);
					$userId = getUserIdFromEmail($userEmail);
					$query = "INSERT INTO `qaos_scoring`(`page_modulecomponentid`,`user_id`,`targetuser_id`,`qaos_score1`,`qaos_score2`,`qaos_score3`,`qaos_score4`,`qaos_score5`,`qaos_reason1`,`qaos_reason2`,`qaos_reason3`,`qaos_reason4`,`qaos_reason5`) VALUES($moduleComponentId,$userId,$targetUserId,'".escape($_POST['qaos_score1'])."','".escape($_POST['qaos_score2'])."','".escape($_POST['qaos_score3'])."','".escape($_POST['qaos_score4'])."','".escape($_POST['qaos_score5'])."','".escape($_POST['qaos_reason1'])."','".escape($_POST['qaos_reason2'])."','".escape($_POST['qaos_reason3'])."','".escape($_POST['qaos_reason4'])."','".escape($_POST['qaos_reason5'])."')";
					if(mysql_query($query))
						displayinfo("Your scores have been stored.");
					else
						displayerror("There was some error in storing your scores");
				}
					
			}
			if($_GET['subaction']=='scoreUser'){				
				if(isset($_GET['userEmail'])){
					$targetUserEmail = $_GET['userEmail'];
					$targetUserId = getUserIdFromEmail($_GET['userEmail']);
					$targetUserFullName = getUserFullName($targetUserId);
					if($targetUserId == $userId){
						displayerror("You can not score yourself");
						return $htmlOut;
					}
					
					$query ="SELECT * FROM `qaos_scoring` WHERE user_id='$userId' AND targetuser_id='$targetUserId'";
					$result = mysql_query($query);
					if(mysql_affected_rows()>0){
						displayerror("You have already scored this person.");
						return $htmlOut;
					}
					
					$htmlOut = "";
					
					$htmlOut .= <<<SCOREUSER
					<div class="scoreuser">
						<form id="scoreuser" method="POST" onsubmit="return checkProfileForm(this)" action="./+score&userEmail=$userEmail&targetUserEmail=$targetUserEmail&subaction=scoringUserDone">
							<fieldset style="width:80%">
								<legend><b>Score, $targetUserFullName</b></legend>
								<table>
									<tr>
										<td>
											<b>Question No. 1:</b><br />
											Is the person regular and punctual in his/her work/meetings?<br />
										</td>
										
									</tr>
									<tr>
										<td>
											<br />
										</td>
									</tr>
									<tr>
										<td> Your Score:
										</td>	
										<td>
											<select name="qaos_score1" id="qaos_score1">
												<option value="1">1</option>
												<option value="2">2</option>
												<option value="3">3</option>
												<option value="4">4</option>
												<option value="5">5</option>
												<option value="6">6</option>
												<option value="7">7</option>
												<option value="8">8</option>
												<option value="9">9</option>
												<option value="10">10</option>
											</select>
										</td>
									</tr>
									<tr>
										<td> Your Reason/Comments:
										</td>
										<td>
											<textarea rows="3" columns="20" name="qaos_reason1" id="qaos_reason1" title="Enter your comments/reason here"></textarea>
										</td>	
									</tr>
									<tr>
										<td>
											<b>Question No. 2:</b><br />
											Is this person a team worker and co ordinates with others well?<br />
										</td>
										
									</tr>
									<tr>
										<td>
											<br />
										</td>
									</tr>
									<tr>
										<td> Your Score:
										</td>	
										<td>
											<select name="qaos_score2" id="qaos_score2">
												<option value="1">1</option>
												<option value="2">2</option>
												<option value="3">3</option>
												<option value="4">4</option>
												<option value="5">5</option>
												<option value="6">6</option>
												<option value="7">7</option>
												<option value="8">8</option>
												<option value="9">9</option>
												<option value="10">10</option>
											</select>
										</td>
									</tr>
									<tr>
										<td> Your Reason/Comments:
										</td>
										<td>
											<textarea rows="3" columns="20" name="qaos_reason2" id="qaos_reason2" title="Enter your comments/reason here"></textarea>
										</td>	
									</tr>
									<tr>
										<td>
											<b>Question No. 3:</b><br />
											How is his/her promptness in completing work?<br />
										</td>
										
									</tr>
									<tr>
										<td>
											<br />
										</td>
									</tr>
									<tr>
										<td> Your Score:
										</td>	
										<td>
											<select name="qaos_score3" id="qaos_score3">
												<option value="1">1</option>
												<option value="2">2</option>
												<option value="3">3</option>
												<option value="4">4</option>
												<option value="5">5</option>
												<option value="6">6</option>
												<option value="7">7</option>
												<option value="8">8</option>
												<option value="9">9</option>
												<option value="10">10</option>
											</select>
										</td>
									</tr>
									<tr>
										<td> Your Reason/Comments:
										</td>
										<td>
											<textarea rows="3" columns="20" name="qaos_reason3" id="qaos_reason3" title="Enter your comments/reason here"></textarea>
										</td>	
									</tr>
									<tr>
										<td>
											<b>Question No. 4:</b><br />
											How is his/her interest/enthusiasm/initiative in his/her work?<br />
										</td>
										
									</tr>
									<tr>
										<td>
											<br />
										</td>
									</tr>
									<tr>
										<td> Your Score:
										</td>	
										<td>
											<select name="qaos_score4" id="qaos_score4">
												<option value="1">1</option>
												<option value="2">2</option>
												<option value="3">3</option>
												<option value="4">4</option>
												<option value="5">5</option>
												<option value="6">6</option>
												<option value="7">7</option>
												<option value="8">8</option>
												<option value="9">9</option>
												<option value="10">10</option>
											</select>
										</td>
									</tr>
									<tr>
										<td> Your Reason/Comments:
										</td>
										<td>
											<textarea rows="3" columns="20" name="qaos_reason4" id="qaos_reason4" title="Enter your comments/reason here"></textarea>
										</td>	
									</tr>
									<tr>
										<td>
											<b>Question No. 5:</b><br />
											How is his/her potential managerial abilities?<br />
										</td>
										
									</tr>
									<tr>
										<td>
											<br />
										</td>
									</tr>
									<tr>
										<td> Your Score:
										</td>	
										<td>
											<select name="qaos_score5" id="qaos_score5">
												<option value="1">1</option>
												<option value="2">2</option>
												<option value="3">3</option>
												<option value="4">4</option>
												<option value="5">5</option>
												<option value="6">6</option>
												<option value="7">7</option>
												<option value="8">8</option>
												<option value="9">9</option>
												<option value="10">10</option>
											</select>
										</td>
									</tr>
									<tr>
										<td> Your Reason/Comments:
										</td>
										<td>
											<textarea rows="3" columns="20" name="qaos_reason5" id="qaos_reason5" title="Enter your comments/reason here"></textarea>
										</td>	
									</tr>
									<tr>
										<td>
											<input type="submit" name="btnSubmitScore" id="submitbutton" value="Save Scores">
										</td>
									</tr>
									
								</table>
							</fieldset>
						</form>
					</div>
SCOREUSER;
					return $htmlOut;
					}
				}
			}
		
		$query = "SELECT `user_id`,un.`qaos_unit_id`,d.`qaos_designation_name`,t.`qaos_team_name` FROM `qaos_users` u,`qaos_designations` d,`qaos_teams` t,`qaos_units` un WHERE un.`qaos_unit_id` = u.`qaos_unit_id` AND un.`qaos_team_id`='$teamId' AND d.`qaos_designation_id` = un.`qaos_designation_id` AND t.`qaos_team_id`=un.`qaos_team_id`" ;
		$queryResult = mysql_query($query);
		$arrayUsers = array();
		$arrayUnits = array();
		$arr = array();
		$designation = array();
		$team = array();
		while($queryArray = mysql_fetch_assoc($queryResult))
		{
			$designation[$queryArray['qaos_unit_id']] = $queryArray['qaos_designation_name'];
			$team[$queryArray['qaos_unit_id']] = $queryArray['qaos_team_name'];
			$arr[$queryArray['qaos_unit_id']][] = $queryArray['user_id'];
		}
		foreach($arr as $unitId=>$userId)
		{
			$htmlOut .= "<li><i>".$team[$unitId]." -</i> <b>".$designation[$unitId]."</b> : <br />";
			$userFullNameArray= array();
			foreach($userId as $i)
			{
				$htmlOut .= "<a href=\"./+score&subaction=scoreUser&userEmail=".getUserEmail($i)."\">";
				$htmlOut .= getUserFullName($i);
				$htmlOut .= "</a>";
				$htmlOut .= "<br />";
				//$userFullNameArray[] .= getUserFullName($i);
			}
			//$htmlOut .= join($userFullNameArray,", ");
			$htmlOut .= "</li>";
		}
		$htmlOut .= "<br /><br />";
		$teamName = $this->getTeamNameFromTeamId($teamId);
		if($teamName=="Core"){
			$unitId = $this->getUnitIdFromUserId($this->userId);
			$query = "SELECT us.user_id,tr.qaos_unit_id,d.qaos_designation_name, tm.qaos_team_name FROM `qaos_tree` tr JOIN qaos_units un ON (tr.qaos_unit_id = un.qaos_unit_id) JOIN qaos_teams tm ON (un.qaos_team_id = tm.qaos_team_id) JOIN qaos_designations d ON (un.qaos_designation_id = d.qaos_designation_id) JOIN qaos_users us ON (un.qaos_unit_id = us.qaos_unit_id) WHERE tr.qaos_parentunit_id='$unitId'";
			$queryResult = mysql_query($query);
			$arrayUsers = array();
			$arrayUnits = array();
			$arr = array();
			$designation = array();
			$team = array();
			while($queryArray = mysql_fetch_assoc($queryResult))
			{
				$designation[$queryArray['qaos_unit_id']] = $queryArray['qaos_designation_name'];
				$team[$queryArray['qaos_unit_id']] = $queryArray['qaos_team_name'];
				$arr[$queryArray['qaos_unit_id']][] = $queryArray['user_id'];
			}
			foreach($arr as $unitId=>$userId)
			{
				$htmlOut .= "<li><i>".$team[$unitId]." -</i> <b>".$designation[$unitId]."</b> : <br />";
				$userFullNameArray= array();
				foreach($userId as $i)
				{
					$htmlOut .= "<a href=\"./+score&subaction=scoreUser&userEmail=".getUserEmail($i)."\">";
					$htmlOut .= getUserFullName($i);
					$htmlOut .= "</a>";
					$htmlOut .= "<br />";
					//$userFullNameArray[] .= getUserFullName($i);
				}
				//$htmlOut .= join($userFullNameArray,", ");
				$htmlOut .= "</li>";
			}
		}
		if($teamName=="Qaos"){
			$unitId = $this->getUnitIdFromUserId($this->userId);
			$query = "SELECT us.`user_id`,u.`qaos_unit_id`,d.`qaos_designation_name`,t.`qaos_team_name` FROM `qaos_units` u,`qaos_designations` d,`qaos_users` us,`qaos_teams` t WHERE u.`qaos_unit_id`= us.`qaos_unit_id` AND u.`qaos_designation_id`= d.`qaos_designation_id` AND u.`qaos_team_id` = t.`qaos_team_id` AND u.`qaos_team_id` IN (SELECT t.`qaos_team_id` FROM `qaos_teams` t WHERE t.`qaos_representative_user_id1` = '$this->userId' OR t.`qaos_representative_user_id2` = '$this->userId')";
			$result = mysql_query($query);
			$arrayUsers = array();
			$arrayUnits = array();
			$arr = array();
			$designation = array();
			$team = array();
			while($queryArray = mysql_fetch_assoc($result))
			{
				$designation[$queryArray['qaos_unit_id']] = $queryArray['qaos_designation_name'];
				$team[$queryArray['qaos_unit_id']] = $queryArray['qaos_team_name'];
				
				$arr[$queryArray['qaos_unit_id']][] = $queryArray['user_id'];
			}
			foreach($arr as $unitId=>$userId)
			{
				$htmlOut .= "<li><i>".$team[$unitId]." -</i> <b>".$designation[$unitId]."</b> : <br />";
				$userFullNameArray= array();
				foreach($userId as $i)
				{
					$htmlOut .= "<a href=\"./+score&subaction=scoreUser&userEmail=".getUserEmail($i)."\">";
					$htmlOut .= getUserFullName($i);
					$htmlOut .= "</a>";
					$htmlOut .= "<br />";
					//$userFullNameArray[] .= getUserFullName($i);
				}
				//$htmlOut .= join($userFullNameArray,", ");
				$htmlOut .= "</li>";
			}	
			
		}
				
		return $htmlOut;
	}
	public function deleteModule($moduleComponentId){
		return true;
	}
	public function copyModule($moduleComponentId,$newId){
		return true;
	}
}
