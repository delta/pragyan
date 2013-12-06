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
 * @author Abhishek Shrivastava
 * @brief Widget Framework for Pragyan CMS
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 * @TODO Add support for File Upload/Download via the widget's configurations
 * @TODO Reload Widgets ,Get Widget Instances. see bottom.
 */
 
 /** 
  * @description
  * Idea :
 A widget is a small customized html code that can be put in any part of the generated page. 
 The template's index.php will have variables $WIDGETS[1], $WIDGETS[2] to $WIDGETS[n] where n is user-defined (its 31 for now).
 Each $WIDGETS[i] represents a unique-location and NOT a unique widget i.e. $WIDGETi can have multiple widgets, but in same location.
 
 
 Put all the widgets inside cms/widgets folder. 
 Every widget should have a folder by its name inside cms/widgets.
 Inside every folder there will be a description file consisting of widget information and usage details.
 Each such folder will have widget.class.php file having a class which should extend widgetFramework abstract class.
 The class will have abstract functions like :

 initWidget() which will be called prior to widget execution.
 getHTML() to get the widget's output html code
 
 Besides these, the class should also create a constructor __construct which will call the parent constructor by passing the configurations to it along with other information. 
 
 widgetFramework will implement the following functions :
 
  __construct() to pass main information like widget id, instance id and default configurations.
 createWidget() will create the entry of the widget in the database along with proper settings
 @see widgetFramework.class.php for more detailed information about implemented functions.
 

-----------------------------------------------------------------
Database Structure :
#TODO Need to verify with the current structure
 
 PragyanV3_widgetsinfo table :
 *widget_id	widget_name	widget_classname	widget_description	widget_version	widget_author	widget_foldername
 
 PragyanV3_widgetsconfiginfo table :
 *widget_id	 *config_name	config_type	config_options	config_displaytext	config_default	is_global

 PragyanV3_widgets table :
 *widget_id	*widget_instanceid	page_id		widget_location		widget_order

  PragyanV3_widgetsconfig
 widget_id	widget_instanceid	config_name	config_value
 
 PragyanV3_widgetsdata
 widget_id	widget_instanceid	widget_datakey		widget_datavalue

 @note Global configurations are the configurations which are common to all the instances of a particular widget type.
 
 @description
 Algo ::
 
 When a page is being opened.
 

 For all widget_id and widget_instanceid from PragyanV3_widgets for page_id order by widget_location, widget_order
 	widget_name = Select widget_name from PragyanV3_widgetsinfo for widget_id
 	Include file cms/widgets/widget_name/widget.class.php
 	widget_object = Object of class widget_name passing widget_id , page_id and widget_instanceid as parameters to constructor
 	Check if widget is installed properly using validInstall() method and if not, do installWidget() and loadWidget()
 	widget_object->initWidget()
 	widget_output = widget_object->getHTML()
 	$WIDGETS[<widget_location>] .= widget_output
 
 @note All the widgets will have the power to add/remove/modify their information in PragyanV3_widgetsdata table
 @note The widgets will be given some RAM like or heap store in _widgetsdata table to add their own information in key => value format.
 
 For administrators. (+admin&subaction=widgets)
 
 1) Generate the list of all the widgets based on PragyanV3_widgetinfo table.
 2) When clicking on a widget, a page should be displayed with some widget informations like authorname from PragyanV3_widgetsinfo table
 3) The above page should also have links to change instance-specific and universal configurations for that widget.
 4) When clicked on instance-specific, a list of all the page_urls (dereferenced from page_id in PragyanV3_widgets table) is generated.
 5) Clicking on any url should open a form-type configuration page based upon PragyanV3_widgetsconfiginfo and PragyanV3_widgetsconfig.
 6) When clicked on universal configurations in (3), it should open a form-like page from PragyanV3_widgetglobalconfig
 7) Widget Installation and Removal (LATER)
 
 For page-admins (+widgets)
 
 1) List of available, enabled widgets for that page.
 2) Configure button to configure the enabled widgets
 3) Manipulate the ordering and Location ID of the widgets.
 @end
*/


/**
 * Populates the widget variables $WIDGETS[1],... $WIDGETS[n] based on the widgets enabled in that page. All the variables are then replaced in
 * template accordingly.
 * @param $pageId Page ID of the page for which to populate the widgets
 */
function populateWidgetVariables($pageId)
{

	global $cmsFolder,$widgetFolder,$WIDGETS;	
	$enwidgets=getEnabledWidgets($pageId);
	$inwidgets=getInheritedWidgets($pageId);
	$enwidgets=array_merge($enwidgets,$inwidgets);
	$widgetsenmap=array();
	
	foreach( $enwidgets as $enwidget )
	{
		$classname=$enwidget['classname'];
		require_once("$widgetFolder/{$enwidget['foldername']}/widget.class.php");
		$widget = new $classname($enwidget['id'],$enwidget['instanceid'],$pageId);
		if(!($widget instanceof widgetFramework))
		{
			displayerror("The widget {$enwidget['name']} doesn't extends widgetFramework class");
			return "";
		}
		
		$class_methods = get_class_methods($widget);
		if(!in_array('initWidget',$class_methods) || !in_array('getHTML',$class_methods))
		{
			displayerror("The widget {$enwidget['name']} is not properly defined.");
			return "";
		}
		
		$widget->initWidget();
		
		if(!isset($widgetsenmap[$enwidget['id']]) || $widgetsenmap[$enwidget['id']]==false)
		{
			if(empty($WIDGETS[(int)$enwidget['location']]))
			  $WIDGETS[(int)$enwidget['location']] = $widget->getCommonHTML();
			else $WIDGETS[(int)$enwidget['location']] .= $widget->getCommonHTML();
			$widgetsenmap[$enwidget['id']]=true;
		}
		
		$WIDGETS[(int)$enwidget['location']] .= $widget->getHTML();
	}
}


/**
 * Handles the widgets configurations for a particular page
 * @param $pageId Page ID of the current page
 * @return HTML code of the widget configuration interface for page settings
 */
