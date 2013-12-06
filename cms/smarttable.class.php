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
 * @file smarttable.class.php
 * @brief Rendering of Jquery smarttable for tables to add features like pagination,sorting,searching,etc.
 * 
 * @author Abhishek Shrivastava <i.abhi27[at]gmail.com>
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 * @todo cms/modules/smarttable is not the correct place for the files as this is NOT a module. Those should be moved out of modules folder.
 * @warning In one page generation, only one time this class render() function should be called. Because the script added has function initSmartTable
 * which will otherwise have multiple definitions and report error. 
 * @todo Solution of warning : Generate some instance number to append to initSmartTable. A static variable can be maintained inside the class
 * and the value of it will be appended to the initSmartTable function and all the associated variables. That number should be passed to the user
 * because they may need to call the functions or edit the variables manually. 
 * 
 */

/**
 * @class smarttable
 * @brief Rendering of Jquery smarttable for tables to add features like pagination,sorting,searching,etc.
 * 
 */
class smarttable
{
	/**
	 * Includes the required CSS and Javascript files
	 *
	 * @param $code The code around which the CSS and JS file includes codes should be appended.
	 */
	function includeCode($code)
	{
		global $urlRequestRoot,$cmsFolder;
		$before=<<<CODE
		<style type="text/css" title="currentStyle">
				@import "$urlRequestRoot/$cmsFolder/modules/smarttable/css/demo_page.css";
				@import "$urlRequestRoot/$cmsFolder/modules/smarttable/css/demo_table_jui.css";
				@import "$urlRequestRoot/$cmsFolder/modules/smarttable/themes/smoothness/jquery-ui-1.7.2.custom.css";
		</style>
		<script type="text/javascript" language="javascript" src="$urlRequestRoot/$cmsFolder/modules/smarttable/js/jquery.dataTables.min.js"></script>
		<script type="text/javascript" charset="utf-8">
		function initSmartTable()
		{
CODE;
		$after=<<<CODE
		}
		</script>
CODE;
		return $before.$code.$after;
	}
	/**
	 * Main function of this class. Takes an array of table IDs and associated parameters array and generated the output HTML code.
	 *
	 * @param $tableidarray Array of Table IDs which should be converted into SmartTable
	 * @param $paramsarray 2D Array of parameters associated with each table ID in the $tableidarray
	 * @return The HTML code for the SmartTable
	 */
	function render($tableidarray,$paramsarray)
	{
		$ret="";
		foreach($tableidarray as $tableid)
		{
			$ret.=self::generate_js($tableid,$paramsarray[$tableid]);
		}
		return self::includeCode($ret);
	}
	/**
	 * Generated the JS for a single table ID and parameters
	 *
	 * @param $tableid Table ID of the table which should be converted into SmartTable
	 * @param $params Associated parameters
	 * @return The Jquery code for the SmartTable
	 */
	function generate_js($tableid,$params)
	{
		$config=array(
				'bJQueryUI' => 'true',
				'sPaginationType' => 'full_numbers',
				'bAutoWidth' => 'true'
			);
		if($params!=null)
		foreach($params as $param=>$value)
		{
			$config[$param]=$value;
		}
		$genparams = "";
		foreach($config as $param=>$value)
		{
			if($param[0]=='b')
				$genparams .= "\"$param\" :  $value, \n";
			else if($param[0]=='s')
				$genparams .= "\"$param\" : \"$value\", \n";
			else if($param[0]=='a')
				$genparams .= "\"$param\" : [ $value ], \n"; 
		}	
		
		$ret=<<<CODE
		
			$(document).ready(function() {
				oTable = $('#$tableid').dataTable({
					$genparams
				});
			} );
		
CODE;
		return $ret;
	}
}

?>
