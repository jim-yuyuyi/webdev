<?php
/*//Library of PHP functions.
	- Private commpany information (e.g. passwords) should not be kept in this file.
	Common Global Vars used: $libdir, $dblink, $sqlerror, $dbtype,$dbserver,$dbname,$dbuser,$dbpass, $curdt;
*/
/* -----------------------------Main Autorun (upon loading this library)------------- */
const EOL_CHR= "\r\n";

if(!empty($GLOBALS['debugmode']))
{	error_reporting(E_ALL);
}else//Production: print only fatal errors, not warnings
{	error_reporting(E_ERROR);
}if(function_exists('myErrorHandler'))
{	$old_error_handler= set_error_handler("myErrorHandler");
}if(function_exists('shutdownfunc'))
{	register_shutdown_function('shutdownfunc');
}if(function_exists("date_default_timezone_set"))
{	date_default_timezone_set('America/New_York');
}if(function_exists('microtime'))
{	$GLOBALS['starttime']= microtime(true);
}if(empty($GLOBALS['libdir']))
{	$GLOBALS['libdir']= 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
}
/* -----------------------------Functions-------------------------------------------- */
function myErrorHandler($errno, $errstr, $errfile, $errline)
{	global $debugmode;
	if(function_exists('error_get_last'))
	{	$output= error_get_last();
		if(!empty($output))
		{	$errstr.= ' '.var_export($output,true);
		}
	}$output = "[{$errno}] {$errstr}, {$errfile}, {$errline}";
	if(!empty($debugmode))
	{	echo "<span class='error'>ERROR: ".$output." </span><br>";
	}
}function shutdownfunc()
{	global $dblink, $debugmode, $starttime;
	if(!empty($dblink)&& is_resource($dblink))
	{	$dbtype= preg_replace("# link$#i",'',get_resource_type($dblink));
		call_user_func("{$dbtype}_close",$dblink);
		if(!empty($debugmode))
		{	debugprint("Closed {$dbtype} database connection");
		}
	}if(function_exists('microtime')&& !empty($debugmode)&& !empty($starttime))
	{	debugprint("<i>Page loaded in ". (microtime(true)-$starttime). " seconds</i>");
	}if(ob_get_level()>0)	//stop output buffering
	{	ob_end_flush();
	}if(function_exists('get_defined_vars'))		//free up memory
	{	foreach(array_keys(get_defined_vars()) as $inkey)
		{	unset($$inkey);
		}unset($inkey);
	}unset($GLOBALS);
}


function debugprint($str)
{	global $debugmode, $debugstr;
	if(empty($debugmode))
	{	unset($str);	return;
	}
	$str= "<p class='debugprint'>DEBUG:".$str."</p>".EOL_CHR;
	if(preg_match("#debugstr#",$debugmode))
	{	if(empty($debugstr))
		{	$debugstr= '';
		}$debugstr.= $str;
	}else
	{	echo $str;
	}unset($str);
}





/*//Converts string to html tag-safe format that can be safely enclosed in quotes, as in &#ASCII;
	BUT for Javascript object-map values, ordflag should be turned off (escape by backslash only)
	Inspiration: htmlspecialchars()
*/
function htmlasciiencode($str,$charary='',$ordflag=true)
{	if(empty($charary)||!is_array($charary))
	{	$charary= array("'",'"',">","<");
	}$codeary= array();
	foreach($charary as $chr1)
	{	if(!empty($ordflag))
		{	$codeary[]= "&#".ord($chr1).";";
		}else
		{	$codeary[]= "\\".$chr1;
		}
	}return str_replace("&#60;br&#62;","<br>",str_replace($charary,$codeary,$str));
}
/*//Encode char or array of 'em to url-safe %HEX format,
	Inspiration: urlencode (e.g  '-'= %2d)
*/
function dechexord($ary1=' ')
{	if(!is_array($ary1))
	{	return dechex(ord($ary1));
	}$codeary= array();
	foreach($ary1 as $chr)
	{	$codeary[]= "%".dechex(ord($chr));
	}return $codeary;
}
/*//Minimal special-char replacement to ensure curl request can access url,
	while peserving get-vars and folder-char and front http:// chars */
function urlencode_min($url,$charary='')
{	if(empty($charary))
	{	$charary= array('%',' ',"'",'#','^');
	}$codeary= dechexord($charary);
	$url= str_replace($charary,$codeary,$url);
	return $url;
}

/*Get Regular Express Match String for particular input
*/
function getregexpmatchstr($input)
{	if(empty($input))
	{	return $input;
	}if(is_array($input))
	{	$input= implode("|",$input);
	}$input= str_replace(",","|",trim($input));
	//POST input specify which fields are editable for this eidt
	$input= preg_replace("#\|\|+#","|",$input);
	$input= preg_replace("#^\||\|$#","",$input);
	return $input;
}

