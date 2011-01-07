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
 * For more details, see README
 */
global $sourceFolder;
require_once("$sourceFolder/widgetFramework.class.php");

class facebook extends widgetFramework
{
	public $type;
	public $pageurl;
	public $color;
	public $show_face;
	public $width;
	public $height;
	public $layout;
	public $stream;
	public $header;
	public $makeunique;
	public $divid;
	public $divclass;
	public $globaldisable;
	
	public function __construct($widgetId,$widgetInstanceId,$pageId)
	{
		$this->configs = array (
			array (
			'name' => 'type',
			'type' => 'select',
			'options' => 'Facebook Page|Site|Each Page',
			'displaytext' => 'Connect to',
			'default' => 'Facebook Page',
			'global' => 0
			),
			array (
			'name' => 'show_face',
			'type' => 'select',
			'options' => 'Yes|No',
			'displaytext' => 'Display the profile pic of people',
			'default' => 'yes',
			'global' => 0
			),
			array (
			'name' => 'color',
			'type' => 'select',
			'options' => 'Light|Dark',
			'displaytext' => 'Color Scheme',
			'default' => 'Light',
			'global' => 0
			),
			array (
			'name' => 'width',
			'type' => 'integer',
			'displaytext' => 'Width',
			'default' => '450',
			'global' => 0
			),
			array (
			'name' => 'height',
			'type' => 'integer',
			'displaytext' => 'Height',
			'default' => '80',
			'global' => 0
			),
			array (
			'name' => 'layout',
			'type' => 'select',
			'options' => 'standard|button_count|box_count',
			'displaytext' => 'Layout',
			'default' => 'standard',
			'global' => 0
			),
			array (
			'name' => 'noinput1',
			'type' => 'noinput',
			'displaytext' => '<b>Below is the list of options for connecting to a Facebook Page. It is recommended to set the width as 300px and height as 556px</b>',
			'global' => 0
			),
			array (
			'name' => 'pageurl',
			'type' => 'text',
			'displaytext' => 'Specify the Facebook pagename',
			'default' => '',
			'global' => 0
			),
			array (
			'name' => 'stream',
			'type' => 'select',
			'options' => 'Yes|No',
			'displaytext' => 'Show Page stream',
			'default' => 'Yes',
			'global' => 0
			),
			array (
			'name' => 'header',
			'type' => 'select',
			'options' => 'Yes|No',
			'displaytext' => 'Show Facebook header',
			'default' => 'Yes',
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
			'default' => 'fb_',
			'global' => 0
			),
			array (
			'name' => 'divid',
			'type' => 'text',
			'displaytext' => 'DIV ID',
			'default' => 'fb_',
			'global' => 0
			),
			array (
			'name' => 'global_disable',
			'type' => 'bool',
			'displaytext' => 'Disable News',
			'default' => '0',
			'global' => 1
			),
		);
		parent::__construct($widgetId,$widgetInstanceId,$pageId,$this->configs);
		
	}
	
	
	public function initWidget()
	{
		$this->type = $this->settings['type'];
		$this->show_face = $this->settings['show_face'];
		$this->pageurl = $this->settings['pageurl'];
		$this->width = $this->settings['width'];
		$this->height = $this->settings['height'];
		$this->color = $this->settings['color'];
		$this->layout = $this->settings['layout'];
		$this->stream = $this->settings['stream'];
		$this->header = $this->settings['header'];
		$this->makeunique = $this->settings['makeunique'];	
		$this->divid = $this->settings['divid'];	
		$this->divclass = $this->settings['divclass'];
		$this->globaldisable = $this->settings['global_disable'];
	}	
	

	public function getCommonHTML()
	{
	}
	public function getHTML()
	{
		global $urlRequestRoot,$cmsFolder;
		if($this->globaldisable=='1' || $this->globaldisable=='Yes') return "";	
		$ran = '';
		if($this->makeunique=='1' || $this->makeunique=='Yes')
			$ran = $this->widgetInstanceId;
		$divid = $this->divid.$ran;
		$type = $this->type;
		$width = $this->width;
		$height = $this->height;
		$show_face = false;
		$color = $this->color;
		if($this->show_face=="Yes")
			$show_face = "true";
		if($type=="Facebook Page")
		{
		$url = urlencode($this->pageurl);
		$stream = $header = false;
		if($this->stream=="Yes")
			$stream = "true";
		if($this->header=="Yes")
			$header = "true";
		$like = <<<FBHTML
		<iframe src="http://www.facebook.com/plugins/likebox.php?href=$url&amp;width=$width&amp;colorscheme=$color&amp;show_faces=$show_face&amp;stream=$stream&amp;header=$header&amp;height=$height" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:$width; height:$height;" allowTransparency="true"></iframe>
FBHTML;
		}
		else
		{
		$layout = $this->layout;
		if($type=="Site")
		{
		$url = urlencode(hostURL());
		$like =<<<FBHTML
		<iframe src="http://www.facebook.com/plugins/like.php?href=$url&amp;layout=$layout&amp;show_faces=$show_face&amp;width=$width&amp;action=like&amp;colorscheme=$color&amp;height=$height" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:$width; height:$height;" allowTransparency="true"></iframe>
FBHTML;
		}
		else if($type=="Each Page")
		{
		$url = urlencode(selfURI());
		$like =<<<FBHTML
		<iframe src="http://www.facebook.com/plugins/like.php?href=$url&amp;layout=$layout&amp;show_faces=$show_face&amp;width=$width&amp;action=like&amp;colorscheme=$color&amp;height=$height" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:$width; height:$height;" allowTransparency="true"></iframe>
FBHTML;
		}
		}
		$fbHTML = "<div class='{$this->divclass}' id='$divid'>".$like."</div>";
		return $fbHTML;
	}	
}

?>
