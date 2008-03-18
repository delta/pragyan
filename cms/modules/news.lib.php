<?php
/*
 * Created on Oct 17, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

 class news implements module {
	private $userId;
	private $moduleComponentId;
	private $action;


	private function getNews() {
		$result=mysql_query("SELECT * FROM `news_desc` WHERE `page_modulecomponentid` = $this->moduleComponentId");
		$query=mysql_fetch_array($result);

		$rss_output1 = 	"<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?><rss version=\"2.0\" xmlns:media=\"http://search.yahoo.com/mrss/\">";
		$rss_output1 .= <<<TTT
		<channel>
    <title> {$query['news_title']} </title>
    <description> {$query['news_description']} </description>
    <link>http://www.pragyan.org/08/</link>
     <language>en-gb</language>
    <copyright>Pragyan 2008 Computer and Technical Support Team, 2007-2008</copyright>
TTT;

		$query1=mysql_query("SELECT * FROM `news_data` WHERE `page_modulecomponentid` = $this->moduleComponentId ORDER BY `news_rank`");
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
		$query="SELECT * FROM `news_data` WHERE 	`page_modulecomponentid`=$moduleCompId ORDER BY `news_rank`,`news_id` ";
		$query="SELECT * FROM `news_data` WHERE `page_modulecomponentid`=$moduleCompId  ORDER BY `news_rank`,`news_id`";
		$result=mysql_query($query) or die (mysql_error());
		$i=0;
		while($news=mysql_fetch_assoc($result)){
			foreach($news as $var=>$val)
			{

				$newsArray[$i][$var]=$val;//Anshu


/*
				if ($var=='title') {$newsArray[$i]['title']=$val; }
				elseif ($var=='feed'){$newsArray[$i]['description']=$val;}
				elseif ($var=='link'){$newsArray[$i]['link']=$val;}
				elseif($var=='news_id'){$newsArray[$i]['id']=$val;}*/