function readtxtfile($filename, $lines='', $mode='r')
{	if(!is_readable($filename)&& !preg_match("#^https?://#",$filename))
	{	debugprint("Server Unable to read file: {$filename}!");
		return '';
	}if(!is_numeric($lines)){$lines=-1;}
	$fh= fopen($filename,$mode);
	$contents= '';
	while ($lines!=0&& !empty($fh)&& !feof($fh) )
	{	$contents .= fgets($fh);
		$lines--;
	}fclose($fh);
	return $contents;
}
function writetxtfile($filename, $contents, $mode='w')
{	$fh= fopen($filename,$mode);
	if(empty($fh))	//(file_exists($filename) && !is_writable($filename))
	{	debugprint("Server Unable to write to file: {$filename} with {$contents}!");
		return false;
	}fwrite($fh,$contents);
	fclose($fh);
	debugprint("wrote {$filename} with {$contents}");
	return $filename;
}
/*//Workaround as PHP5 is_writable() is unreliable:
	returns false if file not exist, even though it is creatable
*/
function iscreatable($filename)
{	$preverr= error_reporting(E_ERROR);
	if(is_dir($filename))
	{	$filename .= "/t1.txt";}
	$fileexist= file_exists($filename);
	$fh= fopen($filename,'a');
	$result= !empty($fh);
	fclose($fh);
	if(!$fileexist&& $result)
	{	unlink($filename);
	}error_reporting($preverr);
	return $result;
}
function uploadfile($formname='upload', $homepath='./')
{	if( empty($_FILES[$formname]) || empty($_FILES[$formname]['tmp_name']))
	{	return false;
	}if( !empty($homepath) && !file_exists($homepath) )
	{	if( !mkdir($homepath) )
		{	return false;
		}
	}if( !preg_match("#/$#",$homepath) )
	{	$homepath.= '/';
	}$targetpath= $homepath. basename($_FILES[$formname]['name']);
	debugprint("Uploading {$_FILES[$formname]['tmp_name']} to {$targetpath}");
	return move_uploaded_file($_FILES[$formname]['tmp_name'], $targetpath);
}

