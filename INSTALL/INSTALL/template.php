<?php
/*
 * Created on Sep 28, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="cs" lang="cs">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />

		<title>Pragyan CMS v3 - Installation</title>

		<link rel="index" href="./" title="Home" />
		<link rel="stylesheet" media="screen,projection" type="text/css" href="<?= $templateFolder ?>/css/main.css" />
		<link rel="stylesheet" media="print" type="text/css" href="<?= $templateFolder ?>/css/print.css" />
		<link rel="stylesheet" media="aural" type="text/css" href="<?= $templateFolder ?>/css/aural.css" />
	</head>

	<body>
		<!-- Main -->
		<div id="main" class="box">

			<!-- Header -->
			<div id="header">
				<!-- Logotyp -->
				<h1 id="logo">Pragyan CMS Installation</h1>
				<hr class="noscreen" />
			</div> <!-- /header -->

			<!-- Main menu (tabs) -->
			<div id="tabs" class="noprint">
				<h3 class="noscreen">Installation Progress</h3>
				<ul class="box">
					<li <?php echo ($installPageNumber == 1 ? 'id="active"' : ''); ?>><a style="cursor: default">Prerequisites<span class="tab-l"></span><span class="tab-r"></span></a></li>
					<li <?php echo ($installPageNumber == 2 ? 'id="active"' : ''); ?>><a style="cursor: default">Configuration<span class="tab-l"></span><span class="tab-r"></span></a></li> <!-- Active -->
					<li <?php echo ($installPageNumber == 3 ? 'id="active"' : ''); ?>><a style="cursor: default">Finalize Installation<span class="tab-l"></span><span class="tab-r"></span></a></li>
				</ul>

				<hr class="noscreen" />
			</div> <!-- /tabs -->

			<!-- Page (2 columns) -->
			<div id="page" style="background: url(images/bg_instpage.jpg); background-repeat: repeat-y; background-color: transparent;">
				<div id="page-in" style="min-height: 12px; padding-left: 24px; padding-right: 24px; background-image: url(images/bg_instpage_in.jpg); background-repeat: no-repeat">

				<?php echo $installPageContent; ?>

				</div> <!-- /page-in -->
			</div> <!-- /page -->

			<!-- Footer -->
			<div id="footer" style="text-align: right; color: #AEAEB8; background: url(images/inst_footer.jpg)">
				<span style="float: right; padding-right: 32px">Pragyan CMS v3 Installation</span>
			</div> <!-- /footer -->

		</div> <!-- /main -->
	</body>
</html>
