/**
 * @package pragyan
 * @copyright (c) 2010 Pragyan Team
 * @author boopathi
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
 
 
$(function() {
	$(".myIconForm a").bind("click",function(e) {
	
		e.preventDefault();
		return false;
	});
});

var internalDNDType = 'text/x-example'; 
var currentURL = "";
function dragEnterHandler(event) {
	if (event.dataTransfer.types.contains(internalDNDType))
	  event.preventDefault();
}
function dragOverHandler(event) {
	event.dataTransfer.dropEffect = 'move';
	$(event.target).css({background: "#AAA", color: "#000"});
	event.preventDefault();
}
function dragOutHandler(event) {
	$(event.target).css({background:"none", color: "#fff"});
}
function dropHandler(event) {
	$(event.target).load(rootUri+"/index.php?action=admin&subaction=icon&iconURL="+escape(currentURL)+"&targetId="+event.target.id.substr(1));
	$(".selection").children("p").html("Hit Refresh Once you've made all the changes.").css("font-weight","bold");
	event.preventDefault();
	return false;
}
function dragStartHandler(event,target) {
	if(target.id != "noImage")
	currentURL = $(target).children("img").attr("src");
	else
		currentURL = "";
}
function selectItem(event, target) {
	$(".dropme").css("background", "none");
	$(target).css({background:"green"});
	
		$(target).load(rootUri+"/index.php?action=admin&subaction=icon&iconURL="+escape(currentURL)+"&targetId="+event.target.id.substr(1));
		$(".selection").children("p").html("Hit Refresh Once you've made all the changes.").css("font-weight","bold");
		
	event.preventDefault();
	return false;
}
function selectIcon(event, target) {
	$(".dragme").css("background", "none");
	$(".dropme").css("background", "none");
	if(target.id != "noImage")
		currentURL = $(target).children("img").attr("src");
	else
		currentURL = "";
	$(target).css({background:"green"});
	event.preventDefault;
	return false;
}
