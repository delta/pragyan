var slideshow_width='220px' //SET SLIDESHOW WIDTH (set to largest image's width if multiple dimensions exist)
var slideshow_height='115px' //SET SLIDESHOW HEIGHT (set to largest image's height if multiple dimensions exist)
var pause=4000 //SET PAUSE BETWEEN SLIDE (2000=2 seconds)
var slidebgcolor="white"

var dropimages=new Array()
//SET IMAGE PATHS.
dropimages[0] = imagesFolder + '/akamai.jpg'
dropimages[1] = imagesFolder + '/avaya.gif';
dropimages[2] = imagesFolder + '/deshaw.gif';
dropimages[3] = imagesFolder + '/google.jpg';
dropimages[4] = imagesFolder + '/wipro.jpg';

var droplinks=new Array()
//SET IMAGE URLs.
droplinks[0]="http://www.yahoo.com"
droplinks[1]=""
droplinks[2]="http://www.google.com"

var preloadedimages=new Array()
for (p=0;p<dropimages.length;p++){
preloadedimages[p]=new Image()
preloadedimages[p].src=dropimages[p]
}

var ie4=document.all
var dom=document.getElementById

if (ie4||dom)
	document.write('<div style="position:relative;width:'+slideshow_width+';height:'+slideshow_height+';overflow:hidden"><div id="canvas0" style="position:absolute;width:'+slideshow_width+';height:'+slideshow_height+';background-color:'+slidebgcolor+';right:-'+slideshow_width+'"></div><div id="canvas1" style="position:absolute;width:'+slideshow_width+';height:'+slideshow_height+';background-color:'+slidebgcolor+';right:-'+slideshow_width+'"></div></div>')
else
	document.write('<a href="javascript:rotatelink()"><img name="defaultslide" src="'+dropimages[0]+'" border="0"></a>')

var curpos=parseInt(slideshow_width)*(-1)
var degree=10
var curcanvas="canvas0"
var curimageindex=linkindex=0
var nextimageindex=1


function movepic(){
	if (curpos<0){
		curpos=Math.min(curpos+degree,0)
		tempobj.style.right=curpos+"px"
	}
	else{
		clearInterval(dropslide)
		nextcanvas=(curcanvas=="canvas0")? "canvas0" : "canvas1"
		tempobj=ie4? eval("document.all."+nextcanvas) : document.getElementById(nextcanvas)
		var slideimage='<img src="'+dropimages[curimageindex]+'" border="0">'
		tempobj.innerHTML=(droplinks[curimageindex]!="")? '<a href="'+droplinks[curimageindex]+'">'+slideimage+'</a>' : slideimage
		nextimageindex=(nextimageindex<dropimages.length-1)? nextimageindex+1 : 0
		setTimeout("rotateimage()",pause)
	}
}

function rotateimage(){
	if (ie4||dom){
		resetit(curcanvas)
		var crossobj=tempobj=ie4? eval("document.all."+curcanvas) : document.getElementById(curcanvas)
		crossobj.style.zIndex++
		var temp='setInterval("movepic()",50)'
		dropslide=eval(temp)
		curcanvas=(curcanvas=="canvas0")? "canvas1" : "canvas0"
	}
	else
		document.images.defaultslide.src=dropimages[curimageindex]
	linkindex=curimageindex
	curimageindex=(curimageindex<dropimages.length-1)? curimageindex+1 : 0
}

function rotatelink(){
	if (droplinks[linkindex]!="")
		window.location=droplinks[linkindex]
}

function resetit(what){
	curpos=parseInt(slideshow_width)*(-1)
	var crossobj=ie4? eval("document.all."+what) : document.getElementById(what)
	crossobj.style.right=curpos+"px"
}

function startit(){
	var crossobj=ie4? eval("document.all."+curcanvas) : document.getElementById(curcanvas)
	crossobj.innerHTML='<a href="'+droplinks[curimageindex]+'"><img src="'+dropimages[curimageindex]+'" border=0></a>'
	rotateimage()
}

if (ie4||dom)
	window.onload=startit
else
	setInterval("rotateimage()",pause)