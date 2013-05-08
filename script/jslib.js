/* Library of commonly used Javascript functions*/
/*example usage: jsaddvent(window,'load',function(){func1(arg1);});*/
function jsaddevent(elem,evt,func)
{	if(func==undefined)
	{	return false;
	}if((typeof(func)).search(/string/i)>=0)
	{	if(func.indexOf('(')>=0&& func.indexOf('new function(')<0)
		{	func= 'new function(e){'+func+'}';
		}func= eval(func);
	}if(evt==undefined)
	{	return false;
	}if(evt.search(/on/i)==0)
	{	evt= evt.substring(2);
	}if(elem.addEventListener)
	{	elem.addEventListener(evt,func,false);
	}else if(elem.attachEvent)
	{	elem.attachEvent('on'+evt,func);
	}
}
function jsdelevent(elem,evt,func)
{	if(document.removeEventListener)
	{	elem.removeEventListener(evt,func,false);
	}else if(document.detachEvent)
	{	elem.detachEvent('on'+evtt,func);
	}
}
/*enables adding multiple js event handlers. e.g. jsaddevent2('form1.onfocus','myfunc1(\'str1\')');*/
function jsaddevent2(evtname,funcname)
{	var evt= eval(evtname);
	if(funcname.indexOf('(')<0)
	{	funcname += '(e)';
	}if(funcname.charAt(funcname.length-1)!=';')
	{	funcname +=  ';';
	}if(typeof evt=='function')
	{	funcname= 'evt(e); '+funcname;
	}eval(evtname+'= function(e){'+funcname+'}');
}

/*CSS ordering: latter css always overrides previous css with same class/id/tagname
note: causes operation aborted error in IE7 if no style tag earlier in body. see http://support.microsoft.com/kb/927917
*/
function jsaddcss(cssStr)
{	var target= document.getElementsByTagName('style');
	if(target.length>0)
	{	target= target[0];
		if(target.styleSheet)
		{	target.styleSheet.cssText= cssStr+target.styleSheet.cssText;
		}else
		{	target.innerHTML= cssStr+target.innerHTML;
		}
	}else
	{	target= document.createElement('style');
		document.body.appendChild(target);
		target.type='text/css'; target.rel='stylesheet';
		if(target.styleSheet)
		{	target.styleSheet.cssText= cssStr;
		}else
		{	var cssnode=document.createTextNode(cssStr);
			target.appendChild(cssnode);
		}
	}
}
/*	calculates aboslute position of element
	credits: www.quirksmode.org/js/findpos.html	*/
function jstotaloffset(elem,level)
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
/*iterates all attributes& properties of obj, like event obj*/
function jsobjiter(obj,avoidregex)
{	var outtext='';
	if(obj==undefined)
	{	return false;
	}if(avoidregex==undefined)
	{	avoidregex= /textContent|innerHTML|outerHTML|innerText|outerText/;
	}for(var prop in obj)
	{	if(prop.search(avoidregex)==0)
		{	continue;
		}
		/*if(!obj.hasOwnProperty(prop)){continue;}*/
		outtext+= prop+': '+obj[prop]+'<br>';
	}return outtext;
}
function js_getevttarget(e)
{	var target1=null;
	if(e==undefined&& window.event)
	{	e= window.event;
	}if(e.target)
	{	target1= e.target;
	}else if(e.srcElement)
	{	target1 = e.srcElement;
	}return target1;
}

//workaround for IE 8- since their getElementsByName fails for new createElement
function jsGetByName(name1,parentnode)
{	var ary1= new Array();
	if(name1==undefined)
	{	return false;
	}if(parentnode==undefined)
	{	parentnode= document.body;
	}if(parentnode==undefined)
	{	parentnode= document.documentElement;
	}for(var j=0;j<parentnode.childNodes.length;j++)
	{	var node1= parentnode.childNodes[j];
		if(node1.name!=undefined&& node1.name==name1)
		{	ary1[ary1.length]= node1;
		}var tmpary= jsGetByName(name1,node1);
		if(tmpary.length>0)
		{	ary1= ary1.concat(tmpary);
		}
	}return ary1;
}
function jsGetByTagAndName(tag1,name1)
{	var ary1= new Array(), tagary;
	if(tag1==undefined||name1==undefined)
	{	return false;
	}tagary= tag1.split('|');
	for(var k=0;k<tagary.length;k++)
	{	var ary2= document.getElementsByTagName(tagary[k]);
		//alert('DEBUG getByTag: '+tagary[k]+'|'+ary2.length);
		for(var j=0;j<ary2.length;j++)
		{	if(ary2[j].name!=undefined&& ary2[j].name==name1)
			{	ary1[ary1.length]= ary2[j];
			}
		}
	}return ary1;
}
/*Get index for target out of all elements with same name (e.g, price-quantity-name rows, radio buttons)*/
function jsGetElemNameIndex(target)
{	if(!target|| !target.name)
	{	return false;
	}var group1= jsGetByName(target.name);
	var ind1=0;
	for(var i=0;i<group1.length;i++)
	{	if(group1[i]==target)
		{	ind1= i;
			break;
		}
	}return ind1;
}

function get_ie_ver()
{	if (/MSIE (\d+\.\d+);/.test(navigator.userAgent))
	{	return new Number(RegExp.$1);
	}return false;
}
function jsclosewindow()
{	if(navigator.userAgent.search(/MSIE [456]/)>=0){window.opener='x';}
	else{window.open('','_parent','');}
	window.close();
}

