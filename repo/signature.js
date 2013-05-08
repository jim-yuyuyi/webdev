/* JS funcntion library to allow a drawable Signature Pad

Example Usage: 
<body onload='CanvasPainter("canvas");initMouseListeners(tcanvas);pathstr=document.getElementsByName("sighidden")[0];'>
<canvas id='canvas' style='width:400;height:200;color:green;'></canvas></body>
*/

/* ----------------------------------------- */
/* excanvas.js	Google 2006.	Adds canvas tag support for browsers without it(e.g. IE5.5 ~IE8).
Uses Microsoft VML (Vector Markup Language) for implmentation - xml-like.
DO NOT put <!DOCTYPE> infront of <html>, becuase it becomes <HTML xmlns:g_vml= urn:schemas-microsoft-com:vml">
*/
if(!window.CanvasRenderingContext2D) {
(function(){
//if(navigator.appVersion.search(/MSIE\s*8/)<0){return false;}
var G_vmlCanvasManager_= {
	init: function(opt_doc) {
	var doc= opt_doc || document;
	if(/MSIE/.test(navigator.userAgent) && !window.opera) {
	var self= this;
	doc.attachEvent('onreadystatechange', function() {
	self.init_(doc);
	});
	}},	init_: function(doc, e){
	if(doc.readyState== 'complete') {
	if(!doc.namespaces['g_vml']) {doc.namespaces.add('g_vml','urn:schemas-microsoft-com:vml');}
	var ss= doc.createStyleSheet();
	ss.cssText= 'canvas{display:inline-block;overflow:hidden;text-align:left;}'+'canvas *{ behavior:url(#default#VML)}';
	var els= doc.getElementsByTagName('canvas');
	for(var i= 0;i<els.length;i++){if(!els[i].getContext){this.initElement(els[i]);}}
	}},fixElement_: function(el){		
		var outerHTML= el.outerHTML;
		var newEl= document.createElement(outerHTML);
		if(outerHTML.slice(-2) != '/>') {
		var tagName= '/' + el.tagName;
		var ns;
		while ((ns= el.nextSibling) && ns.tagName != tagName){ns.removeNode();}
		if(ns){ns.removeNode();}
	}el.parentNode.replaceChild(newEl, el);	
	return newEl;
	},	initElement: function(el){
	el= this.fixElement_(el);
	el.getContext= function(){
	if(this.context_){return this.context_;}
	return this.context_= new CanvasRenderingContext2D_(this);
	};
	var self= this;
	el.attachEvent('onpropertychange',function(e){
		switch (e.propertyName) {
		case 'width':
		case 'height':
		break;
	}});
	var attrs= el.attributes;
	if(attrs.width && attrs.width.specified) {el.style.width= attrs.width.nodeValue;}
	if(attrs.height && attrs.height.specified) {el.style.height= attrs.height.nodeValue;}	
	}
};
G_vmlCanvasManager_.init();
var dec2hex= [];
for(var i= 0;i < 16;i++) {
	for(var j= 0;j < 16;j++) {
	dec2hex[i * 16 + j]= i.toString(16) + j.toString(16);
	}
}
function createMatrixIdentity() {
	return [ [1, 0, 0], [0, 1, 0], [0, 0, 1] ];
}
function matrixMultiply(m1, m2) {
	var result= createMatrixIdentity();
	for(var x= 0;x < 3;x++) {
	for(var y= 0;y < 3;y++) {
	var sum= 0;
	for(var z= 0;z < 3;z++) {sum += m1[x][z] * m2[z][y];}
	result[x][y]= sum;
	}
	}
	return result;
}
function copyState(o1, o2) {
	o2.fillStyle    = o1.fillStyle;
	o2.lineCap      = o1.lineCap;
	o2.lineJoin     = o1.lineJoin;
	o2.lineWidth    = o1.lineWidth;
	o2.miterLimit   = o1.miterLimit;
	o2.shadowBlur   = o1.shadowBlur;
	o2.shadowColor  = o1.shadowColor;
	o2.shadowOffsetX= o1.shadowOffsetX;
	o2.shadowOffsetY= o1.shadowOffsetY;
	o2.strokeStyle  = o1.strokeStyle;
}
function processStyle(styleString) {
	var str, alpha= 1;
	styleString= String(styleString);
	if(styleString.substring(0, 3)== 'rgb') {
	var start= styleString.indexOf('(', 3);
	var end= styleString.indexOf(')', start + 1);
	var guts= styleString.substring(start + 1, end).split(',');
	str= '#';
	for(var i= 0;i < 3;i++) {
	str += dec2hex[parseInt(guts[i])];
	}
	if((guts.length== 4) && (styleString.substr(3, 1)== 'a')) {
	alpha= guts[3];
	}
	} else {
	str= styleString;
	}
	return [str, alpha];
}
function processLineCap(lineCap) {
	switch (lineCap) {
	case 'butt':
	return 'flat';
	case 'round':
	return 'round';
	case 'square':
	default:
	return 'square';
	}
}
function CanvasRenderingContext2D_(surfaceElement) {
	this.m_= createMatrixIdentity();
	this.element_= surfaceElement;
	this.mStack_= [];
	this.aStack_= [];
	this.currentPath_= [];
	this.strokeStyle= '#000';
	this.fillStyle= '#ccc';
	this.lineWidth= 1;
	this.lineJoin= 'miter';
	this.lineCap= 'butt';
	this.miterLimit= 10;
	this.globalAlpha= 1;
};
var contextPrototype= CanvasRenderingContext2D_.prototype;
contextPrototype.clearRect= function() {
	this.element_.innerHTML= '';
	this.currentPath_= [];
};
contextPrototype.beginPath= function() {
	this.currentPath_= [];
};
contextPrototype.moveTo= function(aX, aY) {
	this.currentPath_.push({type: 'moveTo', x: aX, y: aY});
};
contextPrototype.lineTo= function(aX, aY) {
	this.currentPath_.push({type: 'lineTo', x: aX, y: aY});
};
contextPrototype.bezierCurveTo= function(aCP1x,aCP1y,aCP2x,aCP2y,aX,aY) {
	this.currentPath_.push({type: 'bezierCurveTo',cp1x: aCP1x,cp1y: aCP1y,cp2x: aCP2x,cp2y: aCP2y,x: aX,y: aY});
};
contextPrototype.quadraticCurveTo= function(aCPx, aCPy, aX, aY) {
	this.bezierCurveTo(aCPx, aCPy, aCPx, aCPy, aX, aY);
};
contextPrototype.arc= function(aX, aY, aRadius,	aStartAngle, aEndAngle, aClockwise) {
	if(!aClockwise) {
	var t= aStartAngle;
	aStartAngle= aEndAngle;
	aEndAngle= t;
	}
	var xStart= aX + (Math.cos(aStartAngle) * aRadius);
	var yStart= aY + Math.round(Math.sin(aStartAngle) * aRadius);
	var xEnd= aX + (Math.cos(aEndAngle) * aRadius);
	var yEnd= aY + Math.round(Math.sin(aEndAngle) * aRadius);
	this.currentPath_.push({type:'arc',x:aX,y:aY,radius: aRadius,xStart: xStart,yStart:yStart,xEnd:xEnd,yEnd:yEnd});
};
contextPrototype.rect= function(aX, aY, aWidth, aHeight) {
	this.moveTo(aX, aY);
	this.lineTo(aX + aWidth, aY);
	this.lineTo(aX + aWidth, aY + aHeight);
	this.lineTo(aX, aY + aHeight);
	this.closePath();
};
contextPrototype.strokeRect= function(aX, aY, aWidth, aHeight) {
	this.beginPath();
	this.moveTo(aX, aY);
	this.lineTo(aX + aWidth, aY);
	this.lineTo(aX + aWidth, aY + aHeight);
	this.lineTo(aX, aY + aHeight);
	this.closePath();
	this.stroke();
};
contextPrototype.fillRect= function(aX, aY, aWidth, aHeight) {
	this.beginPath();
	this.moveTo(aX, aY);
	this.lineTo(aX + aWidth, aY);
	this.lineTo(aX + aWidth, aY + aHeight);
	this.lineTo(aX, aY + aHeight);
	this.closePath();
	this.fill();
};
contextPrototype.createLinearGradient= function(aX0, aY0, aX1, aY1) {
	var gradient= new CanvasGradient_('gradient');
	return gradient;
};
contextPrototype.createRadialGradient= function(aX0, aY0,	aR0, aX1,	aY1, aR1) {
	var gradient= new CanvasGradient_('gradientradial');
	gradient.radius1_= aR0;
	gradient.radius2_= aR1;
	gradient.focus_.x= aX0;
	gradient.focus_.y= aY0;
	return gradient;
};
contextPrototype.drawImage= function(image, var_args) {
	var dx, dy, dw, dh, sx, sy, sw, sh;
	var w= image.width;
	var h= image.height;
	if(arguments.length== 3) {
	dx= arguments[1];
	dy= arguments[2];
	sx= sy= 0;
	sw= dw= w;
	sh= dh= h;
	} else if(arguments.length== 5) {
	dx= arguments[1];
	dy= arguments[2];
	dw= arguments[3];
	dh= arguments[4];
	sx= sy= 0;
	sw= w;
	sh= h;
	} else if(arguments.length== 9) {
	sx= arguments[1];
	sy= arguments[2];
	sw= arguments[3];
	sh= arguments[4];
	dx= arguments[5];
	dy= arguments[6];
	dw= arguments[7];
	dh= arguments[8];
	} else {
	throw 'Invalid number of arguments';
	}
	var d= this.getCoords_(dx, dy);
	var w2= (sw / 2);
	var h2= (sh / 2);
	var vmlStr= [];
	vmlStr.push(' <g_vml:group',	' coordsize=\'100,100\'',	' coordorigin=\'0, 0\'' ,	' style=\'width:100px;height:100px;position:absolute;');
	if(this.m_[0][0] != 1 || this.m_[0][1]) {
	var filter= [];
	filter.push('M11=\'', this.m_[0][0], '\',',	'M12=\'', this.m_[1][0], '\',',	'M21=\'', this.m_[0][1], '\',',	'M22=\'', this.m_[1][1], '\',',	'Dx=\'', d.x, '\',',	'Dy=\'', d.y, '\'');
	var max= d;
	var c2= this.getCoords_(dx+dw, dy);
	var c3= this.getCoords_(dx, dy+dh);
	var c4= this.getCoords_(dx+dw, dy+dh);
	max.x= Math.max(max.x, c2.x, c3.x, c4.x);
	max.y= Math.max(max.y, c2.y, c3.y, c4.y);
	vmlStr.push(' padding:0 ', Math.floor(max.x), 'px ', Math.floor(max.y),	'px 0;filter:progid:DXImageTransform.Microsoft.Matrix(',	filter.join(''), ', sizingmethod=\'clip\');')
	} else {
	vmlStr.push(' top:', d.y, 'px;left:', d.x, 'px;')
	}
	vmlStr.push(' \'>' ,	'<g_vml:image src=\'', image.src, '\'',	' style=\'width:', dw, ';',	' height:', dh, ';\'',	' cropleft=\'', sx / w, '\'',	' croptop=\'', sy / h, '\'',	' cropright=\'', (w - sx - sw) / w, '\'',	' cropbottom=\'', (h - sy - sh) / h, '\'',	' />',	'</g_vml:group>');
	this.element_.insertAdjacentHTML('BeforeEnd',	vmlStr.join(''));
};
contextPrototype.stroke= function(aFill) {
	var lineStr= [];
	var lineOpen= false;
	var a= processStyle(aFill ? this.fillStyle : this.strokeStyle);
	var color= a[0];
	var opacity= a[1] * this.globalAlpha;
	lineStr.push('<g_vml:shape',	' fillcolor=\'', color, '\'',	' filled=\'', Boolean(aFill), '\'',	' style=\'position:absolute;width:10;height:10;\'',	' coordorigin=\'0 0\' coordsize=\'10 10\'',	' stroked=\'', !aFill, '\'',	' strokeweight=\'', this.lineWidth, '\'',	' strokecolor=\'', color, '\'',	' path=\'');
	var newSeq= false;
	var min= {x: null, y: null};
	var max= {x: null, y: null};
	for(var i= 0;i < this.currentPath_.length;i++) {
	var p= this.currentPath_[i];
	if(p.type== 'moveTo') {
	lineStr.push(' m ');
	var c= this.getCoords_(p.x, p.y);
	lineStr.push(Math.floor(c.x), ',', Math.floor(c.y));
	} else if(p.type== 'lineTo') {
	lineStr.push(' l ');
	var c= this.getCoords_(p.x, p.y);
	lineStr.push(Math.floor(c.x), ',', Math.floor(c.y));
	} else if(p.type== 'close') {
	lineStr.push(' x ');
	} else if(p.type== 'bezierCurveTo') {
	lineStr.push(' c ');
	var c= this.getCoords_(p.x, p.y);
	var c1= this.getCoords_(p.cp1x, p.cp1y);
	var c2= this.getCoords_(p.cp2x, p.cp2y);
	lineStr.push(Math.floor(c1.x), ',', Math.floor(c1.y), ',',	Math.floor(c2.x), ',', Math.floor(c2.y), ',',	Math.floor(c.x), ',', Math.floor(c.y));
	} else if(p.type== 'arc') {
	lineStr.push(' ar ');
	var c = this.getCoords_(p.x, p.y);
	var cStart= this.getCoords_(p.xStart, p.yStart);
	var cEnd= this.getCoords_(p.xEnd, p.yEnd);
	var absXScale= this.m_[0][0];
	var absYScale= this.m_[1][1];
	lineStr.push(Math.floor(c.x - absXScale * p.radius), ',',	Math.floor(c.y - absYScale * p.radius), ' ',	Math.floor(c.x + absXScale * p.radius), ',',	Math.floor(c.y + absYScale * p.radius), ' ',	Math.floor(cStart.x), ',', Math.floor(cStart.y), ' ',	Math.floor(cEnd.x), ',', Math.floor(cEnd.y));
	}
	if(c) {
	if(min.x== null || c.x < min.x) {min.x= c.x;}
	if(max.x== null || c.x > max.x) {max.x= c.x;}
	if(min.y== null || c.y < min.y) {min.y= c.y;}
	if(max.y== null || c.y > max.y) {max.y= c.y;}
	}
	}
	lineStr.push(' \'>');
	if(typeof this.fillStyle=='object') {
	var focus= {x: '50%', y: '50%'};
	var width= (max.x - min.x);
	var height= (max.y - min.y);
	var dimension= (width > height) ? width : height;
	focus.x= Math.floor((this.fillStyle.focus_.x / width) * 100 + 50) + '%';
	focus.y= Math.floor((this.fillStyle.focus_.y / height) * 100 + 50) + '%';
	var colors= [];
	if(this.fillStyle.type_== 'gradientradial') {
	var inside= (this.fillStyle.radius1_ / dimension * 100);
	var expansion= (this.fillStyle.radius2_ / dimension * 100) - inside;
	} else {
	var inside= 0;
	var expansion= 100;
	}
	var insidecolor= {offset: null, color: null};
	var outsidecolor= {offset: null, color: null};
	this.fillStyle.colors_.sort(function(cs1, cs2) {
	return cs1.offset - cs2.offset;
	});
	for(var i= 0;i < this.fillStyle.colors_.length;i++) {
	var fs= this.fillStyle.colors_[i];
	colors.push( (fs.offset * expansion) + inside, '% ', fs.color, ',');
	if(fs.offset > insidecolor.offset || insidecolor.offset== null) {
	insidecolor.offset= fs.offset;
	insidecolor.color= fs.color;
	}
	if(fs.offset < outsidecolor.offset || outsidecolor.offset== null) {
	outsidecolor.offset= fs.offset;
	outsidecolor.color= fs.color;
	}
	}
	colors.pop();
	lineStr.push('<g_vml:fill',	' color=\'', outsidecolor.color, '\'',	' color2=\'', insidecolor.color, '\'',	' type=\'', this.fillStyle.type_, '\'',	' focusposition=\'', focus.x, ', ', focus.y, '\'',	' colors=\'', colors.join(''), '\'',	' opacity=\'', opacity, '\' />');
	} else if(aFill) {
	lineStr.push('<g_vml:fill color=\'', color, '\' opacity=\'', opacity, '\' />');
	} else {
	lineStr.push(
	'<g_vml:stroke',	' opacity=\'', opacity,'\'',	' joinstyle=\'', this.lineJoin, '\'',	' miterlimit=\'', this.miterLimit, '\'',	' endcap=\'', processLineCap(this.lineCap) ,'\'',	' weight=\'', this.lineWidth, 'px\'',	' color=\'', color,'\' />'
	);
	}
	lineStr.push('</g_vml:shape>');
	this.element_.insertAdjacentHTML('beforeEnd', lineStr.join(''));
	this.currentPath_= [];
};
contextPrototype.fill= function() {
	this.stroke(true);
}
contextPrototype.closePath= function() {
	this.currentPath_.push({type: 'close'});
};
contextPrototype.getCoords_= function(aX, aY) {
	return {
	x: (aX * this.m_[0][0] + aY * this.m_[1][0] + this.m_[2][0]),	y: (aX * this.m_[0][1] + aY * this.m_[1][1] + this.m_[2][1])
	}
};
contextPrototype.save= function() {
	var o= {};
	copyState(this, o);
	this.aStack_.push(o);
	this.mStack_.push(this.m_);
	this.m_= matrixMultiply(createMatrixIdentity(), this.m_);
};
contextPrototype.restore= function() {
	copyState(this.aStack_.pop(), this);
	this.m_= this.mStack_.pop();
};
contextPrototype.translate= function(aX, aY) {
	var m1= [ [1,0,0], [0,1,0], [aX,aY,1] ];
	this.m_= matrixMultiply(m1, this.m_);
};
contextPrototype.rotate= function(aRot) {
	var c= Math.cos(aRot);
	var s= Math.sin(aRot);
	var m1= [ [c,  s, 0], [-s, c, 0], [0,  0, 1] ];
	this.m_= matrixMultiply(m1, this.m_);
};
contextPrototype.scale= function(aX, aY) {
	var m1= [ [aX, 0,  0], [0,  aY, 0], [0,  0,  1] ];
	this.m_= matrixMultiply(m1, this.m_);
};
contextPrototype.clip= function() {};
contextPrototype.arcTo= function() {};
contextPrototype.createPattern= function() {return new CanvasPattern_;};
function CanvasGradient_(aType) {
	this.type_= aType;
	this.radius1_= 0;
	this.radius2_= 0;
	this.colors_= [];
	this.focus_= {x: 0, y: 0};
}
CanvasGradient_.prototype.addColorStop= function(aOffset, aColor) {
	aColor= processStyle(aColor);
	this.colors_.push({offset: 1-aOffset, color: aColor});
};
function CanvasPattern_() {}
G_vmlCanvasManager= G_vmlCanvasManager_;
CanvasRenderingContext2D= CanvasRenderingContext2D_;
CanvasGradient= CanvasGradient_;
CanvasPattern= CanvasPattern_;
})();
}

