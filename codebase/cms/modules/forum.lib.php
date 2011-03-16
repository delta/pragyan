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
/*
 * 
 * 
 * 
 *
 */

class forum implements module {
	private $userId;
	private $moduleComponentId;
	private $action;

	public function getHtml($gotuid, $gotmoduleComponentId, $gotaction) {
		$this->userId = $gotuid;
		$this->moduleComponentId = $gotmoduleComponentId;
		$this->action = $gotaction;

		if ($gotaction == 'view')
			return $this->actionView();
		if ($gotaction == 'post')
			return $this->actionPost();
		if ($gotaction == 'moderate')
			return $this->actionModerate();
		if ($gotaction == 'forumsettings')
			return $this->actionForumsettings();		

	}

	/**Returns news array
	 * @param $moduleCompId	ModuleComponenetId of the news array
	 * @return An array of the form  :  a[0][element] = data
	 * 									where element is title, description and link
	 */

	/**Returns the total posts
	 * @param $userID : UserID
	 * @return $posts : An integer value representing the (total posts+threads) created by the given userID
	 */
	public function getTotalPosts($userID) {
		$q1 = "SELECT * FROM `forum_threads` WHERE `forum_thread_user_id`='$userID' AND `page_modulecomponentid`='$this->moduleComponentId'";
		$res1 = mysql_query($q1);
		$posts = mysql_num_rows($res1);
		$q2 = "SELECT * FROM `forum_posts` WHERE `forum_post_user_id`='$userID' AND `page_modulecomponentid`='$this->moduleComponentId'";
		$res2 = mysql_query($q2);
		$posts += mysql_num_rows($res2);
		return $posts;
	}

	public function getLastLogin($userId) {
		$query = "SELECT `user_lastlogin` FROM `" . MYSQL_DATABASE_PREFIX . "users` WHERE `user_id` = '$userId'";
		$result = mysql_query($query);
		$row = mysql_fetch_row($result);
		return $row[0];
	}
	/**
	 * Determines the Registration Date of a user, given his/her User ID
	 * @param $userID UserID of the user, whose Registration Date is to be determined
	 * @return $date representing the Registration Date of the user, null representing failure
	 */
	 function getRegDateFromUserID($userID) {
		if ($userID == 0)
			return 0;
		$query = 'SELECT `user_regdate` FROM `' . MYSQL_DATABASE_PREFIX . "users` WHERE `user_id` = '$userID'";
		$result = mysql_query($query);
		$row = mysql_fetch_row($result);
		return $row[0];
	}
	public function actionForumsettings(){
		$table_name = "forum_threads";
		$table1_name = "forum_posts";
		$table2_name = "forum_module";
		$forumHtml ='';
		if(isset($_POST['mod_permi']))
		{
			if($_POST['forum_desc'])
			{
				$forum_description = addslashes(htmlspecialchars($_POST['forum_desc']));
				if($_POST['del_post'] == "allow")
						$del_post=1;
				else
						$del_post=0;
				if($_POST['like_post'] == "allow")
						$like_post=1;
				else
						$like_post=0;
			}
			else
			{
				$forum_description = "";
			}
			if($_POST['mod_permi']=="public")
				{
					$access_level = 0;
					$approve = 1;
					$q1 = "UPDATE `$table_name` SET `forum_post_approve`='$approve' WHERE `page_modulecomponentid`='$this->moduleComponentId'";
					$res1 = mysql_query($q1);
					$q2 = "UPDATE `$table1_name` SET `forum_post_approve`='$approve' WHERE `page_modulecomponentid`='$this->moduleComponentId'";
					$res2 = mysql_query($q2);
				}
			else
				{$access_level = 1;}
			$pageId=getPageIdFromModuleComponentId("forum",$this->moduleComponentId);
			$q = "UPDATE `$table2_name` SET `forum_moderated`='$access_level',  " .
					"`forum_description`='$forum_description',`allow_delete_posts`='$del_post',`allow_like_posts`='$like_post' WHERE `page_modulecomponentid`='$this->moduleComponentId' LIMIT 1";
			$res = mysql_query($q) or displayerror(mysql_error() . "Update failed L:113");
			displayinfo("Forum settings updated successfully!");
		}
				$query = "SELECT * FROM `$table2_name` WHERE `page_modulecomponentid`='$this->moduleComponentId' LIMIT 1";
				$result = mysql_query($query);
				$rows = mysql_fetch_array($result);
				$forum_description = stripslashes($rows['forum_description']);
				$forum_moderated = $rows['forum_moderated'];
				$allow_delete_posts = $rows['allow_delete_posts'];
				$allow_like_posts = $rows['allow_like_posts'];
				$moderatedselected = "";
				$publicselected = "";
				$allowselected = "";
				$dontallowselected = "";
				$lallowselected = "";
				$ldontallowselected = "";
				
				if($forum_moderated==1) $moderatedselected = 'selected="selected"';
				else 					$publicselected = 'selected="selected"';
				if($allow_delete_posts==1) $allowselected = 'selected="selected"';
				else 					$dontallowselected = 'selected="selected"';
				if($allow_like_posts==1) $lallowselected = 'selected="selected"';
				else $ldontallowselected = 'selected="selected"';
				global $ICONS;
				$forumHtml .=<<<PRE
				<fieldset>
				<legend>{$ICONS['Forum Settings']['small']}Forum Settings</legend>
								<form method="post" name="forum_access" action="./+forumsettings">
				<table><tr><td>
				Choose Forum Access Level  </td><td><select name="mod_permi" style="width:100px;">
				<option value="moderated" $moderatedselected >Moderated</option>
				<option value="public" $publicselected >Public</option>
				</select></td>
				</tr>
				<tr><td>
				Allow users to Delete their posts  </td><td><select name="del_post" style="width:100px;">
				<option value="allow" $allowselected >Allow</option>
				<option value="dontallow" $dontallowselected >Don't Allow</option>
				</select></td>
				</tr>
				<tr><td>
				Allow users to Like posts  </td><td><select name="like_post" style="width:100px;">
				<option value="allow" $lallowselected >Allow</option>
				<option value="dontallow" $ldontallowselected >Don't Allow</option>
				</select></td>
				</tr>
				<tr><td>
				Enter New Forum Description </td><td><textarea name="forum_desc" cols="50" rows="5" class="textbox" >$forum_description</textarea></td>
				</tr></table>
				<input type="submit" value="submit">
				</form>
				</fieldset>
PRE;
		return $forumHtml;
	}
	