function jsnumeric(val,optstr)
{	if(optstr=='phone')
	{return val.replace(/[^\d-x ]+/,'');
	}if(optstr=='negint')
	{return val.replace(/[^\d-]+/,'');
	}if(optstr=='negdec')
	{return val.replace(/[^\d-.]+/,'');
	}if(optstr!=undefined)
	{return val.replace(/[^\d.]+/,'');
	}return val.replace(/[^\d]+/,'');
}/*e.g. onkeyup='jsnumericreplace(this,event.keyCode);' */
function jsnumericreplace(target,keycode,optstr)
{	if(target==undefined||(keycode>=37&&keycode<=40))
	{return true;
	}target.value= jsnumeric(target.value,optstr);
}
/*e.g. onkeyup='this.value=jstelef(this.value);' */
function jstelef(val)
{	return val.replace(/[^\d- :ext\.\(\)]+/,'');
}

/*for enforcing account# & telephone patterns . e.g. okeyup='sapplymask(this,event.keyCode,'DDDD-DDDDD');' */
function jsapplymask(target,keycode,mask)
{	if(target==undefined||(keycode>=37&&keycode<=40))
	{return true;}
	var newval= target.value;
	var i=0;
	if(!target.maxLength|| target.maxLength!=mask.length)
	{	target.maxLength= mask.length;
	}while(i<newval.length)
	{	if(i>=mask.length)
		{	break;
		}var maski= mask.substring(i,i+1);
		var curchar= newval.substring(i,i+1);
		if(maski.search(/-|:|\(|\)|\/| /)>=0&&curchar!=maski)
		{	newval= newval.substring(0,i)+maski+newval.substring(i);
			i++;
		}else if(maski=='D')
		{	if(curchar.search(/\d/)<0)
			{	break;
			}
		}else if(maski!=curchar)
		{	break;
		}i++;
	}target.value= newval.substring(0,i);
}


/* Math.round(n*Math.pow(10,d))/Math.pow(10,d) && n.toFixed(d) rounding method has flaws
	(e.g. for 4.185 to 2 decimcal.)
	This workaround methods round decimal to 'digit' decimal places.
	'upnum' determines min ending digit to round up, default is 5.
	use upnum=0 for ceil, upnum=9 for floor
*/
function jsrounddec(num,digit,upnum)
{	if(num==undefined)
	{	return num;
	}if(upnum==undefined)
	{	upnum=5;
	}if(digit==undefined)
	{	digit= 2;
	}else
	{	digit= parseInt(digit);}
	if(isNaN(digit)|| digit<0)
	{	return num;	}
	num= ''+num;
	var decind= num.indexOf('.');
	if(decind<0||num.length<=decind+digit+1)
	{	return num;}
	var decider= parseInt(num.charAt(decind+digit+1));
	if(decider<upnum)
	{	return num.substring(0,decind+digit+1);
	}var str1, x, startind=0;
	str1= num.substring(0,decind)+num.substring(decind+1,decind+digit+1);
	if(str1.charAt(0)=='0'||decind==0)
	{	str1='1'+str1;
		startind=1;
		decind++;
	}x= parseInt(str1);
	str1=''+x;
	x++;
	num=''+x;
	if(str1.length<num.length)
	{	decind++;}
	return num.substring(startind,decind)+'.'+num.substring(decind);
}
/*hide all error images within elem. example: jsimgerrorhide(document.body);*/
function jsimgerrorhide(elem)
{	var target;
	for(var i=0;i<elem.getElementsByTagName('img').length;i++)
	{	target= elem.getElementsByTagName('img')[i];
		if(target.onerror==undefined)
		{	target.onerror= function(e){this.style.visibility='hidden';/*this.style.width=0;this.style.height=0;*/};
			var tmp=target.src;target.src='tmp';target.src=tmp;
		}
	}
}

/*credits: developer.mozilla.org/en/CSS/-moz-transform,
	msdn.microsoft.com/en-us/library/ms533014(VS.85).aspx,
	en.wikipedia.org/wiki/Transformation_matrix*/
function jscssmatrix(elem,a,b,c,d)
{	if(elem.style.transform)
	{	/*css3*/
		elem.style.transform='matrix('+a+','+b+','+c+','+d+',0,0)';
	}else if(elem.style.filter)
	{	/*IE*/
		elem.style.filter='progid:DXImageTransform.Microsoft.Matrix(sizingMethod=\'auto expand\',Dx=0,Dy=0,M11='+a+',M21='+b+',M12='+c+',M22='+d+')';
	}else
	{	/*safari*/
		elem.style.webkitTransform='matrix('+a+','+b+','+c+','+d+',0,0)';
		/*mozilla*/
		elem.style.MozTransform='matrix('+a+','+b+','+c+','+d+',0,0)';
	}
}
function jsmousedrag(e)
{	if(!e&&window.event){e=window.event;}
	if(!e)/*default event registration - when no args provided*/
	{	jsaddevent(document,'mousedown',jsmousedrag);
		return false;
	}if(e.type=='mousedown')
	{	window.dragelem= e.target?e.target:e.srcElement;
		window.dragelem.style.position='relative';
		window.dragelem.tx = parseInt(window.dragelem.style.left+0)-e.clientX;
		window.dragelem.ty = parseInt(window.dragelem.style.top+0)-e.clientY;
		jsaddevent(document,'mousemove',jsmousedrag);
		jsaddevent(document,'mouseup',jsmousedrag);
		return false;
	}if(e.type=='mousemove'&& window.dragelem)
	{	if(e.preventDefault){e.preventDefault();}
		window.dragelem.style.left= window.dragelem.tx+ e.clientX;
		window.dragelem.style.top= window.dragelem.ty+ e.clientY;
		return false;
	}if(e.type=='mouseup'&& window.dragelem)
	{	window.dragelem= null;
		jsdelevent(document,'mousemove',jsmousedrag);
		jsdelevent(document,'mouseup',jsmousedrag);
		return false;
	}
}

