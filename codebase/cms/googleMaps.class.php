<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden</h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}
/**
 * @package pragyan
 * @class googlemaps Class to render Google maps using its API and Geocoding technique
 * @author Abhishek Shrivastava
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
 
class googlemaps
{
	var $latlong="-34.397, 150.644"; ///< Just a random number :) 
	var $zoom="14";
	var $maptype="ROADMAP"; ///< Other options : SATELLITE, HYBRID, TERRAIN
	var $divid="map_canvas";
	var $divwidth="300px";
	var $divheight="300px";
	var $counter="0";
	var $includejs="<script type=\"text/javascript\" src=\"http://maps.google.com/maps/api/js?sensor=false\"></script>";
	var $mainjs = "";
	function render($text)
 	{
		global $sourceFolder;
		global $uploadFolder;
		global $urlRequestRoot, $cmsFolder;
		global $STARTSCRIPTS;
		preg_match_all("/\[googlemaps\](.*?)\[\/googlemaps\]/si", $text, $matches);
		
		if(count($matches[0])==0)
			return $text;
		
		
		$address = array();
		
		for ($i = 0; $i < count($matches[0]); $i++) {
			$position = strpos($text, $matches[0][$i]);
			$address[] = $matches[1][$i];
			$div=$this->get_div($i);
			$text = substr_replace($text, $div, $position, strlen($matches[0][$i]));
		}
		$mainjs=$this->generate_js($i,$address);
		$STARTSCRIPTS.="googlemaps_initialize();";
		return $this->includejs.$mainjs.$text;
		//return $text;
	
 	}
	function get_div($id)
	{	
		
		$div = " <div id=\"{$this->divid}{$id}\" style=\"width: {$this->divwidth}; height: {$this->divheight};\"></div>";
		return $div;
	}
	function generate_js($count,$address)
	{
		$varmaps= array();
		$varaddr= array();
		$varobj= array();
		for($i=0;$i<$count;$i++)
		{
			$varmaps[]="maps$i";
			$varaddr[]=<<<ADDR
			    var address$i = "{$address[$i]}";
			    geocoder.geocode( { 'address': address$i}, function(results, status) {
			      if (status == google.maps.GeocoderStatus.OK) {
				map$i.setCenter(results[0].geometry.location);
				var marker = new google.maps.Marker({
				    map: map$i, 
				    position: results[0].geometry.location
				});
			      } else {
				alert("Geocode was not successful for the following reason: " + status);
			      }
			    });
ADDR;
			$varobj[]="map$i = new google.maps.Map(document.getElementById(\"{$this->divid}{$i}\"), myOptions);";
		}
			
		$varmapsj=implode(",",$varmaps);
		$varaddrj=implode("\n",$varaddr);
		$varobjj=implode("\n",$varobj);
		$mainjs=<<<JS
		<script>
		    var geocode;
		    var $varmapsj;
		    function codeAddress() {
		   	$varaddrj
		  }
		  function googlemaps_initialize() {
		    geocoder = new google.maps.Geocoder();
		    var latlng = new google.maps.LatLng({$this->latlong});
		    var myOptions = {
		      zoom: {$this->zoom},
		      center: latlng,
		      mapTypeId: google.maps.MapTypeId.{$this->maptype}
		    }
		    $varobjj
		    codeAddress();
		  }

		</script>
JS;
		return $mainjs;
	
	}
	
}
