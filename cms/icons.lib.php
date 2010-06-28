<?php
$ICON_ARRAY=array(
'Refresh'=>"actions/view-refresh.png",
'Edit'=>"apps/accessories-text-editor.png",
'Delete'=>"actions/edit-delete.png",
'Deactivate'=>"emblems/emblem-readonly.png",
'Activate'=>"emblems/emblem-symbolic-link.png",
'Edit Page'=>"actions/edit-select-all.png",
'New User'=>"actions/contact-new.png",
'Search'=>"actions/system-search.png",
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
'Page Inherited Info'=>"apps/preferences-desktop-theme.png",
'Email Registrants'=>"actions/mail-reply-all.png"
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