/*credits: forums.devarticles.com/javascript-development-22/how-to-disable-right-click-in-firefox-87551.html
	USAGE: if(navigator.userAgent.search(/MSIE/)>=0)){jsaddevent(document,'mousedown',jsnorightclick);}
			else{jsaddevent(document,'click',jsnorightclick);}
*/
function jsnorightclick(e)
{	var rightclick= false;
	if(!e){e=window.event;}
	if(e.which){rightclick= (e.which==3);}
	else if(e.button){rightclick= (e.button==2);}
	if(rightclick)
	{	alert('Right click disabled');
		if(e.preventDefault){e.preventDefault();}
		if(e.stopPropagation){e.stopPropagation();}
		return false;
	}
}

/*disable F8 and print screen keys; Source: forums.asp.net/p/380325/380325.aspx
	css disable print web- credits: www.codeguru.com/forum/archive/t-202533.html
*/
function jsnoprint_keydown(e)
{	if(!e){e= window.event;}
	if(e.keyCode==85)
	{	window.ukeydown=true;
		/*if(clipboardData){clipboardData.clearData();}*/
	}if(e.keyCode==119||(window.ukeydown&& e.keyCode==17))
	{	window.ukeydown=false;
		/*alert('That key is disabled!');*/
		if(e.preventDefault){e.preventDefault();}
		if(e.stopPropagation){e.stopPropagation();}
		return false;
	}return true;
}
function jsnoprint_keyup(e)
{	if(!e){e= window.event;}
	if(e.keyCode==85){window.ukeydown=false;}
	if(e.keyCode==44)
	{	/*alert('That key is disabled!');*/
		/*if(clipboardData){clipboardData.clearData();}*/
		if(e.preventDefault){e.preventDefault();}
		if(e.stopPropagation){e.stopPropagation();}
		return false;
	}return true;
}
function jsnoprint_init()
{	jsaddevent(document,'keydown',jsnoprint_keydown);
	jsaddevent(document,'keyup',jsnoprint_keyup);
	jsaddcss('@media print{*{display:none;visibility:hidden;}}');
}
/*jsaddevent(window,'load',jskeynoenter_init);*/
function jskeynoenter_flag(val1)
{	if(val1==undefined)
	{	return window.noenterflag;
	}window.noenterflag= val1;
}
function jskeynoenter(e)
{	var key1, curtarget;
	if(!e&&window.event){e=window.event;}
	else if(!e&&event){e= event;}
	if(!e){return false;}
	if(e.srcElement)
	{	curtarget= e.srcElement;
	}else if(e.target)
	{	curtarget= e.target;
	}key1= e.keyCode;
	if(key1==13)
	{	jskeynoenter_flag(false);
		if(e.preventDefault){e.preventDefault();}
		if(e.stopPropagation){e.stopPropagation();}
		if(curtarget!=undefined)
		{	if(curtarget.nextSibling)
			{	do
				{	curtarget= curtarget.nextSibling;
					if(curtarget.tagName&& curtarget.tagName.search(/input|select|textarea/i)>=0)
					{	break;
					}
				}while(curtarget.nextSibling);
				if(curtarget.focus!=undefined)
				{	curtarget.focus();
				}
			}
		}
	}else
	{	jskeynoenter_flag(true);
	}return jskeynoenter_flag();
}
function jskeynoenter_init()
{	var targets= document.getElementsByTagName('input');
	targets= concat_obj(targets,document.getElementsByTagName('textarea'));
	for(var i=0;i<targets.length;i++)
	{	if(targets[i].type&& targets[i].type.search(/submit/i)>=0)
		{	jsaddevent(targets[i],'click','jskeynoenter_flag(true);');
		}else
		{	jsaddevent(targets[i],'keydown',jskeynoenter);
			jsaddevent(targets[i],'focus','jskeynoenter_flag(false);');
			jsaddevent(targets[i],'blur','jskeynoenter_flag(true);');
		}
	}targets= document.getElementsByTagName('form');
	for(var i=0;i<targets.length;i++)
	{	jsaddevent(targets[i],'submit','return jskeynoenter_flag();');
	}
}

function jsstr2obj(target)
{	if(typeof(target)!='string')
	{	return target;
	}var target2= document.getElementById(target);
	if(target2!=undefined)
	{	return target2;
	}target2= document.getElementsByName(target);
	if(target2.length>0)
	{	return target2[0];
	}target2= jsGetByName(target);
	if(target2.length>0)
	{	return target2[0];
	}return null;
}

