/**
 * @author Boopathi
 * @description Adding an extra behaviour to the native Javascript function
 * @usage function(){}.debounce()
*/ 
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
	var topnavLi = $("ul.topnav > li");
	var registerEvt = "<a href=\"{{{href}}}\" class=\"registerButton\" style=\"display:none;color:blue;position:absolute;margin-top:-22px;left:120px;padding:0px 10px;border-radius: 10px;-moz-border-radius:10px;-webkit-border-radius:10px\">+</a>";
	var subMenuIsClosed = true;
	topnavLi.bind({
		mouseenter: function() {
			$(this).stop().animate({
				paddingLeft: 10
			}, 75).children("ul.subnav").css({
				display:"block",opacity:0,left: 50
			}).stop().delay(250).animate({
				left: 0, opacity:1
			}, 250).find("ul").css({
				display:"none"
			});
		},
		mouseleave: function() {
			$(this).stop().animate({paddingLeft: 0}, 100).find("ul.subnav").css({display: 'none'});
		}
	}).find("ul.depth2 > li").bind({
		click: function(){
	 		var suub = $(this).children("ul.depth3");
	 		if(subMenuIsClosed) {
	 			suub.stop().slideDown(100);
	 			subMenuIsClosed = false;
	 		}
	 		else {
	 			suub.stop().slideUp(100);
	 			subMenuIsClosed = true;
	 		}
	 		if((suub.length != 0) && (subMenuIsClosed == false))
	 			return false;
		},
		dblclick: function() {
			var url = $(this).children("a").attr("href");
			location.href = url;
		},
		mouseleave: function() {
			$(this).find("ul.subnav").css({display: 'none'});
			subMenuIsClosed = true;
		}
	}).end().parent().children("#cms-menu-item0").find("ul.depth3 > li").append(function(){
		var actual = $(this).children("a").attr("href");
		return registerEvt.replace("{{{href}}}", actual+"registrations");
	}).bind({
			mouseenter: function() {
				$(this).find(".registerButton").stop().show().bind({
					mouseenter: function(){
						$(this).css({background: "#027703", color: "black"});
					},
					mouseleave: function(){
						$(this).css({background: "none", color: "blue"});
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
	function openLoginDialog(target) {
		if(target == null) target = this;
		
		///User-friendly and safety - password clear
		loginForm.css({display:"block", top:-200}).animate({top:0}, 100).find("#user_email").focus().end().find("#user_password").attr("value","");
		
		///Display form when mouse over the table.
		loginForm.hover(function(){
				$(target).css({display: "block"});
				
			}, function(){
				$(target).slideUp(100);//css("display","none");
				
		});
		return false;
	}
	$("a.cms-actionlogin").click(openLoginDialog).bind({
		click: function(event){ event.preventDefault(); return false; }
	});
	
	/*
	 * Extended Header
	 */
	var isClosed = true;
	var extTarget = $("#exthead"); //to reduce reflow
	var extLink = $(".extendHeadLink a");
	extLink.bind({
		mouseenter: function() {
			extTarget.css({top: -3});
		},
		mouseleave: function() {
			if(isClosed)
				extTarget.css({top: 0});
		}
	}).click(extendHeader);
	
	function extendHeader() {
		if(isClosed) {
			isClosed = false;
			$("body").animate({scrollTop: 0}, 400);
			extTarget.stop().animate({height: 300}, 400,function(){$(this).children(".extendedContainer").fadeIn(50);});
			var path= templateBrowserPath + "/../common/icons/16x16/actions/go-up.png"
			extLink.find("img").attr("src", path);
		}
		else {
			isClosed = true;
			extTarget.children(".extendedContainer").fadeOut(50, function(){$(this).parent().stop().animate({height: 0}, 300);});
			var path= templateBrowserPath + "/../common/icons/16x16/actions/go-down.png"
			extLink.find("img").attr("src", path);
		}
		return false;
	}
	
	/**
	 * Profile Menu
	 */
	var profileMenu = $("#hc_profile");
	$(".cms-actionprofile").click(function(){
		
		profileMenu.slideDown(10);
		profileMenu.hover(function(){
				$(this).css("display","block");
			}, function(){
				$(this).slideUp(10);//css("display","none");
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

	/* Added by Abhishek */
	
	/**
 	 * For handling right columns sub menu
 	 */
	$('#rt_topic_downloads').hide();
	$('#rt_topic_forums').hide();
	$('.rtbt').click(function(){

		$('#rt_topic_links').slideUp(50);
		$('#rt_topic_downloads').slideUp(50);
		$('#rt_topic_forums').slideUp(50);
		$key=$(this).attr('id').split("_").pop();
		$('#rt_topic_'+$key).slideDown(300);
	
	});
	
	/**
	 * Toggle display keyboard shortcuts
         */
	var kbd_shorts=$('#kbd_shortcuts');
	var kbd_disp=$('#kbd_disp');
	$('#kbd_disp').click(function(){
		if(kbd_disp.html()=="[+]")
		{
			kbd_disp.html("[-]");
			kbd_shorts.slideDown(100);
		}
		else {
			kbd_disp.html("[+]");
			kbd_shorts.slideUp(100);
		}
		});

	
	/**
	 * Enable keyboard Shortcuts
	 */
	$.ctrl = function(key, callback, args) {
		var isCtrl = false;
		var isAlt = false;
		var isEnabled = true;
		$(document).keydown(function(e) {
			if(!args)args=[];
			if(e.keyCode==17)isCtrl = true;
			if(e.keyCode==18)isAlt = true;
			if(e.keyCode == key.charCodeAt(0) && isCtrl && isEnabled) {
            	callback.apply(this, args);
            	return false;
        	}
        	if(e.keyCode == "K".charCodeAt(0) && isCtrl && isAlt) {
        		var ot = $("#right4");
        		if(isEnabled) {
        			isEnabled=false;
        			ot.children("ul").hide(50).parent().children("h3").html("Keyboard Shortcuts (Disabled)").css("background", "#666");
        		}
        		else {
        			isEnabled=true;
        			ot.children("h3").css("background", "#136dac").html("Keyboard Shortcuts (Enabled)").parent().children("ul").show(50);
        		}
        		return false;
        	}
    	}).keyup(function(e) {
    	    if(e.keyCode == 17) isCtrl = false;
    	    if(e.keyCode == 18) isAlt = false;
		});
    }
    
    //Assign Shortcuts
    $.ctrl("H", function(){location.href=urlRequestRoot;});
    $.ctrl("E", function(){location.href=urlRequestRoot+ "/home/events/";});
    $.ctrl("Q", function(){extendHeader();});
    $.ctrl("L", function(){openLoginDialog("a.cms-actionlogin")});
    $.ctrl("W", function(){location.href=urlRequestRoot+"/home/workshops/";});
    $.ctrl("S", function(){location.href="./+pdf"});
    $.ctrl("R", function(){location.href="+login&subaction=register"});
        
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
