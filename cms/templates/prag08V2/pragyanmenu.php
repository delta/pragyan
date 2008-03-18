
<? 	global $urlRequestRoot; global $pageFullPath;
   	$url = rtrim($pageFullPath, '/');
	$urlPieces = explode('/', $url);
	$pageRoot = $urlPieces[1];
?>
<div id="topmenu">
		<a id="Events" href="<?=$urlRequestRoot?>/events" <?= $pageRoot=="events"?'class="menucurrent"':''?>> Events</a>
		<a id="GuestLectures" href="<?=$urlRequestRoot?>/guestlectures" <?= $pageRoot=="guestlectures"?'class="menucurrent"':''?>>Guest Lectures</a>
		<a id="Jagriti" href="<?=$urlRequestRoot?>/jagriti" <?= $pageRoot=="jagriti"?'class="menucurrent"':''?>>Jagriti</a>
		<a id="Workshops" href="<?=$urlRequestRoot?>/workshops" <?= $pageRoot=="workshops"?'class="menucurrent"':''?>>Workshops</a>
		<a id="Infotainment" href="<?=$urlRequestRoot?>/infotainment" <?= $pageRoot=="infotainment"?'class="menucurrent"':''?>>Infotainment</a>
        <a id="OtherLinks" href="<?=$urlRequestRoot?>/otherlinks">Other Links</a>
</div>

