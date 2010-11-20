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
    <link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/styles/main.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/styles/menu.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/styles/content.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/styles/error.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/../common/adminui.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/../common/other.css" />

    <link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/styles/ticker.css" />
    
    <script language="javascript" type="text/javascript" src="<?php echo  $TEMPLATEBROWSERPATH; ?>/scripts/jquery-latest.js" ></script>
    <script type="text/javascript" src="<?php echo $TEMPLATEBROWSERPATH; ?>/scripts/jquery.ticker.min.js"></script>
    <script type="text/javascript" src="<?php echo $TEMPLATEBROWSERPATH; ?>/scripts/script.js"></script>
    
  
  
    	<link media="screen" rel="stylesheet" type="text/css" href="<?php echo $TEMPLATEBROWSERPATH; ?>/epiclock/stylesheet/jquery.epiclock.css"/>
        <link media="screen" rel="stylesheet" type="text/css" href="<?php echo $TEMPLATEBROWSERPATH; ?>/epiclock/renderers/retro/epiclock.retro.css"/>
        <link media="screen" rel="stylesheet" type="text/css" href="<?php echo $TEMPLATEBROWSERPATH; ?>/epiclock/renderers/retro-countdown/epiclock.retro-countdown.css"/>
        
        
        <script type="text/javascript" src="<?php echo $TEMPLATEBROWSERPATH; ?>/epiclock/javascript/jquery.dateformat.js"></script>
        <script type="text/javascript" src="<?php echo $TEMPLATEBROWSERPATH; ?>/epiclock/javascript/jquery.epiclock.js"></script>
        <script type="text/javascript" src="<?php echo $TEMPLATEBROWSERPATH; ?>/epiclock/renderers/retro/epiclock.retro.js"></script>
        <script type="text/javascript" src="<?php echo $TEMPLATEBROWSERPATH; ?>/epiclock/renderers/retro-countdown/epiclock.retro-countdown.js"></script>
        
 
    <script language="javascript" type="text/javascript">
		//defined here for use in javascript
		var templateBrowserPath = "<?php echo $TEMPLATEBROWSERPATH ?>";
		var urlRequestRoot = "<?php echo $urlRequestRoot?>";
	</script>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-19500581-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</head>

<body>
<div class="outercontainer">
	<div class="clearer"></div>
	<div class="innercontainer">
		<div id="header">
			
			<div id="invisibleContainer">
				<a href="<?php echo $urlRequestRoot?>/home">
					<div id="invisible">Pragyan 2011</div>
				</a>
			</div>
			
			
		</div>
		<div class="clearer"></div>
		<div class="contentcontainer">
			<div class="clearer"></div>
			<div class="contentinnertube">
				<div id="leftContent">
					<div class="menucontainer">
		    				<?php echo $COMPLETEMENU; ?>
					</div> 
				</div>
				<div id="rightContent">
					
					<div id="right1" class="cont">
						<!-- Countdown dashboard start -->
						<div id="countdown_dashboard">
							 <div id="countdown-retro"></div>
							
							<h3 id='countdown'>&nbsp;&nbsp;&nbsp;... before<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Pragyan 2011</h3>
						</div>
						 <script language="javascript" type="text/javascript">
							    $(function ()
            {             
                $('#clock').epiclock();        
                $('#countdown-retro').epiclock({mode: $.epiclock.modes.countdown, time: new Date('February 18 2011, 00:00:00 GMT'), format: 'V:x:i:s', renderer: 'retro-countdown'});          
            });
				
			
							
						</script>
					<!-- Countdown dashboard end -->
					
					</div> 
					<div id="right2" class="cont">

						<h3>Links</h3>
						Facebook<br />
						Twitter
					</div>
				</div>
				<div id="centerContent" class="cont">
					<div id="ticker-wrapper" class="no-js">
						<ul id="js-news" class="js-hidden">
							<li class="news-item"><a href="#">Presenting the new FOSS events for pragyan</a></li>
							<li class="news-item"><a href="#">This time treasure hunt goes online</a></li>
							<li class="news-item"><a href="#">A site by Delta Force </a></li>
							<li class="news-item"><a href="#">A Scriptle Inc. Template</a></li>
						</ul>
					</div>
					<div class="contentContainer">
						<div id="titlebar">
							<?php echo $TITLE; ?>
						</div>
						<div id="windowmenu">
							<?php echo $ACTIONBARMODULE; ?>

						</div>
						<div id="breadcrumb">
							<div style="float:left;">
								<ul>
								<?php 
								$classNameHome = "cms-homeicon";
								global $pageId;
								if($pageId == 0)
									$classNameHome = "cms-homeselected";
								?>
								<li id="homeimg"><a href="<?php echo $urlRequestRoot; ?>"><div class="<?php echo $classNameHome; ?>">&nbsp;</div></a>
								</li></ul>
							</div>
							<div style="float:left;">
							<?php echo $BREADCRUMB; ?>
							</div>
						</div>
						<div id="content">
							<?php echo $INFOSTRING; ?>
							<?php echo $WARNINGSTRING;?>
							<?php echo $ERRORSTRING; ?>

							<?php echo $CONTENT; ?>
						</div>
						<div id="statusbar">
							<script type="text/javascript" language="javascript">document.write(location.href);</script>
						</div>
					</div>
				</div>
			</div>
			<div class="clearer"></div>
		</div>
	</div>
</div>
	<div class="footercontainer">
		<?php echo $FOOTER;?>	
	</div>
	<div id="quicklinks">
	<div id="linksid">
		<a href="http://www.facebook.com/pragyan.nitt" target="_blank"><span id="link1" class="qlinks">&nbsp;</span></a>
		<!--<a href=""><span id="link2"  class="qlinks">&nbsp;</span></a> ///To be added later. -->
		<a href=""><span id="link3" class="qlinks">&nbsp;</span></a>
		<a href="http://twitter.com/pragyan_nitt" target="_blank"><span id="link4" class="qlinks">&nbsp;</span></a>
		<a href=""><span id="link5" class="qlinks">&nbsp;</span></a>
	</div>
	<?php echo $ACTIONBARPAGE ?>
	<div id="hc_loginform">
		<?php echo $LOGINFORM; ?>
	</div>
	<!--
	*
	* Hard Coded links to profile and logout.
	*
	-->
	<div id="hc_profile">
		<a href="./+profile"><div>Profile</div></a><br/>
		<a href="./+logout"><div>Logout</div></a>
	</div>
</div>
</body>
</html>