function handleWidgetPageSettings($pageId)
{
	global $ICONS,$ICONS_SRC,$urlRequestRoot,$cmsFolder,$moduleFolder;
	
	/**
	 * @todo Add widget builder, template editor to change the $WIDGET locations, location-visualizer etc.
	 */
	$quicklinks ="
	<fieldset>
        <legend>{$ICONS['Widgets']['small']}Widgets</legend>
        <table class='iconspanel'>
        <tr>
        <td><a href='./+widgets#enabledwidgets'><div>{$ICONS['Widgets']['large']}<br/>Enabled Widgets for this Page</div></a></td>
        <td><a href='./+widgets#inheritedwidgets'><div>{$ICONS['Propagate']['large']}<br/>Inherited Widgets for this Page</div></a></td>
        <td><a href='./+widgets&subaction=enable'><div>{$ICONS['Add']['large']}<br/>Add More Widgets</div></a></td>
        </tr>
        </table>   
        </fieldset>
        ";
        $html = "";
        
      	if(isset($_GET['subaction']) && $_GET['subaction']=='enable')
      	{
      		if(isset($_GET['widgetid']))
      		{
      			$widgetId=escape($_GET['widgetid']);
      			createWidgetInstance($pageId,$widgetId);	
      		}
      		
      		$widgetsarr=getAllWidgetsInfo();
		$allwidgets = "<fieldset><legend>{$ICONS['Add']['small']}Add Widgets</legend>";
	
		$allwidgets .= "<table width=100%><tr><th colspan=4>Available Widgets<br/><i>Mouse over for description</i></th></tr>
		<tr><th>Name</th><th>Version</th><th>Author</th><th>Add</th></tr>";
		foreach( $widgetsarr as $widget )
		{
			$allwidgets.="\n<tr><td><a title='".$widget['description']."'>".$widget['name']."</a></td><td>{$widget['version']}</td><td>{$widget['author']}</td><td><a href='./+widgets&subaction=enable&widgetid={$widget['id']}'><img src='{$ICONS_SRC['Add']['large']}' title='Add an instance of this widget' /></a></td></tr>";
		}
		$allwidgets.="</table></fieldset>";
		
		$html.=$allwidgets;
      	}
      	if(isset($_GET['subaction']) && isset($_GET['widgetid']) && isset($_GET['widgetinstanceid']))
      	{
      		$subaction=escape($_GET['subaction']);
	 	$widgetid=escape($_GET['widgetid']);
   		$widgetinstanceid=escape($_GET['widgetinstanceid']);
   		
      		if($subaction=="config")
   		{
   			/// POST variables are processed inside this function
   			if(isset($_GET['subsubaction']) && $_GET['subsubaction']=="update")
				updateWidgetConf($widgetid,$widgetinstanceid,FALSE); 
			
			
			$widgetinfo=getWidgetInfo($widgetid);
			$widgetpageconfigs=getWidgetPageConfigInfo($widgetid);
		
			/// @todo Do something about file uploads by widgets
			$containsFileUploadFields = false;
			$formElements=getConfigFormAsArray($widgetpageconfigs,$containsFileUploadFields,$widgetinstanceid,FALSE);
			
			$jsPath = "$urlRequestRoot/$cmsFolder/templates/common/scripts/formValidator.js";//validation.js
			$calpath = "$urlRequestRoot/$cmsFolder/$moduleFolder/form/calendar";
			$jsPathMooTools = "$urlRequestRoot/$cmsFolder/templates/common/scripts/mootools-1.11-allCompressed.js";
		
			$html .= '<link rel="stylesheet" type="text/css" media="all" href="'.$calpath.'/calendar.css" title="Aqua" />' .
							 '<script type="text/javascript" src="'.$calpath.'/calendar.js"></script>';
			$html .= '<fieldset><legend>'.$ICONS['Widgets']['small'].'Widget Page Settings</legend><div class="registrationform"><form class="fValidator-form" name="widgetpagesettings" action="./+widgets&subaction=config&subsubaction=update&widgetid='.$widgetid.'&widgetinstanceid='.$widgetinstanceid.'" method="post"';
		
			if($containsFileUploadFields)
				$html .= ' enctype="multipart/form-data"';
			$html .= '>';
		
			$html.="<table class='pragyan_fulltable'><tr><th colspan=2>Widget : {$widgetinfo['name']}</th><tr>";
			$html.="<tr><td>Description : </td><td> {$widgetinfo['description']}</td></tr>";
			$html .="<tr>".join($formElements, "</tr>\n<tr>")."</tr>";
		
			$html.="</table><input name='update_widget_page_settings' type='submit' value='Update'/>" .
					"<input type='reset' value='Reset'/>";
			$html.="</form><br/></fieldset>";
   		}
   		else if($subaction=="delete")
   			deleteWidgetInstance($widgetid,$widgetinstanceid);
   		else if($subaction=='propagate')
   			propagateWidgetInstance($widgetid,$widgetinstanceid);
   		else if($subaction=='unpropagate')
   			unpropagateWidgetInstance($widgetid,$widgetinstanceid);
      	}
      	if(isset($_GET['subaction']) && isset($_GET['subsubaction']) && isset($_GET['widgetid']) && isset($_GET['widgetinstanceid']) )
      	{
	 	$subaction=escape($_GET['subaction']);
	 	$subsubaction=escape($_GET['subsubaction']);
	 	$widgetid=escape($_GET['widgetid']);
   		$widgetinstanceid=escape($_GET['widgetinstanceid']);
   		
   		if($subaction=="location")
   		{
	   		if($subsubaction=="up")
	   		{
	   			modifyWidgetInstanceLocation($pageId,$widgetid,$widgetinstanceid,"-1");
	   		}
	   		if($subsubaction=="down")
	   		{
	   			modifyWidgetInstanceLocation($pageId,$widgetid,$widgetinstanceid,"+1");
	   		}
	   	}
	   	else if($subaction=="order")
	   	{
	   		if($subsubaction=="up")
	   		{
	   			modifyWidgetInstanceOrder($pageId,$widgetid,$widgetinstanceid,"-1");
	   		}
	   		if($subsubaction=="down")
	   		{
	   			modifyWidgetInstanceOrder($pageId,$widgetid,$widgetinstanceid,"+1");
	   		}
	   	}
      	}
        
	$enabledwidgetsarr=getEnabledWidgets($pageId);
	
	$enabled = "<fieldset><legend>{$ICONS['Widgets']['small']}Enabled Widgets</legend><a name='enabledwidgets'></a>";
	$enabled .= "<table class='pragyan_fulltable'><tbody><tr><th colspan=4>Enabled Widgets <br/><i>in order of their appearance</i></th></tr>
	<tr><th>Widget</th><th>Location</th><th>Order</th><th>Actions</th></tr>";
	
	foreach ( $enabledwidgetsarr as $widget )
	{
		$propagatebtn = "<a href='./+widgets&subaction=propagate&widgetid={$widget['id']}&widgetinstanceid={$widget['instanceid']}'><img src='{$ICONS_SRC['Propagate']['small']}' title='Propagate : Add this widget to all the child pages recursively. Widget will retain its location.' /></a>";
		$unpropagatebtn = "<a href='./+widgets&subaction=unpropagate&widgetid={$widget['id']}&widgetinstanceid={$widget['instanceid']}'><img src='{$ICONS_SRC['Unpropagate']['small']}' title='Unpropagate : Remove the copies of this widget from all the child pages recursively.' /></a>";
		
		$configbtn = "<a href='./+widgets&subaction=config&widgetid={$widget['id']}&widgetinstanceid={$widget['instanceid']}'><img src='{$ICONS_SRC['Edit']['small']}' title='Edit : Configure this instance of this widget' /></a>";
		
		$deletebtn = "<a href='./+widgets&subaction=delete&widgetid={$widget['id']}&widgetinstanceid={$widget['instanceid']}'><img src='{$ICONS_SRC['Delete']['small']}' title='Delete : Delete this instance of this widget' /></a>";
		$locationup = "<a href='./+widgets&subaction=location&subsubaction=up&widgetid={$widget['id']}&widgetinstanceid={$widget['instanceid']}'><img src='{$ICONS_SRC['Up']['small']}' title='Move to an upper location' /></a>";
		$locationdown = "<a href='./+widgets&subaction=location&subsubaction=down&widgetid={$widget['id']}&widgetinstanceid={$widget['instanceid']}'><img src='{$ICONS_SRC['Down']['small']}' title='Move to a lower location' /></a>";
		
		$orderup = "<a href='./+widgets&subaction=order&subsubaction=up&widgetid={$widget['id']}&widgetinstanceid={$widget['instanceid']}'><img src='{$ICONS_SRC['Up']['small']}' title='Move to an upper order' /></a>";
		$orderdown = "<a href='./+widgets&subaction=order&subsubaction=down&widgetid={$widget['id']}&widgetinstanceid={$widget['instanceid']}'><img src='{$ICONS_SRC['Down']['small']}' title='Move to a lower order' /></a>";
		
		if($widget['propagate']=='1') $propunpropbtn=$unpropagatebtn;
		else $propunpropbtn=$propagatebtn;
		
		$enabled .= "\n<tr><td><a title='{$widget['description']}' href='./+widgets&subaction=config&widgetid={$widget['id']}&widgetinstanceid={$widget['instanceid']}'>{$widget['name']}</a></td><td>{$widget['location']} $locationup $locationdown</td><td>{$widget['order']} $orderup $orderdown</td><td>$configbtn $deletebtn $propunpropbtn</td></tr>";
		
	}
	$enabled .="</tbody></table></fieldset>";
	$enabled .="<fieldset><legend>{$ICONS['Propagate']['small']}Inherited Widgets</legend><a name='inheritedwidgets'></a><b>Note: Inherited widgets can be configured from the origin page only and they are always ordered last in their location in the inherited pages.</b>";
	$enabled .="<table class='pragyan_fulltable'><tbody><tr><th colspan=3>Inherited Widgets <br/><i>due to propagation from a parent page</i></th></tr><tr><th>Widget</th><th>Location</th><th>Origin</th></tr>";
	
	$inheritedwidgetsarr=getInheritedWidgets($pageId);
	foreach ( $inheritedwidgetsarr as $widget )
	{
		$link=hostURL().$widget['source'];
		$enabled .= "\n<tr><td><a title='{$widget['description']}'>{$widget['name']}</a></td><td>{$widget['location']}</td><td><a href='$link'>{$widget['source']}</a></td></tr>";
	}
	$enabled .="</tbody></table>";
	
	
	
	$enabled .="</fieldset>";
	
	
	return $html.$enabled.$quicklinks;
	
}
/**
 * Gets the information about all the widgets which are inherited to a page via any parent page.
 * @param $pageId Page Id of the given page 
 * @return Array of widgets containing widgets information
 */
