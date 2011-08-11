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
 * @brief Class file for the Date Countdown.
 * 
 * @author Balanivash <balanivash[at]gmail.com>
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
global $sourceFolder;
require_once("$sourceFolder/widgetFramework.class.php");

class count_down extends widgetFramework
{
	public $configs;
	public $timeformat;
	public $globaldisable;
	public $date;
	public $event;
	public $format;
	
	public function __construct($widgetId,$widgetInstanceId,$pageId)
	{
		$this->configs = array (
			array (
			'name' => 'date',
			'type' => 'datetime',
			'displaytext' => 'Enter the date to countdown to',
			'default' => '',
			'global' => 0
			),
			array (
			'name' => 'format',
			'type' => 'select',
			'options' => 'days|hours|minutes|seconds',
			'displaytext' => 'Enter the format of countdown',
			'default' => 'days',
			'global' => 0
			),
			array (
			'name' => 'event',
			'type' => 'text',
			'displaytext' => 'Enter the Event',
			'default' => 'event',
			'global' => 0
			),
			array (
			'name' => 'global_disable',
			'type' => 'bool',
			'displaytext' => 'Disable all countdowns in the website',
			'default' => '0',
			'global' => 1
			)
		);
		parent::__construct($widgetId,$widgetInstanceId,$pageId,$this->configs);
		
	}
	
	
	public function initWidget()
	{
		$this->date = $this->settings['date'];
		$this->globaldisable = $this->settings['global_disable'];
		$this->event = $this->settings['event'];
		$this->format = $this->settings['format'];
	}
	
	public function getCommonHTML()
	{
	
			$count =<<<COUNT
<script type="text/javascript">
function cdtime(container, targetdate){
if (!document.getElementById || !document.getElementById(container)) return;
this.container=document.getElementById(container);
this.currentTime=new Date();
this.targetdate=new Date(targetdate);
this.timesup=false;
this.updateTime();
}
cdtime.prototype.updateTime=function(){
var thisobj=this;
this.currentTime.setSeconds(this.currentTime.getSeconds()+1);
setTimeout(function(){thisobj.updateTime()}, 1000); //update time every second
}
cdtime.prototype.displaycountdown=function(baseunit, functionref){
this.baseunit=baseunit;
this.formatresults=functionref;
this.showresults();
}
cdtime.prototype.showresults=function(){
var thisobj=this;
var timediff=(this.targetdate-this.currentTime)/1000; //difference btw target date and current date, in seconds
if (timediff<0){ //if time is up
this.timesup=true;
var timediff=(this.currentTime-this.targetdate)/1000;
}
var oneMinute=60; //minute unit in seconds
var oneHour=60*60; //hour unit in seconds
var oneDay=60*60*24; //day unit in seconds
var dayfield=Math.floor(timediff/oneDay);
var hourfield=Math.floor((timediff-dayfield*oneDay)/oneHour);
var minutefield=Math.floor((timediff-dayfield*oneDay-hourfield*oneHour)/oneMinute);
var secondfield=Math.floor((timediff-dayfield*oneDay-hourfield*oneHour-minutefield*oneMinute));
if (this.baseunit=="days"){
dayfield=dayfield+" <sub>days</sub> "
hourfield=hourfield+" <sub>hours</sub> ";
minutefield=minutefield+" <sub>minutes</sub> ";
secondfield=secondfield+" <sub>seconds</sub>";
}
else if (this.baseunit=="hours"){ //if base unit is hours, set "hourfield" to be topmost level
hourfield=dayfield*24+hourfield+" <sub>hours</sub> ";
minutefield=minutefield+" <sub>minutes</sub> ";
secondfield=secondfield+" <sub>seconds</sub>";
dayfield="";
}
else if (this.baseunit=="minutes"){ //if base unit is minutes, set "minutefield" to be topmost level
minutefield=dayfield*24*60+hourfield*60+minutefield+" <sub>minutes</sub> ";
secondfield=secondfield+" <sub>seconds</sub>";
dayfield=hourfield="";
}
else if (this.baseunit=="seconds"){ //if base unit is seconds, set "secondfield" to be topmost level
var secondfield=parseInt(timediff)+" <sub>seconds</sub>";;
dayfield=hourfield=minutefield="";
}
this.container.innerHTML=this.formatresults(dayfield, hourfield, minutefield, secondfield);
setTimeout(function(){thisobj.showresults()}, 1000); //update results every second
}
</script>
COUNT;
	return $count;

	}
	public function getHTML()
	{
		if($this->globaldisable=='1' || $this->globaldisable=='Yes') return "";
		$date =$this->date;
		if($this->event!="")
			$event=$this->event;
		else
			$event="Event";
		
		
		///Converting to proper format
		$date=preg_replace('/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})[\s]+([0-9]{1,2}):([0-9]{1,2})/i','$2 $3 $1 $4:$5',$date);
		
		$format = $this->format;
		$ran = $this->widgetInstanceId;
		$count ="<div id=\"countdowncontainer_$ran\"></div>";
		$count .=<<<COUNT
<script type="text/javascript">
function result(){
var eventstring = "$event";
if (this.timesup==false){ //if target date/time not yet met
var displaystring=arguments[0]+arguments[1]+arguments[2]+arguments[3]+"left until "+eventstring;
}
else{ //else if target date/time met
if(arguments[0]=="0 <sub>days</sub> ")
	var displaystring=eventstring + "is here!!!";
else
	var displaystring= arguments[0]+arguments[1]+arguments[2]+arguments[3]+" since "+eventstring;
}
return displaystring;
}
var count_down_date=new cdtime("countdowncontainer_$ran","$date");
count_down_date.displaycountdown("$format", result);
</script>
COUNT;
		return $count;
	}	
}

?>
