<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta name="DC.description" content="Pragyan 2008 : The International Technical Festival of NIT Trichy" />
<meta name="DC.creator" content="Pragyan 2008 Team" />
<meta name="DC.subject" content="Pragyan,Technical,Festival,India,Tamil Nadu,NIT,Trichy,Bytecode" />
<meta name="DC.format" content="text/html; charset=UTF-8" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?global $urlRequestRoot;	global $PAGELASTUPDATED;
if($PAGELASTUPDATED!="")
	echo '<meta http-equiv="Last-Update" content="'.substr($PAGELASTUPDATED,0,10).'" />'."\n";
?>
<link href="<?=$urlRequestRoot?>/news/+rssview" rel="alternate" type="application/rss+xml" title="Pragyan 2008 | RSS" />
<?
	global $pageId;	global $action;
	if(getEffectivePageModule($pageId)=='article' && ($action=='profile' || $action=='view' || $action=='login'||$action=='logout'))
		include("$TEMPLATECODEPATH/sidebar.php");
	else $SIDEBARCONTENT='';
	if($action!='view' && $action!='edit' && $action!='profile' && $action!='login' && $action != 'logout')
		$MENUBAR = '';
	$pageStyle="";
	if($SIDEBARCONTENT!="" || $MENUBAR != "")
		$pageStyle=" <link rel=\"stylesheet\" href=\"$TEMPLATEBROWSERPATH/style-rightbar.css\" />";
 ?>
  <title><?= $TITLE ?></title>
	<!--[if lt IE 7]><script defer type="text/javascript" src="<?= $TEMPLATEBROWSERPATH ?>/../common/scripts/pngfix.js"></script><![endif]-->
	<!--[if lt IE 7]>
	  	<style type="text/css">
		  #title_image { margin-right: -6px;}
		  #title_image { margin-bottom: -3px;}
		  .clearer {margin-bottom: -3px;}
	   	</style>
	<![endif]-->
  <script language="javascript" type="text/javascript">
		//defined here for use in javascript
		var templateBrowserPath = "<?=$TEMPLATEBROWSERPATH ?>";
		var urlRequestRoot = "<?=$urlRequestRoot?>";
  </script>
  <link rel="stylesheet" href="<?= $TEMPLATEBROWSERPATH ?>/style.css" />
  <link rel="stylesheet" href="<?= $TEMPLATEBROWSERPATH ?>/../common/error.css" />
  <?= $pageStyle ?>
  <!--<script language="javascript" type="text/javascript" src="<?= $TEMPLATEBROWSERPATH ?>/../common/scripts/ftod.js"></script>-->
  <!--  //  window.onload = function() { AddFillerLink("header", "left", "main", "sidebar", "footer"); }  -->
  <!-- <script language="javascript" type="text/javascript" src="<?= $TEMPLATEBROWSERPATH ?>/../common/scripts/header.js"></script> -->
  <script language="javascript" type="text/javascript" src="<?= $TEMPLATEBROWSERPATH ?>/../common/scripts/mootools-1.11-allCompressed.js"></script>
  <script language="javascript" type="text/javascript" src="<?= $TEMPLATEBROWSERPATH ?>/page.js"></script>
  <link rel="stylesheet" type="text/css" href="<?=$TEMPLATEBROWSERPATH ?>/pragyanmenu.css" media="all" />
  <script type="text/javascript" language="javascript" src="<?=$TEMPLATEBROWSERPATH ?>/pragyanmenu.js"></script>

  <? $STARTSCRIPTS .= "pragyanMenuInit();"; ?>
  <script language="javascript" type="text/javascript">
  	function loadScript() {
  		//startPix();
  		<?= $STARTSCRIPTS ?>
  	}
  </script>
</head>

<body onload="loadScript()">
  <div id="outer_wrapper">
    <div id="wrapper">
      <div id="header">
      	<span class="title_page">
			<a  href="<?= $urlRequestRoot?>">
				<img id="title_image" src="<?= $TEMPLATEBROWSERPATH?>/img/pragyan3.gif" alt="Pragyan 08" border="0"/>
			</a>
		</span>
<?  require_once("news.php");
	echo displayNews();
	global $userId;
	if($userId==0) require_once("login.php");
