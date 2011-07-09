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
 * @author Abhishek Shrivastava
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
 // To get : Select All, Clear All, Toggle All for permissions. Groups properties, users and form assocs. 
 
$ICON_ARRAY=array(
'Up'=>"actions/go-up.png",
'Down'=>"actions/go-down.png",
'Next'=>"actions/go-next.png",
'Previous'=>"actions/go-previous.png",
'Top'=>"actions/go-top.png",
'Bottom'=>"actions/go-bottom.png",
'First'=>"actions/go-first.png",
'Last'=>"actions/go-last.png",
'Refresh'=>"actions/view-refresh.png",
'Edit'=>"apps/accessories-text-editor.png",
'Delete'=>"actions/edit-delete.png",
'Add'=>'actions/list-add.png',
'Remove'=>'actions/list-remove.png',
'Deactivate'=>"emblems/emblem-readonly.png",
'Activate'=>"emblems/emblem-symbolic-link.png",
'Propagate'=>"apps/preferences-system-windows.png",
'Unpropagate'=>"actions/system-log-out.png",
'Search'=>"actions/system-search.png",
'Home'=>"actions/go-home.png",
'News'=>'apps/internet-news-reader.png',
'Correct'=>'categories/applications-office.png',
'Icons'=>'places/start-here.png',
'Widgets'=>'apps/accessories-calculator.png',
'Edit Page'=>"actions/edit-select-all.png",
'New User'=>"actions/contact-new.png",
'Uploaded Files'=>"actions/document-open.png",
'Drafts'=>"actions/edit-paste.png",
'Page Revisions'=>"apps/preferences-system-session.png",
'Page Comments'=>"apps/internet-group-chat.png",
'Global Settings'=>"categories/preferences-desktop.png",
'User Management'=>"apps/system-users.png",
'Templates Management'=>"categories/applications-graphics.png",
'Modules Management'=>"categories/applications-accessories.png",
'Site Maintenance'=>"categories/preferences-system.png",
'Page Settings'=>"actions/document-properties.png",
'Page Information'=>"mimetypes/x-office-document.png",
'Create New Page'=>"actions/document-new.png",
'Copy or Move Page'=>"actions/edit-copy.png",
'Delete Section'=>"actions/process-stop.png",
'Page Inherited Info'=>"apps/preferences-desktop-theme.png",
'Email Registrants'=>"actions/mail-reply-all.png",
'Forum Settings'=>"emblems/emblem-system.png",
'Forum Moderate'=>"actions/edit-clear.png",
'Forum New Entry'=>"actions/window-new.png",
'Website Administration'=>"categories/applications-development.png",
'Access Permissions'=>"status/network-wireless-encrypted.png",
'User Profile'=>"places/user-home.png",
'User Groups'=>"apps/system-users.png",
'Group Management'=>"apps/system-users.png",
'PR Add User'=>"apps/system-users.png",
'Form Edit'=>'actions/edit-paste.png',
'Form Registrants'=>'apps/system-users.png',
'Gallery Edit'=>'apps/preferences-desktop-screensaver.png',
'News Edit'=>'apps/internet-news-reader.png',
'News Add'=>'apps/internet-news-reader.png',
'Database Information'=>'places/network-server.png',
'SQL Query'=>'apps/utilities-terminal.png',
'Quiz Edit'=>'categories/applications-games.png',
'Quiz Correct'=>'categories/applications-office.png',
'Group Associate Form'=>'actions/format-indent-more.png',
'Help'=>'apps/help-browser.png'
);
global $urlRequestRoot,$cmsFolder,$templateFolder;
foreach($ICON_ARRAY as $action=>$icon)
{
	$ICONS_SRC[$action]['small']="$urlRequestRoot/$cmsFolder/$templateFolder/common/icons/16x16/$icon";
	$ICONS_SRC[$action]['large']="$urlRequestRoot/$cmsFolder/$templateFolder/common/icons/32x32/$icon";
	$ICONS[$action]['small']="<img src='{$ICONS_SRC[$action]['small']}' alt='$action' title='$action'>";
	$ICONS[$action]['large']="<img src='{$ICONS_SRC[$action]['large']}' alt='$action' title='$action'>";
}
?>
