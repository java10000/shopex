dd = document.getElementById("demopro");
dd1 = document.getElementById("demopro1");
dd2 = document.getElementById("demopro2");
var speed=30 
dd2.innerHTML=dd1.innerHTML 
function Marqueepro(){ 
if(dd2.offsetWidth-dd.scrollLeft<=0) 
dd.scrollLeft-=dd1.offsetWidth 
else{ 
dd.scrollLeft=dd.scrollLeft+2 
} 
} 
var MyMar=setInterval(Marqueepro,speed) 
dd.onmouseover=function() {clearInterval(MyMar)} 
dd.onmouseout=function() {MyMar=setInterval(Marqueepro,speed)} 