<script type="text/javascript" language="javascript">
		// set up drop downs anywhere in the body of the page. I think the bottom of the page is better..
	    // but you can experiment with effect on loadtime.
	    if (PragyanMenu.isSupported()) {
<?/*	        //==================================================================================================
	        // create a set of dropdowns
	        //==================================================================================================
	        // the first param should always be down, as it is here
	        //
	        // The second and third param are the top and left offset positions of the menus from their actuators
	        // respectively. To make a menu appear a little to the left and bottom of an actuator, you could use
	        // something like -5, 5
	        //
	        // The last parameter can be .topLeft, .bottomLeft, .topRight, or .bottomRight to inidicate the corner
	        // of the actuator from which to measure the offset positions above. Here we are saying we want the
	        // menu to appear directly below the bottom left corner of the actuator
	        //==================================================================================================
*/?>
	        var ms = new PragyanMenuSet(PragyanMenu.direction.down, 1, 0, PragyanMenu.reference.bottomLeft);

<?/*	    //==================================================================================================
	        // create a dropdown menu
	        //==================================================================================================
	        // the first parameter should be the HTML element which will act actuator for the menu
	        //==================================================================================================
*/?>	    var menu1 = ms.addMenu(document.getElementById("Events"));

	        menu1.addItem("Engineering Sciences", urlRequestRoot+"/events/engsc/");
	        menu1.addItem("Management", urlRequestRoot+"/events/management/");
			menu1.addItem("Coding", urlRequestRoot+"/events/coding/");
			menu1.addItem("Robovigyan", urlRequestRoot+"/events/robovigyan/");
			menu1.addItem("Innovation", urlRequestRoot+"/events/innovation/");
			menu1.addItem("Brainwork", urlRequestRoot+"/events/brainwork/");
			menu1.addItem("Informals", urlRequestRoot+"/events/informals/");
			menu1.addItem("Sanrachana", urlRequestRoot+"/events/sanrachana/");

	        var submenu0 = menu1.addMenu(menu1.items[0]);
	        submenu0.addItem("Circuitrix", urlRequestRoot+"/events/engsc/circuitrix/");
	        submenu0.addItem("Flipped Logic", urlRequestRoot+"/events/engsc/flippedlogic/");
	        submenu0.addItem("Fox Hunt", urlRequestRoot+"/events/engsc/foxhunt/");
	        submenu0.addItem("Manthan", urlRequestRoot+"/events/engsc/manthan/");
	        submenu0.addItem("Nittro", urlRequestRoot+"/events/engsc/nittro/");
	        submenu0.addItem("Ashtavigyan", urlRequestRoot+"/events/engsc/ashtavigyan");
	        submenu0.addItem("Transforma", urlRequestRoot+"/events/engsc/transforma");
	        submenu0.addItem("Dexter's Lab", urlRequestRoot+"/events/engsc/dlab");

		var submenu1 = menu1.addMenu(menu1.items[1]);
	        submenu1.addItem("Arthashastra", urlRequestRoot+"/events/management/arthashastra/");
	        submenu1.addItem("Dalal Street", urlRequestRoot+"/events/management/ds/");
	        submenu1.addItem("Ventura", urlRequestRoot+"/events/management/ventura/");
	        submenu1.addItem("BiZQuiZ", urlRequestRoot+"/events/management/bizquiz/");

		var submenu2 = menu1.addMenu(menu1.items[2]);
	        submenu2.addItem("Adaventure", urlRequestRoot+"/events/coding/adaventure/");
	        submenu2.addItem("Bytecode", urlRequestRoot+"/events/coding/bytecode/");
	        submenu2.addItem("War of Bots", urlRequestRoot+"/events/coding/warofbots/");

		var submenu3 = menu1.addMenu(menu1.items[3]);
	        submenu3.addItem("Micro mouse", urlRequestRoot+"/events/robovigyan/micromouse/");
	        submenu3.addItem("Sim-BOT", urlRequestRoot+"/events/robovigyan/simbot/");

		var submenu4 = menu1.addMenu(menu1.items[4]);
	        submenu4.addItem("Anveshanam", urlRequestRoot+"/events/innovation/anveshanam/");
	        submenu4.addItem("Avishkar", urlRequestRoot+"/events/innovation/avishkar/");
	        submenu4.addItem("Contraption", urlRequestRoot+"/events/innovation/contraption/");
	        submenu4.addItem("Junkyard Wars", urlRequestRoot+"/events/innovation/junkyardwars/");

		var submenu5 = menu1.addMenu(menu1.items[5]);
	        submenu5.addItem("Online Math", urlRequestRoot+"/events/brainwork/onlinemath/");
	        submenu5.addItem("Principia", urlRequestRoot+"/events/brainwork/principia/");
	        submenu5.addItem("Technical Quiz", urlRequestRoot+"/events/brainwork/tq/");
	        submenu5.addItem("The Pragyan Main Quiz", urlRequestRoot+"/events/brainwork/pmq/");

		var submenu6 = menu1.addMenu(menu1.items[6]);
	        submenu6.addItem("Adrenaline", urlRequestRoot+"/events/informals/adrenaline/");
	        submenu6.addItem("Pulse - Smackdown", urlRequestRoot+"/events/informals/smackdown/");
	        submenu6.addItem("Pulse - Smackdown", urlRequestRoot+"/events/informals/racewars/");
	        
	    var submenu7 = menu1.addMenu(menu1.items[7]);
	        submenu7.addItem("Roller Coaster", urlRequestRoot+"/events/sanrachana/roller/");
	        submenu7.addItem("Bowling Machine", urlRequestRoot+"/events/sanrachana/bowling/");
	        submenu7.addItem("Aero Car", urlRequestRoot+"/events/sanrachana/aero/");

	        //==================================================================================================

	        //==================================================================================================
	        var menu2 = ms.addMenu(document.getElementById("GuestLectures"));
	        menu2.addItem("Dr. Philippe Lebrun", urlRequestRoot+"/guestlectures/philippe_lebrun/");
	        menu2.addItem("Dr. Subramanian Swamy", urlRequestRoot+"/guestlectures/subramanian_swamy/");
	        menu2.addItem("Philip Zimmermann", urlRequestRoot+"/guestlectures/philip_zimmermann/");
	        menu2.addItem("Prof Trilochan Sastry", urlRequestRoot+"/guestlectures/trilochan_sastry/");
	        menu2.addItem("Mark Shuttleworth", urlRequestRoot+"/guestlectures/mark_shuttleworth/");
	        menu2.addItem("Ronald Mallett", urlRequestRoot+"/guestlectures/ronald_mallett/");


	        //==================================================================================================

	        //==================================================================================================
	        var menu3 = ms.addMenu(document.getElementById("Jagriti"));


	        //==================================================================================================

	        //==================================================================================================
	        var menu4 = ms.addMenu(document.getElementById("Workshops"));
	         menu4.addItem("Ham Radio Workshop", urlRequestRoot+"/workshops/ham_radio/");
	         menu4.addItem("Astronomy Workshop", urlRequestRoot+"/workshops/astronomy/");
	         menu4.addItem("Robotics Workshop", urlRequestRoot+"/workshops/robotics/");

	        //==================================================================================================

	        //==================================================================================================
	        var menu5 = ms.addMenu(document.getElementById("Infotainment"));
	        menu5.addItem("Infotainment @ Pragyan 07", "http://pragyan.org/07/?page=/events/info");

	        var submenu9 = menu5.addMenu(menu5.items[0]);
	        submenu9.addItem("Rhythm and Hues - Animation Demo", "http://pragyan.org/07/?page=/events/info/rhythm");
	        submenu9.addItem("Sand Animation", "http://pragyan.org/07/?page=/events/info/sand");
	        //==================================================================================================

			//==================================================================================================
	        var menu6 = ms.addMenu(document.getElementById("OtherLinks"));
	        menu6.addItem("Contacts", urlRequestRoot+"/contacts");
	    	menu6.addItem("NIT Trichy", "http://www.nitt.edu");
	    	menu6.addItem("Pragyan Mail", "http://pragyan.org/mail");
	    	menu6.addItem("Pragyan 07", "http://pragyan.org/07");

<?/*        //==================================================================================================

	        //==================================================================================================
	        // write drop downs into page
	        //==================================================================================================
	        // this method writes all the HTML for the menus into the page with document.write(). It must be
	        // called within the body of the HTML page.
	        //==================================================================================================
*/?>	    PragyanMenu.renderAll();
  	    }

</script>
