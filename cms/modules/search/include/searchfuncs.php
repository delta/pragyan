<?php 
/*******************************************
* Sphider Version 1.3.x
* This program is licensed under the GNU GPL.
* By Ando Saabas		  ando(a t)cs.ioc.ee
********************************************/

error_reporting(E_ALL ^ E_NOTICE);
	
	function swap_max (&$arr, $start, $domain) {
		$pos  = $start;
		$maxweight = $arr[$pos]['weight'];
		for  ($i = $start; $i< count($arr); $i++) {
			if ($arr[$i]['domain'] == $domain) {
				$pos = $i;
				$maxweight = $arr[$i]['weight'];
				break;
			}
			if ($arr[$i]['weight'] > $maxweight) {
				$pos = $i;
				$maxweight = $arr[$i]['weight'];
			}
		}
		$temp = $arr[$start];
		$arr[$start] = $arr[$pos];
		$arr[$pos] = $temp;
	}

	function sort_with_domains (&$arr) {
		$domain = -1;
		for  ($i = 0; $i< count($arr)-1; $i++) {
			swap_max($arr, $i, $domain);
			$domain = $arr[$i]['domain'];
		}
	}
	
	function cmp($a, $b) {
		if ($a['weight'] == $b['weight'])
			return 0;

		return ($a['weight'] > $b['weight']) ? -1 : 1;
	}

	function addmarks($a) {
		$a = preg_replace("/[ ]+/", " ", $a);
		$a = str_replace(" +", "+", $a);
		$a = str_replace(" ", "+", $a);
		return $a;

	}

	function makeboollist($a) {
		global $entities, $stem_words;
		while ($char = each($entities)) {
			$a = preg_replace("/".$char[0]."/i", $char[1], $a);
		}
		$a = trim($a);

		$a = preg_replace("/&quot;/i", "\"", $a);
		$returnWords = array();
		//get all phrases
		$regs = Array();
		while (preg_match("/([-]?)\"([^\"]+)\"/", $a, $regs)) {
			if ($regs[1] == '') {
				$returnWords['+s'][] = $regs[2];
				$returnWords['hilight'][] = $regs[2];
			} else {
				$returnWords['-s'][] = $regs[2];
			}
			$a = str_replace($regs[0], "", $a);
		}
		$a = strtolower(preg_replace("/[ ]+/", " ", $a));
//		$a = remove_accents($a);
		$a = trim($a);
		$words = explode(' ', $a);
		if ($a=="") {
			$limit = 0;
		} else {
		$limit = count($words);
		}


		$k = 0;
		//get all words (both include and exlude)
		$includeWords = array();
		while ($k < $limit) {
			if (substr($words[$k], 0, 1) == '+') {
				$includeWords[] = substr($words[$k], 1);
				if (!ignoreWord(substr($words[$k], 1))) {
					$returnWords['hilight'][] = substr($words[$k], 1);
					if ($stem_words == 1) {
						$returnWords['hilight'][] = stem(substr($words[$k], 1));
					}
				}
			} else if (substr($words[$k], 0, 1) == '-') {
				$returnWords['-'][] = substr($words[$k], 1);
			} else {
				$includeWords[] = $words[$k];
				if (!ignoreWord($words[$k])) {
					$returnWords['hilight'][] = $words[$k];
					if ($stem_words == 1) {
						$returnWords['hilight'][] = stem($words[$k]);
					}
				}
			}
			$k++;
		}
		//add words from phrases to includes
		if (isset($returnWords['+s'])) {
			foreach ($returnWords['+s'] as $phrase) {
				$phrase = strtolower(preg_replace("/[ ]+/", " ", $phrase));
				$phrase = trim($phrase);
				$temparr = explode(' ', $phrase);
				foreach ($temparr as $w)
					$includeWords[] = $w;
			}
		}

		foreach ($includeWords as $word) {
			if (!($word =='')) {
				if (ignoreWord($word)) {

					$returnWords['ignore'][] = $word;
				} else {
					$returnWords['+'][] = $word;
				}	
			}

		}
		return $returnWords;

	}

	function ignoreword($word) {
		global $common;
		global $min_word_length;
		global $index_numbers;
		if ($index_numbers == 1) {
			$pattern = "[a-z0-9]+";
		} else {
			$pattern = "[a-z]+";
		}
		if (strlen($word) < $min_word_length || (!preg_match("/".$pattern."/i", remove_accents($word))) || ($common[$word] == 1)) {
			return 1;
		} else {
			return 0;
		}
	}

	function search($searchstr, $category, $start, $per_page, $type, $domain) {
		global $length_of_link_desc,$mysql_table_prefix, $show_meta_description, $merge_site_results, $stem_words, $did_you_mean_enabled ;
		
		$possible_to_find = 1;
		$result = mysql_query("select domain_id from ".$mysql_table_prefix."domains where domain = '$domain'");
		if (mysql_num_rows($result)> 0) {
			$thisrow = mysql_fetch_array($result);
			$domain_qry = "and domain = ".$thisrow[0];
		} else {
			$domain_qry = "";
		}

		//find all sites that should not be included in the result
		if (count($searchstr['+']) == 0) {
			return null;
		}
		$wordarray = $searchstr['-'];
		$notlist = array();
		$not_words = 0;
		while ($not_words < count($wordarray)) {
			if ($stem_words == 1) {
				$searchword = addslashes(stem($wordarray[$not_words]));
			} else {
				$searchword = addslashes($wordarray[$not_words]);
			}
			$wordmd5 = substr(md5($searchword), 0, 1);

            $query1 = "SELECT link_id from ".$mysql_table_prefix."link_keyword$wordmd5, ".$mysql_table_prefix."keywords where ".$mysql_table_prefix."link_keyword$wordmd5.keyword_id= ".$mysql_table_prefix."keywords.keyword_id and keyword='$searchword'";

			$result = mysql_query($query1);

			while ($row = mysql_fetch_row($result)) {	
				$notlist[$not_words]['id'][$row[0]] = 1;
			}
			$not_words++;
		}
		

		//find all sites containing the search phrase
		$wordarray = $searchstr['+s'];
		$phrase_words = 0;
		while ($phrase_words < count($wordarray)) {

			$searchword = addslashes($wordarray[$phrase_words]);
			$query1 = "SELECT link_id from ".$mysql_table_prefix."links where fulltxt like '% $searchword%'";
			echo mysql_error();
			$result = mysql_query($query1);
			$num_rows = mysql_num_rows($result);
			if ($num_rows == 0) {
				$possible_to_find = 0;
				break;
			}
			while ($row = mysql_fetch_row($result)) {	
				$phraselist[$phrase_words]['id'][$row[0]] = 1;
			}
			$phrase_words++;
		}
		

		if (($category> 0) && $possible_to_find==1) {
			$allcats = get_cats($category);
			$catlist = implode(",", $allcats);
			$query1 = "select link_id from ".$mysql_table_prefix."links, ".$mysql_table_prefix."sites, ".$mysql_table_prefix."categories, ".$mysql_table_prefix."site_category where ".$mysql_table_prefix."links.site_id = ".$mysql_table_prefix."sites.site_id and ".$mysql_table_prefix."sites.site_id = ".$mysql_table_prefix."site_category.site_id and ".$mysql_table_prefix."site_category.category_id in ($catlist)";
			$result = mysql_query($query1);
			echo mysql_error();
			$num_rows = mysql_num_rows($result);
			if ($num_rows == 0) {
				$possible_to_find = 0;
			}
			while ($row = mysql_fetch_row($result)) {	
				$category_list[$row[0]] = 1;
			}
		}


		//find all sites that include the search word		
		$wordarray = $searchstr['+'];
		$words = 0;
		$starttime = getmicrotime();
		while (($words < count($wordarray)) && $possible_to_find == 1) {
			if ($stem_words == 1) {
				$searchword = addslashes(stem($wordarray[$words]));
			} else {
				$searchword = addslashes($wordarray[$words]);
			}
			$wordmd5 = substr(md5($searchword), 0, 1);
			$query1 = "SELECT distinct link_id, weight, domain from ".$mysql_table_prefix."link_keyword$wordmd5, ".$mysql_table_prefix."keywords where ".$mysql_table_prefix."link_keyword$wordmd5.keyword_id= ".$mysql_table_prefix."keywords.keyword_id and keyword='$searchword' $domain_qry order by weight desc";
			echo mysql_error();
			$result = mysql_query($query1);
			$num_rows = mysql_num_rows($result);
			if ($num_rows == 0) {
				if ($type != "or") {
					$possible_to_find = 0;
					break;
				}
			}
			if ($type == "or") {
				$indx = 0;
			} else {
				$indx = $words;
			}

			while ($row = mysql_fetch_row($result)) {	
				$linklist[$indx]['id'][] = $row[0];
				$domains[$row[0]] = $row[2];
				$linklist[$indx]['weight'][$row[0]] = $row[1];
			}
			$words++;
		}


		if ($type == "or") {
			$words = 1;
		}
		$result_array_full = Array();

		if ($possible_to_find !=0) {
			if ($words == 1 && $not_words == 0 && $category < 1) { //if there is only one search word, we already have the result
				$result_array_full = $linklist[0]['weight'];
			} else { //otherwise build an intersection of all the results
				$j= 1;
				$min = 0;
				while ($j < $words) {
					if (count($linklist[$min]['id']) > count($linklist[$j]['id'])) {
						$min = $j;
					}
					$j++;
				}

				$j = 0;


				$temp_array = $linklist[$min]['id'];
				$count = 0;
				while ($j < count($temp_array)) {
					$k = 0; //and word counter
					$n = 0; //not word counter
					$o = 0; //phrase word counter
					$weight = 1;
					$break = 0;
					while ($k < $words && $break== 0) {
						if ($linklist[$k]['weight'][$temp_array[$j]] > 0) {
							$weight = $weight + $linklist[$k]['weight'][$temp_array[$j]];
						} else {
							$break = 1;
						}
						$k++;
					}
					while ($n < $not_words && $break== 0) {
						if ($notlist[$n]['id'][$temp_array[$j]] > 0) {
							$break = 1;
						}
						$n++;
					}				

					while ($o < $phrase_words && $break== 0) {
						if ($phraselist[$n]['id'][$temp_array[$j]] != 1) {
							$break = 1;
						}
						$o++;
					}
					if ($break== 0 && $category > 0 && $category_list[$temp_array[$j]] != 1) {
						$break = 1;
					}

					if ($break == 0) {
						$result_array_full[$temp_array[$j]] = $weight;
						$count ++;
					}
					$j++;
				}
			}
		}
		$end = getmicrotime()- $starttime;


		if ((count($result_array_full) == 0 || $possible_to_find == 0) && $did_you_mean_enabled == 1) {
			reset ($searchstr['+']);
			foreach ($searchstr['+'] as $word) {
				$word = addslashes($word);
				$result = mysql_query("select keyword from ".$mysql_table_prefix."keywords where soundex(keyword) = soundex('$word')");
				$max_distance = 100;
				$near_word ="";
				while ($row=mysql_fetch_row($result)) {
					
					$distance = levenshtein($row[0], $word);
					if ($distance < $max_distance && $distance <4) {
						$max_distance = $distance;
						$near_word = $row[0];
					}
				}

				if ($near_word != "" && $word != $near_word) {
					$near_words[$word] = $near_word;
				}

			}
			$res['did_you_mean'] = $near_words;
			return $res;
		}
		if (count($result_array_full) == 0) {
			return null;
		}
		arsort ($result_array_full);


		if ($merge_site_results == 1 && $domain_qry == "") {
			while (list($key, $value) = each($result_array_full)) {
				if (!isset($domains_to_show[$domains[$key]])) {
					$result_array_temp[$key] = $value;
					$domains_to_show[$domains[$key]] = 1;
				} else if ($domains_to_show[$domains[$key]] ==  1) {
					$domains_to_show[$domains[$key]] = Array ($key => $value);
				}
			}
		} else {
			$result_array_temp = $result_array_full;
		}
	
		
		while (list($key, $value) = each ($result_array_temp)) {
			$result_array[$key] = $value;
			if (isset ($domains_to_show[$domains[$key]]) && $domains_to_show[$domains[$key]] != 1) {
				list ($k, $v) = each($domains_to_show[$domains[$key]]);
				$result_array[$k] = $v;
			}
		}

		$results = count($result_array);

		$keys = array_keys($result_array);
		$maxweight = $result_array[$keys[0]];


		for ($i = ($start -1)*$per_page; $i <min($results, ($start -1)*$per_page + $per_page) ; $i++) {
			$in[] = $keys[$i];

		}
		if (!is_array($in)) {
			$res['results'] = $results;
			return $res;
		}

		$inlist = implode(",", $in);


		if ($length_of_link_desc == 0) {
			$fulltxt = "fulltxt";
		} else {
			$fulltxt = "substring(fulltxt, 1, $length_of_link_desc)";
		}

		$query1 = "SELECT distinct link_id, url, title, description,  $fulltxt, size FROM ".$mysql_table_prefix."links WHERE link_id in ($inlist)";

		$result = mysql_query($query1);
		echo mysql_error();

		$i = 0;
		while ($row = mysql_fetch_row($result)) {
			$res[$i]['title'] = $row[2];
			$res[$i]['url'] = $row[1];
			if ($row[3] != null && $show_meta_description == 1)
				$res[$i]['fulltxt'] = $row[3];
			else 
				$res[$i]['fulltxt'] = $row[4];
			$res[$i]['size'] = $row[5];
			$res[$i]['weight'] = $result_array[$row[0]];
			$dom_result = mysql_query("select domain from ".$mysql_table_prefix."domains where domain_id='".$domains[$row[0]]."'");
			$dom_row = mysql_fetch_row($dom_result);
			$res[$i]['domain'] = $dom_row[0];
			$i++;
		}



		if ($merge_site_results  && $domain_qry == "") {
			sort_with_domains($res);
		} else {
			usort($res, "cmp"); 	
		}
		echo mysql_error();
		$res['maxweight'] = $maxweight;
		$res['results'] = $results;
		return $res;
	/**/
	}

