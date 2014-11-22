<?php
	function generateImage($certiImage,$posXString,$posYString,$posX2String,$posY2String,$valueString){
		$certiPath = dirname(__FILE__);
		$certiImage = imagecreatefromjpeg($certiPath.'/certi_images/'.$certiImage);
		$color = imagecolorallocate($certiImage, 0, 0, 0);	//black
		//		$whiteBackground = imagecolorallocate($background, 255, 255, 255);
		//	$imagefill($certiImage,0,0,$whiteBackground);
		$rotatedImage = imagerotate($certiImage, 90, $color);	//rotate certificate
		$font = $certiPath.'/fonts/odstemplik.otf';
		$posXArray = explode("::", $posXString);
		$posYArray = explode("::", $posYString);
		$posX2Array = explode("::",$posX2String);
		$posY2Array =  explode("::",$posY2String);
		$valuesArray = explode("::", $valueString);
	//	error_log(print_r($valuesArray));
		for($i=0;$i<sizeof($valuesArray);$i++){
			$lineWidth = $posYArray[$i]-$posY2Array[$i];
			
			$font_size=90;
			do{
       			$p=imagettfbbox($font_size,0,$font,$valuesArray[$i]);
			   $textWidth = $p[2]-$p[0];
			   $font_size--;
//			   error_log($textWidth);
			   }while($textWidth>=$lineWidth);
			   $y=($lineWidth-$textWidth)/2;
			imagettftext($rotatedImage, $font_size, 90, $posXArray[$i], $posYArray[$i]-$y, $color, $font, $valuesArray[$i]);
		}
		ob_start();
		imagejpeg($rotatedImage);
		$actual_image = base64_encode(ob_get_contents());
		ob_end_clean();
		return "data:image/png;base64,".$actual_image;
	}
?>