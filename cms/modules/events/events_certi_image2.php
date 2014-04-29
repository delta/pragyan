<?php
	function generateImage($certiImage,$posXString,$posYString,$valueString){
		$certiPath = dirname(__FILE__);
		$certiImage = imagecreatefromjpeg($certiPath.'/certi_images/'.$certiImage);
		$color = imagecolorallocate($certiImage, 0, 0, 0);	//black
		$rotatedImage = imagerotate($certiImage, 90, $color);	//rotate certificate
		$font = $certiPath.'/fonts/font.ttf';
		$posXArray = explode("::", $posXString);
		$posYArray = explode("::", $posYString);
		$valuesArray = explode("::", $valueString);
		for($i=0;$i<sizeof($valuesArray);$i++){
			imagettftext($rotatedImage, 100, 90, $posXArray[$i], $posYArray[$i], $color, $font, $valuesArray[$i]);
		}
		ob_start();
		imagejpeg($rotatedImage);
		$actual_image = base64_encode(ob_get_contents());
		ob_end_clean();
		return "data:image/png;base64,".$actual_image;
	}
?>