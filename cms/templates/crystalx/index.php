<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="cs" lang="cs">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title><?= $TITLE ?></title>
    <meta name="description" content="The official website of the Rotaract Club of NIT Trichy" />
    <meta name="keywords" content="Rotaract Club, National Institute of Technology, Tiruchirappalli, Trichy" />
	<?global $urlRequestRoot;	global $PAGELASTUPDATED;
	if($PAGELASTUPDATED!="")
		echo '<meta http-equiv="Last-Update" content="'.substr($PAGELASTUPDATED,0,10).'" />'."\n";
	?>
    <link rel="index" href="./" title="Home" />
    <link rel="stylesheet" media="screen,projection" type="text/css" href="<?= $TEMPLATEBROWSERPATH ?>/css/main.css" />
    <link rel="stylesheet" media="print" type="text/css" href="<?= $TEMPLATEBROWSERPATH ?>/css/print.css" />
    <link rel="stylesheet" media="aural" type="text/css" href="<?= $TEMPLATEBROWSERPATH ?>/css/aural.css" />
    <link rel="stylesheet" type="text/css" href="<?= $TEMPLATEBROWSERPATH ?>/css/other.css" />
    <link rel="stylesheet" type="text/css" href="<?= $TEMPLATEBROWSERPATH ?>/../common/error.css" />

	<script language="javascript" type="text/javascript">
		//defined here for use in javascript
		var templateBrowserPath = "<?=$TEMPLATEBROWSERPATH ?>";
		var urlRequestRoot = "<?=$urlRequestRoot?>";
	</script>
</head>

<body>

<!-- Main -->
<div id="main" class="box">

    <!-- Header -->
    <div id="header">
        <!-- Logotyp -->
        <h1 id="logo"><?= $TITLE ?></h1>
      
    </div> <!-- /header -->

    <!-- Page (2 columns) -->
    <div id="page" class="box">
    <div id="page-in" class="box">
        <div id="strip" class="box noprint">
            <!-- Breadcrumbs -->
            <div id="breadcrumbs">
	           	<?= $BREADCRUMB ?>

				<?=$ACTIONBARPAGE?>
				<?=$ACTIONBARMODULE?>
            </div>
            <hr class="noscreen" />
            
        </div> <!-- /strip -->

        <!-- Content -->
        <div id="content">

            <!-- Article -->
            <div class="article">
              <?=$INFOSTRING?>
	          <?=$WARNINGSTRING?>
	          <?=$ERRORSTRING?>
	          <?= $CONTENT ?>
            </div> <!-- /article -->
        </div> <!-- /content -->

        <!-- Right column -->
        <div id="col" class="noprint">
            <div id="col-in">
                <!-- Category -->
				<?=$MENUBAR?>

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
        <p id="copyright">&copy; 2010 - powered by <a href="http://sourceforge.net/projects/pragyan" title="Praygan CMS">Pragyan CMS v3.0</a></p>
    </div> <!-- /footer -->

</div> <!-- /main -->

</body>
</html>