	public function actionModerate() {
		global $urlRequestRoot, $moduleFolder, $cmsFolder,$templateFolder,$sourceFolder;
		$temp = $urlRequestRoot . "/" . $cmsFolder . "/" . $moduleFolder . "/forum/images";
		require_once ("$sourceFolder/$moduleFolder/forum/bbeditor.php");
		require_once ("$sourceFolder/$moduleFolder/forum/bbparser.php");
		$js=$urlRequestRoot."/".$cmsFolder."/".$moduleFolder."/forum/images/jscript.js";
		$userId = $this->userId;
		$table_name = "forum_threads";
		$table1_name = "forum_posts";
		$table2_name = "forum_module";
		$templatesImageFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/".TEMPLATE;
		if(isset($_GET['subaction'])){
		if ($_GET['subaction'] == "approve" || $_GET['subaction'] == "disapprove") {
			if ($_GET['subaction'] == "approve")
				$approval = 1;
			else
				$approval = 0;
			if (!isset ($_GET['post_id'])) {
				$thread_id = escape($_GET['thread_id']);
				$query = "UPDATE `$table_name` SET `forum_post_approve`='$approval' WHERE `forum_thread_id`='$thread_id' AND `page_modulecomponentid`='$this->moduleComponentId' LIMIT 1";
				$result = mysql_query($query);
			} else {
				$thread_id = escape($_GET['forum_id']);
				$post_id = escape($_GET['post_id']);
				$query = "UPDATE `$table1_name` SET `forum_post_approve`='$approval' WHERE `forum_thread_id`='$thread_id' AND `forum_post_id`='$post_id' AND `page_modulecomponentid`='$this->moduleComponentId' LIMIT 1";
				$result = mysql_query($query);
			}
			if (!$result)
				displayerror("Could not perform the desired action(approve/disapprove)!");
		}
		if ($_GET['subaction'] == "delete") {
			if (!isset ($_GET['post_id'])) {
				$thread_id = escape($_GET['thread_id']);
				$query = "DELETE FROM `$table_name` WHERE `forum_thread_id`='$thread_id' AND `page_modulecomponentid`='$this->moduleComponentId' LIMIT 1";
				$result = mysql_query($query);
				$query1 = "DELETE FROM `forum_posts` WHERE `forum_thread_id`='$thread_id' AND `page_modulecomponentid`='$this->moduleComponentId'";
				$result1 = mysql_query($query1);
				$query2 = "DELETE FROM `forum_like` WHERE `forum_thread_id`='$thread_id' AND `page_modulecomponentid`='$this->moduleComponentId'";
				$result2 = mysql_query($query2);
				if (!$result)
					displayerror("Could not perform the delete operation!");
				else
					displayinfo("Successfully deleted the Thread!");
			} 
			else {
				$thread_id = escape($_GET['forum_id']);
				$post_id = escape($_GET['post_id']);
				$query1 = "DELETE FROM `forum_posts` WHERE `forum_thread_id`='$thread_id' AND `forum_post_id`='$post_id' AND `page_modulecomponentid`='$this->moduleComponentId' LIMIT 1";
				$result1 = mysql_query($query1);
				$query1 = "DELETE FROM `forum_like` WHERE `forum_thread_id`='$thread_id' AND `forum_post_id`='$post_id' AND `page_modulecomponentid`='$this->moduleComponentId' LIMIT 1";
				$result1 = mysql_query($query1);
				if (!$result1)
					displayerror("Could not perform the delete operation!");
				else
					displayinfo("Successfully deleted the Post!");
			}
		}
		}
		if (!isset ($_GET['forum_id'])) {
			$query = "SELECT * FROM `$table_name` WHERE `page_modulecomponentid`='" . $this->moduleComponentId . "" .
					"' AND `forum_thread_category`='general' ORDER BY `forum_thread_lastpost_date` DESC";
			$result = mysql_query($query);
			$query1 = "SELECT * FROM `$table_name` WHERE `page_modulecomponentid`='" . $this->moduleComponentId . "" .
					"' AND `forum_thread_category`='sticky' ORDER BY `forum_thread_datetime` DESC";
			$result1 = mysql_query($query1);
			$num_rows = mysql_num_rows($result);
			$num_rows1 = mysql_num_rows($result1);
			global $ICONS;
			if ($result) {
			
				$action = "+post&subaction=create_thread";
				$moderate =<<<PRE
		<link rel="stylesheet" href="$temp/styles.css" type="text/css" />
		<fieldset><legend>{$ICONS['Forum Moderate']['small']}Moderate Forum</legend>
		<p align="left"><a href="$action" style="color:#0F5B96"><img title="New Thread" src="$temp/newthread.gif" /></a></p>
        <table width="100%" id="forum" align="center" cellpadding="3" cellspacing="1">
        <tr>
        <td class="forumTableHeader" colspan="4"><strong>Subject</strong><br /></td>
        <td class="forumTableHeader"><strong>Views</strong></td>
        <td class="forumTableHeader"><strong>Replies</strong></td>
        <td class="forumTableHeader"><strong>Last Post</strong></td>
        </tr>
        <tr>
PRE;
				if ($result1 && $num_rows1 > 0) {
					for ($i = 1; $i <= $num_rows1; $i++) {
						$rows = mysql_fetch_array($result1);
						$query2 = "SELECT `forum_post_id` FROM `$table1_name` WHERE `forum_thread_id`='" . $rows['forum_thread_id'] . "' AND `forum_post_approve`='1' AND `page_modulecomponentid`='$this->moduleComponentId'";
						$result2 = mysql_query($query2);
						$reply_count = mysql_num_rows($result2);
						$topic = ucfirst(parseubb(parsesmileys(htmlspecialchars($rows['forum_thread_topic']))));
						$name = ucfirst(getUserName($rows['forum_thread_user_id']));
						$last_post_author = ucfirst(getUserName($rows['forum_thread_last_post_userid']));
						if ($rows['forum_post_approve'] == 0)
							{
								$text = "Approve";
								$img = "like.gif";
							}
						else
							{
								$text = "Disapprove";
								$img = "unlike.gif";
							}
						$subaction = strtolower($text);
						$moderate .=<<<PRE
		        <tr>
		        <td class="forumThreadRow"  width="3%"><a href="+moderate&subaction=delete&thread_id=$rows[forum_thread_id]">
		        <img src="$temp/delete1.gif" /></a></td>
		        <td class="forumThreadRow"  width="3%"><a href="+moderate&subaction=$subaction&thread_id=$rows[forum_thread_id]">
		        <img src="$temp/$img" /></a></td>
		        <td class="forumThreadRow"  width="3%"><img src="$temp/sticky.gif" /></td>
		        <td class="forumThreadRow"  width="41%"><a href="+moderate&forum_id=$rows[forum_thread_id]"> $topic </a><br /><small>by <b> $name </a></b>
		        on $rows[forum_thread_datetime] </small></td>
		        <td class="forumThreadRow"  width="10%"> $rows[forum_thread_viewcount] </td>
		        <td class="forumThreadRow"  width="10%"> $reply_count </td>
		        <td class="forumThreadRow"  width="30%"><small>by <b> $last_post_author </a></b> on $rows[forum_thread_lastpost_date] </small></td>
		        </tr>
PRE;

					}
				}
				if ($num_rows < 1)
					$moderate .= "<tr><td colspan=\"5\" class='forumTableRow'><strong>No Post</strong></td></tr>";
				for ($i = 1; $i <= $num_rows; $i++) {
					$rows = mysql_fetch_array($result);
					if($userId>0 && ($_SESSION['last_to_last_login_datetime']<$rows['forum_thread_lastpost_date']))
						$img_src = "thread_new.gif";
					else
						$img_src = "thread_hot.gif";

					$query1 = "SELECT `forum_post_id` FROM `$table1_name` WHERE `forum_thread_id`='" . $rows['forum_thread_id'] . "' AND `forum_post_approve`='1'";
					$result1 = mysql_query($query1);
					$reply_count = mysql_num_rows($result1);
					$topic = ucfirst(parseubb(parsesmileys($rows['forum_thread_topic'])));
					$name = ucfirst(getUserName($rows['forum_thread_user_id']));
					$last_post_author = ucfirst(getUserName($rows['forum_thread_last_post_userid']));
						if ($rows['forum_post_approve'] == 0)
							{
								$text = "Approve";
								$img = "like.gif";
							}
						else
							{
								$text = "Disapprove";
								$img = "unlike.gif";
							}
					$subaction = strtolower($text);
					$moderate .=<<<PRE
        <tr>
        <td class="forumThreadRow"  width="3%"><a href="+moderate&subaction=delete&thread_id=$rows[forum_thread_id]"><img src="$temp/delete1.gif" />
        </a></td>
		<td class="forumThreadRow"  width="3%"><a href="+moderate&subaction=$subaction&thread_id=$rows[forum_thread_id]"><img src="$temp/$img" />
		</a></td>
        <td class="forumThreadRow"  width="3%"><img src="$temp/$img_src" /></td>
        <td class="forumThreadRow"  width="41%"><a href="+moderate&forum_id=$rows[forum_thread_id]"> $topic </a><br /><small>by <b> $name </a></b>
        on $rows[forum_thread_datetime] </small></td>
        <td class="forumThreadRow"  width="10%"> $rows[forum_thread_viewcount] </td>
        <td class="forumThreadRow"  width="10%"> $reply_count </td>
        <td class="forumThreadRow"  width="30%"><small>by <b> $last_post_author </a></b> on $rows[forum_thread_lastpost_date] </small></td>
        </tr>
PRE;
				}
				$moderate .= '</table><br />
				        <p align="left"><img alt="" src="' . $temp . '/like.gif" align=left> &nbsp;- To Approve Threads.<br /><br />' .
				        		'<img alt="" src="' . $temp . '/unlike.gif" align=left> &nbsp;- To Disapprove Threads.<br /><br />' .
				        				'<img alt="" src="' . $temp . '/sticky.gif" align=left> &nbsp;- Sticky Threads.<br /><br />' .
				        						'<img alt="" src="' . $temp . '/thread_new.gif" align=left>' .
				        								' &nbsp;- Topic with new posts since last visit.<br /><br />' .
				        								'<img alt="" src="' . $temp . '/thread_hot.gif" align=left>' .
				        										'&nbsp;- Topic with no new posts since last visit. </p><hr /></fieldset>';
			}
			return $moderate;
		} else {
			$q = "SELECT * FROM `forum_module` WHERE `page_modulecomponentid`='$this->moduleComponentId' LIMIT 1";
			$r = mysql_query($q) or displayerror(mysql_error() . "Moderate failed L:343");
			$r = mysql_fetch_array($r);
			$forum_id = escape($_GET['forum_id']); //Parent Thread ID
			$sql = "SELECT * FROM `$table_name` WHERE `forum_thread_id`='$forum_id' AND `page_modulecomponentid`='$this->moduleComponentId' LIMIT 1";
			$result1 = mysql_query($sql);
			$rows = mysql_fetch_array($result1);
			$forum_topic = ucfirst(parseubb(parsesmileys($rows['forum_thread_topic'])));
			$forum_detail = parseubb(parsesmileys($rows['forum_detail']));
			$name = ucfirst(getUserName($rows['forum_thread_user_id']));
			$posts = $this->getTotalPosts($rows['forum_thread_user_id']);
			$reg_date = $this->getRegDateFromUserID($rows['forum_thread_user_id']);
			$count='0';
			if ($rows['forum_post_approve'] == 0)
				{
					$text = "Approve";
					$img = "like.gif";
				}
			else
				{
					$text = "Disapprove";
					$img = "unlike.gif";
				}
			$subaction = strtolower($text);
			$postpart =<<<PRE
		<link rel="stylesheet" href="$temp/styles.css" type="text/css" />
        <p align="left"><a href="+post&subaction=post_reply&thread_id=$forum_id"><img title="Reply" src="$temp/reply.gif" /></a>&nbsp;
        <a href="+post&subaction=create_thread"><img title="New Thread" src="$temp/newthread.gif" /></a><a href="+view"><img title="Go Back to Forum" src="$temp/go_back.gif" /></a>
		<table id="forum" width="100%" cellpadding="3" cellspacing="1" bordercolor="1" >
		<tr>
        <td class="forumThreadRow" rowspan="2"><a href="+moderate&subaction=delete&thread_id=$rows[forum_thread_id]">
        <img src="$temp/delete1.gif" /></a></td>
		<td class="forumThreadRow" rowspan="2"><a href="+moderate&subaction=$subaction&thread_id=$rows[forum_thread_id]">
		<img src="$temp/$img" /></a></td>
		<td class="forumThreadRow"><strong>$forum_topic</strong><br /><img src="$temp/post_icon.gif" />
		 <small">by $name on $rows[forum_thread_datetime] </small></td>
		<td class="forumThreadRow" rowspan="2"><strong>$name <br />
PRE;
			if ($userId > 0 && $name != "Anonymous") {
				if ($rows['forum_thread_user_id'] == $userId)
					$lastLogin = $_SESSION['last_to_last_login_datetime'];
				else
					$lastLogin = $this->getLastLogin($rows['forum_thread_user_id']);
					$moderator=getPermissions($rows['forum_thread_user_id'], getPageIdFromModuleComponentId("forum",$this->moduleComponentId), "moderate");
					if($moderator)$postpart .= "Moderator";else
				$postpart .= "Member";
				$content = 'content'.$count;
				$text = 'text'.$count;
				$postpart .= <<<PRE
						</strong><br /><br />
						<script type="text/javascript" languauge="javascript" src="$js"></script>
						<a  id="$text" href="javascript:toggle('$content','$text');" >Show Details</a><br />
						<div id="$content" style="display: none;"><small>Posts: $posts <br />Joined: $reg_date <br />Last Visit:
						$lastLogin </small></div>
PRE;
			}
			$postpart .=<<<PRE
	        </td>
	        </tr>
	        <tr>
	        <td class="forumThreadRow"><br /> $forum_detail </td>
	        </tr><tr><td class="blank" colspan="2"></td></tr>
PRE;

			$sql2 = "SELECT * FROM `$table1_name` WHERE `forum_thread_id`='$forum_id' AND `page_modulecomponentid`='$this->moduleComponentId' ORDER BY forum_post_id ASC";
			$result2 = mysql_query($sql2);
			while ($rows = mysql_fetch_array($result2)) {
				$count = $count + 1;
				$post_title = ucfirst(parseubb(parsesmileys($rows['forum_post_title'])));
				$post_content = (parseubb(parsesmileys($rows['forum_post_content'])));
				$name = ucfirst(getUserName($rows['forum_post_user_id']));
				$posts = $this->getTotalPosts($rows['forum_post_user_id']);
				$reg_date = $this->getRegDateFromUserID($rows['forum_post_user_id']);
				if ($rows['forum_post_approve'] == 0)
					{
						$text = "Approve";
						$img = "like.gif";
					}
				else
					{
						$text = "Disapprove";
						$img = "unlike.gif";
					}
				$subaction = strtolower($text);
				$postpart .=<<<PRE

	        <td class="forumThreadRow" rowspan="2" width="3%">
	        <a href="+moderate&subaction=delete&forum_id=$rows[forum_thread_id]&post_id=$rows[forum_post_id]"><img src="$temp/delete1.gif" /></a></td>
			<td class="forumThreadRow" rowspan="2" width="3%">
			<a href="+moderate&subaction=$subaction&forum_id=$rows[forum_thread_id]&post_id=$rows[forum_post_id]"><img src="$temp/$img" /></a></td>
	        <td class="forumThreadRow"><strong>Re:- $post_title </strong><br /><img src="$temp/post_icon.gif" />
	        <small">by $name on $rows[forum_post_datetime] <small>
PRE;
			if($r['allow_like_posts'] == 1){
					$likequery = "SELECT * from `forum_like` WHERE `forum_thread_id`='$rows[forum_thread_id]' AND `forum_post_id`='".$rows['forum_post_id']."' AND `like_status`='1' AND `page_modulecomponentid`='$this->moduleComponentId' ";
					$likeres = mysql_query($likequery) or displayerror(mysql_error() . "Moderate failed L:438");;
					$likeres = mysql_num_rows($likeres);
					$dlikequery = "SELECT * from `forum_like` WHERE `forum_thread_id`='$rows[forum_thread_id]' AND `forum_post_id`='".$rows['forum_post_id']."' AND `like_status`='0' AND `page_modulecomponentid`='$this->moduleComponentId' ";
					$dlikeres = mysql_query($dlikequery) or displayerror(mysql_error() . "Moderate failed L:441");
					$dlikeres = mysql_num_rows($dlikeres);
					$postpart .= '<br /><small> ' . $likeres . ' people like this post</small> &nbsp&nbsp&nbsp';
					$postpart .= '<small> ' . $dlikeres . ' people dislike this post</small><br />';
					}
			$postpart .= '</td><td class="forumThreadRow" rowspan="2" width="20%"><strong>$name<br />';

				if ($userId > 0 && $name != "Anonymous") {
					if ($rows['forum_post_user_id'] == $userId)
						$lastLogin = $_SESSION['last_to_last_login_datetime'];
					else
						$lastLogin = $this->getLastLogin($rows['forum_post_user_id']);
						$moderator=getPermissions($rows['forum_post_user_id'], getPageIdFromModuleComponentId("forum",$this->moduleComponentId), "moderate");
					if($moderator)$postpart .= "Moderator";else
					$postpart .= "Member";
					$content = 'content'.$count;
					$text = 'text'.$count;
					$postpart .= <<<PRE
						</strong><br /><br />
						<script type="text/javascript" languauge="javascript" src="$js"></script>
						<a id="$text" href="javascript:toggle('$content','$text');" >Show Details</a><br />
						<div id="$content" style="display: none;"><small>Posts: $posts <br />Joined: $reg_date <br />Last Visit:
						$lastLogin </small></div>
PRE;
				}
				$postpart .=<<<PRE
	        </td>
	        </tr>
	        <tr>
	        <td class="forumThreadRow"><br />$post_content</td>
	        </tr><tr><td class="blank" colspan="2"></td></tr>
PRE;
			}
			$postpart .='</table>';
			$query3 = "SELECT `forum_thread_viewcount` FROM `$table_name` WHERE forum_thread_id='$forum_id' AND `page_modulecomponentid`='$this->moduleComponentId' ";
			$result3 = mysql_query($query3);
			$rows = mysql_fetch_array($result3);
			$view = $rows['forum_thread_viewcount'];
			// count more value
			$addview = $view +1; 
			$query5 = "UPDATE `$table_name` SET `forum_thread_viewcount`='$addview' WHERE forum_thread_id='$forum_id' AND `page_modulecomponentid`='$this->moduleComponentId' LIMIT 1";
			$result5 = mysql_query($query5);
			$postpart .= '<br>
			        <p align="left"><a href="+post&subaction=post_reply&thread_id='.$forum_id.'"><img title="Reply" src="'.$temp.'/reply.gif" />' .
			        		'</a>&nbsp;<a href="+post&subaction=create_thread"><img title="New Thread" src="'.$temp.'/newthread.gif" /></a></p>';
			return $postpart;
		}
	}
		public function actionPost() {
		$userId = $this->userId;
		$i = 0;
		$action = '';
		foreach ($_GET as $var => $val) {
			if ($i == 1)
				$action .= "&" . $var . "=" . $val;
			if ($val == 'post') {
				$action .= "+" . $val;
				$i = 1;
			}
		}
		$table_name = "forum_threads";
		$table1_name = "forum_posts";
		$table2_name = "forum_module";
		if(isset($_GET['subaction']))
			$subaction = escape($_GET['subaction']);
		global $urlRequestRoot, $moduleFolder, $cmsFolder,$templateFolder,$sourceFolder;
		$temp = $urlRequestRoot . "/" . $cmsFolder . "/" . $moduleFolder . "/forum/images";
		require_once ("$sourceFolder/$moduleFolder/forum/bbeditor.php");
		require_once ("$sourceFolder/$moduleFolder/forum/bbparser.php");
		$q = "SELECT * FROM `$table2_name` WHERE `page_modulecomponentid`='$this->moduleComponentId' LIMIT 1";
		$res = mysql_query($q);
		$rows = mysql_fetch_array($res);
		$access_level = $rows['forum_moderated'];
		if ($access_level) {
			$approve = 0;
			$access = "moderated";
		} else {
			$approve = 1;
			$access = "public";
		}
		$moderator=getPermissions($this->userId, getPageIdFromModuleComponentId("forum",$this->moduleComponentId), "moderate");
		if($moderator) {
			$approve = 1;
		}
		if (isset ($_POST['post'])) {
			if (($subaction == "create_thread") ||( $subaction == "")) {

				if (!$_POST['subject'] || !$_POST['message']) {
					$editor = bbeditor();
					return "You did not fill all the fields!" . $editor;
				} else {
					$datetime = date("Y-m-d H:i:s");
					$message = $_POST['message'];
					$subject = addslashes(htmlspecialchars($_POST['subject']));
					$message = addslashes(htmlspecialchars(parsenewline(nl2br($message))));
					if (isset ($_POST['sticky'])&&($moderator))
						$category = "sticky";
					else
						$category = "general";
					$query="SELECT MAX(`forum_thread_id`) AS MAX FROM `forum_threads`";
					$result=mysql_query($query);
					$row1 = mysql_fetch_assoc($result);
					$threadid = $row1['MAX'] + 1;

					$sql = "INSERT INTO `$table_name`(`forum_thread_id` ,`page_modulecomponentid` ,`forum_thread_category` ,`forum_access_status` ," .
							"`forum_thread_topic` ,`forum_detail` ,`forum_thread_user_id` ,`forum_thread_datetime` ,`forum_post_approve` ," .
							"`forum_thread_viewcount` ,`forum_thread_last_post_userid` ,`forum_thread_lastpost_date`)" .
							" VALUES('$threadid', '$this->moduleComponentId', '$category', '$access', '$subject', '$message'," .
							" '$userId', '$datetime', '$approve', '1','$userId', '$datetime')";
					$result = mysql_query($sql) or displayerror(mysql_error() . "Create New Thread failed L:550");
					if ($result) {
						$sql1 = "SELECT * FROM `$table2_name` WHERE `page_modulecomponentid`='$this->moduleComponentId' LIMIT 1";
						$result1 = mysql_query($sql1);
						$rows1 = mysql_fetch_array($result1);
						$total_thread_count = $rows['total_thread_count'];
						// count more value
						$net_thread_count = $total_thread_count +1;
						$sql2 = "UPDATE `$table2_name` SET `total_thread_count`='$net_thread_count', `last_post_userid`='$userId'," .
								" `last_post_datetime`='$datetime' WHERE `page_modulecomponentid`='$this->moduleComponentId' LIMIT 1";
						$result2 = mysql_query($sql2);
						if(($access=="moderated")&& (!$moderator))
								displayinfo("You have successfully created a new thread.It will be published after getting the moderator's approval." .
										"<br />");
						else
								displayinfo("You have successfully created a new thread.<br />");
					} else {
						displayerror("Sorry! Your thread could not be created now. Please try again later!");
					}
					return $this->actionView();
				}
			} else
				if ($subaction == "post_reply") {
					if (!$_POST['subject'] || !$_POST['message']) {
						$editor = bbeditor();
						return "You did not fill all the fields!" . $editor;
					} else {
						$forum_id = escape($_GET['thread_id']);
						$datetime = date("Y-m-d H:i:s");
						$message = $_POST['message'];
						$subject = addslashes(htmlspecialchars($_POST['subject']));
						$message = addslashes(htmlspecialchars(parsenewline(nl2br($message))));
						$sql7 = "SELECT MAX(`forum_post_id`) AS Maxpost_id FROM `$table1_name` WHERE `forum_thread_id` = '$forum_id'";
						$res = mysql_query($sql7);
						$rows = mysql_fetch_array($res);
						// add + 1 to highest answer number and keep it in variable name "$Max_id". if there no answer yet set it = 1
						if ($rows) {
							$Max_id = $rows['Maxpost_id'] + 1;
						} else {
							$Max_id = 1;
						}
						$sql = "INSERT INTO `$table1_name`( `page_modulecomponentid` , `forum_thread_id` , `forum_post_id` , `forum_post_user_id` , `forum_post_title` , " .
								"`forum_post_content` , `forum_post_datetime` , `forum_post_approve` ) VALUES( '$this->moduleComponentId','$forum_id', '$Max_id'," .
								" '$userId', '$subject', '$message', '$datetime', '$approve')";
						$result = mysql_query($sql) or displayerror(mysql_error() . "Post failed L:594");
						if ($result) {
							$sql1 = "SELECT * FROM `$table_name` WHERE `page_modulecomponentid`='$this->moduleComponentId' AND `forum_thread_id`=$forum_id" .
									" LIMIT 1";
							$result1 = mysql_query($sql1);
							$rows1 = mysql_fetch_array($result1);
							$sql2 = "UPDATE `$table_name` SET  `forum_thread_last_post_userid`='$userId', " .
									"`forum_thread_lastpost_date`='$datetime' " .
									"WHERE `page_modulecomponentid`='$this->moduleComponentId' AND `forum_thread_id`='$forum_id' LIMIT 1";
							$result2 = mysql_query($sql2);
							$sql3 = "SELECT * FROM `$table2_name` WHERE `page_modulecomponentid`='$this->moduleComponentId' LIMIT 1";
							$result3 = mysql_query($sql3);
							$rows3 = mysql_fetch_array($result3);
							$sql4 = "UPDATE `$table2_name` SET  `last_post_userid`='$userId', " .
									"`last_post_datetime`='$datetime' WHERE `page_modulecomponentid`='$this->moduleComponentId' LIMIT 1";
							$result4 = mysql_query($sql4);
							if(($rows1['forum_access_status']=="moderated")&& (!$moderator))
								displayinfo("You have successfully posted your reply.It will be published after getting the moderator's approval." .
										"<br />");
							else
								displayinfo("You have successfully posted your reply!");
						} else {
							displayerror("Sorry! Your reply could not be posted now. Please try again later!");
						}
					{
						$forumHtml = '';
						$thread_id = $forum_id;
						$sql = "SELECT * FROM `$table_name` WHERE `forum_thread_id`='$thread_id' AND `page_modulecomponentid`='$this->moduleComponentId' LIMIT 1";
						$result1 = mysql_query($sql);
						$rows = mysql_fetch_array($result1);
						$threadUserId = $rows['forum_thread_user_id'];
						$forum_topic = parseubb(parsesmileys($rows['forum_thread_topic']));
						$forum_detail = parseubb(parsesmileys($rows['forum_detail']));
						$name = getUserName($rows['forum_thread_user_id']);
						$posts = $this->getTotalPosts($rows['forum_thread_user_id']);
						$reg_date = $this->getRegDateFromUserID($rows['forum_thread_user_id']);
						$forumHtml = $this->forumHtml($rows,'threadHead');
						if ($rows['forum_post_approve'] == 1)
						$forumHtml .= $this->forumHtml($rows,'threadMain');
						$sql2 = "SELECT * FROM `$table1_name` WHERE `forum_thread_id`='$thread_id' AND `forum_post_approve` = 1 AND `page_modulecomponentid`='$this->moduleComponentId' ORDER BY `forum_post_id` ASC";
						$result2 = mysql_query($sql2);
						while ($rows = mysql_fetch_array($result2)) 
							$forumHtml .= $this->forumHtml($rows,'threadMain',1);
						$sql3 = "SELECT `forum_thread_viewcount` FROM `$table_name` WHERE `forum_thread_id`='$thread_id' AND `page_modulecomponentid`='$this->moduleComponentId'";
						$result3 = mysql_query($sql3);
						$rows = mysql_fetch_array($result3);
						$view = $rows['forum_thread_viewcount'];
						// count more value
						$addview = $view +1;
						$sql5 = "UPDATE `$table_name` SET `forum_thread_viewcount`='$addview' WHERE forum_thread_id='$thread_id' AND `page_modulecomponentid`='$this->moduleComponentId' LIMIT 1";
						$result5 = mysql_query($sql5);
						$forumHtml .= '</table> ';
						return $forumHtml;
					}
					}
				}
		} else
			if (isset ($_POST['preview'])) {

				$message = escape($_POST['message']);
				$subject = addslashes(htmlspecialchars($_POST['subject']));
				$text = $message;
				$message = nl2br($message);
				$message = parseubb(parsesmileys(addslashes(htmlspecialchars(parsenewline($message)))));
				$editor = bbeditor($action, $subject, $text);
				return "<b>Subject :</b> " . $subject . "<br><b>Message :</b><br> " . $message . $editor;
			} else
				if (isset ($_GET['thread_id'])) {
					$editor = bbeditor($action);
					return $editor;
				} else {
					$editor = bbeditor($action);
					return $editor;
				}
	}
public function actionView() {
		$userId = $this->userId;
		global $urlRequestRoot, $moduleFolder, $cmsFolder,$templateFolder,$sourceFolder;
		$templatesImageFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/".TEMPLATE;
		$temp = $urlRequestRoot . "/" . $cmsFolder . "/" . $moduleFolder . "/forum/images";
		$table_name = "forum_threads";
		$table1_name = "forum_posts";
		$forumHtml = <<<PRE
		<link rel="stylesheet" href="$temp/styles.css" type="text/css" />
PRE;
		$forum_lastVisit = $this->forumLastVisit();
		$moderator=getPermissions($this->userId, getPageIdFromModuleComponentId("forum",$this->moduleComponentId), "moderate");
		//to check last visit to the forum
		$table_visit = "forum_visits";
		$query_checkvisit = "SELECT * from `$table_visit` WHERE `user_id`='$userId' AND `page_modulecomponentid`='$this->moduleComponentId'";
		$result_checkvisit = mysql_query($query_checkvisit);
		$check_visits = mysql_fetch_array($result_checkvisit);
		if(mysql_num_rows($result_checkvisit)<1) {
			$forum_lastviewed = date("Y-m-d H:i:s");
		}
		else {
			$forum_lastviewed = $check_visits['last_visit'];	
		}
		//set user's last visit
		$time_visit = date("Y-m-d H:i:s");
		$query_visit = "SELECT * FROM `$table_visit` WHERE `user_id`='$userId' AND `page_modulecomponentid`='$this->moduleComponentId'";
		$result_visit = mysql_query($query_visit);
		$num_rows_visit = mysql_num_rows($result_visit);
		if($num_rows_visit<1) {
		  $query_setvisit = "INSERT INTO `$table_visit`(`page_modulecomponentid`,`user_id`,`last_visit`) VALUES('$this->moduleComponentId','$userId','$time_visit')";
		}
		else {
		  $query_setvisit = "UPDATE `$table_visit` SET `last_visit`='$time_visit' WHERE `user_id`='$userId' AND `page_modulecomponentid`='$this->moduleComponentId'"; 
		}
		mysql_query($query_setvisit);

		require_once ("$sourceFolder/$moduleFolder/forum/bbeditor.php");
		require_once ("$sourceFolder/$moduleFolder/forum/bbparser.php");
		if (!isset ($_GET['thread_id'])) {
			if ((isset($_GET['subaction']))&&($_GET['subaction'] == "delete_thread")) {
				$thread_id = escape($_GET['forum_id']);
				$query = "DELETE FROM `$table_name` WHERE `forum_thread_id`='$thread_id' AND `page_modulecomponentid`='$this->moduleComponentId' LIMIT 1";
				$res = mysql_query($query);
				$query1 = "DELETE FROM `$table1_name` WHERE `forum_thread_id`='$thread_id' AND `page_modulecomponentid`='$this->moduleComponentId'";
				$res1 = mysql_query($query1);
				if (!res || !res1)
					displayerror("Could not perform the delete operation on the selected thread!");
			}
			if($userId>0 )
			{
			$new_mt='0';
			$new_mp='0';
			$new_p='0';
			$new_t='0';
			if($moderator)
			{
			$qum_0 = "SELECT * FROM `$table_name` WHERE `page_modulecomponentid`='" . $this->moduleComponentId ."' AND `forum_post_approve` = 0";
			$resm_0 = mysql_query($qum_0);
			$numm_0 = mysql_num_rows($resm_0);
			for ($j = 1; $j <= $numm_0; $j++) {
				$rows = mysql_fetch_array($resm_0,MYSQL_ASSOC);
				if($forum_lastVisit<$rows['forum_thread_datetime'])
					$new_mt = $new_mt + '1';
			}
			$qum_1 = "SELECT * FROM `$table1_name` WHERE `page_modulecomponentid`='" . $this->moduleComponentId ."' AND `forum_post_approve` = 0";
			$resm_1 = mysql_query($qum_1);
			$numm_1 = mysql_num_rows($resm_1);
			for ($j = 1; $j <= $numm_1; $j++) {
				$rows = mysql_fetch_array($resm_1,MYSQL_ASSOC);
				if($forum_lastVisit<$rows['forum_post_datetime'])
					$new_mp = $new_mp + '1';
			}
			if($new_mt){
			$show_t = $new_mt. " new threads to be moderated since your last visit";
			displayinfo($show_t);}
			if($new_mp) {
			$show_p = $new_mp. " new posts to be moderated since your last visit";
			displayinfo($show_p);}
			}
			$qu_0 = "SELECT * FROM `$table_name` WHERE `page_modulecomponentid`='" . $this->moduleComponentId ."' AND `forum_post_approve` = 1 AND `forum_thread_user_id` !='$this->userId'";
			$res_0 = mysql_query($qu_0);
			$num_0 = mysql_num_rows($res_0);
			for ($j = 1; $j <= $num_0; $j++) {
				$rows = mysql_fetch_array($res_0,MYSQL_ASSOC);
				if($forum_lastVisit<$rows['forum_thread_datetime'])
					$new_t = $new_t + '1';
			}
			$qu_1 = "SELECT * FROM `$table1_name` WHERE `page_modulecomponentid`='" . $this->moduleComponentId ."' AND `forum_post_approve` = 1 AND `forum_post_user_id` !='$this->userId'";
			$res_1 = mysql_query($qu_1) or die(mysql_error());
			$num_1 = mysql_num_rows($res_1);
			for ($j = 1; $j <= $num_1; $j++) {
				$rows = mysql_fetch_array($res_1,MYSQL_ASSOC);
				if($forum_lastVisit<$rows['forum_post_datetime'])
					$new_p = $new_p + '1';
			}
			if($new_t && $new_t!=$new_mt){
			$show_t = $new_t. " new threads since your last visit";
			displayinfo($show_t);}
			if($new_p && $new_p!=$new_mp) {
			$show_p = $new_p. " new posts since your last visit";
			displayinfo($show_p);}
			}
			$query_d = "SELECT `forum_description` FROM `forum_module` WHERE `page_modulecomponentid`='" . $this->moduleComponentId ."' LIMIT 1";
			$result_d = mysql_query($query_d) or die(mysql_error());
			$result_d = mysql_fetch_array($result_d);
			$query = "SELECT * FROM `$table_name` WHERE `page_modulecomponentid`='" . $this->moduleComponentId . "' AND " .
					"`forum_thread_category`='general' ORDER BY `forum_thread_lastpost_date` DESC";
			$result = mysql_query($query) or displayerror(mysql_error() . "View of General Threads failed L:776");
			$query1 = "SELECT * FROM `$table_name` WHERE `page_modulecomponentid`='" . $this->moduleComponentId . "' AND " .
					"`forum_thread_category`='sticky' ORDER BY `forum_thread_datetime` DESC";
			$result1 = mysql_query($query1)or displayerror(mysql_error() . "View of sticjy Threads failed L:779");
			$num_rows1 = mysql_num_rows($result1); //counts the total no of sticky threads
			if ($result) {
				$action = "+post&subaction=create_thread";
				$num_rows = mysql_num_rows($result); //counts the total no of general threads				
				$forum_header =<<<PRE
			<p align="left"><a href="$action"><img title="New Thread" src="$temp/newthread.gif" /></a></p>
			<div style="text-align:center;"><b>" $result_d[0] "</b></div>
	        <table width="100%" border="1" align="center" cellpadding="4" cellspacing="2" id="forum">
	        <tr class="TableHeader">
	        <td class="forumTableHeader" colspan="2"><strong>TOPICS</strong><br /></td>
	        <td class="forumTableHeader"> <strong>VIEWS</strong></td>
	        <td class="forumTableHeader"><strong>REPLIES</strong></td>
	        <td class="forumTableHeader"><strong>LAST POST</strong></td>
	        </tr>
PRE;
				$forumHtml .= $forum_header;
				if ($result1 && $num_rows1 > 0) {
					for ($j = 1; $j <= $num_rows1; $j++) {
						$rows = mysql_fetch_array($result1,MYSQL_ASSOC);
						$query2 = "SELECT `forum_post_id` FROM `$table1_name` WHERE `forum_thread_id`='" . $rows['forum_thread_id'] . "' AND `forum_post_approve`='1' AND `page_modulecomponentid`='$this->moduleComponentId'";
						$result2 = mysql_query($query2);
						$reply_count = mysql_num_rows($result2);
						$topic = parseubb(parsesmileys(stripslashes($rows['forum_thread_topic'])));
						$name = getUserName($rows['forum_thread_user_id']);
						$last_post_author = getUserName($rows['forum_thread_last_post_userid']);
						if ($rows['forum_post_approve'] == 1) {
							$forumHtml .= $this->forumHtml($rows,'threadRow');
						}
					}
				}
			if ($num_rows < 1)
					$forum_header .= "<tr><td colspan=\"5\" class='forumTableRow'><strong>No Post</strong></td></tr>";
				for ($i = 1; $i <= $num_rows; $i++) {
					$rows = mysql_fetch_array($result);
					$query1 = "SELECT `forum_post_id` FROM `$table1_name` WHERE `forum_thread_id`='" . $rows['forum_thread_id'] . "' AND `forum_post_approve`='1' AND `page_modulecomponentid`='$this->moduleComponentId'";
					$result1 = mysql_query($query1);
					$reply_count = mysql_num_rows($result1);
					$topic = parseubb(parsesmileys($rows['forum_thread_topic']));
					$name = getUserName($rows['forum_thread_user_id']);
					$last_post_author = getUserName($rows['forum_thread_last_post_userid']);
					if ($rows['forum_post_approve'] == 1) {
						$forumHtml .= $this->forumHtml($rows,'threadRow');
						}
					}
				$forumHtml .= '<tr></tr></table><br />';
				}
			}
			else {
			$thread_id = escape($_GET['thread_id']); //Parent Thread ID
			if(isset($_GET['subaction'])){
				if ($_GET['subaction'] == "delete_post") {
					$post_id = escape($_GET['post_id']);
					$query = "DELETE FROM `$table1_name` WHERE `forum_thread_id`='$thread_id' AND `forum_post_id`='$post_id' AND `page_modulecomponentid`='$this->moduleComponentId' LIMIT 1";
					$res = mysql_query($query);
					if ( !$res )
						displayerror("Could not perform the delete operation on the selected post!");
					$query = "DELETE FROM `forum_like` WHERE `forum_thread_id`='$thread_id' AND `forum_post_id`='$post_id' AND `page_modulecomponentid`='$this->moduleComponentId'";
					$res = mysql_query($query);
			}
				if ($_GET['subaction'] == "like_post") {
					$post_id = escape($_GET['post_id']);
					$query = "SELECT * FROM `forum_like` WHERE `forum_thread_id`='$thread_id' AND `forum_post_id`='$post_id' AND `page_modulecomponentid`='$this->moduleComponentId' ";
					$res = mysql_query($query);
					if(mysql_num_rows($res)==0) {
					$query = "INSERT INTO`forum_like` (`page_modulecomponentid`,`forum_thread_id`,`forum_post_id`,`forum_like_user_id`,`like_status`) VALUES ('$this->moduleComponentId','$thread_id','$post_id','$userId','1')";
					$res = mysql_query($query);
					if ( !$res )
						displayerror("Could not perform the like operation on the selected post!");
					}	
			}
				if ($_GET['subaction'] == "dislike_post") {
					$post_id = escape($_GET['post_id']);
					$query = "SELECT * FROM `forum_like` WHERE `forum_thread_id`='$thread_id' AND `forum_post_id`='$post_id' AND `page_modulecomponentid`='$this->moduleComponentId' ";
					$res = mysql_query($query);
					if(mysql_num_rows($res)==0) {
					$query = "INSERT INTO`forum_like` (`page_modulecomponentid`,`forum_thread_id`,`forum_post_id`,`forum_like_user_id`,`like_status`) VALUES ('$this->moduleComponentId','$thread_id','$post_id','$userId','0')";
					$res = mysql_query($query);
					if ( !$res )
						displayerror("Could not perform the dislike operation on the selected post!");
						}
			}			
			}
			$sql = "SELECT * FROM `$table_name` WHERE `forum_thread_id`='$thread_id' AND `page_modulecomponentid`='$this->moduleComponentId' LIMIT 1";
			$result1 = mysql_query($sql);
			$rows = mysql_fetch_array($result1);
			$threadUserId = $rows['forum_thread_user_id'];
			$forum_topic = parseubb(parsesmileys($rows['forum_thread_topic']));
			$forum_detail = parseubb(parsesmileys($rows['forum_detail']));
			$name = getUserName($rows['forum_thread_user_id']);
			$posts = $this->getTotalPosts($rows['forum_thread_user_id']);
			$reg_date = $this->getRegDateFromUserID($rows['forum_thread_user_id']);
			$forumHtml = $this->forumHtml($rows,'threadHead');
			$count=0;
			if ($rows['forum_post_approve'] == 1)
				$forumHtml .= $this->forumHtml($rows,'threadMain',0,0);
			$sql2 = "SELECT * FROM `$table1_name` WHERE `forum_thread_id`='$thread_id' AND `forum_post_approve` = 1 AND `page_modulecomponentid`='$this->moduleComponentId' ORDER BY `forum_post_id` ASC";
			$result2 = mysql_query($sql2);
			while ($rows1 = mysql_fetch_array($result2)) {
				$count = $count + 1;
				$forumHtml .= $this->forumHtml($rows1,'threadMain',1,$count);
			}
			$sql3 = "SELECT `forum_thread_viewcount` FROM `$table_name` WHERE `forum_thread_id`='$thread_id' AND `page_modulecomponentid`='$this->moduleComponentId'";
			$result3 = mysql_query($sql3);
			$rows2 = mysql_fetch_array($result3);
			$view = $rows2['forum_thread_viewcount'];
			// count more value
			$addview = $view +1;
			$sql5 = "UPDATE `$table_name` SET `forum_thread_viewcount`='$addview' WHERE forum_thread_id='$thread_id' AND `page_modulecomponentid`='$this->moduleComponentId' LIMIT 1";
			$result5 = mysql_query($sql5);
			$forumHtml .= '</table><br />';
			if($rows['forum_thread_category']!='sticky'){
			$forumHtml .= '<p align="left"><a href="+post&subaction=post_reply&thread_id='.$thread_id.'"><img alt="Reply" title="Reply" src="'.$temp.'/reply.gif" /></a></p>';
			}
			}
			$forumHtml .= '<p align="left"><img alt="Sticky" title="Sticky" src="' . $temp . '/sticky.gif" align=left> &nbsp;- Sticky Threads.<br /><br />' .
				            		'<img alt="New Posts" title="New Posts" src="' . $temp . '/thread_new.gif" align=left> &nbsp;- Topic with new posts since last visit.' .
				            				'<br /><br /><img alt="No new posts" title="No new Posts" src="' . $temp . '/thread_hot.gif" align=left>' .
				            						'&nbsp;- Topic with no new posts since last visit. </p>';
		return $forumHtml;
	}
	private function forumHtml($data, $type='thread', $post=0,$count=0) {
		global $urlRequestRoot, $moduleFolder, $cmsFolder,$templateFolder,$sourceFolder,$userId;
		require_once ("$sourceFolder/$moduleFolder/forum/bbeditor.php");
		require_once ("$sourceFolder/$moduleFolder/forum/bbparser.php");
		$js=$urlRequestRoot."/".$cmsFolder."/".$moduleFolder."/forum/images/jscript.js";
		$table_name = "forum_threads";
		$table1_name = "forum_posts";
		$templatesImageFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/".TEMPLATE;
		$temp = $urlRequestRoot . "/" . $cmsFolder . "/" . $moduleFolder . "/forum/images";
		if(isset($_GET['thread_id']))
			$thread_id = escape($_GET['thread_id']); 
		$forumHtml = '';
		$forum_threads = '';
		$rows = $data;
		$action = "+post&subaction=create_thread";
		$forum_lastVisit = $this->forumLastVisit();
		if($type == 'threadRow')
			{
					if($userId>0 && ($forum_lastVisit<$rows['forum_thread_lastpost_date']))
						{						
							$img_src = "thread_new.gif";
						}
					else
						{
							$img_src = "thread_hot.gif";
						}
				$topic = ucfirst((parseubb(parsesmileys($rows['forum_thread_topic']))));
				$name = ucfirst(getUserName($rows['forum_thread_user_id']));
				$last_post_author = ucfirst(getUserName($rows['forum_thread_last_post_userid']));
				if($rows['forum_thread_category']=='sticky') {
						$img_src = 'sticky.gif';
						}
				$query1 = "SELECT `forum_post_id` FROM `$table1_name` WHERE `forum_thread_id`='" . $rows['forum_thread_id'] . "' AND `forum_post_approve`='1' AND `page_modulecomponentid`='$this->moduleComponentId' ";
				$result1 = mysql_query($query1);
				$reply_count = mysql_num_rows($result1);
				$forum_threads .=<<<PRE1
			            <tr class="forumThreadRow">
			            <td class="forumThreadRow forumTableIcon" width="3%"><img src="$temp/$img_src" /></td>
			            <td class="forumThreadRow" width="51%"><a class="threadRow" href="+view&thread_id=$rows[forum_thread_id]"> $topic </a><br /><small>by <b> $name </b>
			             on $rows[forum_thread_datetime] </small></td>
			            <td class="forumThreadRow" width="8%" style="text-align:center;"> $rows[forum_thread_viewcount] </td>
			            <td class="forumThreadRow" width="8%" style="text-align:center;"> $reply_count </td>
			            <td class="forumThreadRow" width="30%"><small>by <b> $last_post_author </a></b> on $rows[forum_thread_lastpost_date] </small></td>
			            </tr>        
PRE1;
				$forumHtml .= $forum_threads;
			}
		if($type == 'threadHead'){
				$thread_Header = '<p align="left">';
				if($rows['forum_thread_category']!='sticky') {
					$thread_Header .= '<a href="+post&subaction=post_reply&thread_id='.$thread_id.'"><img alt="Reply" title="Reply" src="'.$temp.'/reply.gif" /></a>&nbsp&nbsp';
				}
				$thread_Header .=<<<PRE
				<link rel="stylesheet" href="$temp/styles.css" type="text/css" />
				&nbsp<a href="+post&subaction=create_thread"><img title="New Thread" src="$temp/newthread.gif" /></a>&nbsp;<a 
href="+view"> <img title="Go Back to Forum" src="$temp/go_back.gif" /></a>
				<table width="100%" cellpadding="4" cellspacing="2" id="forum" >
PRE;
			$forumHtml = $thread_Header;
		}
		if($type == 'threadMain') {
			$q = "SELECT * FROM `forum_module` WHERE `page_modulecomponentid`='$this->moduleComponentId' LIMIT 1";
			$r = mysql_query($q) or displayerror(mysql_error() . "View of Thread failed L:962");
			$r = mysql_fetch_array($r);
		if($post == 0){
			$topic = censor_words(ucfirst(parseubb(parsesmileys($rows['forum_thread_topic']))));
			$name = ucfirst(getUserName($rows['forum_thread_user_id']));
			$last_post_author = ucfirst(getUserName($rows['forum_thread_last_post_userid']));
			$threadUserId = $rows['forum_thread_user_id'];
			$detail = censor_words(parseubb(parsesmileys($rows['forum_detail'])));
			$posts = $this->getTotalPosts($rows['forum_thread_user_id']);
			$reg_date = $this->getRegDateFromUserID($rows['forum_thread_user_id']);
			$postTime = $rows['forum_thread_datetime'];
			}
			if($post == 1){
			$postUserId = $rows['forum_post_user_id'];
			$topic = censor_words(ucfirst(parseubb(parsesmileys($rows['forum_post_title']))));
			$detail = censor_words(parseubb(parsesmileys($rows['forum_post_content'])));
			$name = ucfirst(getUserName($rows['forum_post_user_id']));
			$posts = $this->getTotalPosts($rows['forum_post_user_id']);
			$reg_date = $this->getRegDateFromUserID($rows['forum_post_user_id']);
			$postTime = $rows['forum_post_datetime'];
			$threadUserId = $postUserId;
			}
			$datetime = date("Y-m-d H:i:s")-$postTime;
					$threadHtml = '<tr class="ThreadHeadRow" cellspacing="10">
					        <td class="forumThreadRow"><strong> ' . $topic . ' </strong><br />' .
					        		'<img src="' . $temp . '/post_icon.gif" /><small>&nbsp&nbsp by ' . $name . ' </a>' .
					        				' on ' . $postTime  . ' </small>';
					if($post == 1)						
					if($r['allow_like_posts'] == 1){
					$likequery = "SELECT * from `forum_like` WHERE `forum_thread_id`='$thread_id' AND `forum_post_id`='".$rows['forum_post_id']."' AND `like_status`='1' AND `page_modulecomponentid`='$this->moduleComponentId' ";
					$likeres = mysql_query($likequery);
					$likeres = mysql_num_rows($likeres);
					$dlikequery = "SELECT * from `forum_like` WHERE `forum_thread_id`='$thread_id' AND `forum_post_id`='".$rows['forum_post_id']."' AND `like_status`='0' AND `page_modulecomponentid`='$this->moduleComponentId' ";
					$dlikeres = mysql_query($dlikequery);
					$dlikeres = mysql_num_rows($dlikeres);
						$threadHtml .= '<br /><small> ' . $likeres . ' people like this post</small> &nbsp&nbsp&nbsp';
						$threadHtml .= '<small> ' . $dlikeres . ' people dislike this post</small><br />';
					}
					$threadHtml .='</td>
					        <td class="forumThreadRow" width="25%" rowspan="2"><strong> ' . $name . ' </a><br />';
				if ($threadUserId > 0) {
					if ($threadUserId == $userId)
						$lastLogin = $_SESSION['last_to_last_login_datetime'];
					else
						$lastLogin = $this->getLastLogin($threadUserId);
						$moderator=getPermissions($threadUserId, getPageIdFromModuleComponentId("forum",$this->moduleComponentId), "moderate");
					if($moderator)$threadHtml .= "Moderator";else
					$threadHtml .= "Member";
					$content = 'content'.$count;
					$text = 'text'.$count;
					$threadHtml .= <<<PRE
						</strong><br /><br />
						<script type="text/javascript" languauge="javascript" src="$js"></script>
						<a id="$text" href="javascript:toggle('$content','$text');" >Show Details</a><br />
						<div id="$content" style="display: none;"><small>Posts: $posts <br />Joined: $reg_date <br />Last Visit:
						$lastLogin </small></div>
PRE;
if($post==1 && $userId>0 && ( ($r['allow_delete_posts'] == 1) ||($r['allow_like_posts']==1))) {	
			//$threadHtml .= '<tr><td colspan="2" align="right">';
			if($r['allow_delete_posts'] == 1){
			if ($post==1 && $userId > 0 && $userId == $rows['forum_post_user_id'])
					 //compare the userID of the logged in user with that of the author of the current reply
						{
						$threadHtml .= '<br /><a href="+view&subaction=delete_post&thread_id=' . $thread_id . '&post_id=' . $rows['forum_post_id'] . '">' .
										'<img src="'.$temp.'/delete1.gif"></a></span>';
					}
			}
			if($r['allow_like_posts'] == 1) {
				if ($userId > 0 && $post == 1)
						{
						$postId=$rows['forum_post_id'];
						$qu = " SELECT * FROM `forum_like` WHERE `forum_like_user_id` = '$userId' AND`forum_thread_id` = '$thread_id' AND `forum_post_id` = '$postId' AND `page_modulecomponentid`='$this->moduleComponentId' AND `like_status`='1'";
						$re = mysql_query($qu) ;
						$qu1 = " SELECT * FROM `forum_like` WHERE `forum_like_user_id` = '$userId' AND`forum_thread_id` = '$thread_id' AND `forum_post_id` = '$postId' AND `page_modulecomponentid`='$this->moduleComponentId' AND `like_status`='0'";
						$re1 = mysql_query($qu1);
						if(mysql_num_rows($re)==0 && mysql_num_rows($re1)==0)
							{
							$threadHtml .= '  <a href="+view&subaction=like_post&thread_id=' . $thread_id . '&post_id=' . $rows['forum_post_id'] . '">' .
										'  <img title="Like this post" src="'.$temp.'/like.gif"></a></span>';
							$threadHtml .= '  <a href="+view&subaction=dislike_post&thread_id=' . $thread_id . '&post_id=' . $rows['forum_post_id'] . '">' .
										'  <img title="Dislike this post" src="'.$temp.'/unlike.gif"></a></span>';
							}
						else {
						if(mysql_num_rows($re)>0)
							$threadHtml .= '<br /> You Like this post';
						else
							$threadHtml .= '<br /> You Dislike this post';
						}
						}
			}
			//$threadHtml .= '</td></tr>';
		}
				}
				$threadHtml .=<<<PRE
	        </td>
	        </tr>
	        <tr>
	        <td class="forumThreadRow"> <br />$detail </td>
	        </tr>
PRE;
	        $threadHtml .= '<tr><td class="blank" colspan="2"></td></tr>';


			$forumHtml .= $threadHtml;
		}
			
		return $forumHtml;
	}
		
