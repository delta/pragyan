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
 

class sqlquery implements module {
	private $userId;
	private $moduleComponentId;
	private $action;

	public function getHtml($userId, $moduleComponentId, $action) {
		$this->userId = $userId;
		$this->moduleComponentId = $moduleComponentId;
		$this->action = $action;

		switch($action) {
			case 'view':
				return $this->actionView();
			case 'edit':
				return $this->actionEdit();
		}
	}

	public function actionView() {
		$sqlQueryQuery = 'SELECT `sqlquery_title`, `sqlquery_query` FROM `sqlquery_desc` WHERE `page_modulecomponentid` = \'' . $this->moduleComponentId."'";
		$sqlQueryResult = mysql_query($sqlQueryQuery);
		if(!$sqlQueryResult) {
			displayerror('Database error. An unknown error was encountered while trying to load page data.');
			return '';
		}
		$sqlQueryRow = mysql_fetch_row($sqlQueryResult);
		if(!$sqlQueryRow) {
			displayerror('Database error. Could not find data for the page requested.');
			return '';
		}

		$pageTitle = $sqlQueryRow[0];
		$pageQuery = $sqlQueryRow[1];

		$pageContent = "<h2>$pageTitle</h2><br />\n";
		return $pageContent . $this->generatePageData($pageQuery);
	}

	public function actionEdit() {
		$editPageContent = '';
		$paramSqlQuery = '';
		$paramPageTitle = '';
		$useParams = false;

		if(isset($_POST['btnSubmitQueryData'])) {
			if(!isset($_POST['pagetitle']) || !isset($_POST['sqlquery']))
				displayerror('Error. Incomplete form data.');
			$pageTitle = $_POST['pagetitle'];
			$sqlQuery = $_POST['sqlquery'];
			if($this->saveQueryEditForm($pageTitle, $sqlQuery))
				displayinfo('Changes saved successfully.');
		}
		elseif(isset($_POST['btnPreviewResults'])) {
			if(!isset($_POST['pagetitle']) || !isset($_POST['sqlquery']))
				displayerror('Error. Incomplete form data.');
			$pageTitle = $_POST['pagetitle'];
			$sqlQuery = $_POST['sqlquery'];
			$editPageContent = "<h2>$pageTitle (Preview)</h2><br />\n" . $this->generatePageData(stripslashes($sqlQuery)) . "<br />\n";

			$useParams = true;
			$paramSqlQuery = stripslashes($sqlQuery);
			$paramPageTitle = $pageTitle;
		}

		$editPageContent .= $this->getQueryEditForm($paramPageTitle, $paramSqlQuery, $useParams);
		
		$helptext = "";
		if(isset($_POST['btnListTables'])||( isset($_GET['subaction']) && $_GET['subaction']=="listalltables") )
		{
			
			$helptext.="<h2>Tables of Database ".MYSQL_DATABASE."</h2><br/><table id='sqlhelptable' name='sqlhelptable' class='display'><thead></tr><tr><th>Table Name</th><th>Columns Information</th><th>Rows Information</th></tr></thead><tbody>";
			$query="SHOW TABLES";
			$res=mysql_query($query);
			while($row=mysql_fetch_row($res))
			{
				$helptext .="<tr><td>{$row[0]}</td><td><a href='./+edit&subaction=tablecols&tablename={$row[0]}'>View Columns</a></td><td><a href='./+edit&subaction=tablerows&tablename={$row[0]}'>View Rows</a></td></tr>";
			}
			$helptext .="</tbody></table>";
		}
		if((isset($_POST['btnListRows']) && $_POST['tablename']!="") || ( isset($_GET['subaction']) && $_GET['subaction']=="tablerows") )
		{
			if(isset($_POST['tablename'])) $tablename=escape(safe_html($_POST['tablename']));
			else if(isset($_GET['tablename'])) $tablename=escape(safe_html($_GET['tablename']));
			else { displayerror("Table name missing"); return $editPageContent; }
			
			$query="SELECT * FROM '$tablename'";
			$res=mysql_query($query);
			$numfields=mysql_num_fields($res);
			$helptext .="<table id='sqlhelptable' name='sqlhelptable' class='display'><thead><tr><th colspan=".$numfields.">Rows of Table $tablename <br/><a href='./+edit&subaction=tablecols&tablename=$tablename'>View Columns</a>  <a href='./+edit&subaction=listalltables'>View All Tables</a></th></tr>";
			$helptext .="<tr>";
			
			for($i=0;$i<$numfields;$i++)
			{
				 $name = mysql_field_name($res, $i);
				    if (!$name) {
					displayerror("Field name could not be retrieved");
					break;
				    }
				 $helptext.="<th>$name</th>";
			}
			$helptext .="</tr></thead><tbody>";
			
			
			while($row=mysql_fetch_row($res))
			{
				$helptext .="<tr>";
				for($i=0;$i<$numfields;$i++)
					$helptext .="<td>{$row[$i]}</td>";
				$helptext .="</tr>";
			}
			$helptext .="</tbody></table>";
		}
		if((isset($_POST['btnListColumns']) && $_POST['tablename']!="") || ( isset($_GET['subaction']) && $_GET['subaction']=="tablecols"))
		{
			if(isset($_POST['tablename'])) $tablename=escape(safe_html($_POST['tablename']));
			else if(isset($_GET['tablename'])) $tablename=escape(safe_html($_GET['tablename']));
			else { displayerror("Table name missing"); return $editPageContent; }
			
			$helptext .="<table id='sqlhelptable' name='sqlhelptable' class='display'><thead><tr><th colspan=6>Column Information of Table $tablename <br/><a href='./+edit&subaction=tablerows&tablename=$tablename'>View Rows</a>  <a href='./+edit&subaction=listalltables'>View All Tables</a> </th></tr>";
			$helptext .="<tr><th>Column Name</th><th>Column Type</th><th>Maximum Length</th><th>Default Value</th><th>Not Null</th><th>Primary Key</th></tr></thead><tbody>";
			$query="SELECT * FROM '$tablename' LIMIT 1";
			$res=mysql_query($query);
			for($i=0;$i<mysql_num_fields($res);$i++)
			{
				 $meta = mysql_fetch_field($res, $i);
				    if (!$meta) {
					displayerror("Field information could not be retrieved");
					break;
				    }
				 $helptext.="<tr><td>{$meta->name}</td><td>{$meta->type}</td><td>{$meta->max_length}</td><td>{$meta->def}</td><td>{$meta->not_null}</td><td>{$meta->primary_key}</td></tr>";
			}
			$helptext .="</tbody></table>";
		}
		global $urlRequestRoot,$cmsFolder,$STARTSCRIPTS;
		$smarttable = smarttable::render(array('sqlhelptable'),null);
		$STARTSCRIPTS .= "initSmartTable();";

		global $ICONS;
		if($helptext!="") $helptext="<fieldset><legend>{$ICONS['Database Information']['small']}Database Information</legend>$smarttable $helptext</fieldset>";
		return $helptext.$editPageContent;
	}