?>
      </div>
      <? include("pragyanmenu.php");?>
      <div id="container">
        <div id="left">

        </div><!-- /left -->

        <div id="main">
      	  <?=$BREADCRUMB?>
			<?=(($userId==0)?"":$ACTIONBARPAGE)?>
			<?=(($userId==0)?"":$ACTIONBARMODULE)?>
			  <span class="clearer"></span>
          <div id="content">

          <?=$INFOSTRING?>
          <?=$WARNINGSTRING?>
          <?=$ERRORSTRING?>
          <?=$CONTENT?>
          </div>
        </div><!-- /main -->
        <!-- This is for NN6 -->
        <!--<div class="clearing">&nbsp;</div>-->
      </div><!-- /container -->
      <div id="sidebar">
      	<?=$MENUBAR?>
        <?//=$SIDEBARCONTENT ?>
      </div><!-- /sidebar -->
      <!-- This is for NN4 -->
      <div class="clearing">&nbsp;</div>
      <div id="footer">
      <div class="subfooter">
<p ></p>
</div>
<div id="footerbar">
<span class="left">&copy; 2008 <a href="http://www.pragyan.org">Pragyan Team</a>. All Rights Reserved</span>
<div class="right">
<a href="<?= $urlRequestRoot?>/sitemap">Sitemap</a>
<a href="/sitemap.xml" style="position:relative;padding-top:5px;"><img src="<?= $TEMPLATEBROWSERPATH?>/img/xml-small.png"></a>
</div>
<a class="right" href="http://nitt.edu">NIT Trichy</a>
<a class="right" href="<?= $urlRequestRoot?>/contacts">Contacts</a>

<a class="right" style="padding-top:10px;" href="<?=$urlRequestRoot?>/news/+rssview"><img src="<?= $TEMPLATEBROWSERPATH?>/img/rss.png"></a>
<? 	$BlogItemPermalinkURL = "http://pragyan.org";
	$BlogItemTitle = "Pragyan : The International Technical Festival of NIT Trichy";
?>
<div class="right" style="position:relative;left:-13px;padding-top:4px;">
<a href="http://del.icio.us/post?url=<?=$BlogItemPermalinkURL?>&amp;title=<?=$BlogItemTitle?>" alt="Add Pragyan.org to delicious" >
<img src="<?= $TEMPLATEBROWSERPATH?>/img/delicious.gif"></a>&nbsp;
<a href="http://digg.com/submit?phase=2&amp;url=<?=$BlogItemPermalinkURL?>&amp;title=<?=$BlogItemTitle?>" alt="Add Pragyan.org to digg" >
<img src="<?= $TEMPLATEBROWSERPATH?>/img/digg.gif"></a>&nbsp;
<a href="http://reddit.com/submit?url=<?=$BlogItemPermalinkURL?>&amp;title=<?=$BlogItemTitle?>" alt="Add Pragyan.org to reddit" >
<img src="<?= $TEMPLATEBROWSERPATH?>/img/reddit.gif"></a>&nbsp;
<a href="http://www.furl.net/storeIt.jsp?u=<?=$BlogItemPermalinkURL?>&amp;t=<?=$BlogItemTitle?>" alt="Add Pragyan.org to furl" >
<img src="<?= $TEMPLATEBROWSERPATH?>/img/furl.gif"></a>&nbsp;
<a href="http://www.google.com/bookmarks/mark?op=edit&amp;bkmk=<?=$BlogItemPermalinkURL?>&amp;title=<?=$BlogItemTitle?>">
<img src="<?= $TEMPLATEBROWSERPATH?>/img/google.png"></a>&nbsp;
<a href="http://www.facebook.com/sharer.php?u=<?=$BlogItemPermalinkURL?>&t=<?=$BlogItemTitle?>">
<img src="<?= $TEMPLATEBROWSERPATH?>/img/facebook.gif"></a>&nbsp;
<a href="http://www.stumbleupon.com/submit?url=<?=$BlogItemPermalinkURL?>&amp;title=<?=$BlogItemTitle?>">
<img src="<?= $TEMPLATEBROWSERPATH?>/img/stumbleupon.gif"></a>&nbsp;
</div>
<!--<div class="right" >Bookmark Us :</div>-->
</div><!--/Footer bar-->

      </div><!-- /footer -->
    </div><!-- /wrapper -->
  </div><!-- /outer_wrapper -->
</body>

</html>
