<?php 
/*******************************************
* Sphider Version 1..3.*
* This program is licensed under the GNU GPL.
* By Ando Saabas           ando(a t)cs.ioc.ee
********************************************/

error_reporting (E_ALL ^ E_NOTICE);

$include_dir = "../include";
include "auth.php";
include "$include_dir/commonfuncs.php";
extract (getHttpVars());
$settings_dir = "../settings";
$template_dir = "../templates";
include "$settings_dir/conf.php";
set_time_limit (0);


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>Sphider administrator tools</title>
<link rel="stylesheet" href="admin.css" type="text/css" />
</head>
<body>
<?php 
if (!isset($f)) {
	$f=2;
}
$site_funcs = Array (22=> "default",21=> "default",4=> "default", 19=> "default", 1=> "default", 2 => "default", "add_site" => "default", 20=> "default", "edit_site" => "default", 5=>"default");
$stat_funcs = Array ("statistics" => "default",  "delete_log"=> "default");
$settings_funcs = Array ("settings" => "default");
$index_funcs = Array ("index" => "default");
$index_funcs = Array ("index" => "default");
$clean_funcs = Array ("clean" => "default", 15=>"default", 16=>"default", 17=>"default", 23=>"default");
$cat_funcs = Array (11=> "default", 10=> "default", "categories" => "default", "edit_cat"=>"default", "delete_cat"=>"default", "add_cat" => "default", 7=> "default");
$database_funcs = Array ("database" => "default");
?>

<div id="admin"> 
	<div id="tabs">
		<ul>
		<?php 	
		if ($stat_funcs[$f] ) {
			$stat_funcs[$f] = "selected";
		} else {
			$stat_funcs[$f] = "default";
		}

		if ($site_funcs[$f] ) {
			$site_funcs[$f] = "selected";
		}else {
			$site_funcs[$f] = "default";
		}

		if ($settings_funcs[$f] ) {
			$settings_funcs[$f] = "selected";
		} else {
			$settings_funcs[$f] = "default";
		} 

		if ($index_funcs[$f] ) {
			$index_funcs[$f]  = "selected";
		} else {
			$index_funcs[$f] = "default";
		} 

		if ($cat_funcs[$f] ) {
			$cat_funcs[$f]  = "selected";
		} else {
			$cat_funcs[$f] = "default";
		} 

		if ($clean_funcs[$f] ) {
			$clean_funcs[$f]  = "selected";
		} else {
			$clean_funcs[$f] = "default";
		} 

		if ($database_funcs[$f] ) {
			$database_funcs[$f]  = "selected";
		} else {
			$database_funcs[$f] = "default";
		} 
		?>
			
		<li><a href="admin.php?f=2" id="<?php print $site_funcs[$f]?>">Sites</a>  </li>
		<li><a href="admin.php?f=categories" id="<?php print $cat_funcs[$f]?>">Categories</a></li> 
		<li><a href="admin.php?f=index" id="<?php print $index_funcs[$f]?>">Index</a></li>
		<li><a href="admin.php?f=clean" id="<?php print $clean_funcs[$f]?>">Clean tables</a> </li>
		<li><a href="admin.php?f=settings" id="<?php print $settings_funcs[$f]?>">Settings</a></li>
		<li><a href="admin.php?f=statistics" id="<?php print $stat_funcs[$f]?>">Statistics</a> </li>
		<li><a href="admin.php?f=database" id="<?php print $database_funcs[$f]?>">Database</a></li>
		<li><a href="admin.php?f=24" id="default">Log out</a></li>
		</ul>
	</div>
	<div id="main">

<?php 
	function list_cats($parent, $lev, $color, $message) {
		global $mysql_table_prefix;
		if ($lev == 0) {
			?>
			<div id="submenu">
				<ul>
				<li><a href="admin.php?f=add_cat">Add category</a> </li>
				</ul>
			</div>
			<?php 
			print $message;
			print "<br/>";
			print "<br/><div align=\"center\"><center><table cellspacing =\"0\" cellpadding=\"0\" class=\"darkgrey\" width =\"600\"><tr><td><table table cellpadding=\"3\" cellspacing=\"1\" width=\"100%\">\n";
		}
		$space = "";
		for ($x = 0; $x < $lev; $x++)
			$space .= "&nbsp;&nbsp;&nbsp;&nbsp;";

		$query = "SELECT * FROM ".$mysql_table_prefix."categories WHERE parent_num=$parent ORDER BY category";
		$result = mysql_query($query);
		echo mysql_error();
		
		if (mysql_num_rows($result) <> '')
			while ($row = mysql_fetch_array($result)) {
				if ($color =="white") 
					$color = "grey";
				else 
					$color = "white";				
	
				$id = $row['category_id'];
				$cat = $row['category'];
				print "<tr class=\"$color\"><td width=90% align=left>$space<a href=\"admin.php?f=edit_cat&cat_id=$id\">".stripslashes($cat). "</a></td><td><a href=\"admin.php?f=edit_cat&cat_id=$id\" id=\"small_button\">Edit</a></td><td> <a href=\"admin.php?f=11&cat_id=$id\" onclick=\"return confirm('Are you sure you want to delete? Subcategories will be lost.')\" id=\"small_button\">Delete</a></td></tr>\n";
	
				$color = list_cats($id, $lev + 1, $color, "");
			}

		if ($lev == 0)
			print "</table></td></tr></table></center></div>\n";
		return $color;
	}

	function walk_through_cats($parent, $lev, $site_id) {
		global $mysql_table_prefix;
		$space = "";
		for ($x = 0; $x < $lev; $x++)
			$space .= "&nbsp;&nbsp;&nbsp;&nbsp;";

		$query = "SELECT * FROM ".$mysql_table_prefix."categories WHERE parent_num=$parent ORDER BY category";
		$result = mysql_query($query);
		echo mysql_error();
		
		if (mysql_num_rows($result) <> '')
			while ($row = mysql_fetch_array($result)) {
				$id = $row['category_id'];
				$cat = $row['category'];
				$state = '';
				if ($site_id <> '') {
					$result2 = mysql_query("select * from ".$mysql_table_prefix."site_category where site_id=$site_id and category_id=$id");
					echo mysql_error();
					$rows = mysql_num_rows($result2);

					if ($rows > 0)
						$state = "checked";
				}

				print $space . "<input type=checkbox name=cat[$id] $state>" . $cat . "<br/>\n";
				;
				walk_through_cats($id, $lev + 1, $site_id);
			}
	}