/*Sends email through sockets using raw SMTP via sockets.
	Good alternative in case php's built email function is too slow/not working/not configured.
	Recommended format for attachs: array( array($data, $fname), ..... etc. )
	Credits: www.webcheatsheet.com/php/send_email_text_html_attachment.php,
		www.sitepoint.com/print/advanced-email-php, www.codewalkers.com/c/a/Email-Code/Smtp-Auth-Email-Script
*/
function sendemail($from='',$to='',$subject='',$body='',$attachs='',$timeout='')
{	global $smtpuser,$smtppass,$smtpserv,$smtpport;
	$smtpreply='';
	$from= preg_replace("#\r|\n|;+.*$#",'',trim($from));
	$from= preg_replace("#\s*@\s*#",'@',$from);
	$fromheader= $from;
	//e.g. YOURNAME <youremail@doamin.com>;
	if(preg_match("#<([^>]+)>#",$from,$matches))
	{	$from= $matches[1];
	}$subject= preg_replace("#\r|\n|<br>#",'',$subject);
		//prevent bareline LF, see	cr.yp.to/docs/smtplf.html
	$body= preg_replace("#([^\r])\n#","$1\r\n",$body);
		//emails fails for long lines (70+ chars) without \r\n
	$body= preg_replace("#([^\r]{50,70})(<|>|\s)#","$1\r\n$2",$body);
		//reserved email body end
	$body= str_replace("\r\n.\r\n","\r\n . \r\n",$body);
	if(empty($smtpuser))
	{	$smtpuser= ini_get('sendmail_from');
	}if(empty($smtpuser))
	{	$smtpuser= $from;
	}if(empty($smtpserv))
	{	$smtpserv= ini_get('SMTP');
	}if(empty($smtpserv)|| $smtpserv=="localhost")
	{	$smtpserv= "smtp.".preg_replace("#[^@]*@#",'',$smtpuser);
	}if(!is_numeric($smtpport)&& preg_match("#^ssl://#i",$smtpserv))
	{	$smtpport= "465";
	}if(empty($smtpport))
	{	$smtpport= ini_get('smtp_port');
	}if(!is_numeric($smtpport))
	{	$smtpport= "25";
	}if(empty($from))
	{	$from= $smtpuser;
	}if(empty($to))
	{	$to= $from;
	}if(empty($subject))
	{	$subject= "no subject";
	}if(empty($body))
	{	$body= "no body text";
	}if(empty($attachs))
	{	$attachs= array();
	}else if(!is_array($attachs))
	{	$attachs= explode('-#-#1#-',$attachs);
	}if(empty($timeout)||!is_numeric($timeout))
	{	$timeout= 3;
	}$mime_boundary= "x2Multipart_Boundary_x". md5(time())."x";
	$headers= "MIME-Version: 1.0\r\nContent-Type: multipart/mixed; boundary=\"{$mime_boundary}\"\r\n";
	$mime_msg='text/plain';
	if(preg_match("#<(br|table|div|body|img|html)#",$body))
	{	$mime_msg='text/html';
	}$body= "--{$mime_boundary}\r\n".
		"Content-Type: {$mime_msg}; charset=iso-8859-1\r\n".
		"Content-Transfer-Encoding: 7bit\r\n\r\n".
		"{$body}\r\n\r\n";
	foreach($attachs as $attachary1)
	{	$attachdata= ''; //data or fullpath to file
		$attachname= ''; //attache file name
		$attachtype= '';//mime type
		if(!is_array($attachary1))
		{	$attachary1= explode('-#-#2#-',$attachary1);
		}while(empty($attachdata)&& count($attachary1)>0)
		{	$attachdata= array_shift($attachary1);
		}if(count($attachary1)>0)
		{	$attachname= array_shift($attachary1);
		}if(count($attachary1)>0)
		{	$attachtype= array_shift($attachary1);
		}if(empty($attachname))
		{	$attachname= basename($attachdata);
		}if(empty($attachtype)&& !empty($attachname)&& function_exists('mime_content_type'))
		{	$attachtype= mime_content_type($attachname);
		}if(empty($attachtype))
		{	$attachtype= 'text/plain';
		}if(empty($attachdata))
		{	continue;
		}$data= '';
		if(!preg_match("#[\*\?<>\|]#",$attachdata)&& strlen($attachdata)<100)
		{	$fh= fopen($attachdata,'rb');
		}while(!empty($fh)&& !feof($fh))
		{	$data.= fgets($fh);	}
		if(empty($fh))
		{	$data= $attachdata;
		}else
		{	fclose($fh);
		}$data= chunk_split(base64_encode($data));
		$body.=	"--{$mime_boundary}\r\n".
			"Content-Transfer-Encoding: base64\r\n".
			"Content-Type:{$attachtype}; name=\"{$attachname}\"\r\n".
			"Content-Disposition: attachment;\r\n\r\n".
			"{$data}\r\n\r\n";
	}$body.= "--{$mime_boundary}--\r\n";
	$to= preg_replace("#\r|\n|^mailto:|^\s+|\s+$#i",'',$to);
	$to= preg_replace("#;+#",";",$to);
	$to= preg_replace("#\s*@\s*#","@",$to);
	if(is_array($to))
	{	$headers.= "To: ".$to[0]."\r\n";
		if(!empty($to[1]))
		{	$headers.= "Cc: ".$to[1]."\r\n";
		}$to= implode(";",$to);
	}else
	{	$headers.= "To: ".$to."\r\n";
	}$headers.= "From: {$fromheader}\r\nSubject: {$subject}\r\n";
	try
	{	if(empty($quitresponse))
		{	$smtpConnect= fsockopen($smtpserv, $smtpport, $errno, $errstr, $timeout);
			if(empty($smtpConnect))
			{	$quitresponse= "cannot connect {$smtpserv}: {$smtpreply} {$errno} {$errstr}";
				$smtpreply.= "|quitresponse:". $quitresponse;
			}
		}if(!empty($smtpConnect))
		{	$smtpreply.= "|connection:". fgets($smtpConnect, 515);
			$instr= "HELO ".$smtpserv."\r\n";
			fputs($smtpConnect,$instr);
			$smtpreply.= $instr."|". fgets($smtpConnect, 515);
			if(!empty($smtpuser)&& !empty($smtppass))
			{	$instr= "AUTH LOGIN\r\n";
				fputs($smtpConnect,$instr);
				$smtpreply.= $instr."|". fgets($smtpConnect, 515);
				$instr= base64_encode($smtpuser)."\r\n";
				fputs($smtpConnect,$instr);
				$smtpreply.= $instr."|". fgets($smtpConnect, 515);
				$instr= base64_encode($smtppass)."\r\n";
				fputs($smtpConnect,$instr);
				$smtpreply.= $instr."|". fgets($smtpConnect, 515);
			}$instr= "MAIL FROM: <".$from.">\r\n";
			fputs($smtpConnect,$instr);
			$smtpreply.= $instr."|". fgets($smtpConnect, 515);
			$rcpts=array();
			foreach(explode(";",$to) as $rcpt)
			{	$rcpt= trim($rcpt);
				if(preg_match("#(<[^>]+>)#",$rcpt,$matches))
				{	$rcpt= $matches[1];
				}else
				{	$rcpt= "<".$rcpt.">";
				}if(preg_match("#@#",$rcpt))
				{	$rcpts[$rcpt]=1;
				}
			}foreach(array_keys($rcpts) as $rcpt)
			{	$instr= "RCPT TO: {$rcpt}\r\n";
				fputs($smtpConnect,$instr);
				$smtpreply.= $instr."|". fgets($smtpConnect, 515);
			}$instr= "DATA\r\n";
			fputs($smtpConnect,$instr);
			$smtpreply.= $instr."|". fgets($smtpConnect, 515);
			$instr=  $headers."\r\n"."\r\n".$body."\r\n.\r\n";
			fputs($smtpConnect,$instr);
			$smtpreply.= "|header_body_response:". fgets($smtpConnect, 515);
			$instr= "QUIT\r\n";
			fputs($smtpConnect,$instr);
			$quitresponse=	fgets($smtpConnect, 515);
			$smtpreply.= "|quitresponse:". $quitresponse;
		}
	}catch(Exception $e)
	{	$smtpreply.= "Exception: ".$e."\r\n";
	}return $smtpreply;
}