function getInheritedWidgets($pageId)
{
	$parentId=getParentPage($pageId);
	
	if($parentId==$pageId) return array();
	
	$query="SELECT t1.`widget_id` AS 'id', t1.`widget_instanceid` AS 'instanceid', t1.`widget_location` AS 'location', t2.`widget_name` AS 'name', t2.`widget_description` AS 'description', t2.`widget_author` AS 'author', t2.`widget_version` AS 'version', t2.`widget_classname` AS 'classname', t2.`widget_foldername` AS 'foldername' FROM `".MYSQL_DATABASE_PREFIX."widgets` AS t1, `".MYSQL_DATABASE_PREFIX."widgetsinfo` AS t2 WHERE t1.`page_id`='$parentId' AND t1.`widget_propagate`=1 AND t2.`widget_id`=t1.`widget_id` ORDER BY t1.`widget_location` ASC";
	$result=mysql_query($query);
	$return=array();
	while($row=mysql_fetch_array($result))
	{
		$row['source']=getPagePath($parentId);
		$return[]=$row;
	}
	
	$more=getInheritedWidgets($parentId);
	$return=array_merge($return,$more);
	return $return;
}

/**
 * Marks the widget for propagation to all child pages.
 * @param $widgetId Widget ID
 * @param $widgetInstanceId Instance of the widget that is to be propagated
 */
function propagateWidgetInstance($widgetId,$widgetInstanceId)
{
	$query="UPDATE `".MYSQL_DATABASE_PREFIX."widgets` SET `widget_propagate`=1 WHERE `widget_id`='$widgetId' AND `widget_instanceid`='$widgetInstanceId'";
	mysql_query($query);
	displayinfo("Widget has been succesfully propagated to all child pages recursively.");
}

/**
 * Unmarks the widget for propagation to all child pages. So that it wont be propagated anymore.
 * @param $widgetId Widget ID
 * @param $widgetInstanceId Instance of the widget that is to be unpropagated
 */
function unpropagateWidgetInstance($widgetId,$widgetInstanceId)
{
	$query="UPDATE `".MYSQL_DATABASE_PREFIX."widgets` SET `widget_propagate`=0 WHERE `widget_id`='$widgetId' AND `widget_instanceid`='$widgetInstanceId'";
	mysql_query($query);
	displayinfo("Widget copies has been succesfully removed from all child pages recursively.");
}

/**
 * Deletes a particular instance of the widget including all its configurations.
 * @param $widgetId ID of the widget to be deleted
 * @param $widgetInstanceId Instance ID of the particular widget instance to be deleted
 * @return Boolean True if deletion was successful, else false.
 */