function jslookupchange(target,lookupobj,curval)
{	if(typeof(target)=='string')
	{	target= jsstr2obj(target);
	}if(typeof(target)!='object'|| typeof(lookupobj)!='object')
	{	return false;
	}if(lookupobj[curval]!=undefined&& lookupobj[curval].length>0)
	{	target.value= lookupobj[curval];
	}else if(curval.length>0)
	{	/*alert('DEBUG '+curval+' not found in jslookup map');*/
	}
}
/*add textbox input in front of an select-option dropdown box*/
function jssyncchildsize(parent,child)
{	var h1, w1, x1,y1;
	x1= parseInt(parent.offsetLeft);
	y1= parseInt(parent.offsetTop);
	w1= parseInt(parent.offsetWidth);
	h1= parseInt(parent.offsetHeight);	
	if(navigator.userAgent.search(/MSIE/)>=0)
	{	h1-=5;
		w1-=22;
	}else
	{	w1-=18;
	}with(child.style){position='absolute';left=x1;top=y1;width=w1;height=h1;}
}/*add textbox input in front of an select-option dropdown box*/
function jsselect_searchbox(selelem)
{	if(typeof(selelem)=='string')
	{	selelem= jsstr2obj(selelem);
	}if(selelem==undefined)
	{	return false;
	}var boxname= '_textbox';
	if(selelem.name){boxname= selelem.name+boxname;}
	var txtbox= jsGetByName(boxname);
	if(txtbox.length>0){return false;}
	txtbox= document.createElement('input');	txtbox.name= boxname;
	jssyncchildsize(selelem,txtbox);
	if(selelem.value){txtbox.value=selelem.value;}
	var valueary= Array();
	for(var i=0;i<selelem.length;i++)
	{if(selelem.options[i].value.length>0)
		{valueary.push(selelem.options[i].value);}
	}txtbox.setAttribute('autocomplete','off');
	if(selelem.parentNode&& selelem.nextSibling)
	{	selelem.parentNode.insertBefore(txtbox,selelem.nextSibling);
	}else if(selelem.parentNode)
	{	selelem.parentNode.appendChild(txtbox);
	}else
	{	document.body.appendChild(txtbox);
	}txtbox.onfocus= function(e){jskeynoenter_flag(false);};
	jsaddevent(txtbox,'blur','jskeynoenter_flag(true);');
	jsaddevent(txtbox,'keydown',jskeynoenter);
	txtbox.onkeyup= function(e){
		var foundcnt= jssearchbox_dropdown(this,valueary);
		if(foundcnt<=0){selelem.selectedIndex=0;}};
	txtbox.style.zIndex=5;
	selelem.style.zIndex=-1;
	selelem.onchange= function(e){
		if(this.focused&& this.value.length>0)
		{txtbox.value= this.value;}};
	selelem.onfocus= function(e){this.focused=true;};
	selelem.onblur= function(e){this.focused=false;};
	selelem.onclick= selelem.onfocus;
	jsaddevent(window,'resize',function(){jssyncchildsize(selelem,txtbox)});
}

/*dropdown of div and anchor of matching values in valueary for txtbox.value*/
function jssearchbox_dropdown(txtbox,valueary)
{	if(!txtbox.value|| txtbox.value.search(/\w/)<0){return false;}
	var optname= '_searchopt';
	if(txtbox.name){optname= txtbox.name+optname;}
	var optparent= document.getElementById(optname);
	if(optparent!=undefined){optparent.parentNode.removeChild(optparent);optparent= null;}
	var foundcnt=0;
	for(var i=0;i<valueary.length;i++)
	{	if(valueary[i].toLowerCase().indexOf(txtbox.value.toLowerCase())==0)
		{	foundcnt++;
			if(optparent==undefined)
			{	optparent=document.createElement('div');	optparent.id=optname;
				with(optparent.style){position='absolute';cursor='pointer';backgroundColor='#F4F4F4';border='1px solid black';zIndex='10';}
				with(optparent.style){top=txtbox.offsetTop+txtbox.offsetHeight;left=txtbox.offsetLeft;}
				if(txtbox.nextSibling)
				{txtbox.parentNode.insertBefore(optparent,txtbox.nextSibling);}
				else{txtbox.parentNode.appendChild(optparent);}
				optparent.onblur= function(e){if(this.focused){this.focused=false;
						setTimeout('var x1= document.getElementById("'+this.id+'");if(x1){x1.onblur();}',400);}
					else{txtbox.focused=false;this.parentNode.removeChild(this);}};
				txtbox.onblur= function(e){
					var optname1= '_searchopt';if(this.name){optname1= this.name+optname1;}
					if(!document.getElementById(optname1)){this.focused=false;return false;}
					setTimeout('var x1=document.getElementById("'+optname1+'");if(x1){x1.onblur();}',200);};
			}newopt= document.createElement('a');
			with(newopt.style){textDecoration='none';}
			newopt.innerHTML= valueary[i];	newopt.title=i+1;
			newopt.onfocus=	function(e){this.parentNode.focused=true;txtbox.value=this.innerHTML;
				var id2=txtbox.name.replace('_textbox',''); id2= jsGetByName(id2);
				if(id2){id2=id2[0];id2.selectedIndex=this.title;}};
			newopt.onmousedown= newopt.onfocus;
			optparent.appendChild(newopt);
			newopt= document.createElement('br');	optparent.appendChild(newopt);
		}
	}return foundcnt;
}

