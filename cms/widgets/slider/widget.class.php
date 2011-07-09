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
 * @brief Class file for the widget News.
 * 
 * @author Balanivash <balanivash[at]gmail.com>
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * 
 * To do:
 * Add diff types of sliders
 * Options to have more than 1 image in the slider (that will come as a part of another slider)
 */
global $sourceFolder;
require_once("$sourceFolder/widgetFramework.class.php");

class slider extends widgetFramework
{
	public $configs;
	public $images;
	public $slidertype;
	public $width;
	public $height;
	public $speed;
	public $effect;
	public $gjs;
	public $makeunique;
	public $divid;
	public $divclass;
	public $ulclass;
	public $ulid;
	
	public function __construct($widgetId,$widgetInstanceId,$pageId)
	{
		$this->configs = array (
			array (
			'name' => 'images',
			'type' => 'textarea',
			'displaytext' => 'Image (Specify the images like image_text [image_link]) (Use | to separate two images)',
			'default' => 'Image_text [http://pragyan.org/home/a.jpg] | [http://pragyan.org/home/b.jpg]',
			'global' => 0
			),
			array (
			'name' => 'slidertype',
			'type' => 'select',
			'options' => 'None|Advanced Slider',
			'displaytext' => 'Select the slider type',
			'default' => 'None',
			'global' => 0
			),
			array (
			'name' => 'width',
			'type' => 'text',
			'displaytext' => 'Image Width',
			'default' => '',
			'global' => 0
			),
			array (
			'name' => 'height',
			'type' => 'text',
			'displaytext' => 'Image Height',
			'default' => '',
			'global' => 0
			),
			array (
			'name' => 'speed',
			'type' => 'text',
			'displaytext' => 'Speed of the slider',
			'default' => '3000',
			'global' => 0
			),
			array (
			'name' => 'effect',
			'type' => 'select',
			'options' => 'random|swirl|rain|straight',
			'displaytext' => 'Select the effect',
			'default' => 'random',
			'global' => 0
			),
			array (
			'name' => 'noinput1',
			'type' => 'noinput',
			'displaytext' => '<b>Below is the list of advanced options for Advance Slider. If you are not using it, please leave them as it is.</b>',
			'global' => 0
			),
			
			array (
			'name' => 'makeunique',
			'type' => 'bool',
			'displaytext' => 'Make the IDs unique automatically by appending a unique number ?',
			'default' => '1',
			'global' => 0
			),
			array (
			'name' => 'divclass',
			'type' => 'text',
			'displaytext' => 'DIV Class',
			'default' => '',
			'global' => 0
			),
			array (
			'name' => 'divid',
			'type' => 'text',
			'displaytext' => 'DIV ID',
			'default' => 'slider_',
			'global' => 0
			),
			array (
			'name' => 'ulclass',
			'type' => 'text',
			'displaytext' => 'UL Class',
			'default' => '',
			'global' => 0
			),
			array (
			'name' => 'ulid',
			'type' => 'text',
			'displaytext' => 'UL ID',
			'default' => '',
			'global' => 0
			),
			array (
			'name' => 'global_disable',
			'type' => 'bool',
			'displaytext' => 'Disable Slider',
			'default' => '0',
			'global' => 1
			),
			array (
			'name' => 'global_jsenable',
			'type' => 'select',
			'options' => 'yes|no',
			'displaytext' => 'Enable Javascript?',
			'default' => 'yes',
			'global' => 1
			)
		);
		parent::__construct($widgetId,$widgetInstanceId,$pageId,$this->configs);
		
	}
	
	
	public function initWidget()
	{
		$this->images = $this->settings['images'];
		$this->slidertype = $this->settings['slidertype'];
		$this->width = $this->settings['width'];
		$this->height = $this->settings['height'];
		$this->speed = $this->settings['speed'];
		$this->effect = $this->settings['effect'];
		$this->makeunique = $this->settings['makeunique'];	
		$this->divid = $this->settings['divid'];	
		$this->divclass = $this->settings['divclass'];
		$this->ulid = $this->settings['ulid'];
		$this->ulclass = $this->settings['ulclass'];
		$this->globaldisable = $this->settings['global_disable'];
		$this->gjs = $this->settings['global_jsenable'];
	}	
	public function getCommonHTML()
	{

	}
	public function getHTML()
	{
		global $urlRequestRoot,$cmsFolder;
		$jsenable = 0;
		if($this->globaldisable=='1' || $this->globaldisable=='Yes') return "";
		if($this->gjs=='yes') $jsenable = 1;
		$ran = '';
		if($this->makeunique=='1' || $this->makeunique=='Yes')
			$ran = $this->widgetInstanceId;
		$divid = $this->divid.$ran;
		$ulid = $this->ulid.$ran;
		$script = '';
		if($this->slidertype=="Advanced Slider")
			{
			$script =<<<SCRIPT
<link rel="stylesheet" type="text/css" href='$urlRequestRoot/$cmsFolder/widgets/slider/coin_sloder/coin-slider-styles.css'/>
<script src='$urlRequestRoot/$cmsFolder/widgets/slider/coin_slider/coin-slider.min.js' type='text/javascript'></script>
<script type="text/javascript">
	$(document).ready(function() {
		$('#$divid').coinslider({width:$this->width,height:$this->height,delay:$this->speed,effect:'$this->effect'});
	});
</script>
SCRIPT;
			}
		$image=explode('|',$this->images);
		$imageHtml = "";		
		$imageHtml .= "<div id='slider_container'>";
		if($jsenable==1)
			$imageHtml .= $script;		
		$imageHtml .="<div class='{$this->divclass}' id='$divid'><ul id='$ulid' class='{$this->ulclass}'>";
		for($i = 0; $i < count($image); $i++) {
			$str = explode('[',$image[$i],2);
			if(isset($str[1]))
				$link = explode(']',$str[1],2);
			$imageHtml .= "<li><a href =\"#\"><img src='$link[0]' alt='$str[0]' width='$this->width' height='$this->height' \>";
			if($str[0]!='')
				$imageHtml .= "<span>$str[0]</span>";
			$imageHtml .= "</a></li>";
		}
			$imageHtml .= "</ul></div></div>";
			return $imageHtml;
	}	
}

?>
