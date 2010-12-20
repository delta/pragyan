/**
 * @author Boopathi
 * @description Adding an extra behaviour to the native Javascript function
 * @usage function(){}.debounce()
 **/
Function.prototype.debounce = function(threshold, execAsap) {
	var func = this, timeout;
	return function debounced(){
		var obj= this, args = arguments;
		function delayed() {
			if(!execAsap)
				func.apply(obj,args);
			timeout = null;
		}
		if(timeout)
			clearTimeout(timeout);
		else if(execAsap)
			func.apply(obj,args);
		timeout = setTimeout(delayed, threshold || 100);
	}
}
$(function() {
	/*
	 * Hover Function for Menu
	 */
	$("ul.topnav li").hover(function(e){
	 	$(this).children("ul.subnav").fadeIn();
	}.debounce(250), function() {
		$(this).children("ul.subnav").css({display: 'none'});
	});
	$("ul.topnav li").bind({
		mouseenter: function(){
			$(this).animate({left: "+=2"}, 100);
		},
		mouseleave: function(){
			$(this).animate({left: "-=2"}, 250);
		}
	});
	
	///Write in StatusBar - "To"
	$("a").bind({
		mouseenter: function(){
			$("#statusbar").html($(this).attr("href"));
		},
		mouseleave: function(){
			$("#statusbar").html(location.href);
		}
	});
	
	///Login Form
	$("a.cms-actionlogin").hover(function() {
		$("a.cms-actionlogin").css({
			backgroundColor : "white",
			border: "solid 1px #345",
			color : "black"
		});///<Any Changes in menu.css will now automatically reflect here. Should be done manually
		
		///User-friendly and safety - password clear
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
	$("a.cms-actionlogin").bind({click: function(e){ e.preventDefault(); return false; }});
	
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
	/**                                                                              
	 * Trigger normal and Open id menu                                                
	 */

        $('#openidLogin').bind("click",function(e){
                $("#openid_form *").fadeIn(100,function(){
                        $("hc_loginform fieldset").fadeOut();
		    });
                $("#pragyan_loginform *").fadeOut(100,function(){
                        $("hc_loginform fieldset").fadeOut();
		    });

                e.preventDefault();
                return false;
	    });

        $('#pragyanLogin').bind("click",function(e){
                $("#pragyan_loginform *").fadeIn(100,function(){
                        $("hc_loginform fieldset").fadeOut();
		    });
                $("#openid_form *").fadeOut(100,function(){
                        $("hc_loginform fieldset").fadeOut();
		    });

                e.preventDefault();
                return false;
	    });
	
	
	///Expand on mouse click in registration field
	
	$("form.cms-registrationform input[type=text]").focus(focusField).blur(blurField);
	$("form.cms-registrationform input[type=password]").focus(focusField).blur(blurField);
	
	/**
	* Enable ticker
	*/
	$('#js-news').ticker({titleText: "Updates : "});
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
function changeHomeImage()
{
	$(this).find("img").attr("src",templateBrowserPath+"/images/home.png");
}

///Function focus for registration menu
function focusField(){
	$(this).animate({
		fontSize: "12px"
	},50).css({
		background: "#fff",
		color: "black"
	});
}
	
function blurField(){
	$(this).animate({
		fontSize: "10px",
	},50).css({
		background: "#ddd",
		color: "#666"
	});
}
