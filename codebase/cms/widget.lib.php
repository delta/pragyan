<?php
/**
 * @package pragyan
 * @author Abhishek Shrivastava
 * @description Widget Framework for Pragyan CMS
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
 
 /* Idea :
 
 Put all the widgets inside cms/widgets folder. 
 Every widget should have a folder by its name inside cms/widgets.
 Each such folder will have widget.class.php file having a class which should extend WidgetFramework abstract class.
 The class will have abstract functions like :

 init_widget() to pass widget-instance-specific configurations to the widgets
 get_widget() to get the widget's output html code
 
 WidgetFramework will implement the following functions :
 
  __construct() to pass universal (static) parameters to the widgets
 create_widget() will create the entry of the widget in the database along with proper settings
 reset_widget() will reset all the widgets configurations to default (OPTIONAL)
 destroy_widget() will remove the widget from the database and its associated files
 
 
 A widget is a small customized html code that can be put in any part of the generated page. 
 The template's index.php will have variables $WIDGET1, $WIDGET2 to $WIDGETn where n is user-defined.
 Each $WIDGETi represents a unique-location and NOT a unique widget i.e. $WIDGETi can have multiple widgets, but in same location.
 
 Database Structure :
 
 PragyanV3_widgetsinfo table :
 *widget_id	widget_name	widget_description	widget_config 
 
 PragyanV3_widgetsconfiginfo table :
 *widget_id	*config_id	config_name	config_type	config_description	config_default
 
  
 PragyanV3_widgets table :
 *widget_id	*widget_instanceid	page_id		widget_location		widget_order
 
 PragyanV3_widgetsconfig table :
 *widget_id	*widget_instanceid	*config_id		config_data

 PragyanV3_widgetsglobalconfig table :
 *widget_id	*config_id 	config_name		config_value	config_description
 
 PragyanV3_widgetsdata
 widget_id	widget_instanceid	widget_datakey		widget_datavalue

 
 Algo ::
 
 When a page is being opened.
 
 For all widget_id and widget_instanceid from PragyanV3_widgets for page_id order by widget_location, widget_order
 	widget_globalconfigs = Get all global configurations from PragyanV3_widgetglobalconfig for widget_id
 	widget_configs = Get all configurations from PragyanV3_widgetsconfig for widget_id and widget_instanceid
 	widget_name = Select widget_name from PragyanV3_widgetsinfo for widget_id
 	Include file cms/widgets/widget_name/widget.class.php
 	widget_object = Object of class widget_name passing some universal widget_globalconfigs in the contructor
 	widget_object.init_widget(widget_configs)
 	widget_output = widget_object.get_widget()
 	$WIDGET<widget_location> .= widget_output
 
 Note : All the widgets will have the power to add/remove/modify their information in PragyanV3_widgetsdata table
 
 For administrators.
 
 1) Generate the list of all the widgets based on PragyanV3_widgetinfo table.
 2) When clicking on a widget, a page should be displayed with some widget informations like authorname from PragyanV3_widgetsinfo table
 3) The above page should also have links to change instance-specific and universal configurations for that widget.
 4) When clicked on instance-specific, a list of all the page_urls (dereferenced from page_id in PragyanV3_widgets table) is generated.
 5) Clicking on any url should open a form-type configuration page based upon PragyanV3_widgetsconfiginfo and PragyanV3_widgetsconfig.
 6) When clicked on universal configurations in (3), it should open a form-like page from PragyanV3_widgetglobalconfig
 7) Widget Installation and Removal (LATER)
 
*/

function handleWidgetMgmt($pageId)
{
	return "Under Construction :)";	
}
 
?>
