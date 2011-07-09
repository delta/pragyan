<?php 
/*******************************************
* Sphider Version 1.3.*
* This program is licensed under the GNU GPL.
* By Ando Saabas          ando(a t)cs.ioc.ee
********************************************/

	/**
	* Returns the result of a query as an array
	* 
	* @param string $query SQL päring stringina
	* @return array|null massiiv
	 */
	function sql_fetch_all($query) {
		$result = mysql_query($query);
		if($mysql_err = mysql_errno()) {
			print $query.'<br>'.mysql_error();
		} else {
			while($row=mysql_fetch_array($result)) {
				$data[]=$row;
			}	
		}		
		return $data;
	}



	/*
	Removes duplicate elements from an array
	*/
	function distinct_array($arr) {
		rsort($arr);
		reset($arr);
		$newarr = array();
		$i = 0;
		$element = current($arr);

		for ($n = 0; $n < sizeof($arr); $n++) {
			if (next($arr) != $element) {
				$newarr[$i] = $element;
				$element = current($arr);
				$i++;
			}
		}

		return $newarr;
	}

	function get_cats($parent) {
		global $mysql_table_prefix;
		$query = "SELECT * FROM ".$mysql_table_prefix."categories WHERE parent_num=$parent";
		echo mysql_error();
		$result = mysql_query($query);
		$arr[] = $parent;
		if (mysql_num_rows($result) <> '') {
			while ($row = mysql_fetch_array($result)) {
				$id = $row[category_id];
				$arr = add_arrays($arr, get_cats($id));
			}
		}

		return $arr;
	}
	
	function add_arrays($arr1, $arr2) {
		foreach ($arr2 as $elem) {
			$arr1[] = $elem;
		}
		return $arr1;
	}

	global $entities;
	$entities = array
		(
		"&amp" => "&",
		"&apos" => "'",
		"&THORN;"  => "Ş",
		"&szlig;"  => "ß",
		"&agrave;" => "à",
		"&aacute;" => "á",
		"&acirc;"  => "â",
		"&atilde;" => "ã",
		"&auml;"   => "ä",
		"&aring;"  => "å",
		"&aelig;"  => "æ",
		"&ccedil;" => "ç",
		"&egrave;" => "è",
		"&eacute;" => "é",
		"&ecirc;"  => "ê",
		"&euml;"   => "ë",
		"&igrave;" => "ì",
		"&iacute;" => "í",
		"&icirc;"  => "î",
		"&iuml;"   => "ï",
		"&eth;"    => "ğ",
		"&ntilde;" => "ñ",
		"&ograve;" => "ò",
		"&oacute;" => "ó",
		"&ocirc;"  => "ô",
		"&otilde;" => "õ",
		"&ouml;"   => "ö",
		"&oslash;" => "ø",
		"&ugrave;" => "ù",
		"&uacute;" => "ú",
		"&ucirc;"  => "û",
		"&uuml;"   => "ü",
		"&yacute;" => "ı",
		"&thorn;"  => "ş",
		"&yuml;"   => "ÿ",
		"&THORN;"  => "Ş",
		"&szlig;"  => "ß",
		"&Agrave;" => "à",
		"&Aacute;" => "á",
		"&Acirc;"  => "â",
		"&Atilde;" => "ã",
		"&Auml;"   => "ä",
		"&Aring;"  => "å",
		"&Aelig;"  => "æ",
		"&Ccedil;" => "ç",
		"&Egrave;" => "è",
		"&Eacute;" => "é",
		"&Ecirc;"  => "ê",
		"&Euml;"   => "ë",
		"&Igrave;" => "ì",
		"&Iacute;" => "í",
		"&Icirc;"  => "î",
		"&Iuml;"   => "ï",
		"&ETH;"    => "ğ",
		"&Ntilde;" => "ñ",
		"&Ograve;" => "ò",
		"&Oacute;" => "ó",
		"&Ocirc;"  => "ô",
		"&Otilde;" => "õ",
		"&Ouml;"   => "ö",
		"&Oslash;" => "ø",
		"&Ugrave;" => "ù",
		"&Uacute;" => "ú",
		"&Ucirc;"  => "û",
		"&Uuml;"   => "ü",
		"&Yacute;" => "ı",
		"&Yhorn;"  => "ş",
		"&Yuml;"   => "ÿ"
		);

	global $apache_indexes;
	//Apache multi indexes parameters
	$apache_indexes = array (  
		"N=A" => 1,
		"N=D" => 1,
		"M=A" => 1,
		"M=D" => 1,
		"S=A" => 1,
		"S=D" => 1,
		"D=A" => 1,
		"D=D" => 1,
		"C=N;O=A" => 1,
		"C=M;O=A" => 1,
		"C=S;O=A" => 1,
		"C=D;O=A" => 1,
		"C=N;O=D" => 1,
		"C=M;O=D" => 1,
		"C=S;O=D" => 1,
		"C=D;O=D" => 1);


	function remove_accents($string) {
		return (strtr($string, "ÀÁÂÃÄÅÆàáâãäåæÒÓÔÕÕÖØòóôõöøÈÉÊËèéêëğÇçĞÌÍÎÏìíîïÙÚÛÜùúûüÑñŞßÿı",
					  "aaaaaaaaaaaaaaoooooooooooooeeeeeeeeecceiiiiiiiiuuuuuuuunntsyy"));
	}

	global $common;
	$common = array
		(
		);

	global $lines;
	$lines = @file($include_dir.'/common.txt');

	if (is_array($lines)) {
		while (list($id, $word) = each($lines))
			$common[trim($word)] = 1;
	}

	$ext = array
		(
		);

	$lines = @file('ext.txt');

	if (is_array($lines)) {
		while (list($id, $word) = each($lines))
			$ext[] = trim($word);
	}

	function is_num($var) {
	   for ($i=0;$i<strlen($var);$i++) {
		   $ascii_code=ord($var[$i]);
		   if ($ascii_code >=49 && $ascii_code <=57){
			   continue;
		   } else {
			   return false;
		   }
	   }
  		   return true;
	}

	function getHttpVars() {
		$superglobs = array(
			'_POST',
			'_GET',
			'HTTP_POST_VARS',
			'HTTP_GET_VARS');

		$httpvars = array();

		// extract the right array
		foreach ($superglobs as $glob) {
			global $$glob;
			if (isset($$glob) && is_array($$glob)) {
				$httpvars = $$glob;
			 }
			if (count($httpvars) > 0)
				break;
		}
		return $httpvars;

	}