function headerpath_parse($path='|DEFAULT|')
{	if($path=='|DEFAULT|'&&!empty($_SERVER['PHP_SELF']))
	{	$path= dirname($_SERVER['PHP_SELF']);
	}$path= str_replace("\\","/",$path);
	if(!preg_match("#\w#",$path))
	{	$path= '';
	}$path= preg_replace("#^/+#",'/','/'.$path);
	return $path;
}

/*//Convience function to start session.
	Default $lifetime: 9000secs= 15 mins.
*/
function sess_start($lifetime=9000,$path='|DEFAULT|')
{	$path= headerpath_parse($path);
	session_set_cookie_params($lifetime,$path);
	session_cache_limiter('public');
	$expstr= $lifetime/60;
	session_cache_expire($expstr);
	return session_start();
}
function sess_clear()
{	foreach(array_keys($_SESSION) as $key)
	{	unset($_SESSION[$key]);
	}session_cache_expire(0);
	return session_destroy();
}


/*//Manually set all cookies value in HTTP header using header() function.
	Meant to replace builtin functions "setcookie()", as it suffers from 32-bit unix time_t overflow bug.
	Note: Cookie are set in http header, so either use ob_start or run this before any print.
*/
function updatecookies($lifetime="+1 hours",$path='|DEFAULT|')
{
	if(!is_array($_COOKIE)|| count($_COOKIE)==0)
	{	return;
	}if(!empty($lifetime))
	{	try
		{	$expiredt= new DateTime();
			$expiredt->setTimezone(new DateTimeZone("GMT"));
			$expiredt->modify($lifetime);
			$lifetime= "expires=".$expiredt->format("D, d-M-Y H:i:s")." GMT; ";
		}catch(Exception $e)
		{	$lifetime='';
		}
	}
	$path= headerpath_parse($path);
	foreach(array_keys($_COOKIE) as $inkey)
	{	header("Set-Cookie: {$inkey}={$_COOKIE[$inkey]}; {$lifetime}path={$path};\r\n",false);
	}return $lifetime;
}
function clearcookies($pattern='',$path='|DEFAULT|')
{	if(empty($pattern))
	{	$pattern='#.*#';
	}else if(preg_match("#^[A-Za-z]#",$pattern)|| !preg_match("#".str_replace("#","\\#",substr($pattern,0,1))."[A-Za-z0-9]*$#",$pattern))
	{	$pattern= '#'.$pattern.'#';
	}
	$path= headerpath_parse($path);
	foreach(array_keys($_COOKIE) as $inkey)
	{	if(preg_match($pattern,$inkey))
		{	header("Set-Cookie: {$inkey}= ; expires=Fri, 31-Jan-1970 00:00:00 GMT; path={$path};\r\n",false);
			unset($_COOKIE[$inkey]);
		}
	}return count($_COOKIE)<=0;
}

function getfullurl($extra='')
{	return "http".(empty($_SERVER['HTTPS'])||$_SERVER['HTTPS']=='off'?'':"s"). "://".
		preg_replace("#:80$#",'',$_SERVER['HTTP_HOST']).
		(empty($extra)?$_SERVER['PHP_SELF']:$_SERVER['REQUEST_URI']);
}
function getlasterror()
{	return var_export(error_get_last(),true);
}
function ob_fileout($buffer)		//redirect all outputs to a file
{	fwrite($GLOBALS['ob_out'],$buffer);
	//usage: 	$GLOBALS["ob_out"]= fopen($GLOBALS["repfile"],'w');	ob_start('ob_fileout');
}
if(!function_exists('sys_get_temp_dir'))
{	function sys_get_temp_dir()
	{	$str= '';
		foreach(array('TMP','TMPDIR','TEMP') as $inkey)
		{	$str= getenv($inkey);
			if(!empty($str))
			{	break;}
		}if(function_exists('realpath'))
		{	$str= realpath($str);
		}else if(function_exists('filename_parse'))
		{	$str= filename_parse($str);
		}if(!preg_match("#/$#",$str))
		{	$str.= "/";}
		return $str;
	}
}
function htmlredirect($loc,$timeout=0)
{	//header("refresh:{$timeout};url={$loc}");
	header("Location:{$loc}");
	return "<html><head><meta http-equiv='REFRESH' content='{$timeout};url={$loc}'/></head>
		<body><a href='".$loc."'>Redirecting to: ".$loc."</a></body><html>";
}

//escape new line character for translation into javascript
function esc_newline(&$val, $key)
{	$val= preg_replace("#\r*\n#","\\n",$val);
}
/*//Converts php array string into javascript array declaration.
	Can include non-numeric keys, like a map.*/
