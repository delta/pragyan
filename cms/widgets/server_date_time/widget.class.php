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
	public $includes;
	public $timeformat;
	public $globaldisable;
	public $displaytext;
	
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
			'name' => 'display_text',
			'type' => 'text',
			'displaytext' => 'Display text with the time ( [%s] will be substituted with time )',
			'default' => 'Server time : [%s].',
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
		$this->displaytext = $this->settings['display_text'];
	}
	
	public function getCommonHTML()
	{
		return $this->includes;
	}
	
	public function getHTML()
	{
		if($this->globaldisable=='1' || $this->globaldisable=='Yes') return "";
		
		$finaltime = "";
		
		if($this->timeformat == "12 Hour")
			$finaltime = date("g:i:s a");
		else $finaltime = date("H:i:s a");
		
		$finaltext = preg_replace('/(.*)\[\%s\](.*)/','$1 '.$finaltime.'$2',$this->displaytext);
		return $finaltext;
	}	
}

?>
