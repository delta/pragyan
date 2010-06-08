<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<?
	global $urlRequestRoot;
	$pageStyle="";
	if($MENUBAR!="")	$pageStyle=" <link rel=\"stylesheet\" href=\"$TEMPLATEBROWSERPATH/style/style-bothbars.css\" />";
	else $pageStyle=" <link rel=\"stylesheet\" href=\"$TEMPLATEBROWSERPATH/style/style-rightbar.css\" />";
 ?>
  <title><? echo  $TITLE ?></title>
  <link rel="stylesheet" href="<? echo  $TEMPLATEBROWSERPATH; ?>/style/style.css" />
  <link rel="stylesheet" href="<? echo  $TEMPLATEBROWSERPATH; ?>/style/admin.css" />
  <link rel="stylesheet" href="<? echo  $TEMPLATEBROWSERPATH; ?>/style/error.css" />
  <? echo  $pageStyle; ?>
  <link rel="stylesheet" href="<? echo  $TEMPLATEBROWSERPATH; ?>/style/other.css" />

  <script language='javascript' src="<? echo  $TEMPLATEBROWSERPATH; ?>>/scripts/ajaxbasic.js" ></script>

</head>
<body onload='<? echo  $STARTSCRIPTS ?>'>
  <div id="outer_wrapper">
    <div id="wrapper">
      <div id="header">
        <h1>Pragyan CMS v3</h1>
      </div><!-- /header -->
      <div id="container">
        <div id="left">
          <? echo $MENUBAR;?>
        </div><!-- /left -->

        <div id="main">
      	   <div id="breadcrumbs">
	           	<? echo  $BREADCRUMB; ?>
	           	<?php
	           		global $userId;
	           		if($userId == 0) {
	           			echo "<div id=\"cms-actionbarPage\"><span class=\"cms-actionbarPageItem\"><a href=\"./+login\">Login</a></span></div>";
	           		}
	           	?>
	           		<? echo (($userId==0)?"":$ACTIONBARPAGE);?>
	           		<? echo (($userId==0)?"":$ACTIONBARMODULE);?>
				
				
            </div>
          <div id="content">
          <div id="pageheading">          <? echo  $TITLE; ?></div>
            <? echo $INFOSTRING;?>
	          <? echo $WARNINGSTRING;?>
	          <? echo $ERRORSTRING;?>
	          <? echo  $CONTENT; ?>
          
	
	
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