function phpary_2_jsary($varstr='')
{	if(is_array($varstr))
	{	array_walk($varstr,'esc_newline');
		$varstr= var_export($varstr,true);
	}$varstr= trim($varstr);
	$varstr= preg_replace("#\s*\)\s*,#i",'},',$varstr);
	$varstr= preg_replace("#\s*\)\s*\),#i",'}},',$varstr);
	$varstr= preg_replace("#\)$#i",'}',$varstr);
	$varstr= preg_replace("#\s*array\s*\(\s*#i",'{',$varstr);
	$varstr= preg_replace("#\s*=>\s*#", ":",$varstr);
	$varstr= preg_replace("#\s*,\s*#",",",$varstr);
	$varstr= preg_replace("#,\}#","}",$varstr);
	$varstr= preg_replace("#(\r|\n)#","",$varstr);
	$varstr= preg_replace("#'(/[^/']+/)'#","$1",$varstr);
	$varstr= str_replace(array('<','>'),array("\\<","\\>"),$varstr);
	$varstr= preg_replace("#\\\\+#","\\",$varstr);
	return $varstr;
}


//transform 2D array of DBquery result into simple html table
function dbtable2htmltable($result,$groupfield='',$maxcols='')
{	$str='';	$cnt=0;	$groupcnt=0;	$grouplastval='';	$lastkey= false;
	if(!is_array($result)){return $result;}
	if(empty($maxcols)||!is_numeric($maxcols)){$maxcols=2;}
	foreach(array_keys($result) as $inkey1)
	{	if($cnt==0){$str.= "<table border='1'>";}
		if(is_array($result[$inkey1]))
		{	$curkey= implode("</th><th>",array_keys($result[$inkey1]));
			if($lastkey!=$curkey&& !empty($curkey))
			{	$lastkey= $curkey;
				if($cnt>0)
				{	$str.= "<tr><td></td></tr>";
				}$str.= "<tr><th>".$lastkey."</th></tr>";
			}if(!empty($groupfield))	//grouping totals
			{	if(!array_key_exists($groupfield,$result[$inkey1]))
				{	$result[$inkey1][$groupfield]= '';
				}
				//debugprint("GROUPVAL COMPARE {$groupfield}: {$grouplastval} vs ".var_export($result[$inkey1],true));
				if($grouplastval!= $result[$inkey1][$groupfield])
				{	if($groupcnt>0)
					{	if(empty($grouplastval)){$grouplastval='[BLANK]';}
						$str.= "<tr><td colspan='".count(array_keys($result[$inkey1]))."' class='centered'>--> {$groupfield}: {$grouplastval} &nbsp; TOTAL: {$groupcnt} <br>&nbsp; </td></tr>";
					}$grouplastval= $result[$inkey1][$groupfield];
					$groupcnt= 0;
				}$groupcnt++;
			}$str.= "<tr>";
			foreach(array_keys($result[$inkey1]) as $inkey2)
			{	if(!preg_match('#^<(img |input |a |noscript|\n)#i',$result[$inkey1][$inkey2])&& strlen($result[$inkey1][$inkey2])>50)
				{	$result[$inkey1][$inkey2]= preg_replace("#(\r*\n)+#","<br>",$result[$inkey1][$inkey2]);
					$str.= "<td><div class='tdoverflow'>{$result[$inkey1][$inkey2]}</div></td>".EOL_CHR;
				}else
				{	$str.= "<td>{$result[$inkey1][$inkey2]}</td>".EOL_CHR;
				}
			}$str.= "</tr>".EOL_CHR;
		}else{
			if($cnt==0){$str.= "<tr>";}
			else if($cnt%$maxcols==0){$str.= "</tr><tr>";}
			$str.= "<th>{$inkey1}</th><td>{$result[$inkey1]}</td><td> &nbsp; </td>";
		}if($cnt>=count($result)-1)
		{	if(!is_array($result[$inkey1])){$str.= "</tr>";}
			if(!empty($groupfield))
			{	if($groupcnt>0);
				{	if(empty($grouplastval)){$grouplastval='[BLANK]';}
					$str.= "<tr><td colspan='".count(array_keys($result[$inkey1]))."' class='centered'>--> {$groupfield}: {$grouplastval} &nbsp; TOTAL: {$groupcnt} <br>&nbsp; </td></tr>";
				}$str.= "<tr><td colspan='".count(array_keys($result[$inkey1]))."' class='centered'>TOTAL: ".($cnt+1)."</td></tr>".EOL_CHR;
			}$str.= "</table>";
		}$cnt++;
	}return $str;
}

/*//Transform 2D array of DBquery result into select-option html text, using first field as ID
	fieldtxtnum= # of other fields are displayed in option text with '|' delimter; fieldtxtnum<0 means don't include ID in text*/
