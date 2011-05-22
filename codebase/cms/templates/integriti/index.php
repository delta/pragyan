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
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="cs" lang="cs">
<head>
    <title><?php $cmstitle=$TITLE;echo $cmstitle; ?></title>
    <?php if(isset($WIDGETS[0])) echo $WIDGETS[0]; ?>
	<link rel="shortcut icon" href="<?php echo $TEMPLATEBROWSERPATH; ?>/images/logo_16.png" >
	<link rel="stylesheet" type="text/css" href="<?php echo $TEMPLATEBROWSERPATH; ?>/styles/main.css" />
    <meta name="description" content="<?php echo $SITEDESCRIPTION ?>" />
    <meta name="keywords" content="<?php echo $SITEKEYWORDS ?>" /> 
	<?php global $urlRequestRoot;	global $PAGELASTUPDATED;
	if($PAGELASTUPDATED!="")
		echo '<meta http-equiv="Last-Update" content="'.substr($PAGELASTUPDATED,0,10).'" />'."\n";
	?>
    <link rel="index" href="./" title="Home" />
	<link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/styles/adminui.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/styles/other.css" />

    <link rel="stylesheet" type="text/css" href="<?php echo $TEMPLATEBROWSERPATH; ?>/styles/header.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo $TEMPLATEBROWSERPATH; ?>/styles/gallery.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo $TEMPLATEBROWSERPATH; ?>/styles/menu.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo $TEMPLATEBROWSERPATH; ?>/styles/content.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo $TEMPLATEBROWSERPATH; ?>/styles/footer.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo $TEMPLATEBROWSERPATH; ?>/styles/error.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo $TEMPLATEBROWSERPATH; ?>/styles/breadcrumb.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo $TEMPLATEBROWSERPATH; ?>/styles/actionbar.css" />
    
    <script language="javascript" type="text/javascript" src="<?php echo  $TEMPLATEBROWSERPATH; ?>/scripts/jquery-latest.js" ></script>
    <script type="text/javascript" src="<?php echo $TEMPLATEBROWSERPATH; ?>/scripts/script.js"></script>

    <script language="javascript" type="text/javascript">
		//defined here for use in javascript
		var templateBrowserPath = "<?php echo $TEMPLATEBROWSERPATH ?>";
		var urlRequestRoot = "<?php echo $urlRequestRoot?>";
	</script>

</head>

<body onload="<?php echo $STARTSCRIPTS; ?>" >
<div class="outercontainer">
	<div class="clearer"></div>
	<div class="innercontainer">
		
		<div class="clearer"></div>
		<div class="header">
		<img src="<?php echo $TEMPLATEBROWSERPATH; ?>/images/pragyancmslogo.png" style="padding-top: 6px; padding-left: 10px;">
		<div id='header_text'><?php echo $cmstitle;?></div>

		<?php if(isset($WIDGETS[1])) echo $WIDGETS[1]; ?>		
		</div>

		<!-- breadcrumb starts-->
		<div class="breadcrumb">
			<div id="breadcrumb">
			<?php echo $BREADCRUMB; ?>
			</div>
		</div>
		<!-- breadcrumb ends-->
		<div class="clearer"></div>
		<div class="actionbarcontainer">
		
			<div class="actionbar">
			<?php echo $ACTIONBARMODULE; ?>
			<?php echo $ACTIONBARPAGE; ?>
			</div>
		</div>
		<div class="clearer">
		</div>
		<div class="contentcontainer">
			<div id="cms-leftcontent">
				<div class="menucontainer">
					<?php echo $MENUBAR; ?>
					<?php if(isset($WIDGETS[4])) echo $WIDGETS[4]; ?>
				</div>
				
			</div>
			<div id="cms-content">
				<?php echo $INFOSTRING; ?>
				<?php echo $WARNINGSTRING;?>
				<?php echo $ERRORSTRING; ?>
				<?php if(isset($WIDGETS[2])) echo $WIDGETS[2]; ?>
				<?php echo $CONTENT; ?>
				<?php if(isset($WIDGETS[3])) echo $WIDGETS[3]; ?>
			</div>
			<div class="bottomcontentbar"></div>
		</div>
		<div class="clearer"></div>
		<div class="footer">
			
			<?php echo $FOOTER;?>	
		</div>
	</div>
</div>
</body>
</html>
