<?php

  /******************************************************************

   Projectname:   CAPTCHA class
   Version:       2.0
   Author:        Pascal Rehfeldt <Pascal@Pascal-Rehfeldt.com>
   Last modified: 15. January 2006

   * GNU General Public License (Version 2, June 1991)
   *
   * This program is free software; you can redistribute
   * it and/or modify it under the terms of the GNU
   * General Public License as published by the Free
   * Software Foundation; either version 2 of the License,
   * or (at your option) any later version.
   *
   * This program is distributed in the hope that it will
   * be useful, but WITHOUT ANY WARRANTY; without even the
   * implied warranty of MERCHANTABILITY or FITNESS FOR A
   * PARTICULAR PURPOSE. See the GNU General Public License
   * for more details.

   Description:
   This class can generate CAPTCHAs, see README for more details!

  ******************************************************************/

  require('filter.class.php');
  require('error.class.php');

  class captcha
  {

    var $Length;
    var $CaptchaString;
    var $fontpath;
    var $fonts;
    var $captchaImageUrl;
    var $sourceFolder;
    var $moduleFolder;
    var $uploadFolder;
    var $cmsFolder;
    var $urlRequestRoot;

    function captcha ($sourceFolder, $moduleFolder, $uploadFolder, $urlRequestRoot, $cmsFolder, $length = 5)
    {
			$this->sourceFolder = $sourceFolder;
			$this->moduleFolder = $moduleFolder;
			$this->uploadFolder = $uploadFolder;
			$this->urlRequestRoot = $urlRequestRoot;
			$this->cmsFolder = $cmsFolder;

      // header('Content-type: image/png');

      $this->Length   = $length;

      //$this->fontpath = dirname($_SERVER['SCRIPT_FILENAME']) . '/fonts/';
      global $sourceFolder, $moduleFolder;
      $this->fontpath = "$sourceFolder/$moduleFolder/form/captcha/fonts/";
      $this->fonts    = $this->getFonts();
      $errormgr       = new error;

      if ($this->fonts == FALSE)
      {

      	//$errormgr = new error;
      	$errormgr->addError('No fonts available!');
      	$errormgr->displayError();
//      	die();

      }

      if (function_exists('imagettftext') == FALSE)
      {

        $errormgr->addError('');
        $errormgr->displayError();
//       die();

      }

      $this->stringGen();

      $this->makeCaptcha();

    } //captcha

    function getFonts ()
    {

      $fonts = array();

      if ($handle = @opendir($this->fontpath))
      {

        while (($file = readdir($handle)) !== FALSE)
        {

          $extension = strtolower(substr($file, strlen($file) - 3, 3));

          if ($extension == 'ttf')
          {

            $fonts[] = $file;

          }

        }

        closedir($handle);

      }
      else
      {

      	return FALSE;

      }

      if (count($fonts) == 0)
      {

      	return FALSE;

      }
      else
      {

      	return $fonts;

      }

    } //getFonts

    function getRandFont ()
    {

      return $this->fontpath . $this->fonts[mt_rand(0, count($this->fonts) - 1)];

    } //getRandFont

    function stringGen ()
    {

      $uppercase  = array('A', 'B', 'D', 'E', 'G', 'H', 'M', 'N', 'R', 'T'); // range('A', 'Z');
      $lowercase  = array('a', 'b', 'd', 'e', 'g', 'h', 'm', 'n', 'q', 'r'); // range('a', 'z');
      $numeric    = array(2, 3, 4, 5, 6, 7, 8, 9); // range(0, 9);

      $CharPool   = array_merge($uppercase, $lowercase, $numeric);
      $PoolLength = count($CharPool) - 1;

      for ($i = 0; $i < $this->Length; $i++)
      {

        $this->CaptchaString .= $CharPool[mt_rand(0, $PoolLength)];

      }

    } //StringGen

    function makeCaptcha ()
    {

      $imagelength = $this->Length * 25 + 16;
      $imageheight = 75;

      $image       = imagecreate($imagelength, $imageheight);

      //$bgcolor     = imagecolorallocate($image, 222, 222, 222);
      $bgcolor     = imagecolorallocate($image, 255, 255, 255);

      $stringcolor = imagecolorallocate($image, 0, 0, 0);

      $filter      = new filters;

      $funcnumber = rand(0,1);
      switch ($funcnumber) {
      	case 0 :
      		$randcellno = rand(2,7);
      		$filter->signs($image, $this->getRandFont(),$randcellno);
      		break;
      	case 1 :
      		$randruns = rand(10,30);
      	    $filter->noise($image,$randruns);
      	    break;
        case 2 :
        default :
        	$randblurradius = rand(8,20);
            $filter->blur($image,15);
            break;
      }

      for ($i = 0; $i < strlen($this->CaptchaString); $i++)
      {

        imagettftext($image, 25, mt_rand(-15, 15), $i * 25 + 10,
                     mt_rand(30, 70),
                     $stringcolor,
                     $this->getRandFont(),
                     $this->CaptchaString{$i});

      }

      //$filter->noise($image, 10);
      //$filter->blur($image, 6);

			$captchaImageFolder = "$this->sourceFolder/$this->uploadFolder/temp";
			// exec('find "'.$captchaImageFolder.'" -maxdepth 1 -type 5 -mmin +60 | xargs -0 /bin/rm -f');

			$captchaImageFile = scandir($captchaImageFolder, 1);
			if(count($captchaImageFile) <= 1) {
				$captchaImageFile[0] = '000000.png';
			}
			$captchaImageFile = substr($captchaImageFile[0], 0, strrpos($captchaImageFile[0], '.'));
			$captchaImageFile++;
			$captchaImageFile = str_pad($captchaImageFile, 6, '0', STR_PAD_LEFT) . '.png';

			$this->captchaImageUrl = "$this->urlRequestRoot/$this->cmsFolder/$this->uploadFolder/temp/$captchaImageFile";

      imagepng($image, $captchaImageFolder . '/' . $captchaImageFile);
      imagedestroy($image);

    } //MakeCaptcha

    function getCaptchaString ()
    {

      return $this->CaptchaString;

    } //GetCaptchaString

		function getCaptchaUrl() {
			return $this->captchaImageUrl;
		}

  } //class: captcha


