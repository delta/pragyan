<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<?
	$pageStyle="";
	if($MENUBAR!="")	$pageStyle=" <link rel=\"stylesheet\" href=\"$TEMPLATEBROWSERPATH/../common/style-leftbar.css\" />";
	$SIDEBARCONTENT=file_get_contents("$sourceFolder/$templateFolder/common/sidebar.php");
	if($SIDEBARCONTENT!="")
		if($pageStyle=="") $pageStyle=" <link rel=\"stylesheet\" href=\"$TEMPLATEBROWSERPATH/../common/style-rightbar.css\" />";
		else	$pageStyle=" <link rel=\"stylesheet\" href=\"$TEMPLATEBROWSERPATH/../common/style-bothbars.css\" />";
/*&$TITLE,&$MENUBAR,&$TEMPLATEBROWSERPATH,&$ACTIONBARMODULE,&$ACTIONBARPAGE,&$BREADCRUMB,
 * &$MENUBAR,&$CONTENT,&$DEBUGINFO,&$ERRORSTRING,&$WARNINGSTRING,&$INFOSTRING */ ?>
  <title><?= $TITLE ?></title>
  <!--<link rel="stylesheet" href="{template_path}style.css" />-->
  <link rel="stylesheet" href="<?= $TEMPLATEBROWSERPATH ?>/style.css" />
  <?= $pageStyle ?>
  <link rel="stylesheet" href="<?= $TEMPLATEBROWSERPATH ?>/../common/other.css" />
  <link rel="stylesheet" href="<?= $TEMPLATEBROWSERPATH ?>/../common/style.css" />

  <script language="javascript" type="text/javascript" src="<?= $TEMPLATEBROWSERPATH ?>/../common/scripts/ftod.js"></script>
  <script language="javascript" type="text/javascript">
  <!--
  //  window.onload = function() {
  //    AddFillerLink("header", "left", "main", "sidebar", "footer");
  //  }
  -->
  </script>
</head>

<body>
  <div id="outer_wrapper">
    <div id="wrapper">
      <div id="header">
        <h2>Pragyan '08</h2>
      </div><!-- /header -->
      <div id="container">
        <div id="left">
           <?=$MENUBAR?>
        </div><!-- /left -->

        <div id="main">
      	  <?=$BREADCRUMB?>
			<?=$ACTIONBARPAGE?>
			<?=$ACTIONBARMODULE?>
          <div id="content"">
          <?=$ERRORSTRING?>
          <?=$INFOSTRING?>
          <?=$WARNINGSTRING?>
          <?=$CONTENT?>
          </div>
        </div><!-- /main -->
        <!-- This is for NN6 -->
        <div class="clearing">&nbsp;</div>
      </div><!-- /container -->
      <div id="sidebar">
        <?=$SIDEBARCONTENT?>
      </div><!-- /sidebar -->
      <!-- This is for NN4 -->
      <div class="clearing">&nbsp;</div>

      <div id="footer">
        <h2>Pragyan 08</h2>
      </div><!-- /footer -->
    </div><!-- /wrapper -->
  </div><!-- /outer_wrapper -->
</body>

</html>