function dbtable2options($result,$defvalue='',$fieldtxtnum=1,$addlabel='')
{	$str= '';
	if(!is_array($result)){	return	$str;	}
	if(!is_numeric($fieldtxtnum)){	$fieldtxtnum= 1;}
	for($k=0;$k<count($result);$k++)
	{	$keys= array_keys($result[$k]);
		$id= array_shift($result[$k]);
		if($id==''){continue;}
		$attrbs= '';
		if((is_array($defvalue)&& preg_match('#@'.$id.'@#','@'.implode('@',$defvalue).'@'))|| (!is_array($defvalue)&& $id==$defvalue))
		{	$attrbs= " selected='selected'"; }
		$str.= "<option value='{$id}'{$attrbs}>";
			$fcount= $fieldtxtnum;
			//fieldtxtnum<0 means don't include id in text
			if($fcount<0&& count($result[$k])>0){	$id= '';	$fcount= 0- $fcount;	array_shift($keys);}
			else if(!empty($addlabel)){	$id= array_shift($keys).": ".$id;	}
			while(count($result[$k])>0&& $fcount>0)
			{	if(!empty($id)){	$id.= " | ";	}
				if(!empty($addlabel))
				{	$id.= array_shift($keys).": ";	}
				$id.= array_shift($result[$k]);
				$fcount--;
			}
		$str.= $id."</option>";
	}return $str;
}


//for building SQL select queries with key->value array pairs as where conditions. Usage: array_map("aryjoinquery",array_keys($ary),$ary);
function aryjoinquery($k,$v)
{	if(preg_match("#^[^']+(=|>|<)'#i",$k.$v))
	{	return "{$k}{$v}";
	}if(preg_match("#%#",$v))
	{	return "{$k} like '{$v}'";
	}if($v=='_NULL'||$v=='')
	{	return "({$k} is NULL or {$k}='')";
	}if($v=='_NOTNULL')
	{	return "({$k} is NOT NULL and {$k}!='')";
	}if(preg_match("#NULL$#",$v))
	{	return "{$k} is {$v}";
	}return "{$k}='{$v}'";
}function aryjoinupdatesql($k,$v)
{	if(preg_match("#^NULL$#i",$v)|| preg_match("#'\)?$#",$v))
	{	return "{$k}={$v}";
	}return "{$k}='{$v}'";
}

/*//Query DB Table structure and store it into 2D map of
	columna name-> (ORDER, TYPE, MAXLEN, DEFAULT, DECIMAL, NULL, IDENTITY) info-array
*/
function sql_descmap($tablename,$extraflag=false)
{	global $dbtypestr;
	$descmap= array();
	$searchsql= "desc ".$tablename;
	$tabledesc= sqlrunquery($searchsql);
	foreach(array_keys($tabledesc) as $i)
	{	/*//Mandatory fill-in fields: ORDER, TYPE, MAXLEN, DEFAULT, DECIMAL, NULL, IDENTITY */
		$fieldname= '';
		if(!empty($tabledesc[$i]['Field']))
		{	$fieldname= $tabledesc[$i]['Field'];
		}else if(!empty($tabledesc[$i]['COLUMN_NAME']))
		{	$fieldname= $tabledesc[$i]['COLUMN_NAME'];
		}$descmap[$fieldname]= array('ORDER'=>$i);
		$descmap[$fieldname]['TYPE']='';
		if(!empty($tabledesc[$i]['Type']))
		{	$descmap[$fieldname]['TYPE']= $tabledesc[$i]['Type'];
		}else if(!empty($tabledesc[$i]['DATA_TYPE']))
		{	$descmap[$fieldname]['TYPE']= $tabledesc[$i]['DATA_TYPE'];
		}if(preg_match("#\((\d+),(\d+)\)#",$descmap[$fieldname]['TYPE'],$matches))
		{	$descmap[$fieldname]['MAXLEN']= $matches[1];
			$descmap[$fieldname]['DECIMAL']= $matches[2];
			$descmap[$fieldname]['TYPE']= preg_replace("#\(\d.+#",'',$descmap[$fieldname]['TYPE']);
		}else if(preg_match("#\(\d+\)#",$descmap[$fieldname]['TYPE']))
		{	$descmap[$fieldname]['MAXLEN']= preg_replace("#\D#",'',$descmap[$fieldname]['TYPE']);
			$descmap[$fieldname]['TYPE']= preg_replace("#\(\d+\)#",'',$descmap[$fieldname]['TYPE']);
		}if(!empty($tabledesc[$i]['CHARACTER_MAXIMUM_LENGTH']))
		{	$descmap[$fieldname]['MAXLEN']= $tabledesc[$i]['CHARACTER_MAXIMUM_LENGTH'];
		}if(!empty($tabledesc[$i]['NUMERIC_SCALE']))
		{	$descmap[$fieldname]['DECIMAL']= $tabledesc[$i]['NUMERIC_SCALE'];
		}
		$descmap[$fieldname]['IDENTITY']= false;
		if(!empty($tabledesc[$i]['Extra'])&& preg_match('#auto_increment#i',$tabledesc[$i]['Extra']))
		{	$descmap[$fieldname]['IDENTITY']= $tabledesc[$i]['Extra'];
		}else if(!empty($tabledesc[$i]['ISIDENTITY']))
		{	$descmap[$fieldname]['IDENTITY']= $tabledesc[$i]['ISIDENTITY'];
		}
		$descmap[$fieldname]['DEFAULT']='';
		if(!empty($tabledesc[$i]['Default']))
		{	$descmap[$fieldname]['DEFAULT']= preg_replace("#\('?(.*)'?\)#",'$1',$tabledesc[$i]['Default']);
		}else if(!empty($tabledesc[$i]['COLUMN_DEFAULT']))
		{	$descmap[$fieldname]['DEFAULT']= preg_replace("#\('?(.*)'?\)#",'$1',$tabledesc[$i]['COLUMN_DEFAULT']);
		}//prepend space for default email suffix
		if(!empty($descmap[$fieldname]['DEFAULT'])&& preg_match("#^\s*@\w+#",$descmap[$fieldname]['DEFAULT']))
		{	$descmap[$fieldname]['DEFAULT']= '              '.$descmap[$fieldname]['DEFAULT'];
		}
		$descmap[$fieldname]['NULL']= false;
		if(!empty($tabledesc[$i]['Null']))
		{	$descmap[$fieldname]['NULL']= $tabledesc[$i]['Null'];
		}else if(!empty($tabledesc[$i]['IS_NULLABLE']))
		{	$descmap[$fieldname]['NULL']= $tabledesc[$i]['IS_NULLABLE'];
		}if(!empty($descmap[$fieldname]['NULL'])&& !preg_match("#^y#i",$descmap[$fieldname]['NULL']))
		{	$descmap[$fieldname]['NULL']= false;
		}
		if(!empty($extraflag))
		{	$descmap[$fieldname]= array_merge($tabledesc[$i],$descmap[$fieldname]);
		}
	}return $descmap;
}

