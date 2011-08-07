<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}
?>
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
    <script type="text/javascript" src="<?php echo $TEMPLATEBROWSERPATH; ?>/scripts/easynews.js"></script>
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
	<div id="exthead">
		<div class="extendedContainer">
			<?php
				$pageName = "extend";
				$getQ = "SELECT page_modulecomponentid FROM ". MYSQL_DATABASE_PREFIX ."pages WHERE page_name='extend'";
				$componentTempId = mysql_fetch_assoc(mysql_query($getQ));
				$query = "SELECT article_content FROM article_content WHERE page_modulecomponentid=" . $componentTempId['page_modulecomponentid'];
				$res = mysql_query($query);
				while($r = mysql_fetch_assoc($res)) {
					echo $r['article_content'];
				}
			?>
		</div>
	</div>
	<div class="innercontainer">
	
		<div id="header">
			
			<div id="invisibleContainer">
			<!--
				<svg style="position: absolute; width: 150px; height: 180px;opacity:0.5">
	    	 		<rect x="23" y="23" height="155" width="105" style="fill: #000; opacity: 0.2" />
    	 	   
			 		<path id="pragyan_legs" d="M114 117
			 		c5 -25 -30 -14 -50 5
			 		c-10 10 -25 25 -24 42
			 		c4 -15 10 -25 28 -40
			 		c20 -15 40 -20 45 -6
			 		z
			 		" style="fill: #000;stroke:blue;stroke-width:1;opacity: 0.71" />
			 		
			 		<path id="pragyan_wheel" d="M92 116
			 		c-31 5 -31 43 0 47
			 		c31 -5 31 -43 0 -47
			 		z" style="fill: #999;stroke: #fedcba; stroke-width:1;" />
			 		
				</svg>
			-->
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
						<div class="toptoptop">
						&nbsp;
						</div>
		    				<?php echo $MENUBAR; ?>
		    			<div class="bottombottombottom">
		    			&nbsp;
		    			</div>
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
					<?php if(isset($WIDGETS[3])) echo $WIDGETS[3]; ?>
					<div id="right3" class="cont Rblue">
					<h3>Partners</h3>
					<link rel="stylesheet" type="text/css" href="<?php echo  $TEMPLATEBROWSERPATH; ?>/styles/slider.css" />
					<script type="text/javascript" src="<?php echo $TEMPLATEBROWSERPATH; ?>/scripts/s3Slider.js"></script>
			    <script type="text/javascript">
			    $(document).ready(function() {
      		 $('#slider').s3Slider({
          	  timeOut: 3000
       			 });
    			});
 			</script>
	 <div id="slider">
	 <ul id="sliderContent">
  <li class="sliderImage">
                <a href="http://www.murugappa.com/"><img src="http://www.pragyan.org/11/home/murugappa.jpg" alt="10" /></a><span></span>

            </li>
  <li class="sliderImage">
                <a href="http://www.codechef.com/goforgold"><img src="http://www.pragyan.org/11/home/gfg%20updated.jpg" alt="6" /></a><span></span>

            </li>

 <li class="sliderImage">
                <a href="http://www.mcafee.com/"><img src="http://www.pragyan.org/11/home/mcafee%20updated.jpg" alt="9" /></a><span></span>

            </li>

            <li class="sliderImage">
                <a href="http://bhash.in/"><img src="http://www.pragyan.org/11/home/bhash%20updated.jpg" alt="1" /></a><span></span>
            </li>
            <li class="sliderImage">
                <a href="http://www.bies.co.in/"><img src="http://www.pragyan.org/11/home/bies%20updated.jpg" alt="2" /></a><span></span>

            </li>
            <li class="sliderImage">
                <a href="http://www.dhl.co.in/"><img src="http://www.pragyan.org/11/home/dhl%20updated.jpg" alt="3" /></a><span></span>

            </li>
            <li class="sliderImage">
                <a href="http://www.ethosindia.in/"><img src="http://www.pragyan.org/11/home/ethos%20updated.jpg" alt="4" /></a><span></span>

            </li>
             <li class="sliderImage">
                <a href="http://freshersworld.com/"><img src="http://www.pragyan.org/11/home/freshersworld%20updated.jpg" alt="5" /></a><span></span>

            </li>
             <li class="sliderImage">
                <a href="http://www.landmarkonthenet.com/"><img src="http://www.pragyan.org/11/home/landmark%20updated.jpg" alt="7" /></a><span></span>

            </li>
            <li class="sliderImage">
                <a href="http://markmyfest.com/"><img src="http://www.pragyan.org/11/home/mark%20my%20fest%20updated.jpg" alt="8" /></a><span></span>

            </li>
            <li class="sliderImage">
                <a href="http://www.thinkdigit.com/"><img src="http://www.pragyan.org/11/home/thinkdigit%20updated.jpg" alt="10" /></a><span></span>

            </li>
             <li class="sliderImage">
                <a href="http://www.btechguru.com/"><img src="http://www.pragyan.org/11/home/btechguru.jpg" alt="10" /></a><span></span>

            </li>
            <div class="clear sliderImage"></div>
        </ul>
    </div>
    
    
   <div class="cont Rblue">
