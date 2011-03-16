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

// htmlentities is too agressive so we use this function
function phpentities($text) {
	$search = array("&", "\"", "'", "\\", "<", ">");
	$replace = array("&amp;", "&quot;", "&#39;", "&#92;", "&lt;", "&gt;");
	$text = str_replace($search, $replace, $text);
	return $text;
}
// Parse smiley bbcode into HTML images
global $urlRequestRoot,$sourceFolder,$moduleFolder,$cmsFolder;
$imgpath=$urlRequestRoot."/".$cmsFolder."/".$moduleFolder."/forum/images/smileys/";
define("IMAGES","$imgpath");
function parsesmileys($message) {
	global $IMAGES;
	$smiley = array(
		"#\:\)#si" => "<img title='Smile' src='".IMAGES."smile.gif' alt=':)'>",
		"#\;\)#si" => "<img title='Wink' src='".IMAGES."wink.gif' alt=';)'>",
		"#\:\(#si" => "<img title='Sad' src='".IMAGES."sad.gif' alt=':('>",
		"#\:\|#si" => "<img title='Frown' src='".IMAGES."frown.gif' alt=':|'>",
		"#\:o#si" => "<img title='Shock' src='".IMAGES."shock.gif' alt=':o'>",
		"#\:p#si" => "<img title='lol'src='".IMAGES."pfft.gif' alt=':P'>",
		"#b\)#si" => "<img title='Cool'src='".IMAGES."cool.gif' alt='B)'>",
		"#\:d#si" => "<img title='Grin' src='".IMAGES."grin.gif' alt=':D'>",
		"#\:@#si" => "<img title='Angry' src='".IMAGES."angry.gif' alt=':@'>",
	);
	foreach($smiley as $key=>$smiley_img) $message = preg_replace($key, $smiley_img, $message);
	return $message;
}
function parsenewline($text) {
		$text = preg_replace('#\<br/\>#si', '[br/]', $text);
		$text = preg_replace('#\<br /\>#si', '[br /]', $text);
		return $text;
}
// Show smiley icons in comments, forum and other post pages
function displaysmileys($textarea) {
	$smiles = "";
	$smileys = array (
		":)" => "smile.gif",
		";)" => "wink.gif",
		":|" => "frown.gif",
		":(" => "sad.gif",
		":o" => "shock.gif",
		":p" => "pfft.gif",
		"B)" => "cool.gif",
		":D" => "grin.gif",
		":@" => "angry.gif",
	);
	foreach($smileys as $key=>$smiley) $smiles .= "<img src='".IMAGES."$smiley' alt='smiley' onClick=\"insertText('$textarea', '$key');\">\n";
	return $smiles;
}