/*//Reset auto-increasing ID on delete
*/
function sql_resetid($tablename, $fieldid='id')
{	global $dbtypestr;
	$searchsql= "select max(".$fieldid.") as '".$fieldid."' from ".$tablename;
	$maxid= current(sqlrunquery($searchsql));
	if(!empty($maxid[$fieldid]))
	{	$maxid= $maxid[$fieldid];
		$resetsql= "alter table ".$tablename." AUTO_INCREMENT=".$maxid;
		return var_export(current(sqlrunquery($resetsql)),true);
	}return var_export($maxid,true);
}

/*//To save DB access time & resources: reserve a connection line while script is in use.
	Don't reopen, don't close it until script shutdown.
	Thus the global variables are there to close manually & change connection as needed.
*/
function sqlrunquery($sql)
{	global $debugmode, $dblink,$sqlerror,$dbtype,$dbserver,$dbname,$dbuser,$dbpass;
	$output= array();
	if(empty($dblink))
	{	if(!empty($dbtype)&& function_exists("{$dbtype}_connect"))
		{	$dblink= call_user_func("{$dbtype}_connect",$dbserver,$dbuser,$dbpass);
		}if(!empty($dblink))
		{	$query= call_user_func("{$dbtype}_select_db",$dbname,$dblink);
			//debugprint("XX1 _select_db {$dbname}");
			if(empty($query))
			{	$query= call_user_func("{$dbtype}_query","use {$dbname}", $dblink);
				//debugprint("XX1 use {$dbname}");
			}unset($query);
		}else
		{	$output[0]= array("ERROR"=>"cannot dbconnect: {$dbtype} {$dbserver}- {$dbuser},{$dbpass} !");
			if(!function_exists("{$dbtype}_connect"))
			{	$output[0]["ERROR"].= "{$dbtype}_connect() function not exist - DB extension not loaded";
			}
		}
	}if(!empty($dblink))
	{	//ensure group by fields added for "count" in queries
		if(preg_match("#count\(#i",$sql)&& !preg_match("#group by#",$sql))
		{	$sql2= $sql;
			$sql2= preg_replace("#\s+from\s+.*#i",'',$sql2);
			$sql2= preg_replace("#^select#i",'',$sql2);
			$sql2= preg_replace("#count\([^,]*,?#i",'',$sql2);
			$sql2= preg_replace("#^\s*,#",'',$sql2);
			$sql2= preg_replace("#,\s*$#",'',$sql2);
			if(preg_match("#\w#",$sql2))
			{	$sql.= " Group By ".$sql2;
			}
		}//ensure apostrophe in values will be included in sql
		$sql= preg_replace("#(\w)'(\w)#","$1\\'$2",$sql);

		$query= call_user_func("{$dbtype}_query",$sql, $dblink);
		if(empty($query)&& empty($sqlerror))
		{	$sqlerror='';
			foreach(array("_error","_result_error","_stmt_errormsg","_errcode","_get_last_message") as $errfunc)
			{	if(function_exists($dbtype.$errfunc))
				{	$sqlerror.= call_user_func($dbtype.$errfunc, $dblink);
					break;
				}
			}
		}if(empty($query))
		{	$output[]= array("ERROR"=>$sqlerror);
		}else if(preg_match("#^\s*(insert|delete|update|alter|drop|create)#i",$sql))
		{	$output[]= array("success"=>$sql);
		}else if(!is_resource($query))
		{	$output[]= array("success"=>"return not a resouce;".$sql);
		}else
		{	while($row= call_user_func("{$dbtype}_fetch_assoc",$query))
			{	$row= array_map("trim",$row);
				array_push($output,$row);
			}
		}
	}debugprint("sqlrunquery {$sql}| result: ".var_export($output,true));
	return $output;
}

