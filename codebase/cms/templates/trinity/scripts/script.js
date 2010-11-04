
$(function() {
	/*
	 * Hover Function for Menu
	 */
	 $("ul.topnav > li").hover(function(){
	 	$(this).animate({left: "+=2"}, 100);
		}, function() {
		$(this).animate({left: "-=2"}, 250);
	});
	$("ul.topnav li").hover(function(){
		$(this).children("ul.subnav").fadeIn();//css({display: 'block'});
		},function() {
		$(this).children("ul.subnav").css({display: 'none'});
	});
	///Write in StatusBar - "To"
	$("a").hover(function(){
		$("#statusbar").html($(this).attr("href"));
	},function(){
		$("#statusbar").html(location.href);
	}
	);
	
	///Login Form
	$("a.cms-actionlogin").hover(function() {
		$("a.cms-actionlogin").css({
			backgroundColor : "white",
			border: "solid 1px #345",
			color : "black"
		});///<Any Changes in menu.css will now automatically reflect here. Should be done manually
		
		///User-friednly and safety - password clear
		$("#hc_loginform").fadeIn(100).find("#user_email").focus().end().find("#user_password").attr("value","");
		
		///Display form when mouse over the table.
		$("#hc_loginform").hover(function(){
				$(this).css("display","block");
				$("a.cms-actionlogin").css({
					backgroundColor : "white",
					border: "solid 1px #345",
					color : "black"
					});
			}, function(){
				$(this).fadeOut();//css("display","none");
				$("a.cms-actionlogin").css({
					background : "none",
					border: "none",
					color : "white"
				});
		});
	});
	
	/**
	 * Disable navigation to login form page
	 */
	$("a.cms-actionlogin").click(function(e){ e.preventDefault(); return false; });
	
	/**
	 * Profile Menu
	 */
	$(".cms-actionprofile").hover(function(){
		$(this).css({
			backgroundColor : "white",
			border: "solid 1px #345",
			color : "black"
		});
		$("#hc_profile").fadeIn(100);
		$("#hc_profile").hover(function(){
				$(this).css("display","block");
				$("a.cms-actionprofile").css({
					backgroundColor : "white",
					border: "solid 1px #345",
					color : "black"
					});
			}, function(){
				$(this).fadeOut();//css("display","none");
				$("a.cms-actionprofile").css({
					background : "none",
					border: "none",
					color : "white"
				});
		});
	});
	
	
});

///Login form Validation
function checkLoginForm(inputhandler) {
	if(inputhandler.user_email.value.length==0) {
		alert("Blank Email Address not allowed.");
		return false;
	}
	else if(inputhandler.user_email.value.indexOf('@') == -1)
	{
		alert("Invalid Email address");
		return false;
	}
	else if(inputhandler.user_password.value.length==0) {
		alert("Blank password not allowed.");
		return false;
	}
}

///Change Home Icon on mouseover
function 	changeHomeImage()
{
	$(this).find("img").attr("src",templateBrowserPath+"/images/home.png");
}

