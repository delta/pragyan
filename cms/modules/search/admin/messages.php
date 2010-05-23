<?php 
	error_reporting (E_ALL);
$messages = Array (
 "noFollow" => Array (
	0 => " <font color=red><b> No-follow flag set</b></font>. ",
	1 => " No-follow flag set."
 ),
 "inDatabase" => Array (
	0 => " <font color=red><b> already in database</b></font><br>",
	1 => " already in database\n"
 ),
 "completed" => Array (
	0 => "<br>Completed at %cur_time.\n<br>",
	1 => "Completed at %cur_time.\n"
 ),
 "starting" => Array (
	0 => " Starting indexing at %cur_time.\n",
	1 => " Starting indexing at %cur_time.\n"
	 ),
 "quit" => Array (
	0 => "</body></html>",
	1 => ""
 ),
 "pageRemoved" => Array (
	0 => " <font color=red>Page removed from index.</font><br>\n",
	1 => " Page removed from index.\n"
 ),
  "continueSuspended" => Array (
	0 => "<br>Continuing suspended indexing.<br>\n",
	1 => "Continuing suspended indexing.\n"
 ),
  "indexed" => Array (
	0 => "<br><b> <font color=\"green\">Indexed</font></b><br>\n",
	1 => " \nIndexed\n"
 ),
"duplicate" => Array (
	0 => " <font color=\"red\"><b>Page is a duplicate.</b></font><br>\n",
	1 => " Page is a duplicate.\n"
 ),
"md5notChanged" => Array (
	0 => " <font color=\"red\"><b>MD5 sum checked. Page content not changed</b></font><br>\n",
	1 => " MD5 sum checked. Page content not changed.\n"
 ),
"metaNoindex" => Array (
	0 => " <font color=\"red\">No-Index flag set in meta tags.</font><br>\n",
	1 => " No-Index flag set in meta tags.\n"
 ),
  "re-indexed" => Array (
	0 => " <font color=\"green\">Re-indexed</font><br>\n",
	1 => " Re-indexed\n"
 ),
"minWords" => Array (
	0 => " <font color=\"red\">Page contains less than $min_words_per_page words</font><br>\n",
	1 => " Page contains less than $min_words_per_page words.\n"
 )
);

function printRobotsReport($num, $thislink, $cl) {
	global $print_results, $log_format;
	$log_msg_txt = "$num. Link $thislink: file checking forbidden in robots.txt file.\n";
	$log_msg_html = "<b>$num</b>. Link <b>$thislink</b>: <font color=red>file checking forbidden in robots.txt file</font></br>";
	if ($print_results) {
		if ($cl==0) {
			print $log_msg_html; 
		} else {
			print $log_msg_txt;
		}
		flush();
	}
	if ($log_format=="html") {
		writeToLog($log_msg_html);
	} else {
		writeToLog($log_msg_txt);
	}

}

function printUrlStringReport($num, $thislink, $cl) {
	global $print_results, $log_format;
	$log_msg_txt = "$num. Link $thislink: file checking forbidden  by required/disallowed string rule.\n";
	$log_msg_html = "<b>$num</b>. Link <b>$thislink</b>: <font color=red>file checking forbidden by required/disallowed string rule</font></br>";
	if ($print_results) {
		if ($cl==0) {
			print $log_msg_html;
		} else {
			print $log_msg_txt;
		}
		flush();
	}

	if ($log_format=="html") {
		writeToLog($log_msg_html);
	} else {
		writeToLog($log_msg_txt);
	}
}

function printRetrieving($num, $thislink, $cl) {
	global $print_results, $log_format;
	$log_msg_txt = "$num. Retrieving: $thislink at " . date("H:i:s").".\n";
	$log_msg_html = "<b>$num</b>. Retrieving: <b>$thislink</b> at " . date("H:i:s").".<br>\n";
	if ($print_results) {
		if ($cl==0) {
			print $log_msg_html;
		} else {
			print $log_msg_txt;
		}
		flush();
	}

	if ($log_format=="html") {
		writeToLog($log_msg_html);
	} else {
		writeToLog($log_msg_txt);
	}
}


