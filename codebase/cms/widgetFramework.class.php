<?php
/**
 * @class widgetFramework
 * @author Abhishek Shrivastava <i.abhi27 [at] gmail.com> .
 * @brief An abstract class that every widget must extend.
 * It contains the implementation of some of the common methods like widget creation, deletion, etc. Other methods must be defined by the
 * derived class.
 * @uses widget.lib.php
 */
abstract class widgetFramework
{	

	public $widgetName; ///<Name of the widget
	public $widgetId; ///<Id of the widget
	public $widgetInstanceId; ///<Instance Id of the widget
	public $widgetPageId; ///<Page Id of the page this instance is located on
	public $widgetLocation; ///<Location of the widget on the page 
	public $widgetOrder; ///<Order of the widget, in case when multiple widgets are on the same location
	public $configs;
	
	/// For widget initialization.
	abstract public function initWidget();
	
	/// For retrieving the widget output.
	abstract public function getHTML();
	
	
	/**
	 * Constructor.
	 *
	 * @param $widgetId Widget ID of the widget
	 * @param $widgetName Widget Name of the widget
	 * @return instance of the class widgetFramework
	 */
	public function __construct($widgetId)
	{
		$this->widgetId=$widgetId;
		$configs
	} 
	
	/**
	 * Loads the widget settings from the database if the widget already exists.
	 *
	 * @param $widgetInstanceId widget instance id of the existing widget
	 * @param $pageId page Id of the page in which widget exists
	 * @return true on success, else false.
	 */
	public function loadWidget($widgetInstanceId,$pageId)
	{
		
	}
	
	/**
	 * Sets the configuration for the widget. Should be called before createWidget otherwise default configurations will be created.
	 *
	 * @param $configArray Stores the configurations in an array-style format
	 *
	 * @return true if succesfully sets the configurations, else false.
	 */
	public function setConfig($configArray)
	{
	
	}
	/**
	 * Creates an instance of the widget on the given page Id by adding the appropriate fields in the database.
	 * The configurations must be set before this method is called, otherwise default configuration will be used.
	 * @param $pageID Page Id of the page on which to create the widget
	 * @param $widgetLocation Location ID of the widget on the page
	 * @param $widgetOrder Order of the widget on a location, when multiple widgets are there
	 * @return true, if succesfully created the widget else false. 
	 */
	public function createWidget($pageId,$widgetLocation,$widgetOrder)
	{
		
	}
}	 
?>