function jsselect_setvalue(target,targetval)
{	if(typeof(target)=='string')
	{	target= jsstr2obj(target);
	}if(target==undefined)
	{	return false;
	}if(targetval==undefined)
	{	targetval='';
	}if(!target.options)
	{	target.value= targetval;
		return targetval;
	}for(var i=0;i<target.options.length;i++)
	{	if(targetval==target.options[i].value)
		{	target.selectedIndex= i;
			return i;
		}
	}return false;
}function jsradio_setvalue(targetname,targetval)
{	var targets= jsGetByName(targetname);
	for(var i=0;i<targets.length;i++)
	{	if(targets[i].value.search(targetval)>=0)
		{	targets[i].checked='checked';
		}else
		{	targets[i].checked=false;
		}
	}
}

/*popup span help window using input content as innerhtml*/
function jspopupshow(content)
{	var obj1id= 'popupspan';	var obj1type= 'span';
	if(content.search(/^http:/i)>=0)
	{	obj1id= 'popupiframe';	obj1type='iframe';
	}var obj1= document.getElementById(obj1id);
	if(obj1==undefined)
	{	obj1= document.createElement(obj1type);
		obj1.id= obj1id;	obj1.className= obj1id;
		document.body.appendChild(obj1);
	}if(obj1type.search(/frame/i)>=0)
	{	obj1.src= content;
		obj1.frameBorder= 0;
	}else
	{	obj1.innerHTML= content;
	}return obj1;
}
function jspopupspan(content,target)
{	var obj1= jspopupshow(content);
	if(!obj1.onmouseout)
	{	obj1.onmouseout= function(){obj1.style.display='none';obj1.style.zIndex=0;}
		obj1.onblur= obj1.onmouseout;
	}var toffset= jstotaloffset(target);
	with(obj1.style){display='block';position='absolute';left=toffset.x;top=(toffset.y+20);zIndex=5;}
	if(!target.onmouseout)
	{	target.onmouseout= obj1.onmouseout;
	}
}
/* <input type='reset'> button will not clear boxes with pre-set value, thus this function as workaround*/
function jsclearinput(parentelem)
{	if(parentelem==undefined){parentelem=document;}
	var target= parentelem.getElementsByTagName('input');
	for(var i=0;i<target.length;i++)
	{	if(!target[i].type||target[i].type.search(/text/i)>=0)
		{	target[i].value= '';
		}
		else if(target[i].checked)
		{	target[i].checked='';
		}
	}target= document.getElementsByTagName('select');
	for(var i=0;i<target.length;i++)
	{	target[i].selectedIndex='';
	}target= document.getElementsByTagName('textarea');
	for(var i=0;i<target.length;i++)
	{	target[i].value= '';
	}
}

function jsshowhidebtn(elemid,btntxt,btnstyle)
{	var newbtn= document.createElement('input');
	newbtn.type='button';	newbtn.id=elemid+'_btn';	newbtn.value= btntxt;
	if(btnstyle){with(newbtn.style){eval(btnstyle);}}
	var target=document.getElementById(elemid);
	if(target==undefined)
	{	return false;
	}newbtn.onclick=function(e){
		var target= document.getElementById(this.id.replace(/_btn$/,''));
		if(target==undefined)
		{	return false;
		}if(!target.className|| target.className.search(/show/i)>=0)
		{	target.className='jssectionhide';
			this.value=this.value.replace(/^hide/i,'Show');
		}else
		{	target.className='jssectionshow';
			this.value=this.value.replace(/^show/i,'Hide');
		}};
	target.parentNode.insertBefore(newbtn,target);
	return newbtn;
}

function removeAllChild(target)
{	if(target==undefined)
	{	return false;
	}while(target.childNodes.length>0)
	{	target.removeChild(target.firstChild);
	}
}

/*	Helper function to merge 2 objects with as much compatibilty and editabilty as possible.
	Implmented compatibilty: number key matching, and number value adding
	E.g. usage: targets= concat_obj(targets,document.getElementsByTagName('textarea'));
*/function concat_obj(obj1,obj2,dupe_suffix)	
{	var obj_union= new Object();
	var k1, k2, i_max= -1;
	if(dupe_suffix==undefined|| typeof(dupe_suffix)!='string')
	{	dupe_suffix= '_2';
	}for(k1 in obj1)
	{	k2= parseInt(k1);
		if(!isNaN(k2))
		{	k1= k2;
		}if(typeof(k1)=='number'&& k1>i_max)
		{	i_max= k1;		
		}
		if(typeof(obj1[k1])=='number')
		{	obj_union[k1]= 0+obj1[k1];
		}else if(typeof(obj1[k1])=='string')
		{	obj_union[k1]= ''+obj1[k1];
		}else
		{	obj_union[k1]= obj1[k1];
		}
	}	
	for(k1 in obj2)
	{	if(obj_union[k1]!= undefined)
		{	k2= parseInt(k1);
			if(!isNaN(k2))
			{	k1= k2;
			}if(typeof(obj_union[k1])=='number'&& typeof(obj2[k1])=='number')
			{	obj_union[k1]+= obj2[k1];
			}else if(typeof(k1)=='number')
			{	i_max++;
				obj_union[i_max]= obj2[k1];
			}else
			{	k2= k1+dupe_suffix;
				obj_union[k2]= obj2[k1];
			}
		}else
		{	obj_union[k1]= obj2[k1];
		}
	}return obj_union;
}

