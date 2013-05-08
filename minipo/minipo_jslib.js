/* Library of commonly Javascript functions */
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
	if(elem==undefined)
	{	return elem;
	}
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


function removeAllChild(target)
{	if(target==undefined)
	{	return false;
	}while(target.childNodes.length>0)
	{	target.removeChild(target.firstChild);
	}
}


/*clone first line of srcnode table and append to target element
	useful: jsaddevent(window,'resize',function(){jsclonetblheader(srcnode,target,true)});
*/
function jsclonetblheader(srcnode,target,resetflag)
{	if(srcnode==undefined||target==undefined)
	{	return false;
	}
	if(typeof(srcnode)=='string')
	{	srcnode= jsstr2obj(srcnode);
	}if(typeof(target)=='string')
	{	target= jsstr2obj(target);
	}if(srcnode==undefined||target==undefined)
	{	return false;
	}
	if(resetflag!=undefined)
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

/*moves an element always on top, even with scrolling*/
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
	}
	if(srcnode==undefined||target==undefined)
	{	return false;
	}
	if(typeof(jstotaloffset)=='function')
	{	var toffset= jstotaloffset(srcnode);
		target.origpos= toffset.y;
	}else
	{	target.origpos= srcnode.offsetTop;
	}if(offset1!=undefined)
	{	target.offset= offset1;
	}var f1= function(e){jsscrolltopelem(target);};
	jsaddevent(window,'scroll',f1);
}


/* Setups up automaitcal running JS timr print out,
	includes - Date HH:MM:SS.
	Rolls over the second	*/