//				elseif
			}
			$i=$i+1;
		}

		return $newsArray;
	}

	public function actionRssview() {
		header('Content-type: application/rss+xml; charset=utf-8');
		echo $this->getNews();
		exit;
	}

	public function actionEdit() {
		if(isset($_GET['subaction'])) {
			if(isset($_GET['newsid']) && ctype_digit($_GET['newsid'])) {
				if($_GET['subaction'] == 'deletenews') {
					$query = "DELETE FROM `news_data` WHERE `news_id`='{$_GET['newsid']}' AND `page_modulecomponentid`='$this->moduleComponentId'";
					$result = mysql_query($query);
					displayinfo('News feed has been successfully deleted.');
				}
				elseif($_GET['subaction'] == 'editnews') {
					$query = "SELECT * FROM `news_data` WHERE `news_id`={$_GET['newsid']} AND `page_modulecomponentid` = $this->moduleComponentId";
					$result = mysql_query($query);
					$row = mysql_fetch_assoc($result);
					$editForm = <<<EDITFORM
					 	<form action="./+edit" method="POST">
							Title of News Item  <input type="text" name="title" size="50" value="{$row['news_title']}"><br /><br />
							News Description  <br><textarea name="feed" cols="50" rows="10">{$row['news_feed']}</textarea><br />
							Rank/Importance of Feed  <input type="text" name="rank" size="10" value="{$row['news_rank']}" /><br /><br />
							Relative link  <input type="text" name="link" size=40 value="{$row['news_link']}" ><br><br>
							<input type="submit" value="Save Changes" name="btnSaveChanges"/>
							<input type="hidden" name="newsid" value="{$row['news_id']}" />
				  	</form>
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
					$query2 = "INSERT INTO `news_data` (`page_modulecomponentid`, `news_id`, `news_title`, `news_feed`, `news_rank`,`news_link`) VALUES('$this->moduleComponentId','$news_id','{$_POST['title']}','{$_POST['feed']}','{$_POST['rank']}','{$_POST['link']}')";
		 			$result = mysql_query($query2) or die(mysql_error() . '<br />' . $query2);
				}
				else {
					$addnews='<form action="./+edit&subaction=addnews" method="POST">
								Title of News Item  <input type="text" name="title" size=50><br><br>
								News Description  <br><textarea name="feed" cols="50" rows="10"> </textarea><br>
								Rank/Importance of Feed <input type="text" name="rank" size=10><br><br>' .
										'Relative link  <input type="text" name="link" size=40><br><br>
								<input type="submit" name="btnAddNews" value="Submit News Feed">
								</form>';
					return $addnews;
				}
			}
		}
		elseif(isset($_POST['btnSaveChanges']) && isset($_POST['newsid'])) {
			$query = "UPDATE `news_data` SET `news_title`='{$_POST['title']}',`news_feed`='{$_POST['feed']}',`news_rank`='{$_POST['rank']}',`news_link`='{$_POST['link']}' WHERE `news_id`={$_POST['newsid']} AND `page_modulecomponentid`=$this->moduleComponentId";
			$result = mysql_query($query);
			displayinfo("News feed has been successfully updated.");
		}

		$query="SELECT * FROM `news_data` WHERE `page_modulecomponentid`='$this->moduleComponentId' ORDER BY `news_rank`,`news_id`";
		$result=mysql_query($query);

		$rowCount = mysql_num_rows($result);
		$news = '<form name="newsedit" action="./+edit" method="POST">';
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
		global $urlRequestRoot, $sourceFolder, $templateFolder;
		$editImage = "<img style=\"padding:0px\" src=\"$urlRequestRoot/$sourceFolder/$templateFolder/common/icons/16x16/apps/accessories-text-editor.png\" alt=\"Edit\" />";
		$deleteImage = "<img style=\"padding:0px\" src=\"$urlRequestRoot/$sourceFolder/$templateFolder/common/icons/16x16/actions/edit-delete.png\" alt=\"Delete\" />";

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
		$news .= "</table>\n";
		$addImage = '<img src="' . "$urlRequestRoot/$sourceFolder/$templateFolder/common/icons/16x16/actions/list-add.png" . '"alt="Add New Question" style="padding:0px" />';
		$news .= '<br /><br /><a href="./+edit&subaction=addnews">' . $addImage . 'Add News</a><br /><br />';
		return $news;
	}



	public function createModule(&$moduleComponentId) {
		$query = "SELECT MAX(page_modulecomponentid) as MAX FROM `news_data` ";
		$result = mysql_query($query) or die(mysql_error() . "newws.lib L:73");
		$row = mysql_fetch_assoc($result);
		$compId = $row['MAX'] + 1;

		$query = "INSERT INTO `news_data` (`page_modulecomponentid` ,`news_title`,`news_feed`)VALUES ('$compId', 'New news!')";
		$result = mysql_query($query); // or die(mysql_error()."article.lib L:76");
		if (mysql_affected_rows()) {
			$moduleComponentId = $compId;
			return true;
		} else
			return false;

	}
	public function deleteModule($moduleComponentId){

	}



	public function copyModule($moduleComponentId){

	}

	public function actionView()
	{
		$moduleCompId=$this->moduleComponentId;
		$newsId=isset($_GET['id'])?$_GET['id']:"";
		$newsId = $newsId;
		if($newsId=='')
		{
			$query="SELECT `news_title` FROM `news_desc` WHERE `page_modulecomponentid`=$moduleCompId";
			$result=mysql_query($query) or die(mysql_error()."news.libL247");
			$temp=mysql_fetch_assoc($result);
			$newsView.="<h1>$temp[news_title]</h1><br>";
			 $cond="";


		}
		 else$cond="AND `news_id`=$newsId";
		 $query="SELECT * FROM `news_data` WHERE `page_modulecomponentid`=$moduleCompId $cond";
		 $result=mysql_query($query);// or die (mysql_error()."news.lib 247");
		 while($newsResult=mysql_fetch_assoc($result))
		 {
		 $newsView.=<<<NEWS
		 <h2><a href="$newsResult[news_link]"> $newsResult[news_title]</a></h2>
		 <p>$newsResult[news_feed]</p>
NEWS;
		 }
return $newsView;
	}
 }
?>
