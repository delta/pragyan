<?php
/*
 * Created on Oct 17, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
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
	}
	public function actionView(){

		return $this->generateTree($this->moduleComponentId);
	}
	public function actionEdit($moduleComponentId){
	global $urlRequestRoot;
	global $sourceFolder;
	global $templateFolder;
	$scriptsFolder = "$urlRequestRoot/$sourceFolder/$templateFolder/common/scripts";
	$imagesFolder = "$urlRequestRoot/$sourceFolder/$templateFolder/common/images";

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
					$query = "UPDATE `qaos_version` SET `qaos_version` = '$_POST[qaos_version]' WHERE `page_modulecomponentid` = $moduleComponentId";
					$result = mysql_query($query);
					if(mysql_query($query))
						displayinfo("The version has been successfully updated.");
					else
						displayinfo("There was some error while updating the version. Please check your query once.");
					}
			}
			elseif($_GET['subaction']=='addteammember'){
				if(isset($_POST['btnAddTeamMember'])){
					$email = $_POST['useremail'];
					$designation = $_POST['userdesignation'];
					$team = $_POST['userteam'];
					$parentTeam =$_POST['userparentteam'];
					$parentDesignation = $_POST['userparentdesignation'];
					$name = $this->addTeamMember($email,$designation,$team,$parentTeam,$parentDesignation);

				}
			}
			elseif($_GET['subaction'] == 'getsuggestions' && isset($_GET['forwhat'])) {
				echo $this->getSuggestions($_GET['forwhat'], $_GET['suggestiontype']);
				exit();
			}
		}


		$queryVersion = "SELECT `qaos_version` FROM `qaos_version` WHERE `page_modulecomponentid` = $moduleComponentId";
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
		$queryTeam = "SELECT * FROM `qaos_teams` WHERE `page_modulecomponentid`=$moduleComponentId ORDER BY `qaos_team_name`";
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
							-->
							</script>

							</table>
					</fieldset>
				</form>
			</div>
		</div>

ADDPERSON;
// if the user team is core, then display the parent team name and designation field, otherwise disable it!

		if($userTeamId == 1){
			$html .=<<<DISABLEPARENTFIELD
				<script language="javascript" type="text/javascript">
					document.getElementById("userParentTeam").disabled=false;
					document.getElementById("userParentDesignation").disabled=false;
				</script>
DISABLEPARENTFIELD;
		}
		else
			$html .=<<<DISABLEPARENTFIELD
				<script language="javascript" type="text/javascript">
						document.getElementById("userParentTeam").disabled=true;
						document.getElementById("userParentDesignation").disabled=true;
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
		$query = "SELECT `qaos_designation_priority` FROM `qaos_designations` WHERE `qaos_designation_id`=$designationId";
		$result = mysql_query($query);
		$row = mysql_fetch_assoc($result);
		$designationPriority = $row['qaos_designation_priority'];
		return $designationPriority;
	}
	function getUnitId($teamId,$designationId){
		$query = "SELECT `qaos_unit_id` FROM `qaos_units` WHERE `qaos_team_id`=$teamId AND `qaos_designation_id`=$designationId";
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
	function addTeamMember($emailName,$designation,$team,$newparentTeam,$newparentDesignation){
		$input = explode(" - ",$emailName);
		$email = $input[0];
		$name = $input[1];
		$userId = getUserIdFromEmail($email);
		$parentUserId = $this->userId;
/**		get the unit id, the team id and the designation id and
 * 		the designation priority of the parent.
 */
		$parentTeamId = $this->getTeamId($parentUserId);
		$parentDesignationId = $this->getDesignationId($parentUserId);
		$parentUnitId = $this->getUnitId($parentTeamId,$parentDesignationId);
		$parentDesignationPriority = $this->getDesignationPriority($parentDesignationId);
