<?php 

error_reporting (E_ALL ^ E_NOTICE);
extract($_POST);
extract($_REQUEST);
include "auth.php";
$backup_path="./backup/";
$dbname=$database;
$dbprefix=$mysql_table_prefix;

if (isset($send2)) {
	include("db_backup.php");
}
?>
<head>
<script language="JavaScript">
function checkAll(theForm, cName, allNo_stat) {
	var n=theForm.elements.length;
	for (var x=0;x<n;x++){
		if (theForm.elements[x].className.indexOf(cName) !=-1){
			if (allNo_stat.checked) {
			theForm.elements[x].checked = true;
		} else {
			theForm.elements[x].checked = false;
		}
	}
	}
}

  function confirm_del_prompt(URL) {
	if (!confirm("Do you really want to delete the backup file?")) 
		return false;	  
	window.location = URL;
	}

 function confirm_rest_prompt(URL) {
	if (!confirm("Do you want to restore the database from backup file? Current database will be lost.")) 
		return false;	  
	window.location = URL;
	}
</script>

</head>
<div id="submenu">
</div>
  <TABLE WIDTH="94%">
	 <TR> 
	  <TD valign="top"><center> 
		<?php 
		  if (!get_extension_funcs('zlib'))  {
			 echo "Compression module status notice: <font color='red'>Zlib is NOT installed on the server! Backup disabled!";
		  } ?>
		  </font></center>	  
		</TD>
	</TR>
  </TABLE>

<FORM NAME="dobackup" ID="dbform" METHOD="post" ACTION="admin.php">
<input type="hidden" name="f" value="database">

<table width="94%" border="0" cellspacing=0 cellpadding=0 align="center">
  <tr align=center> 
	<td width="1%" class="greyHeading">&nbsp;</td>
	<td class="greyHeading"><b>Tables</b></font></td>
	<td width="10%" class="greyHeading"><b>Rows</b></font></td>
	<td width="20%" class="greyHeading"><b>Created on</b></font></td>
	<td width="15%" class="greyHeading"><b>Data Size kB</b></font></td>
	<td width="17%" class="greyHeading"><b>Index Size kB</b></font></td>
  </tr>
 <?php


		$stats  = mysql_query ("SHOW TABLE STATUS FROM $dbname LIKE '$dbprefix%'");
		$num_tables = mysql_num_rows($stats);
		if ($num_tables==0) {
			echo("ERROR: Database contains no tables");
		}	

		$bgcolor='grey';
		$i=0;
		while ($rows=mysql_fetch_array($stats) ) {
			print "<tr><td class=".$bgcolor."><input type='checkbox' id='tables$i' class='check' name='tables[$i]' value='".$rows["Name"]."' ></td>";
			print "<td class=".$bgcolor.">".$rows["Name"]."</td>";
			print '<td align="center" class='.$bgcolor.'>'.$rows['Rows'].'</td>';
			print '<td align="center" class='.$bgcolor.'>'.$rows['Create_time'].'</td>';
			print '<td align="center" class='.$bgcolor.'>'.number_format($rows['Data_length']/1024,1).'</td>';
			print '<td align="center" class='.$bgcolor.'>'.number_format($rows['Index_length']/1024,1).'</td></tr>'."\n";
  		$i++;
  		if ($bgcolor=='grey') {
			$bgcolor='white';
  		} else {
			$bgcolor='grey';
  		}
  	}	

echo "
<tr><td colspan='6'>
<input type='checkbox' name='chg' onclick=\"checkAll(document.getElementById('dbform'),'check',this);\"><b>Check all tables</b>
</td></tr></table>
<center><input  id='submit' type='submit' name='send2' value='Optimize'></center><br>


