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
		$sqlQueryResult = mysqli_query($GLOBALS["___mysqli_ston"], $sqlQueryQuery);
		if(!$sqlQueryResult) {
			displayerror('Database error. An unknown error was encountered while trying to load page data.');
			return '';
		}
		$sqlQueryRow = mysqli_fetch_row($sqlQueryResult);
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
			$res=mysqli_query($GLOBALS["___mysqli_ston"], $query);
			while($row=mysqli_fetch_row($res))
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
			$res=mysqli_query($GLOBALS["___mysqli_ston"], $query);
			$numfields=(($___mysqli_tmp = mysqli_num_fields($res)) ? $___mysqli_tmp : false);
			$helptext .="<table id='sqlhelptable' name='sqlhelptable' class='display'><thead><tr><th colspan=".$numfields.">Rows of Table $tablename <br/><a href='./+edit&subaction=tablecols&tablename=$tablename'>View Columns</a>  <a href='./+edit&subaction=listalltables'>View All Tables</a></th></tr>";
			$helptext .="<tr>";
			
			for($i=0;$i<$numfields;$i++)
			{
				 $name = ((($___mysqli_tmp = mysqli_fetch_field_direct($res,  $i)->name) && (!is_null($___mysqli_tmp))) ? $___mysqli_tmp : false);
				    if (!$name) {
					displayerror("Field name could not be retrieved");
					break;
				    }
				 $helptext.="<th>$name</th>";
			}
			$helptext .="</tr></thead><tbody>";
			
			
			while($row=mysqli_fetch_row($res))
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
			$res=mysqli_query($GLOBALS["___mysqli_ston"], $query);
			for($i=0;$i<(($___mysqli_tmp = mysqli_num_fields($res)) ? $___mysqli_tmp : false);$i++)
			{
				 $meta = (((($___mysqli_tmp = mysqli_fetch_field_direct($res, 0)) && is_object($___mysqli_tmp)) ? ( (!is_null($___mysqli_tmp->primary_key = ($___mysqli_tmp->flags & MYSQLI_PRI_KEY_FLAG) ? 1 : 0)) && (!is_null($___mysqli_tmp->multiple_key = ($___mysqli_tmp->flags & MYSQLI_MULTIPLE_KEY_FLAG) ? 1 : 0)) && (!is_null($___mysqli_tmp->unique_key = ($___mysqli_tmp->flags & MYSQLI_UNIQUE_KEY_FLAG) ? 1 : 0)) && (!is_null($___mysqli_tmp->numeric = (int)(($___mysqli_tmp->type <= MYSQLI_TYPE_INT24) || ($___mysqli_tmp->type == MYSQLI_TYPE_YEAR) || ((defined("MYSQLI_TYPE_NEWDECIMAL")) ? ($___mysqli_tmp->type == MYSQLI_TYPE_NEWDECIMAL) : 0)))) && (!is_null($___mysqli_tmp->blob = (int)in_array($___mysqli_tmp->type, array(MYSQLI_TYPE_TINY_BLOB, MYSQLI_TYPE_BLOB, MYSQLI_TYPE_MEDIUM_BLOB, MYSQLI_TYPE_LONG_BLOB)))) && (!is_null($___mysqli_tmp->unsigned = ($___mysqli_tmp->flags & MYSQLI_UNSIGNED_FLAG) ? 1 : 0)) && (!is_null($___mysqli_tmp->zerofill = ($___mysqli_tmp->flags & MYSQLI_ZEROFILL_FLAG) ? 1 : 0)) && (!is_null($___mysqli_type = $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = (($___mysqli_type == MYSQLI_TYPE_STRING) || ($___mysqli_type == MYSQLI_TYPE_VAR_STRING)) ? "type" : "")) &&(!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && in_array($___mysqli_type, array(MYSQLI_TYPE_TINY, MYSQLI_TYPE_SHORT, MYSQLI_TYPE_LONG, MYSQLI_TYPE_LONGLONG, MYSQLI_TYPE_INT24))) ? "int" : $___mysqli_tmp->type)) &&(!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && in_array($___mysqli_type, array(MYSQLI_TYPE_FLOAT, MYSQLI_TYPE_DOUBLE, MYSQLI_TYPE_DECIMAL, ((defined("MYSQLI_TYPE_NEWDECIMAL")) ? constant("MYSQLI_TYPE_NEWDECIMAL") : -1)))) ? "real" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && $___mysqli_type == MYSQLI_TYPE_TIMESTAMP) ? "timestamp" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && $___mysqli_type == MYSQLI_TYPE_YEAR) ? "year" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && (($___mysqli_type == MYSQLI_TYPE_DATE) || ($___mysqli_type == MYSQLI_TYPE_NEWDATE))) ? "date " : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && $___mysqli_type == MYSQLI_TYPE_TIME) ? "time" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && $___mysqli_type == MYSQLI_TYPE_SET) ? "set" : $___mysqli_tmp->type)) &&(!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && $___mysqli_type == MYSQLI_TYPE_ENUM) ? "enum" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && $___mysqli_type == MYSQLI_TYPE_GEOMETRY) ? "geometry" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && $___mysqli_type == MYSQLI_TYPE_DATETIME) ? "datetime" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && (in_array($___mysqli_type, array(MYSQLI_TYPE_TINY_BLOB, MYSQLI_TYPE_BLOB, MYSQLI_TYPE_MEDIUM_BLOB, MYSQLI_TYPE_LONG_BLOB)))) ? "blob" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type && $___mysqli_type == MYSQLI_TYPE_NULL) ? "null" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->type = ("" == $___mysqli_tmp->type) ? "unknown" : $___mysqli_tmp->type)) && (!is_null($___mysqli_tmp->not_null = ($___mysqli_tmp->flags & MYSQLI_NOT_NULL_FLAG) ? 1 : 0)) ) : false ) ? $___mysqli_tmp : false);
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
			$defaultValueResult = mysqli_query($GLOBALS["___mysqli_ston"], $defaultValueQuery);
			if(!$defaultValueResult) {
				displayerror('Error. Could not retrieve data for the page requested.');
				return '';
			}
			$defaultValueRow = mysqli_fetch_row($defaultValueResult);
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
		$result = mysqli_query($GLOBALS["___mysqli_ston"], $sqlQuery);

		if(!$result) {
			return 'Error. The query used to generate this page is invalid. <a href="./+edit">Click here</a> to change the default query.<br />';
		}

		$pageContent = '<table>';

		$pageContent .= "<tr>\n";
		$fieldCount = (($___mysqli_tmp = mysqli_num_fields($result)) ? $___mysqli_tmp : false);
		for($i = 0; $i < $fieldCount; $i++) {
			$pageContent .= "<th>" . ((($___mysqli_tmp = mysqli_fetch_field_direct($result,  $i)->name) && (!is_null($___mysqli_tmp))) ? $___mysqli_tmp : false) . "</th>";
		}
		$pageContent .= "</tr>\n";

		while($resultrow = mysqli_fetch_row($result))
			$pageContent .= "<tr><td>" . implode('</td><td>', $resultrow) . "</td></tr>\n";
		$pageContent .= "</table>\n";

		return $pageContent;
	}

	private function saveQueryEditForm($pageTitle, $sqlQuery) {
		$updateQuery = "UPDATE `sqlquery_desc` SET `sqlquery_title` = '$pageTitle', `sqlquery_query` = '$sqlQuery' WHERE `page_modulecomponentid` = '{$this->moduleComponentId}'";
		$updateResult = mysqli_query($GLOBALS["___mysqli_ston"], $updateQuery);
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
                $insertResult = mysqli_query($GLOBALS["___mysqli_ston"], $insertQuery);
        }
	public function moduleAdmin(){
		return "This is the SQL Query module administration page. Options coming up soon!!!";
	}
	

}

