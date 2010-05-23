<?php
/*******************************************
* Sphider Version 1.3.x
* This program is licensed under the GNU GPL.
* By Ando Saabas          ando(a t)cs.ioc.ee
********************************************/
if (isset($_GET['query']))
	$query = $_GET['query'];
if (isset($_GET['search']))
	$search = $_GET['search'];
if (isset($_GET['domain']))
	$domain = $_GET['domain'];
if (isset($_GET['type']))
	$type = $_GET['type'];
if (isset($_GET['catid']))
	$catid = $_GET['catid'];
if (isset($_GET['category']))
	$category = $_GET['category'];
if (isset($_GET['results']))
	$results = $_GET['results'];
if (isset($_GET['start']))
	$start = $_GET['start'];
if (isset($_GET['adv']))
	$adv = $_GET['adv'];

global $urlRequestRoot, $sourceFolder, $moduleFolder, $templateFolder;
global $sph_mysql_table_prefix, $sph_messages;
global $sph_show_query_scores, $sph_merge_site_results;

$searchModuleFolder = "$sourceFolder/$moduleFolder/search";
$include_dir = "$searchModuleFolder/include";
include ("$include_dir/commonfuncs.php");
$include_dir = "$searchModuleFolder/include";
$template_dir = "$searchModuleFolder/templates";
$settings_dir = "$searchModuleFolder/settings";
$language_dir = "$searchModuleFolder/languages";

require_once("$include_dir/searchfuncs.php");
require_once("$include_dir/categoryfuncs.php");

if ($type != "or" && $type != "and" && $type != "phrase") $type = "and";
if (preg_match("/[^a-z0-9-.]+/", $domain)) $domain="";
if ($results != "") $results_per_page = $results;
if (get_magic_quotes_gpc()==1) $query = stripslashes($query);
if (!is_numeric($catid)) $catid = "";
if (!is_numeric($category)) $category = "";
if ($catid && is_numeric($catid))
	$tpl_['category'] = sql_fetch_all('SELECT category FROM '.$sph_mysql_table_prefix.'categories WHERE category_id='.(int)$_REQUEST['catid']);

$count_level0 = sql_fetch_all('SELECT count(*) FROM '.$sph_mysql_table_prefix.'categories WHERE parent_num=0');
$has_categories = 0;

if ($count_level0) $has_categories = $count_level0[0][0];


function getmicrotime(){
	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);
}



function poweredby () {
	global $sph_messages;
	global $urlRequestRoot, $templateFolder, $moduleFolder, $sourceFolder,$cmsFolder;
	return $sph_messages['Powered by'] . ' <a href="http://www.sphider.eu/"><img src="' . "$urlRequestRoot/$cmsFolder/$moduleFolder/search/" . 'sphider-logo.png" border="0" style="vertical-align: middle" alt="Sphider"></a>';
}

function saveToLog ($query, $elapsed, $results) {
	global $sph_mysql_table_prefix;
	if ($results == '')
		$results = 0;
	$query = "insert into ".$sph_mysql_table_prefix."query_log (query, time, elapsed, results) values ('$query', now(), '$elapsed', '$results')";
	if(!mysql_query($query))
		displayerror(mysql_error());
}


$resultHTML = '';
switch ($search) {
	case 1:
		if (!isset($results))
			$results = '';
		$search_results = get_search_results($query, $start, $category, $type, $results, $domain);

		extract($search_results);

		if ($search_results['did_you_mean']){
			$dym = quote_replace(addmarks($search_results['did_you_mean']));
			$resultHTML .= <<<DIDYOUMEAN
				<div id="did_you_mean">
					{$sph_messages['DidYouMean']}: <a href="./+search&query=dym&search=1">{$search_results['did_you_mean_b']}</a>
				</div>
DIDYOUMEAN;
		}
		
		if ($search_results['ignore_words']) {
			$resultHTML .= '<div id="common_report">';
			$ignored = '';
			while ($thisword=each($ignore_words)) {
				$ignored .= " ".$thisword[1];
			}
			$resultHTML .= str_replace ('%ignored_words', $ignored, $sph_messages["ignoredWords"]);
			$resultHTML .= '</div>';
		}

		if ($search_results['total_results']==0) {
			$resultHTML .= '<div id ="result_report">';
			$resultHTML .= str_replace ('%query', $ent_query, $sph_messages["noMatch"]);
			$resultHTML .= '</div>';
		}

		if ($total_results != 0 && $from <= $to) {
			$resultHTML .= '<div id ="result_report">';
			$res = $sph_messages['Results'];
			$res = str_replace ('%from', $from, $res);
			$res = str_replace ('%to', $to, $res);
			$res = str_replace ('%all', $total_results, $res);
			$matchword = $sph_messages["matches"];
			if ($total_results== 1)
				$matchword= $sph_messages["match"];
			else
				$matchword= $sph_messages["matches"];
		
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
				$resultHTML .= "<a href=\"$url\" class=\"sph_title\">" . ($title?$title:$sph_messages['Untitled']) . "</a><br/>\n";
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

			$resultHTML .= "<div id=\"other_pages\">\n{$sph_messages["Result page"]}: ";
			if ($start >1) {
				$q = quote_replace(addmarks($query));
				$resultHTML .= "<a href=\"./+search&query=$q&start=$prev&search=1&results={$results_per_page}{$type_qry}{$adv_qry}&domain=$domain\">{$sph_messages['Previous']}</a>";
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
				$resultHTML .= "<a href=\"./+search&query=$q&start=$next&search=1&results={$results_per_page}{$type_qry}{$adv_qry}&domain=$domain\">{$sph_messages['Next']}</a>";
			}

			$resultHTML .= '</div>';
		}

		$resultHTML .= '<div class="divline"></div>';
		$resultHTML .= '<div id="powered_by">';
		$resultHTML .= poweredby();
		$resultHTML .= '</div>';

	break;

	default:
		if ($show_categories) {
			if ($_REQUEST['catid']  && is_numeric($catid)) {
				$cat_info = get_category_info($catid);
			} else {
				$cat_info = get_categories_view();
			}
			require("$template_dir/$template/categories.html");
		}
	break;
}
?>