// Parse bbcode into HTML code
function parseubb($text) {
	$text = preg_replace('#\[b\](.*?)\[/b\]#si', '<b>\1</b>', $text);
	$text = preg_replace('#\[i\](.*?)\[/i\]#si', '<i>\1</i>', $text);
	$text = preg_replace('#\[u\](.*?)\[/u\]#si', '<u>\1</u>', $text);
	$text = preg_replace('#\[center\](.*?)\[/center\]#si', '<center>\1</center>', $text);
	$text = preg_replace('#\[br/\]#si', '<br/>', $text);
	$text = preg_replace('#\[br /\]#si', '<br />', $text);
	$text = preg_replace('#\[url\]([\r\n]*)(http://|ftp://|https://|ftps://)([^\s\'\";\+]*?)([\r\n]*)\[/url\]#si', '<a href=\'\2\3\' target=\'_blank\'>\2\3</a>', $text);
	$text = preg_replace('#\[url\]([\r\n]*)([^\s\'\";\+]*?)([\r\n]*)\[/url\]#si', '<a href=\'http://\2\' target=\'_blank\'>\2</a>', $text);
	$text = preg_replace('#\[url=([\r\n]*)(http://|ftp://|https://|ftps://)([^\s\'\";\+]*?)\](.*?)([\r\n]*)\[/url\]#si', '<a href=\'\2\3\' target=\'_blank\'>\4</a>', $text);
	$text = preg_replace('#\[url=([\r\n]*)([^\s\'\";\+]*?)\](.*?)([\r\n]*)\[/url\]#si', '<a href=\'http://\2\' target=\'_blank\'>\3</a>', $text);

	$text = preg_replace('#\[mail\]([\r\n]*)([^\s\'\";:\+]*?)([\r\n]*)\[/mail\]#si', '<a href=\'mailto:\2\'>\2</a>', $text);
	$text = preg_replace('#\[mail=([\r\n]*)([^\s\'\";:\+]*?)\](.*?)([\r\n]*)\[/mail\]#si', '<a href=\'mailto:\2\'>\3</a>', $text);

	$text = preg_replace('#\[small\](.*?)\[/small\]#si', '<span class=\'small\'>\1</span>', $text);
	$text = preg_replace('#\[color=(black|blue|brown|cyan|gray|green|lime|maroon|navy|olive|orange|purple|red|silver|violet|white|yellow)\](.*?)\[/color\]#si', '<span style=\'color:\1\'>\2</span>', $text);

	$text = preg_replace('#\[flash width=([0-9]*?) height=([0-9]*?)\]([^\s\'\";:\+]*?)(\.swf)\[/flash\]#si', '<object classid=\'clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\' codebase=\'http://active.macromedia.com/flash6/cabs/swflash.cab#version=6,0,0,0\' id=\'\3\4\' width=\'\1\' height=\'\2\'><param name=movie value=\'\3\4\'><param name=\'quality\' value=\'high\'><param name=\'bgcolor\' value=\'#ffffff\'><embed src=\'\3\4\' quality=\'high\' bgcolor=\'#ffffff\' width=\'\1\' height=\'\2\' type=\'application/x-shockwave-flash\' pluginspage=\'http://www.macromedia.com/go/getflashplayer\'></embed></object>', $text);
	$text = preg_replace("#\[img\]((http|ftp|https|ftps)://)(.*?)(\.(jpg|jpeg|gif|png|JPG|JPEG|GIF|PNG))\[/img\]#sie","'<img src=\'\\1'.str_replace(array('.php','?','&','='),'','\\3').'\\4\' style=\'border:0px\'>'",$text);

	$qcount = substr_count($text, "[quote]"); $ccount = substr_count($text, "[code]");
	for ($i=0;$i < $qcount;$i++) $text = preg_replace('#\[quote\](.*?)\[/quote\]#si', '<div class=\'quote\'>\1</div>', $text);
	for ($i=0;$i < $ccount;$i++) $text = preg_replace('#\[code\](.*?)\[/code\]#si', '<div class=\'quote\' style=\'width:400px;white-space:nowrap;overflow:auto\'><code style=\'white-space:nowrap\'>\1<br><br><br></code></div>', $text);

	$text = descript($text,false);

	return $text;
}