function countSubstrs($haystack, $needle) {
	$count = 0;
	while(strpos($haystack,$needle) !== false) {
	   $haystack = substr($haystack, (strpos($haystack,$needle) + 1));
	   $count++;
	}
	return $count;
}

function quote_replace($str) {

		$str = str_replace("\"",
					  "&quot;", $str);
		return str_replace("'","&apos;", $str);
}


function fst_lt_snd($version1, $version2) {

	$list1 = explode(".", $version1);
	$list2 = explode(".", $version2);

	$length = count($list1);
	$i = 0;
	while ($i < $length) {
		if ($list1[$i] < $list2[$i])
			return true;
		if ($list1[$i] > $list2[$i])
			return false;
		$i++;
	}
	
	if ($length < count($list2)) {
		return true;
	}
	return false;

}

function get_dir_contents($dir) {
	$contents = Array();
	if ($handle = opendir($dir)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				$contents[] = $file;
			}
		}
		closedir($handle);
	}
	return $contents;
}

function replace_ampersand($str) {
	return str_replace("&", "%26", $str);
}



    /**
	* Stemming algorithm
    * Copyright (c) 2005 Richard Heyes (http://www.phpguru.org/)
    * All rights reserved.
    * This script is free software.
	* Modified to work with php versions prior 5 by Ando Saabas
    */

	/**
	* Regex for matching a consonant
	*/
	global $regex_consonant;
	$regex_consonant = '(?:[bcdfghjklmnpqrstvwxz]|(?<=[aeiou])y|^y)';


	/**
	* Regex for matching a vowel
	*/
	global $regex_vowel;
	$regex_vowel = '(?:[aeiou]|(?<![aeiou])y)';

	/**
	* Stems a word. Simple huh?
	*
	* @param  string $word Word to stem
	* @return string       Stemmed word
	*/
	function stem($word)
	{
		if (strlen($word) <= 2) {
			return $word;
		}

		$word = step1ab($word);
		$word = step1c($word);
		$word = step2($word);
		$word = step3($word);
		$word = step4($word);
		$word = step5($word);

		return $word;
	}


	/**
	* Step 1
	*/
	function step1ab($word)
	{
		global $regex_vowel, $regex_consonant;
		// Part a
		if (substr($word, -1) == 's') {

			   replace($word, 'sses', 'ss')
			OR replace($word, 'ies', 'i')
			OR replace($word, 'ss', 'ss')
			OR replace($word, 's', '');
		}

		// Part b
		if (substr($word, -2, 1) != 'e' OR !replace($word, 'eed', 'ee', 0)) { // First rule
			$v = $regex_vowel;
			// ing and ed
			if (   preg_match("#$v+#", substr($word, 0, -3)) && replace($word, 'ing', '')
				OR preg_match("#$v+#", substr($word, 0, -2)) && replace($word, 'ed', '')) { // Note use of && and OR, for precedence reasons

				// If one of above two test successful
				if (    !replace($word, 'at', 'ate')
					AND !replace($word, 'bl', 'ble')
					AND !replace($word, 'iz', 'ize')) {

					// Double consonant ending
					if (    doubleConsonant($word)
						AND substr($word, -2) != 'll'
						AND substr($word, -2) != 'ss'
						AND substr($word, -2) != 'zz') {

						$word = substr($word, 0, -1);

					} else if (m($word) == 1 AND cvc($word)) {
						$word .= 'e';
					}
				}
			}
		}

		return $word;
	}


	/**
	* Step 1c
	*
	* @param string $word Word to stem
	*/
	function step1c($word)
	{
		global $regex_vowel, $regex_consonant;
		$v = $regex_vowel;

		if (substr($word, -1) == 'y' && preg_match("#$v+#", substr($word, 0, -1))) {
			replace($word, 'y', 'i');
		}

		return $word;
	}


	/**
	* Step 2
	*
	* @param string $word Word to stem
	*/
	function step2($word)
	{
		switch (substr($word, -2, 1)) {
			case 'a':
				   replace($word, 'ational', 'ate', 0)
				OR replace($word, 'tional', 'tion', 0);
				break;

			case 'c':
				   replace($word, 'enci', 'ence', 0)
				OR replace($word, 'anci', 'ance', 0);
				break;

			case 'e':
				replace($word, 'izer', 'ize', 0);
				break;

			case 'g':
				replace($word, 'logi', 'log', 0);
				break;

			case 'l':
				   replace($word, 'entli', 'ent', 0)
				OR replace($word, 'ousli', 'ous', 0)
				OR replace($word, 'alli', 'al', 0)
				OR replace($word, 'bli', 'ble', 0)
				OR replace($word, 'eli', 'e', 0);
				break;

			case 'o':
				   replace($word, 'ization', 'ize', 0)
				OR replace($word, 'ation', 'ate', 0)
				OR replace($word, 'ator', 'ate', 0);
				break;

			case 's':
				   replace($word, 'iveness', 'ive', 0)
				OR replace($word, 'fulness', 'ful', 0)
				OR replace($word, 'ousness', 'ous', 0)
				OR replace($word, 'alism', 'al', 0);
				break;

			case 't':
				   replace($word, 'biliti', 'ble', 0)
				OR replace($word, 'aliti', 'al', 0)
				OR replace($word, 'iviti', 'ive', 0);
				break;
		}

		return $word;
	}


	/**
	* Step 3
	*
	* @param string $word String to stem
	*/
	function step3($word)
	{
		switch (substr($word, -2, 1)) {
			case 'a':
				replace($word, 'ical', 'ic', 0);
				break;

			case 's':
				replace($word, 'ness', '', 0);
				break;

			case 't':
				   replace($word, 'icate', 'ic', 0)
				OR replace($word, 'iciti', 'ic', 0);
				break;

			case 'u':
				replace($word, 'ful', '', 0);
				break;

			case 'v':
				replace($word, 'ative', '', 0);
				break;

			case 'z':
				replace($word, 'alize', 'al', 0);
				break;
		}

		return $word;
	}


	/**
	* Step 4
	*
	* @param string $word Word to stem
	*/
	function step4($word)
	{
		switch (substr($word, -2, 1)) {
			case 'a':
				replace($word, 'al', '', 1);
				break;

			case 'c':
				   replace($word, 'ance', '', 1)
				OR replace($word, 'ence', '', 1);
				break;

			case 'e':
				replace($word, 'er', '', 1);
				break;

			case 'i':
				replace($word, 'ic', '', 1);
				break;

			case 'l':
				   replace($word, 'able', '', 1)
				OR replace($word, 'ible', '', 1);
				break;

			case 'n':
				   replace($word, 'ant', '', 1)
				OR replace($word, 'ement', '', 1)
				OR replace($word, 'ment', '', 1)
				OR replace($word, 'ent', '', 1);
				break;

			case 'o':
				if (substr($word, -4) == 'tion' OR substr($word, -4) == 'sion') {
				   replace($word, 'ion', '', 1);
				} else {
					replace($word, 'ou', '', 1);
				}
				break;

			case 's':
				replace($word, 'ism', '', 1);
				break;

			case 't':
				   replace($word, 'ate', '', 1)
				OR replace($word, 'iti', '', 1);
				break;

			case 'u':
				replace($word, 'ous', '', 1);
				break;

			case 'v':
				replace($word, 'ive', '', 1);
				break;

			case 'z':
				replace($word, 'ize', '', 1);
				break;
		}

		return $word;
	}


	/**
	* Step 5
	*
	* @param string $word Word to stem
	*/
	function step5($word)
	{
		// Part a
		if (substr($word, -1) == 'e') {
			if (m(substr($word, 0, -1)) > 1) {
				replace($word, 'e', '');

			} else if (m(substr($word, 0, -1)) == 1) {

				if (!cvc(substr($word, 0, -1))) {
					replace($word, 'e', '');
				}
			}
		}

		// Part b
		if (m($word) > 1 AND doubleConsonant($word) AND substr($word, -1) == 'l') {
			$word = substr($word, 0, -1);
		}

		return $word;
	}


	/**
	* Replaces the first string with the second, at the end of the string. If third
	* arg is given, then the preceding string must match that m count at least.
	*
	* @param  string $str   String to check
	* @param  string $check Ending to check for
	* @param  string $repl  Replacement string
	* @param  int    $m     Optional minimum number of m() to meet
	* @return bool          Whether the $check string was at the end
	*                       of the $str string. True does not necessarily mean
	*                       that it was replaced.
	*/
	function replace(&$str, $check, $repl, $m = null)
	{
		$len = 0 - strlen($check);

		if (substr($str, $len) == $check) {
			$substr = substr($str, 0, $len);
			if (is_null($m) OR m($substr) > $m) {
				$str = $substr . $repl;
			}

			return true;
		}

		return false;
	}


	/**
	* What, you mean it's not obvious from the name?
	*
	* m() measures the number of consonant sequences in $str. if c is
	* a consonant sequence and v a vowel sequence, and <..> indicates arbitrary
	* presence,
	*
	* <c><v>       gives 0
	* <c>vc<v>     gives 1
	* <c>vcvc<v>   gives 2
	* <c>vcvcvc<v> gives 3
	*
	* @param  string $str The string to return the m count for
	* @return int         The m count
	*/
	function m($str)
	{
		global $regex_vowel, $regex_consonant;
		$c = $regex_consonant;
		$v = $regex_vowel;

		$str = preg_replace("#^$c+#", '', $str);
		$str = preg_replace("#$v+$#", '', $str);

		preg_match_all("#($v+$c+)#", $str, $matches);

		return count($matches[1]);
	}


	/**
	* Returns true/false as to whether the given string contains two
	* of the same consonant next to each other at the end of the string.
	*
	* @param  string $str String to check
	* @return bool        Result
	*/
	function doubleConsonant($str)
	{
		global $regex_consonant;
		$c = $regex_consonant;

		return preg_match("#$c{2}$#", $str, $matches) AND $matches[0]{0} == $matches[0]{1};
	}


	/**
	* Checks for ending CVC sequence where second C is not W, X or Y
	*
	* @param  string $str String to check
	* @return bool        Result
	*/
	function cvc($str)
	{
		$c = $regex_consonant;
		$v = $regex_vowel;

		return     preg_match("#($c$v$c)$#", $str, $matches)
			   AND strlen($matches[1]) == 3
			   AND $matches[1]{2} != 'w'
			   AND $matches[1]{2} != 'x'
			   AND $matches[1]{2} != 'y';
	}

?>
