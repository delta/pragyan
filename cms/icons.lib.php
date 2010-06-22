<?php
$ICON_ARRAY=array(
'Refresh'=>"actions/view-refresh.png",
'Edit Page'=>"actions/edit-select-all.png",
'Uploaded Files'=>"actions/document-open.png",
'Page Revisions'=>"apps/preferences-system-session.png",
'Page Comments'=>"apps/internet-group-chat.png",
'Global Settings'=>"categories/preferences-desktop.png",
'User Management'=>"apps/system-users.png",
'Templates Management'=>"categories/applications-graphics.png",
'Site Maintenance'=>"categories/preferences-system.png",
'Page Settings'=>"actions/document-properties.png",
'Create New Page'=>"actions/document-new.png",
'Copy or Move Page'=>"actions/edit-copy.png",
'Page Inherited Info'=>"apps/preferences-desktop-theme.png"
);
global $urlRequestRoot,$cmsFolder,$templateFolder;
foreach($ICON_ARRAY as $action=>$icon)
{
	$ICONS[$action]['small']="<img src='$urlRequestRoot/$cmsFolder/$templateFolder/common/icons/16x16/$icon' alt='$action' title='$action'>";
	$ICONS[$action]['large']="<img src='$urlRequestRoot/$cmsFolder/$templateFolder/common/icons/32x32/$icon' alt='$action' title='$action'>";
}
?>
