$(document).ready(function(){
var srcTemp = templateBrowserPath + "/images/icons/logout.jpg";
var hrefTemp = "./+logout";
if($(".cms-actionbarPageItem").parent().find('[href=./+login]').html() == "Login")
{
	srcTemp = templateBrowserPath + "/images/icons/login.jpg";
	hrefTemp = "./+login";
}
$("#userLog").attr("src", srcTemp);
$("#userLog").parent().attr("href", hrefTemp);


		 $("ul.topnav > li > ul.subnav").parent().append("<span></span>"); //Only shows drop down trigger when js is enabled - Adds empty span tag after ul.subnav
		 var subnavStyle = "float: right;margin: -27px 15px 0 0;z-index: 10000; color: #55ff55;font-weight:bold;"
		 $("ul.subnav > li > ul.subnav").parent().append("<div style=\""+subnavStyle+"\">></div>");
		$("ul.topnav").append("<div id='topmenu-homebtn'><a href=\""+urlRequestRoot+"/home/\"><img src='"+templateBrowserPath+"/images/home.png'/></a></div>");
		 $("ul.topnav li span").click(function() { //When trigger is clicked...

		 //Following events are applied to the subnav itself (moving subnav up and down)
		 $(this).parent().children("ul.subnav").slideDown('fast').show(); //Drop down the subnav on click

		 $(this).parent().hover(function() {
		 }, function(){
		 $(this).parent().find("ul.subnav").slideUp('slow'); //When the mouse hovers out of the subnav, move it back up
		 });

		 //Following events are applied to the trigger (Hover events for the trigger)
		}).hover(function() {
		 $(this).addClass("subhover"); //On hover over, add class "subhover"
		 }, function(){ //On Hover Out
		$(this).removeClass("subhover"); //On hover out, remove class "subhover"
		 });
	$("ul.subnav li").hover(function(){
		$(this).children(".subnav").slideToggle();
		},
		function(){
		$(this).find("ul.subnav").hide("slow");
		});
	$("#quicklinks img").hover(function(){
	$(this).animate({width: "+=10", height: "+=10"}, 100);
	},function(){
	$(this).animate({width: "-=10", height: "-=10"},300);
	});
}); 