	private function forumLastVisit() {
		global $userId;
		//to check last visit to the forum
		if(!isset($_SESSION['forum_lastVisit'])){
		$table_visit = "forum_visits";
		$query_checkvisit = "SELECT * from `$table_visit` WHERE `user_id`='$userId' AND `page_modulecomponentid`='$this->moduleComponentId'";
		$result_checkvisit = mysql_query($query_checkvisit);
		$check_visits = mysql_fetch_array($result_checkvisit);
		if(mysql_num_rows($result_checkvisit)<1) {
			$forum_lastViewed = date("Y-m-d H:i:s");
		}
		else {
			$forum_lastViewed = $check_visits['last_visit'];	
		}
		$_SESSION['forum_lastVisit'] = $forum_lastViewed ;
		//set user's last visit
		$time_visit = date("Y-m-d H:i:s");
		$query_visit = "SELECT * FROM `$table_visit` WHERE `user_id`='$userId' AND `page_modulecomponentid`='$this->moduleComponentId'";
		$result_visit = mysql_query($query_visit);
		$num_rows_visit = mysql_num_rows($result_visit);
		if($num_rows_visit<1) {
		  $query_setvisit = "INSERT INTO `$table_visit`(`page_modulecomponentid`,`user_id`,`last_visit`) VALUES('$this->moduleComponentId','$userId','$time_visit')";
		}
		else {
		  $query_setvisit = "UPDATE `$table_visit` SET `last_visit`='$time_visit' WHERE `user_id`=$userId AND `page_modulecomponentid`=$this->moduleComponentId"; 
		}
		mysql_query($query_setvisit);//or die(mysql_error());
		}
		else {
			$forum_lastViewed = $_SESSION['forum_lastVisit'];
		}
		return $forum_lastViewed;
		
	}
public function createModule($compId) {
		$query = "INSERT INTO `forum_module` (`page_modulecomponentid`,`forum_description`,`last_post_userid` )VALUES ('$compId','Forum Description Here!!!','1')";
		$result = mysql_query($query) or die(mysql_error() . " forum.lib L:1113");
	}

	public function deleteModule($moduleComponentId) {
		return true;
	}

	public function copyModule($moduleComponentId,$newId) {
		return true;
	}
}

