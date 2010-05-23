<?php
/**
 * @package pragyan
 * @copyright (c) 2008 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

 function bbeditor($action,$subject="",$text="")
 {

 	global $urlRequestRoot,$sourceFolder,$moduleFolder,$cmsFolder;
$css=$urlRequestRoot."/".$cmsFolder."/".$moduleFolder."/forum/images/styles.css";
$js=$urlRequestRoot."/".$cmsFolder."/".$moduleFolder."/forum/images/jscript.js";
$imgpath=$urlRequestRoot."/".$cmsFolder."/".$moduleFolder."/forum/";
$editor=<<<FORUM


<link rel="stylesheet" href="$css" type="text/css" />
<script type="text/javascript" languauge="javascript" src="$js"></script>

<div id="bbeditor">
	<table cellpadding="0" cellspacing="0" width="100%">
	<tbody><tr>

	<td class="main-bg" valign="top">
	<br><table cellpadding="0" cellspacing="0" width="100%">
	<tbody><tr>
	<td class="capmain">Post Thread</td>
	</tr>
	<tr>
	<td class="main-body">
	<form name="inputform" method="post" action="$action" enctype="multipart/form-data">
	<table class="tbl-border" cellpadding="0" cellspacing="0" width="100%">
	<tbody><tr>
	<td>
	<table border="0" cellpadding="0" cellspacing="1" width="100%">
	<tbody><tr>
	<td class="tbl2" width="145">Subject*</td>
	<td class="tbl2"><input name="subject" value="RE:$subject" class="textbox" maxlength="255" style="width: 250px;" type="text"></td>
	</tr>
	<tr>
	<td class="tbl2" valign="top" width="145">Message*</td>
	<td class="tbl1"><textarea name="message" cols="80" rows="15" class="textbox" >$text</textarea></td>
	</tr>
	<tr>
	<td class="tbl2" width="145">&nbsp;</td>
	<td class="tbl2">
	<input value="b" class="button" style="font-weight: bold; width: 25px;" onclick="addText('message', '[b]', '[/b]');" type="button">
	<input value="i" class="button" style="font-style: italic; width: 25px;" onclick="addText('message', '[i]', '[/i]');" type="button">
	<input value="u" class="button" style="text-decoration: underline; width: 25px;" onclick="addText('message', '[u]', '[/u]');" type="button">
	<input value="url" class="button" style="width: 30px;" onclick="addText('message', '[url]', '[/url]');" type="button">
	<input value="mail" class="button" style="width: 35px;" onclick="addText('message', '[mail]', '[/mail]');" type="button">
	<input value="img" class="button" style="width: 30px;" onclick="addText('message', '[img]', '[/img]');" type="button">
	<input value="center" class="button" style="width: 45px;" onclick="addText('message', '[center]', '[/center]');" type="button">
	<input value="small" class="button" style="width: 40px;" onclick="addText('message', '[small]', '[/small]');" type="button">
	<input value="code" class="button" style="width: 40px;" onclick="addText('message', '[code]', '[/code]');" type="button">
	<input value="quote" class="button" style="width: 45px;" onclick="addText('message', '[quote]', '[/quote]');" type="button">
	</td>
	</tr>
	<tr>
	<td class="tbl2" width="145">&nbsp;</td>
	<td class="tbl1">
	Font Color: <select name="bbcolor" class="textbox" style="width: 90px;" onchange="addText('message', '[color=' + this.options[this.selectedIndex].value + ']', '[/color]');this.selectedIndex=0;">
	<option value="">Default</option>
	<option value="maroon" style="color: maroon;">Maroon</option>
	<option value="red" style="color: red;">Red</option>
	<option value="orange" style="color: orange;">Orange</option>
	<option value="brown" style="color: brown;">Brown</option>
	<option value="yellow" style="color: yellow;">Yellow</option>
	<option value="green" style="color: green;">Green</option>
	<option value="lime" style="color: lime;">Lime</option>
	<option value="olive" style="color: olive;">Olive</option>
	<option value="cyan" style="color: cyan;">Cyan</option>
	<option value="blue" style="color: blue;">Blue</option>
	<option value="navy" style="color: navy;">Navy Blue</option>
	<option value="purple" style="color: purple;">Purple</option>
	<option value="violet" style="color: violet;">Violet</option>
	<option value="black" style="color: black;">Black</option>
	<option value="gray" style="color: gray;">Gray</option>
	<option value="silver" style="color: silver;">Silver</option>
	<option value="white" style="color: white;">White</option>
	</select>
	</td>
	</tr>
	<tr>
	<td class="tbl2" width="145">&nbsp;</td>
	<td class="tbl2">
	<img src="$imgpath/images/smile.gif" alt="smiley" onclick="insertText('message', ':)');">
	<img src="$imgpath/images/wink.gif" alt="smiley" onclick="insertText('message', ';)');">
	<img src="$imgpath/images/frown.gif" alt="smiley" onclick="insertText('message', ':|');">
	<img src="$imgpath/images/sad.gif" alt="smiley" onclick="insertText('message', ':(');">
	<img src="$imgpath/images/shock.gif" alt="smiley" onclick="insertText('message', ':o');">
	<img src="$imgpath/images/pfft.gif" alt="smiley" onclick="insertText('message', ':p');">
	<img src="$imgpath/images/cool.gif" alt="smiley" onclick="insertText('message', 'B)');">
	<img src="$imgpath/images/grin.gif" alt="smiley" onclick="insertText('message', ':D');">
	<img src="$imgpath/images/angry.gif" alt="smiley" onclick="insertText('message', ':@');">

	</td>
	</tr>
	<tr>
	<td class="tbl2" valign="top" width="145">Options</td>
	<td class="tbl1">
FORUM;
global $userId;
global $pageId;
$moderate = getPermissions($userId,$pageId,'moderate','forum');
if ($moderate) {
		$editor .= '<input name="sticky" value="1" type="checkbox"> Make this Thread Sticky(Moderators only can post stickies!)<br>';
}
$editor.=<<<FORUM

	</td>
	</tr>
	<tr><td>* = Mandatory Field</td></tr>
	</tbody></table>
	</td>
	</tr>
	</tbody></table>
	<table cellpadding="0" cellspacing="0" width="100%">
	<tbody><tr>
	<td colspan="2" class="tbl1" align="center">
	<input name="preview" value="Preview " class="button" type="submit">
	<input name="post" value="Post " class="button" type="submit">
	</td>
	</tr>
	</tbody></table>
	</form>
	</td>
	</tr>
	</tbody></table>
	</td>

	</tr>
	</tbody></table>
</div>

FORUM;
return $editor;

 }



