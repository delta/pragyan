<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="cs" lang="cs">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

    <title><?php echo  $TITLE; ?></title>
    <meta name="description" content="<?php echo $SITEDESCRIPTION ?>" />
    <meta name="keywords" content="<?php echo $SITEKEYWORDS ?>" /> 
	<?php global $urlRequestRoot;	global $PAGELASTUPDATED;
	if($PAGELASTUPDATED!="")
		echo '<meta http-equiv="Last-Update" content="'.substr($PAGELASTUPDATED,0,10).'" />'."\n";
	?>
    <link rel="index" href="./" title="Home" />
    <link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/styles/layout.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/styles/main.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/styles/menu.css" />
 <link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/../common/adminui.css" />

    
    <script language="javascript" type="text/javascript" src="<?php echo  $TEMPLATEBROWSERPATH; ?>/scripts/jquery-latest.js" ></script>
    <script type="text/javascript" src="<?php echo $TEMPLATEBROWSERPATH; ?>/scripts/jquery.utils.min.js"></script>
	<script language="javascript" type="text/javascript">
		//defined here for use in javascript
		var templateBrowserPath = "<?php echo $TEMPLATEBROWSERPATH ?>";
		var urlRequestRoot = "<?php echo $urlRequestRoot?>";
	</script>
	<script src="<?php echo $TEMPLATEBROWSERPATH;?>/scripts/menu.js" type="text/javascript"></script>
	<script src="<?php echo $TEMPLATEBROWSERPATH;?>/scripts/countdown.js" type="text/javascript"></script>
</head>

<body onload="<?php echo $STARTSCRIPTS; ?>">

<div class="outercontainer">
	<div class="innercontainer">
	<div class="menucontainer">
	    		<?php echo $COMPLETEMENU; ?>
	</div> 
			
		<div id="header">
		<a href="./"> <img src="<?php echo $TEMPLATEBROWSERPATH;?>/images/header.jpg" alt="Pragyan 2010" /> </a>
		</div>
	<div class="contentcontainer">
		<div id="actionbar" class="cont">
			<?php echo $ACTIONBARPAGE ."<div>&nbsp; </div>". $ACTIONBARMODULE; ?>
		</div>
		<div class="clearer"></div>
		<div class="contentinnertube">
			<div id="leftContent">
				<div id="quicklinks">
				<table border="1" cellspacing="0" cellpadding="7.5">
				<tr><td><a href="./+login"><img id="userLog" src="<?php echo $TEMPLATEBROWSERPATH;?>/images/icons/login.jpg" alt="Login" /></a></td></tr>
				<tr><td><a href="/contact"><img src="<?php echo $TEMPLATEBROWSERPATH;?>/images/icons/contactus.jpg" alt="" /></a></td></tr>
				<tr><td><a href="http://www.facebook.com/pages/pragyan"><img src="<?php echo $TEMPLATEBROWSERPATH;?>/images/icons/facebook.jpg" alt="" /></a></td></tr>
				<tr><td><a href="http://www.twitter.com/pragyan"><img src="<?php echo $TEMPLATEBROWSERPATH;?>/images/icons/twitter.jpg" alt="" /></a></td></tr>
				</table>
				</div>
			</div>
			<div id="rightContent">
				<div id="right1" class="cont">
					<h1 id="countdown">155 Days to go</h1>
				</div>
				<div id="right2" class="cont">
					<h3>Links</h3>
					Facebook<br />
					Twitter
				</div>
			</div>
			<div id="centerContent" class="cont">
				<div id="content">
					<?php echo $INFOSTRING; ?>
					<?php echo $ERRORSTRING; ?>
					<?php echo $CONTENT; ?>
				</div>
			</div>
		</div>
		<div class="clearer"></div>
	</div>
    </div>
</div>



<?php 
/*

<!-- Main -->
<div id="main" class="box">

    <!-- Header -->
    <div id="header">
        <!-- Logotyp -->
        <h1 id="logo"><?php echo  $TITLE; ?></h1>
      
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
	          <?php echo  $CONTENT; ?>
            </div> <!-- /article -->
        </div> <!-- /content -->

        <!-- Right column -->
        <div id="col" class="noprint">
            <div id="col-in">
                <!-- Category -->
				<?php echo $MENUBAR;?>

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
        <p id="copyright"><?php echo $FOOTER; ?></p>
    </div> <!-- /footer -->

</div> <!-- /main -->
*/
?>
</body>
</html>
