$(document).ready(
function(){

var d = new Date();

$('#countdown-1').countdown({now: 'Happy new year !', 
    year: d.getFullYear()+1, month: d.getMonth()+1, day: d.getDate()+1, hour: 1, min: 1, sec: 1 
});

});