function printLinksReport($numoflinks, $all_links, $cl) {
	global $print_results, $log_format;
	$log_msg_txt = " Legit links found: $all_links. New links found: $numoflinks\n";
	$log_msg_html = " Links found: <font color=\"blue\"><b>$all_links</b></font>. New links: <font color=\"blue\"><b>$numoflinks</b></font><br>\n";
	if ($print_results) {
		if ($cl==0) {
			print $log_msg_html;
		} else {
			print $log_msg_txt;
		}
		flush();
	}

	if ($log_format=="html") {
		writeToLog($log_msg_html);
	} else {
		writeToLog($log_msg_txt);
	}
}

function printHeader($omit, $url, $cl) {
	global $print_results, $log_format;

	if (count($omit) > 0 ) {
		$urlparts = parse_url($url);
		foreach ($omit as $dir) {			
			$omits[] = $urlparts['scheme']."://".$urlparts['host'].$dir;
		}
	}
	
	$log_msg_txt = "Spidering $url\n";
	if (count($omit) > 0) {
		$log_msg_txt .= "Disallowed files and directories in robots.txt:\n";
		$log_msg_txt .= implode("\n", $omits);
		$log_msg_txt .= "\n\n";
	}

	$log_msg_html_1 = "<html><head><LINK REL=STYLESHEET HREF=\"admin.css\" TYPE=\"text/css\"></head>\n";
	$log_msg_html_1 .= "<body style=\"font-family:Verdana, Arial; font-size:12px\">";
	
	$log_msg_html_link = "[Back to <a href=\"admin.php\">admin</a>]";
	$log_msg_html_2 = "<p><font size=\"+1\">Spidering <b>$url</b></font></p>\n";

	if (count($omit) > 0) {
		$log_msg_html_2 .=  "Disallowed files and directories in robots.txt:<br>\n";
		$log_msg_html_2 .=  implode("<br>", $omits);
		$log_msg_html_2 .=  "<br><br>";
	}

	if ($print_results) {
		if ($cl==0) {
			print $log_msg_html_1.$log_msg_html_link.$log_msg_html_2;
		} else {
			print $log_msg_txt;
		}
		flush();
	}

	if ($log_format=="html") {
		writeToLog($log_msg_html_1.$log_msg_html_2);
	} else {
		writeToLog($log_msg_txt);
	}
}

function printPageSizeReport($pageSize) {
	global $print_results, $log_format;
	$log_msg_txt = "Size of page: $pageSize"."kb. ";
	if ($print_results) {
		print $log_msg_txt;
		flush();
	}

	writeToLog($log_msg_txt);
}

function printUrlStatus($report, $cl) {
	global $print_results, $log_format;
	$log_msg_txt = "$report\n";
	$log_msg_html = " <font color=red><b>$report</b></font><br>\n";
	if ($print_results) {
		if ($cl==0) {
			print $log_msg_html; 
		} else {
			print $log_msg_txt;
		}
		flush();
	}
	if ($log_format=="html") {
		writeToLog($log_msg_html);
	} else {
		writeToLog($log_msg_txt);
	}

}



function printConnectErrorReport($errmsg) {
	global $print_results, $log_format;
	$log_msg_txt = "Establishing connection with socket failed. ";
	$log_msg_txt .= $errmsg;

	if ($print_results) {
		print $log_msg_txt;
		flush();
	}

	writeToLog($log_msg_txt);
}



function writeToLog($msg) {
	global $keep_log, $log_handle;
	if($keep_log) {
		if (!$log_handle) {
			die ("Cannot open file for logging. ");
		}

		if (fwrite($log_handle, $msg) === FALSE) {
			die ("Cannot write to file for logging. ");
		}
	}
}


function printStandardReport($type, $cl) {
	global $print_results, $log_format, $messages;
	if ($print_results) {
		print str_replace('%cur_time', date("H:i:s"), $messages[$type][$cl]);
		flush();
	}

	if ($log_format=="html") {
		writeToLog(str_replace('%cur_time', date("H:i:s"), $messages[$type][0]));
	} else {
		writeToLog(str_replace('%cur_time', date("H:i:s"), $messages[$type][1]));
	}

}


?>