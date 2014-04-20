$(document).ready(function() {
    var pressed = false; 
    var chars = []; 
    $(window).keypress(function(e) {
        chars.push(String.fromCharCode(e.which));
        if (pressed == false) {
            setTimeout(function(){
                if (chars.length >= 1) {
                    var barcode = chars.join("");
		    console.log(barcode);
		    if ($("#txtFormUserId").is(":focus")) {
			document.getElementById("prCheckInForm").submit();
		    }
                }
                chars = [];
                pressed = false;
            },500);
        }
        pressed = true;
    });
});
$("#txtFormUserId").keypress(function(e){
    if ( e.which === 13 ) {
        console.log("Prevent form submit.");
        e.preventDefault();
    }
});
