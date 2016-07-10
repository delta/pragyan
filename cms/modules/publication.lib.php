<?php
if(!defined('__PRAGYAN_CMS'))
{ 
    header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
    echo "<h1>403 Forbidden</h1><h4>You are not authorized to access the page.</h4>";
    echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
    exit(1);
}
/**
 * @package pragyan
 * @copyright (c) 2008 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

class publication implements module
{
    private $userId;
    private $moduleComponentId;
    private $action;
	
   	public function getHtml($gotuid, $gotmoduleComponentId, $gotaction) 
   	{
	
       	$this->userId = $gotuid;
        $this->moduleComponentId = $gotmoduleComponentId;
        $this->action = $gotaction;
    
        if ($this->action == "view")
            return $this->actionView();
        if ($this->action == "edit")
            return $this->actionEdit();
	                                 
    }

    public function actionView()
    {

        global $urlRequestRoot, $moduleFolder, $cmsFolder,$templateFolder,$sourceFolder,$STARTSCRIPTS;
        require_once($sourceFolder."/".$moduleFolder."/publication/edit.php");


        $view ='';

        $view1 =<<<TABLE_HEAD
   <table id="publications">
    	<thead style="font-weight:bold;">
        	<td>
                sl no.
        	</td>
        	<td>
                publication
        	</td>
        	<td>
                Saved Time
        	</td>
    		<td>
      			Created Time
    		</td>
  		</thead>

TABLE_HEAD;


			/*Get stuff from database based on moduleComponentId */
        $counter=0;
        $query="SELECT * FROM publication_details where module_component_id =".$this->moduleComponentId.";";
        $details=mysql_query($query) or die("error ..".mysql_error());	
        
        $view2='';

        while($arr_details=mysql_fetch_array($details))
        {
            $counter1 = $counter+1;
            $view2.=<<<ROWS
				  <tr>
         			<td> $counter1 </td>
         			<td>{$arr_details['publication']}</td>
          			<td>{$arr_details['saved_time']}</td>
         			<td>{$arr_details['created_time']}</td>
         		 </tr>

ROWS;

            $counter++;
        
        }
        		
        $view3=<<<END_TABLE
   </table>

END_TABLE;
				/* Concatenate all the views */
        $view .=$view1.$view2.$view3;
                 

        return $view;
    }

	public function actionEdit()
    {

        global $urlRequestRoot, $moduleFolder, $cmsFolder,$templateFolder,$sourceFolder,$STARTSCRIPTS;
        require_once($sourceFolder."/".$moduleFolder."/publication/edit.php");

        $scriptFolder = "$urlRequestRoot/$cmsFolder/$moduleFolder/publication";
        $js="<script src='$scriptFolder/edit.js'></script>";

/* Check for get requests */
        if(isset($_GET['add']))
        {
            add_new($this->moduleComponentId);

        }
        if(isset($_GET['del']))
        {
            p_delete($this->moduleComponentId , $_GET['del']);
        }
			
        if(isset($_GET['subaction']))
        {
            $pb_array= Array();
	
            for ($i=0; $i <= $_POST['num']-1; $i++) 
            { 
                $pb_array[$i]=$_POST['p'.$i];
		
            }

            formSubmit($this->moduleComponentId , $pb_array);
										
        }
		
        $view ='';

        $view1 =<<<FORM_AND_TABLE
<form id='p_form' method='POST' enctype='multipart/form-data' action='./+edit&subaction=SUBMIT'> 
   <table id="publications">
    	<thead style="font-weight:bold;">
        	<td >
                sl no.
        	</td>
        	<td >
                Publication
        	</td>
        	<td >
                Saved Time
        	</td>
    		<td >
      			Created Time
    		</td>    	
  		</thead>

FORM_AND_TABLE;
			
			//Get the publications from database

        $counter=0;
        $query="SELECT * FROM publication_details where module_component_id =".$this->moduleComponentId.";";
        $details=mysql_query($query) or die("error ..".mysql_error());
        $rows=mysql_num_rows($details) - 1;	
        $view2='';
			 
        while($arr_details=mysql_fetch_array($details))
        {
            $counter1 = $counter+1;
				
            $view2.=<<<ROWS
				  <tr>
         			<td > $counter1 </td>
         			<td ><textarea name='p{$counter}' id='p{$counter}'>{$arr_details['publication']}</textarea></td>
          			<td >{$arr_details['saved_time']}</td>
         			<td >{$arr_details['created_time']}</td>
         			<td>
    					<button> 
    						<a href='./+edit&del={$counter}'>DELETE
    						</a>
    					</button>
    				</td>    
ROWS;
				//Check for first row 
            if($counter!=$rows)
                $view2.=<<<FIRST_ROW
					<td ><button type="button" onclick="reorder({$counter},1);"><span style='font-weight:bolder;font-size:25px;'>&#x2193;</span></button></td>
FIRST_ROW;
				//Check for last row
            if($counter!=0)
                $view2.=<<<LAST_ROW
				<td ><button type="button" onclick="reorder({$counter},-1);"><span style='font-weight:bolder;font-size:25px;'>&#x2191;</span></button></td>
LAST_ROW;
            $view2.="</tr>";					
				
            $counter++;

        }
        		
        $view3=<<<END_TABLE
   </table>
   <button><a href="./+edit&add=new">ADD NEW PUBLICATION</a></button>
   <input type='hidden' name='num' value='{$counter}'>
   <button type="submit" name="btn_submit">SUBMIT</button> 
 </form>  
END_TABLE;
        $view4=<<<js
				{$js}
js;
				
        $view .=$view1.$view2.$view3.$view4;
               
        return $view;
    }
    public function createModule($compId)
    {
        return true;
    }
    public function deleteModule($moduleComponentId)
    {
        return true;
    }
    public function copyModule($moduleComponentId, $newId)
    {
        return true;
    }
    public function moduleAdmin()
    {
        return "This is the Article module administration page. Options coming up soon!!!";
    }

}
