<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}
?>
<?php
global $SIDEBARCONTENT;
$date = date("D d M y, h:ia");
$date .= " IST";
$SIDEBARCONTENT = '<div id="sidebardate">'.$date.'</div>
		<div class="quicklinks">
				<h6>Quick Links</h6>				
				<ul>
					<li><a href="/icsr/teqip">TEQIP</a></li>

					<li><a href="http://connectnit.org" target="_blank">Connect NIT</a></li>
					<li><a href="/students/facilitiesnservices">Facilities And Services</a></li>
				      	<li><a href="academics/departments">Departments</a></li>
				      	<li><a href="/icsr/teqip/visitingprof">Visiting Proffesors Under TEQIP</a></li>
					<li>Achievements</li>
				</ul>

			</div>
			<div class="quicklinks">
				<h6>Events @ NITT</h6>				
				<ul>' .
						'<li><a href=""></a></li>
				</ul>
			</div>

';
?>
		

