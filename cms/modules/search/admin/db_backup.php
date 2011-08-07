<?php
include "auth.php";
$backup_path="./backup/";

$stats  = mysql_query("SHOW TABLE STATUS FROM $database LIKE '$mysql_table_prefix%'");
$numtables = mysql_num_rows($stats);
$starttime=microtime();
if($send2=="Optimize"){
	$i = 0;  
	while($i < $numtables) {
		if (isset($tables[$i])) {
		  mysql_query("OPTIMIZE TABLE ".$tables[$i]);

			}
		$i++;
		}
} else{
   if (!is_dir($backup_path)) mkdir($backup_path, 0766);
   chmod($backup_path, 0777);

	$fp = gzopen ($backup_path.$filename,"w");
        $copyr="# Table backup from Sphider\n".
               "# Creation date: ".date("d-M-Y H:s",time())."\n".
               "# Database: ".$database."\n".
               "# MySQL Server version: ".mysql_get_server_info()."\n\n" ;
	gzwrite ($fp,$copyr);
	gzclose ($fp);
  chmod($backup_path.$filename, 0777);


if (!eregi("/restore\.",$_SERVER['PHP_SELF'])) {
	$cur_time=date("Y-m-d H:i");
	$i = 0;  
	$fp = gzopen ($backup_path.$filename,"a");
	while($i < $numtables) { 
           if (isset($tables[$i])) {
	         	 get_def($database,$tables[$i],$fp);
	           if (!isset($structonly) || $structonly!="Yes") {
	             get_content($database,$tables[$i],$fp);
	       	   }	      
	         }
	      $i++;
	}	
	   gzwrite ($fp,"# Valid end of backup from Sphider backup\n");
     gzclose ($fp);
}
}
function get_def($database,$table,$fp) {
 
    $def = "";
    $def .= "DROP TABLE IF EXISTS $table;#%%\n";
    $def .= "CREATE TABLE $table (\n";
    $result = mysql_db_query($database, "SHOW FIELDS FROM $table") or die("Table $table not existing in database");
    while($row = mysql_fetch_array($result)) {
        $def .= "    $row[Field] $row[Type]";
        if ($row["Default"] != "") $def .= " DEFAULT '$row[Default]'";
        if ($row["Null"] != "YES") $def .= " NOT NULL";
       	if ($row["Extra"] != "") $def .= " $row[Extra]";
        	$def .= ",\n";
     }
     $def = ereg_replace(",\n$","", $def);
     $result = mysql_db_query($database, "SHOW KEYS FROM $table");
     while($row = mysql_fetch_array($result)) {
          $kname=$row["Key_name"];
          if(($kname != "PRIMARY") && ($row["Non_unique"] == 0)) $kname="UNIQUE|$kname";
          if(!isset($index[$kname])) $index[$kname] = array();
          $index[$kname][] = $row["Column_name"];
     }
     while(list($x, $columns) = @each($index)) {
          $def .= ",\n";
          if($x == "PRIMARY") $def .= "   PRIMARY KEY (" . implode($columns, ", ") . ")";
          else if (substr($x,0,6) == "UNIQUE") $def .= "   UNIQUE ".substr($x,7)." (" . implode($columns, ", ") . ")";
          else $def .= "   KEY $x (" . implode($columns, ", ") . ")";
     }

     $def .= "\n);#%%\n\n";
     $def=stripslashes($def);
     gzwrite ($fp,$def);
     //return (stripslashes($def));
}

function get_content($database,$table,$fp) {
     $result = mysql_db_query($database, "SELECT * FROM $table") or die("Cannot get content of table");
          
     while($row = mysql_fetch_row($result)) {
         
         $insert = "INSERT INTO $table VALUES (";
        
         for($j=0; $j<mysql_num_fields($result);$j++) {
            if(!isset($row[$j])) $insert .= "NULL,";
            elseif(isset($row[$j])) $insert .= "'".addslashes($row[$j])."',";
            else $insert .= "'',";
         }
         $insert  = ereg_replace(",$","",$insert);
         $insert .= ");#%%\n";
         gzwrite ($fp,$insert);
        
     }
     
     gzwrite ($fp,"\n\n");
     
}
function diff_microtime($mt_old,$mt_new)
{
  list($old_usec, $old_sec) = explode(' ',$mt_old);
  list($new_usec, $new_sec) = explode(' ',$mt_new);
  $old_mt = ((float)$old_usec + (float)$old_sec);
  $new_mt = ((float)$new_usec + (float)$new_sec);
  return $new_mt - $old_mt;
}
//echo("<script language='JavaScript'>window.location = './admin.php?f=database';</script>");
?>
