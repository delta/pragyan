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

  class error
  {

  	var $errors;

  	function error ()
  	{

  	  $this->errors = array();

  	} //error

  	function addError ($errormsg)
  	{

  	  $this->errors[] = $errormsg;

  	} //addError

  	function displayError ()
  	{
  		displayerror('Error! Could not generate captcha.<br />' . join($this->errors, '<br />'));
/*
      $iheight     = count($this->errors) * 20 + 10;
      $iheight     = ($iheight < 130) ? 130 : $iheight;

      $image       = imagecreate(600, $iheight);

      $errorsign   = imagecreatefromjpeg('gfx/errorsign.jpg');
      imagecopy($image, $errorsign, 1, 1, 1, 1, 180, 120);

      $bgcolor     = imagecolorallocate($image, 255, 255, 255);

      $stringcolor = imagecolorallocate($image, 0, 0, 0);

      for ($i = 0; $i < count($this->errors); $i++)
      {

        $imx = ($i == 0) ? $i * 20 + 5 : $i * 20;


        $msg = 'Error[' . $i . ']: ' . $this->errors[$i];

        imagestring($image, 5, 190, $imx, $msg, $stringcolor);

  	  }

      imagepng($image);

      imagedestroy($image);*/

  	} //displayError

  	function isError ()
  	{

  	  if (count($this->errors) == 0)
  	  {

  	  	return FALSE;

  	  }
  	  else
  	  {

  	  	return TRUE;

  	  }

  	} //isError

  } //class: error