/**		get the team id of the user.
 *
 */
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
		$query = "SELECT `user_id`,`qaos_unit_id` FROM `qaos_users` WHERE `user_id`=$userId";
		$result = mysql_query($query);
		if($row = mysql_fetch_assoc($result)){
			$unitId = $row['qaos_unit_id'];
			$queryTeam = "SELECT `qaos_teams`.`qaos_team_name` FROM `qaos_teams`, `qaos_units` WHERE `qaos_teams`.`qaos_team_id`=`qaos_units`.`qaos_team_id` AND `qaos_units`.`qaos_unit_id`=$unitId";
			$resultTeam = mysql_query($queryTeam);
			$row = mysql_fetch_assoc($resultTeam);
			var_dump($row);
			$teamName = $row['qaos_team_name'];
			displayerror("Sorry, the user can not be added. The person already exists in the ".$teamName." team.");
		}
		else {
// Check whether the unit already exists in the database or not. If not add a new unit, and then add the user otherwise directly add the user
			$queryUnits = "SELECT `qaos_unit_id` FROM `qaos_units` WHERE `qaos_team_id`=$teamId AND `qaos_designation_id`=$designationId";
			$resultUnits = mysql_query($queryUnits);
//if the unit exist, just add the user to the qaos_user table.
			if($rowUnits = mysql_fetch_assoc($resultUnits)){
				$unitId = $rowUnits['qaos_unit_id'];
				$queryUsers = "INSERT INTO `qaos_users` (`page_modulecomponentid`,`user_id`,`qaos_unit_id`) VALUES ($this->moduleComponentId,$userId,$unitId)";
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
				$queryInsertUnit = "INSERT INTO `qaos_units` (`page_modulecomponentid`,`qaos_unit_id`,`qaos_team_id`,`qaos_designation_id`) VALUES ($this->moduleComponentId,$unitId,$teamId,$designationId)";
				$resultInsertUnit = mysql_query($queryInsertUnit);
				//echo $parentUnitId."two";
				$queryInsertTree = "INSERT INTO `qaos_tree` (`page_modulecomponentid`,`qaos_unit_id`,`qaos_parentunit_id`) VALUES ($this->moduleComponentId,$unitId,$parentUnitId)";
				$resultInsertTree = mysql_query($queryInsertTree);

				$queryInsertUser = "INSERT INTO `qaos_users` (`page_modulecomponentid`,`user_id`,`qaos_unit_id`) VALUES ($this->moduleComponentId,$userId,$unitId)";
				$resultInsertUser = mysql_query($queryInsertUser);
				displayinfo("The User was successfully added to the team.");
			}
		}
		return $name;
	}
	function generateTree($moduleComponentId) {
		global $sourceFolder;
		global $urlRequestRoot;
		global $templateFolder;
		$imagesFolder = "$urlRequestRoot/$sourceFolder/$templateFolder/common/images";
		$scriptsFolder = "$urlRequestRoot/$sourceFolder/$templateFolder/common/scripts";
		$queryVersion = "SELECT `qaos_version` FROM `qaos_version` WHERE `page_modulecomponentid` = $moduleComponentId";
		$resultVersion = mysql_query($queryVersion);
		$row = mysql_fetch_row($resultVersion);
		$version = $row[0];
		$treeData .= "<h2>$version</h2>	<br />";
		$treeData .= '<div id="directorybrowser"><ul class="treeview" id="qaos">' .
				'<script type="text/javascript" language="javascript" src="'.$scriptsFolder.'/ddtree.js"></script>';
// for all those unit id's which have no parent.. like core team!
		$unitId = 0;
		$treeData .= $this->getNodeHtml($unitId);
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
	function getNodeHtml($unitId) {
		$htmlOut = '';
		$query = "SELECT `user_id`,us.`qaos_unit_id`,d.`qaos_designation_name`,tm.`qaos_team_name` FROM `qaos_users` us,`qaos_designations` d,`qaos_units` un,`qaos_tree` t,`qaos_teams` tm WHERE t.`qaos_parentunit_id`=$unitId AND us.`qaos_unit_id` = t.`qaos_unit_id` AND un.`qaos_unit_id`=us.`qaos_unit_id` AND d.`qaos_designation_id` = un.`qaos_designation_id` AND tm.`qaos_team_id`=un.`qaos_team_id` ORDER BY d.`qaos_designation_name`,tm.`qaos_team_name`";
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
				$userFullNameArray[] .= getUserFullName($i);
			}
			$htmlOut .= join($userFullNameArray,", ");
			$childHtml = $this->getNodeHtml($unitId);
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

	public function createModule(& $moduleComponentId){
		$query = "SELECT MAX(page_modulecomponentid) as MAX FROM `qaos_version` ";
		$result = mysql_query($query) or die(mysql_error() . "qaos.lib L:31");
		$row = mysql_fetch_assoc($result);
		$compId = $row['MAX'] + 1;

		$query = "INSERT INTO `qaos_version` (`page_modulecomponentid`) VALUES ('$compId')";
		$result = mysql_query($query) or die(mysql_error()."article.lib L:76");
		if (mysql_affected_rows()) {
			$moduleComponentId = $compId;
			return true;
		} else
			return false;
	}/*createModule just creates an empty qaos module*/
	public function actionScore($moduleComponentId){
		$moduleComponentId = $this->moduleComponentId;
		$userId = $this->userId;
		$designationId = $this->getDesignationId($userId);
		$designationName = $this->getDesignationNameFromDesignationId($designationId);
		$teamId = $this->getTeamId($userId);
		$htmlOut = '';
		if(isset($_GET['subaction'])){
			if($_GET['subaction']=='scoreUser'){
				if(isset($_GET['userEmail'])){
					$targetUserId = getUserIdFromEmail($_GET['user']);
					$targetUserFullName = getUserFullName($userId);
					$htmlOut = "";
					$htmlOut .= <<<SCOREUSER
					<div class="scoreuser">
						<form id="scoreuser" method="POST" onsubmit="return checkProfileForm(this)" action="./+score&subaction=scoringuserdone">
							<fieldset style="width:80%">
								<legend><b>Score, $targetUserFullName</b></legend>
								<table>
									<tr>
										<td>
											<b>Question No. 1:</b><br />
											How much did u see of this person during pragyan?
										</td>
										
										<td>
											<input name="qaos_score1" id="qaos_score1" type="text">
										</td>
									</tr>
									<tr>													
										<td>
											<b>Question No. 2:</b><br />
											How much did u see of this person during pragyan?
										</td>
										<td>
											<input name="qaos_score2" id="qaos_score2" type="text">
										</td>
									</tr>
									<tr>
										<td>
											<b>Question No. 3:</b><br />
											How much did u see of this person during pragyan?
										</td>
										<td>
											<input name="qaos_score3" id="qaos_score3" type="text">
										</td>
									</tr>
									<tr>
										<td>
											<b>Question No. 4:</b><br />
											How much did u see of this person during pragyan?
										</td>
										<td>
											<input name="qaos_score4" id="qaos_score4" type="text">
										</td>
									</tr>
									<tr>
										<td>
											<b>Question No. 5:</b><br />
											How much did u see of this person during pragyan?
										</td>
										<td>
											<input name="qaos_score5" id="qaos_score5" type="text">
										</td>
										<td><input type="submit" name="btnSubmitVersion" id="submitbutton" value="Save Version"></td>
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
		
		$query = "SELECT `user_id`,un.`qaos_unit_id`,d.`qaos_designation_name`,t.`qaos_team_name` FROM `qaos_users` u,`qaos_designations` d,`qaos_teams` t,`qaos_units` un WHERE un.`qaos_unit_id` = u.`qaos_unit_id` AND un.`qaos_team_id`=$teamId AND d.`qaos_designation_id` = un.`qaos_designation_id` AND t.`qaos_team_id`=un.`qaos_team_id`";
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
		
		//if()
		return $htmlOut;
	}
	public function deleteModule($moduleComponentId){
	}
	public function copyModule($moduleComponentId){
	}
}
?>