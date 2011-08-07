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
	 <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="description" content="<?php echo $SITEDESCRIPTION ?>" />
    <meta name="keywords" content="<?php echo $SITEKEYWORDS ?>" /> 
<?php
	global $urlRequestRoot;
	$pageStyle="";
	if($MENUBAR!="")	$pageStyle=" <link rel=\"stylesheet\" href=\"$TEMPLATEBROWSERPATH/style/style-bothbars.css\" />";
	else $pageStyle=" <link rel=\"stylesheet\" href=\"$TEMPLATEBROWSERPATH/style/style-rightbar.css\" />";
 ?>
  <title><?php echo  $TITLE ?></title>
  <link rel="stylesheet" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/style/style.css" />
  <link rel="stylesheet" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/style/admin.css" />
  <link rel="stylesheet" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/style/error.css" />
  <?php echo  $pageStyle; ?>
  <link rel="stylesheet" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/style/other.css" />
<link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/../common/adminui.css" />
  <script language='javascript' src="<?php echo  $TEMPLATEBROWSERPATH; ?>>/scripts/ajaxbasic.js" ></script>

</head>
<body onload='<?php echo  $STARTSCRIPTS ?>'>
  <div id="outer_wrapper">
    <div id="wrapper">
      <div id="header">
        <h1>Pragyan CMS v3</h1>
      </div><!-- /header -->
      <div id="container">
        <div id="left">
          <?php echo $MENUBAR;?>
        </div><!-- /left -->

        <div id="main">
      	   <div id="breadcrumbs">
	           	<?php echo  $BREADCRUMB; ?>
	           	<?php
	           		global $userId;
	           		if($userId == 0) {
	           			echo "<div id=\"cms-actionbarPage\"><span class=\"cms-actionbarPageItem\"><a href=\"./+login\">Login</a></span></div>";
	           		}
	           	?>
	           		<?php echo (($userId==0)?"":$ACTIONBARPAGE);?>
	           		<?php echo (($userId==0)?"":$ACTIONBARMODULE);?>
				
				
            </div>
          <div id="content">
          <div id="pageheading">          <?php echo  $TITLE; ?></div>
            <?php echo $INFOSTRING;?>
	          <?php echo $WARNINGSTRING;?>
	          <?php echo $ERRORSTRING;?>
	          <?php echo  $CONTENT; ?>
          
	
	
          </div>
        </div><!-- /main -->
         <!-- This is for NN6 -->
        <div class="clearing">&nbsp;</div>
      </div><!-- /container -->
    
      <!-- This is for NN4 -->
      <div class="clearing">&nbsp;</div>

      <div id="footer">
      <center>powered by Pragyan CMS v3.0 released by Abhishek (abhishekdelta);<center>
      </div><!-- /footer -->
    </div><!-- /wrapper -->
  </div><!-- /outer_wrapper -->
</body>

</html>
