<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php


$menu_left = $MENUBAR;
include_once("$TEMPLATECODEPATH/sidebar.php");global $pageId, $action;
$menu_right = "";
if($pageId==0 && $action == "view" )
	$menu_right = $SIDEBARCONTENT;
$extracss="";

if($menu_left != "" && $menu_right != "")
	$extracss = "leftAndRightMenu.css";
elseif($menu_left != "" && $menu_right == "")
	$extracss = "leftMenuOnly.css";
elseif($menu_left == "" && $menu_right != "")
	$extracss = "rightMenuOnly.css";
else
	$extracss = "noMenu.css";
?>

<html>
<head>
	<title><?php echo $TITLE ?></title>
	<link href="<?php echo $TEMPLATEBROWSERPATH ?>/images/favicon.ico" rel="shortcut icon" />
	<link rel="stylesheet" type="text/css" href="<?php echo $TEMPLATEBROWSERPATH ?>/style.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $TEMPLATEBROWSERPATH ?>/<?php echo $extracss?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH ?>/../common/error.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/../common/adminui.css" />
</head>
<body>
	
	<div id="outer_wrapper">
		<div id="content_wrapper"><!--the whole page from left to right-->
			<div id="content_outercontainer">
				<div id="content_containerrightborder"><!-- having the right border -->
				
					<div id="topbox_wrapper">
			
						<div id="header_wrapper">
							<div id="header_content">
								<a  href="<?php echo  $urlRequestRoot==""?"/":$urlRequestRoot ?>">
									<img alt="NIT Trichy" src="<?php echo  $TEMPLATEBROWSERPATH ?>/images/nitt-logo-white.png"/>
									<h1>National Institute of Technology</h1>
									<h2>Tiruchirappalli</h2>
								</a>
							</div><!--header_content-->
						</div><!--header_wrapper-->
			
						<div id="toplinkbar_wrapper">
							<div id="toplinkbar_content">
								<div id="toplinkbar_links">
									<div><div class="toplinkinner"><a href="<?php echo  $urlRequestRoot==""?"/":$urlRequestRoot ?>" title="Home">
				<img alt="To start page" src="<?php echo  $TEMPLATEBROWSERPATH ?>/images/home_button.png" />
				</a></div></div>
									<div><div class="toplinkinner"><a class="toplink" href="<?php echo  $urlRequestRoot?>/academics/admissions">Admissions</a></div></div>									
									<div><div class="toplinkinner"><a class="toplink" href="<?php echo  $urlRequestRoot?>/academics/departments">Departments</a></div></div>
									<div><div class="toplinkinner"><a class="toplink" href="<?php echo  $urlRequestRoot?>/students/facilitiesnservices">Facilities</a></div></div>
									<div><div class="toplinkinner"><a class="toplink" href="http://webmail.nitt.edu" target="_blank">Webmail</a></div></div>
								</div> <!--toplinkbar_links-->
							</div><!--toplinkbar_content-->
						</div><!--toplinkbar_wraper-->
			
					</div><!--topbox_wrapper-->
			
				
				
				
					<div id="content_containerleftborder"><!-- having the left border -->
						<div id="content_container"><!--content within the borders, including menus and footer--> 
	
							<div id="menu_left">
								<?php echo  $menu_left ?>
							</div><!--menu_left-->
							<div id="menu_right">
								<?php echo  $menu_right ?>
							</div><!--menu_right-->
							<div id="content_main">
								<table id="breadcrumbtable" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td><?php echo $BREADCRUMB?></td>
										<td align="right"><?php echo $ACTIONBARPAGE?><?php echo $ACTIONBARMODULE?></td>
									</tr>
								</table>
								<div id="content">
									<?php echo $ERRORSTRING?>
	        						<?php echo $INFOSTRING?>
	        						<?php echo $WARNINGSTRING?>
	        						<?php echo ($action=="view"?$INHERITEDINFO:"")?>
	        						<?php echo  $heading = ''; if (getTitle($pageId,$action, $heading)) echo "<h1 id=\"contentheading\">$heading</h1>\n"; ?>
									<?php echo $CONTENT?>
								</div><!--content-->
							</div><!--content_main-->
							<div id="footer">
								<strong>
	
				<a href="<?php echo $urlRequestRoot?>/contact">Contact</a> | <a href="<?php echo $urlRequestRoot?>/other/righttoinfoact">Right to Information Act</a> | <a href="<?php echo $urlRequestRoot?>/webteam">Webteam</a> | <a href="<?php echo $urlRequestRoot?>/about">About</a>
	
			</strong>
			<br />
			<?php echo $FOOTER; ?>
			<br />
		 	National Institute of Technology, Tiruchirapalli - 620015, INDIA
							</div><!--footer-->
						</div><!--content_container-->
					</div><!--content_containerleftborder-->
				</div><!--content_containerrightborder-->
			</div><!--content_outercontainer-->
		</div><!--content_wrapper-->
	</div><!-- /outer_wrapper -->
</body>
</html>
