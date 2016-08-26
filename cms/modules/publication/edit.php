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
 * @copyright (c) 2016 Pragyan Team
 * @author Sriram Sundarraj
 * @author SHRAVAN MURALI
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

/* Function to get the details of the edited form and to send info to databse */
function formSubmit($mcd , $pb_array)
{

    $query ="SELECT `sl_no` FROM publication_details where module_component_id=".$mcd.";";
    $result = mysqli_query($GLOBALS["___mysqli_ston"], $query) or die("error1.");
 
    $counter=0;

//All the <textarea> fields are updated in the database

    while($array_nos = mysqli_fetch_array($result))
    {	
        $publication=$pb_array[$counter];
        $query_update = "UPDATE publication_details set publication='{$publication}' where sl_no=".$array_nos['sl_no'];
        $result_update = mysqli_query($GLOBALS["___mysqli_ston"], $query_update) or die("error.");
        $counter++;
	
    }

}

/* Function to add a new publication */
function add_new($mcd)
{

    $query = "INSERT INTO publication_details(publication ,saved_time , created_time , module_component_id) values('', CURRENT_TIMESTAMP , CURRENT_TIMESTAMP , ".$mcd.");";
    $result = mysqli_query($GLOBALS["___mysqli_ston"], $query);

}

/* Function to delete a publication */
function p_delete($mcd , $num)
{

    $query1= "SELECT `sl_no` from publication_details where module_component_id=".$mcd;
    $result1 = mysqli_query($GLOBALS["___mysqli_ston"], $query1) or die("error. ".((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));

    $counter=0;

    while($arr = mysqli_fetch_array($result1))
    {
        if($counter==$num)
        {
            $query = "DELETE FROM publication_details where sl_no=".$arr['sl_no'];
            $result = mysqli_query($GLOBALS["___mysqli_ston"], $query);
            break;
        }	
    
    $counter++;   
    
    }

}

?>
