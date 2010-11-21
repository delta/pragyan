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
	$(event.target).css({background:"none", color: "none"});
}
function dropHandler(event) {
	$(event.target).load(rootUri+"/"+cmsFolder+"/iconmanagement/addicon.php?iconURL="+currentURL+"&targetId="+event.target.id.substr(1));
	$(".selection").children("p").html("Hit Refresh Once you've made all the changes.").css("background","yellow");
	event.preventDefault();
	return false;
}
function dragStartHandler(event,target) {
	currentURL = $(target).children("img").attr("src");
}