<h3>News</h3>

	<div id="updatestab" class="ui-tabs-panel">
						
						
						<div style="display: none;" id="newscontainer">
						
						<div class="news_style" rel="Das Kapital Prelims" id="news0"><h4><a href="http://www.pragyan.org/10/home/events/managing_technology/das_kapital">Das Kapital Prelims</a></h4>  <img 110="" src="<?php echo $TEMPLATEBROWSERPATH; ?>/images/controls.png" alt="Das Kapital" style="margin: 0pt 6px;" width="70" height=" align="left"><p>Online prelims of Das Kapital is now open! Click<a href="http://www.pragyan.org/10/home/events/managing_technology/das_kapital/prelims/"> here</a> to take the quiz.</p></div>
						
						<div class="news_style" rel="Bytecode Registrations" id="news1"><h4><a href="http://www.pragyan.org/10/home/events/encypher/bytecode/">Bytecode Registrations</a></h4><img src="<?php echo $TEMPLATEBROWSERPATH; ?>/images/controls.png" style="margin: 8px;" align="left"><p>Registrations for <a href="http://www.pragyan.org/10/home/events/encypher/bytecode/">Bytecode</a>, the algorithm intensive online programming contest have been opened!</p>
<p>The prize structure has been announced!</p></div>

						</div>


			<div id="newsouterbar">
				<div id="newsbarwrapper">
				
				<div style="display: none;" id="newsbar"><h4><a href="http://www.pragyan.org/10/home/highlights/jagriti">Jagriti Registrations</a></h4>     <img 110="" src="<?php echo $TEMPLATEBROWSERPATH; ?>/images/controls.png" alt="Jagriti" style="margin: 0pt 6px;" width="70
 height=" align="left">
			<p>Registrations for the social consciousness event <a href="http://www.pragyan.org/10/home/highlights/jagriti/"> Jagriti </a>is now open! Click <a href="http://www.pragyan.org/10/home/highlights/jagriti/register/">here </a>to register!</p></div> 
			
			
			</div>
				<div class="buttondiv" id="newsbuttons">
					<img src="<?php echo $TEMPLATEBROWSERPATH; ?>/images/prev.gif" id="news_prev" align="absmiddle"><img src="<?php echo $TEMPLATEBROWSERPATH; ?>/images/pause.gif" id="news_pause" align="absmiddle"><img src="<?php echo $TEMPLATEBROWSERPATH; ?>/images/next0.gif" id="news_next" align="absmiddle">
				</div>
				<div id="newsbartitle">Now Viewing: 5/12<br>Next: Reverse Engineering - Ada'Venture</div>
				<div class="clearer"></div>
			</div>
		</div>
	</div>
<div class="Rblue"><h3>
<a href="http://www.pragyan.org/11/home/schedule.xlsx"><div class="link">Schedule</div></a>
</h3></div>	
				
<div style='clear:both;'>
 <div class='rtbt' id='rt_bt_links'>
 	 <h3>Links</h3>
 </div> 
 <div class='rtbt' id='rt_bt_downloads'>
	<h3>Downloads</h3>
 </div> 
 <div class='rtbt' id='rt_bt_forums'>
	<h3>Forums</h3>
 </div>
</div>

<div class='clearer'></div>