/*clone first line of srcnode table and append to target element
	useful: jsaddevent(window,'resize',function(){jsclonetblheader(srcnode,target,true)});
*/
function jsclonetblheader(srcnode,target,resetflag)
{	if(typeof(srcnode)=='string')
	{	srcnode= jsstr2obj(srcnode);
	}if(typeof(target)=='string')
	{	target= jsstr2obj(target);
	}if(resetflag!=undefined)
	{	removeAllChild(target);
	}var newtbl= srcnode.cloneNode(false);
	var copynode, curnode= newtbl;
	while(srcnode.childNodes.length>0&& srcnode.tagName.toUpperCase()!='TR')
	{	srcnode= srcnode.childNodes[0];
		copynode= srcnode.cloneNode(false);
		curnode.appendChild(copynode)
		curnode= curnode.childNodes[0];
	}var totalwidth=44;
	for(var i=0;i<srcnode.childNodes.length;i++)
	{	copynode= srcnode.childNodes[i].cloneNode(true);
		copynode.style.width= srcnode.childNodes[i].clientWidth;
		curnode.appendChild(copynode);
		totalwidth+= srcnode.childNodes[i].clientWidth;;
	}target.style.width= totalwidth;
	target.appendChild(newtbl);
}
/*moves an element always on top.*/
function jsscrolltopelem(target)
{	if(typeof(target)=='string')
	{	target= jsstr2obj(target);
	}if(target==undefined)
	{	return false;
	}var curscrolltop=0;
	if(document.documentElement&& document.documentElement.scrollTop)
	{	curscrolltop= document.documentElement.scrollTop;
	}else
	{	curscrolltop= document.body.scrollTop;
	}if(target.offset!=undefined)
	{	curscrolltop+= target.offset;
	}if(target.origpos!=undefined&& curscrolltop<=target.origpos)
	{	target.style.display='none';
		/*alert('DEBUG origpos: '+target.origpos);*/
	}else
	{	target.style.display='block';
		target.style.position='absolute';
		target.style.top= curscrolltop;
	}
}
/*e.g.	jsscrolltopelem_init('searchlisttbl','topelemfixed',10);
		jsclonetblheader('searchlisttbl','topelemfixed');	*/
function jsscrolltopelem_init(srcnode,target,offset1)
{	if(typeof(srcnode)=='string')
	{	srcnode= jsstr2obj(srcnode);
	}if(typeof(target)=='string')
	{	target= jsstr2obj(target);
	}if(typeof(jstotaloffset)=='function')
	{	var toffset= jstotaloffset(srcnode);
		target.origpos= toffset.y;
	}else
	{	target.origpos= srcnode.offsetTop;
	}if(offset1!=undefined)
	{	target.offset= offset1;
	}var f1= function(e){jsscrolltopelem(target);};
	jsaddevent(window,'scroll',f1);
}


/*jsscrollto: window.scrollTo(xcord,ycord);
	may be better off using <a name='anchor'>, then url appended '#anchor'
*/

/* JS time formats: 
		* default 'LocaleString' - 'M d, Y H:i:s'
		* numeric 'JSON' - 'YYYY-MM-DDTHH:mm:ss.sssZ'
	Example usage: jstimer('$timerid','$curtime'); */
function jstimer(timerid,offset,format)
{	var d, target, targetval;
	d= new Date();
	if(typeof(offset)=='string')
	{	offset= new Date(offset);
		offset= offset.getTime()-d.getTime();
	}target= document.getElementById(timerid);
	if(target==undefined){return;}
	if(typeof(offset)=='number')
	{	d.setTime(d.getTime()+offset);
	}targetval= d.toLocaleString();	
	if(format!=undefined&& format.search(/num/i)>=0)
	{	//	note: IE8- does not support toJSON string
		//targetval= d.toJSON().replace(/T/,' ').replace(/\.\d+Z/,'');		
		targetval= ''+d.getFullYear()+'-'+(d.getMonth()+1)+'-'+d.getDate();
		targetval+= ' '+d.getHours()+':'+d.getMinutes()+':'+d.getSeconds();		
		var t_regex = /(^|\D)(\d)($|\D)/;	//sprintf-like %02d
		var match = t_regex.exec(targetval);
		while(match!=undefined)
		{	targetval= targetval.replace(t_regex,match[1]+'0'+match[2]+match[3]);
			match = t_regex.exec(targetval);
		}
	}if(target.tagName.search(/input|textarea/i)>=0)
	{	target.value= targetval;
	}else
	{	target.innerHTML= targetval;
	}window.setTimeout('jstimer(\''+timerid+'\','+offset+',\''+format+'\')',1000);
}
/*jsredirect - timed relocation: window.setTimeout('window.location=\"index.html\";',1000);	1000= 1 sec */
/*In child window,	window.opener	=>	parent window obj
In child iframe,	parent	=>	parent window	obj (ex. parent.location='index.htm';)
*/
function jsopenwindow(url,wndname,wndparam)
{	if(wndname==undefined||wndname=='')
	{	wndname='_blank';
	}if(wndparam==undefined||wndparam=='')
	{	wndparam='toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=750,height=550,left=100,top=200';
	}var wnd2= window.open(url,wndname,wndparam);
	if(wnd2){wnd2.focus();} return wnd2;
}

function jsaddupload1(target,fieldname)
{	var x=null;
	x= document.createElement('input');
	x.name=fieldname; x.type='file';
	/*if(!x){x=document.createElement('<input type=\"file\" name=\"'+fieldname+'\">');}*/
	target.appendChild(x);
	x= document.createElement('br');
	target.appendChild(x);
}

