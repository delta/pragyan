<?php

/**
 * @package pragyan
 * @copyright (c) 2008 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
/* 
 * TO DO : 1.create threads(code for the sticky threads also) and post replies CODE
 * 				2.Add smileys and BBcode DONE
 * 				3.define forum's access(moderated/public) DONE
 * 				4.implement pagination
 * 				5.parsing  DONE
 * 
 * 
 *
 */
 
 /*
  * actionModerate -> hari
  * 
  * 
  * */

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
		$q1 = "SELECT * FROM forum_threads WHERE forum_thread_user_id=$userID";
		$res1 = mysql_query($q1);
		$posts = mysql_num_rows($res1);
		$q2 = "SELECT * FROM forum_posts WHERE forum_post_user_id=$userID";
		$res2 = mysql_query($q2);
		$posts += mysql_num_rows($res2);
		return $posts;
	}

	public function getLastLogin($userId) {
		$query = "SELECT `user_lastlogin` FROM `" . MYSQL_DATABASE_PREFIX . "users` WHERE `user_id` = $userId";
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

	public function actionModerate() {
		global $urlRequestRoot, $moduleFolder, $cmsFolder,$templateFolder,$sourceFolder;
		$temp = $urlRequestRoot . "/" . $cmsFolder . "/" . $moduleFolder . "/forum/images";
		require_once ("$sourceFolder/$moduleFolder/forum/bbeditor.php");
		require_once ("$sourceFolder/$moduleFolder/forum/bbparser.php");

		$userId = $this->userId;
		$table_name = "forum_threads";
		$table1_name = "forum_posts";
		$table2_name = "forum_module";
		$templatesImageFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/".TEMPLATE;

		if(isset($_POST['mod_permi']))
		{
			if($_POST['forum_name'] && $_POST['forum_desc'])
			{
				$forum_name = addslashes($_POST['forum_name']);
				$forum_description = addslashes($_POST['forum_desc']);
			}
			else
			{
				$forum_name = "";
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
			$q = "UPDATE `$table2_name` SET `forum_moderated`='$access_level', `forum_name`='$forum_name', " .
					"`forum_description`='$forum_description' WHERE `page_modulecomponentid`='$this->moduleComponentId' LIMIT 1";
			$res = mysql_query($q);
		}

		if ($_GET['subaction'] == "approve" || $_GET['subaction'] == "disapprove") {
			if ($_GET['subaction'] == "approve")
				$approval = 1;
			else
				$approval = 0;
			if (!isset ($_GET['post_id'])) {
				$thread_id = $_GET['thread_id'];
				$query = "UPDATE `$table_name` SET `forum_post_approve`=$approval WHERE `forum_thread_id`=$thread_id LIMIT 1";
				$result = mysql_query($query);
			} else {
				$thread_id = $_GET['forum_id'];
				$post_id = $_GET['post_id'];
				$query = "UPDATE `$table1_name` SET `forum_post_approve`=$approval WHERE `forum_thread_id`=$thread_id AND `forum_post_id`=$post_id LIMIT 1";
				$result = mysql_query($query);
			}
			if (!$result)
				displayerror("Could not perform the desired action(approve/disapprove)!");
		}
		if ($_GET['subaction'] == "delete") {
			if (!isset ($_GET['post_id'])) {
				$thread_id = $_GET['thread_id'];
				$query = "DELETE FROM `$table_name` WHERE `forum_thread_id`=$thread_id LIMIT 1";
				$result = mysql_query($query);
				$query1 = "DELETE FROM `forum_posts` WHERE `forum_thread_id`=$thread_id";
				$result1 = mysql_query($query1);
			} else {
				$thread_id = $_GET['forum_id'];
				$post_id = $_GET['post_id'];
				$query1 = "DELETE FROM `forum_posts` WHERE `forum_thread_id`=$thread_id AND `forum_post_id`=$post_id LIMIT 1";
				$result1 = mysql_query($query1);
			}
			if (!result || !result1)
				displayerror("Could not perform the delete operation!");
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
			if ($result) {
				$action = "+post&subaction=create_thread";
				$moderate =<<<PRE
		<p align="left"><a href="$action" style="color:#0F5B96"><img src="$temp/newthread.gif" /></a></p>
        <table width="100%" border="0" align="center" cellpadding="3" cellspacing="1">
        <tr>
        <td class="forumTableHeaderSubject" colspan="4"><strong>Subject</strong><br /></td>
        <td class="forumTableHeaderViews"><strong>Views</strong></td>
        <td class="forumTableHeaderReplies"><strong>Replies</strong></td>
        <td class="forumTableHeaderPost"><strong>Last Post</strong></td>
        </tr>
        <tr>
PRE;
				if ($result1 && $num_rows1 > 0) {
					for ($i = 1; $i <= $num_rows1; $i++) {
						$rows = mysql_fetch_array($result1);
						$query2 = "SELECT `forum_post_id` FROM `$table1_name` WHERE `forum_thread_id`='" . $rows['forum_thread_id'] . "' AND `forum_post_approve`='1'";
						$result2 = mysql_query($query2);
						$reply_count = mysql_num_rows($result2);
						$topic = parseubb(parsesmileys(stripslashes($rows['forum_thread_topic'])));
						$name = getUserName($rows['forum_thread_user_id']);
						$last_post_author = getUserName($rows['forum_thread_last_post_userid']);
						if ($rows['forum_post_approve'] == 0)
							{
								$text = "Approve";
								$img = "thumbs_up.gif";
							}
						else
							{
								$text = "Disapprove";
								$img = "thumbs_down.gif";
							}
						$subaction = strtolower($text);
						$moderate .=<<<PRE
		        <tr>
		        <td class="forumTableCol"  width="3%"><a href="+moderate&subaction=delete&thread_id=$rows[forum_thread_id]">
		        <img src="$temp/b_drop.png" /></a></td>
		        <td class="forumTableCol"  width="3%"><a href="+moderate&subaction=$subaction&thread_id=$rows[forum_thread_id]">
		        <img src="$temp/$img" /></a></td>
		        <td class="forumTableCol"  width="3%"><img src="$temp/pinned_topic_icon.gif" /></td>
		        <td class="forumTableRow"  width="41%"><a href="+moderate&forum_id=$rows[forum_thread_id]"> $topic </a><br /><small>by <b> $name </a></b>
		        on $rows[forum_thread_datetime] </small></td>
		        <td class="forumTableCol"  width="10%"> $rows[forum_thread_viewcount] </td>
		        <td class="forumTableCol"  width="10%"> $reply_count </td>
		        <td class="forumTableRow"  width="30%"><small>by <b> $last_post_author </a></b> on $rows[forum_thread_lastpost_date] </small></td>
		        </tr>
PRE;

					}
				}
				if ($num_rows < 1)
					$moderate .= "<tr><td colspan=\"5\" class='forumTableRow'><strong>No Post</strong></td></tr>";
				for ($i = 1; $i <= $num_rows; $i++) {
					$rows = mysql_fetch_array($result);
					if($userId>0 && ($_SESSION['last_to_last_login_datetime']<$rows['forum_thread_lastpost_date']))
						$img_src = "new_posts_icon.gif";
					else
						$img_src = "no_new_posts_icon.gif";

					$query1 = "SELECT `forum_post_id` FROM `$table1_name` WHERE `forum_thread_id`='" . $rows['forum_thread_id'] . "' AND `forum_post_approve`='1'";
					$result1 = mysql_query($query1);
					$reply_count = mysql_num_rows($result1);
					$topic = parseubb(parsesmileys(stripslashes($rows['forum_thread_topic'])));
					$name = getUserName($rows['forum_thread_user_id']);
					$last_post_author = getUserName($rows['forum_thread_last_post_userid']);
						if ($rows['forum_post_approve'] == 0)
							{
								$text = "Approve";
								$img = "thumbs_up.gif";
							}
						else
							{
								$text = "Disapprove";
								$img = "thumbs_down.gif";
							}
					$subaction = strtolower($text);
					$moderate .=<<<PRE
        <tr>
        <td class="forumTableCol"  width="3%"><a href="+moderate&subaction=delete&thread_id=$rows[forum_thread_id]"><img src="$temp/b_drop.png" />
        </a></td>
		<td class="forumTableCol"  width="3%"><a href="+moderate&subaction=$subaction&thread_id=$rows[forum_thread_id]"><img src="$temp/$img" />
		</a></td>
        <td class="forumTableCol"  width="3%"><img src="$temp/$img_src" /></td>
        <td class="forumTableRow"  width="41%"><a href="+moderate&forum_id=$rows[forum_thread_id]"> $topic </a><br /><small>by <b> $name </a></b>
        on $rows[forum_thread_datetime] </small></td>
        <td class="forumTableCol"  width="10%"> $rows[forum_thread_viewcount] </td>
        <td class="forumTableCol"  width="10%"> $reply_count </td>
        <td class="forumTableRow"  width="30%"><small>by <b> $last_post_author </a></b> on $rows[forum_thread_lastpost_date] </small></td>
        </tr>
PRE;
				}
				$moderate .= '</table><br />
				        <p align="left"><img alt="" src="' . $temp . '/thumbs_up.gif" align=left> &nbsp;- To Approve Threads.<br /><br />' .
				        		'<img alt="" src="' . $temp . '/thumbs_down.gif" align=left> &nbsp;- To Disapprove Threads.<br /><br />' .
				        				'<img alt="" src="' . $temp . '/pinned_topic_icon.gif" align=left> &nbsp;- Sticky Threads.<br /><br />' .
				        						'<img alt="" src="' . $temp . '/new_posts_icon.gif" align=left>' .
				        								' &nbsp;- Topic with new posts since last visit.<br /><br />' .
				        								'<img alt="" src="' . $temp . '/no_new_posts_icon.gif" align=left>' .
				        										'&nbsp;- Topic with no new posts since last visit. </p><hr />';
				$query = "SELECT * FROM `$table2_name` WHERE `page_modulecomponentid`='$this->moduleComponentId' LIMIT 1";
				$result = mysql_query($query);
				$rows = mysql_fetch_array($result);
				$forum_name = stripslashes($rows['forum_name']);
				$forum_description = stripslashes($rows['forum_description']);
				$forum_moderated = $rows['forum_moderated'];
				$moderatedselected = "";
				$publicselected = "";
				if($forum_moderated==1) $moderatedselected = 'selected="selected"';
				else 					$publicselected = 'selected="selected"';
				$moderate.=<<<PRE
				<form method="post" name="forum_access" action="./+moderate">
				Choose Forum Access Level : <select name="mod_permi" style="width:100px;">
				<option value="moderated" $moderatedselected >Moderated</option>
				<option value="public" $publicselected >Public</option>
				</select>
				&nbsp;
				Enter Forum Name : <input type="text" name="forum_name" value="$forum_name" size="30">
				&nbsp;
				Enter Forum Description : <input type="text" name="forum_desc" value="$forum_description" size="30">
				<br />
				<input type="submit" value="submit">
				</form>
PRE;
			}
			return $moderate;
		} else {
			$forum_id = $_GET['forum_id']; //Parent Thread ID
			$sql = "SELECT * FROM `$table_name` WHERE `forum_thread_id`=$forum_id LIMIT 1";
			$result1 = mysql_query($sql);
			$rows = mysql_fetch_array($result1);
			$forum_topic = parseubb(parsesmileys(stripslashes($rows['forum_thread_topic'])));
			$forum_detail = parseubb(parsesmileys(stripslashes($rows['forum_detail'])));
			$name = getUserName($rows['forum_thread_user_id']);
			$posts = $this->getTotalPosts($rows['forum_thread_user_id']);
			$reg_date = $this->getRegDateFromUserID($rows['forum_thread_user_id']);
			if ($rows['forum_post_approve'] == 0)
				{
					$text = "Approve";
					$img = "thumbs_up.gif";
				}
			else
				{
					$text = "Disapprove";
					$img = "thumbs_down.gif";
				}
			$subaction = strtolower($text);
			$postpart =<<<PRE
        <p align="left"><a href="+post&subaction=post_reply&thread_id=$forum_id"><img src="$temp/reply.gif" /></a>&nbsp;
        <a href="+post&subaction=create_thread"><img src="$temp/newthread.gif" /></a></p>
        <table width="100%" border="0" cellpadding="3" cellspacing="1" bordercolor="1" >
		<tr>
        <td class="forumTableCol" rowspan="2"><a href="+moderate&subaction=delete&thread_id=$rows[forum_thread_id]">
        <img src="$temp/b_drop.png" /></a></td>
		<td class="forumTableCol" rowspan="2"><a href="+moderate&subaction=$subaction&thread_id=$rows[forum_thread_id]">
		<img src="$temp/$img" /></a></td>
		<td class="forumTableRow"><strong>$forum_topic</strong><br /><img src="$temp/post_icon.gif" />
		 <small style="font-size:9px;">by $name on $rows[forum_thread_datetime] </small></td>
		<td class="forumTableRow" rowspan="2"><strong>$name <br />
PRE;
			if ($userId > 0 && $name != "Anonymous") {
				if ($rows['user_id'] == $userId)
					$lastLogin = $_SESSION['last_to_last_login_datetime'];
				else
					$lastLogin = $this->getLastLogin($rows['forum_thread_user_id']);
					$moderator=getPermissions($rows['forum_thread_user_id'], getPageIdFromModuleComponentId("forum",$this->moduleComponentId), "moderate");
					if($moderator)$postpart .= "Moderator";else
				$postpart .= "Member";
				$postpart .= '</strong><br /><br /><small>Posts: ' . $posts . ' <br />Joined: ' . $reg_date . ' <br />Last Visit:' . $lastLogin .
'</small>';
			}
			$postpart .=<<<PRE
	        </td>
	        </tr>
	        <tr>
	        <td class="forumTableRow"><br /> $forum_detail </td>
	        </tr>
PRE;
			$postpart .= "</table><br />";
			$sql2 = "SELECT * FROM `$table1_name` WHERE `forum_thread_id`=$forum_id ORDER BY forum_post_id ASC";
			$result2 = mysql_query($sql2);
			while ($rows = mysql_fetch_array($result2)) {
				$post_title = parseubb(parsesmileys(stripslashes($rows['forum_post_title'])));
				$post_content = parseubb(parsesmileys(stripslashes($rows['forum_post_content'])));
				$name = getUserName($rows['forum_post_user_id']);
				$posts = $this->getTotalPosts($rows['forum_post_user_id']);
				$reg_date = $this->getRegDateFromUserID($rows['forum_post_user_id']);
				if ($rows['forum_post_approve'] == 0)
					{
						$text = "Approve";
						$img = "thumbs_up.gif";
					}
				else
					{
						$text = "Disapprove";
						$img = "thumbs_down.gif";
					}
				$subaction = strtolower($text);
				$postpart .=<<<PRE
	        <a name="$rows[forum_thread_id].$rows[id]"></a>
	        <table width="100%" border="0" cellpadding="3" cellspacing="1" >
	        <td class="forumTableCol" rowspan="2" width="3%">
	        <a href="+moderate&subaction=delete&forum_id=$rows[forum_thread_id]&post_id=$rows[forum_post_id]"><img src="$temp/b_drop.png" /></a></td>
			<td class="forumTableCol" rowspan="2" width="3%">
			<a href="+moderate&subaction=$subaction&forum_id=$rows[forum_thread_id]&post_id=$rows[forum_post_id]"><img src="$temp/$img" /></a></td>
	        <td class="forumTableRow"><strong>Re:- $post_title </strong><br /><img src="$temp/post_icon.gif" />
	        <small style="font-size:9px;">by $name on $rows[forum_post_datetime] <small></td>
			<td class="forumTableRow" rowspan="2" width="20%"><strong>$name<br />
PRE;
				if ($userId > 0 && $name != "Anonymous") {
					if ($rows['forum_post_user_id'] == $userId)
						$lastLogin = $_SESSION['last_to_last_login_datetime'];
					else
						$lastLogin = $this->getLastLogin($rows['forum_post_user_id']);
						$moderator=getPermissions($rows['forum_post_user_id'], getPageIdFromModuleComponentId("forum",$this->moduleComponentId), "moderate");
					if($moderator)$postpart .= "Moderator";else
					$postpart .= "Member";
					$postpart .= '</strong><br /><br /><small>Posts: ' . $posts . ' <br />Joined: ' . $reg_date . ' <br />' .
							'Last Visit:' . $lastLogin . '</strong>';
				}
				$postpart .=<<<PRE
	        </td>
	        </tr>
	        <tr>
	        <td class="forumTableRow"><br />$post_content</td>
	        </tr>
	        </table>
PRE;
			}
			$query3 = "SELECT `forum_thread_viewcount` FROM `$table_name` WHERE forum_thread_id='$forum_id'";
			$result3 = mysql_query($query3);
			$rows = mysql_fetch_array($result3);
			$view = $rows['forum_thread_viewcount'];
			// count more value
			$addview = $view +1;
			$query5 = "UPDATE `$table_name` SET `forum_thread_viewcount`='$addview' WHERE forum_thread_id='$forum_id' LIMIT 1";
			$result5 = mysql_query($query5);
			$postpart .= '<br>
			        <p align="left"><a href="+post&subaction=post_reply&thread_id='.$forum_id.'"><img src="'.$temp.'/reply.gif" />' .
			        		'</a>&nbsp;<a href="+post&subaction=create_thread"><img src="'.$temp.'/newthread.gif" /></a></p>';
			return $postpart;
		}
	}

	public function actionPost() {
		$userId = $this->userId;
		$i = 0;
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
		$subaction = $_GET['subaction'];
		global $sourceFolder;
		global $moduleFolder;
		require_once ("$sourceFolder/$moduleFolder/forum/bbeditor.php");
		require_once ("$sourceFolder/$moduleFolder/forum/bbparser.php");
		$q = "SELECT * FROM `$table2_name` WHERE `page_modulecomponentid`=$this->moduleComponentId LIMIT 1";
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
		$temp= <<<PRE
		<p><strong>$forum_name</strong></p><br />
		<p align="right"><a href="+view"> >> Go Back to Forum</a>&nbsp;
PRE;
		if (isset ($_POST['post'])) {
			if (($subaction == "create_thread") ||( $subaction == "")) {

				if (!$_POST['subject'] || !$_POST['message']) {
					$editor = bbeditor();
					return "You did not fill all the fields!" . $editor;
				} else {
					$datetime = date("Y-m-d H:i:s");
					$message = $_POST['message'];
					$subject = $_POST['subject'];
					$message = nl2br($message);
					
					if (isset ($_POST['sticky'])&&($moderator))
						$category = "sticky";
					else
						$category = "general";
						

					$query="SELECT MAX(forum_thread_id) AS MAX FROM forum_threads";// WHERE `page_modulecomponentid`=$this->moduleComponentId";
					$result=mysql_query($query);
					$row1 = mysql_fetch_assoc($result);
					$threadid = $row1['MAX'] + 1;

					$sql = "INSERT INTO `$table_name`(`forum_thread_id` ,`page_modulecomponentid` ,`forum_thread_category` ,`forum_access_status` ," .
							"`forum_thread_topic` ,`forum_detail` ,`forum_thread_user_id` ,`forum_thread_datetime` ,`forum_post_approve` ," .
							"`forum_thread_viewcount` ,`forum_thread_last_post_userid` ,`forum_thread_lastpost_date`)" .
							" VALUES('$threadid', '$this->moduleComponentId', '$category', '$access', '$subject', '$message'," .
							" '$userId', '$datetime', '$approve', '','$userId', '$datetime')";
//							echo $sql;
					$result = mysql_query($sql) or die(mysql_error());
					if ($result) {
						$sql1 = "SELECT * FROM `$table2_name` WHERE `page_modulecomponentid`=$this->moduleComponentId LIMIT 1";
						$result1 = mysql_query($sql1);
						$rows1 = mysql_fetch_array($result1);
						$total_thread_count = $rows['total_thread_count'];
						// count more value
						$net_thread_count = $total_thread_count +1;
						$sql2 = "UPDATE `$table2_name` SET `total_thread_count`='$net_thread_count', `last_post_userid`='$userId'," .
								" `last_post_datetime`='$datetime' WHERE `page_modulecomponentid`=$this->moduleComponentId LIMIT 1";
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
						$forum_id = $_GET['thread_id'];
						$datetime = date("Y-m-d H:i:s");
						$message = $_POST['message'];
						$subject = $_POST['subject'];
						$message = nl2br($message);
						$sql7 = "SELECT MAX(forum_post_id) AS Maxpost_id FROM $table1_name WHERE forum_thread_id='$forum_id'";
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
						$result = mysql_query($sql);
						if ($result) {
							$sql1 = "SELECT * FROM `$table_name` WHERE `page_modulecomponentid`=$this->moduleComponentId AND `forum_thread_id`=$forum_id" .
									" LIMIT 1";
							$result1 = mysql_query($sql1);
							$rows1 = mysql_fetch_array($result1);
							$sql2 = "UPDATE `$table_name` SET  `forum_thread_last_post_userid`='$userId', " .
									"`forum_thread_lastpost_date`='$datetime' " .
									"WHERE `page_modulecomponentid`=$this->moduleComponentId AND `forum_thread_id`='$forum_id' LIMIT 1";
							$result2 = mysql_query($sql2);
							$sql3 = "SELECT * FROM `$table2_name` WHERE `page_modulecomponentid`=$this->moduleComponentId LIMIT 1";
							$result3 = mysql_query($sql3);
							$rows3 = mysql_fetch_array($result3);
							$sql4 = "UPDATE `$table2_name` SET  `last_post_userid`='$userId', " .
									"`last_post_datetime`='$datetime' WHERE `page_modulecomponentid`=$this->moduleComponentId LIMIT 1";
							$result4 = mysql_query($sql4);
							if(($rows1['forum_access_status']=="moderated")&& (!$moderator))
								displayinfo("You have successfully posted your reply.It will be published after getting the moderator's approval." .
										"<br />");
							else
								displayinfo("You have successfully posted your reply!");
						} else {
							displayerror("Sorry! Your reply could not be posted now. Please try again later!");
						}
						return $this->returnThread($forum_id);
					}
				}
		} else
			if (isset ($_POST['preview'])) {

				$message = $_POST['message'];
				$subject = $_POST['subject'];
				$text = $message;
				$message = parsesmileys($message);
				$message = parseubb($message);
				$message = nl2br($message);
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
		/*
		//to check last visit to the forum
		$table_visit = "forum_visits";
		$query_checkvisit = "SELECT * from `$table_visit` WHERE `user_id`=$userId AND `page_modulecomponentid`=$this->moduleComponentId";
		$result_checkvisit = mysql_query($query_checkvisit)or die(mysql_error());
		$check_visits = mysql_fetch_array($result_checkvisit);
		if(mysql_num_rows($result_checkvisit)<1) {
			$forum_lastviewed = date("Y-m-d H:i:s");
		}
		else {
			$forum_lastviewed = $check_visits['last_visit'];	
		}
		//set user's last visit
		$time_visit = date("Y-m-d H:i:s");
		$query_visit = "SELECT * FROM `$table_visit` WHERE `user_id`=$userId AND `page_modulecomponentid`=$this->moduleComponentId";
		$result_visit = mysql_query($query_visit)or die(mysql_error());
		$num_rows_visit = mysql_num_rows($result_visit);
		if($num_rows_visit<1) {
		  $query_setvisit = "INSERT INTO `$table_visit`(`page_modulecomponentid`,`user_id`,`last_visit`) VALUES($this->moduleComponentId,$userId,'$time_visit')";
		}
		else {
		  $query_setvisit = "UPDATE `$table_visit` SET `last_visit`='$time_visit' WHERE `user_id`=$userId AND `page_modulecomponentid`=$this->moduleComponentId"; 
		}
		mysql_query($query_setvisit)or die(mysql_error());
		//
		*/
		
		require_once ("$sourceFolder/$moduleFolder/forum/bbeditor.php");
		require_once ("$sourceFolder/$moduleFolder/forum/bbparser.php");
//		/*
		if (!isset ($_GET['thread_id'])) {
			if ($_GET['subaction'] == "delete_thread") {
				$thread_id = $_GET['forum_id'];
				$query = "DELETE FROM `$table_name` WHERE `forum_thread_id`=$thread_id LIMIT 1";
				$res = mysql_query($query);
				$query1 = "DELETE FROM `$table1_name` WHERE `forum_thread_id`=$thread_id";
				$res1 = mysql_query($query1);
				if (!res || !res1)
					displayerror("Could not perform the delete operation on the selected thread!");
			}
			
			$query = "SELECT * FROM `$table_name` WHERE `page_modulecomponentid`='" . $this->moduleComponentId . "' AND " .
					"`forum_thread_category`='general' ORDER BY `forum_thread_lastpost_date` DESC";
			$result = mysql_query($query);
			$query1 = "SELECT * FROM `$table_name` WHERE `page_modulecomponentid`='" . $this->moduleComponentId . "' AND " .
					"`forum_thread_category`='sticky' ORDER BY `forum_thread_datetime` DESC";
			$result1 = mysql_query($query1)or die(mysql_error());
			$num_rows1 = mysql_num_rows($result1); //counts the total no of sticky threads
			if ($result) {
				$action = "+post&subaction=create_thread";
				$num_rows = mysql_num_rows($result); //counts the total no of general threads
				$forumHtml .= $this->forumHtml($rows);
				/*
				$forum_header =<<<PRE
			<p align="left"><strong>$forum_name</strong></p><br />
			<p align="left"><a href="$action" style="color:#0F5B96"><img src="$temp/newthread.gif" /></a></p>

	        <table width="100%" border="0" align="center" cellpadding="3" cellspacing="1">
	        <tr>
	        <td class="forumTableHeaderSubject" colspan="2"><strong>Subject</strong><br /></td>
	        <td class="forumTableHeaderViews"> <strong>Views</strong></td>
	        <td class="forumTableHeaderReplies"><strong>Replies</strong></td>
	        <td class="forumTableHeaderPost"><strong>Last Post</strong></td>
	        </tr>

PRE;
				*/
				if ($result1 && $num_rows1 > 0) {
					for ($j = 1; $j <= $num_rows1; $j++) {
						$rows = mysql_fetch_array($result1,MYSQL_ASSOC);
						$query2 = "SELECT `forum_post_id` FROM `$table1_name` WHERE `forum_thread_id`='" . $rows['forum_thread_id'] . "' AND `forum_post_approve`='1'";
						$result2 = mysql_query($query2);
						$reply_count = mysql_num_rows($result2);
						$topic = parseubb(parsesmileys(stripslashes($rows['forum_thread_topic'])));
						$name = getUserName($rows['forum_thread_user_id']);
						$last_post_author = getUserName($rows['forum_thread_last_post_userid']);
						if ($rows['forum_post_approve'] == 1) {
							$forumHtml .= $this->forumHtml($rows,'threadRow');
							
							/*
							$forum_header .=<<<PRE1
						<p align="left"><b>$forum_name</b></p>
			            <tr>
			            <td class="forumTableCol" width="3%"><img src="$temp/pinned_topic_icon.gif" /></td>
			            <td class="forumTableRow" width="51%"><a href="+view&thread_id=$rows[forum_thread_id]"> $topic </a><br /><small>by <b> $name </a></b>
			             on $rows[forum_thread_datetime] </small></td>
			            <td class="forumTableCol" width="8%"> $rows[forum_thread_viewcount] </td>
			            <td class="forumTableCol" width="8%"> $reply_count </td>
			            <td class="forumTableRow" width="30%"><small>by <b> $last_post_author </a></b> on $rows[forum_thread_lastpost_date] </small></td>
			            </tr>
			            
PRE1;
						*/
						}
					}
				}
				if ($num_rows < 1)
					$forum_header .= "<tr><td colspan=\"5\" class='forumTableRow'><strong>No Post</strong></td></tr>";
				for ($i = 1; $i <= $num_rows; $i++) {
					$rows = mysql_fetch_array($result);
				
					/*
					 * 
						if($userId>0 && ($forum_lastviewed<$rows['forum_thread_lastpost_date']))
							$img_src = "new_posts_icon.gif";
						else
							$img_src = "no_new_posts_icon.gif";
					*/
								
					$query1 = "SELECT `forum_post_id` FROM `$table1_name` WHERE `forum_thread_id`='" . $rows['forum_thread_id'] . "' AND `forum_post_approve`='1'";
					$result1 = mysql_query($query1);
					$reply_count = mysql_num_rows($result1);
					$topic = parseubb(parsesmileys(stripslashes($rows['forum_thread_topic'])));
					$name = getUserName($rows['forum_thread_user_id']);
					$last_post_author = getUserName($rows['forum_thread_last_post_userid']);
					if ($rows['forum_post_approve'] == 1) {
						
						$forumHtml .= $this->forumHtml($rows,'threadRow');
						/*
						$forum_header .=<<<PRE1
            <tr>
            <td class="forumTableCol" width="3%"><img src="$temp/$img_src" /></td>
            <td class="forumTableRow" width="61%"><a href="+view&thread_id=$rows[forum_thread_id]"> $topic </a><br /><small>by <b> $name </a></b>
            on $rows[forum_thread_datetime] </small></td>
            <td class="forumTableCol" width="8%"> $rows[forum_thread_viewcount] </td>
            <td class="forumTableCol" width="8%"> $reply_count </td>
            <td class="forumTableRow" width="20%"><small>by <b> $last_post_author  </a></b> on $rows[forum_thread_lastpost_date] </small></td>
            </tr>
PRE1;
*/
					}
				}
		/*
				$forum_header .= '</table><br />
				            <p align="left"><img alt="" src="' . $temp . '/pinned_topic_icon.gif" align=left> &nbsp;- Sticky Threads.<br /><br />' .
				            		'<img alt="" src="' . $temp . '/new_posts_icon.gif" align=left> &nbsp;- Topic with new posts since last visit.' .
				            				'<br /><br /><img alt="" src="' . $temp . '/no_new_posts_icon.gif" align=left>' .
				            						'&nbsp;- Topic with no new posts since last visit. </p>';
		*/
			}
//			return $forum_header;
		} else {
			$thread_id = $_GET['thread_id']; //Parent Thread ID
			if ($_GET['subaction'] == "delete_post") {
				$post_id = $_GET['post_id'];
				$query = "DELETE FROM `$table1_name` WHERE `forum_thread_id`=$thread_id AND `forum_post_id`=$post_id LIMIT 1";
				$res = mysql_query($query);
				if (!res)
					displayerror("Could not perform the delete operation on the selected post!");
			}
			$sql = "SELECT * FROM `$table_name` WHERE `forum_thread_id`=$thread_id LIMIT 1";
			$result1 = mysql_query($sql);
			$rows = mysql_fetch_array($result1);
			$threadUserId = $rows['forum_thread_user_id'];
			$forum_topic = parseubb(parsesmileys(stripslashes($rows['forum_thread_topic'])));
			$forum_detail = parseubb(parsesmileys(stripslashes($rows['forum_detail'])));
			$name = getUserName($rows['forum_thread_user_id']);
			$posts = $this->getTotalPosts($rows['forum_thread_user_id']);
			$reg_date = $this->getRegDateFromUserID($rows['forum_thread_user_id']);
			$forumHtml = $this->forumHtml($rows,'threadHead');
			/*
			$postpart =<<<PRE
        <p align="left"><a href="+post&subaction=post_reply&thread_id=$thread_id"><img src="$temp/reply.gif" /></a>&nbsp;

        <a href="+post&subaction=create_thread"><img src="$temp/newthread.gif" /></a></p>
        <p align="right"><a href="+view"> >> Go Back to Forum</a>&nbsp;
		<table width="100%" border="0" cellpadding="3" cellspacing="1" bordercolor="1" >
PRE;
*/
			if ($rows['forum_post_approve'] == 1) {
				$forumHtml .= $this->forumHtml($rows,'threadMain');
				/*
				$postpart .= '<tr>
					        <td class="forumTableRow"><strong> ' . $forum_topic . ' </strong><br />' .
					        		'<img src="' . $temp . '/post_icon.gif" /><small style="font-size:9px;">by ' . $name . ' </a>' .
					        				' on ' . $rows[forum_thread_datetime] . ' </small></td>
					        <td class="forumTableRow" width="20%" rowspan="2"><strong> ' . $name . ' </a><br />';
				if ($threadUserId > 0) {
					if ($threadUserId == $userId)
						$lastLogin = $_SESSION['last_to_last_login_datetime'];
					else
						$lastLogin = $this->getLastLogin($threadUserId);
						$moderator=getPermissions($threadUserId, getPageIdFromModuleComponentId("forum",$this->moduleComponentId), "moderate");
					if($moderator)$postpart .= "Moderator";else
					$postpart .= "Member";
					$postpart .= '</strong><br /><br /><small>Posts: ' . $posts . ' <br />Joined: ' . $reg_date . ' <br />Last Visit:'
					. $lastLogin . '</small>';
				}
				$postpart .=<<<PRE
	        </td>
	        </tr>
	        <tr>
	        <td style="font-size:11px;"> <br />$forum_detail </td>
	        </tr>

PRE;
*/
			}
			$postpart .= "</table><br />";
			$sql2 = "SELECT * FROM `$table1_name` WHERE `forum_thread_id`=$thread_id AND `forum_post_approve` = 1 ORDER BY forum_post_id ASC";
			$result2 = mysql_query($sql2);
			while ($rows = mysql_fetch_array($result2)) {
				$rows['type'] = 'post';
				
				$forumHtml .= $this->forumHtml($rows,'threadMain');
				/*
				$postUserId = $rows['forum_post_user_id'];
				$post_title = parseubb(parsesmileys(stripslashes($rows['forum_post_title'])));
				$post_content = parseubb(parsesmileys(stripslashes($rows['forum_post_content'])));
				$name = getUserName($rows['forum_post_user_id']);
				$posts = $this->getTotalPosts($rows['forum_post_user_id']);
				$reg_date = $this->getRegDateFromUserID($rows['forum_post_user_id']);
				$postpart .=<<<PRE
	        <a name="$rows[forum_thread_id].$rows[id]"></a>
			<table width="100%" border="0" cellpadding="3" cellspacing="1" >
PRE;
				if ($rows['forum_post_approve'] == 1) {
					$postpart .= '<tr>
						        <td class="forumTableRow"><strong>Re:-' . $post_title . '</strong><br />' .
						        		'<img src="' . $temp . '/post_icon.gif" /><small style="font-size:9px;">by ' . $name . ' </a> ' .
						        				'on ' . $rows['forum_post_datetime'] . ' </small></td>
						        <td class="forumTableRow" rowspan="2" width="20%"><strong>' . $name . '</a><br />';
					if ($postUserId > 0) {
						if ($postUserId == $userId)
							$lastLogin = $_SESSION['last_to_last_login_datetime'];
						else
							$lastLogin = $this->getLastLogin($postUserId);
							$moderator=getPermissions($rows['forum_post_user_id'], getPageIdFromModuleComponentId("forum",$this->moduleComponentId), "moderate");
					if($moderator)$postpart .= "Moderator";else
						$postpart .= "Member";
						$postpart .= '</strong><br /><br /><small>Posts: ' . $posts . ' <br />Joined: ' . $reg_date . ' <br />Last Visit:'
						. $lastLogin . '</small>';
					}
					$postpart .=<<<PRE
	        </td>
	        </tr>
	        <tr>
	        <td class="forumTableRow"><br />$post_content</td>
	        </tr>
PRE;
					if ($userId > 0 && $userId == $rows['forum_post_user_id'])
					//compare the userID of the logged in user with that of the author of the current reply
						{
						$postpart .= '<tr><td class="forumTableCol" colpan="2">' .
								'<a href="+view&subaction=delete_post&thread_id=' . $thread_id . '&post_id=' . $rows['forum_post_id'] . '">' .
										'<img src="'.$temp.'/delete_sm.gif"</a></span></td></tr>';
					}
				}
				$postpart .= "</table>";
				*/
			}
		
			$sql3 = "SELECT `forum_thread_viewcount` FROM `$table_name` WHERE forum_thread_id='$thread_id'";
			$result3 = mysql_query($sql3);
			$rows = mysql_fetch_array($result3);
			$view = $rows['forum_thread_viewcount'];
			// count more value
			$addview = $view +1;
			$sql5 = "UPDATE `$table_name` SET `forum_thread_viewcount`='$addview' WHERE forum_thread_id='$thread_id' LIMIT 1";
			$result5 = mysql_query($sql5);
			$postpart .= '<br>
			        <p align="left"><a href="+post&subaction=post_reply&thread_id='.$thread_id.'">' .
			        		'<img src="'.$temp.'/reply.gif" /></a>&nbsp;<a href="+post&subaction=create_thread">' .
			        				'<img src="'.$temp.'/newthread.gif" /></a></p>';
//			return $postpart;
		}
//		*/
//$forumHtml .= '</table>';
	$forumHtml .= '</table><br />
				            <p align="left"><img alt="" src="' . $temp . '/pinned_topic_icon.gif" align=left> &nbsp;- Sticky Threads.<br /><br />' .
				            		'<img alt="" src="' . $temp . '/thread_new.gif" align=left> &nbsp;- Topic with new posts since last visit.' .
				            				'<br /><br /><img alt="" src="' . $temp . '/thread_hot.gif" align=left>' .
				            						'&nbsp;- Topic with no new posts since last visit. </p>';

		return $forumHtml;
	
	}
	
	private function forumHtml($data, $type='thread') {
		$thread_id = $_GET['thread_id']; 
		$forumHtml = '';
		$rows = $data;
		global $urlRequestRoot, $moduleFolder, $cmsFolder,$templateFolder,$sourceFolder,$userId;
		require_once ("$sourceFolder/$moduleFolder/forum/bbeditor.php");
		require_once ("$sourceFolder/$moduleFolder/forum/bbparser.php");
		$table_name = "forum_threads";
		$table1_name = "forum_posts";
		$templatesImageFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/".TEMPLATE;
		$temp = $urlRequestRoot . "/" . $cmsFolder . "/" . $moduleFolder . "/forum/images";
		$topic = parseubb(parsesmileys(stripslashes($rows['forum_thread_topic'])));
		$name = getUserName($rows['forum_thread_user_id']);
		$last_post_author = getUserName($rows['forum_thread_last_post_userid']);
		$action = "+post&subaction=create_thread";
		$query = "SELECT `forum_name`, `forum_description` from forum_module where page_modulecomponentid=$this->moduleComponentId";
		$resource = mysql_query($query);
		$result = mysql_fetch_assoc($resource);
		$forum_name = $result['forum_name'];
		if($type == 'thread')
		{
	        $forum_threads_header =<<<PRE
			<p align="left"><strong>$forum_name </strong></p><br />
			<p align="left"><a href="$action"><img src="$temp/newthread.gif" /></a></p>
	        <table width="100%" border="1" align="center" cellpadding="4" cellspacing="2" id="forum">
	        <tr class="TableHeader">
	        <td class="forumTableHeaderSubject" colspan="2"><strong>Subject</strong><br /></td>
	        <td class="forumTableHeaderViews"> <strong>Views</strong></td>
	        <td class="forumTableHeaderReplies"><strong>Replies</strong></td>
	        <td class="forumTableHeaderPost"><strong>Last Post</strong></td>
	        </tr>
PRE;
		$forumHtml = $forum_threads_header;
}
$forum_lastVisit = $this->forumLastVisit();
if($userId>0 && ($forum_lastVisit<$rows['forum_thread_lastpost_date']))
	{						
		$img_src = "thread_new.gif";
	}
	else
	{
		$img_src = "thread_hot.gif";
	}
if($type == 'threadRow')
{
	if($rows['forum_thread_category']=='sticky') {
	$img_src = 'pinned_topic_icon.gif';
}
	$query1 = "SELECT `forum_post_id` FROM `$table1_name` WHERE `forum_thread_id`='" . $rows['forum_thread_id'] . "' AND `forum_post_approve`='1'";
					$result1 = mysql_query($query1);
					$reply_count = mysql_num_rows($result1);
$forum_threads .=<<<PRE1
			            <tr>
			            <td class="forumTableIcon" width="3%"><img src="$temp/$img_src" /></td>
			            <td class="forumTableTopic" width="51%"><a href="+view&thread_id=$rows[forum_thread_id]"> $topic </a><br /><small>by <b> $name </a></b>
			             on $rows[forum_thread_datetime] </small></td>
			            <td class="forumTableViews" width="8%"> $rows[forum_thread_viewcount] </td>
			            <td class="forumTablePosts" width="8%"> $reply_count </td>
			            <td class="forumTableLast" width="30%"><small>by <b> $last_post_author </a></b> on $rows[forum_thread_lastpost_date] </small></td>
			            </tr>
			            
PRE1;
$forumHtml .= $forum_threads;
		}
		if($type == 'threadHead'){
			$thread_Header = '<p align="left"><strong>'.$forum_name .'</strong></p><br />
        <p align="left">';
        if($rows['forum_thread_category']!='sticky') {
        	$thread_Header .= '<a href="+post&subaction=post_reply&thread_id='.$thread_id.'"><img src="'.$temp.'/reply.gif" /></a>&nbsp';
        }
			$thread_Header .=<<<PRE
        <a href="+post&subaction=create_thread"><img src="$temp/newthread.gif" /></a></p>
        <p align="right"><a href="+view"> >> Go Back to Forum</a>&nbsp;
		<table width="100%" border="1" cellpadding="4" cellspacing="2" id="forum" >
PRE;
			$forumHtml = $thread_Header;
		}
		if($type == 'threadMain') {
			$threadUserId = $rows['forum_thread_user_id'];
//			$topic = parseubb(parsesmileys(stripslashes($rows['forum_thread_topic'])));
			$detail = parseubb(parsesmileys(stripslashes($rows['forum_detail'])));
//			$name = getUserName($rows['forum_thread_user_id']);
			$posts = $this->getTotalPosts($rows['forum_thread_user_id']);
			$reg_date = $this->getRegDateFromUserID($rows['forum_thread_user_id']);
			$postTime = $rows[forum_thread_datetime];

			if($rows['type'] == 'post'){
			$postUserId = $rows['forum_post_user_id'];
			$topic = parseubb(parsesmileys(stripslashes($rows['forum_post_title'])));
			$detail = parseubb(parsesmileys(stripslashes($rows['forum_post_content'])));
			$name = getUserName($rows['forum_post_user_id']);
			$posts = $this->getTotalPosts($rows['forum_post_user_id']);
			$reg_date = $this->getRegDateFromUserID($rows['forum_post_user_id']);
			$postTime = $rows['forum_post_datetime'];
			$threadUserId = $postUserId;
			}
					$threadHtml = '<tr class="ThreadHeadRow" cellspacing="10">
					        <td class="ThreadRowTopic"><strong> ' . $topic . ' </strong><br />' .
					        		'<img src="' . $temp . '/post_icon.gif" /><small style="font-size:9px;">by ' . $name . ' </a>' .
					        				' on ' . $postTime  . ' </small></td>
					        <td class="ThreadAuthorBox" width="20%" rowspan="2"><strong> ' . $name . ' </a><br />';
				if ($threadUserId > 0) {
					if ($threadUserId == $userId)
						$lastLogin = $_SESSION['last_to_last_login_datetime'];
					else
						$lastLogin = $this->getLastLogin($threadUserId);
						$moderator=getPermissions($threadUserId, getPageIdFromModuleComponentId("forum",$this->moduleComponentId), "moderate");
					if($moderator)$threadHtml .= "Moderator";else
					$threadHtml .= "Member";
					$threadHtml .= '</strong><br /><br /><small>Posts: ' . $posts . ' <br />Joined: ' . $reg_date . ' <br />Last Visit:'
					. $lastLogin . '</small>';
				}
				$threadHtml .=<<<PRE
	        </td>
	        </tr>
	        <tr>
	        <td class="forumContent"> <br />$detail </td>
	        </tr>
	        <tr class="blank"><td colspan="2"></td></tr>

PRE;
			$forumHtml .= $threadHtml;
		}
			
		return $forumHtml;
	}
	
	private function forumLastVisit() {
		global $userId;
	/*	//to check last visit to the forum
		if(!isset($_SESSION['forum_lastVisit'])){
		$table_visit = "forum_visits";
		$query_checkvisit = "SELECT * from `$table_visit` WHERE `user_id`=$userId AND `page_modulecomponentid`=$this->moduleComponentId";
		$result_checkvisit = mysql_query($query_checkvisit)or die(mysql_error());
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
		$query_visit = "SELECT * FROM `$table_visit` WHERE `user_id`=$userId AND `page_modulecomponentid`=$this->moduleComponentId";
		$result_visit = mysql_query($query_visit)or die(mysql_error());
		$num_rows_visit = mysql_num_rows($result_visit);
		if($num_rows_visit<1) {
		  $query_setvisit = "INSERT INTO `$table_visit`(`page_modulecomponentid`,`user_id`,`last_visit`) VALUES($this->moduleComponentId,$userId,'$time_visit')";
		}
		else {
		  $query_setvisit = "UPDATE `$table_visit` SET `last_visit`='$time_visit' WHERE `user_id`=$userId AND `page_modulecomponentid`=$this->moduleComponentId"; 
		}
		mysql_query($query_setvisit);//or die(mysql_error());
		}
		else {
			$forum_lastViewed = $_SESSION['forum_lastVisit'];
		}
		return $forum_lastViewed;*/
		//
	}
	public function returnThread($threadID)
	{
			$thread_id = $threadID;
			$userId = $this->userId;
			global $urlRequestRoot, $moduleFolder, $sourceFolder;
			$temp = $urlRequestRoot . "/" . $sourceFolder . "/" . $moduleFolder . "/forum/images";
			$table_name = "forum_threads";
			$table1_name = "forum_posts";
			require_once ("$sourceFolder/$moduleFolder/forum/bbeditor.php");
			require_once ("$sourceFolder/$moduleFolder/forum/bbparser.php");
			if ($_GET['subaction'] == 'delete_post')
			 {
				$post_id = $_GET['post_id'];
				$query = "DELETE FROM `$table1_name` WHERE `forum_thread_id`=$thread_id AND `forum_post_id`=$post_id LIMIT 1";
				$res = mysql_query($query);
				if (!res)
					displayerror("Could not perform the delete operation on the selected post!");
			}
			$sql = "SELECT * FROM `$table_name` WHERE `forum_thread_id`=$thread_id LIMIT 1";
			$result1 = mysql_query($sql);
			$rows = mysql_fetch_array($result1);
			$threadUserId = $rows['forum_thread_user_id'];
			$forum_topic = parseubb(parsesmileys(stripslashes($rows['forum_thread_topic'])));
			$forum_detail = parseubb(parsesmileys(stripslashes($rows['forum_detail'])));
			$name = getUserName($rows['forum_thread_user_id']);
			$posts = $this->getTotalPosts($rows['forum_thread_user_id']);
			$reg_date = $this->getRegDateFromUserID($rows['forum_thread_user_id']);
			$postpart =<<<PRE
        <p align="left"><a href="+post&subaction=post_reply&thread_id=$thread_id"><img src="$temp/reply.gif" /></a>&nbsp;
        <a href="+post&subaction=create_thread"><img src="$temp/newthread.gif" /></a></p>
		<table width="100%" border="0" cellpadding="3" cellspacing="1" bordercolor="1" >
PRE;
			if ($rows['forum_post_approve'] == 1) {
				$postpart .= '<tr>
					        <td style="font-size:13px;"><strong> ' . $forum_topic . ' </strong><br />' .
					        		'<img src="' . $temp . '/post_icon.gif" /><small style="font-size:9px;">by ' . $name . ' </a> ' .
					        				'on ' . $rows[forum_thread_datetime] . ' </small></td>
					        <td width="20%" rowspan="2" valign="top" width="20%"><strong> ' . $name . ' </a><br />';
				if ($threadUserId > 0) {
					if ($threadUserId == $userId)
						$lastLogin = $_SESSION['last_to_last_login_datetime'];
					else
						$lastLogin = $this->getLastLogin($threadUserId);
						$moderator=getPermissions($rows['forum_post_user_id'], getPageIdFromModuleComponentId("forum",$this->moduleComponentId), "moderate");
					if($moderator)$postpart .= "Moderator";else
					$postpart .= "Member";
					$postpart .= '</strong><br /><br /><small>Posts: ' . $posts . ' <br />Joined: ' . $reg_date . ' <br />' .
							'Last Visit:' . $lastLogin . '</small>';
				}
				$postpart .=<<<PRE
	        </td>
	        </tr>
	        <tr>
	        <td style="font-size:11px;"> <br />$forum_detail </td>
	        </tr>

PRE;
			}
			$postpart .= "</table><br />";
			$sql2 = "SELECT * FROM `$table1_name` WHERE `forum_thread_id`=$thread_id ORDER BY forum_post_id ASC";
			$result2 = mysql_query($sql2);
			while ($rows = mysql_fetch_array($result2)) {
				$postUserId = $rows['forum_post_user_id'];
				$post_title = parseubb(parsesmileys(stripslashes($rows['forum_post_title'])));
				$post_content = parseubb(parsesmileys(stripslashes($rows['forum_post_content'])));
				$name = getUserName($rows['forum_post_user_id']);
				$posts = $this->getTotalPosts($rows['forum_post_user_id']);
				$reg_date = $this->getRegDateFromUserID($rows['forum_post_user_id']);
				$postpart .=<<<PRE
	        <a name="$rows[forum_thread_id].$rows[id]"></a>
			<table width="100%" border="0" cellpadding="3" cellspacing="1" >
PRE;
				if ($rows['forum_post_approve'] == 1) {
					$postpart .= '<tr>
						        <td style="font-size:13px;"><strong>Re:-' . $post_title . '</strong><br />' .
						        		'<img src="' . $temp . '/post_icon.gif" /><small style="font-size:9px;">by ' . $name . ' </a>' .
						        				' on ' . $rows['forum_post_datetime'] . ' </small></td>
						        <td width="20%" rowspan="2" valign="top" width="20%"><strong>' . $name . '</a><br />';
					if ($postUserId > 0) {
						if ($postUserId == $userId)
							$lastLogin = $_SESSION['last_to_last_login_datetime'];
						else
							$lastLogin = $this->getLastLogin($postUserId);
							$moderator=getPermissions($rows['forum_post_user_id'], getPageIdFromModuleComponentId("forum",$this->moduleComponentId), "moderate");
					if($moderator)$postpart .= "Moderator";else

						$postpart .= "Member";
						$postpart .= '</strong><br /><br /><small>Posts: ' . $posts . ' <br />Joined: ' . $reg_date . ' <br />' .
								'Last Visit:' . $lastLogin . '</small>';
					}
					$postpart .=<<<PRE
	        </td>
	        </tr>
	        <tr>
	        <td style="font-size:11px;"><br />$post_content</td>
	        </tr>
PRE;
					if ($userId > 0 && $userId == $rows['forum_post_user_id'])
					 //compare the userID of the logged in user with that of the author of the current reply
						{
						$postpart .= '<tr><td colspan="2" align="right">' .
								'<a href="+view&subaction=delete_post&thread_id=' . $thread_id . '&post_id=' . $rows['forum_post_id'] . '">' .
										'<img src="'.$temp.'/delete_sm.gif"</a></span></td></tr>';
					}
				}
				$postpart .= "</table>";
			}
			$sql3 = "SELECT `forum_thread_viewcount` FROM `$table_name` WHERE forum_thread_id='$thread_id'";
			$result3 = mysql_query($sql3);
			$rows = mysql_fetch_array($result3);
			$view = $rows['forum_thread_viewcount'];
			// count more value
			$addview = $view +1;
			$sql5 = "UPDATE `$table_name` SET `forum_thread_viewcount`='$addview' WHERE forum_thread_id='$thread_id' LIMIT 1";
			$result5 = mysql_query($sql5);
			//			$subaction=&subaction=post_reply&thread_id='.$thread_id.'
			$postpart .= '<br>
			        <p align="left"><a href="+post&subaction=post_reply&thread_id='.$thread_id.'"><img src="'.$temp.'/reply.gif" />' .
			        		'</a>&nbsp;<a href="+post&subaction=create_thread"><img src="'.$temp.'/newthread.gif" /></a></p>';
			return $postpart;
	}

	public function createModule(& $moduleComponentId) {

		$query = "SELECT MAX(page_modulecomponentid) as MAX FROM `forum_module` ";
		$result = mysql_query($query) or die(mysql_error() . "forum.lib L:70");
		$row = mysql_fetch_assoc($result);
		$compId = $row['MAX'] + 1;

		$query = "INSERT INTO `forum_module` (`page_modulecomponentid` )VALUES ('$compId')";
		$result = mysql_query($query) or die(mysql_error() . "article.lib L:76");
		if (mysql_affected_rows()) {
			$moduleComponentId = $compId;
			return true;
		} else
			return false;

	}

	public function deleteModule($moduleComponentId) {
		$query = "DELETE FROM `forum_posts` WHERE `page_modulecomponentid`=$moduleComponentId";
		$result = mysql_query($query);
		$query1 = "DELETE FROM `forum_threads` WHERE `page_modulecomponentid`=$moduleComponentId";
		$result1 = mysql_query($query1);
		$query2 = "DELETE FROM `forum_module` WHERE `page_modulecomponentid`=$moduleComponentId";
		$resul2 = mysql_query($query2);
		if ((mysql_affected_rows()) >= 1)
			return true;
		else
			return false;
	}

	public function copyModule($moduleComponentId) {
$query = "SELECT MAX(page_modulecomponentid) as MAX FROM `forum_module` ";
		$result = mysql_query($query) or displayerror(mysql_error() . "Copy for forum failed L:866");
		$row = mysql_fetch_assoc($result);
		$compId = $row['MAX'] + 1;
		//insert a new row in forum_module
		$query = "SELECT * FROM `forum_module` WHERE `page_modulecomponentid`=$moduleComponentId";
		$result = mysql_query($query);
		$rows = mysql_num_rows($result);
		while($forummodule_content = mysql_fetch_assoc($result)){
			$forummodule_query="INSERT INTO `forum_module` (`page_modulecomponentid` ,`forum_name` ,`forum_description` ,`forum_moderated` ," .
					"`total_thread_count` ,`last_post_userid` ,`last_post_datetime` )" .
					" VALUES ($compId, '".mysql_escape_string($forummodule_content['forum_name'])."', " .
							"'".mysql_escape_string($forummodule_content['forum_description'])."'," .
									" '".mysql_escape_string($forummodule_content['forum_moderated'])."'," .
											" '".mysql_escape_string($forummodule_content['total_thread_count'])."' , " .
//													"'".mysql_escape_string($forummodule_content['total_reply_count'])."' ," .
															" '".mysql_escape_string($forummodule_content['last_post_userid'])."', " .
																	"'".mysql_escape_string($forummodule_content['last_post_datetime'])."')";
			mysql_query($forummodule_query) or displayerror(mysql_error()."Copy for forum failed L:878");
			$rows -= mysql_affected_rows();
		}
		if($rows!=0)
			return false;
		//insert a new row in forum_posts
		$query = "SELECT * FROM `forum_posts` WHERE `page_modulecomponentid`=$moduleComponentId";
		$result = mysql_query($query);
		$rows = mysql_num_rows($result);

		while($forumanswer_content = mysql_fetch_assoc($result)){
			$forumanswer_query="INSERT INTO `forum_posts` (`page_modulecomponentid` ,`forum_thread_id` ,`forum_post_id` ,`forum_post_user_id` ,`forum_post_title` ," .
					"`forum_post_content` ,`forum_post_datetime` ,`forum_post_approve`) VALUES ($compId, '".mysql_escape_string($forumanswer_content['forum_thread_id'])."'," .
							" '".mysql_escape_string($forumanswer_content['forum_post_id'])."', '".mysql_escape_string($forumanswer_content['forum_post_user_id']).
"', '".mysql_escape_string($forumanswer_content['forum_post_title'])."' , '".mysql_escape_string($forumanswer_content['forum_post_content'])."" .
		"' , '".mysql_escape_string($forumanswer_content['forum_post_datetime'])."', '".mysql_escape_string($forumanswer_content['forum_post_approve'])."')";
			mysql_query($forumanswer_query) or displayerror(mysql_error()."Copy for forum failed L:886");
			$rows -= mysql_affected_rows();
		}
		if($rows!=0)
			return false;
		//insert a new row in forum_threads
		$query = "SELECT * FROM `forum_threads` WHERE `page_modulecomponentid`=$moduleComponentId";
		$result = mysql_query($query);
		$rows = mysql_num_rows($result);
		while($forumquestion_content = mysql_fetch_assoc($result)){
			$forumquestion_query="INSERT INTO `forum_threads` (`page_modulecomponentid` ,`forum_thread_id` ,`forum_thread_category` ," .
					"`forum_access_status` ,`forum_thread_topic` ,`forum_detail` ,`forum_thread_user_id` ,`forum_thread_datetime` ,`forum_post_approve` ,`forum_thread_viewcount` ," .
					"`forum_thread_last_post_userid` ,`forum_thread_lastpost_date`) VALUES ($compId," .
					" '".mysql_escape_string($forumquestion_content['forum_thread_id'])."', " .
							"'".mysql_escape_string($forumquestion_content['forum_thread_category'])."'," .
									" '".mysql_escape_string($forumquestion_content['forum_access_status'])."'," .
											" '".mysql_escape_string($forumquestion_content['forum_thread_topic'])."' ," .
													" '".mysql_escape_string($forumquestion_content['forum_detail'])."' , " .
	"'".mysql_escape_string($forumquestion_content['forum_detail'])."', " .
			"'".mysql_escape_string($forumquestion_content['forum_thread_datetime'])."'," .
					" '".mysql_escape_string($forumquestion_content['forum_post_approve'])."', " .
							"'".mysql_escape_string($forumquestion_content['forum_thread_viewcount'])."'," .
//									" '".mysql_escape_string($forumquestion_content['reply_count'])."'," .
											" '".mysql_escape_string($forumquestion_content['forum_thread_last_post_userid'])."', " .
													"'".mysql_escape_string($forumquestion_content['forum_thread_lastpost_date'])."')";
			mysql_query($forumquestion_query) or displayerror(mysql_error()."Copy for forum failed L:908");
			$rows -= mysql_affected_rows();
		}
		if($rows!=0)
			return false;
		return $compId;
	}
}

