/**
 * @author Boopathi
 * @description Adding an extra behaviour to the native Javascript function
 * @usage function(){}.debounce()
 
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
 **/
$(function() {
	
	/*
	 * Hover Function for Menu
	 */
	
	var topnavLi = $("ul.topnav > li");
	var registerEvt = "<a href=\"register\" class=\"registerButton\" style=\"display:none;position:absolute;margin-top:-22px;left:120px;padding:0px 10px;border-radius: 10px;-moz-border-radius:10px;-webkit-border-radius:10px\">+</a>";
	
	topnavLi.hover(function(){
	 	$(this).children("ul.subnav").css({display:"block",opacity:0,left: 50}).animate({left: 0, opacity:1}, 250);
	},function() {
		$(this).find("ul.subnav").css({display: 'none'});
	}).bind({
		mouseenter: function() {
			$(this).animate({paddingLeft: 10}, 75);
		},
		mouseleave: function() {
			$(this).animate({paddingLeft: 0}, 100);
		}
	}).find("ul.subnav > li").bind({
		mouseenter: function(){
	 		$(this).children("ul.subnav").slideDown(100);
		},
		mouseleave: function() {
			$(this).find("ul.subnav").css({display: 'none'});
		}
	}).end().parent().children("#cms-menu-item0").find("ul.depth3 > li").append(registerEvt).bind({
			mouseenter: function() {
				$(this).find(".registerButton").show().bind({
					mouseenter: function(){
						$(this).css({background: "white", color: "black"});
					},
					mouseleave: function(){
						$(this).css({background: "none", color: "white"});
					},
					click: function(){
						return false;
					}
				});
			},
			mouseleave: function() {
				$(this).find(".registerButton").hide();
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
	var loginForm = $("#hc_loginform");
	$("a.cms-actionlogin").click(function() {
		
		///User-friendly and safety - password clear
		loginForm.css({display:"block", top:-200}).animate({top:0}, 100).find("#user_email").focus().end().find("#user_password").attr("value","");
		
		///Display form when mouse over the table.
		loginForm.hover(function(){
				$(this).css({display: "block"});
				
			}, function(){
				$(this).slideUp(100);//css("display","none");
				
		});
		return false;
	}).bind({
		click: function(event){ event.preventDefault(); return false; }
	});
	
	/*
	 * Extended Header
	 */
	var isClosed = true;
	var extTarget = $("#exthead"); //to reduce reflow
	$(".extendHeadLink a").bind({
		mouseenter: function() {
			extTarget.css({top: -3});
		},
		mouseleave: function() {
			if(isClosed)
				extTarget.css({top: 0});
		}
	}).click(function() {
		if(isClosed) {
			isClosed = false;
			$("body").animate({scrollTop: 0}, 400);
			extTarget.animate({height: 300}, 400,function(){$(this).children(".extendedContainer").fadeIn(50);});
			var path= templateBrowserPath + "/../common/icons/16x16/actions/go-up.png"
			$(this).find("img").attr("src", path);
		}
		else {
			isClosed = true;
			extTarget.children(".extendedContainer").fadeOut(50, function(){$(this).parent().animate({height: 0}, 300);});
			var path= templateBrowserPath + "/../common/icons/16x16/actions/go-down.png"
			$(this).find("img").attr("src", path);
		}
		return false;
	});	
	
	/**
	 * Profile Menu
	 */
	var profileMenu = $("#hc_profile");
	$(".cms-actionprofile").click(function(){
		
		profileMenu.slideDown(100);
		profileMenu.hover(function(){
				$(this).css("display","block");
			}, function(){
				$(this).slideUp(100);//css("display","none");
		});
		return false;
	});
	
	/**                                                                              
	 * Trigger normal and Open id menu                                                
	 */

    $('#openidLogin').bind("click",function(e){
		loginForm.animate({width: 450, height: 50}, function() {
			$(this).animate({height: 200});
			loginForm.find("#openid_form *").fadeIn(100);
		});
		loginForm.find("#pragyan_loginform *").fadeOut(100);
	    e.preventDefault();
	    return false;
    });

    $('#pragyanLogin').bind("click",function(e){
    	loginForm.animate({width: 270});
        loginForm.find("#pragyan_loginform *").fadeIn(100);
        loginForm.find("#openid_form *").fadeOut(100);

        e.preventDefault();
        return false;
    });
	
	
	///Expand on focus in registration field
	var regForm = $("form.cms-registrationform");
	regForm.find("input[type=text]").focus(focusField).blur(blurField);
	regForm.find("input[type=password]").focus(focusField).blur(blurField);
	
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
	},75).css({
		background: "#fff",
		color: "black"
	});
}
	
function blurField(){
	$(this).animate({
		fontSize: "10px",
	},75).css({
		background: "#ddd",
		color: "#666"
	});
}
