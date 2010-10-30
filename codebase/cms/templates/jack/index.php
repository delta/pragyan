<!-- Created By Jack (chakradarraju@gmail.com) -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="description" content="<? echo $SITEDESCRIPTION ?>" />
<meta name="keywords" content="<? echo $SITEKEYWORDS ?>" /> 
<title><?php echo $TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="<? echo $TEMPLATEBROWSERPATH; ?>/../common/error.css" />
<link rel="stylesheet" type="text/css" href="<? echo $TEMPLATEBROWSERPATH; ?>/../common/adminui.css" />
<link rel="stylesheet" type="text/css" href="<? echo $TEMPLATEBROWSERPATH; ?>/index.css" media="all" />
<?global $urlRequestRoot;	global $PAGELASTUPDATED;
if($PAGELASTUPDATED!="")
	echo '<meta http-equiv="Last-Update" content="'.substr($PAGELASTUPDATED,0,10).'" />'."\n";
?>
</head>
<body onload="<? echo $STARTSCRIPTS;?>">

    <div id="framecontent">
      <div class="innertube">
      	<img src = "<?php echo $TEMPLATEBROWSERPATH; ?>/site-logo.png">
        <?php echo $MENUBAR; ?>
       </div>
    </div>
  
    <div id="hframecontent">
      <div class="innertube"></div>
        <div id="hmenu">
				<? echo $ACTIONBARMODULE;?>
				<? echo $ACTIONBARPAGE;?>
        </div>
    </div>
  
    <div id="maincontent">
      <div class="bcenvelope">
              <? echo $BREADCRUMB; ?>
      </div>
      <div class="innertube">
              <? echo $INFOSTRING;?>
	          <? echo $WARNINGSTRING;?>
	          <? echo $ERRORSTRING;?>
	          <? echo $CONTENT; ?>
	        <br class="clearFloat" />
			<div id="footer">
				<? echo $FOOTER; ?>
			</div>
      </div>
    </div>



</body>
</html>
