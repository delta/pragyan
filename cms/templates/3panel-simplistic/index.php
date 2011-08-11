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
<html>
<head>
<?php
	global $urlRequestRoot;
	$pageStyle="";
	if($MENUBAR!="")	$pageStyle=" <link rel=\"stylesheet\" href=\"$TEMPLATEBROWSERPATH/../common/style-leftbar.css\" />";
	include_once("$TEMPLATECODEPATH/sidebar.php");
	if($SIDEBARCONTENT!="")
		if($pageStyle=="") $pageStyle=" <link rel=\"stylesheet\" href=\"$TEMPLATEBROWSERPATH/../common/style-rightbar.css\" />";
		else	$pageStyle=" <link rel=\"stylesheet\" href=\"$TEMPLATEBROWSERPATH/../common/style-bothbars.css\" />";
/*&$TITLE,&$MENUBAR,&$TEMPLATEBROWSERPATH,&$ACTIONBARMODULE,&$ACTIONBARPAGE,&$BREADCRUMB,
 * &$MENUBAR,&$CONTENT,&$DEBUGINFO,&$ERRORSTRING,&$WARNINGSTRING,&$INFOSTRING */ ?>
  <title><?php echo $TITLE ?></title>
  <!--<link rel="stylesheet" href="{template_path}style.css" />-->
  <link rel="stylesheet" href="<?php echo  $TEMPLATEBROWSERPATH ?>/style.css" />
  <?php echo  $pageStyle ?>
  <link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/../common/other.css" />
  <link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/../common/style.css" />
  <link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/../common/error.css" />
  <link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/../common/adminui.css" />
  <script language="javascript" type="text/javascript" src="<?php echo  $TEMPLATEBROWSERPATH; ?>/../common/scripts/ftod.js"></script>
  <script language="javascript" type="text/javascript">
  <!--
  //  window.onload = function() {
  //    AddFillerLink("header", "left", "main", "sidebar", "footer");
  //  }
  -->
  </script>
  <script language="javascript" type="text/javascript">
  	function loadScript() {
  		<?php echo  $STARTSCRIPTS ?>
  	}
  </script>
</head>
<body onload="loadScript()">
  <div id="outer_wrapper">
    <div id="wrapper">
      <div id="header">
        <h1><?php echo  $TITLE ?></h1>
      </div><!-- /header -->
      <div id="container">
        <div id="left">
           <?php echo $MENUBAR?>
        </div><!-- /left -->

        <div id="main">
      	  <?php echo $BREADCRUMB?>
			<?php echo $ACTIONBARPAGE?>
			<?php echo $ACTIONBARMODULE?>
          <div id="content"">
          <?php echo $ERRORSTRING?>
          <?php echo $INFOSTRING?>
          <?php echo $WARNINGSTRING?>
          <?php echo $CONTENT?>
          </div>
        </div><!-- /main -->
        <!-- This is for NN6 -->
        <div class="clearing">&nbsp;</div>
      </div><!-- /container -->
      <div id="sidebar">
        <?php echo $SIDEBARCONTENT?>
      </div><!-- /sidebar -->
      <!-- This is for NN4 -->
      <div class="clearing">&nbsp;</div>

      <div id="footerbar">
      <center><?php echo $FOOTER; ?></center>
      </div><!-- /footer -->
    </div><!-- /wrapper -->
  </div><!-- /outer_wrapper -->
</body>

</html>