/*	Use XMLhttp request to read data from another web site/file in Javascript
	param1 in format:  k1=v1&k2=v2&k3=v3....., (e.g. GET-param string)
	If async_func is specified, it is know as AJAX (Asynchronous JavaScript & XML)
	asynchronous = do not wait for read source to continue processing current page
	note: passing async_func as custom function might not work for IE5~ IE6
	postflag determine wheter to send using POST or GET method
*/
function ajax_read(url1,param1,async_func,postflag)
{	var xmlhttp;
	if(window.XMLHttpRequest)
	{	xmlhttp= new XMLHttpRequest();
	}else
	{	xmlhttp= new ActiveXObject('Microsoft.XMLHTTP');
		if(!xmlhttp)
		{	alert('XMLHTTP ActiveX component cannot be initailized!');
			return false;
		}
	}if(async_func!=undefined)
	{	if(typeof(async_func)!='function'&& ajax_asyncf_alert)
		{	async_func= ajax_asyncf_alert;
		}if(typeof(async_func)!='function')
		{	xmlhttp.onreadystatechange= async_func;
		}else
		{	async_func= null;
		}
	}//post method send
	if(postflag!=undefined&& postflag)
	{	xmlhttp.open('POST',url1, (async_func!=undefined));
		xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		xmlhttp.setRequestHeader('Content-length', param1.length);
		xmlhttp.setRequestHeader('Connection', 'close');
		xmlhttp.send(param1);
	}else
	{	if(param1== undefined)
		{	param1= '';
		}else if(param1.charAt(0)!='?')
		{	param1= '?'+param1;
		}xmlhttp.open('GET',url1+param1, (async_func!=undefined));
		xmlhttp.send();
	}if(async_func==undefined)
	{	return xmlhttp.responseText;
	}return xmlhttp;
}
function ajax_asyncf_alert()
{	if(this.readyState==4&& this.status==200)
	{	alert(this.responseText);
	}//other states are waiting for response
}

function jsparse_map2d(datastr)
{	if(datastr==undefined)
	{	return false;
	}var map2d= new Object();
	var dataary= datastr.split('\n');
	for(var i=0;i<dataary.length;i++)
	{	if(dataary[i].search(/\w/)>=0)
		{	dataary[i]= dataary[i].replace(/\s+\|\s+/,'|');
			dataary[i]= dataary[i].replace(/\s{3,}/,'|');
			var valuepair= dataary[i].split('|');
			if(map2d[valuepair[0]]==undefined)
			{	map2d[valuepair[0]]= new Object();
			}var map_sub= map2d[valuepair[0]];
			map_sub[valuepair[1]]= true;
			map2d[valuepair[0]]= map_sub;
		}
	}return map2d;
}
function jsbuild_dropdown1(targetname,map1,defval)
{	var target;
	if(map1==undefined)
	{	return false;
	}target= jsGetByName(targetname);
	if(target.length<=0)
	{	return false;
	}target=target[0];
	for(var j=target.options.length-1;j>=0;j--)
	{	target.remove(j);
	}for(var k1 in map1)
	{	k1= k1+'';
		var optelem= document.createElement('option');
		optelem.value= k1;
		optelem.innerHTML= k1.replace('<','&#60').replace('>','&#62');
		if(defval!=undefined&& defval==k1)
		{	optelem.selected= 'selected';
		}target.appendChild(optelem);
	}if(target.onchange!=undefined)
	{	target.onchange();
	}
}
function jsbuild_dropdown_2d(targetname,subname,map2d,defval,defval2)
{	var target= jsGetByName(targetname);
	if(target.length<=0)
	{	return false;
	}target=target[0];
	if(defval==undefined)
	{	defval= '';
	}if(defval2==undefined)
	{	defval2= '';
	}jsbuild_dropdown1(targetname,map2d,defval);
	jsbuild_dropdown1(subname,map2d[target.value],defval2);
	var f1= function(e,target1){
		if(target1==undefined)
		{	target1= js_getevttarget(e);
		}jsbuild_dropdown1(subname,map2d[target1.value]);};
	jsaddevent(target,'change',f1);
}
/*Good for converting map-object into get-param string*/
function jsjoin_map1d(map1,delim,sep)
{	var outtxt='';
	if(delim==undefined)
	{	delim= '';
	}if(sep==undefined)
	{	sep= '';
	}for(var k1 in map1)
	{	outtxt+= k1+delim+ map1[k1]+sep;
	}return outtxt;
}

/* Applies mapping(Object) using mad2d under index 'val1' to other text fields,
		useful for drop-down auto-fills.
	Optional: map_formname is map between map-field to actual form-field names
	e.g. target[0].onchange= function(e){js_map2d_autopop(my_map2d,this.value);};
*/
function js_map2d_autopop(map2d,val1,map_formname)
{	for(var map_f in map2d[val1])
	{	var form_f= map_f;
		if(map_formname!=undefined&& map_formname[map_f]!=undefined)
		{	form_f= map_formname[map_f];
		}var target= jsstr2obj(form_f);
		if(target!= undefined)
		{	var targetval= '';
			if(map2d[val1]!= undefined&& map2d[val1][map_f]!=undefined)
			{	targetval= map2d[val1][map_f];
			}target.value= targetval;
		}
	}
}

