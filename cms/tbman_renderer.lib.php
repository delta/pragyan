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
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
 
class tbman
{
  protected $tablename;
  protected $result;
  public $editable;
  public $formaction;
  protected $querystring;
  private $imagePath;
  private $scriptPath;
  
  function tbman($querystring)
  {
    $this->tablename=$this->get_tablename_from_query($querystring);
    $this->querystring=$querystring;
  //  echo "<br/>querystring in tbman_renderer.lib.php: ".$querystring;
    @ $result=mysql_query($querystring);//@suppresses error messages
    if(!$result) {                             // and allows to put custom error messages like this one - Error: (used here)
    	displayerror("Error(tbman_renderer.lib.php): ".mysql_error());
    	return;
    }
	  else
      $this->result=$result;
    if(stristr($querystring,"select"))
       $this->editable="yes";
    global $urlRequestRoot;
    global $cmsFolder,$sourceFolder;
    global $templateFolder;
    $this->imagePath = "$urlRequestRoot/$cmsFolder/$templateFolder/common/images";
    $this->scriptPath = "$urlRequestRoot/$cmsFolder/$templateFolder/common/scripts";
  }
  
  function make_table()
  {
    $result=$this->result;

	$str =<<<STR
		<style type="text/css">
	.first 	{	background-color:#d3dce3;	}
	.even 	{	background-color:#e5e5e5;	}
	.odd 	{	background-color:#d5d5d5;	}
		</style>
		<script type="text/javascript" src="$this->scriptPath/tbman.js"></script>
STR;
	$str.="\n<form id='f1' name='f1' method='post' action='".$this->formaction."'>";
    $str.="\n<table id=resultTable cellpadding=2 cellspacing=1>";
	
    for($i=0;$i<mysql_num_rows($result);$i++)
      {
	$a_rows=mysql_fetch_assoc($result);

#Field name row and add row-
	if($i==0)//ie run only once
	{
	    $str.="\n<tr bgcolor=#d3dce3>\n";
	    if($this->editable=="yes")	    
	    {
		    $str.='<td><input type="checkbox" onClick="challtoggle(this)"></td>';
		    $str.="<td colspan=2></td>";
		    $str_addRow="\n<tr bgcolor=#d3dce3 id=\"addRow\" style=\"display:none\">\n";
		    $str_addRow.="<td><input type=\"checkbox\" id=\"checkiaddRow\" onClick=\"if(this.checked==false) {document.getElementById('addRow').style.display='none';document.getElementById('addRowLink').style.display='';statusOfUpdateButton();}\"></td>";
		    $str_addRow.="<td colspan=2></td>";
		    foreach($a_rows as $key=>$value)
		      {
			$str_addRow.="\n\t<td id=".str_replace(" ","_",$key)."addRow".">";

			if(($input_box_size=$this->get_size_of_field($key))!="text_type")
				$str_addRow.=('<input type="text" value="" size="'.$input_box_size.
	    '" name='.str_replace(" ","_",$key)."addRow".' /></td>');
	  	else
				$str_addRow.=('<textarea rows="3" cols="25" name='.str_replace(" ","_",$key)."addRow".' /></textarea></td>');
			
			$str_addRow.='</td>';
		      }
		    $str_addRow.="\n</tr>";
	    }
	    $fields = "";
	    foreach($a_rows as $key=>$value)
	    {
		$str.="<td>".$key."</td>";
		#to get the fields array:
		$fields.=$key."|";//to pass all the fields (later)
	    }
	    $fields=substr($fields,0,-1);
	    $str.="\n</tr>";
	}

#data rows-
	$dat_str="\n<tr id=\"dat".$i."\" bgcolor=".(($i%2==0)?"#e5e5e5":"#d5d5d5").">\n";
	$dat_str.="\n\t<input id=\"chan".$i."\" type=\"hidden\" value=\"notchanged\">";
	$inp_str="\n<tr id=\"inp".$i."\" style=\"display:none\" bgcolor=".(($i%2==0)?"#e5e5e5":"#d5d5d5").">";

#edit columns-
###$dat_str- code for initially shown rows
###$dat_str- code for initially hidden rows
	if($this->editable=="yes")
	  {
	    $dat_str.="\n\t<td><input onClick=\"statusOfDeleteSelected()\" id=checkd".$i." type=checkbox /></td>";
	    //toggle only in checki coz only reverse toggle required
	    //need to remove edit if not selected but no need to add edit if selected
	    $dat_str.="\n\t<td><a href=\"javascript:toggle(".$i.")\"><img src=\"$this->imagePath/b_edit.png\" border=0 alt=\"edit\"></a></td>";
	    $dat_str.="\n\t<td><a href=\"javascript:deleteRow(".$i.")\"><img src=\"$this->imagePath/b_drop.png\" border=0 alt=\"delete\"></a></td>";
	    
	    $inp_str.="\n\t<td><input onClick=\"toggle(".$i.")\" id=checki".$i." type=checkbox /></td>";
	    $inp_str.="\n\t<td><a href=\"javascript:toggle(".$i.")\"><img src=\"$this->imagePath/b_edit.png\" border=0 alt=\"edit\"></a></td>";
	    $inp_str.="\n\t<td><a href=\"javascript:deleteRow(".$i.")\"><img src=\"$this->imagePath/b_drop.png\" border=0 alt=\"delete\"></a></td>";
	  }
#data columns- 
	//  print_r($a_rows);
	$inp3_str="";//resetting it
	foreach($a_rows as $key=>$value)
	  {
	    $dat_str.="\n\t<td>".$value."</td>";
	    $inp_str.=("\n\t<td id=".str_replace(" ","_",$key).$i.">");
	    if(($input_box_size=$this->get_size_of_field($key))!="text_type")
		$inp_str.=('<input type="text" value="'.$value.'" size="'.$input_box_size.
		'" name='.str_replace(" ","_",$key).$i.' /></td>');
	  	else
		$inp_str.=('<textarea rows="3" cols="25" name='.str_replace(" ","_",$key).$i.' />'.$value.'</textarea></td>');
	  }
	$dat_str.="\n</tr>";
	$inp_str.="\n</tr>";
	if($this->editable=="yes")
	  $str.=$dat_str.$inp_str;
	else
	  $str.=$dat_str;
      }
    if($this->editable=="yes")
      $str.=$str_addRow;
    
    $str.="\n</table>";
 #add row, update and delete all button-
    if($this->editable=="yes")
      {
	$str.="\n<table>";
	$str.="\n<tr><td><input id=\"noOfRows\" name=\"noOfRows\" type=\"hidden\" value=\"".$i."\"></td>";//to compensate for the addRow
	$str.="\n<td id=\"addRowLink\" onClick=\"document.getElementById('addRow').style.display='';document.getElementById('checkiaddRow').checked=true;this.style.display='none';statusOfUpdateButton();\"><small>Add record</small></td>";
	$str.="\n<td id=\"deleteSelectedText\"><small>Delete selected</small></td>";
	$str.="\n<td id=\"deleteSelectedLink\" style=\"display:none\">
<a href=\"javascript:deleteRow('selected')\"><small>Delete selected</small></a></td>\n";
	$str.='<td id="updateButton" style="display:none">
<input onClick=\'update()\' type="submit" value="Update"></input></td></tr>';

	$str.="\n<input type=\"hidden\" id=\"fields\" name=\"fields\" value=\"".$fields."\" />";
	$str.="\n<input type=\"hidden\" id=\"querystring\" name=\"querystring\" value=\"".$this->querystring."\" />";
	$str.="\n<input type=\"hidden\" id=\"buttonpressed\" name=\"buttonpressed\" value=\"\" />";
	$str.="\n<input type=\"hidden\" id=\"tablename\" name=\"tablename\" value=\"".$this->tablename."\" />";
	$str.="\n</table>";
      } 
    $str.="\n</form>";
    
    return $str;
  }
 
  function get_tablename_from_query($query)
  {
  //selection of tablename-
    $a=strtok($query,' ');
    while(is_string($a)) 
      { 
	if($a)
	  { 
	    if (strtolower($a)!='from')
	      {
		$a=strtok(' ');
		continue;
	      }
	    else
	      {
		$a=strtok(' ');
		break;
	      }
	  }
      }
    return $a;
  }
  
  function get_size_of_field($fieldname)
  {
    if(!is_string($this->tablename)) return "text_type";

    //making query to get info about the column named $key-
		/*mysql_field_len($result,$key).*/    
    $b=mysql_fetch_assoc(mysql_query("explain ".$this->tablename." '".$fieldname."'"));

    $a=$b['Type'];//gives datatype of this format - varchar(20)
	if($a=="text") return "text_type";
    $a=substr($a,strpos($a,'(')+1,strpos($a,')')-strpos($a,'(')-1);//extracting 20 out of varchar(20)

    if(!is_string($a)) return 15;
    else if ($a>15) return 15; 
    return $a;
  }

}
