<?php
	$eventId = trim(mysql_real_escape_string($_GET['eventId']));
	$pmcId = trim(mysql_real_escape_string($_GET['pmcId']));
	$userId = trim(mysql_real_escape_string($_GET['userId']));
	$userRank = trim(mysql_real_escape_string($_GET['userRank']));

	$certiImage = imagecreatefromjpeg('certi.jpg');
	$color = imagecolorallocate($certiImage, 0, 0, 0);	//black
	$rotatedImage = imagerotate($certiImage, 90, $color);	//rotate certificate
	$font = './font.ttf';
	//$getCertiImgQuery= $urlRequestRoot." ".$sourceFolder." ".$templateFolder;
	$getCertiImgQuery = "SELECT `certificate_id`,`certificate_image` FROM `events_certificate` WHERE `user_rank` = '{$userRank}' AND `page_moduleComponentId`='{$pmcId}' AND `event_id` = '{$eventId}'";
	$getCertiImgRes = mysql_query($getCertiImgQuery);// or displayerror(mysql_error());
	while($certiDetails = mysql_fetch_assoc($getCertiImgRes)){
		$certiImage = imagecreatefromjpeg($certiDetails['certificate_image']);
		$color = imagecolorallocate($certiImage, 0, 0, 0);	//black
		$rotatedImage = imagerotate($certiImage, 90, $color);	//rotate certificate
		$font = './font.ttf';
		$certiId = $certiDetails['certificate_id'];
		imagettftext($rotatedImage, 15, 90, 216, 512, $color, $font, $certiId);
		//Get Certificate Details From evets_certficate_details

		$getCertiDetailsQuery = "SELECT `certificate_posx`,`certificate_posy`,`form_value_id` FROM `events_certificate_details` WHERE `page_moduleComponentId`='{$pmcId}' AND `certificate_id`='{$certiId}'";
		$getCertiDetailsRes = mysql_query($getCertiDetailsQuery) or displayerror(mysql_error());

		//Get Form Values From form_elementdesc
		//Form_value_id=-1 -> Rank
		//Form_value_id=-2 -> Event Name
		while($getValues = mysql_fetch_assoc($getCertiDetailsRes)){
			//User Rank
			if($getValues['form_value_id'] == -1){
				imagettftext($rotatedImage, 20, 90, $getValues['certificate_posx'], $getValues['certificate_posy'], $color, $font, $userRank);
			}
			//Event Name
			else if($getValues['form_value_id'] == -2){
				//Get Event Name
				$getEventNameQuery = "SELECT `event_name` FROM `events_details` WHERE `event_id` = '{$eventId}' AND `page_moduleComponentId` '{$pmcId}'";
				$getEventNameRes = mysql_query($getEventNameQuery) or displayerror(mysql_error());
				$eventName = mysql_result($getEventNameRes,0);
				imagettftext($rotatedImage, 20, 90, $getValues['certificate_posx'], $getValues['certificate_posy'], $color, $font, $eventName);
			}
			else{
				//Check if modified value exists in events_edited_form
				$getFormValuesQuery = "SELECT `events_edited_form`.`form_elementdata` FROM `events_edited_form` INNER JOIN `events_form` ON `events_edited_form`.`form_id`=`events_form`.`form_id`
				AND `events_form`.`event_id`='{$event_id}' AND `events_form`.`page_moduleComponentId`='{$pmcId}' AND `events_edited_form`.`user_id`='{$userId}' AND `events_edited_form`.`page_moduleComponentId`='{$pmcId}' AND 
				`events_edited_form`.`form_elementid`='{$getValues['form_value_id']}'";
				$getFormValuesRes = mysql_query($getFormValuesQuery) or displayerror(mysql_error());
				if(mysql_num_rows($getFormValuesRes) == 0){
					//Else get value from form_elementdata
					$getFormValuesQuery = "SELECT `form_elementdata`.`form_elementdata` FROM `form_elementdata` INNER JOIN `events_form` ON `form_elementdata`.`page_moduleComponentId`=`events_form`.`form_id` 
					AND `events_form`.`event_id`='{$eventId}' AND `events_form`.`page_moduleComponentId`='{$pmcId}' AND `form_elementdata`.`user_id`='{$userId}' AND `form_elementdata`.`form_elementid`='{$getValues['form_value_id']}'";
					$getFormValuesRes = mysql_query($getFormValuesQuery) or displayerror(mysql_error());
				}
				while($formData = mysql_fetch_assos($getFormValuesRes)){
					imagettftext($rotatedImage, 20, 90, $getValues['certificate_posx'], $getValues['certificate_posy'], $color, $font, $formData['form_elementdata']);
				}
			}
		}
	}
	
	//imagettftext($rotatedImage, 15, 90, 216, 512, $color, $font, $getCertiImgQuery);
	header("Content-type:image/jpeg");
	imagejpeg($rotatedImage);
?>