/*from array of checkbox/radio/select-option element with same name, return only those that are selected/checked*/
function jsgetchecked(ary1)
{	if(ary1==undefined||ary1.length==0){return ary1;}
	var ary2= Array();
	for(var i=0;i<ary1.length;i++)
	{	if(ary1[i].checked|| ary1[i].selected)
		{	ary2.push(ary1[i]);	}
	}return ary2;
}/*print formatting for table in javascript email
	window.location=' mailto:?subject=&body=&cc=&bcc= '
*/
function jsprintfe(str)
{	str= str.replace(/%/g,'%25');
	str= str.replace(/<(table|div)[^>]*>/gi,'');
	str= str.replace(/<\/(table|div)[^>]*>/gi,'');
	str= str.replace(/<tr[^>]*>/gi,'|');
	str= str.replace(/<\/tr>/gi,'%0D%0A');
	str= str.replace(/<td[^>]*>/gi,'');
	str= str.replace(/<\/td>/gi,'|');
	str= str.replace(/<br[^>]*>/gi,'%0D%0A');
	str= str.replace(/#/g,'%23');
	str= str.replace(/&/g,'%26');
	str= str.replace(/\r/g,'%0D');
	str= str.replace(/\n/g,'%0A');
	return str;
}
function jsprepad(str,len)
{	if(typeof(str)=='number')
	{	str+='';
		while(str.length<len)
		{	str= '0'+str;
		}return str;
	}while(str.length<len)
	{	str += ' ';
	}return str;
}
function jscheckform(map1)
{	var errstr= '';
	for(var field1 in map1)
	{	field1=''+field1;
		var regex1= map1[field1];
		var targets= jsGetByName(field1);
		for(var i=0;i<targets.length; i++)
		{	if(targets[i].value!=undefined&& targets[i].value.search(regex1)<0)
			{	if(''+regex1=='/.+/')
				{	errstr+= field1+ ' is empty (it must be filled in) \r\n';
				}else
				{	errstr+= field1+ ' has incorrect format ('+targets[i].value+')\r\n';
				}break;
			}
		}
	}return errstr;
}
/*e.g. jsaddevent(window,'load','jscheckform_init(regex_map);'); */
function jscheckform_init(map1,targets)
{	var f1= function(e)
	{	var errstr= jscheckform(map1);
		if(errstr.length>0){alert(errstr);return false;}
		return true;
	};
	var targets= document.getElementsByTagName('form');
	for(var i=0;i<targets.length;i++)
	{	if(targets[i].onsubmit!=undefined)
		{	jsaddevent(targets[i],'submit',f1);
		}else
		{	targets[i].onsubmit= f1;
		}
	}
}

/*for use in conjuction with htmlulcssmenu(), only for IE7 & below.
	e.g. window.onload= cssmenuinitjs; */
function cssmenuinitjs()
{	/*alert('Your browser cannot handle pure CSS menu - using javascript workaround: '+navigator.appVersion);*/
	var menuli= document.getElementsByTagName('a');
	for(var i=0;i<menuli.length;i++)
	{	menuli[i].style.color='black';
	}menuli= document.getElementsByTagName('li');
	for(var i=0;i<menuli.length;i++)
	{	if(menuli[i].parentNode.className=='hoverMenuV'|| menuli[i].parentNode.className=='hoverMenuH')
		{	if(!menuli[i].style.width)
			{	menuli[i].style.width= menuli[i].parentNode.offsetWidth-10;
			}menuli[i].onmouseover=function(e){cssmenuchilddisplay(this,'block');};
		}
	}
}function cssmenuchilddisplay(target,display1)
{	if(target==undefined)
	{	return false;
	}if(display1!='none'&& window.cssmenulast!=undefined&& window.cssmenulast!=target)
	{	cssmenuchilddisplay(window.cssmenulast,'none');
	}for(var j=0;j<target.childNodes.length;j++)
	{	var x1= target.childNodes[j];var tag1=''+x1.tagName;
		if(tag1.search(/ul/i)>=0&&(display1!='none'|| !x1.focused))
		{	x1.style.display= display1;
		}
	}window.cssmenulast= target;
}
function cookie_getmap()
{	var cookie_ary, cookiemap;
	cookiemap= new Object();
	cookie_ary= document.cookie.split(';');
	for(var i=0;i<cookie_ary.length;i++)
	{	var key1= cookie_ary[i].substr(0,cookie_ary[i].indexOf('='));
		var val1= cookie_ary[i].substr(cookie_ary[i].indexOf('=')+1);
		key1= key1.replace(/^\s+|\s+$/g,'');
		cookiemap[key1]= unescape(val1);
	}return cookiemap;
}
// eg: document.cookie= 'ppkcookie1=testcookie; expires=Thu, 2 Aug 2001 20:47:11 UTC; path=/';
function cookie_savemap(cookiemap,cdate,cpath)
{	var endstr='', tmp1;
	if(cdate!=undefined)
	{	tmp1= new Date(cdate);
		endstr+='; expires='+tmp1.toUTCString();
	}if(cpath!=undefined)
	{	endstr+='; path='+cpath;
	}for(var key1 in cookiemap)
	{	document.cookie= key1+'='+escape(cookiemap[key1])+endstr;
	}
}
function cookie_clear()
{	var cookiemap= cookie_getmap();
	for(var key1 in cookiemap)
	{	cookiemap[key1]='';
	}cookie_savemap(cookiemap,'1 Jan 1900');
}