function deleteWidgetInstance($widgetId,$widgetInstanceId)
{
	$query="DELETE FROM `".MYSQL_DATABASE_PREFIX."widgets` WHERE `widget_id`='$widgetId' AND `widget_instanceid`='$widgetInstanceId'";
	if(mysql_query($query)===FALSE)
	{
		displayerror("Could not delete widget. Internal error occurred.");
		return FALSE;
	}
	
	$query="DELETE FROM `".MYSQL_DATABASE_PREFIX."widgetsconfig` WHERE `widget_id`='$widgetId' AND `widget_instanceid`='$widgetInstanceId'";
	if(mysql_query($query)===FALSE)
	{
		displayerror("Could not delete widget. Internal error occurred.");
		return FALSE;
	}
	
	$query="DELETE FROM `".MYSQL_DATABASE_PREFIX."widgetsdata` WHERE `widget_id`='$widgetId' AND `widget_instanceid`='$widgetInstanceId'";
	if(mysql_query($query)===FALSE)
	{
		displayerror("Could not delete widget. Internal error occurred.");
		return FALSE;
	}
	
	displayinfo("Widget successfully deleted!");
	return TRUE;
	
}

/**
 * Modify the widget location.
 * @param $pageId Page Id of the page in which the widget exists
 * @param $widgetId The Widget ID of the widget to relocate
 * @param $widgetInstanceId The Widget Instance ID of the widget to relocate
 * @param $mod The modification to be done in the widget location. Should start with either + or - operator, followed by a number.
 * @note The widget location cannot be negative.
 * @return Boolean True if relocation done successfully or else False.
 */
function modifyWidgetInstanceLocation($pageId,$widgetId,$widgetInstanceId,$mod)
{
	$query="UPDATE `".MYSQL_DATABASE_PREFIX."widgets` SET `widget_location`=`widget_location`$mod WHERE `page_id`='$pageId' AND `widget_id`='$widgetId' AND `widget_instanceid`='$widgetInstanceId' AND `widget_location`$mod >= 0";
	$res=mysql_query($query);
	if(!$res) return false;
	if(mysql_affected_rows()==0)
		displayerror("Could not move widget to that location. Location cannot be negative.");
	else displayinfo("Widget has been successfully relocated.");			
}

/**
 * Modify the widget order.
 * @param $pageId Page Id of the page in which the widget exists
 * @param $widgetId The Widget ID of the widget to relocate
 * @param $widgetInstanceId The Widget Instance ID of the widget to relocate
 * @param $mod The modification to be done in the widget order. Should start with either + or - operator, followed by a number.
 * @return Boolean True if relocation done successfully or else False.
 */
function modifyWidgetInstanceOrder($pageId,$widgetId,$widgetInstanceId,$mod)
{
	$query="UPDATE `".MYSQL_DATABASE_PREFIX."widgets` SET `widget_order`=`widget_order`$mod WHERE `page_id`='$pageId' AND `widget_id`='$widgetId' AND `widget_instanceid`='$widgetInstanceId'";
	$res=mysql_query($query);
	if(!$res) return false;
	if(mysql_affected_rows()==0)
		displayerror("Could not move widget. Some internal error occurred.");
	else displayinfo("Widget has been successfully reordered.");			
}

/**
 * Creates an instance of the widget on the given page using default location and order
 * @note Default location is 1, and it will chose the default order as the 'maximum order value + 1' for the location 1.
 * @param $pageId Page Id of the page in which to create the widget
 * @param $widgetId Widget Id of the widget to enable
 * @return Boolean true if success, else false.
 */
function createWidgetInstance($pageId,$widgetId)
{
	$query="SELECT `widget_name` AS 'name', `widget_classname` AS 'classname', `widget_foldername` AS 'foldername' FROM `".MYSQL_DATABASE_PREFIX."widgetsinfo` WHERE `widget_id`='$widgetId'";
	$res=mysql_query($query);
	if(mysql_num_rows($res)==0)
	{
		displayerror("Required widget is not registered with Pragyan CMS properly.");
		return false;
	}
	$row=mysql_fetch_array($res);
	
	global $widgetFolder;
	$classname=$row['classname'];
	require_once("$widgetFolder/{$row['foldername']}/widget.class.php");
	
	///Initializing as global instance.
	$widget = new $classname($widgetId,-1,$pageId);
	if(!($widget instanceof widgetFramework))
	{
		displayerror("The widget {$row['name']} doesn't extends widgetFramework class");
		return false;
	}
	
	if(!$widget->validInstall())
	{
		if(!$widget->installWidget())
		{
			displayerror("{$row['name']} widget is not installed properly.");
			return false;
		}
		$widget->loadWidget();
	}
	
	$widgetLocation;
	$widgetOrder;
	
	///Now create the actual instance with a unique instance Id.
	$success=$widget->createWidget($pageId,$widgetLocation,$widgetOrder);

	if(!$success)
		displayinfo("{$row['name']} widget instance successfully created at location $widgetLocation with ordering $widgetOrder. You can now change its configurations and location from <a href='./+widgets#enabledwidgets'>here</a>.");
	else displayerror("An error occurred while creating an instance of this widget.");
	
	return $success;
		
}
/**
 * Returns an array of enabled widgets and related information
 * @note The order will be sorted by widget_location, then widget_order, to sync with the way it appears in the template.
 * @param $pageId The page id for which to get the enabled widgets list
 * @return Array of enabled widgets and their names, description, order and location.
 */
function getEnabledWidgets($pageId)
{
	$query="SELECT t1.`widget_id` AS 'id', t1.`widget_instanceid` AS 'instanceid', t1.`widget_location` AS 'location', t1.`widget_order` AS 'order', t1.`widget_propagate` AS 'propagate', t2.`widget_name` AS 'name', t2.`widget_description` AS 'description', t2.`widget_author` AS 'author', t2.`widget_version` AS 'version', t2.`widget_classname` AS 'classname', t2.`widget_foldername` AS 'foldername' FROM `".MYSQL_DATABASE_PREFIX."widgets` AS t1, `".MYSQL_DATABASE_PREFIX."widgetsinfo` AS t2 WHERE t1.`page_id`='$pageId' AND t2.`widget_id`=t1.`widget_id` ORDER BY t1.`widget_location`, t1.`widget_order` ASC";
	$result=mysql_query($query);
	$return=array();
	while($row=mysql_fetch_array($result))
		$return[]=$row;
	return $return;
}



function getWidgetName($actualPath) {
	$actualPath = substr($actualPath,0,-1);
	return substr(strrchr($actualPath,"/"),1);
}

/**
 * Handles the global widget administration interface.
 * @param $pageId Id of the current page
 * @return HTML code of the widget admin page
 */