function addcatform($parent) {
	global $mysql_table_prefix;
	$par2 = "";
	$par2num = "";
	?>
	<div id="submenu">
	</div>
	<?php 
	if ($parent=='') 
		$par='(Top level)';
	else {
		$query = "SELECT category, parent_num FROM ".$mysql_table_prefix."categories WHERE category_id='$parent'";
		$result = mysql_query($query);
		if (!mysql_error())	{
			if ($row = mysql_fetch_row($result)) {
				$par=$row[0];
				$query = "SELECT Category_ID, Category FROM ".$mysql_table_prefix."categories WHERE Category_ID='$row[1]'";
				$result = mysql_query($query);
				echo mysql_error();
				if (mysql_num_rows($result)<>'') {
					$row = mysql_fetch_row($result);
					$par2num = $row[0];
					$par2 = $row[1];
				}
				else
					$par2 = "Top level";
	
				}
			}
		else
			echo mysql_error();
		print "</td></tr></table>";
	}

?>
	   <br/><center><table><tr><td valign=top align=center colspan=2><b>Parent: <?php print "<a href=admin.php?f=add_cat&parent=$par2num>$par2</a> >".stripslashes($par)?></b></td></tr>
		<form action=admin.php method=post>
   		<input type=hidden name=f value=7>
   		<input type=hidden name=parent value="<?php print $parent?>"
		<tr><td><b>Category:</b></td><td> <input type=text name=category size=40></td></tr>
		<tr><td></td><td><input type=submit id="submit" value=Add></td></tr></form>
		
<?php 
	print "<tr><td colspan=2>";
	$query = "SELECT category_ID, Category FROM ".$mysql_table_prefix."categories WHERE parent_num='$parent'";
	$result = mysql_query($query);
	echo mysql_error();
	if (mysql_num_rows($result)>0) {
		print "<br/><b>Create subcategory under</b><br/><br/>";
	}
	while ($row = mysql_fetch_row($result)) {
		print "<a href=\"admin.php?f=add_cat&parent=$row[0]\">".stripslashes($row[1])."</a><br/>";
	}
	print "</td></tr></table></center>";
}


	function addcat ($category, $parent) {
			global $mysql_table_prefix;
			if ($category=="") return;
		$category = addslashes($category);
		if ($parent == "") {
			$parent = 0;
		}
		$query = "INSERT INTO ".$mysql_table_prefix."categories (category, parent_num)
				 VALUES ('$category', ".$parent.")";
		mysql_query($query);
		If (!mysql_error()) {
			return "<center><b>Category $category added.</b></center>" ;
		} else {
			return mysql_error();
		}
	}




	function addsiteform() {
		?>
		<div id="submenu"><center><b>Add a site</b></center></div>
		<br/><div align=center><center><table>
		<form action=admin.php method=post>
   		<input type=hidden name=f value=1>
		<input type=hidden name=af value=2>
		<tr><td><b>URL:</b></td><td align ="right"></td><td><input type=text name=url size=60 value ="http://"></td></tr>
		<tr><td><b>Title:</b></td><td></td><td> <input type=text name=title size=60></td></tr>
		<tr><td><b>Short description:</b></td><td></td><td><textarea name=short_desc cols=45 rows=3 wrap="virtual"></textarea></td></tr>
		<tr><td>Category:</td><td></td><td>
		<?php  walk_through_cats(0, 0, '');?></td></tr>
		<tr><td></td><td></td><td><input type=submit id="submit" value=Add></td></tr></form></table></center></div>
		<?php 
	}

	function editsiteform($site_id) {
		global $mysql_table_prefix;
		$result = mysql_query("SELECT site_id, url, title, short_desc, spider_depth, required, disallowed, can_leave_domain from ".$mysql_table_prefix."sites where site_id=$site_id");
		echo mysql_error();
		$row = mysql_fetch_array($result);
		$depth = $row['spider_depth'];
		$fullchecked = "";
		$depthchecked = "";		
		if ($depth == -1 ) {
			$fullchecked = "checked";
			$depth ="";
		} else {
			$depthchecked = "checked";
		}
		$leave_domain = $row['can_leave_domain'];
		if ($leave_domain == 1 ) {
			$domainchecked = "checked";
		} else {
			$domainchecked = "";
		}		
		?>
					<div id="submenu"><center><b>Edit site</b></center>
			</div>
			<br/><div align=center><center><table>
			<form action=admin.php method=post>
			<input type=hidden name=f value=4>
			<input type=hidden name=site_id value=<?php print $site_id;?>>
			<tr><td><b>URL:</b></td><td align ="right"></td><td><input type=text name=url value=<?php print "\"".$row['url']."\""?> size=60></td></tr>
			<tr><td><b>Title:</b></td><td></td><td> <input type=text name=title value=<?php print  "\"".stripslashes($row['title'])."\""?> size=60></td></tr>
			<tr><td><b>Short description:</b></td><td></td><td><textarea name=short_desc cols=45 rows=3 wrap><?php print stripslashes($row['short_desc'])?></textarea></td></tr>
			<tr><td><b>Spidering options:</b></td><td></td><td><input type="radio" name="soption" value="full" <?php print $fullchecked;?>> Full<br/>
			<input type="radio" name="soption" value="level" <?php print $depthchecked;?>>To depth: <input type="text" name="depth" size="2" value="<?php print $depth;?>"><br/>
			<input type="checkbox" name="domaincb" value="1" <?php print $domainchecked;?>> Spider can leave domain
			</td></tr>			
			<tr><td><b>URLs must include:</b></td><td></td><td><textarea name=in cols=45 rows=2 wrap="virtual"><?php print $row['required'];?></textarea></td></tr>
			<tr><td><b>URLs must not include:</b></td><td></td><td><textarea name=out cols=45 rows=2 wrap="virtual"><?php print $row['disallowed'];?></textarea></td></tr>
			
			<tr><td>Category:</td><td></td><td>
			<?php  walk_through_cats(0, 0, $site_id);?></td></tr>
			<tr><td></td><td></td><td><input type="submit"  id="submit"  value="Update"></td></tr></form></table></center></div>
		<?php 
		}


		function editsite ($site_id, $url, $title, $short_desc, $depth, $required, $disallowed, $domaincb,  $cat) {
			global $mysql_table_prefix;
			$short_desc = addslashes($short_desc);
			$title = addslashes($title);
			mysql_query("delete from ".$mysql_table_prefix."site_category where site_id=$site_id");
			echo mysql_error();
			$compurl=parse_url($url);
			if ($compurl['path']=='')
				$url=$url."/";
			mysql_query("UPDATE ".$mysql_table_prefix."sites SET url='$url', title='$title', short_desc='$short_desc', spider_depth =$depth, required='$required', disallowed='$disallowed', can_leave_domain=$domaincb WHERE site_id=$site_id");
			echo mysql_error();
			$result=mysql_query("select category_id from ".$mysql_table_prefix."categories");
			echo mysql_error();
			print mysql_error();
			while ($row=mysql_fetch_row($result)) {
				$cat_id=$row[0];
				if ($cat[$cat_id]=='on') {
					mysql_query("INSERT INTO ".$mysql_table_prefix."site_category (site_id, category_id) values ('$site_id', '$cat_id')");
					echo mysql_error();
				}
			}
			If (!mysql_error()) {
				return "<br/><center><b>Site updated.</b></center>" ;
			} else {
				return mysql_error();
			}
		}

	function editcatform($cat_id) {
		global $mysql_table_prefix;
		$result = mysql_query("SELECT category FROM ".$mysql_table_prefix."categories where category_id='$cat_id'");
		echo mysql_error();
		$row=mysql_fetch_array($result);
		$category=$row[0];
		?>
				<div id="submenu">
				<center><b>Edit category</b></center>
			</div>
			<br/>
		   <div align="center"><center><table>
			<form action="admin.php" method="post">
			<input type="hidden" name="f" value="10">
			<input type="hidden" name="cat_id" value="<?php  print $cat_id;?>"
			<tr><td><b>Category:</b></td><td> <input type="text" name="category" value="<?php print $category?>"size=40></td></tr>
			<tr><td></td><td><input type="submit"  id="submit"  value="Update"></td></tr></form></table></center></div>
		<?php 
		}


	function editcat ($cat_id, $category) {
		global $mysql_table_prefix;
		$qry = "UPDATE ".$mysql_table_prefix."categories SET category='".addslashes($category)."' WHERE category_id='$cat_id'";
		mysql_query($qry);
		if (!mysql_error())	{
			return "<br/><center><b>Category updated</b></center>";
		} else {
			return mysql_error();
		}
	}



	function showsites($message) {
		global $mysql_table_prefix;
		$result = mysql_query("SELECT site_id, url, title, indexdate from ".$mysql_table_prefix."sites ORDER By indexdate, title");
		echo mysql_error();
		?>
		<div id='submenu'>
		 <ul>
		  <li><a href='admin.php?f=add_site'>Add site</a> </li>
		  <?php 
			if (mysql_num_rows($result) > 0) {
				?>
				<li><a href='spider.php?all=1'> Reindex all</a></li>
				<?php 
			}
			?>
		 </ul>
		</div>

		<?php 
		print $message;
		print "<br/>";
		if (mysql_num_rows($result) > 0) {
			print "<div align=\"center\"><table cellspacing =\"0\" cellpadding=\"0\" class=\"darkgrey\"><tr><td><table cellpadding=\"3\" cellspacing=\"1\">
			<tr class=\"grey\"><td align=\"center\"><b>Site name</b></td><td align=\"center\"><b>Site url</b></td><td align=\"center\"><b>Last indexed</b></td><td colspan=4></td></tr>\n";
		} else {
			?><center><p><b>Welcom to Sphider. <br><br>Choose "Add site" from the submenu to add a new site, or "Index" to directly go to the indexing section.</b></p></center><?php 
		}
		$class = "grey";
		while ($row=mysql_fetch_array($result))	{
			if ($row['indexdate']=='') {
				$indexstatus="<font color=\"red\">Not indexed</font>";
				$indexoption="<a href=\"admin.php?f=index&url=$row[url]\">Index</a>";
			} else {
				$site_id = $row['site_id'];
				$result2 = mysql_query("SELECT site_id from ".$mysql_table_prefix."pending where site_id =$site_id");
				echo mysql_error();			
				$row2=mysql_fetch_array($result2);
				if ($row2['site_id'] == $row['site_id']) {
					$indexstatus = "Unfinished";
					$indexoption="<a href=\"admin.php?f=index&url=$row[url]\">Continue</a>";

				} else {
					$indexstatus = $row['indexdate'];
					$indexoption="<a href=\"admin.php?f=index&url=$row[url]&reindex=1\">Re-index</a>";
				}
			}
			if ($class =="white") 
				$class = "grey";
			else 
				$class = "white";
			print "<tr class=\"$class\"><td align=\"left\">".stripslashes($row[title])."</td><td align=\"left\"><a href=\"$row[url]\">$row[url]</a></td><td>$indexstatus</td>";
			print "<td><a href=admin.php?f=20&site_id=$row[site_id] id=\"small_button\">Options</a></td></tr>\n";

		}
		if (mysql_num_rows($result) > 0) {
			print "</table></td></tr></table></div>";
		}
	}

	function deletecat($cat_id) {
		global $mysql_table_prefix;
		$list = implode(",", get_cats($cat_id));
		mysql_query("delete from ".$mysql_table_prefix."categories where category_id in ($list)");
		echo mysql_error();
		mysql_query("delete from ".$mysql_table_prefix."site_category where category_id=$cat_id");
		echo mysql_error();
		return "<center><b>Category deleted.</b></center>";
	}
	function deletesite($site_id) {
		global $mysql_table_prefix;
		mysql_query("delete from ".$mysql_table_prefix."sites where site_id=$site_id");
		echo mysql_error();
		mysql_query("delete from ".$mysql_table_prefix."site_category where site_id=$site_id");
		echo mysql_error();
		$query = "select link_id from ".$mysql_table_prefix."links where site_id=$site_id";
		$result = mysql_query($query);
		echo mysql_error();
		$todelete = array();
		while ($row=mysql_fetch_array($result)) {
			$todelete[]=$row['link_id'];
		}

		if (count($todelete)>0) {
			$todelete = implode(",", $todelete);
			for ($i=0;$i<=15; $i++) {
				$char = dechex($i);
				$query = "delete from ".$mysql_table_prefix."link_keyword$char where link_id in($todelete)";
				mysql_query($query);
				echo mysql_error();
			}
		}

		mysql_query("delete from ".$mysql_table_prefix."links where site_id=$site_id");
		echo mysql_error();
		mysql_query("delete from ".$mysql_table_prefix."pending where site_id=$site_id");
		echo mysql_error();
		return "<br/><center><b>Site deleted</b></center>";
	}

	function deletePage($link_id) {
		global $mysql_table_prefix;
		mysql_query("delete from ".$mysql_table_prefix."links where link_id=$link_id");
		echo mysql_error();
		for ($i=0;$i<=15; $i++) {
			$char = dechex($i);
			mysql_query("delete from ".$mysql_table_prefix."link_keyword$char where link_id=$link_id");
		}
		echo mysql_error();
		return "<br/><center><b>Page deleted</b></center>";
	}

	
	function cleanTemp() {
		global $mysql_table_prefix;
		$result = mysql_query("delete from ".$mysql_table_prefix."temp where level >= 0");
		echo mysql_error();
		$del = mysql_affected_rows();
				?>
		<div id="submenu">
		</div><?php 
		print "<br/><center><b>Temp table cleared, $del items deleted.</b></center>";
	}

	function clearLog() {
		global $mysql_table_prefix;
		$result = mysql_query("delete from ".$mysql_table_prefix."query_log where time >= 0");
		echo mysql_error();
		$del = mysql_affected_rows();
		?>
		<div id="submenu">
		</div><?php 
		print "<br/><center><b>Search log cleared, $del items deleted.</b></center>";
	}

	
	function cleanLinks() {
		global $mysql_table_prefix;
		$query = "select site_id from ".$mysql_table_prefix."sites";
		$result = mysql_query($query);
		echo mysql_error();
		$todelete = array();
		if (mysql_num_rows($result)>0) {
			while ($row=mysql_fetch_array($result)) {
				$todelete[]=$row['site_id'];
			}
			$todelete = implode(",", $todelete);
			$sql_end = " not in ($todelete)";
		}
		
		$result = mysql_query("select link_id from ".$mysql_table_prefix."links where site_id".$sql_end);
		echo mysql_error();
		$del = mysql_num_rows($result);
		while ($row=mysql_fetch_array($result)) {
			$link_id=$row[link_id];
			for ($i=0;$i<=15; $i++) {
				$char = dechex($i);
				mysql_query("delete from ".$mysql_table_prefix."link_keyword$char where link_id=$link_id");
				echo mysql_error();
			}
			mysql_query("delete from ".$mysql_table_prefix."links where link_id=$link_id");
			echo mysql_error();
		}

		$result = mysql_query("select link_id from ".$mysql_table_prefix."links where site_id is NULL");
		echo mysql_error();
		$del += mysql_num_rows($result);
		while ($row=mysql_fetch_array($result)) {
			$link_id=$row[link_id];
			for ($i=0;$i<=15; $i++) {
				$char = dechex($i);
				mysql_query("delete from ".$mysql_table_prefix."link_keyword$char where link_id=$link_id");
				echo mysql_error();
			}
			mysql_query("delete from ".$mysql_table_prefix."links where link_id=$link_id");
			echo mysql_error();
		}
		?>
		<div id="submenu">
		</div><?php 
		print "<br/><center><b>Links table cleaned, $del links deleted.</b></center>";
	}

	function cleanKeywords() {
		global $mysql_table_prefix;
		$query = "select keyword_id, keyword from ".$mysql_table_prefix."keywords";
		$result = mysql_query($query);
		echo mysql_error();
		$del = 0;
		while ($row=mysql_fetch_array($result)) {
			$keyId=$row['keyword_id'];
			$keyword=$row['keyword'];
			$wordmd5 = substr(md5($keyword), 0, 1);
			$query = "select keyword_id from ".$mysql_table_prefix."link_keyword$wordmd5 where keyword_id = $keyId";
			$result2 = mysql_query($query);
			echo mysql_error();
			if (mysql_num_rows($result2) < 1) {
				mysql_query("delete from ".$mysql_table_prefix."keywords where keyword_id=$keyId");
				echo mysql_error();
				$del++;
			}
		}?>
		<div id="submenu">
		</div><?php 
		print "<br/><center><b>Keywords table cleaned, $del keywords deleted.</b></center>";
	}

	function getStatistics() {
		global $mysql_table_prefix;
		$stats = array();
		$keywordQuery = "select count(keyword_id) from ".$mysql_table_prefix."keywords";
		$linksQuery = "select count(url) from ".$mysql_table_prefix."links";
		$siteQuery = "select count(site_id) from ".$mysql_table_prefix."sites";
		$categoriesQuery = "select count(category_id) from ".$mysql_table_prefix."categories";

		$result = mysql_query($keywordQuery);
		echo mysql_error();
		if ($row=mysql_fetch_array($result)) {
			$stats['keywords']=$row[0];
		}
		$result = mysql_query($linksQuery);
		echo mysql_error();
		if ($row=mysql_fetch_array($result)) {
			$stats['links']=$row[0];
		}
		for ($i=0;$i<=15; $i++) {
			$char = dechex($i);
			$result = mysql_query("select count(link_id) from ".$mysql_table_prefix."link_keyword$char");
			echo mysql_error();
			if ($row=mysql_fetch_array($result)) {
				$stats['index']+=$row[0];
			}
		}
		$result = mysql_query($siteQuery);
		echo mysql_error();
		if ($row=mysql_fetch_array($result)) {
			$stats['sites']=$row[0];
		}
		$result = mysql_query($categoriesQuery);
		echo mysql_error();
		if ($row=mysql_fetch_array($result)) {
			$stats['categories']=$row[0];
		}
		return $stats;
	}



	function addsite ($url, $title, $short_desc, $cat) {
		global $mysql_table_prefix;
		$short_desc = addslashes($short_desc);
		$title = addslashes($title);
		$compurl=parse_url("".$url);
		if ($compurl['path']=='')
			$url=$url."/";
		$result = mysql_query("select site_ID from ".$mysql_table_prefix."sites where url='$url'");
		echo mysql_error();
		$rows = mysql_numrows($result);
		if ($rows==0 ) {
			mysql_query("INSERT INTO ".$mysql_table_prefix."sites (url, title, short_desc) VALUES ('$url', '$title', '$short_desc')");
			echo mysql_error();
			$result = mysql_query("select site_ID from ".$mysql_table_prefix."sites where url='$url'");
			echo mysql_error();
			$row = mysql_fetch_row($result);
			$site_id = $row[0];
			$result=mysql_query("select category_id from ".$mysql_table_prefix."categories");
			echo mysql_error();
			while ($row=mysql_fetch_row($result)) {
				$cat_id=$row[0];
				if ($cat[$cat_id]=='on') {
					mysql_query("INSERT INTO ".$mysql_table_prefix."site_category (site_id, category_id) values ('$site_id', '$cat_id')");
					echo mysql_error();
				}
	 		}
		
			If (!mysql_error())	{
				$message =  "<br/><center><b>Site added</b></center>" ;
			} else {
				$message = mysql_error();
			}

		} else {
			$message = "<center><b>Site already in database</b></center>";
		}
		return $message;
	}


	function indexscreen ($url, $reindex) {
		global $mysql_table_prefix;
		$check = "";
		$levelchecked = "checked";
		$spider_depth = 2;
		if ($url=="") {
			$url = "http://";
			$advurl = "";
		} else {
			$advurl = $url;
			$result = mysql_query("select spider_depth, required, disallowed, can_leave_domain from ".$mysql_table_prefix."sites " .
					"where url='$url'");
			echo mysql_error();
			if (mysql_num_rows($result) > 0) {
				$row = mysql_fetch_row($result);
				$spider_depth = $row[0];
				if ($spider_depth == -1 ) {
					$fullchecked = "checked";
					$spider_depth ="";
					$levelchecked = "";
				}
				$must = $row[1];
				$mustnot = $row[2];
				$canleave = $row[3];
			}			
		}

		?>
		<div id="submenu">
			<ul>
				<li>
				<?php 
				if ($must !="" || $mustnot !="" || $canleave == 1 ) {	
					$_SESSION['index_advanced']=1;
				}
				if ($_SESSION['index_advanced']==1){
					print "<a href='admin.php?f=index&adv=0&url=$advurl'>Hide advanced options</a>";
				} else {
					print "<a href='admin.php?f=index&adv=1&url=$advurl'>Advanced options</a>";
				}

				?>
				</li>
			</ul>
		</div>
		<br/>
		<div id="indexoptions"><table>
		<form action="spider.php" method="post">
		<tr><td><b>Address:</b></td><td> <input type="text" name="url" size="48" value=<?php print "\"$url\"";?>></td></tr>
		<tr><td><b>Indexing options:</b></td><td>
		<input type="radio" name="soption" value="full" <?php print $fullchecked;?>> Full<br/>
		<input type="radio" name="soption" value="level" <?php print $levelchecked;?>>To depth: <input type="text" name="maxlevel" size="2" value="<?php print $spider_depth;?>"><br/>
		<?php if ($reindex==1) $check="checked"?>
		<input type="checkbox" name="reindex" value="1" <?php print $check;?>> Reindex<br/>
		</td></tr>
		<?php 
		if ($_SESSION['index_advanced']==1){
			?>
			<?php if ($canleave==1) {$checkcan="checked" ;} ?>
			<tr><td></td><td><input type="checkbox" name="domaincb" value="1" <?php print $checkcan;?>> Spider can leave domain <!--a href="javascript:;" onClick="window.open('hmm','newWindow','width=300,height=300,left=600,top=200,resizable');" >?</a--><br/></td></tr>
			<tr><td><b>URL must include:</b></td><td><textarea name=in cols=35 rows=2 wrap="virtual"><?php print $must;?></textarea></td></tr>
			<tr><td><b>URL must not include:</b></td><td><textarea name=out cols=35 rows=2 wrap="virtual"><?php print $mustnot;?></textarea></td></tr>
			<?php 
		}
		?>

		<tr><td></td><td><input type="submit" id="submit" value="Start indexing"></td></tr>
		</form></table></div>
		<?php 
	}

	function siteScreen($site_id, $message)  {
		global $mysql_table_prefix;
		$result = mysql_query("SELECT site_id, url, title, short_desc, indexdate from ".$mysql_table_prefix."sites where site_id=$site_id");
		echo mysql_error();
		$row=mysql_fetch_array($result);
		$url = replace_ampersand($row[url]);
		if ($row['indexdate']=='') {
			$indexstatus="<font color=\"red\">Not indexed</font>";
			$indexoption="<a href=\"admin.php?f=index&url=$url\">Index</a>";
		} else {
			$site_id = $row['site_id'];
			$result2 = mysql_query("SELECT site_id from ".$mysql_table_prefix."pending where site_id =$site_id");
			echo mysql_error();			
			$row2=mysql_fetch_array($result2);
			if ($row2['site_id'] == $row['site_id']) {
				$indexstatus = "Unfinished";
				$indexoption="<a href=\"admin.php?f=index&url=$url\">Continue indexing</a>";

			} else {
				$indexstatus = $row['indexdate'];
				$indexoption="<a href=\"admin.php?f=index&url=$url&reindex=1\">Re-index</a>";
			}
		}
		?>

		<div id="submenu">
		</div>
		<?php print $message;?>
			<br/>

		<center>
		<div style="width:755px;">
		<div style="float:left; margin-right:0px;">
		<div class="darkgrey">
		<table cellpadding="3" cellspacing="0">

			<table  cellpadding="5" cellspacing="1" width="640">
			  <tr >
				<td class="grey" valign="top" width="20%" align="left">URL:</td>
				<td class="white" align="left"><a href="<?php print  $row['url']; print "\">"; print $row['url'];?></a></td>
			  </tr>
			<tr>
				<td class="grey" valign="top" align="left">Title:</td>
				<td class="white" align="left"><b><?php print stripslashes($row['title']);?></b></td>
			</tr>
			  <tr>
				<td class="grey" valign="top" align="left">Description:</td>
				<td width="80%" class="white"  align="left"><?php print stripslashes($row['short_desc']);?></td>
			  </tr>
			  <tr>
				<td class="grey" valign="top" align="left">Last indexed:</td>
				<td class="white"  align="left"><?php print $indexstatus;?></td>
			  </tr>
			</table>
		</div>
		</div>
		<div id= "vertmenu">
		<ul>
		 <li><a href=admin.php?f=edit_site&site_id=<?php print  $row['site_id']?>>Edit</a></li>
		<li><?php print $indexoption?></li>
		<li><a href=admin.php?f=21&site_id=<?php print  $row['site_id']?>>Browse pages</a></li>
		<li><a href=admin.php?f=5&site_id=<?php print  $row['site_id'];?> onclick="return confirm('Are you sure you want to delete? Index will be lost.')">Delete</a></li>
		<li><a href=admin.php?f=19&site_id=<?php print  $row['site_id'];?>>Stats</a></li>
		</div>
		</ul>
		</div>
		</center>
		<div class="clear">
		</div>
		<br/>
	<?php 
	}


	function siteStats($site_id) {
		global $mysql_table_prefix;
		$result = mysql_query("select url from ".$mysql_table_prefix."sites where site_id=$site_id");
		echo mysql_error();
		if ($row=mysql_fetch_array($result)) {
			$url=$row[0];

			$lastIndexQuery = "SELECT indexdate from ".$mysql_table_prefix."sites where site_id = $site_id";
			$sumSizeQuery = "select sum(length(fulltxt)) from ".$mysql_table_prefix."links where site_id = $site_id";
			$siteSizeQuery = "select sum(size) from ".$mysql_table_prefix."links where site_id = $site_id";
			$linksQuery = "select count(*) from ".$mysql_table_prefix."links where site_id = $site_id";

			$result = mysql_query($lastIndexQuery);
			echo mysql_error();
			if ($row=mysql_fetch_array($result)) {
				$stats['lastIndex']=$row[0];
			}

			$result = mysql_query($sumSizeQuery);
			echo mysql_error();
			if ($row=mysql_fetch_array($result)) {
				$stats['sumSize']=$row[0];
			}
			$result = mysql_query($linksQuery);
			echo mysql_error();
			if ($row=mysql_fetch_array($result)) {
				$stats['links']=$row[0];
			}

			for ($i=0;$i<=15; $i++) {
				$char = dechex($i);
				$result = mysql_query("select count(*) from ".$mysql_table_prefix."links, ".$mysql_table_prefix."link_keyword$char where ".$mysql_table_prefix."links.link_id=".$mysql_table_prefix."link_keyword$char.link_id and ".$mysql_table_prefix."links.site_id = $site_id");
				echo mysql_error();
				if ($row=mysql_fetch_array($result)) {
					$stats['index']+=$row[0];
				}
			}
			for ($i=0;$i<=15; $i++) {
				$char = dechex($i);
				$wordQuery = "select count(distinct keyword) from ".$mysql_table_prefix."keywords, ".$mysql_table_prefix."links, ".$mysql_table_prefix."link_keyword$char where ".$mysql_table_prefix."links.link_id=".$mysql_table_prefix."link_keyword$char.link_id and ".$mysql_table_prefix."links.site_id = $site_id and ".$mysql_table_prefix."keywords.keyword_id = ".$mysql_table_prefix."link_keyword$char.keyword_id";
				$result = mysql_query($wordQuery);
				echo mysql_error();
				if ($row=mysql_fetch_array($result)) {
					$stats['words']+=$row[0];
				}
			}
			
			$result = mysql_query($siteSizeQuery);
			echo mysql_error();
			if ($row=mysql_fetch_array($result)) {
				$stats['siteSize']=$row[0];
			}
			if ($stats['siteSize']=="")
				$stats['siteSize'] = 0;
			$stats['siteSize'] = number_format($stats['siteSize'], 2);
			print"<div id=\"submenu\"></div>";
			print "<br/><div align=\"center\"><center><table cellspacing =\"0\" cellpadding=\"0\" class=\"darkgrey\"><tr><td><table cellpadding=\"3\" cellspacing = \"1\"><tr  class=\"grey\"><td colspan=\"2\">";
			print "Statistics for site <a href=\"admin.php?f=20&site_id=$site_id\">$url</a>";
			print "<tr class=\"white\"><td>Last indexed:</td><td align=\"center\"> ".$stats['lastIndex']."</td></tr>";
			print "<tr class=\"grey\"><td>Pages indexed:</td><td align=\"center\"> ".$stats['links']."</td></tr>";
			print "<tr class=\"white\"><td>Total index size:</td><td align=\"center\"> ".$stats['index']."</td></tr>";
			$sum = number_format($stats['sumSize']/1024, 2);
			print "<tr class=\"grey\"><td>Cached texts:</td><td align=\"center\"> ".$sum."kb</td></tr>";
			print "<tr class=\"white\"><td>Total number of keywords:</td><td align=\"center\"> ".$stats['words']."</td></tr>";
			print "<tr class=\"grey\"><td>Site size:</td><td align=\"center\"> ".$stats['siteSize']."kb</td></tr>";
			print "</table></td></tr></table></center></div>";
		}
	}

	function browsePages($site_id, $start, $filter, $per_page) {
		global $mysql_table_prefix;
		$result = mysql_query("select url from ".$mysql_table_prefix."sites where site_id=$site_id");
		echo mysql_error();
		$row = mysql_fetch_row($result);
		$url = $row[0];
		
		$query_add = "";
		if ($filter != "") {
			$query_add = "and url like '%$filter%'";
		}
		$linksQuery = "select count(*) from ".$mysql_table_prefix."links where site_id = $site_id $query_add";
		$result = mysql_query($linksQuery);
		echo mysql_error();
		$row = mysql_fetch_row($result);
		$numOfPages = $row[0]; 

		$result = mysql_query($linksQuery);
		echo mysql_error();
		$from = ($start-1) * 10;
		$to = min(($start)*10, $numOfPages);

		
		$linksQuery = "select link_id, url from ".$mysql_table_prefix."links where site_id = $site_id and url like '%$filter%' order by url limit $from, $per_page";
		$result = mysql_query($linksQuery);
		echo mysql_error();
		?>
		<div id="submenu"></div>
		<br/>
		<center>
		<b>Pages of site <a href="admin.php?f=20&site_id=<?php  print $site_id?>"><?php print $url;?></a></b><br/>
		<p>
		<form action="admin.php" method="post">
		Urls per page: <input type="text" name="per_page" size="3" value="<?php print $per_page;?>"> 
		Url contains: <input type="text" name="filter" size="15" value="<?php print $filter;?>"> 
		<input type="submit" id="submit" value="Filter">
		<input type="hidden" name="start" value="1">
		<input type="hidden" name="site_id" value="<?php print $site_id?>">
		<input type="hidden" name="f" value="21">
		</form>
		</p>
	<table width="600"><tr><td>
		<table cellspacing ="0" cellpadding="0" class="darkgrey" width ="100%"><tr><td>
		<table  cellpadding="3" cellspacing="1" width="100%">

		<?php 
		$class = "white";
		while ($row = mysql_fetch_array($result)) {
			if ($class =="white") 
				$class = "grey";
			else 
				$class = "white";
			print "<tr class=\"$class\"><td><a href=\"".$row['url']."\">".$row['url']."</a></td><td width=\"8%\"> <a href=\"admin.php?link_id=".$row['link_id']."&f=22&site_id=$site_id&start=1&filter=$filter&per_page=$per_page\">Delete</a></td></tr>";
		}

		print "</table></td></tr></table>";

		$pages = ceil($numOfPages / $per_page);
		$prev = $start - 1;
		$next = $start + 1;

		if ($pages > 0)
			print "<center>Pages: ";

		$links_to_next =10;
		$firstpage = $start - $links_to_next;
		if ($firstpage < 1) $firstpage = 1;
		$lastpage = $start + $links_to_next;
		if ($lastpage > $pages) $lastpage = $pages;
		
		for ($x=$firstpage; $x<=$lastpage; $x++)
			if ($x<>$start)	{
				print "<a href=admin.php?f=21&site_id=$site_id&start=$x&filter=$filter&per_page=$per_page>$x</a> ";
			} 	else
				print "<b>$x </b>";
		print"</td></tr></table></center>";

	}


	function cleanForm () {
		global $mysql_table_prefix;
		$result = mysql_query("select count(*) from ".$mysql_table_prefix."query_log");
		echo mysql_error();
		if ($row=mysql_fetch_array($result)) {
			$log=$row[0];
		}
		$result = mysql_query("select count(*) from ".$mysql_table_prefix."temp");
		echo mysql_error();
		if ($row=mysql_fetch_array($result)) {
			$temp=$row[0];
		}

		
		?>
		<div id="submenu">
		</div>
		<br/><div align="center">
		<table cellspacing ="0" cellpadding="0" class="darkgrey"><tr><td align="left"><table cellpadding="3" cellspacing = "1"  width="100%"><tr class="grey"  ><td align="left"><a href="admin.php?f=15" id="small_button">Clean keywords</a> 
		 </td><td align="left"> Delete all keywords not associated with any link.</td></tr>
		<tr class="grey"  ><td align="left"><a href="admin.php?f=16" id="small_button">Clean links</a>
		</td><td align="left"> Delete all links not associated with any site.</td></tr>
		<tr class="grey"  ><td align="left"><a href="admin.php?f=17" id="small_button">Clear temp tables </a>
		</td><td align="left"> <?php print $temp;?> items in temporary table.</td></tr>
		<tr class="grey"  ><td align="left"><a href="admin.php?f=23" id="small_button">Clear search log </a> 
		</td><td align="left"><?php print $log;?> items in search log.
		</td></tr></table>		</td></tr></table></div>
		<?php 
	}

	function statisticsForm ($type) {
		global $mysql_table_prefix, $log_dir;
		?>
		<div id='submenu'>
		<ul>
		<li><a href="admin.php?f=statistics&type=keywords">Top keywords</a></li>
		<li><a href="admin.php?f=statistics&type=pages">Largest pages</a></li>
		<li><a href="admin.php?f=statistics&type=top_searches">Most popular searches</a></li>
		<li><a href="admin.php?f=statistics&type=log">Search log</a></li>
		<li><a href="admin.php?f=statistics&type=spidering_log">Spidering logs</a></li>
		</ul>
		</div>
		
		<?php 
			if ($type == "") {
				$cachedSumQuery = "select sum(length(fulltxt)) from ".$mysql_table_prefix."links";
				$result=mysql_query("select sum(length(fulltxt)) from ".$mysql_table_prefix."links");
				echo mysql_error();
				if ($row=mysql_fetch_array($result)) {
					$cachedSumSize = $row[0];
				}
				$cachedSumSize = number_format($cachedSumSize / 1024, 2);

				$sitesSizeQuery = "select sum(size) from ".$mysql_table_prefix."links";
				$result=mysql_query("$sitesSizeQuery");
				echo mysql_error();
				if ($row=mysql_fetch_array($result)) {
					$sitesSize = $row[0];
				}
				$sitesSize = number_format($sitesSize, 2);

				$stats = getStatistics();
				print "<br/><div align=\"center\"><table cellspacing =\"0\" cellpadding=\"0\" class=\"darkgrey\"><tr><td><table cellpadding=\"3\" cellspacing = \"1\"><tr  class=\"grey\"><td><b>Sites:</b></td><td align=\"center\">".$stats['sites']."</td></tr>";				
				print "<tr class=\"white\"><td><b>Links:</b></td><td align=\"center\"> ".$stats['links']."</td></tr>";
				print "<tr class=\"grey\"><td><b>Categories:</b></td><td align=\"center\"> ".$stats['categories']."</td></tr>";
				print "<tr class=\"white\"><td><b>Keywords:</b></td><td align=\"center\"> ".$stats['keywords']."</td></tr>";
				print "<tr class=\"grey\"><td><b>Keyword-link realations:</b></td><td align=\"center\"> ".$stats['index']."</td></tr>";
				print "<tr class=\"white\"><td><b>Cached texts total:</b></td><td align=\"center\"> $cachedSumSize kb</td></tr>";
				print "<tr class=\"grey\"><td><b>Sites size total:</b></td><td align=\"center\"> $sitesSize kb</td></tr>";
				print "</table></td></tr></table></div>";
			}	

			if ($type=='keywords') {
				$class = "grey";
				print "<br/><div align=\"center\"><table cellspacing =\"0\" cellpadding=\"0\" class=\"darkgrey\"><tr><td><table cellpadding=\"3\" cellspacing = \"1\"><tr  class=\"grey\"><td><b>Keyword</b></td><td><b>Occurrences</b></td></tr>";
				for ($i=0;$i<=15; $i++) {
					$char = dechex($i);
					$result=mysql_query("select keyword, count(".$mysql_table_prefix."link_keyword$char.keyword_id) as x from ".$mysql_table_prefix."keywords, ".$mysql_table_prefix."link_keyword$char where ".$mysql_table_prefix."keywords.keyword_id = ".$mysql_table_prefix."link_keyword$char.keyword_id group by keyword order by x desc limit 30");
					echo mysql_error();
					while (($row=mysql_fetch_row($result))) {
						$topwords[$row[0]] = $row[1];
					}
				}
				arsort($topwords);
				$count = 0;
				while ((list($word, $weight) = each($topwords)) && $count <= 30) {
					
					$count++;
					if ($class =="white") 
						$class = "grey";
					else 
						$class = "white";

					print "<tr class=\"$class\"><td align=\"left\">".$word."</td><td> ".$weight."</td></tr>\n";
		 		}			
				print "</table></td></tr></table></div>";
			}
			if ($type=='pages') {
				$class = "grey";
				?>
				<br/><div align="center">
				<table cellspacing ="0" cellpadding="0" class="darkgrey"><tr><td>
				<table cellpadding="2" cellspacing="1">
				  <tr class="grey"><td>
				   <b>Page</b></td>
				   <td><b>Text size</b></td></tr>
				<?php 
				$result=mysql_query("select ".$mysql_table_prefix."links.link_id, url, length(fulltxt)  as x from ".$mysql_table_prefix."links order by x desc limit 20");
				echo mysql_error();
				while ($row=mysql_fetch_row($result)) {
					if ($class =="white") 
						$class = "grey";
					else 
						$class = "white";
					$url = $row[1];
					$sum = number_format($row[2]/1024, 2);
					print "<tr class=\"$class\"><td align=\"left\"><a href=\"$url\">".$url."</td><td align= \"center\"> ".$sum."kb</td></tr>";
		 		}			
				print "</table></td></tr></table></div>";
			}

			if ($type=='top_searches') {
				$class = "grey";
				print "<br/><div align=\"center\"><table cellspacing =\"0\" cellpadding=\"0\" class=\"darkgrey\"><tr><td><table cellpadding=\"3\" cellspacing = \"1\"><tr  class=\"grey\"><td><b>Query</b></td><td><b>Count</b></td><td><b> Average results</b></td><td><b>Last queried</b></td></tr>";
				$result=mysql_query("select query, count(*) as c, date_format(max(time), '%Y-%m-%d %H:%i:%s'), avg(results)  from ".$mysql_table_prefix."query_log group by query order by c desc");
				echo mysql_error();
				while ($row=mysql_fetch_row($result)) {
					if ($class =="white") 
						$class = "grey";
					else 
						$class = "white";

					$word = $row[0];
					$times = $row[1];
					$date = $row[2];
					$avg = number_format($row[3], 1);
					print "<tr class=\"$class\"><td align=\"left\">".htmlentities($word)."</td><td align=\"center\"> ".$times."</td><td align=\"center\"> ".$avg."</td><td align=\"center\"> ".$date."</td></tr>";
		 		}			
				print "</table></td></tr></table></div>";
			}
			if ($type=='log') {
				$class = "grey";
				print "<br/><div align=\"center\"><table cellspacing =\"0\" cellpadding=\"0\" class=\"darkgrey\"><tr><td><table cellpadding=\"3\" cellspacing = \"1\"><tr  class=\"grey\"><td align=\"center\"><b>Query</b></td><td align=\"center\"><b>Results</b></td><td align=\"center\"><b>Queried at</b></td><td align=\"center\"><b>Time taken</b></td></tr>";
				$result=mysql_query("select query,  date_format(time, '%Y-%m-%d %H:%i:%s'), elapsed, results from ".$mysql_table_prefix."query_log order by time desc");
				echo mysql_error();
				while ($row=mysql_fetch_row($result)) {
					if ($class =="white") 
						$class = "grey";
					else 
						$class = "white";

					$word = $row[0];
					$time = $row[1];
					$elapsed = $row[2];
					$results = $row[3];
					print "<tr class=\"$class\"><td align=\"left\">".htmlentities($word)."</td><td align=\"center\"> ".$results."</td><td align=\"center\"> ".$time."</td><td align=\"center\"> ".$elapsed."</td></tr>";
		 		}			
				print "</table></td></tr></table></div>";
			}
	
			if ($type=='spidering_log') {
				$class = "grey";
				$files = get_dir_contents($log_dir);
				if (count($files)>0) {
					print "<br/><div align=\"center\"><table cellspacing =\"0\" cellpadding=\"0\" class=\"darkgrey\"><tr><td><table cellpadding=\"3\" cellspacing = \"1\"><tr  class=\"grey\"><td align=\"center\"><b>File</b></td><td align=\"center\"><b>Time</b></td><td align=\"center\"><b></b></td></tr>";

					for ($i=0; $i<count($files); $i++) {
						$file=$files[$i];
						$year = substr($file, 0,2);
						$month = substr($file, 2,2);
						$day = substr($file, 4,2);
						$hour = substr($file, 6,2);
						$minute = substr($file, 8,2);
						if ($class =="white") 
							$class = "grey";
						else 
							$class = "white";
						print "<tr class=\"$class\"><td align=\"left\"><a href='$log_dir/$file' tareget='_blank'>$file</a></td><td align=\"center\"> 20$year-$month-$day $hour:$minute</td><td align=\"center\"> <a href='?f=delete_log&file=$file' id='small_button'>Delete</a></td></tr>";
					}

					print "</table></td></tr></table></div>";
				} else {
					?>
					<br/><br/>
					<center><b>No saved logs.</b></center>
					<?php 
				}
			}
	
	}

	switch ($f)	{
		case 1:
			$message = addsite($url, $title, $short_desc, $cat);
			$compurl=parse_url($url);
			if ($compurl['path']=='')
				$url=$url."/";
		 
			$result = mysql_query("select site_id from ".$mysql_table_prefix."sites where url='$url'");
			echo mysql_error();
			$row = mysql_fetch_row($result);
			if ($site_id != "")
				siteScreen($site_id, $message);
			else
				showsites($message);
		break;
		case 2:
			showsites();
		break;
		case edit_site:
			editsiteform($site_id);
		break;
		case 4:
			if (!isset($domaincb))
				$domaincb = 0;
			if (!isset($cat))
				$cat = "";
			if ($soption =='full') {
				$depth = -1;
			} 
			$message = editsite ($site_id, $url, $title, $short_desc, $depth, $in, $out,  $domaincb, $cat);
			showsites($message);
		break;
		case 5:
			deletesite ($site_id);
			showsites();
		break;
		case add_cat:
			if (!isset($parent))
				$parent = "";
			addcatform ($parent);
		break;
		case 7:
			if (!isset($parent)) {
				$parent = "";
			}
			$message = addcat ($category, $parent);
			list_cats (0, 0, "white", $message);
		break;
		case categories:
			list_cats (0, 0, "white", "");
		break;
		case edit_cat;
			editcatform($cat_id);
		break;
		case 10;
			$message = editcat ($cat_id, $category);
			list_cats (0, 0, "white", $message);
		break;
		case 11;
			deletecat($cat_id);
			list_cats (0, 0, "white");
		break;
		case index;
			if (!isset($url))
				$url = "";
			if (!isset($reindex))
				$reindex = "";
			if (isset($adv)) {	
					$_SESSION['index_advanced']=$adv;
			}
			indexscreen($url, $reindex);
		break;
		case add_site;
			addsiteform();
		break;
		case clean;
			cleanForm();
		break;

		case 15;
			cleanKeywords();
		break;
		case 16;
			cleanLinks();
		break;

		case 17;
			cleanTemp();
		break;

		case statistics;
			if (!isset($type))
				$type = "";
			statisticsForm($type);
		break;

		case 19;
			siteStats($site_id);
		break;
		case 20;
			siteScreen($site_id);
		break;
		case 21;
			if (!isset($start))
				$start = 1;
			if (!isset($filter))
				$filter = "";
			if (!isset($per_page))
				$per_page = 10;

			browsePages($site_id, $start, $filter, $per_page);
		break;
		case 22;
			deletePage($link_id);
			if (!isset($start))
				$start = 1;
			if (!isset($filter))
				$filter = "";
			if (!isset($per_page))
				$per_page = 10;
			browsePages($site_id, $start, $filter, $per_page);
		break;
		case 23;
			clearLog();
		break;
		case 24;
			session_destroy();
			header("Location: admin.php");
		break;
		case database;
			include "db_main.php";
		break;
		case settings;
			include('configset.php');
		break;
		case delete_log;
			unlink($log_dir."/".$file);
			statisticsForm('spidering_log');
		break;
		case '':
			showsites();
		break;
	}
	$stats = getStatistics();
	print "<br/><br/>	<center>Currently in database: ".$stats['sites']." sites, ".$stats['links']." links, ".$stats['categories']." categories and ".$stats['keywords']." keywords.<br/><br/></center>\n";

?>
</div>
</div>
</body>
</html>