<div id='rt_topic_links'>
<a href="http://www.pragyan.org/11/home/publicity_video">
                                                <div class="link">Video : Pragyan 2011 Publicity Video</div></a>

						<a href="http://www.pragyan.org/11/home/making_of_pragyan_wheel">
						<div class="link">Video : Making of the Pragyan Wheel</div></a>
						<a href="http://www.pragyan.org/blog"><div class="link">Pragyan Blog</div></a>
						<a href="http://www.youtube.com/user/nittpragyan"><div class="link">Pragyan's Youtube Channel
						</div></a>
						<a href="http://www.pragyan.org/10"><div class="link">Pragyan 2010</div></a>
						<a href="http://www.pragyan.org/09"><div class="link">Pragyan 2009</div></a>
</div>
<div id='rt_topic_downloads'>
<a href="http://www.pragyan.org/11/home/guest_lectures/GL-Brochure1.pdf"><div class="link">Pragyan Guest Lectures Brochure</div></a>
</div>
<div id='rt_topic_forums'>
<a href="http://www.pragyan.org/11/home/forum"><div class="link">Pragyan Main Forum</div></a>
<a href="http://www.pragyan.org/11/home/events/brain_work/labyrinth/labyrinth_forum/"><div class="link">Labyrinth Forum</div></a>
</div>

</div>
					<?php if(isset($WIDGETS[4])) echo $WIDGETS[4]; ?>
<div class="clearer"></div>



					<div id="right4" class="cont Rblue">

    <h3>KeyBoard Shortcuts <a id='kbd_disp' style='float:right;cursor:pointer'>[+]</a></h3>
<div id='kbd_shortcuts' style='display:none;'>
						<address>CTRL + ALT + K = Enable / Disable Keyboard Shortcuts</address>
						<br />
						<ul style="list-style:none;margin:0; padding: 0;">
						<li>CTRL + H = Home</li>
						<li>CTRL + E = Events</li>
						<li>CTRL + W = Workshops</li>
						<li>CTRL + Q = Quicklinks</li>
						<li>CTRL + S = Save Page as PDF</li>
						<li>CTRL + L = Login</li>
						<li>CTRL + R = Register</li>
						</ul>
</div>
					</div>
					
				</div>
				<div id="centerContent" class="cont">
					<div id="ticker-wrapper" class="no-js">
					
					 <?php if(isset($WIDGETS[1])) echo $WIDGETS[1]; ?>
					
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
<?php if(isset($WIDGETS[2])) echo $WIDGETS[2]; ?>
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
<a href="/11/home/contacts" style="float:left">Contact Us</a>		<?php echo $FOOTER;?>	<a href="/11/home/feedback" style="float:right">Feedback</a>
	</div>
	<div id="quicklinks">
	<div id="linksid">

		<a href="http://www.facebook.com/pragyan.nitt" target="_blank" title="Facebook"><span id="link1" class="qlinks">&nbsp;</span></a>
		<a href="http://www.stumbleupon.com/submit?url=http://www.pragyan.org&title=Pragyan%20:%20The%20International%20Technical%20Festival%20of%20NIT%20Trichy" target="_blank" title="Stumbleupon"><span id="link2"  class="qlinks">&nbsp;</span></a> 
		<a href="http://www.pragyan.org/mail" target="_blank" title="Mail"><span id="link3" class="qlinks">&nbsp;</span></a>
		<a href="http://twitter.com/pragyan_nitt" target="_blank" title="Twitter"><span id="link4" class="qlinks" >&nbsp;</span></a>
		<a href="http://www.pragyan.org/11/home/news/+rssview" target="_blank" title="RSS"><span id="link5" class="qlinks">&nbsp;</span></a>
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
        <div id="login_menu">                                               
        <a href="+login" id="pragyanLogin"><div>Pragyan Login</div></a><a href="+login" id="openidLogin"><div>Open-id Login</div></a>                                                
		</div>
		<div id="login_actual_form">
        <?php echo $LOGINFORM;?>    
        </div>                                            
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
	<div class="extendHeadLink">
			<a href="./" id="extendHead" >
			<div class="extendHead">
			<img src="<?php echo $TEMPLATEBROWSERPATH; ?>/../common/icons/16x16/actions/go-down.png" />
			Quicklinks
			</div></a>
	</div>
</div>
</body>
</html><div>