<center>
<table>
 <tr>
	<td>
	 <font color='#990000' size='1'><strong>Backup File Name: </strong></font>

	 <input name='filename' type='text' class='textbox' id='filename' size='20' maxlength='25' value='$dbname.sql.gz' >
	<input  id='submit'"; if (!get_extension_funcs('zlib'))echo 'disabled'; echo " type='submit' name='send2' value='Backup'>
	</td>
 </tr>
 <tr>
	<td>
	 Backup structure only
	 <input type='checkbox' name='structonly' value='Yes'>
	</td>
</tr>
</table>

</form>
<br>
<table width='94%' border='0' cellspacing='0' cellpadding='0' align='center'>
  <TR> 
	  <TD valign='top'>";
  

if (isset($file) && $del==0) {
	  if (eregi("gz",$file)) { 
		 @unlink($backup_path."backup.sql");
		 $fp = @fopen($backup_path."backup.sql","w");
		 fwrite ($fp,"");
			fclose ($fp);
		 chmod($backup_path."backup.sql", 0777);
		 $fp = @fopen($backup_path."backup.sql","w");
		 $zp = @gzopen($backup_path.$file, "rb");
		 if(!$fp) {
			die("No sql file can be created"); 
		 }	
		 if(!$zp) {
			die("Cannot read zip file");
		 }	
		 while(!gzeof($zp)){
			$data=gzgets($zp, 8192);// buffer php
			fwrite($fp,$data);
		 }
		 fclose($fp);
		 gzclose($zp);
		 $file="backup.sql";

		flush();
	set_time_limit(1000);
	$file_temp=fread(fopen($backup_path.$file, "r"), filesize($backup_path.$file));
	$query=explode(";#%%\n",$file_temp);
	for ($i=0;$i < count($query)-1;$i++) {
		mysql_db_query($dbname,$query[$i]) or die(mysql_error());
	}
	unlink($backup_path.$file);
	echo "<table width=\"94%\"><tr><td><b>Your restore 
request was processed.</b> If you did not receive any errors on the screen, then 
you should find that your database tables have been restored.<br></td></tr></table>";
}
} 
if (isset($file) && $del==1) {
	@unlink($backup_path.$file);
}

?>
	  </TD>
	</TR>
	<TR> 
	  <TD valign="top">
	  <table width="100%" cellspacing="0">
		  
<?php
	if (!is_dir($backup_path)) {
		mkdir($backup_path, 0766);
	}
	$dir=opendir($backup_path); 
	$bgcolor='grey';
	$is_first=1;

		

	while ($file = readdir ($dir)) { 
		if ($file != "." && $file != ".." &&  (eregi("\.sql",$file) || eregi("\.gz",$file))){
			if($is_first==1){
				echo "<tr> 
				<td width=\"30%\" align=\"center\" class=\"greyHeading\"><b>File</b></td>
				<td width=\"20%\" align=\"center\" class=\"greyHeading\"><b>Size</b></td>
				<td width=\"20%\" align=\"center\" class=\"greyHeading\"><b>Date</b></td>
				<td class=\"greyHeading\">&nbsp;</td></tr>"; 
			}
			$is_first=0;
			echo "<tr><td nowrap class=$bgcolor align=\"center\">$file</td>
				 <td nowrap class=$bgcolor align=\"center\">".round(filesize($backup_path.$file) / 1024, 2)." kB</td>
				 <td nowrap class=$bgcolor align=\"center\">".date("Y-m-d",filemtime($backup_path.$file))."</td>
				 <td nowrap class=$bgcolor align=\"left\"><input type='button' id='submit' onclick=\"confirm_rest_prompt('./admin.php?f=database&file=$file&del=0');\" value='Restore'> &nbsp;<input type='button' id='submit' onclick=\"confirm_del_prompt('./admin.php?f=database&file=$file&del=1');\" value='Delete'></td>
				 </tr>"; 
			 
			if ($bgcolor=='grey') {
				$bgcolor='greyForm';
  			} else {
				$bgcolor='grey';
  			}
		}
		
	}
	closedir($dir);
	?>
		</table></TD>
	</TR>
	
  </TABLE>
</CENTER>
