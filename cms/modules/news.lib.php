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
 * @author Ankit Srivastava
 * @copyright (c) 2008 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
 
//NOTE (by Abhishek) : I've deliberately not used safe_html in NEWS module so as to give the user the freedom to use HTML tags to specify stuff like highlighted news,colored news, images, etc ... 

 class news implements module {
	private $userId;
	private $moduleComponentId;
	private $action;


	private function getNews() {
		$result=mysql_query("SELECT * FROM `news_desc` WHERE `page_modulecomponentid` = '$this->moduleComponentId'");
		$query=mysql_fetch_array($result);

		$rss_output1 ="<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?><rss version=\"2.0\" xmlns:media=\"http://search.yahoo.com/mrss/\">";
		$rss_output1 .= <<<TTT
		<channel>
    <title> {$query['news_title']} </title>
    <description> {$query['news_description']} </description>
    <link> {$query['news_link']} </link>
     <language>en-gb</language>
    <copyright> {$query['news_copyright']} </copyright>
TTT;

		$query1=mysql_query("SELECT * FROM `news_data` WHERE `page_modulecomponentid` = '$this->moduleComponentId' ORDER BY `news_rank`");
		while($myrow=mysql_fetch_array($query1)){

			$rss_output1.=<<<RSSOUTPUT

    <item>
      <title>{$myrow['news_title']}</title>
      <description>{$myrow['news_feed']}</description>
      <link>http://www.pragyan.org/{$myrow['news_link']}</link>
      <pubDate>{$myrow['news_date']}</pubDate>
    </item>
RSSOUTPUT;

		}

		$rss_output1.="\n  </channel>\n</rss>\n";
		return $rss_output1;
	}


	public function getHtml($gotuid, $gotmoduleComponentId, $gotaction) {
		$this->userId = $gotuid;
		$this->moduleComponentId = $gotmoduleComponentId;
		$this->action = $gotaction;

		if($gotaction=='view')
			return $this->actionView();
		if($gotaction=='edit')
			return $this->actionEdit();
		if($gotaction == 'rssview')
			return $this->actionRssview();

	}

	/**Returns news array
	 * @param $moduleCompId	ModuleComponenetId of the news array
     * @return An array of the form  :  a[0][element] = data
     * 									where element is title, description and link
	 */
	public function getNewsArray($moduleCompId) {
	
	  //changes added to news module by Kulz to fetch news to main menu etc with same function
	
		if($moduleCompId<>0)
		  $query="SELECT * FROM `news_data` WHERE `page_modulecomponentid`='$moduleCompId'  ORDER BY `news_rank`,`news_id`";
		else
		  $query="SELECT * FROM `news_data` ORDER BY `news_rank`,`news_id`";
		$result=mysql_query($query) or die (mysql_error());
		$i=0;
		while($news=mysql_fetch_assoc($result)){
			foreach($news as $var=>$val)
				$newsArray[$i][$var]=$val;
			$i++;
		}

		return $newsArray;
	}

	public function actionRssview() {
		header('Content-type: application/rss+xml; charset=utf-8');
		echo $this->getNews();
		exit;
	}

	public function actionEdit() {
	
	
	$validateScript=<<<VALSCRIPT
	<script type="text/javascript">
	function trim(str)
		{
			 return str.replace(/^\s+|\s+$/g, '');
		}

	function validate_empty()
		{
			var empty = 0;
			var title = trim(document.AddNews.title.value);
			var feed  = trim(document.AddNews.feed.value);
	
			if(title.length == 0)
				{
					empty++;
					alert("The title should not be left blank");
					document.AddNews.title.focus();
				}
			else if(feed.length == 0)
				{
					empty++;
					alert("Enter a Description of the News");
					document.AddNews.feed.focus();
				}
			return (empty == 0);
		}
	</script>
VALSCRIPT;
		if(isset($_GET['subaction'])) {
			global $ICONS;
			if(isset($_GET['newsid']) && ctype_digit($_GET['newsid'])) {
				if($_GET['subaction'] == 'deletenews') {
					$query1 = "SELECT * FROM `news_data` WHERE `news_id`='".escape($_GET['newsid'])."' AND `page_modulecomponentid` = '$this->moduleComponentId'";
					$result = mysql_query($query1);
					$row = mysql_fetch_assoc($result);
	
					$query = "DELETE FROM `news_data` WHERE `news_id`='".escape($_GET['newsid'])."' AND `page_modulecomponentid`='$this->moduleComponentId'";
					$result = mysql_query($query);
					displayinfo('News feed has been successfully deleted.');
				}
				elseif($_GET['subaction'] == 'editnews') {
					$query = "SELECT * FROM `news_data` WHERE `news_id`='".escape($_GET['newsid'])."' AND `page_modulecomponentid` = '$this->moduleComponentId'";
					$result = mysql_query($query);
					$row = mysql_fetch_assoc($result);
					$editForm = <<<EDITFORM
						$validateScript
					 	<fieldset><legend>{$ICONS['News Edit']['small']} Edit News<legend><form name="AddNews" action="./+edit" method="POST" onsubmit="return validate_empty();">
							Title of News Item  <input type="text" name="title" id="title" size="50" value="{$row['news_title']}"><br /><br />
							News Description  <br><textarea name="feed" id="feed" cols="50" rows="10">{$row['news_feed']}</textarea><br />
							Rank/Importance of Feed  <input type="text" name="rank" size="10" value="{$row['news_rank']}" /><br /><br />
							Relative link  <input type="text" name="link" size=40 value="{$row['news_link']}" ><br><br>
							<input type="submit" value="Save Changes" name="btnSaveChanges"/>
							<input type="hidden" name="newsid" value="{$row['news_id']}" />
				  	</form></fieldset>
EDITFORM;

					return $editForm;
				}
			}
			elseif($_GET['subaction'] == 'addnews') {
				if(isset($_POST['btnAddNews'])) {
					$query1 = "SELECT MAX(`news_id`) FROM `news_data` WHERE `page_modulecomponentid`='$this->moduleComponentId'";
					$result = mysql_query($query1);
					$resultArray = mysql_fetch_row($result);
					$news_id = 1;
					if(!is_null($resultArray[0]))
						$news_id = $resultArray[0] +1;
					$query2 = "INSERT INTO `news_data` (`page_modulecomponentid`, `news_id`, `news_title`, `news_feed`, `news_rank`,`news_link`) VALUES('$this->moduleComponentId','$news_id','".escape($_POST['title'])."','".escape($_POST['feed'])."','".escape($_POST['rank'])."','".escape($_POST['link'])."')";
		 			$result = mysql_query($query2) or die(mysql_error() . '<br />' . $query2);
				}
				else {
				
					$addnews=<<<NEWS
$validateScript
<fieldset><legend>{$ICONS['News Add']['small']} Add News<legend>
<form name="AddNews" action="./+edit&subaction=addnews" method="POST" onsubmit="return validate_empty()">
								Title of News Item  <input type="text" name="title" id="title" size=50 /><br><br>
								News Description  <br><textarea name="feed" id="feed" cols="50" rows="10"> </textarea><br>
								Rank/Importance of Feed <input type="text" name="rank" size=10 /><br><br>' .
										'Relative link  <input type="text" name="link" size=40 /><br><br>
								<input type="submit" name="btnAddNews" value="Submit News Feed" />
								</form></fieldset>
NEWS;
					return $addnews;
				}
			}
		}
		elseif(isset($_POST['btnSaveChanges']) && isset($_POST['newsid'])) {
			$query = "UPDATE `news_data` SET `news_title`='".escape($_POST['title'])."',`news_feed`='".escape($_POST['feed'])."',`news_rank`='".escape($_POST['rank'])."',`news_link`='".escape($_POST['link'])."' WHERE `news_id`='".escape($_POST['newsid'])."' AND `page_modulecomponentid`='$this->moduleComponentId'";
			$result = mysql_query($query);
			displayinfo("News feed has been successfully updated.");
		}
		if(isset($_POST['btnNewsPropSave'])) {
			$query = "UPDATE `news_desc` SET `news_title` = '".escape($_POST['news_title'])."', `news_description`='".escape($_POST['news_desc'])."', `news_link`='".escape($_POST['news_link'])."', `news_copyright`='".escape($_POST['news_copyright'])."' WHERE `page_modulecomponentid` = '{$this->moduleComponentId}'";
			if(mysql_query($query))
				displayinfo("News Page Properties has been successfully updated.");
			else
				displayerror("There has been some error in updating Properties.");
		}

		$query="SELECT * FROM `news_data` WHERE `page_modulecomponentid`='$this->moduleComponentId' ORDER BY `news_rank`,`news_id`";
		$result=mysql_query($query);
		$descResult = mysql_fetch_assoc(mysql_query("SELECT * FROM `news_desc` WHERE `page_modulecomponentid` = '{$this->moduleComponentId}'"));
		$rowCount = mysql_num_rows($result);
		global $ICONS;
		$news = "<form method=POST action='./+edit'>";
		$news .= "<table width=100%><tr><td>Title:</td><td><input name='news_title' type='text' value='{$descResult['news_title']}'></td></tr>";
		$news .= "<tr><td>Description:</td><td><textarea name='news_desc'>{$descResult['news_description']}</textarea></td></tr>";
		$news .= "<tr><td>Link:</td><td><input name='news_link' type='text' value='{$descResult['news_link']}'></td></tr>";
		$news .= "<tr><td>Copyright:</td><td><textarea name='news_copyright'>{$descResult['news_copyright']}</textarea></td></tr>";
		$news .= "<tr><td></td><td><input type='submit' value='Save' name='btnNewsPropSave'></td></tr></table>";
		$news .= "</form>";
		$news .= "<fieldset><legend>{$ICONS['News Edit']['small']} Edit News<legend><form name=\"newsedit\" action=\"./+edit\" method=\"POST\">";
		$news.=<<<CHECKDEL
		<script language="javascript">

			function checkDelete(butt,fileDel) {
				if(confirm('Are you sure you want to delete news id'+fileDel+'?')) {
					window.location+= '&subaction=deletenews&newsid='+fileDel;
				}
				else
					return false;
			}
	    </script>

CHECKDEL;
		global $urlRequestRoot, $sourceFolder, $templateFolder,$cmsFolder;
		$editImage = "<img style=\"padding:0px\" src=\"$urlRequestRoot/$cmsFolder/$templateFolder/common/icons/16x16/apps/accessories-text-editor.png\" alt=\"Edit\" />";
		$deleteImage = "<img style=\"padding:0px\" src=\"$urlRequestRoot/$cmsFolder/$templateFolder/common/icons/16x16/actions/edit-delete.png\" alt=\"Delete\" />";

		
		$news .= "<table frame=\"vsides\" border=\"1\" width=\"100%\">";
		$news .="<tr><th>Sl. No.</th><th>Edit</th><th>Delete</th><th>News ID</th><th>Title</th><th>Feed</th><th>Rank</th><th>Date</th><th>Link</th></tr>";
		$i = 1;
		while($row=mysql_fetch_assoc($result)) {
			$news .=
					'<tr align="center"><td>'.$i.'</td><td><a href="./+edit&subaction=editnews&newsid='.$row['news_id'].'">' . $editImage . '</a></td>' .
					'<td><a onclick="return checkDelete(this, \''.$row['news_id'].'\');" >' . $deleteImage . '</a></td>';
			$news .= "<td>{$row['news_id']}</td><td>{$row['news_title']}</td><td>{$row['news_feed']}</td><td>{$row['news_rank']}</td><td>{$row['news_date']}</td><td><a href=\"$row[news_link]\">{$row['news_link']}</a></td></tr>\n";
			++$i;
		}
		$news .= <<<END
</table>
<br /><input type=button value='Add News' onClick='window.location="./+edit&subaction=addnews"'> <input type=button value='View News' onClick='window.location="./+view"'></form></fieldset>
END;
		return $news;
	}



	public function createModule($moduleComponentId) {
		$globalSettings = getGlobalSettings();
		mysql_query("INSERT INTO `news_desc` (`page_modulecomponentid` ,`news_copyright`)VALUES ('$compId', '{$globalSettings['cms_footer']}')");
	}
	public function deleteModule($moduleComponentId){
		return true;
	}



	public function copyModule($moduleComponentId,$newId){
		return true;
	}

	public function actionView()
	{
		$moduleCompId=$this->moduleComponentId;
		$newsId=isset($_GET['id'])?escape($_GET['id']):"";

		$newsView = "";
		if($newsId=='')
		{
			$query="SELECT * FROM `news_desc` WHERE `page_modulecomponentid`='$moduleCompId'";
			$result=mysql_query($query) or die(mysql_error()."news.lib L247");
			$temp=mysql_fetch_assoc($result);
			$newsView.="<h1><a href='{$temp['news_link']}'>{$temp['news_title']}</a></h1><br>";
			$cond="";


		}
		else
			$cond="AND `news_id`='$newsId'";
		$query="SELECT * FROM `news_data` WHERE `page_modulecomponentid`='$moduleCompId' $cond ORDER BY `news_rank`, `news_id`";
		$result=mysql_query($query);// or die (mysql_error()."news.lib 247");
		while($newsResult=mysql_fetch_assoc($result))
		{
			$newsView.=<<<NEWS
<div class='pragyan_newsbox'>
				<h3><a href="$newsResult[news_link]"> $newsResult[news_title]</a></h3>
				<p>$newsResult[news_feed]</p>
</div>
NEWS;

		}
		$newsView .= "<br>" .$temp['news_copyright'];
		return $newsView;
	}
 }