// This function sanitises news & article submissions
function descript($text,$striptags=true) {
	// Convert problematic ascii characters to their true values
	$search = array("40","41","58","65","66","67","68","69","70",
		"71","72","73","74","75","76","77","78","79","80","81",
		"82","83","84","85","86","87","88","89","90","97","98",
		"99","100","101","102","103","104","105","106","107",
		"108","109","110","111","112","113","114","115","116",
		"117","118","119","120","121","122"
		);
	$replace = array("(",")",":","a","b","c","d","e","f","g","h",
		"i","j","k","l","m","n","o","p","q","r","s","t","u",
		"v","w","x","y","z","a","b","c","d","e","f","g","h",
		"i","j","k","l","m","n","o","p","q","r","s","t","u",
		"v","w","x","y","z"
		);
	$entities = count($search);
	for ($i=0;$i < $entities;$i++) $text = preg_replace("#(&\#)(0*".$search[$i]."+);*#si", $replace[$i], $text);
	// the following is based on code from bitflux (http://blog.bitflux.ch/wiki/)
	// Kill hexadecimal characters completely
	$text = preg_replace('#(&\#x)([0-9A-F]+);*#si', "", $text);
	// remove any attribute starting with "on" or xmlns
	$text = preg_replace('#(<[^>]+[\\"\'\s])(onmouseover|onmousedown|onmouseup|onmouseout|onmousemove|onclick|ondblclick|onload|xmlns)[^>]*>#iU', ">", $text);
	// remove javascript: and vbscript: protocol
	$text = preg_replace('#([a-z]*)=([\`\'\"]*)script:#iU', '$1=$2nojscript...', $text);
	$text = preg_replace('#([a-z]*)=([\`\'\"]*)javascript:#iU', '$1=$2nojavascript...', $text);
	$text = preg_replace('#([a-z]*)=([\'\"]*)vbscript:#iU', '$1=$2novbscript...', $text);
        //<span style="width: expression(alert('Ping!'));"></span> (only affects ie...)
	$text = preg_replace('#(<[^>]+)style=([\`\'\"]*).*expression\([^>]*>#iU', "$1>", $text);
	$text = preg_replace('#(<[^>]+)style=([\`\'\"]*).*behaviour\([^>]*>#iU', "$1>", $text);
	if ($striptags) {
		do {
	        	$thistext = $text;
			$text = preg_replace('#</*(applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base)[^>]*>#i', "", $text);
		} while ($thistext != $text);
	}
	return $text;
}


// Replace offensive words with the defined replacement word
function censorwords($text) {
	global $settings;
	if ($settings['bad_words_enabled'] == "1" && $settings['bad_words'] != "" ) {
		$word_list = explode("\r\n", $settings['bad_words']);
		for ($i=0;$i < count($word_list);$i++) {
			if ($word_list[$i] != "") $text = preg_replace("/".$word_list[$i]."/si", $settings['bad_word_replace'], $text);
		}
	}
	return $text;
}

// Universal page pagination function by CrappoMan
function makepagenav($start,$count,$total,$range=0,$link=""){
	global $locale;
	if ($link == "") $link = FUSION_SELF."?";
	$res="";
	$pg_cnt=ceil($total / $count);
	if ($pg_cnt > 1) {
		$idx_back = $start - $count;
		$idx_next = $start + $count;
		$cur_page=ceil(($start + 1) / $count);
		$res.="<table cellspacing='1' cellpadding='1' border='0' class='tbl-border'>\n<tr>\n";
		$res.="<td class='tbl2'><span class='small'>".$locale['052']."$cur_page".$locale['053']."$pg_cnt</span></td>\n";
		if ($idx_back >= 0) {
			if ($cur_page > ($range + 1)) $res.="<td class='tbl2'><a class='small' href='$link"."rowstart=0'>&lt;&lt;</a></td>\n";
			$res.="<td class='tbl2'><a class='small' href='$link"."rowstart=$idx_back'>&lt;</a></td>\n";
		}
		$idx_fst=max($cur_page - $range, 1);
		$idx_lst=min($cur_page + $range, $pg_cnt);
		if ($range==0) {
			$idx_fst = 1;
			$idx_lst=$pg_cnt;
		}
		for($i=$idx_fst;$i<=$idx_lst;$i++) {
			$offset_page=($i - 1) * $count;
			if ($i==$cur_page) {
				$res.="<td class='tbl1'><span class='small'><b>$i</b></span></td>\n";
			} else {
				$res.="<td class='tbl1'><a class='small' href='$link"."rowstart=$offset_page'>$i</a></td>\n";
			}
		}
		if ($idx_next < $total) {
			$res.="<td class='tbl2'><a class='small' href='$link"."rowstart=$idx_next'>&gt;</a></td>\n";
			if ($cur_page < ($pg_cnt - $range)) $res.="<td class='tbl2'><a class='small' href='$link"."rowstart=".($pg_cnt-1)*$count."'>&gt;&gt;</a></td>\n";
		}
		$res.="</tr>\n</table>\n";

	}
	return $res;
}

