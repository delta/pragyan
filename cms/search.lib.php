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
 * @author Chakradar Raju
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
 
global $cmsFolder,$sourceFolder;
require_once("$sourceFolder/modules/search/settings/conf.php");
require_once("$sourceFolder/modules/search/include/commonfuncs.php");
require_once("$sourceFolder/modules/search/include/categoryfuncs.php");
require_once("$sourceFolder/modules/search/include/searchfuncs.php");

function saveToLog ($query, $elapsed, $results) {
	global $sph_mysql_table_prefix;
	if ($results == '')
		$results = 0;
	$query = "insert into ".$sph_mysql_table_prefix."query_log (query, time, elapsed, results) values ('$query', now(), '$elapsed', '$results')";
	if(!mysql_query($query))
		displayerror(mysql_error());
}

function getmicrotime(){
	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);
}

function getSearchBox(){
	$CMS_TITLE = CMS_TITLE;
	$lastquery="";
	if($_GET['query']!="") $lastquery=safe_html($_GET['query']);
	if($_POST['query']!="") $lastquery=safe_html($_POST['query']);

	global $cmsFolder,$urlRequestRoot,$ICONS;
	$searchbox=<<<SEARCH
	<style type="text/css">
		table.searchBox{border:1px solid #113377}
		#result_report {
			text-align:center ;
			padding:3px;
			background-color:#e5ecf9; 
			font-weight: bold;
			margin-bottom:14px;
			margin-right: 10px;
		}
	</style>

	<fieldset>
	<legend>{$ICONS['Search']['small']}$CMS_TITLE Search</legend>
	
	<center>
	<form action="./+search" method="POST">
	<table cellspacing="1" cellpadding="5" class="searchBox">
		<tr>
			<td align="center">
				<table>
					<tr>
						<td><div align="left"><input type="text" size="40" id="query" name="query" value="$lastquery" /></td>
						<td><input type="submit" value="Search"/></td>
					</tr>
				</table>
				
			</td>
		</tr>
	</table>
	Powered by <a href="http://www.sphider.eu/" target="_blank"><img src="$urlRequestRoot/$cmsFolder/templates/common/images/sphider-logo.png" border="0" style="vertical-align: middle" alt="Sphider"></a><br/><br/>
	</form>
	</center>
SEARCH;
	return $searchbox;
			
}
function getSearchResultString($query) {
	$search_results = get_search_results("$query","","","and","","");

	extract($search_results);
	global $urlRequestRoot,$cmsFolder;
	$resultHTML = "<link rel='stylesheet' media='print' type='text/css' href=\"$urlRequestRoot/$cmsFolder/templates/common/search.css\" />";
	if ($search_results['did_you_mean']){
		$dym = quote_replace(addmarks($search_results['did_you_mean']));
		$resultHTML .= <<<DIDYOUMEAN
			<div id="did_you_mean">
				Did You Mean: <a href="./+search&query=$dym&search=1">{$search_results['did_you_mean_b']}</a>
			</div>
DIDYOUMEAN;
	}

	if ($search_results['ignore_words']) {
		$resultHTML .= '<div id="common_report">';
		$ignored = '';
		while ($thisword=each($ignore_words)) {
			$ignored .= " ".$thisword[1];
		}
		$resultHTML .= '</div>';
	}

	if ($search_results['total_results']==0) {
		$resultHTML .= '<div id ="result_report">';
		$resultHTML .= str_replace ('%query', $ent_query, "Sorry! No matches found.");
		$resultHTML .= '</div>';
	}

	if ($total_results != 0 && $from <= $to) {
		$resultHTML .= '<div id ="result_report">';
		$res = 'Results';
		$res = str_replace ('%from', $from, $res);
		$res = str_replace ('%to', $to, $res);
		$res = str_replace ('%all', $total_results, $res);
		$matchword = "matches";
		if ($total_results== 1)
			$matchword= "match";
		else
			$matchword= "matches";

		$res = str_replace ('%matchword', $matchword, $res);
		$res = str_replace ('%secs', $time, $res);
		$resultHTML .= $res;
		$resultHTML .= '</div>';
	}

	if (isset($qry_results)) {
		$resultHTML .= '<div id="results">';

		foreach ($qry_results as $_key => $_row){
			$last_domain = $domain_name;
			extract($_row);
			if ($sph_show_query_scores == 0)
				$weight = '';
			else
				$weight = "[$weight%]"; 
			if ($domain_name == $last_domain && $sph_merge_site_results == 1 && $domain == "") {
				$resultHTML .= '<div class="idented">';
			}

			$resultHTML .= "<b>$num.</b> $weight \n";
			$resultHTML .= "<a href=\"$url\" class=\"sph_title\">" . ($title?$title:'Untitled') . "</a><br/>\n";
			$resultHTML .= "<div class=\"description\">$fulltxt</div>\n";
			$resultHTML .= "<div class=\"url\">$url2 - $page_size</div>\n";

			if ($domain_name == $last_domain && $sph_merge_site_results == 1 && $domain == "") {
				$q = quote_replace(addmarks($query));
				$resultHTML .= "[ <a href=\"./+search&query=$q&search=1&results=$results_per_page&domain=$domain_name\">More results from $domain_name</a> ]";
				$resultHTML .= "</div class=\"idented\">\n";
			}
			$resultHTML .= "<br />\n";
		}
		$resultHTML .= '</div>';
	}

	if (isset($other_pages)) {
		if ($adv==1) {
			$adv_qry = "&adv=1";
		}
		if ($type != "") {
			$type_qry = "&type=$type";
		}

		$resultHTML .= "<div id=\"other_pages\">\nResult page: ";
		if ($start >1) {
			$q = quote_replace(addmarks($query));
			$resultHTML .= "<a href=\"./+search&query=$q&start=$prev&search=1&results={$results_per_page}{$type_qry}{$adv_qry}&domain=$domain\">Previous</a>";
		}

		foreach ($other_pages as $page_num) {
			if ($page_num !=$start) {
				$q = quote_replace(addmarks($query));
				$resultHTML .= "<a href=\"./+search&query=$q&start=$page_num&search=1&results={$results_per_page}{$type_qry}{$adv_qry}&domain=$domain\">$page_num</a>";
			}
			else {
				$resultHTML .= "<b>$page_num</b>";
			}
		}

		if ($next <= $pages) {
			$q = quote_replace(addmarks($query));
			$resultHTML .= "<a href=\"./+search&query=$q&start=$next&search=1&results={$results_per_page}{$type_qry}{$adv_qry}&domain=$domain\">Next</a>";
		}

		$resultHTML .= '</div>';
	}
	return $resultHTML;
}
