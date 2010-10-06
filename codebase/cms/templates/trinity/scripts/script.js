
window.onload = function() {
	$("ul.topnav li").hover(function(){
		$(this).animate({left: "+=2"}, 100);
		$(this).find("ul.depth2").fadeIn();//css({display: 'block'});
		},function() {
		$(this).animate({left: "-=2"}, 250);
		$(this).find("ul.depth2").css({display: 'none'});
		
	});
	$("a").hover(function(){
		$("#statusbar").html($(this).attr("href"));
	},function(){
		$("#statusbar").html(location.href);
	}
	);
}
