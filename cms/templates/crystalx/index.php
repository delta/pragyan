<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="cs" lang="cs">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title><?php echo  $TITLE; ?></title>
    <meta name="description" content="<?php echo $SITEDESCRIPTION ?>" />
    <meta name="keywords" content="<?php echo $SITEKEYWORDS ?>" /> 
	<?php global $urlRequestRoot;	global $PAGELASTUPDATED;
	if($PAGELASTUPDATED!="")
		echo '<meta http-equiv="Last-Update" content="'.substr($PAGELASTUPDATED,0,10).'" />'."\n";
	?>
    <link rel="index" href="./" title="Home" />
    <link rel="stylesheet" media="screen,projection" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/css/main.css" />
    <link rel="stylesheet" media="print" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/css/print.css" />
    <link rel="stylesheet" media="aural" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/css/aural.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/css/other.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/../common/other.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/../common/error.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/../common/adminui.css" />
    <script language="javascript" type="text/javascript" src="<?php echo  $TEMPLATEBROWSERPATH; ?>/../common/scripts/jquery-latest.js" ></script>

	<script language="javascript" type="text/javascript">
		//defined here for use in javascript
		var templateBrowserPath = "<?php echo $TEMPLATEBROWSERPATH ?>";
		var urlRequestRoot = "<?php echo $urlRequestRoot?>";
	</script>
</head>

<body onload="<?php echo $STARTSCRIPTS;?>">

<!-- Main -->
<div id="main" class="box">
    <!-- Header -->
    <div id="header">
        <!-- Logotyp -->
        <h1 id="logo"><?php echo  $TITLE; ?></h1>
      <?php if(isset($WIDGETS[0])) echo $WIDGETS[0]; ?>
    </div> <!-- /header -->

    <!-- Page (2 columns) -->
    <div id="page" class="box">
    <div id="page-in" class="box">
        <div id="strip" class="box noprint">
            <!-- Breadcrumbs -->
            <div id="breadcrumbs">
	           	<?php echo  $BREADCRUMB; ?>

				<?php echo $ACTIONBARPAGE;?>
				<?php echo $ACTIONBARMODULE;?>
				  <?php if(isset($WIDGETS[1])) echo $WIDGETS[1]; ?>
            </div>
            <hr class="noscreen" />
            
        </div> <!-- /strip -->

        <!-- Content -->
        <div id="content">

            <!-- Article -->
            <div class="article">
              <?php echo $INFOSTRING;?>
	          <?php echo $WARNINGSTRING;?>
	          <?php echo $ERRORSTRING;?>
	            <?php if(isset($WIDGETS[2])) echo $WIDGETS[2]; ?>
	          <?php echo  $CONTENT; ?>
  <?php if(isset($WIDGETS[3])) echo $WIDGETS[3]; ?>
            </div> <!-- /article -->
        </div> <!-- /content -->

        <!-- Right column -->
        <div id="col" class="noprint">
            <div id="col-in">
                <!-- Category -->
				<?php echo $MENUBAR;?>
  <?php if(isset($WIDGETS[4])) echo $WIDGETS[4]; ?>
				<hr class="noscreen" />
				<h3><span>Links</span></h3>
				<ul id="links">
					<li><a href="http://www.sourceforge.net/projects/pragyan">Pragyan CMS Project</a></li>
					<li><a href="http://www.pragyan.org">Pragyan Festival</a></li>
					<li><a href="http://www.nitt.edu">NIT Trichy</a></li>
				</ul>
				<hr class="noscreen"/>
            </div> <!-- /col-in -->
        </div> <!-- /col -->
    </div> <!-- /page-in -->
    </div> <!-- /page -->

    <!-- Footer -->
    <div id="footer">
        <div id="top" class="noprint"><p><span class="noscreen">Back to top</span> <a href="#header" title="Back to top ^">^<span></span></a></p></div>
        <hr class="noscreen" />
       
        <p id="copyright"><?php echo $FOOTER; ?>  <?php if(isset($WIDGETS[5])) echo $WIDGETS[5]; ?></p>
         <div id="pragyan_banner_footer"></div>
    </div> <!-- /footer -->

</div> <!-- /main -->

</body>
</html>
