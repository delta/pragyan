<?php
/**
 * @package pragyan
 * @file widget.class.php
 * @brief Class file for the widget Server Date and Time.
 * 
 * @author Abhishek Shrivastava <i.abhi27[at]gmail.com>
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
global $sourceFolder;
require_once("$sourceFolder/widgetFramework.class.php");

class serverDateTime extends widgetFramework
{
	public $configs;
	public $timeformat;
	public $globaldisable;
	
	public function __construct($widgetId,$widgetInstanceId,$pageId)
	{
		$this->configs = array (
			array (
			'name' => 'time_format',
			'type' => 'select',
			'options' => '12 Hour|24 Hour',
			'displaytext' => 'Time display format',
			'default' => '12 Hour',
			'global' => 0
			),
			array (
			'name' => 'global_disable',
			'type' => 'bool',
			'displaytext' => 'Disable all clocks in the website',
			'default' => '0',
			'global' => 1
			)
		);
		parent::__construct($widgetId,$widgetInstanceId,$pageId,$this->configs);
		
	}
	
	
	public function initWidget()
	{
		$this->timeformat = $this->settings['time_format'];
		$this->globaldisable = $this->settings['global_disable'];
		
	}
	
	public function getHTML()
	{
		if($this->globaldisable=='1' || $this->globaldisable=='Yes') return "";
		
		if($this->timeformat == "12 Hour")
			return date("g:i:s a");
		else return date("H:i:s a");
	}	
}

?>
