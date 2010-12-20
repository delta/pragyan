<?php
/**
 * @package pragyan
 * @file widget.class.php
 * @brief Class file for the widget News.
 * 
 * @author Balanivash <balanivash[at]gmail.com>
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
global $sourceFolder;
require_once("$sourceFolder/widgetFramework.class.php");

class news extends widgetFramework
{
	public $configs;
	public $timeformat;
	public $globaldisable;
	public $news;
	public $js;
	public $num;
	public $gjs;
	
	public function __construct($widgetId,$widgetInstanceId,$pageId)
	{
		$this->configs = array (
			array (
			'name' => 'news',
			'type' => 'textarea',
			'displaytext' => 'News (Specify the link like News[link]) (Use | to separate two events items)',
			'default' => 'News',
			'global' => 0
			),
			array (
			'name' => 'jsenable',
			'type' => 'select',
			'options' => 'yes|no',
			'displaytext' => 'Enable Javascript (Ticker)?',
			'default' => 'yes',
			'global' => 0
			),
				array (
			'name' => 'classname',
			'type' => 'noinput',
			'displaytext' => 'BELOW IS THE LIST OF ADVANCED OPTIONS',
			'global' => 0
			),
			array (
			'name' => 'number',
			'type' => 'integer',
			'displaytext' => 'Number of news items in one block (if js enabled)',
			'default' => '3',
			'global' => 0
			),
		
			array (
			'name' => 'global_disable',
			'type' => 'bool',
			'displaytext' => 'Disable News',
			'default' => '0',
			'global' => 1
			),
			array (
			'name' => 'global_jsenable',
			'type' => 'select',
			'options' => 'yes|no',
			'displaytext' => 'Enable Javascript (Ticker)?',
			'default' => 'yes',
			'global' => 1
			)
		);
		parent::__construct($widgetId,$widgetInstanceId,$pageId,$this->configs);
		
	}
	
	
	public function initWidget()
	{
		$this->news = $this->settings['news'];
		$this->js = $this->settings['jsenable'];
		$this->num = $this->settings['number'];
		$this->globaldisable = $this->settings['global_disable'];
		$this->gjs = $this->settings['global_jsenable'];		
	}
	

	public function getCommonHTML()
	{
	global $urlRequestRoot,$cmsFolder;
	$scripts ="<script src='$urlRequestRoot/$cmsFolder/templates/common/scripts/jcarousellite_1.0.1.js' type='text/javascript'></script> ";
	return $scripts;

	}
	public function getHTML()
	{
		global $urlRequestRoot,$cmsFolder;
		$jsenable = 0;
		if($this->globaldisable=='1' || $this->globaldisable=='Yes') return "";
		if($this->js=='yes' && $this->gjs=='yes') $jsenable = 1;	
		$num = $this->num;
		$ran = $this->widgetInstanceId;
		$style =<<<STYLE
<style type="text/css">
#news_$ran ul {
list-style:none;
}
#news_$ran ul a{
text-decoration:none;
}
</style>
STYLE;
		$script =<<<SCRIPT
<script type="text/javascript">  
$(function() {  
$("#news_$ran").jCarouselLite({  
        vertical: true,  
        visible: $num,  
        auto:500,  
        speed:1000  
    });  
});  
</script>  
SCRIPT;
		$news=explode('|',$this->news);
		$newsHtml = "";		
		$newsHtml .= $style."<div id='news_container'>";
		if($jsenable==1)
			$newsHtml .= $script;		
		$newsHtml .="<div id='news_$ran'><ul>";
		for($i = 0; $i < count($news); $i++) {
			$str = explode('[',$news[$i],2);
			if(isset($str[1]))
				$link = explode(']',$str[1],2);
			else
				$link = '#';
			$newsHtml .= "<li><a href =\"$link[0]\">$str[0]</a><br /></li>";
		}
			$newsHtml .= "</ul></div></div>";
			return $newsHtml;
	}	
}

?>