function handleWidgetAdmin($pageId)
{
	global $ICONS,$urlRequestRoot,$cmsFolder,$moduleFolder,$sourceFolder,$widgetFolder;
	$html = "";
	if(isset($_GET['subsubaction'])) {
		if($_GET['subsubaction']=="installwidget") {
			require_once("$sourceFolder/module.lib.php");
			$uploadId = processUploaded("Widget");
			if($uploadId != -1) {
				$ret = installModule($uploadId,"Widget");
				if($ret != "")
					return $ret;
			}
		} 
	}
	if(isset($_GET["deletewidget"])) {
		$widgetId = escape($_GET['deletewidget']);
		if(is_numeric($widgetId)) {
			$widget = mysql_fetch_assoc(mysql_query("SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "widgetsinfo` WHERE `widget_id` = '{$widgetId}'"));
			$error = false;
			$deletelist = array("widgets","widgetsinfo","widgetsconfiginfo","widgetsconfig","widgetsdata");
			$rowCount = 0;
			foreach($deletelist as $deleteitem) {
				$query = "DELETE FROM `" . MYSQL_DATABASE_PREFIX . $deleteitem . "` WHERE `widget_id` = '{$widgetId}'";
				mysql_query($query) or die($query . "<br><br>" . mysql_error());
				
				$ans = mysql_fetch_row(mysql_query("SELECT COUNT(*) FROM `" . MYSQL_DATABASE_PREFIX . $deleteitem . "` WHERE `widget_id` = '{$widgetId}'"));
				$rowCount += $ans[0];
			}
			if(is_dir("$sourceFolder/$widgetFolder/{$widget['widget_foldername']}"))
				if(!delDir("$sourceFolder/$widgetFolder/{$widget['widget_foldername']}"))
					$error = true;
			if($rowCount!=0||$error)
				displayerror("There was some error in deleting widget {$widget['widget_name']}");
			else
				displayinfo("{$widget['widget_name']} successfully deleted.");
		}
	}

	
	if(isset($_GET['widgetid']))
	{
		$widgetid=escape($_GET['widgetid']);		
		
		$query="SELECT `widget_name` AS 'name', `widget_classname` AS 'classname', `widget_foldername` AS 'foldername' FROM `".MYSQL_DATABASE_PREFIX."widgetsinfo` WHERE `widget_id`='$widgetid'";
		$res=mysql_query($query);
		if(mysql_num_rows($res)==0)
		{
			displayerror("Required widget is not registered with Pragyan CMS properly.");
			return false;
		}
		$row=mysql_fetch_array($res);
	
		global $widgetFolder;
		$classname=$row['classname'];
		require_once("$widgetFolder/{$row['foldername']}/widget.class.php");
	
		///Initializing as global instance.
		$widget = new $classname($widgetid,-1,$pageId);
		if(!($widget instanceof widgetFramework))
		{
			displayerror("The widget {$row['name']} doesn't extends widgetFramework class");
			return false;
		}
	
		if(!$widget->validInstall())
		{
			if(!$widget->installWidget())
			{
				displayerror("{$row['name']} widget is not installed properly.");
				return false;
			}
			$widget->loadWidget();
		}

		/// POST variables are processed inside this function
		if(isset($_GET['subsubaction']) && $_GET['subsubaction']=="globalconf")
			updateWidgetConf($widgetid,-1,TRUE); 
			
		$widgetinfo=getWidgetInfo($widgetid);
		$widgetglobalconfigs=getWidgetGlobalConfigInfo($widgetid);
		
	
		$containsFileUploadFields = false;
		$formElements=getConfigFormAsArray($widgetglobalconfigs,$containsFileUploadFields,-1,TRUE);
		
		$jsPath = "$urlRequestRoot/$cmsFolder/templates/common/scripts/formValidator.js";//validation.js
		$calpath = "$urlRequestRoot/$cmsFolder/$moduleFolder/form/calendar";
		$jsPathMooTools = "$urlRequestRoot/$cmsFolder/templates/common/scripts/mootools-1.11-allCompressed.js";
		
		$html = '<link rel="stylesheet" type="text/css" media="all" href="'.$calpath.'/calendar.css" title="Aqua" />' .
						 '<script type="text/javascript" src="'.$calpath.'/calendar.js"></script>';
		$html .= '<div class="registrationform"><form class="fValidator-form" name="widgetglobalsettings" action="./+admin&subaction=widgets&subsubaction=globalconf&widgetid='.$widgetid.'" method="post"';
		
		if($containsFileUploadFields)
			$html .= ' enctype="multipart/form-data"';
		$html .= '>';
		
		$html.="<table width=100%><tr><th colspan=2>Widget : {$widgetinfo['name']}</th></tr>";
		$html.="<tr><td>Description : </td><td> {$widgetinfo['description']}</td></tr>";
		
		//Uncomment when support for retrieving instances is there
		/*$html.="<tr><td>Instances : </td><td>";
		$instances=getWidgetInstances($widgetid);
		if(count($instances)>0) $html.="<ol>";
		else $html.="None"; 
		foreach($instances as $instance)
		{
			$html.="<li><a href='$urlRequestRoot/{$instance['url']}/+settings&subaction=widgets'>".
					"{$instance['name']} [{instance['url']}]</li>";
		}
		if(count($instances)>0) $html.="</ol>";
		$html.="</td></tr>";
		*/
		
		$html .="<tr>".join($formElements, "</tr>\n<tr>")."</tr>";
		
		$html.="</table><input name='update_global_settings' type='submit' value='Update'/>" .
				"<input type='reset' value='Reset'/>";
		$html.="</form><br/>";
		
	
	}
	
	
	$widgetsarr=getAllWidgetsInfo();
	
	
	$html .= "<fieldset><legend>{$ICONS['Widgets']['small']}Available Widgets</legend>";
	
	$html .= "<table width=100%><tr><th colspan=4>Available Widgets<br/><i>Mouse over for description and Click for configuration</i></th></tr>
	<tr><th>Name</th><th>Version</th><th>Author</th><th>Actions</th></tr>";
	foreach( $widgetsarr as $widget )
	{
		$html.="<tr><td><a title='".$widget['description']."' href='./+admin&subaction=widgets&widgetid=".$widget['id']."'>".$widget['name']."</a></td><td>{$widget['version']}</td><td>{$widget['author']}</td><td><a href='./+admin&subaction=widgets&widgetid={$widget['id']}'>{$ICONS['Edit']['small']}</a><a href='./+admin&subaction=widgets&deletewidget={$widget['id']}'>{$ICONS['Delete']['small']}</a></td></tr>";
	}
	$html .= <<<HTML
<tr><td>Install new widget:</td><td colspan=3>
<form method='POST' action='./+admin&subaction=widgets&subsubaction=installwidget' enctype="multipart/form-data">
<input type='file' name='file' id='file'><input type='submit' name='btn_install' value='Upload'>
</form>
</td></tr></table></fieldset>
HTML;
	return $html;
}
/**
 * Retrieves the configurations in the form of an HTML with all the fields and types appropriately put into place. Can be used for retrieveing both
 * global and instance-specific configurations.
 * @param $widgetconfigs Contains the array of configuration settings
 * @param $containsFileUploadFields Must be set to true if the configuration form has file upload fields
 * @param $widgetinstanceid Instance ID of the widget for which to get the instance-specific configurations, for global settings must be -1.
 * @param $isglobal Must be set to true if handling global settings, else false.
 * @return see description.
 */
function getConfigFormAsArray($widgetconfigs,$containsFileUploadFields,$widgetinstanceid,$isglobal)
{
		$containsFileUploadFields = false;
		$formValues = array();
	
		$formElements = array();
		$confnames = array();
		
		if($isglobal) $widgetinstanceid=-1;
		
		/// Initially load the default global configurations in the $formValues array
		foreach($widgetconfigs as $configentry)
		{
			$confnames[]=$configentry['confname'];
			$formValues[$configentry['confname']]=$configentry['confdefault'];
		}
			
		$query="SELECT `config_name` AS 'confname', `config_value` AS 'confvalue' FROM ".MYSQL_DATABASE_PREFIX."widgetsconfig WHERE `widget_instanceid`='$widgetinstanceid' AND `widget_id`='{$widgetconfigs[0]['id']}'  AND `config_name` IN ('".join($confnames,"','")."')";

		$res=mysql_query($query);

		/// For those configurations which are set, overwrite the $formValues array
		while($row=mysql_fetch_assoc($res))
		{
			$formValues[$row['confname']]=$row['confvalue'];	
		}
		
		$jsValidationFunctions = array();
		
		foreach( $widgetconfigs as $configentry )
		{
			$jsOutput = '';
			if($configentry['conftype'] == 'file') {
				$containsFileUploadFields = true;
			}
		
			$formElements[] =	getFormInputField
						(
							$configentry,
							isset($formValues[$configentry['confname']]) ? $formValues[$configentry['confname']] : '',
							$isglobal
						);
			if($jsOutput != '') {
				$jsValidationFunctions[] = $jsOutput;
			}
		}

		return $formElements;
}

/**
 * Retrieves the HTML code of a particular form element type.
 * @param $configentry Contains the informations about a particular configuration
 * @param $value Current value of that configuration (not default)
 * @param $isglobal Must be set to true if handling global settings, else false.
 * @return The HTML field of that configuration along with the javascript Validation function (if any).
 */
function getFormInputField($configentry, $value="", $isglobal) {


	$elementType = $configentry['conftype'];
	
	if($elementType == 'noinput')
		$htmlOutput = '<td colspan=2>';
	else
		$htmlOutput = '<td>';
		
	$htmlOutput .=  $configentry['confdisplay'];

	
	$elementTypeOptions = $configentry['confoptions'];
	
	$options = explode('|', $elementTypeOptions);
	/// To make sure that any special HTML character is converted into equivalent HTML code
	$options=array_map("htmlentities",$options);
	
	$value = htmlentities($value);
	
	if($isglobal) $formname='globalconfform';
	else $formname='pageconfform';
	
	$elementName = "{$formname}_" .  $configentry['confname'];
	
	if($elementType != 'noinput')
		$htmlOutput .='</td><td>';
	
	$functionName = "render".ucfirst(strtolower($elementType))."TypeField";
	
	
	if($functionName($elementName,$value,$options,$htmlOutput)==false)
		displayerror("Unable to run function ".$functionName);

	return $htmlOutput . "</td>\n";
}
/**
 * Renders the Text-Area type element
 * @param $elementName	The name of the field
 * @param $value The current value
 * @param $options The extra options related to this field type
 * @param $htmlOutput Will contain the actual HTML Output
 * @return true if succesful, or false if rendering failed.
 */
function renderTextareaTypeField($elementName,$value,$options,&$htmlOutput)
{
			$rows = 5;
			$cols = 20;
			$htmlOutput .= '<textarea style="width:100%" rows="'.$rows.'" cols="'.$cols.'"  name="'.$elementName.'" id="'.$elementName.'" />'.$value.'</textarea>';
			return true;
}
/**
 * Renders the Select type element
 * @param $elementName	The name of the field
 * @param $value The current value
 * @param $options The extra options related to this field type
 * @param $htmlOutput Will contain the actual HTML Output
 * @return true if succesful, or false if rendering failed.
 */
function renderSelectTypeField($elementName,$value,$options,&$htmlOutput)
{

	
	$optionsHtml = '';

	for($i = 0; $i < count($options); $i++) {
		if($options[$i] == $value) {
			$optionsHtml .= '<option value="'.$options[$i].'" selected="selected" >' . $options[$i] . "</option>\n";
		}
		else {
			$optionsHtml .= '<option value="'.$options[$i].'" >' . $options[$i] . "</option>\n";
		}
	}

	$htmlOutput .= '<select name="'.$elementName.'" id="'.$elementName.'">' . $optionsHtml . '</select>';
	return true;
}
/**
 * Renders the Radio type element
 * @param $elementName	The name of the field
 * @param $value The current value
 * @param $options The extra options related to this field type
 * @param $htmlOutput Will contain the actual HTML Output
 * @return true if succesful, or false if rendering failed.
 */
function renderRadioTypeField($elementName,$value,$options,&$htmlOutput)
{

	
	
	$optionsHtml = '';

	for($i = 0; $i < count($options); $i++) {
		$optionsHtml .= '<label><input type="radio" id="'.$elementName.'" name="'.$elementName.'" value="'.
										$options[$i].'"';

		if($options[$i] == $value) {
			$optionsHtml .= ' checked="checked"';
		}

		$optionsHtml .= '/>'.$options[$i].'</label>&nbsp;&nbsp;';
	}

	$htmlOutput .= $optionsHtml;
	return true;
}
/**
 * Renders the Bool type element
 * @param $elementName	The name of the field
 * @param $value The current value
 * @param $options The extra options related to this field type
 * @param $htmlOutput Will contain the actual HTML Output
 * @return true if succesful, or false if rendering failed.
 */
function renderBoolTypeField($elementName,$value,$options,&$htmlOutput)
{

	
	$options = array("Yes","No");

	$value = ($value=='1'||$value=='Yes')?"Yes":"No";
	$optionsHtml = '';

	for($i = 0; $i < count($options); $i++) {
		$optionsHtml .= '<label><input type="radio" id="'.$elementName.'" name="'.$elementName.'" value="'.
										$options[$i].'"';

		if($options[$i] == $value) {
			$optionsHtml .= ' checked="checked"';
		}

		$optionsHtml .= '/>'.$options[$i].'</label>&nbsp;&nbsp;';
	}

	$htmlOutput .= $optionsHtml;
	return true;
}
/**
 * Renders the Checkbox type element
 * @param $elementName	The name of the field
 * @param $value The current value
 * @param $options The extra options related to this field type
 * @param $htmlOutput Will contain the actual HTML Output
 * @return true if succesful, or false if rendering failed.
 */
function renderCheckboxTypeField($elementName,$value,$options,&$htmlOutput)
{

	$optionsHtml = '';
	$values=explode("|",$value);
	for($i = 0; $i < count($options); $i++) {
		$optionsHtml .= '<label><input type="checkbox" id="'.$elementName.'_'.$i.'" name="'.$elementName.'_'.$i.'" value="'.
										$options[$i].'"';

		if(array_search($options[$i],$values)!==FALSE) {
			$optionsHtml .= ' checked="checked"';
		}

		$optionsHtml .= $validCheck.' />'.$options[$i].'</label>&nbsp;&nbsp;';
	}

	$htmlOutput .= $optionsHtml;
	return true;
}
/**
 * Renders the File type element
 * @param $elementName	The name of the field
 * @param $value The current value
 * @param $options The extra options related to this field type
 * @param $htmlOutput Will contain the actual HTML Output
 * @see getFileUploadField
 * @return true if succesful, or false if rendering failed.
 */
function renderFileTypeField($elementName,$value,$options,&$htmlOutput)
{

	global $sourceFolder;
	require_once("$sourceFolder/upload.lib.php");
	
	///Used to maintain uniformity in upload fields in the CMS
	$htmlOutput .= getFileUploadField($elementName,"", 2*1024*1024, ""); 

	
	if($value != '') {
		$htmlOutput .= '<br />(Leave blank to keep current file : <a href="./' . $value . '">'.$value.'</a>)';
	}

	return true;
}
/**
 * Renders the Text type element
 * @param $elementName	The name of the field
 * @param $value The current value
 * @param $options The extra options related to this field type
 * @param $htmlOutput Will contain the actual HTML Output
 * @return true if succesful, or false if rendering failed.
 */
function renderTextTypeField($elementName,$value,$options,&$htmlOutput)
{
	
	$htmlOutput .= '<input type="text" name="'.$elementName.'" id="'.$elementName.'" value="'.$value.'"  />';
								
	return true;
}
/**
 * Renders the Integer type element
 * @param $elementName	The name of the field
 * @param $value The current value
 * @param $options The extra options related to this field type
 * @param $htmlOutput Will contain the actual HTML Output
 * @return true if succesful, or false if rendering failed.
 */
function renderIntegerTypeField($elementName,$value,$options,&$htmlOutput)
{
	
	$htmlOutput .= '<input type="text" class="numeric" name="'.$elementName.'" id="'.$elementName.'" value="'.$value.'"  />';
								
	return true;
}
/**
 * Renders the Hidden type element
 * @param $elementName	The name of the field
 * @param $value The current value
 * @param $options The extra options related to this field type
 * @param $htmlOutput Will contain the actual HTML Output
 * @return true if succesful, or false if rendering failed.
 */
function renderHiddenTypeField($elementName,$value,$options,&$htmlOutput)
{
	
	$htmlOutput .= '<input type="hidden" name="'.$elementName.'" id="'.$elementName.'" value="'.$value.'"  />';
								
	return true;
}
/**
 * Renders the Datetime type element
 * @param $elementName	The name of the field
 * @param $value The current value
 * @param $options The extra options related to this field type
 * @param $htmlOutput Will contain the actual HTML Output
 * @return true if succesful, or false if rendering failed.
 */
function renderDatetimeTypeField($elementName,$value,$options,&$htmlOutput)
{
	
	$datetimeFormat = "'%Y-%m-%d %H:%M'";

	$validCheck = ' dateformat="YY-MM-DD hh:mm" ';


	$htmlOutput .= '<input type="text" '. $validCheck . ' name="'.$elementName.'" value="' . $value . '" id="'.$elementName.'" /><input name="cal'.$elementName.'" type="reset" value=" ... " onclick="return showCalendar(\'' . $elementName . '\', '.$datetimeFormat.', \'24\', true);" />';
	return true;
}
/**
 * Renders the Date type element
 * @param $elementName	The name of the field
 * @param $value The current value
 * @param $options The extra options related to this field type
 * @param $htmlOutput Will contain the actual HTML Output
 * @return true if succesful, or false if rendering failed.
 */
function renderDateTypeField($elementName,$value,$options,&$htmlOutput)
{
	
	$datetimeFormat = "'%Y-%m-%d'" ;

	$validCheck .= ' dateformat="YY-MM-DD" ';


	$htmlOutput .= '<input type="text" '. $validCheck . ' name="'.$elementName.'" value="' . $value . '" id="'.$elementName.'" /><input name="cal'.$elementName.'" type="reset" value=" ... " onclick="return showCalendar(\'' . $elementName . '\', '.$datetimeFormat.', \'24\', true);" />';
	return true;
}

/**
 * Renders the Noinput type element. Will only display some text, and not take any input.
 * @param $elementName	The name of the field
 * @param $value The current value
 * @param $options The extra options related to this field type
 * @param $htmlOutput Will contain the actual HTML Output
 * @return true if succesful, or false if rendering failed.
 */
function renderNoinputTypeField($elementName,$value,$options,&$htmlOutput)
{
	return true;
}

/**
 * Gets the widget information and instance-specific configuration settings about a particular widget
 * @param $widgetid Id of the widget type
 * @return Configuration settings, only instance-specific and not global.
 */
function getWidgetPageConfigInfo($widgetid)
{
	$query="SELECT `widget_id` AS 'id', `config_name` AS 'confname', `config_displaytext` AS 'confdisplay', `config_type` AS 'conftype',`config_options` AS 'confoptions',`config_default` AS 'confdefault'  FROM `".MYSQL_DATABASE_PREFIX."widgetsconfiginfo` WHERE `widget_id`='$widgetid' AND `is_global`=0 ORDER BY `config_rank`";
	$res=mysql_query($query);
	$ret=array();
	while($arr=mysql_fetch_assoc($res))
	{
		$ret[]=$arr;
	}
	return $ret;
}

/**
 * Gets the widget information and global configuration settings about a particular widget
 * @param $widgetid Id of the widget type
 * @return Configuration settings, only global.
 */
function getWidgetGlobalConfigInfo($widgetid)
{
	$query="SELECT `widget_id` AS 'id', `config_name` AS 'confname', `config_displaytext` AS 'confdisplay', `config_type` AS 'conftype',`config_options` AS 'confoptions',`config_default` AS 'confdefault'  FROM `".MYSQL_DATABASE_PREFIX."widgetsconfiginfo` WHERE `widget_id`='$widgetid' AND `is_global`=1 ORDER BY `config_rank`";
	$res=mysql_query($query);
	$ret=array();
	while($arr=mysql_fetch_assoc($res))
	{
		$ret[]=$arr;
	}
	return $ret;
}
/**
 * Gets the widget information and global configuration settings about a particular widget
 * @param $widgetid Id of the widget type
 * @return widget information like name, description, version, authorname and widget folder name.
 */
function getWidgetInfo($widgetid)
{
	$query="SELECT `widget_id` AS 'id', `widget_name` AS 'name', `widget_description` AS 'description', `widget_version` AS 'version', `widget_author` AS 'author', `widget_foldername` AS 'foldername' FROM `".MYSQL_DATABASE_PREFIX."widgetsinfo` WHERE `widget_id`='$widgetid'";
	$res=mysql_query($query);
	return mysql_fetch_assoc($res);
}
/**
 * Retrieves the widget id and name of all the widgets
 * @return An associative 2D array containing the widget id, name, description, version and authorname.
 */
function getAllWidgetsInfo()
{
	$query="SELECT `widget_id` AS 'id',`widget_name` AS 'name',`widget_description` AS 'description', `widget_version` AS 'version', `widget_author` AS 'author' FROM `".MYSQL_DATABASE_PREFIX."widgetsinfo`";
	$res=mysql_query($query);
	$ret=array();
	while($row=mysql_fetch_array($res))
	{
		$ret[]=$row;
	}
	return $ret;
}
/**
 * Handles the submission of the widget configuration forms (both global and instance-specific) and updates the database.
 * @param $widgetid ID of the widget.
 * @param $widgetinstanceid Widget Instance ID of the widget for instance-specific configurations, default is -1 for global configurations.
 * @param $isglobal Default is set to true if handling global configurations, for instance-specific configurations must be set to false explicitly.
 * @note It uses $_POST variables implicitly to retrieve submitted form values.
 */
function updateWidgetConf($widgetid,$widgetinstanceid=-1,$isglobal=TRUE)
{
	$query="SELECT `config_name`,`config_type`,`config_default`,`config_options` FROM `".MYSQL_DATABASE_PREFIX."widgetsconfiginfo` WHERE `widget_id`='$widgetid' AND `is_global`=".(int)$isglobal;
	
	$res=mysql_query($query);
	
	if($isglobal) $widgetinstanceid=-1;
	
	while($row=mysql_fetch_array($res))
	{
	
		$conftype=$row['config_type'];
		$confname=$row['config_name'];
		$confdef=$row['config_default'];
		$confoptions=$row['config_options'];
		if($isglobal)
			$postvar="globalconfform_".$confname;
		else $postvar="pageconfform_".$confname;
		
		$confcur=false;
		
		$query="SELECT `config_value` FROM `".MYSQL_DATABASE_PREFIX."widgetsconfig` WHERE `config_name`='$confname' AND `widget_id`='$widgetid' AND `widget_instanceid`='$widgetinstanceid'";
	
		$result=mysql_query($query);
		
		while($row=mysql_fetch_assoc($result))
			$confcur=$row['config_value'];
		
		if($conftype=='checkbox')
			$confval=escape(interpretSubmitValue($conftype,$postvar,$confoptions));
		else	
			$confval=escape(interpretSubmitValue($conftype,$postvar));
		
		///If there was no submit value, then check for the current value, if even that's missing then use the default value	
		$confval=($confval===false)?(($confcur===false)?$confdef:$confcur):$confval;
		if(mysql_num_rows($result)==0)
		{
			$query="INSERT INTO `".MYSQL_DATABASE_PREFIX."widgetsconfig` (`widget_id`,`widget_instanceid`,`config_name`,`config_value`) VALUES ($widgetid,$widgetinstanceid,'$confname','$confval')";
			mysql_query($query);
		}	
		else if($confval!=$confcur)
		{
			$query="UPDATE `".MYSQL_DATABASE_PREFIX."widgetsconfig` SET `config_value`='$confval' WHERE `config_name`='$confname' AND `widget_id`='$widgetid' AND `widget_instanceid`='$widgetinstanceid'";
			mysql_query($query);
		}
	
	}
	displayinfo("Configurations updated successfully!");
	
}

/**
 * Interprets the submit values of individual field types in the configuration form
 * @param $conftype The type of the input field
 * @param $postvar The POST variable name
 * @param $options The extra options like for checkbox
 * @return The value in string format if successful, else returns boolean false.
 */
function interpretSubmitValue($conftype,$postvar,$options=NULL)
{
	if($conftype=='textarea')
	{
		return $_POST[$postvar];
	}
	else if($conftype=='select')
	{
		return isset($_POST[$postvar])?$_POST[$postvar]:false;
	}
	else if($conftype=='radio')
	{
		return isset($_POST[$postvar])?$_POST[$postvar]:false;
	}
	else if($conftype=='bool')
	{
		return isset($_POST[$postvar])?$_POST[$postvar]:false;
	}
	else if($conftype=='checkbox')
	{
		$optionvals = explode("|",$options);
		$i=-1;
		$values = array();
		foreach($optionvals as $value) {
			$i++;
			if(!isset($_POST[$postvar."_".$i]))
				continue;
			$values[] = $value;
		}
		$valuesString = join($values,"|");
		return $valuesString;
	}
	else if($conftype=='text')
	{
		return $_POST[$postvar];
	}
	else if($conftype=='integer')
	{
		return (isset($_POST[$postvar])&&is_numeric($_POST[$postvar]))?$_POST[$postvar]:false;
	}
	else if($conftype=='hidden')
	{
		return $_POST[$postvar];
	}
	else if($conftype=='datetime')
	{
		return isset($_POST[$postvar])?$_POST[$postvar]:false;
	}
	else if($conftype=='date')
	{
		return isset($_POST[$postvar])?$_POST[$postvar]:false;	
	}	
	else if($conftype=='noinput')
	{
		return NULL;
	}
	return false;
	
}
/**
 * Reloads the widgets from the widget directoty and update proper entries in database.
 * @param
 * @return
 */
function reloadWidgets()
{
	// Load the widgets from widget/ directory and update proper entries in database. Should be there in admin/site-maintenaince
}
function getWidgetInstances($widgetid)
{
	return array();
}
 
?>