function noncache_header()
{	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
}


/*Check if login is sucessful or not, from database
*/
function minipo_checklogin_sess($table_user,$field_user,$field_pass='')
{	global $field_userck, $field_passck;
	if(empty($field_userck))
	{	$field_userck="_loginuser_";
	}if(empty($field_passck))
	{	$field_passck="_loginpass_";
	}
	$loginflag= false;
	$user=''; $pass='';
	if(array_key_exists($field_userck,$_POST))
	{	$user= $_POST[$field_userck];
	}if(array_key_exists($field_passck,$_POST))
	{	$pass= $_POST[$field_passck];
	}if(!empty($_SESSION[$field_userck])&& $user=='')
	{	$user= $_SESSION[$field_userck];
	}if(!empty($_SESSION[$field_passck])&& $pass=='')
	{	$pass= $_SESSION[$field_passck];
	}//query login database table
	$searchsql= "select * from {$table_user} where BINARY {$field_user}='{$user}'";
	if(!empty($fieldpass))
	{	$searchsql.= " and BINARY {$field_pass}='{$pass}'";
	}
	$loginflag= current(sqlrunquery($searchsql));
	if(empty($loginflag[$field_user]))
	{	unset($loginflag);
	}else
	{	$loginflag[$field_userck]= $loginflag[$field_user];
		$loginflag[$field_passck]= $loginflag[$field_pass];
	}
	//failed login or logout
	if(empty($loginflag)||!empty($_POST['__logout']))
	{	sess_clear();
		$loginflag= false;
	}else
	{	$_SESSION[$field_userck]= $user;
		$_SESSION[$field_passck]= $pass;
	}
	return $loginflag;
}

function printhtmlheader($webtitle,$cssfile="",$webdesc="")
{	echo "<!DOCTYPE HTML PUBLIC><head><title>".$webtitle."</title>".EOL_CHR;
	echo "<meta http-equiv='content-type' content='text/html; charset=UTF-8'>".EOL_CHR;
	if(!empty($cssfile))
	{	echo "<link href='".$cssfile."' rel='stylesheet' type='text/css'>".EOL_CHR;
	}
	if(!empty($webdesc))
	{	echo "<meta name='description' content='".$webdesc."'/>".EOL_CHR;
		echo "<meta name='keywords' content='".$webdesc."'/>".EOL_CHR;
	}
	echo "</head><body>".EOL_CHR;
}
function printhtmlbottom()
{	global $debugmode;
	if(function_exists('memory_get_usage')&& !empty($debugmode))
	{	debugprint("MEM ".memory_get_usage()."bytes");
	}
	echo "</body></html>".EOL_CHR;
}

function minipo_printtopinfo()
{	global $webtitle;
	$curdt= new DateTime();
	echo "<span class='header1'>".$webtitle."</span><br>".EOL_CHR;
	echo "Current Time: <span id='curtime'>".$curdt->format('M d, Y H:i:s')."</span><br>".EOL_CHR;
	if(!empty($_SESSION[$field_userck]))
	{	echo "<div class='userinfo'>Current User:".$_SESSION[$field_userck]."</div>".EOL_CHR;
	}
	//common javascript libraries
	echo "<script type='text/javascript' src='minipo_jslib.js'></script>".EOL_CHR;
}

//echo menu options
function minipo_menu($logoutbtn=true)
{	$curpage= basename($_SERVER['PHP_SELF']);
	$htmlstr= "<br><hr>".EOL_CHR;;

	$htmlstr.="<a href='minipo_Summary.php' class='inlinebtn".
		(preg_match("#minipo_Summary#i",$curpage)?" curpagecls":'').
		"'><input type='button' value='Search / Listing'></a>".EOL_CHR;

	$htmlstr.="<a href='minipo_Edit.php' class='inlinebtn".
		(preg_match("#pay_edit#i",$curpage)?" curpagecls":'').
		"'><input type='button' value='New Entry'></a>".EOL_CHR;

	//Log out button
	if(!empty($logoutbtn))
	{	$htmlstr.= "<form enctype='multipart/form-data' method='post' class='inlinebtn'>".
			"<input type='submit' name='__logout' value='Log out'></form>".EOL_CHR;
	}$htmlstr.= "<hr>".EOL_CHR;
	return $htmlstr;
}
?>