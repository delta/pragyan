<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}
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
	public $widgetDescription; ///<Description of the widget
	public $widgetVersion; ///<Version of the widget
	public $widgetAuthor; ///<Author of the widget
	public $widgetId; ///<Id of the widget
	public $widgetInstanceId; ///<Instance Id of the widget
	public $widgetPageId; ///<Page Id of the page this instance is located on
	public $widgetLocation; ///<Location of the widget on the page 
	public $widgetOrder; ///<Order of the widget, in case when multiple widgets are on the same location
	public $settings; ///<Instance-Specific Settings of the widget in key=>value format
	public $data; ///<A sort of RAM or heap store for the widget in key=>value format
	public $defaultConfigs; ///<Default configurations of the widget
	
	
	/// For widget initialization.
	abstract public function initWidget();
	
	/// For retrieving the widget output.
	abstract public function getHTML();
	
	/// For retrieving the widget output which is common for all instances of same widget. Not used always but must be defined.
	abstract public function getCommonHTML();
	
	/**
	 * Constructor.
	 *
	 * @param $widgetId Widget ID of the widget
	 * @param $widgetName Widget Name of the widget
	 * @return instance of the class widgetFramework
	 */
	public function __construct($widgetId,$widgetInstanceId,$pageId,$defaultconfigs)
	{
		$this->widgetId=$widgetId;
		$this->widgetInstanceId=$widgetInstanceId;
		$this->pageId=$pageId;
		$this->defaultConfigs=$defaultconfigs;
		$this->loadWidget();
		
		
	} 
	
	/**
	 * Loads the widget information, settings and data from the database if the widget already exists.
	 * @param $widgetInstanceId widget instance id of the existing widget
	 * @param $pageId page Id of the page in which widget exists
	 * @return Boolean True on success, else false.
	 */
	public final function loadWidget()
	{
	
		///Loading widget information
		$query="SELECT `widget_name` AS 'name', `widget_description` AS 'description', `widget_version` AS 'version', `widget_author` AS 'author' FROM `".MYSQL_DATABASE_PREFIX."widgetsinfo` WHERE `widget_id`='{$this->widgetId}'";
		$res=mysql_query($query);
		if($res===false || mysql_num_rows($res)==0)
		{
			displayerror("Error in loading widget {$this->widgetName}");
			return false;
		}
		$row=mysql_fetch_array($res);
		
		$this->widgetName=$row['name'];
		$this->widgetDescription=$row['description'];
		$this->widgetVersion=$row['version'];
		$this->widgetAuthor=$row['author'];
		
		///Loading configuration settings (both instance-specific and global)
		$query="SELECT `config_name` AS 'key', `config_value` AS 'value' FROM `".MYSQL_DATABASE_PREFIX."widgetsconfig` WHERE `widget_id`='{$this->widgetId}' AND `widget_instanceid` IN ({$this->widgetInstanceId},-1) ";
		$res=mysql_query($query);
		
		if($res===false)
		{
			displayerror("Error in loading widget {$this->widgetName}");
			return false;
		}
		$this->settings = array();
		while($row=mysql_fetch_array($res))
		{
			$this->settings[$row['key']]=$row['value'];
		}
		
		///If configurations doesn't exists, then loading default values.
		$query="SELECT `config_name` AS 'key', `config_default` AS 'value', `is_global` AS 'global' FROM `".MYSQL_DATABASE_PREFIX."widgetsconfiginfo` WHERE `widget_id`='{$this->widgetId}'";
		
		$res=mysql_query($query);
		if($res===false)
		{
			displayerror("Error in loading widget {$this->widgetName}");
			return false;
		}
		
		while($row=mysql_fetch_array($res))
		{
			if(!isset($this->settings[$row['key']]))
			{
				$this->settings[$row['key']]=$row['value'];
				
				///Only add to database if the current instance type and the configuration type matches i.e. global or instance-specific.
				if(($row['global']=='1' && $this->widgetInstanceId==-1) || ($row['global']=='0' && $this->widgetInstanceId!=-1))
				{
					$query="INSERT IGNORE INTO `".MYSQL_DATABASE_PREFIX."widgetsconfig` (`widget_id`,`widget_instanceid`,`config_name`,`config_value`) VALUES ('{$this->widgetId}', '{$this->widgetInstanceId}', '{$row['key']}', '{$row['value']}')";
					$res2=mysql_query($query);
					if($res2===false)
					{
						displayerror("Error in loading widget {$this->widgetName}");
						return false;
					}
				}
			}
		}
	
		///Loading data settigns
		$query="SELECT `widget_datakey` AS 'key', `widget_datavalue` AS 'value' FROM `".MYSQL_DATABASE_PREFIX."widgetsdata` WHERE `widget_id`='{$this->widgetId}' AND`widget_instanceid` = '{$this->widgetInstanceId}'";
		$res=mysql_query($query);
		if($res===false)
		{
			displayerror("Error in loading widget {$this->widgetName}");
			return false;
		}
		$this->data = array();
		while($row=mysql_fetch_array($res))
		{
			$this->data[$row['key']]=$row['value'];
		}
		
		return true;
		
	}
	
	/**
	 * Checks whether the widget has been installed properly. It checks the install flag in data. 
	 * If its 0, it assumes the widget needs installation.
	 * @note It will only work if widgetInstanceId = -1 (i.e. global) since we need not check installation of every instance.
	 * @return Boolean True if widget is properly installed, else False.
	 */
	function validInstall()
	{
		if(!isset($this->data['pragyan_widget_install']) || isset($this->data['pragyan_widget_install'])==0)
			return false;
		return true;
	}
	
	/**
	 * Installs the widget properly by storing its configuration in the configsinfo table and updating the install flag in data.
	 * @return Boolean True if successful, else False.
	 */
	function installWidget()
	{
		
		///If some configuration fields are already there in table, we remove them.
		$query = "DELETE FROM `".MYSQL_DATABASE_PREFIX."widgetsconfiginfo` WHERE `widget_id`='{$this->widgetId}'";
		mysql_query($query);
		
		
		$install=$this->setConfigs($this->defaultConfigs);
		if($install==true)
		{
			displayinfo("{$this->widgetName} widget successfully installed!");
			return $this->saveData('pragyan_widget_install','1');
		}
		return false;
	}
	
	/**
	 * Sets the widget settings by creating the settings in the database. Only needed during installation.
	 * @param $configs Configuration for the widget.
	 * @return true on success, else false.
	 */
	public final function setConfigs($configs)
	{
		$rank=0;
		foreach($configs as $config)
		{
			$config['global']=(int)$config['global'];
			
			foreach($config as $key=>$value) $config[$key]=escape($value);
			
			$query="INSERT IGNORE INTO `".MYSQL_DATABASE_PREFIX."widgetsconfiginfo` (`widget_id`,`config_name`,`config_type`,`config_options`,`config_displaytext`,`config_default`,`is_global`,`config_rank`) VALUES ({$this->widgetId},'{$config['name']}','{$config['type']}','{$config['options']}','{$config['displaytext']}','{$config['default']}',{$config['global']},{$rank})";
			
			$rank++;
			if(mysql_query($query)==false)
			{
				displayerror("Error in saving configurations for the widget {$this->widgetName}");
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Saves a particular widget configuration in the database.
	 * @param $key The setting name which is to be saved.
	 * @param $value The value of the setting.
	 * @return Boolean True if successful, else False.
	 */
	public final function saveSetting($key,$value)
	{
		$value = escape($value);
		$key = escape($key);
		
		$query="UPDATE `".MYSQL_DATABASE_PREFIX."widgetsconfig` SET `config_value`='$value' WHERE `config_name`='$key' AND `widget_id`='{$this->widgetId}' AND `widget_instanceid`='{$this->widgetInstanceId}'";

		if(mysql_query($query)===false) {
			displayerror("Error in saving setting for the widget {$this->widgetName}.");
			return false; 
		}
		return true;
		
	}
	
	/**
	 * Saves a particular widget data in the database.
	 * @param $key The data name which is to be saved.
	 * @param $value The data value.
	 * @return Boolean True if successful, else False.
	 */
	public final function saveData($key,$value)
	{
		$value = escape($value);
		$key = escape($key);
		
		if(isset($this->data[$key]))
			$query="UPDATE `".MYSQL_DATABASE_PREFIX."widgetsdata` SET `widget_datavalue`='$value' WHERE `widget_datakey`='$key' AND `widget_id`={$this->widgetId} AND `widget_instanceid`={$this->widgetInstanceId}";
		else 
			$query="INSERT INTO `".MYSQL_DATABASE_PREFIX."widgetsdata` (`widget_id`,`widget_instanceid`,`widget_datakey`,`widget_datavalue`) VALUES ('{$this->widgetId}','{$this->widgetInstanceId}','$key','$value')";
		
		if(mysql_query($query)===false)
		{
			displayerror("Error in saving data for the widget {$this->widgetName}.");
			return false;
		}
		return true;
	}
	
	/**
	 * Creates an instance of the widget on the given page Id by adding the appropriate fields in the database.
	 * Default configuration will be used initially.
	 * @param $pageID Page Id of the page on which to create the widget
	 * @param $widgetLocation Location ID of the widget on the page
	 * @param $widgetOrder Order of the widget on a location, when multiple widgets are there
	 * @return true, if succesfully created the widget else false.
	 * @uses $configs 
	 */
	public final function createWidget($pageId,&$widgetLocation,&$widgetOrder)
	{
		///Default location for the creation of the widget
		$defaultloc=1;
		
		$query="SELECT MAX(`widget_instanceid`) FROM `".MYSQL_DATABASE_PREFIX."widgets` WHERE `widget_id` = '{$this->widgetId}'";
		$result=mysql_query($query);
		if($result===false) return false;
		$row1=mysql_fetch_row($result);
		if($row1==NULL)
		 $row1[0]=0;
		
		$query="SELECT MAX(`widget_order`) FROM `".MYSQL_DATABASE_PREFIX."widgets` WHERE `page_id` = '$pageId' AND `widget_location` = '$defaultloc'";
		$result=mysql_query($query);
		if($result===false) return false;
		$row2=mysql_fetch_array($result);
		if($row2==NULL)
		 $row2[0]=0;
		
		$instanceId=$row1[0]+1; 
		$widgetOrder=$row2[0]+1;
		$widgetLocation=$defaultloc; 
		
		$query="INSERT INTO `".MYSQL_DATABASE_PREFIX."widgets` (`widget_id`,`widget_instanceid`,`page_id`,`widget_location`,`widget_order`) VALUES ('{$this->widgetId}','$instanceId','$pageId','$widgetLocation','$widgetOrder')";
		
		if(mysql_query($query)==false)
			return false;
		
	}
	
	
}	 
?>