function get_search_results($query, $start, $category, $searchtype, $results, $domain) {

	global $sph_messages, $results_per_page,
		$links_to_next,
		$show_query_scores,
		$mysql_table_prefix,
		$desc_length;
	
	if ($results != "") {
		$results_per_page = $results;
	}

	if ($searchtype == "phrase") {
	   $query=str_replace('"','',$query);
	   $query = "\"".$query."\"";
	}

	$starttime = getmicrotime();
	// catch " if only one time entered
        if (substr_count($query,'"')==1){
           $query=str_replace('"','',$query);
        }   
	$words = makeboollist($query);
	$ignorewords = $words['ignore'];

	
	$full_result['ignore_words'] = $words['ignore'];

	if ($start==0) 
		$start=1;
	$result = search($words, $category, $start, $results_per_page, $searchtype, $domain);
	$query= stripslashes($query);

	$entitiesQuery = htmlspecialchars($query);
	$full_result['ent_query'] = $entitiesQuery;

	$endtime = getmicrotime() - $starttime;
	$rows = $result['results'];
	$time = round($endtime*100)/100;

	
	$full_result['time'] = $time;
	
	$did_you_mean = "";


	if (isset($result['did_you_mean'])) {
		$did_you_mean_b=$entitiesQuery;
		$did_you_mean=$entitiesQuery;
		while (list($key, $val) = each($result['did_you_mean'])) {
			if ($key != $val) {
				$did_you_mean_b = str_replace($key, "<b>$val</b>", $did_you_mean_b);
				$did_you_mean = str_replace($key, "$val", $did_you_mean);
			}
		}
	}

	$full_result['did_you_mean'] = $did_you_mean;
	$full_result['did_you_mean_b'] = $did_you_mean_b;

	$matchword = $sph_messages["matches"];
	if ($rows == 1) {
		$matchword= $sph_messages["match"];
	}

	$num_of_results = count($result) - 2;
	
	
	
	$full_result['num_of_results'] = $num_of_results;


	if ($start < 2)
		saveToLog(addslashes($query), $time, $rows);
	$from = ($start-1) * $results_per_page+1;
	$to = min(($start)*$results_per_page, $rows);

	
	$full_result['from'] = $from;
	$full_result['to'] = $to;
	$full_result['total_results'] = $rows;

	if ($rows>0) {
		$maxweight = $result['maxweight'];
		$i = 0;
		while ($i < $num_of_results && $i < $results_per_page) {
			$title = $result[$i]['title'];
			$url = $result[$i]['url'];
			$fulltxt = $result[$i]['fulltxt'];
			$page_size = $result[$i]['size'];
			$domain = $result[$i]['domain'];
			if ($page_size!="") 
				$page_size = number_format($page_size, 1)."kb";
			
			
			$txtlen = strlen($fulltxt);
			if ($txtlen > $desc_length) {
				$places = array();
				foreach($words['hilight'] as $word) {
					$tmp = strtolower($fulltxt);
					$found_in = strpos($tmp, $word);
					$sum = -strlen($word);
					while (!($found_in =='')) {
						$pos = $found_in+strlen($word);
						$sum += $pos;  //FIX!!
						$tmp = substr($tmp, $pos);
						$places[] = $sum;
						$found_in = strpos($tmp, $word);

					}
				}
				sort($places);
				$x = 0;
				$begin = 0;
				$end = 0;
				while(list($id, $place) = each($places)) {
					while ($places[$id + $x] - $place < $desc_length && $x+$id < count($places) && $place < strlen($fulltxt) -$desc_length) {
						$x++;
						$begin = $id;
						$end = $id + $x;
					}
				}

				$begin_pos = max(0, $places[$begin] - 30);
				$fulltxt = substr($fulltxt, $begin_pos, $desc_length);

				if ($places[$begin] > 0) {
					$begin_pos = strpos($fulltxt, " ");
				}
				$fulltxt = substr($fulltxt, $begin_pos, $desc_length);
				$fulltxt = substr($fulltxt, 0, strrpos($fulltxt, " "));
				$fulltxt = $fulltxt;
			}

			$weight = number_format($result[$i]['weight']/$maxweight*100, 2);
			if ($title=='')
				$title = $sph_messages["Untitled"];
			$regs = Array();

			if (strlen($title) > 80) {
				$title = substr($title, 0,76)."...";
			}
			foreach($words['hilight'] as $change) {
				while (preg_match("/[^\>](".$change.")[^\<]/i", " ".$title." ", $regs)) {
					$title = preg_replace("/".$regs[1]."/i", "<b>".$regs[1]."</b>", $title);
				}

				while (preg_match("/[^\>](".$change.")[^\<]/i", " ".$fulltxt." ", $regs)) {
					$fulltxt = preg_replace("/".$regs[1]."/i", "<b>".$regs[1]."</b>", $fulltxt);
				}
				$url2 = $url;
				while (preg_match("/[^\>](".$change.")[^\<]/i", $url2, $regs)) {
					$url2 = preg_replace("/".$regs[1]."/i", "<b>".$regs[1]."</b>", $url2);
				}
			}


			$num = $from + $i;

			$full_result['qry_results'][$i]['num'] =  $num;
			$full_result['qry_results'][$i]['weight'] =  $weight;
			$full_result['qry_results'][$i]['url'] =  $url;
			$full_result['qry_results'][$i]['title'] =  $title;
			$full_result['qry_results'][$i]['fulltxt'] =  $fulltxt;
			$full_result['qry_results'][$i]['url2'] =  $url2;
			$full_result['qry_results'][$i]['page_size'] =  $page_size;
			$full_result['qry_results'][$i]['domain_name'] =  $domain;
			$i++;
		}
	}



	$pages = ceil($rows / $results_per_page);
	$full_result['pages'] = $pages;
	$prev = $start - 1;
	$full_result['prev'] = $prev;
	$next = $start + 1;
	$full_result['next'] = $next;
	$full_result['start'] = $start;
	$full_result['query'] = $entitiesQuery;

	if ($from <= $to) {

		$firstpage = $start - $links_to_next;
		if ($firstpage < 1) $firstpage = 1;
		$lastpage = $start + $links_to_next;
		if ($lastpage > $pages) $lastpage = $pages;

		for ($x=$firstpage; $x<=$lastpage; $x++)
			$full_result['other_pages'][] = $x;

	}

	return $full_result;

}


?>
