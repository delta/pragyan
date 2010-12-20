<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="cs" lang="cs">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

    <title><?php echo $TITLE; ?></title>
    <meta name="description" content="<?php echo $SITEDESCRIPTION ?>" />
    <meta name="keywords" content="<?php echo $SITEKEYWORDS ?>" /> 
	<?php global $urlRequestRoot;	global $PAGELASTUPDATED;
	if($PAGELASTUPDATED!="")
		echo '<meta http-equiv="Last-Update" content="'.substr($PAGELASTUPDATED,0,10).'" />'."\n";
	?>
    <link rel="index" href="./" title="Home" />
<link rel="shortcut icon" href="http://pragyan.org/favicon.ico" type="image/x-icon">

  <link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/../common/adminui.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/../common/other.css" />

    <link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/styles/main.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/styles/menu.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/styles/content.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/styles/error.css" />

    <link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/styles/ticker.css" />
    
    <script language="javascript" type="text/javascript" src="<?php echo  $TEMPLATEBROWSERPATH; ?>/scripts/jquery-latest.js" ></script>
    <script type="text/javascript" src="<?php echo $TEMPLATEBROWSERPATH; ?>/scripts/jquery.ticker.min.js"></script>
    <script type="text/javascript" src="<?php echo $TEMPLATEBROWSERPATH; ?>/scripts/script.js"></script>
    <script language="JavaScript">
			TargetDate = "02/17/2011 00:00 AM";
			BackColor = "none";
			ForeColor = "white";
			CountActive = true;
			CountStepper = -1;
			LeadingZero = true;
			DisplayFormat = "%%D%% Days, %%H%% Hours, <br>%%M%% Minutes, %%S%% Seconds.";
			FinishMessage = "Day 0 pragyan";
		</script>
 
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

<body onload="<?php echo $STARTSCRIPTS; ?>" >
<div class="outercontainer">
	<div class="clearer"></div>
	<div class="innercontainer">
		<div id="header">
			
			<div id="invisibleContainer">
				<a href="<?php echo $urlRequestRoot?>">
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
						<div id="myCountDown">
						<script language="JavaScript" src="<?php echo $TEMPLATEBROWSERPATH; ?>/scripts/countdown.js"></script>
						</div>
					<!-- Countdown dashboard end -->
					
					</div> 
					<div id="right2" class="cont"> </div>
					<div id="right2" class="cont">
					<h4>ETA Pragyan 2011</h4>				
					</div>
					
					<div id="right3" class="cont">
					
						<h3>Links</h3>
						<div class="link"><a href="http://www.pragyan.org/11/home/making_of_pragyan_wheel">Video : Making of the Pragyan Wheel</a></div>
						<div class="link"><a href="http://www.pragyan.org/blog">Pragyan Blog</a></div>
						<div class="link"><a href="http://www.youtube.com/user/nittpragyan">Pragyan's Youtube Channel</a></div>
						<div class="link"><a href="http://www.pragyan.org/10">Pragyan 2010</a></div>
						<div class="link"><a href="http://www.pragyan.org/09">Pragyan 2009</a></div>
					</div>
					
				</div>
				<div id="centerContent" class="cont">
					<div id="ticker-wrapper" class="no-js">
					
					 <?php if(isset($WIDGETS[1])) echo $WIDGETS[1]; ?>
					
						<ul id="js-news" class="js-hidden">
<li class="news-item"><a href="http://www.pragyan.org/11/home/events/engineering_tomorrow/sorption/">Event format and deadline for Sorption updated !</a></li>							
<li class="news-item"><a href="#">Pragyan 2011 is coming soon! Stay tuned.</a></li>
<li class="news-item"><a href="http://www.pragyan.org/11/home/events/openit/">Event Format, Rules and Regulations for OpeNIT updated !!!</a></li>
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

		<a href="http://www.facebook.com/pragyan.nitt" target="_blank" title="Facebook"><span id="link1" class="qlinks">&nbsp;</span></a>
		<a href="http://www.stumbleupon.com/submit?url=http://www.pragyan.org&title=Pragyan%20:%20The%20International%20Technical%20Festival%20of%20NIT%20Trichy" target="_blank" title="Stumbleupon"><span id="link2"  class="qlinks">&nbsp;</span></a> 
		<a href="http://www.pragyan.org/mail" target="_blank" title="Mail"><span id="link3" class="qlinks">&nbsp;</span></a>
		<a href="http://twitter.com/pragyan_nitt" target="_blank" title="Twitter"><span id="link4" class="qlinks" >&nbsp;</span></a>
		<a href="http://www.pragyan.org/11/home/news/+rss" target="_blank" title="RSS"><span id="link5" class="qlinks">&nbsp;</span></a>
		<a href="http://digg.com/submit?phase=2&url=http://www.pragyan.org&title=Pragyan%20:%20The%20International%20Technical%20Festival%20of%20NIT%20Trichy" target="_blank" title="Digg"><span id="link6" class="qlinks">&nbsp;</span></a>
		<a href="http://www.furl.net/storeIt.jsp?u=http://www.pragyan.org&t=Pragyan%20:%20The%20International%20Technical%20Festival%20of%20NIT%20Trichy" target="_blank" title="Furl"><span id="link7" class="qlinks">&nbsp;</span></a>
		<a href="http://www.google.com/bookmarks/mark?op=edit&bkmk=http://www.pragyan.org&title=Pragyan%20:%20The%20International%20Technical%20Festival%20of%20NIT%20Trichy" target="_blank" title="Google"><span id="link8" class="qlinks">&nbsp;</span></a>
		<a href="http://del.icio.us/post?url=http://www.pragyan.org&title=Pragyan%20:%20The%20International%20Technical%20Festival%20of%20NIT%20Trichy" target="_blank" title="Delicious"><span id="link9" class="qlinks">&nbsp;</span></a>
		<a href="http://reddit.com/submit?url=http://www.pragyan.org&title=Pragyan%20:%20The%20International%20Technical%20Festival%20of%20NIT%20Trichy" target="_blank" title="Reddit"><span id="link10" class="qlinks">&nbsp;</span></a>

	</div>
	<?php echo $ACTIONBARPAGE ?>
<?php                                                                           
global $action;                                                                 
if($action!='login'){                                                           
 /*show the form only if the page is not a loginpage*/                          
?>                                                                              
        <div id="hc_loginform">                                                 
        <a href="+login" id="pragyanLogin">Pragyan Login</a><a href="+login" id="openidLogin">Open-id Login</a>                                                

        <?php echo $LOGINFORM;?>                                                
        </div>    
<script>
	$('#openid_form *').hide();
</script>                                                              
<?php }?>    
	<!--
	* Hard Coded links to profile and logout.
	-->
	<div id="hc_profile">
		<a href="./+profile"><div>Profile</div></a><br/>
		<a href="./+logout"><div>Logout</div></a>
	</div>
</div>
</body>
</html><div>
