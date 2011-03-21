/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	config.extraPlugins='tex,googlemaps';
	config.toolbar_Full.push(['tex']);
	config.toolbar_Full.push(['googlemaps']);
	config.toolbar = 'Pragyan';
 
	config.toolbar_Pragyan =
	[
	    ['Source','-','Save','NewPage','Preview','Print'],    
	    ['Templates','Maximize', 'ShowBlocks','-','Undo','Redo'],
	    ['SelectAll','Cut','Copy','Paste','PasteText','PasteFromWord','RemoveFormat','-','Find','Replace','-','SpellChecker', 'Scayt'],
	    '/',
	    ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
	    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
	    ['Styles','Format','Font','FontSize'],
	    ['TextColor','BGColor'],
	    '/',
	    ['BidiLtr', 'BidiRtl','Outdent','Indent','-','Blockquote','CreateDiv'],
	    ['NumberedList','BulletedList'],
	    ['Link','Unlink','Anchor'],
	    ['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','Iframe'],
	    ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'],
	    '/',
	    ['tex','googlemaps','-','About']
	];

};