	private function getQueryEditForm($pageTitle = '', $sqlQuery = '', $useParams = false) {
		if(!$useParams) {
			$defaultValueQuery = 'SELECT `sqlquery_title`, `sqlquery_query` FROM `sqlquery_desc` WHERE `page_modulecomponentid` = \'' . $this->moduleComponentId."'";
			$defaultValueResult = mysql_query($defaultValueQuery);
			if(!$defaultValueResult) {
				displayerror('Error. Could not retrieve data for the page requested.');
				return '';
			}
			$defaultValueRow = mysql_fetch_row($defaultValueResult);
			if(!$defaultValueRow) {
				displayerror('Error. Could not retrieve data for the page requested.');
				return '';
			}
			$pageTitle = $defaultValueRow[0];
			$sqlQuery = $defaultValueRow[1];
		}
		global $ICONS;
		$dbname=MYSQL_DATABASE;
		$dbprefix=MYSQL_DATABASE_PREFIX;
		$queryEditForm = <<<QUERYEDITFORM
		<fieldset><legend>{$ICONS['SQL Query']['small']}Custom SQL Query</legend>
		<form method="POST" action="./+edit">
			<table>
				<tr><td>Page Title:</td><td><input id="pagetitle" name="pagetitle" type="text" value="$pageTitle" /></td></tr>
				<tr><td>SQL Query:</td><td><textarea id="sqlquery" name="sqlquery" rows="8" cols="50">$sqlQuery</textarea></td></tr>
			</table>
			<input type="submit" name="btnSubmitQueryData" value="Save Changes" />
			<input type="submit" name="btnPreviewResults" value="Preview Result Page" />
			<br/>Need help ? Use the Database Information form below.
		</form>
		</fieldset>
		<fieldset>
		<legend>{$ICONS['Database Information']['small']} Database Information</legend>
		<table style="width:100%">
		<form method="POST" action="./+edit" >
		<tr><td>Database Name</td><td>$dbname</td></tr>
		<tr><td>Tables Prefix</td><td>$dbprefix</td></tr>
		<tr><td colspan="2"><input style="width:100%" type="submit" name="btnListTables" value="List All Tables"/></td></tr>
		<tr><td>Enter a Table Name </td><td><input type="text" name="tablename"/></td>
		<tr><td><input type="submit" name="btnListRows" value="View Rows Information"/></td><td><input type="submit" name="btnListColumns" value="View Columns Information"/></td></tr>
		
		</table>
		</form>
		</fieldset>
QUERYEDITFORM;
		return $queryEditForm;
	}

	private function generatePageData($sqlQuery) {
		$sqlQuery = $sqlQuery;
		$result = mysql_query($sqlQuery);

		if(!$result) {
			return 'Error. The query used to generate this page is invalid. <a href="./+edit">Click here</a> to change the default query.<br />';
		}

		$pageContent = '<table>';

		$pageContent .= "<tr>\n";
		$fieldCount = mysql_num_fields($result);
		for($i = 0; $i < $fieldCount; $i++) {
			$pageContent .= "<th>" . mysql_field_name($result, $i) . "</th>";
		}
		$pageContent .= "</tr>\n";

		while($resultrow = mysql_fetch_row($result))
			$pageContent .= "<tr><td>" . implode('</td><td>', $resultrow) . "</td></tr>\n";
		$pageContent .= "</table>\n";

		return $pageContent;
	}

	private function saveQueryEditForm($pageTitle, $sqlQuery) {
		$updateQuery = "UPDATE `sqlquery_desc` SET `sqlquery_title` = '$pageTitle', `sqlquery_query` = '$sqlQuery' WHERE `page_modulecomponentid` = '{$this->moduleComponentId}'";
		$updateResult = mysql_query($updateQuery);
		if(!$updateResult) {
			displayerror('SQL Error. Could not update database settings.');
			return false;
		}
		return true;
	}

	public function deleteModule($moduleComponentId) {
		return true;
	}

        public function copyModule($moduleComponentId,$newId) {
                return true;
        }

        public function createModule($compId) {

                $insertQuery = "INSERT INTO `sqlquery_desc`(`page_modulecomponentid`, `sqlquery_title`, `sqlquery_query`) VALUES('$compId', 'New Query', 'SELECT * FROM `mytable` WHERE 1')";
                $insertResult = mysql_query($insertQuery);
        }

}

