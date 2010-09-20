<?php
/*
 * Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

if ( !function_exists('version_compare') || version_compare( phpversion(), '5', '<' ) )
	include_once( 'ckeditor_php4.php' ) ;
else
	include_once( 'ckeditor_php5.php' ) ;