function jstimer(timerid)
{	var d, target, targetval, offset;	
	target= jsstr2obj(timerid);
	if(target==undefined)
	{	return false;
	}
	d= new Date();
	/*if(target.innerHTML.search(/[0-9]+/)>=0)
	{	offset= new Date(target.innerHTML);
		offset= offset.getTime()-d.getTime();
	}	
	if(typeof(offset)=='number')
	{	d.setTime(d.getTime()+offset);
	}*/
	targetval= d.toLocaleString();	
	if(target.tagName.search(/input|textarea/i)>=0)
	{	target.value= targetval;
	}else
	{	target.innerHTML= targetval;
	}window.setTimeout('jstimer(\''+timerid+'\')',1000);
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


/*credit: www.rainforestnet.com TengYong Ng 20031116(V.2.1.1) && Yvan Lavoie 20090129*/
var Cal;
function NewCssCal(pCalId,pDateFormat,pScroller,pTimeFormat,cssStr)
{	var pOutTarget;
	if(typeof pCalId=='object')
	{pOutTarget=pCalId;if(pOutTarget.id){pCalId=pOutTarget.id;}else if(pOutTarget.name){pCalId=pOutTarget.name;}}
	else if(pCalId!=undefined&& document.getElementById(pCalId)!=undefined)
	{pOutTarget= document.getElementById(pCalId);}
	else if(pCalId!=undefined&& document.getElementsByName(pCalId).length>0)
	{pOutTarget= document.getElementsByName(pCalId)[0];}
	else{return false;}
	if(Cal==undefined){Cal= new Calendar();Cal.goToday();}
	if(pDateFormat!=undefined&& pDateFormat.search(/FRENCH/i)>=0)
	{	 Cal.MonthName= ["Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre"];
		pDateFormat= pDateFormat.replace(/FRENCH/i,'');
	}if(pDateFormat!=undefined&&pDateFormat.length>0){Cal.DateFormat=pDateFormat.toUpperCase();}
	else{Cal.DateFormat='YYYY-MM-DD';}
	if(pScroller!=undefined){Cal.Scroller=pScroller.toUpperCase();}
	else{Cal.Scroller='DROPDOWN:S4:E5';}
	if(pTimeFormat!=undefined){Cal.TimeFormat=pTimeFormat;}
	else{Cal.TimeFormat='';}
	if(pOutTarget.value){Cal.ParseDateStr(pOutTarget.value);}
	var winCal= document.getElementById(Cal.PopupId);
if(winCal==undefined)
{	if(cssStr==undefined)
	{	cssStr='';
		cssStr+='.caldiv{position:absolute;background-color:white;line-height:100%;} ';
		cssStr+='.caldiv,.caltable,.caltoplbl,.calth,.calwkday,.calwkend,.caltoday,.calpicked,.calbutton,.calintxt,.calsel,.caltimetd';
		cssStr+='{margin:0;padding:0;border-collapse:collapse;text-align:center;font-family:Arial,Verdana;font-size:9pt;} ';
		cssStr+='.calwkday,.calwkend,.caltoday,.calpicked,.calbutton,.calsel{cursor:pointer;} ';
		cssStr+='.calwkday,.calwkend,.caltoday,.calpicked{border:0;} ';
		cssStr+='.caltable{vertical-align:top;cursor:pointer;} .caltoplbl{color:red;white-space:nowrap;width:100%;} ';
		cssStr+='.caltable{border:1px solid black;white-space:nowrap;} ';
		cssStr+='.caltimetd{border:1px solid black;} .calhover{color:red;}';
		cssStr+='.calth{font-style:italic;background-color:#EEEEEE;} .calintxt{width:20px;} ';		
		cssStr+='.calwkday{background-color:white;} .calwkend{background-color:white;} ';
		cssStr+='.caltoday{background-color:#E0E0FF;} .calpicked{font-weight:bold;background-color:#D0FFD0;} ';
		cssStr+='.calreadonly{background-color:white;color:gray;}';
	}if(cssStr!='')
	{	jsaddcss(cssStr);
	}winCal= document.createElement('div');
	winCal.className= 'caldiv';
	document.body.appendChild(winCal);
	winCal.id=Cal.PopupId;
	winCal.onkeyup= function(e){if(!e){e=window.event;}
		if(e.keyCode==27||e.keyCode==46){CssCalHide();}
		if(e.keyCode==13){CssCalSubmit();CssCalHide();}
		if(e.keyCode==46){Cal.cleardate();}
		var newcaldate='';
		/*37~40= left up right down*/
		if(e.keyCode==37){newcaldate=Cal.Date-1;}
		if(e.keyCode==38){newcaldate=Cal.Date-7;}
		if(e.keyCode==39){newcaldate=Cal.Date+1;}
		if(e.keyCode==40){newcaldate=Cal.Date+7;}
		if(newcaldate!='')
		{	var monthdays=Cal.GetMonDays();
			if(newcaldate<0)
			{	newcaldate+=monthdays;}
			newcaldate= newcaldate%monthdays;
			if(newcaldate==0)
			{newcaldate=monthdays;}
			Cal.Date= newcaldate;
			CssCalShow();
		}
	};if('onmouseenter' in winCal)
	{winCal.onmouseenter=function(e){Cal.focused=true;};}
	else{winCal.onmouseover=function(e){Cal.focused=true;};}
	if('onmouseleave' in winCal)
	{winCal.onmouseleave=function(e){Cal.focused=false;};}
	else{winCal.onmouseout=function(e){Cal.focused=false;};}
	if(window.addEventListener){window.addEventListener('mousedown',CssCalHideNoFocus,false);}
	else{document.body.attachEvent('onmousedown',CssCalHideNoFocus);}
}if(Cal.OutTarget!=pOutTarget)
{	if(winCal.parentNode){winCal.parentNode.removeChild(winCal);}
	if(pOutTarget.parentNode&& pOutTarget.parentNode.nodeName)
	{	Cal.ParentType= pOutTarget.parentNode.nodeName.toUpperCase();
	}else
	{	Cal.ParentType='';
	}if(Cal.ParentType.search(/TD|TR|TABLE/)==0)
	{	pOutTarget.parentNode.insertBefore(winCal,pOutTarget);
	}else
	if(pOutTarget.parentNode)
	{	if(pOutTarget.nextSibling&&  pOutTarget.nextSibling.tagName&& pOutTarget.nextSibling.tagName.toUpperCase()!='IMG')
		{	pOutTarget.parentNode.insertBefore(winCal,pOutTarget.nextSibling);
		}else
		{	pOutTarget.parentNode.appendChild(winCal);
		}
	}else
	{	document.body.appendChild(winCal);
	}if(pOutTarget.type!=undefined&& pOutTarget.type=='text')
	{	if(!pOutTarget.readOnly){pOutTarget.readOnly=true;pOutTarget.blur();}
		if(!pOutTarget.onkeyup){pOutTarget.onkeyup= function(e){if(!e){e=window.event;}
		if(e.keyCode==8||e.keyCode==46){Cal.cleardate();}
		if(e.keyCode==27){CssCalHide();}};}
	}
}Cal.OutTarget= pOutTarget;
CssCalShow();
}function CssCalShow(){
var vCalHeader,vCalData,vCalTime='';
var i,j,SelectStr;
var vDayCount= 0;
var curMonthDT= new Date(Cal.Year,Cal.Month,1);
var vFirstDay= curMonthDT.getDay();
var winCal= document.getElementById(Cal.PopupId);
if(!winCal){return false;}
if(Cal.OutTarget)
{	if(Cal.ParentType.search(/TD|TR|TABLE/)==0)
	{	var targetpos= jstotaloffset(Cal.OutTarget);
		with(winCal.style){left=targetpos.x+'px';top=targetpos.y+Cal.OutTarget.offsetHeight+'px';}
	}else
	{	with(winCal.style){left=Cal.OutTarget.offsetLeft+'px';top=(Cal.OutTarget.offsetTop+Cal.OutTarget.offsetHeight)+'px';}
	}	
}vCalHeader="<tr><td colspan='7' class='caltoplbl'>";
if(Cal.Scroller.search('DROPDOWN')>=0){
vCalHeader+="<select name='MonthSelector' class='calsel' onmousedown='Cal.focused=true;' onchange='Cal.Month=this.selectedIndex;CssCalShow();'>";
var StartYear= Cal.TodayDT.getFullYear()-4; 
var EndYear= Cal.TodayDT.getFullYear()+5;
if(Cal.StartDT!=undefined&& Cal.StartDT.getTime!=undefined)
{	StartYear= StartDT.getFullYear();
}
if(Cal.EndDT!=undefined&& Cal.EndDT.getTime!=undefined)
{	EndYear= EndDT.getFullYear();
}
for(i=0;i<12;i++){
if(i==Cal.Month)
{SelectStr="Selected";}
else{SelectStr='';}
vCalHeader+="<option "+SelectStr+" value="+i+">"+Cal.MonthName[i]+"</option>";
}vCalHeader+="</select> ";
vCalHeader+="<select name='YearSelector' class='calsel' onmousedown='Cal.focused=true;' onchange='Cal.Year=this.value;CssCalShow();'>";
for(i=StartYear;i<=EndYear;i++){
if(i==Cal.Year)
{SelectStr="Selected";}
else{SelectStr='';}
vCalHeader+="<option "+SelectStr+" value="+i+">"+i+"</option>";
}vCalHeader+="</select> ";
}else if(Cal.Scroller.search('ARROW')>=0){
vCalHeader+="<span style='float:left;'>";	
if(Cal.StartDT==undefined|| Cal.StartDT.getFullYear==undefined||  Cal.StartDT.getFullYear()<Cal.Year)
{	vCalHeader+="<input type='button' class='calbutton' value='<<' onmousedown='Cal.DecYear();CssCalShow();'>";
}else
{	vCalHeader+="<input type='button' class='calbutton calreadonly' value='<<' >";
}
if(Cal.StartDT==undefined|| Cal.StartDT.getTime==undefined||  Cal.StartDT.getTime()<curMonthDT.getTime())
{	vCalHeader+="<input type='button' class='calbutton' value='<' onmousedown='Cal.DecMonth();CssCalShow();'>";
}else
{	vCalHeader+="<input type='button' class='calbutton calreadonly' value='<' >";
}vCalHeader+="</span>";
vCalHeader+="<span style='float:right;'>";
if(Cal.EndDT==undefined|| Cal.EndDT.getTime==undefined||  Cal.EndDT.getTime()>curMonthDT.getTime())
{	vCalHeader+="<input type='button' class='calbutton' value='>' onmousedown='Cal.IncMonth();CssCalShow();'>";
}else
{	vCalHeader+="<input type='button' class='calbutton calreadonly' value='>' >";
}if(Cal.EndDT==undefined|| Cal.EndDT.getFullYear==undefined||  Cal.EndDT.getFullYear()>Cal.Year)
{	vCalHeader+="<input class='calbutton' type='button' value='>>' onmousedown='Cal.IncYear();CssCalShow();'>";
}else
{	vCalHeader+="<input type='button' class='calbutton calreadonly' value='>>' >";
}vCalHeader+="</span>";
vCalHeader+=" <a onmousedown='Cal.goToday();CssCalShow();'>"+Cal.GetMonthName(false)+" "+Cal.Year+"</a>";
}vCalHeader+="</td></tr>";
vCalHeader+="<tr>";
for(i=0;i<7;i++){vCalHeader+="<td class='calth'>"+Cal.WeekDayName[i].substr(0,Cal.WeekChar)+"</td>";}
vCalHeader+="</tr>";
if(Cal.WeekDayName[0].search(/[Mm]on/)==0){vFirstDay-=1;}
if(vFirstDay==-1){vFirstDay=6;}
vCalData="<tr>";
for(i=0;i<vFirstDay;i++){
vCalData=vCalData+GenCell();
vDayCount=vDayCount+1;
}for(j=1;j<=Cal.GetMonDays();j++){
var TmpDT= new Date(Cal.Year,Cal.Month,j);
var strCell;
if((vDayCount%7==0)&&(j > 1)){vCalData=vCalData+"<tr>";}
vDayCount=vDayCount+1;
if(Cal.StartDT!=undefined&& Cal.StartDT.getTime!=undefined&& TmpDT.getTime()<Cal.StartDT.getTime()-86400000)
{	strCell=GenCell(j,'calreadonly');
}else if(Cal.EndDT!=undefined&& Cal.EndDT.getTime!=undefined&& TmpDT.getTime()>Cal.EndDate.getTime()+86400000)
{	strCell=GenCell(j,'calreadonly');
}
else if(j==Cal.Date&&Cal.PickedYearMonth==''+Cal.Year+Cal.Month)
{strCell=GenCell(j,'calpicked','calpicked');}
else if((j==Cal.TodayDT.getDate())&&(Cal.Month==Cal.TodayDT.getMonth())&&(Cal.Year==Cal.TodayDT.getFullYear()))
{strCell=GenCell(j,'caltoday');}
else if((Cal.WeekDayName[0].search(/[Mm]on/)==0&&(vDayCount%7==0||(vDayCount+1)%7==0))||
(Cal.WeekDayName[0].search(/[Mm]on/)!=0&&(vDayCount%7==0||(vDayCount+6)%7==0)))
{strCell=GenCell(j,'calwkend');}
else{strCell=GenCell(j,'calwkday');}
vCalData=vCalData+strCell;
if((vDayCount%7==0)&&(j<Cal.GetMonDays()))
{vCalData=vCalData+"</tr>";}
}if(!(vDayCount%7)==0){
while(!(vDayCount%7)==0){
vCalData=vCalData+GenCell();
vDayCount++;
}}vCalData+= "</td></tr>";
if(Cal.TimeFormat.length>0){
vCalTime="<tr><td colspan='7' class='caltimetd'><b>Time</b> ";
vCalTime+="<input type='text' name='hour' class='calintxt' maxlength=2 value='"+Cal.Hours+"' onfocus='Cal.focused=true;' onchange='Cal.SetHour(this.value);CssCalSubmit();'> : ";
vCalTime+="<input type='text' name='minute' class='calintxt' maxlength=2 value='"+Cal.Minutes+"'  onfocus='Cal.focused=true;'onchange='Cal.SetMinute(this.value);CssCalSubmit();'>";
if(Cal.TimeFormat.search(/[Ss]/)>=0){
vCalTime+=" : <input type='text' name='second' class='calintxt' maxlength=2 value="+Cal.Seconds+" onfocus='Cal.focused=true;' onchange='Cal.SetSecond(this.value);CssCalSubmit();'>";
}if(Cal.TimeFormat.search(/[Aa]/)>=0){
var SelectAm=(Cal.AMorPM=='AM')?'Selected':'';
var SelectPm=(Cal.AMorPM=='PM')?'Selected':'';
vCalTime+=" <select name='ampm' class='calsel' onfocus='Cal.focused=true;' onmousedown='Cal.focused=true;' onchange='Cal.focused=true;Cal.SetAmPm(this.options[this.selectedIndex].value);CssCalSubmit();'>";
vCalTime+="<option "+SelectAm+" value='AM'>AM</option>";
vCalTime+="<option "+SelectPm+" value='PM'>PM</option>";
vCalTime+="</select>";
}vCalTime+="<input type='button' class='calbutton' value='OK' onmousedown='CssCalSubmit();CssCalHide();'>";
if(Cal.TimeFormat.search('~C')<0)
{	vCalTime+=" &nbsp; <input type='button' class='calbutton' value='Clear' onmousedown='Cal.cleardate();CssCalHide();'>";
}vCalTime+="</td></tr>";
}var tmpstr= "<table class='caltable' border='0'>";
if(Cal.TimeFormat.search('t')>=0)
{	tmpstr+= vCalTime+vCalHeader+vCalData;
}else
{	tmpstr+= vCalHeader+vCalData+vCalTime;
}tmpstr+= "</table>";
with(winCal.style){display='block';zIndex=40;}
winCal.innerHTML= tmpstr;
var calpicked= document.getElementById('calpicked');
if(calpicked){calpicked.focus();}
return true;
}
function GenCell(pValue,pClass,pCellId){
	if(pValue==undefined){pValue='';}
	if(pValue==''){return "<td> &nbsp; </td>";}
	if(pClass==undefined){pClass='';}
	else{pClass=" class='"+pClass+"' ";}
	if(pCellId==undefined){pCellId='';}
	else{pCellId=" id='"+pCellId+"' ";}
	if(pClass.search(/readonly/i)>=0)
	{	return "<td><span "+pCellId+pClass+">"+pValue+"</span></td>";
	}return "<td><input type='button' "+pCellId+pClass+" value='"+pValue+"' onmousedown='CssCalSubmit("+pValue+");CssCalHide();' onmouseover='this.className+=\" calhover \";' onmouseout='this.className=this.className.replace(/ calhover/,\"\");' onfocus='this.onmouseover();' onblur='this.onmouseout();'></td>";
}
function Calendar(){
this.DateFormat='';
this.Scroller='';
this.TimeFormat='';
this.WeekChar=2;
this.MonthName=['January','February','March','April','May','June','July','August','September','October','November','December'];
this.DaysInMonth= [31,28,31,30,31,30,31,31,30,31,30,31];
this.WeekDayName= ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
this.Year=0;this.Month=0;this.Date='';
this.Hours='00';this.Minutes='00';this.Seconds='00';this.AMorPM='AM';
this.TodayDT= new Date();
this.StartDT= null;	this.EndDT= null;
this.PickedYearMonth='';
this.PopupId= 'calBorder';
this.OutTarget='';
this.ParentType='';
this.focused= true;
}
function GetMonthIndex(strMonth){
	for(i=0;i<12;i++)
	{	if(Cal.MonthName[i].substring(0,3).toUpperCase()==strMonth.substring(0,3).toUpperCase())
		{return i;
		}return 'NaN';
	}
}Calendar.prototype.GetMonthIndex=GetMonthIndex;
function cleardate()
{	Cal.Date= '';
	Cal.Hours='00';
	Cal.Minutes='00';
	Cal.Seconds='00';
	if(Cal.OutTarget)
	{	Cal.OutTarget.value='';
	}
}Calendar.prototype.cleardate=cleardate;
function setStartDate(intYear,intMonth,intDay)
{	Cal.StartDT= new Date(intYear,intMonth,intDay);
}Calendar.prototype.setStartDate= setStartDate;
function setEndDate(intYear,intMonth,intDay)
{	Cal.EndDT= new Date(intYear,intMonth,intDay);
}Calendar.prototype.setEndDate= setEndDate;
function goToday(){
var pDate= Cal.TodayDT;
Cal.Year=pDate.getFullYear();
Cal.Month=pDate.getMonth();
Cal.Date=pDate.getDate();
Cal.Hours=pDate.getHours();
Cal.Minutes=pDate.getMinutes();
Cal.Seconds=pDate.getSeconds();
}Calendar.prototype.goToday=goToday;
function IncYear(){Cal.Year++;}
Calendar.prototype.IncYear=IncYear;
function DecYear(){Cal.Year--;}
Calendar.prototype.DecYear=DecYear;
function IncMonth(){
Cal.Month++;
if(Cal.Month>=12){Cal.Month=0;Cal.IncYear();}
}Calendar.prototype.IncMonth=IncMonth;
function DecMonth(){
Cal.Month--;
if(Cal.Month<0){
Cal.Month=11;
Cal.DecYear();
}
}
Calendar.prototype.DecMonth=DecMonth;
function SetHour(instr){
if(instr==undefined){instr='';}
var minval=0;var maxval=23;
Cal.Hours=parseInt(instr.replace(/[^\d]+/,''),10);
if(Cal.Hours>maxval){Cal.Hours=maxval;}
if(Cal.Hours<minval){Cal.Hours=minval;}
if(isNaN(Cal.Hours)){Cal.Hours=0;}
if(Cal.Hours<10){Cal.Hours='0'+Cal.Hours;}
else{Cal.Hours=''+Cal.Hours;}
if(Cal.Hours>12&& Cal.TimeFormat.search(/[Aa]/)>=0)
{	Cal.AMorPM='PM';}
return Cal.Hours;
}Calendar.prototype.SetHour=SetHour;
function SetMinute(instr){
if(instr==undefined){instr='';}
var minval=0;var maxval=60;
Cal.Minutes=parseInt(instr.replace(/[^\d]+/,''),10);
if(Cal.Minutes>maxval){Cal.Minutes=maxval;}
if(Cal.Minutes<minval){Cal.Minutes=minval;}
if(isNaN(Cal.Minutes)){Cal.Minutes=0;}
if(Cal.Minutes<10){Cal.Minutes='0'+Cal.Minutes;}
else{Cal.Minutes=''+Cal.Minutes;}
return Cal.Minutes;
}Calendar.prototype.SetMinute=SetMinute;
function SetSecond(instr){
if(instr==undefined){instr='';}
var minval=0;var maxval=60;
Cal.Seconds=parseInt(instr.replace(/[^\d]+/,''),10);
if(Cal.Seconds>maxval){Cal.Seconds=maxval;}
if(Cal.Seconds<minval){Cal.Seconds=minval;}
if(isNaN(Cal.Seconds)){Cal.Seconds=0;}
if(Cal.Seconds<10){Cal.Seconds='0'+Cal.Seconds;}
else{Cal.Seconds=''+Cal.Seconds;}
return Cal.Seconds;
}
Calendar.prototype.SetSecond=SetSecond;
function SetAmPm(pvalue){
this.AMorPM= pvalue;}
Calendar.prototype.SetAmPm=SetAmPm;
function GetMonthName(IsLong){
var Month=Cal.MonthName[this.Month];
if(Month==undefined)
{	Cal.goToday();
	Month=Cal.MonthName[this.Month];
}if(IsLong){return Month;}
return Month.substr(0,3);
}Calendar.prototype.GetMonthName=GetMonthName;
function GetMonDays(){
if(this.IsLeapYear()&& this.Month==1)
{return this.DaysInMonth[this.Month]+1;}
return this.DaysInMonth[this.Month];
}Calendar.prototype.GetMonDays=GetMonDays;
function IsLeapYear(){
if((this.Year%4)==0){
if((this.Year%100==0)&&(this.Year%400)!=0){return false;}
else{return true;}
}else{return false;}
}Calendar.prototype.IsLeapYear=IsLeapYear;
function GetDateStr(pDate)
{if(pDate==''){return pDate;}
pDate= parseInt(pDate,10);
if(pDate<10){pDate='0'+pDate;}
var pMonth=this.Month+1;
if(pMonth<10){pMonth='0'+pMonth;}
var outstring= this.DateFormat;
outstring= outstring.replace('YYYY',this.Year+'');
outstring= outstring.replace('YY',String(this.Year).substring(2,4));
outstring= outstring.replace('MMMM',this.GetMonthName(true));
outstring= outstring.replace('MMM',this.GetMonthName(false));
outstring= outstring.replace('MM',pMonth);
outstring= outstring.replace('DD',pDate);
return outstring;
}Calendar.prototype.GetDateStr=GetDateStr;
function ParseDateStr(exDateTime)
{if(exDateTime==undefined){return false;}
if(exDateTime==''){return false;}
var ind1,intYear,intMonth,intDate;
if(Cal.DateFormat.indexOf('YYYY')>=0)
{	ind1= Cal.DateFormat.indexOf('YYYY');
	intYear= exDateTime.substring(ind1,ind1+4);
}else if(Cal.DateFormat.indexOf('YY')>=0)
{	var tmpstr= Cal.TodayDT.getFullYear()+'';
	ind1= Cal.DateFormat.indexOf('YY');
	intYear= tmpstr.substring(0,tmpstr.length-2)+exDateTime.substring(ind1,ind1+2);
}intYear= parseInt(intYear,10);
if(Cal.DateFormat.indexOf('MMM')>=0)
{	ind1= Cal.DateFormat.indexOf('MMM');
	intMonth= exDateTime.substring(ind1,ind1+3);
	intMonth= Cal.GetMonthIndex(intMonth)+1;
}else if(Cal.DateFormat.indexOf('MM')>=0)
{	ind1= Cal.DateFormat.indexOf('MM');
	intMonth= exDateTime.substring(ind1,ind1+2);
}intMonth= parseInt(intMonth,10);
if(Cal.DateFormat.indexOf('DD')>=0)
{	ind1= Cal.DateFormat.indexOf('DD');
	intDate= exDateTime.substring(ind1,ind1+2);
}intDate= parseInt(intDate,10);
if(!isNaN(intYear))
{	Cal.Year= intYear;}
if(!isNaN(intMonth)&& intMonth>=0&& intMonth<Cal.MonthName.length)
{	Cal.Month= intMonth-1;}
if(!isNaN(intDate)&& intDate>=1&& intDate<=Cal.GetMonDays())
{	Cal.Date= intDate;}
Cal.PickedYearMonth=''+Cal.Year+Cal.Month;
if(Cal.TimeFormat.length>0){
if(exDateTime.substring(exDateTime.length-1,exDateTime.length).toUpperCase()=='M')
{Cal.AMorPM=exDateTime.substring(exDateTime.length-2,exDateTime.length);}
var tSp1=exDateTime.indexOf(':',0);
var tSp2=exDateTime.indexOf(':',tSp1+1);
if(tSp1>0)
{strHour=exDateTime.substring(tSp1,tSp1-2);
Cal.SetHour(strHour);
strMinute=exDateTime.substring(tSp1+1,tSp1+3);
Cal.SetMinute(strMinute);
strSecond=exDateTime.substring(tSp2+1,tSp2+3);
Cal.SetSecond(strSecond);
}else if(exDateTime.indexOf('D*')!=-1)
{strHour=exDateTime.substring(2,4);
Cal.SetHour(strHour);
strMinute=exDateTime.substring(4,6);
Cal.SetMinute(strMinute);
}}}Calendar.prototype.ParseDateStr=ParseDateStr;
function CssCalSubmit(datum){
var timestr='';
if(datum==undefined){datum='';}
else{datum=''+datum;}
if(datum.search(/\d/)>=0)
{	Cal.Date= datum;
}if(Cal.TimeFormat.length>0){
var target=document.getElementsByName('hour');
if(target.length>0)
{	Cal.SetHour(target[0].value);}
target=document.getElementsByName('minute');
if(target.length>0)
{	Cal.SetMinute(target[0].value);}
timestr+=' '+Cal.Hours+':'+Cal.Minutes;
if(Cal.TimeFormat.search(/[Ss]/)>=0){
	target=document.getElementsByName('second');
	if(target.length>0)
	{	Cal.SetSecond(target[0].value);}
	timestr+=':'+Cal.Seconds;
}if(timestr.search(/[123456789]/)<0&& (isNaN(Cal.Date)||Cal.Date==''))
{	timestr= '';
}if(timestr.length>0&& Cal.TimeFormat.search(/[Aa]/)>=0)
{timestr+=' '+Cal.AMorPM;}
}if(timestr.length>0&& (isNaN(Cal.Date)||Cal.Date==''))
{	Cal.Date= Cal.TodayDT.getDate();
}if(Cal.OutTarget)
{	Cal.OutTarget.value= Cal.GetDateStr(Cal.Date)+timestr;
}
}function CssCalHide()
{if(!Cal||!document.getElementById(Cal.PopupId)){return;}
var winCal= document.getElementById(Cal.PopupId);
with(winCal.style){display='none';zIndex=0;}
}function CssCalHideNoFocus(e)
{if(Cal!=undefined&&Cal.focused!=true){CssCalHide();}}


/*Starting JS operation - clone top header*/
jsscrolltopelem_init('searchlisttbl','topelemfixed',10);
jsclonetblheader('searchlisttbl','topelemfixed');
jsaddevent(window,'resize',function(){jsclonetblheader('searchlisttbl','topelemfixed',true)});
jsaddevent(window,'load',function(){jstimer('curtime');});