/* CanvasPainter.js		Rafael Robayna	  http://caimansys.com/painter/ */
var tcanvas= null;
var tcontext= null;
var loopfindcp= 10;
var tstartpos= {x:-1,y:-1};
var tcurpos= {x:-1,y:-1};
var toff= {x:0,y:0};
var tmousestate= 0;
var pathstr= '';
var canvasready= false;
var canvasreadonly= false;
function drawBrush(context, pntFrom, pntTo){
	if(context==undefined)
	{	alert('ERROR: JS canvas painter not initalized properly');
		return false;
	}context.beginPath();
	context.moveTo(pntFrom.x, pntFrom.y);
	context.lineTo(pntTo.x, pntTo.y);
	context.stroke();
	context.closePath();
}
//usage: clearCanvas(tcontext,tcanvas.width,tcanvas.height);
function clearCanvas(context,cwidth,cheight) {
	context.beginPath();
	context.clearRect(0,0,cwidth,cheight);
	context.closePath();
	setpathstr('',false);
}
//document.getElementById('canvas').toDataURL();	//to save as png file (only on non IE)
function drawstr(context,instr,scale){	
	var ary1, ary2, pos, lastpos, instrscaled;
	if(instr.search(/\d/)<0)
	{	return;	}
	if(isNaN(scale))
	{	scale= 1;
	}ary1= instr.split("-");
	instrscaled= '-';
	for(var i=1; i<ary1.length;i++)
	{	context.beginPath();
		ary2= ary1[i].split("_");
		for(var j=0;j<ary2.length;j++)
		{	pos= ary2[j].split(",");
			pos[0]= parseInt(pos[0]*scale);
			pos[1]= parseInt(pos[1]*scale);
			instrscaled+= pos[0]+','+pos[1];			
			if(isNaN(pos[0])|| isNaN(pos[1]))
			{	continue;
			}if(j==0)
			{	context.moveTo(pos[0], pos[1]);				
			}else
			{	context.quadraticCurveTo(lastpos[0], lastpos[1], (pos[0]+lastpos[0])/2, (pos[1]+lastpos[1])/2);
				//context.lineTo(pos[0], pos[1]);				
			}lastpos= pos;
			instrscaled+= '_';
		}if(!isNaN(pos[0])&& !isNaN(pos[1]))
		{	context.lineTo(pos[0], pos[1]);
		}
		context.stroke();
		context.closePath();
		instrscaled+= '-';
	}setpathstr(instrscaled,true);
}
function CanvasPainter(canvasName)
{	tcanvas= document.getElementById(canvasName);
	//html body not fully loaded yet (onload), wait a second to retry init
	if((tcanvas==undefined|| !tcanvas.getContext)&& loopfindcp>0)
	{	//alert("'"+canvasName+"' canavas element undefined");
		loopfindcp--;
		window.setTimeout('CanvasPainter(\''+canvasName+'\')',1000);
		return false;
	}
	toff= jstotaloffset(tcanvas);
	if(tcanvas.offsetWidth!=undefined)
	{	tcanvas.width= tcanvas.offsetWidth;
	}if(tcanvas.offsetHeight!=undefined)
	{	tcanvas.height= tcanvas.offsetHeight;
	}//alert('DEBUG 	x-y-w-h:'+toff.x+'|'+toff.y+'|'+tcanvas.offsetWidth+'|'+tcanvas.offsetHeight);
	tcontext= tcanvas.getContext('2d');
	tcontext.lineWidth= 2;
	if(tcanvas.style.color!=undefined)
	{	setColor(tcontext,tcanvas.style.color);
	}if(typeof pathstr=='object')
	{	if(pathstr.value.length>0)
		{	drawstr(tcontext,pathstr.value,1);
		}
	}else if(pathstr.length>0)
	{	drawstr(tcontext,pathstr,1);
	}if(document.all)
	{	tcanvas.onselectstart= new Function("return false");
	}if(!canvasreadonly)
	{	initMouseListeners(tcanvas);
	}canvasready= true;
	return true;
}
//usgae: setColor('#000000');
function setColor(context,color) {
	context.fillStyle = color;
	context.strokeStyle = color;
}
function getCanvasMousePos(canvas,e){
	//toff= jstotaloffset(canvas);	
	if(!e.clientX&&	e.pageX)
	{	return {x: e.pageX- toff.x, y: e.pageY- toff.y};
	}return {x: e.clientX+document.body.scrollLeft- toff.x,
		y: e.clientY+document.body.scrollTop- toff.y};
}
function mouseMoveActionPerformed(e){
	if(tmousestate==0){	return false;}
	tcurpos= getCanvasMousePos(tcanvas,e);	
	drawBrush(tcontext, tstartpos, tcurpos);
	tstartpos = tcurpos;
	setpathstr('_'+tcurpos.x+','+tcurpos.y,true);
	//tmousestate= 2;
}
function mouseDownActionPerformed(e) {	
	tstartpos = getCanvasMousePos(tcanvas,e);	
	setpathstr('-'+tstartpos.x+','+tstartpos.y,true);
	tcontext.lineJoin = 'round';	
	tmousestate= 1;
}
function mouseUpActionPerformed(e) {	
	if(tmousestate==0) return;
	//tcurpos= getCanvasMousePos(tcanvas,e);
	tmousestate= 0;
}
function initMouseListeners(canvas) {		
	if(document.body.attachEvent){
		canvas.attachEvent('onmousemove', mouseMoveActionPerformed);
		canvas.attachEvent('onmousedown', mouseDownActionPerformed);
		//canvas.attachEvent('onmouseup', mouseUpActionPerformed);
		//canvas.attachEvent('onmouseout', mouseUpActionPerformed);
		document.body.attachEvent('onmouseup', mouseUpActionPerformed);
	}else{
		canvas.addEventListener('mousemove', mouseMoveActionPerformed, false);
		canvas.addEventListener('mousedown', mouseDownActionPerformed, false);
		//canvas.addEventListener('mouseup', mouseUpActionPerformed, false);
		//canvas.addEventListener('mouseout', mouseUpActionPerformed, false);
		document.body.addEventListener('mouseup', mouseUpActionPerformed, false);
	}
}
function setpathstr(val,appendflag)
{	if(typeof pathstr=='object')
	{	if(appendflag){pathstr.value+= val;}
		else{pathstr.value= val;}
	}else
	{	if(appendflag){pathstr+= val;}
		else{pathstr= val;}
	}
}
function getpathstrscale(scale)
{	var pos,ary1,ary2,ary3, pathstr2;
	pathstr2= pathstr;
	if(typeof pathstr=='object')
	{	pathstr2= pathstr.value;
	}ary1= pathstr2.split("-");
	pathstr2='-';
	for(var i=1; i<ary1.length;i++)
	{	ary2= ary1[i].split("_");
		for(var j=0;j<ary2.length;j++)
		{	pos= ary2[j].split(",");
			pos[0]= parseInt(pos[0]*scale);
			pos[1]= parseInt(pos[1]*scale);
			if(!isNaN(pos[0])&& !isNaN(pos[1]))
			{	pathstr2+= pos[0]+','+pos[1];
				pathstr2+= '_';
			}
		}pathstr2+= '-';
	}return pathstr2;
}
if(typeof jstotaloffset!='function')
{	function jstotaloffset(elem,level)
	{	var curleft=0, curtop=0;
		while(elem.offsetParent)
		{	curleft += elem.offsetLeft;
			curtop += elem.offsetTop;
			elem= elem.offsetParent;
			if(level!=undefined)
			{	level--;
				if(level<=0){break;}
			}
		}return {x:curleft, y:curtop};
	}	
}