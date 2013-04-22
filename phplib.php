<?php
/*//Library of PHP functions. GNU & copyleft license - feel free to copy and paste and modify in any way.
	"Because custom API's are not copy-rightable"
	- All private commpany information (e.g. passwords) must not be kept in this

	Common Global Vars used: $libdir, $dblink, $sqlerror, $dbtype,$dbserver,$dbname,$dbuser,$dbpass, $curdt;
*/
/* -----------------------------Main Autorun (upon loading this library)------------- */
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
}function debugprint($str)
{	global $debugmode, $debugstr;
	if(empty($debugmode))
	{	unset($str);	return;
	}$str= "<p class='debugprint'>DEBUG:".$str."</p>";
	if(preg_match("#debugstr#",$debugmode))
	{	if(empty($debugstr))
		{	$debugstr= '';
		}$debugstr.= $str;
	}else
	{	echo $str;
	}unset($str);
}

/*//Recommneded common html header / stylesheet / top part:
	echo "<!DOCTYPE HTML PUBLIC><head><title>".$webtitle."</title>".
		"<link href='".$libdir."/jy_php.css' rel='stylesheet' type='text/css'>".
		"<meta name='description' content='".$mytitle."'/>".
		"<meta name='keywords' content='".$mytitle."'/>".
		"</head><body>";
*/
function printhtmlbottom()
{	global $debugmode;
	if(function_exists('memory_get_usage')&& !empty($debugmode))
	{	debugprint("MEM ".memory_get_usage()."bytes");
	}echo "<script type='text/javascript'>
		var target=document.getElementById('btm1');
		if(target!=undefined&& target.offsetTop<document.body.scrollHeight)
		{	target.style.top=document.body.scrollHeight;
		}</script>";
	echo "</body></html>";
}

/*	Replace illegal character from filename. Use common format (no backslash path.
	__FILE__= Full filepath of this phplib file.
	$_SERVER['SCRIPT_FILENAME'] = Full filepath to PHP file that imported this phplib
	$_SERVER['PHP_SELF']= sub url path to PHP file running
*/
function filename_parse($filename,$repl_chr='_')
{	$filename= str_replace("\\","/",$filename);
	//For "Microsoft Windows OS"
	if(preg_match("#^Windows#i",php_uname()))
	{	$filename= dirname($filename)."/".preg_replace("#\\|/|:|\*|\?|<|>\|#",$repl_chr,basename($filename));
	}if(is_dir($filename)&& !preg_match("#/$#",$filename))
	{	$filename.= "/";
	}return $filename;
}
function execpath_parse($execpath='|DEFAULT|')
{	if($execpath=='|DEFAULT|')
	{	$execpath= dirname($_SERVER['SCRIPT_FILENAME'])."/";
	}if(preg_match("#^Windows#i",php_uname()))
	{	$execpath= str_replace("/","\\",$execpath);
	}return $execpath;
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


//Randomly scramble the ordering of contents in an array. Does not preserve array keys.
function scarmble_ary($inary)
{	$candary= array_values($inary);
	$outary= array();
	for($j=0;$j<count($inary);$j++)
	{	$ri= rand(0,count($candary)-1);
		$val= array_splice($candary,$ri,1);
		$outary[]= $val[0];
	}
	return $outary;
}

//exec in background without blocking. Must use absolute paths. credit:Arno van den Brink, php.net/manual/en/function.exec.php
function execbg($cmd,$mode="w")
{	if(preg_match("#^Windows#i",php_uname())){return pclose(popen("start /b ".$cmd,$mode));  }
    else
	{	return exec($cmd . " > /dev/null &");
	}
}
//exec and get its output. Blocks until timeout. Must use absolute paths.
function execread($cmd,$timeout=4){
	$contents= '';
	if(!preg_match("#>+#",$cmd))
	{	$cmd.=" 2>&1";
	}$fh= popen("start /b ".$cmd,"r");
	if(!empty($timeout))
	{	stream_set_timeout($fh,$timeout);
	}while(!empty($fh)&& !feof($fh))
	{	$contents.= fread($fh,1024);
	}pclose($fh);
	return $contents;
}
/*//desc_spec specifies where to place each 
		- to pipe array: array("pipe","w")
		- to file ouput: array("file","err.txt", "a")
*/
function execproc($cmd,$desc_spec='',$execpath='|DEFAULT|',$env_ary='')
{	if(empty($desc_spec)|| !is_array($desc_spec))
	{	$desc_spec = array(0=>array("pipe","r"), 1=>array("pipe","w"),2 => array("pipe","w"));
	}if(empty($env_ary)|| !is_array($env_ary))
	{	$env_ary= NULL;
	}if(function_exists('execpath_parse'))
	{	$execpath= execpath_parse($execpath);
	}$proch= proc_open($cmd, $desc_spec, $pipes, $execpath, $env_ary);
	if(is_resource($proch))
	{	fclose($pipes[0]);
		$contents= stream_get_contents($pipes[1]);
		$contents.= stream_get_contents($pipes[2]);
		fclose($pipes[1]);
		$return_value= proc_close($proch);		
	}return $contents;
}
//uses curl to get http headers, generally tend to be faster than builtin function get_headers by 0.002 secs
function getheader2($url='',$field='',$timeout=0,$bodyflag='')
{	if(!function_exists('curl_init'))
	{	return "ERROR: requireed php_curl.dll module not installed.";
	}if(empty($url))
	{	$url= "http://localhost:{$_SERVER['SERVER_PORT']}/";
	}if(function_exists("urlencode_min"))
	{	$url= urlencode_min($url);
	}$ch= curl_init();
	curl_setopt($ch,CURLOPT_URL, $url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch,CURLOPT_HEADER, true);
	if(empty($bodyflag))
	{	curl_setopt($ch,CURLOPT_NOBODY, true);
	}if(!empty($timeout)&& $timeout!=0)
	{	if(is_numeric($timeout))
		{	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
			curl_setopt($ch,CURLOPT_TIMEOUT,$timeout);
		}
	}$str=  preg_replace("#\r?\n#","|", curl_exec($ch) );
	if(!empty($field))
	{	$str= preg_replace("#.*{$field}: ([^\|]+).*#i","$1",$str);
	}return $str;
}

/*//Redirect with get,post,cookie input data intact.
	Will follow header redirection and resend inputs.*/
function redirectcurl($newloc,$maxredir='',$getstr='',$poststr='',$cookiestr='',$headerflag=true)
{	$result='';
	if(!function_exists('curl_init'))
	{	return "ERROR: requireed php_curl.dll module not installed.";
	}if(is_array($getstr))
	{	if(count($getstr)>0)
		{	$getstr='?'.http_build_query($getstr);
		}else
		{	$getstr='';
		}
	}if(is_array($poststr))
	{	if(count($poststr)>0)
		{	$poststr= http_build_query($poststr);
		}else
		{	$poststr='';
		}
	}if(is_array($cookiestr))
	{	if(count($cookiestr)>0)
		{	$cookiestr= "COOKIE: ".http_build_query($cookiestr);
			$cookiestr= str_replace("&","; ",$cookiestr);
		}else
		{	$cookiestr='';
		}
	}if(empty($maxredir)|| !is_numeric($maxredir))
	{	$maxredir= 4;
	}do{
		$ch= curl_init();
		curl_setopt($ch,CURLOPT_URL, $newloc.$getstr);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_HEADER, $headerflag);
			//CURLOPT_FOLLOWLOCATION problem: will not resend get& post data
		//curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
		if(!empty($poststr))
		{	curl_setopt($ch,CURLOPT_POST,true);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$poststr);
		}if(!empty($cookiestr))
		{	curl_setopt($ch,CURLOPT_COOKIE,$cookiestr);
		}$maxredir--;
		$result.= curl_exec($ch);
		if(!empty($headerflag))
		{	$result.="<hr>";
		}unset($newloc);
		//handle redirection with another curl_init
		if(preg_match("#\nLocation:\s+([^\r\n]+)#i",$result,$matches))
		{	$newloc= $matches[1];
		}
	}while(!empty($newloc)&& $maxredir>0);
	return $result;
}
//date function suffers from 2038 unix time_t 32bit bug: maxsec=2147483647		20380118 22:14:07->19011213 15:45:52
//workaround using http get header or dos command for server on windows computer
function getcurdt($curtime='')
{	global $curdt;
	if(!class_exists('DateTime'))
	{	if(empty($curtime)&& function_exists("curl_init")&& function_exists("getheader2")){
			$curtime= getheader2('',"Date");
		}if(empty($curtime)){
			$curtime= trim(exec("date /t")).' '.trim(exec("time /t"));
		}if(empty($curtime))
		{	$curtime= date("Y-m-d H:i:s");
		}$curdt= $curtime;
		return $curtime;
	}else if(!empty($curtime)&& preg_match("#GMT#i",$curtime))
	{	$curtime= trim(preg_replace("#GMT#i",'',$curtime));
		$curdt= new DateTime($curtime, new DateTimeZone("GMT"));
		$curdt->setTimezone(new DateTimeZone("America/New_York"));
	}else
	{	$curdt= new DateTime($curtime);
	}return $curdt;
}
//returns this month's first sunday DateTime object; credits: www.jamesmarquez.com
function getstartwkdt($inputdt='')
{	global $curdt,$startwkdt;
	if(!class_exists('DateTime')){return false;}
	if($inputdt instanceof DateTime)
	{
	}else if(preg_match("#^[\d\s-:]+$#",$inputdt))
	{	$inputdt= new DateTime($inputdt);
	}else if(!empty($curdt))
	{	$inputdt= $curdt;
	}else
	{	$inputdt= new DateTime();
	}$startwkdt= new DateTime($inputdt->format("Y-m-07"));
	$startwkdt= new DateTime($startwkdt->format("Y-m-").($startwkdt->format("d")-$startwkdt->format("w")));
	//special case: first 2 days of month is weekend, first work week starts after
	if($startwkdt->format("d")<=2)
	{	$startwkdt->modify("+7 day");	}
	return $startwkdt;
}
//returns integer of the week# (1~5) in the input datetime's month.
function getwknum($inputdt='')
{	global $curdt,$startwkdt;
	if(!class_exists('DateTime')){return false;}
	if($inputdt instanceof DateTime)
	{	}
	else if(preg_match("#^[\d\s-:]+$#",$inputdt))
	{	$inputdt= new DateTime($inputdt);	}
	else if(!empty($curdt))
	{	$inputdt= $curdt;	}
	else
	{	$inputdt= new DateTime();	}
	if(empty($startwkdt)|| $inputdt->format("Y-m")!=$startwkdt->format("Y-m"))
	{	$startwkdt= getstartwkdt($inputdt->format("Y-m-07"));
	}$curwk= min(5, ceil(($inputdt->format("j")-$startwkdt->format("j"))/7)+1);
	return $curwk;
}
//returns numeircal YYYYmm of previous month
function getlastmonth($inputdt='')
{	global $curdt,$lastmonthdt;
	if(!class_exists('DateTime')){return false;}
	if($inputdt instanceof DateTime)
	{	
	}else if(preg_match("#^[\d\s-:]+$#",$inputdt))
	{	$inputdt= new DateTime($inputdt);
	}else if(!empty($curdt))
	{	$inputdt= $curdt;
	}else
	{	$inputdt= new DateTime();
	}$lastmonthdt= new DateTime($inputdt->format("Y-m-d"));
	$lastmonthdt->modify("-".$inputdt->format("j")." day");
	return $lastmonthdt->format("Ym");
}
/*//Reformat datetime String to common format.
	Return blank string if invalid datetime format.
*/
function datestr_format($datestr='',$dt_format='')
{	if(empty($dt_format))
	{	$dt_format= "Y-m-d H:i:s";
	}try
	{	$datestr= preg_replace("#\s*[ap]m$#i","",$datestr);
		$datestr= new DateTime($datestr);
		$datestr= $datestr->format($dt_format);
	}catch(Exception $e)
	{	$datestr='';
	}return $datestr;
}
/*//Generate random codes with hopefully non-ambigous characters,
	starting with ID number then character 'Z' to avoid duplicates
	In:	$num= number of random codes to generate
		$startid= Starting ID number
		$minlen= mim length of codes
		$maxlen= max length of codes
		$chrs= string containing allowed characters to generate the code
	Out: An array containing the random codes	*/
function generaterdmcodes($num=1, $startid=1, $minlen=12, $maxlen=14, $chrs='')
{	$codeary=array();
	if(preg_match("#\D#",$startid))
	{	$startid= 1;	}
	if( empty($chrs) )
	{	$chrs= "ABCDEFGHJKLMNPQRSTUVWXY2345678";	}
	$chrslen= strlen($chrs);
	for($i=$startid;$i<$startid+$num;$i++)
	{	$code= "{$i}Z";
		$curlen= mt_rand($minlen,$maxlen);
		while( strlen($code)<$curlen )
		{	$code .= $chrs[ mt_rand(0,$chrslen-1) ];
		}
		$codeary[]= $code;
	}return $codeary;
	/*//Other way: md5(time) always give 33 chars of code, not very flexible
		$code= substr(md5($curdt->format("z")+$i),rand(0,33-$maxlen),rand($minlen,$maxlen));
	*/
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
//Does everything in rawurlencode, plus translate the characters (-._)
function rawurlencode2($str,$charary='')
{	if(empty($charary)||!is_array($charary))
	{	$charary= array("-",".","_");	}
	$codeary= dechexord($charary);
	$str= rawurlencode($str);
	return str_replace($charary,$codeary,$str);
}
function rawurldecode2($str,$charary='')
{	if(empty($charary)||!is_array($charary))
	{	$charary= array("-",".","_");	}
	$codeary= dechexord($charary);
	$str= str_replace($codeary,$charary,$str);
	return rawurldecode($str);
}
/*//Encode/Decode by replacing each character in $str with (its unicode + $seed)
	modulated by accepted range of unicodes characters (usually 33~126)*/
function unicode_rotate($str,$seed='',$decode=false)
{	if(!is_numeric($seed)){$seed= 1;}
	if($seed==0){return $str;}
	$str2='';	$unic_min= 33;$unic_max=126;
	$unic_range= $unic_max-$unic_min+1;
	$seed= $seed%$unic_range;
	for($i=0;$i<strlen($str);$i++)
	{	if(preg_match('#\r|\n|\s#',$str[$i]))
		{	$str2.=$str[$i];
			continue;
		}$j= ord($str[$i]);
		if(empty($decode)){
			if($j<$unic_min||$j>$unic_max){continue;}
			$j= $unic_min+(($j+$seed)%$unic_range);
		}else{
			$j=(($j-$seed-$unic_min)%$unic_range);
			while($j<$unic_min){$j+=$unic_range;}
		}$str2.= chr($j);
	}return $str2;
}
//convert string to char-wise hex code, pre-padded with 0's
function strtopadhex($str,$padsize='')
{	$str2= '';
	if(empty($padsize)||!is_numeric($padsize)){$padsize= 2;}
	for($i=0;$i<strlen($str);$i++)
	{	$str2.= sprintf("%0".$padsize."s",dechex(ord($str[$i])));	}
	return $str2;
}
function padhextostr($str,$padsize='')
{	$str2= '';
	if(empty($padsize)||!is_numeric($padsize)){$padsize= 2;}
	for($i=0;$i<strlen($str);$i+=$padsize)
	{$str2.= chr(hexdec(substr($str,$i,$padsize)));}
	return $str2;
}
/*//Mirros Java's DES/CBC/PKCS5Padding Cipher. Requires mcrypt library extension.
	For DES/ECB/PKCS5Padding, replace cbc with ecb */
function pkcs5cbc_encrypt($input,$ky,$iv='',$method='cbc')
{	$size = mcrypt_get_block_size(MCRYPT_TRIPLEDES,$method);
	$input = pkcs5_pad($input, $size);
	$td = mcrypt_module_open(MCRYPT_TRIPLEDES, '',$method, '');
	if(empty($iv)){$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td),MCRYPT_RAND);}
	mcrypt_generic_init($td, $ky, $iv);
	$data = mcrypt_generic($td, $input);
	mcrypt_generic_deinit($td);
	mcrypt_module_close($td);
	return $data;
}
function pkcs5cbc_decrypt($crypt,$ky,$iv='',$method='cbc')
{	//$crypt = urldecode($crypt);	$crypt = base64_decode($crypt);
	$td = mcrypt_module_open (MCRYPT_TRIPLEDES,'',$method,'');
	if(empty($iv)){$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td),MCRYPT_RAND);}
	mcrypt_generic_init($td, $ky, $iv);
	$decrypted_data = mdecrypt_generic($td, $crypt);
	mcrypt_generic_deinit ($td);
	mcrypt_module_close ($td);
	$decrypted_data = pkcs5_unpad($decrypted_data);
	return $decrypted_data;
}
function pkcs5_pad($text,$blocksize)
{	$pad= $blocksize-(strlen($text)%$blocksize);
	return $text. str_repeat(chr($pad),$pad);
}
function pkcs5_unpad($text)
{	$pad= ord($text{strlen($text)-1});
	if($pad>strlen($text)){return false;}
	return substr($text,0,-1*$pad);
}
/*//Creates a link with a thumbnail image for a PDF file,
		provided both the pdf & thumbnail image file is nmed the same and exist in the same folder.
	Input: $filename= required full path to the pdf file. $url= optional link location if not the same as $filename.
		$extra= optional html tag attributes to define th behaviour of the link.
		Examples of $extra: "target='blank'" to open link in a new window, "onclick='_JAVASCRIPT-ACTIONS_' for specific actions"
	Output: HTML Text representing the thumbnail link
*/
function pdfthumblink($filename, $url='', $extra='', $imgfile='')
{	$curdir='';		$displayname= $filename;		$curext='';
	if(preg_match("#(.+/)([^/]*)(\.[^\.]+)$#",$filename,$matches))
	{	$curdir= str_replace(dirname(getfullurl())."/",'',$matches[1]);
		$displayname= $matches[2];
		$curext= $matches[3];
	}debugprint("pdfthumblink {$curdir} | {$displayname} | {$curext}");
	if(empty($url))
	{	$url= $filename;
	}$url= urlencode_min($url);
	if(empty($imgfile))
	{	$exts= array('.jpg','.pdf.jpg','.gif','.png');
		foreach ($exts as $ext)
		{	if(file_exists($curdir.$displayname.$ext))
			{	$imgfile= $curdir.$displayname.$ext;
				break;
			}
		}
	}if(preg_match("#FULLNAME#",$extra))	//special case: use filename as displayname
	{	$displayname= $filename;	$curext= '';
		$extra= preg_replace("#FULLNAME#",'',$extra);
	}if(empty($imgfile))
	{	return "<a href='{$url}' class='pdfthumblink' {$extra}><b>{$displayname}{$curext}</b><br></a>";
		
	}$imgurl= dirname(getfullurl())."/".urlencode_min($imgfile);
	return "<a href='{$url}' class='pdfthumblink' {$extra}>{$displayname}{$curext}<img src='{$imgurl}' class='pdfthumbimg' alt='_'></a>";
}


/*Example of PDF displayed in inner frame with open options bekiw.
Credits: partners.adobe.com/public/developer/en/acrobat/PDFOpenParameters.pdf*/
function pdf_iframe($filename,$opts='')
{	if(empty($opts))
	{	$opts= "#page=1&pagemode=thumbs&toolbar=0&scrollbar=0&statusbar=0&messages=0&navpanes=0'frameborder='0'";
	}
	return "<iframe src='".$filename.$opts."></iframe>";
}


//workaround for filemtime due to 32bit unix time overflow bug
function filemtime_exec($execpath)
{	$execpath= execpath_parse($execpath);
	$execpath= "ls \"".$execpath."\"";
	if(preg_match("#^Windows#i",php_uname()))
	{	$execpath= "dir \"".$execpath."\"";
	}debugprint("EXECing - ".$execpath);
	exec($execpath,$str);
	$mtimes= array();
	foreach($str as $line)
	{	if(preg_match("#^\s*(\d+/\d+/\d+\s*\d+:\d+ [AP]M)\s*[\d,]*\s*(.*)$#",$line,$matches))
		{	$mtimes[trim($matches[2])]= trim($matches[1]);
		}
	}if(count($mtimes)==0)
	{	$mtimes= $str;
	}return $mtimes;
}

function uploadweb($filename,$data='',$pass='',$url='',$timeout=0)
{	global $libdir;
	if(empty($pass))
	{	return false;
	}if(empty($url))
	{	$url= $libdir."uploadweb.php";
	}$ch= curl_init();
	curl_setopt($ch,CURLOPT_URL, $url);
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
	curl_setopt($ch,CURLOPT_TIMEOUT,$timeout);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch,CURLOPT_POST, true);
	if(empty($data)&& file_exists($filename))
	{	$fh = fopen($filename,'rb');
		$data = fread($fh,filesize($filename));
		fclose($fh);
		$filename= basename($filename);
	}$poststr= '';
	foreach(explode(',','filename,data,pass') as $param)
	{	if(empty($param))
		{	continue;
		}$val= $$param;
		$val= preg_replace("#%([\dABCDEF][\dABCDEF])#i","%25$1",$val);
		$val= str_replace(array('+'),array('%2b'),$val);
		$poststr.= "&{$param}=". urlencode_min($val);
	}$poststr= preg_replace("#^&#",'',$poststr);
	curl_setopt($ch,CURLOPT_POSTFIELDS,$poststr);
	$result= curl_exec($ch);
	curl_close($ch);
	return $result;
}function ftp_upload($filedest,$filelocal,$ftphost,$ftpuser='',$ftppass='')
{	$ftpconn= ftp_connect($ftphost);
	if(empty($ftpconn))
	{	return false;}
	if(!empty($ftpuser))
	{	if(!ftp_login($ftpconn,$ftpuser,$ftppass))
		{	ftp_close($ftpconn);
			return false;
		}//ftp_pasv($ftpconn,true);
	}if(is_resource($filelocal))
	{	$result= ftp_fput($ftpconn,$filedest,$filelocal,FTP_BINARY);
	}else
	{	$result= ftp_put($ftpconn,$filedest,$filelocal,FTP_BINARY);
	}//do not use FTP_ASCII, will corrupt file upload
	ftp_close($ftpconn);
	return $result;
}//get unique name before upload to web directory with _$number append to end of filename
function uploadweb_fileuniqname($filename,$httpdir)
{	if(!preg_match("#^\w+://#",$httpdir))
	{	$httpdir= "http://".$_SERVER['HTTP_HOST']."/".$httpdir;
	}$httpdir= preg_replace("#^file:/+#i","file:////",$httpdir);
	$j=0;
	do{	$j++;
		$fdest= preg_replace("#\.[^\.]+$#","_{$j}$0",$filename);
		$url= $httpdir.$fdest;
		try
		{	$result= getheader2($url);
		}catch(Exception $e)
		{	$result= '';
		}if(is_array($result))
		{	$result= current($result);
		}$fileexist= preg_match("#200 OK|Last-Modified:#i",$result);
	}while($fileexist);
	return $fdest;
}

/*//Searches for files under a specifed directory, and returns an array with the matching files' fullpath name as keys,
		each key is mapped to an dictornary of its basic file info (e.g. modifytime, size, permission).
	In: $dirpath= the full path to the directory to preform the search
		$pattern= (optional) regular expression for matching file names. By default matches any files with an alphabet in it
		$opt_recur= (optional) flag indicating whether to preform search on the subdirectories as well
		$opt_infile= (optional) indicates max file size to search for content mathcing $pattern
		$ftpconn= (optional) an FTP resource connection if search is to be done on over an FTP connection. Otherwise leave it blank*/
function searchfiles($dirpath='', $pattern='',$recursive=false, $ftpconn=null)
{	if(empty($dirpath))
	{	$dirpath= "./";
	}if(function_exists('filename_parse'))
	{	$dirpath= filename_parse($dirpath);
	}$matchfiles=array();
	if(!empty($ftpconn))			//FTP directory
	{	$dirlist= ftp_rawlist($ftpconn, $dirpath);
	}else if(file_exists($dirpath))	//Local directory
	{	$dirlist= scandir($dirpath);
	}else
	{	debugprint("searchfiles- WARNING {$dirpath} not exist");
		return $matchfiles;
	}if(empty($pattern))
	{	$pattern='#[A-Za-z0-9]+#';
	}else if(preg_match("#^[A-Za-z]#",$pattern)|| !preg_match("#".str_replace("#","\\#",substr($pattern,0,1))."[A-Za-z0-9]*$#",$pattern))
	{	$pattern= '#'.$pattern.'#';
	}$dirpath= preg_replace("#^\.?/#",'',$dirpath);
	foreach($dirlist as $filename)
	{	if( !empty($ftpconn) )	//FTP retrieved file
		{	$finfo= preg_split("/[\s]+/", $filename, 9);
			if(count($finfo)==9)
			{	$filename= $finfo[8];
				$isdir_flag= preg_match("#^d#",$finfo[0]);
				//filestat contains:  modifytime, size, permission
				$filestat= array(
					'mtime'=> $finfo[5].' '.$finfo[6].' '.$finfo[7],
					'size'=> $finfo[4],
					'perm'=> $finfo[0]
					);
			}else
			{	continue;
			}
		}else					//Locally retrieved file
		{	$isdir_flag= is_dir($dirpath.$filename);
			$filestat= array(
				'mtime'=> filemtime($dirpath.$filename),
				'size'=> filesize($dirpath.$filename),
				'perm'=> fileperms($dirpath.$filename),
				);
				//filemtime is susceptible to 32bit unix time_t overflow bug
				if(empty($filestat['mtime'])|| $filestat['mtime']<=0&& function_exists("filemtime_exec"))
				{	$filestat['mtime']= filemtime_exec($dirpath.$filename);
				}
		}//Directory that is not . or ..
		if( $isdir_flag&& !preg_match("/^\.+$/",$filename) )
		{	$filename .= '/';
			if( preg_match($pattern, $dirpath.$filename) )
			{	$matchfiles[$dirpath.$filename]= $filestat;
			}if( !empty($recursive) )
			{	$matchfiles= array_merge($matchfiles,  searchfiles($dirpath.$filename, $pattern, $recursive, $ftpconn)  );
			}
		}else if( !$isdir_flag&& preg_match($pattern, $dirpath.$filename) )
		{	$matchfiles[ $dirpath.$filename ]= $filestat;
		}
	}
	debugprint("searchfiles {$dirpath}, PATT:{$pattern}, recur:{$recursive}, FTP:{$ftpconn}- ".var_export($matchfiles,true));
	return $matchfiles;
}
/*//List contents within a directory in html code that appears like Windows Explorer, including subdirectories. Non-recursive
	Inputs: $filelist= Dictonary with entries mapping 'fake' path to the real fullpath of the files you want to list.
		The "Fake" path is the path in which you want the file to be listed like; for example, make the
		fake path as root directory (/) to list it at the very top under no other subdirectories.
	credits: www.stylusstudio.com/xsllist/200104/post11060.html for escaping single quotes
*/
function listfileshtml($filelist, $selecteddir='', $icondir='|DEFAULT|')
{	debugprint("listfileshtml ({$selecteddir}): ".var_export($filelist,true));	
	$lastdir='';
	$output='';
	if($icondir=='|DEFAULT|')
	{	$icondir= '../logos/';
	}foreach(array_keys($filelist) as $fpath)
	{	$rpath= $filelist[$fpath];
		$displayname= basename($fpath);
		$link= str_replace("%2F","/",rawurlencode2($rpath));
		if(preg_match("#/$#",$fpath))			//directories
		{	$link= rawurlencode2($fpath);
			if(!empty($selecteddir)&& $selecteddir==$lastdir&&
				$selecteddir!=$fpath&& !preg_match("#<li style=#",$output))
			{	// $output .= "<li style='list-style-type:none;'><i>None</i></li>";
			}while(!empty($lastdir) && !preg_match("#^{$lastdir}#",$fpath) )
			{	$output .= "</ul>";
				$lastdir= preg_replace("#[^/]+/$#",'',$lastdir);
			}$lastdir= $fpath;
			if($selecteddir==$fpath)
			{	$output .= "<a name='opendir'><li class='dirli' style='list-style-image:url(\"{$icondir}opendiricon.gif\")'><input type='submit' class='link' name='subdir|{$link}' value='".str_replace("'","*",$displayname)."'></li></a><ul>";
			}else
			{	$output .= "<li class='dirli' style='list-style-image:url(\"{$icondir}diricon.gif\")'><input type='submit' class='link' name='subdir|{$link}' value='".str_replace("'","*",$displayname)."'></li><ul>";
			}
		}else if( preg_match("#job\s*post#i",$rpath) ) 				//special case for job postings
		{	if(!preg_match("#Job(postings?|apply)\.html#i",basename($fpath)))
			{	$link= dirname($fpath)."/";
				$mtime= preg_replace("#\d+:.*$#",'',getheader2( dirname(getfullurl())."/".$rpath, "Last-Modified"));
				$jobtitle= basename($fpath);
				$link= rawurlencode2("{$link}|{$jobtitle}");
				$displayname= preg_replace("#\.[^\.]*$#",'',$displayname);
				$displayname .= " &nbsp &nbsp &nbsp (Posted:{$mtime})";
				$output .= "<li style='list-style-image:url(\"{$icondir}fileicon.gif\")'><input type='submit' class='link' name='job|{$link}' value='".str_replace("'","*",$displayname)."'></li>";
			}
		}else if( preg_match("/\.pdf$/i",$rpath)  )		//pdf file
		{	if( file_exists("{$rpath}.jpg") )
			{	$output.= pdfthumblink($rpath,'','',"{$rpath}.jpg");
			}else
			{	$output.= "<li style='list-style-image:url(\"{$icondir}pdficon.gif\")'><a target='_blank' href='{$link}'>{$displayname}</a></li>";
			}
		}else if( preg_match("/(.*)\.(jpg|pdf\.jpg|gif|png)$/",$rpath,$matches) )	//image file
		{	if( file_exists("{$matches[1]}.pdf") )
			{	debugprint("pdfthumb: {$matches[1]}");
			}else
			{	$output.= "<li style='list-style-image:url(\"{$icondir}jpgicon.gif\")'><a target='_blank' href='{$link}'>{$displayname}</a></li>";
			}
		}else if( preg_match("/\.url$/i",$rpath)  )		//web link file
		{	$target=$rpath;
			if ( preg_match("/URL=(\S+)/", readtxtfile($rpath) , $matches) )
			{	$target= $matches[1];	}
			$output.= "<li style='list-style-image:url(\"{$icondir}linkicon.gif\")'><a target='_blank' href='{$target}'>{$displayname}</a></li>";
		}
		else if( preg_match("/\.(html?|mht)$/i",$rpath)  )		//web file
		{	$target= $rpath;
			$output.= "<li style='list-style-image:url(\"{$icondir}htmicon.gif\")'><a target='_blank' href='{$target}'>{$displayname}</a></li>";
		}
		else 											//all other files
		{	$exticon= preg_replace("#.*\.([^\.]*)$#","$1",$rpath);
			$exticon= "{$icondir}{$exticon}icon.gif";
			$tmptxt= readtxtfile($exticon,1);
			if(empty($tmptxt))
			{	$exticon= "{$icondir}fileicon.gif";	}
			$output.= "<li style='list-style-image:url(\"{$exticon}\")'><a target='_blank' href='{$link}'>{$displayname}</a></li>";
		}
	}$output= "<ul class='filelist'>".$output."</ul></ul>";
	$output= "<form method='post' action='".$_SERVER['REQUEST_URI']."#opendir'>". $output. "</form>";
	$output= preg_replace("#(<a name='opendir'[^>]*><li[^>]*><input [^>]*></li></a>)<ul></ul>#",
		"$1<ul><li style='list-style-type:none;font-style:italic;'>None</li></ul>",$output);
	$output= str_replace("<ul></ul>",'',$output);
	return $output;
}
function htmlulcssmenu($menulist,$portionfilter='',$orientation='V'){
	$csscode= "<style type='text/css'>
.hoverMenuV,.hoverMenuV ul,.hoverMenuH,.hoverMenuH ul{cursor:pointer;padding:0;margin-left:1px;margin-right:2%;color:black;z-index:10;}
.hoverMenuV li,.hoverMenuH li
{white-space:nowrap;list-style-type:none;position:relative;background-color:#87B23F;border:1px outset #67921F;padding:4px;margin-bottom:2px;color:black;}
.hoverMenuV li > ul,.hoverMenuH li > ul{display:none;position:absolute;color:black;}
.hoverMenuV li a,.hoverMenuV li:hover > ul,.hoverMenuH li a,.hoverMenuH li:hover > ul
{display:block;text-decoration:none;color:black;}
.hoverMenuV,.hoverMenuV ul,.hoverMenuH li{float:left;}
.hoverMenuV li > ul{top:0;left:100%;}
.hoverMenuH li > ul{margin-top:5%;left:10%;}</style>";
	$csscode.= "<!--[if lt IE 7]><![if gt IE 5.0]>
	<style type='text/css'>
	/*//Above conditional comments makes is only visible in IE5+ && <IE7 */
	.hoverMenuV ul{display:none;position:absolute;top:0;left:95%;}
	.hoverMenuH ul{display:none;position:absolute;margin-top:30%;}<</style>	
	<script type='text/javascript'>window.attachEvent('onload',cssmenuinitjs);</script><![endif]>
	<![if lt IE 6.0]><style>.hoverMenuV ul{left:85%;}</style><![endif]>
	<![endif]-->";
	if(preg_match('#css#i',$portionfilter)){return $csscode;}
	if(preg_match('#ul#i',$portionfilter)){$csscode='';}
	if($orientation!='H'){$orientation='V';}
	$str=''; $lastgroup='';
	if(!is_array($menulist)){$menulist=explode('\n',$menulist);}
	foreach(array_keys($menulist) as $i){
		$btnname=''; $btnlink=''; $curgroup='';
		if(!is_array($menulist[$i])){$menulist=explode('|',$menulist[$i]);}
		if(count($menulist[$i])>0){
			$btnname=array_shift($menulist[$i]);
		}if(count($menulist[$i])>0){
			$btnlink=array_shift($menulist[$i]);
		}if(empty($btnlink)|| empty($btnname)){continue;}
		if(count($menulist[$i])>0){
			$curgroup=array_shift($menulist[$i]);
		}if($lastgroup!=$curgroup){
			if(!empty($lastgroup)){$str.= "</ul></li>";}
			if(!empty($curgroup)&& $curgroup!=$btnname){$str.= "<li><a href='{$btnlink}'>{$curgroup}</a><ul>";}
			$lastgroup= $curgroup;
		}$str.= "<li><a group='{$curgroup}' href='{$btnlink}'>".htmlspecialchars($btnname)."</a>";
		if($curgroup==$btnname){$str.='<ul>';}else{$str.='</li>';}
	}if(!empty($lastgroup)){$str.= "</ul></li>";}
	if(!empty($str)){$str= "<ul id='hoverMenu' class='hoverMenu{$orientation}'>".$str."</ul>";}
	return $csscode.$str;
}


//used for sorting by directory name, then by file extension;	example: uksort($matchfiles,'sortfunc_dirext');
function sortfunc_dirext($a,$b)
{	preg_match("#(.*?)([^/]*?)([^/\.]*)$#",$a,$aary);
	preg_match("#(.*?)([^/]*?)([^/\.]*)$#",$b,$bary);

	if($aary[1]==$bary[1])		//directories same, compare extension
	{	if($aary[3]==$bary[3])	//extension same, compare filename
		{	return ($aary[2] < $bary[2])? -1 : 1;
		}
		return ($aary[3] < $bary[3])? -1 : 1;
	}
	return ($aary[1] < $bary[1])? -1 : 1;
}
//for sorting 2D array with first key as normal integers. must set global var #sortfield	example: usort($2dary,'sortfunc_2dary')
function sortfunc_2dary($a,$b)
{	global $sortfield;
	if(empty($sortfield)){	return strcmp($aa,$bb);	}
	$aa= empty($a[$sortfield])?'':$a[$sortfield];
	$bb= empty($b[$sortfield])?'':$b[$sortfield];
	//debugprint("sortfunc ".var_export($a,true).' '.var_export($b,true)."{$aa} {$bb}");
	return ($aa<=$bb)?-1:1;
}
//sort by specified delimted fields, controlled in 3 global vars: sortd(delimiter), sortf(field), sortr(reverse flag)
//	usage: usort($ary,'sortfunc_delimfields');
function sortfunc_delimfields($a,$b)
{	global $sortf, $sortd, $sortr;
	if(empty($sortd))
	{	$sortd= "|";		}
	if(empty($sortf))
	{	$sortf= 0;	}
	$str= 0;
	$aary= explode($sortd,$a);
	$bary= explode($sortd,$b);
	if(empty($aary[$sortf]))
	{	$str= -1;	}
	else if(empty($bary[$sortf]))
	{	$str= 1;		}
	else if($aary[$sortf]<$bary[$sortf])
	{	$str= -1;	}
	else
	{	$str= 1;		}
	if(!empty($sortr))
	{	$str= 0- $str;	}
	return $str;
}

function readtxtfile($filename, $lines='', $mode='r', $tohtml=false)
{	if(!is_readable($filename)&& !preg_match("#^https?://#",$filename))
	{	debugprint("Server Unable to read file: {$filename}!");
		return '';
	}if(!is_numeric($lines)){$lines=-1;}
	if(preg_match("#iframe#i",$tohtml))		//Output as innerframe
	{	$contents= file($filename);
		$lines= substr_count( implode('',$contents) ,"<p");
		$lines += substr_count( implode('',$contents) ,"<br");
		if ( $lines <=0 )
		{	$lines= count($contents);	}
		$fheight= 10+24*$lines;
		if ($fheight > 700) { $fheight=700; }
		debugprint("Lines: {$lines}");
		return "<iframe src='{$filename}' style='border:none;width:95%;height:{$fheight}px;white-space:pre-wrap' frameborder='0' scrolling='no'></iframe>";
	}$fh= fopen($filename,$mode);
	$contents= '';
	while ($lines!=0&& !empty($fh)&& !feof($fh) )
	{	$contents .= fgets($fh);
		$lines--;
	}fclose($fh);
	//Microsoft Word's HTML conversion: extact only the body portion, no header formatting info
	if(!empty($tohtml)&& preg_match("#<body#",$contents)&& preg_match("#schemas-microsoft-com:office#",$contents))
	{	if(preg_match("#MsoNormal[^\{]*\{[^\}<>]*font-size:\s*([^;]+)#i",$contents,$matches))
		{	$contents= preg_replace("#<body[^>]*>#i","$0 <div style='font-size:".$matches[1]."'>",$contents);
			$contents= preg_replace("#</body#i","</div>$0",$contents);
		}$contents= preg_replace("#<body[^>]*>#i","<bodystart>",$contents);
		$start= stripos($contents,"<bodystart>");
		$end= stripos($contents,"</body");
		$contents= substr($contents,$start,$end-$start);
		$contents= preg_replace("#\r|\n#",' ',$contents);
		$contents= preg_replace("#<o:p>([^<]*)</o:p>#","$1",$contents);
		$contents= preg_replace("#<!\[if [^\]]*\]>|<!\[endif\]>|</?body[^>]*>#",'',$contents);
		$contents= preg_replace("#position:\s*absolute;?#i",'',$contents);
		$contents= preg_replace("#margin-(left|right)\s*:\s*-[^;]+;?#i",'',$contents);
		$contents= preg_replace("#<p [^>]*style='([^']+)'[^>]*>(.*?)</p>#","<div style='$1'>$2</div>",$contents);
		$contents= preg_replace("#<p[^>]*class=Mso[^>]*>#","<br>",$contents);
		$contents= preg_replace("#</p>|(mso-[\w-]*|tab-interval|tab-stops):[^;'\"]+;?#i",'',$contents);
		$contents= preg_replace("#<img([^>]*)src=\"([^:>]+)#","<img$1src=\"".dirname($filename)."/$2",$contents);
		$contents= preg_replace("#<span[^>]*class=(SpellE|GramE)[^>]*>([^<]*)</span>#","$2",$contents);
	}else if(!empty($tohtml))		//regular text to html conversion: newlines to br
	{	$contents= preg_replace("/([^\n]*)\n/","<h2>$1</h2>",$contents,1);
		$contents= preg_replace("/\r?\n/","<br>",$contents);
	}return $contents;
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
/*//Resizes image file to $nw widht and $nh height.
	Requires GD dll library*/
function gdresizeimagefile($filename,$filedest='',$nw='',$nh='')
{	if(empty($nw)){$nw='';}if(empty($nh)){$nh='';}
	if(!is_numeric($nw)&&!is_numeric($nh)){$nw=200;$nh=200;}
	if(preg_match("#\.(\w+)$#i",$filename,$ext))
	{	$ext= str_replace(array('jpg'),array('jpeg'),strtolower($ext[1]));
		if(function_exists("imagecreatefrom".$ext))
		{$img= call_user_func("imagecreatefrom".$ext,$filename);}
	}if(empty($img)|| !is_resource($img)){$img= imagecreatefromjpeg($filename);}
	if(empty($img)|| !is_resource($img)){return false;}
	list($width,$height)= getimagesize($filename);
	//percent-based caclulation of other dimension if only 1 dimension provided
	if(!is_numeric($nw)){$nw=round($width*$nh/$height);}
	if(!is_numeric($nh)){$nh=round($height*$nw/$width);}
	$imgresized= imagecreatetruecolor($nw,$nh);
	$img= imagecopyresampled($imgresized, $img,0,0,0,0,$nw,$nh,$width,$height);
	if(preg_match("#\.(\w+)$#i",$filedest,$ext)){
		$ext= str_replace(array('jpg'),array('jpeg'),strtolower($ext[1]));
		if(function_exists("image".$ext))
		{$img= call_user_func("image".$ext,$imgresized,$filedest);}
	}else{
		$filedest= preg_replace("#\.[^\.]+$#",".jpg",$filename);
		imagejpeg($imgresized,$filedest);
	}return $filedest;
}
function emailstrfilter($emailstr,$ary1='')
{	$outputstr='';
	foreach(explode(';',$emailstr) as $inkey)
	{	$val1= $inkey;
		if(!empty($ary1)&& !empty($ary1[$inkey])&& $ary1[$inkey])
		{	$val1= $ary1[$inkey];
		}if(preg_match("#@#",$val1))
		{	$outputstr.= $val1.';';
		}
	}return $outputstr;		
}
/*//Sends email through sockets using pure SMTP text protocol.
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

/*//If fsockopen in "sendemail" function above not working, send via web-based proxy.
	Recommended format for attachs: array( array($data, $fname), ..... etc. )
*/
function sendemailweb($from='',$to='',$subject='',$body='',$attachs='',$url='',$extra='',$timeout=7)
{	if(empty($url))
	{	$url= "sendemail.php";
	}if(is_array($to)){$to=implode('|',$to);}
	if(is_array($attachs))
	{	$str='';
		foreach($attachs as $attach)
		{	if(is_array($attach))
			{	$filename= array_shift($attach);
				if(!preg_match("#[\*\?<>\|]#",$filename))
				{	$fh= fopen($filename,'r');
				}while(!empty($fh)&& !feof($fh))
				{	$str.= fgets($fh);
				}if(empty($fh))
				{	$str.= $filename;
				}else
				{	fclose($fh);
				}$str.= '-#-#2#-'.implode('-#-#2#-',$attach);
			}else
			{	$str.= $attach;
			}$str.= '-#-#1#-';
		}$attachs= str_replace('+','\+',$str);
	}$ch= curl_init();
	curl_setopt($ch,CURLOPT_URL, $url);
	if(!empty($timeout)&& $timeout!=0)
	{	if(is_numeric($timeout))
		{	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
			curl_setopt($ch,CURLOPT_TIMEOUT,$timeout);
		}
	}curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch,CURLOPT_POST, true);
	$poststr= '';
	foreach(explode(',','from,to,subject,body,attachs') as $param)
	{	$val= $$param;
		$val= preg_replace("#%([\dABCDEF][\dABCDEF])#i","%25$1",$val);
		$val= str_replace(array('+'),array('%2b'),$val);
		$poststr.= "&{$param}=". urlencode_min($val);
	}$poststr= preg_replace("#^&#",'',$poststr).$extra;
	curl_setopt($ch,CURLOPT_POSTFIELDS, $poststr);
	return curl_exec($ch);
}
//simplified version for hosted web service that only support mail function, not socket
function sendemailattach($from,$to,$subject,$body,$attachs)
{	$semi_rand = md5(time());
	$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
	$headers = "From: ".$from;
	$headers .= "\r\nMIME-Version: 1.0\r\n";
	$headers .="Content-Type: multipart/mixed;\r\n";
	$headers .=" boundary=\"{$mime_boundary}\"";
	$body="This is a multi-part message in MIME format.\r\n\r\n".
			"--{$mime_boundary}\r\n".
			"Content-Type: text/plain; charset=\"iso-8859-1\"\r\n".
			"Content-Transfer-Encoding: 7bit\r\n\r\n".
			$body." \r\n";
	if(!empty($_FILES['resume']['name']))
	foreach(array_keys($attachs) as $inkey)
	{	$fileatt = $attachs[$inkey][0];
		$fileatt_name =  $attachs[$inkey][1];
		$fileatt_type =  $attachs[$inkey][2];
		$fh = fopen($fileatt,'rb');
		$data = fread($fh,filesize($fileatt));
		fclose($fh);
		$data = chunk_split(base64_encode($data));
		$body .= "--{$mime_boundary}\r\n";
		$body .= "Content-Type: {$fileatt_type};\r\n";
		$body .= " name=\"{$fileatt_name}\"\r\n";
		$body .= "Content-Transfer-Encoding: base64\r\n\r\n";
		$body .= $data . "\r\n\r\n";
		$body .= "--{$mime_boundary}--\r\n";
	}$body= str_replace(array('‘','’','“','”','–'), array('\'','\'','"','"','-'),$body);
	if(mail($to,$subject,$body,$headers))
	{	return "quitresponse: 221";
	}return false;
}


function printiis404()
{	header("Status: 404");
	echo "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01//EN' 'http://www.w3.org/TR/html4/strict.dtd'>".
		"<HTML><HEAD><TITLE>The page cannot be found</TITLE> <META HTTP-EQUIV='Content-Type' Content='text/html; charset=Windows-1252'>".
		"<STYLE type='text/css'> BODY { font: 8pt/12pt verdana } H1 { font: 13pt/15pt verdana } H2 { font: 8pt/12pt verdana }".
		"A:link { color: red } A:visited { color: maroon } </STYLE> </HEAD><BODY><TABLE width=500 border=0 cellspacing=10><TR><TD>".
		"<h1>The page cannot be found</h1> The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.".
		"<hr><p>Please try the following:</p> <ul><li>Make sure that the Web site address displayed in the address bar of your browser is spelled and formatted correctly.</li>".
		"<li>If you reached this page by clicking a link, contact the Web site administrator to alert them that the link is incorrectly formatted. </li>".
		"<li>Click the <a href='javascript:history.back(1)'>Back</a> button to try another link.</li> </ul> <h2>HTTP Error 404 - File or directory not found.".
		"<br>Internet Information Services (IIS)</h2><hr><p>Technical Information (for support personnel)</p> <ul> <li>Go to <a href='http://go.microsoft.com/fwlink/?linkid=8180'>Microsoft Product Support Services</a>".
		" and perform a title search for the words <b>HTTP</b> and <b>404</b>.</li> <li>Open <b>IIS Help</b>, which is accessible in IIS Manager (inetmgr), and search for topics titled <b>Web Site Setup</b>,".
		" <b>Common Administrative Tasks</b>, and <b>About Custom Error Messages</b>.</li> </ul> </TD></TR></TABLE></BODY></HTML>";
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
	Note: Cookie are set in http header, so either use ob_start or run this before any echo.
*/
function updatecookies($lifetime="+1 hours",$path='|DEFAULT|')
{	global $curdt;
	if(!is_array($_COOKIE)|| count($_COOKIE)==0)
	{	return;}
	if(!empty($lifetime))
	{	try
		{	$expiredt='';
			if(!empty($curdt))
			{	$expiredt= $curdt->format("Y-m-d H:i:s");
			}$expiredt= new DateTime($expiredt);
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
function getipaddr()
{	return $_SERVER['REMOTE_ADDR'];
}
function getinisettings()
{	return var_export(ini_get_all(),true);
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
{	header("refresh:{$timeout};url={$loc}");
	//header("Location:{$loc}");
	return "<head><meta http-equiv='REFRESH' content='{$timeout};url={$loc}'/></head>";
}

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
//credits: articles.sitepoint.com-Mitchell Harper&David Rusik, 	en.wikipedia.org/wiki/Bank_card_number
function cardverify_luhn($cardNumber,$cardtype=0)
{	$cardNumber= preg_replace("#\s#",'',$cardNumber);
	if(preg_match("#\D#",$cardNumber)){		return false;	}
	$numSum= 0;
	$numlen= strlen($cardNumber);
	for($i=$numlen; $i>=0  ; $i--)
	{	$currentNum= substr($cardNumber, $i, 1);
		// Double every second digit
		if(($numlen-$i)%2==0)
		{$currentNum*= 2;}
		// Add digits of 2-digit numbers together
		if($currentNum>9)
		{	$firstNum= $currentNum % 10;
			$secondNum= floor(($currentNum - $firstNum)/10);
			$currentNum= $firstNum + $secondNum;
		}$numSum += $currentNum;
	}debugprint("cardverify_luhn {$cardNumbe} -> numSum: {$numSum}");
	return $numSum%10==0?true:false;
}

//converts (up to 2D) array into special url query string;	inspired from php functions http_build_query & parse_str
function array2urlgetstr($ary1='',$eqsign='',$delim='',$eqsign2='',$delim2='')
{	$str= '';
	if(!is_array($ary1))
	{$ary1= explode(",",$ary1);
	}$charary= array();
	$charary[]=$eqsign;	$charary[]=$delim;
	$codeary= dechexord($charary);
	//also remove \r & \n line breaks
	$charary[]="\r";$codeary[]='';
	$charary[]="\n";$codeary[]='';
	if(empty($eqsign))
	{	$eqsign="=";
	}if(empty($delim))
	{	$delim="&\n";
	}if(empty($eqsign2))
	{	$eqsign2= $eqsign;
	}if(empty($delim2))
	{	$delim2= $delim;
	}foreach(array_keys($ary1) as $inkey)
	{	if(is_array($ary1[$inkey]))
		{	$ary1[$inkey]= array2urlgetstr($ary1[$inkey],$eqsign2,$delim2);
		}$str.= $inkey.$eqsign.str_replace($charary,$codeary,$ary1[$inkey]).$delim;
	}return $str;
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
					$str.= "<td><div class='tdoverflow'>{$result[$inkey1][$inkey2]}</div></td>";
				}else
				{	$str.= "<td>{$result[$inkey1][$inkey2]}</td>";
				}
			}$str.= "</tr>";
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
				}$str.= "<tr><td colspan='".count(array_keys($result[$inkey1]))."' class='centered'>TOTAL: ".($cnt+1)."</td></tr>";
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
//$field|$fieldtype|$fieldlabel|$fieldreadonly|$fieldmand|$length|$value
function dbfield2editform($fieldary)
{	global $libdir;
	$str='';
	if(!is_array($fieldary))
	{	$fieldary= explode("\n",$fieldary);
	}foreach($fieldary as $fieldrow)
	{	if(!is_array($fieldrow)){$fieldrow=explode('|',$fieldary);}
		debugprint('dbfield2editform: '.implode('|',$fieldrow));
		$field= (count($fieldrow)>0)?array_shift($fieldrow):'';
		if($field==''){continue;}
		$fieldtype= (count($fieldrow)>0)?array_shift($fieldrow):'';
		$fieldlabel= (count($fieldrow)>0)?array_shift($fieldrow):$field;
		$fieldreadonly= (count($fieldrow)>0)?array_shift($fieldrow):'';
		$fieldmand= (count($fieldrow)>0)?array_shift($fieldrow):'';
		$length= (count($fieldrow)>0)?array_shift($fieldrow):'';
		$value= (count($fieldrow)>0)?array_shift($fieldrow):'';
		if(!empty($fieldreadonly))
		{	$fieldreadonly= " readonly='readonly' class='readonlybg'";
		}
		if(preg_match("#^datetime#i",$fieldtype)&& $value!=''&& !preg_match('#-#',$value)&& class_exists('DateTime'))
		{	$value= new DateTime($value);
			$value= $value->format('Y-m-d h:i A');
		}$str.= "<span class='label'>";
		$str.= $fieldlabel." &nbsp; ";
			//special case: field is datatime; use javascript datetime chooser
		if(preg_match("#^datetime#i",$fieldtype)&& empty($fieldreadonly))
		{	$str.= "<input type='text' id='{$field}' name='{$field}' value='{$value}' size='20' onfocus='NewCssCal(this.id,\"yyyy-mm-dd\",\"arrow\",\"H:i a\");' {$fieldreadonly}>";
			if(!empty($libdir)&& file_exists($libdir."btn_cal.gif"))
			{	$str.= "<img src='".$libdir."btn_cal.gif' style='cursor:pointer;' onclick='NewCssCal(this.previousSibling.id,\"yyyy-mm-dd\",\"arrow\",\"H:i a\");' alt='PickDate' onerror='var newbtn= document.createElement(\"input\");newbtn.type=\"button\";newbtn.value=this.alt;this.parentNode.replaceChild(newbtn,this
	);'>";
			}
		}else if(preg_match("#^bit$|^tinyint$#i",$fieldtype))
		{	$fieldreadonly= str_replace("readonly","disabled",$fieldreadonly);
			$str.= "<input type='checkbox' name='{$field}' value='1'".(empty($value)?'':" checked='checked' ")."{$fieldreadonly}>";
		}else if(preg_match("#^<#",$fieldtype))
		{	$str.= $fieldtype;
		}else if(preg_match("#^noinput$#i",$fieldtype))
		{	$str.= $value;
		}else if(preg_match("#^@#",$fieldtype))//array dropdown
		{	$fieldtype= explode("@", preg_replace("#^@#",'',$fieldtype));
			$selectlong='';
			$selectstr= "<option value=''></option>";
			foreach($fieldtype as $k1)
			{	$v1=$k1;
				if(preg_match("#\|#",$k1))
				{	$k1= trim(preg_replace("#\|.*#",'',$k1));
					$v1= preg_replace("#\|\s+\|#",'',$v1);
					if(strlen($v1)>=100&& empty($selectlong))
					{	$selectlong= "class='editform' onfocus='this.className=\"editformlong\";' onchange='this.className=\"editform\";'";
					}
				}$selectstr.= "<option value='{$k1}'".($k1==$value?" selected='selected'":'').">{$v1}</option>";
			}$str.= "<select name='{$field}' {$selectlong}>".$selectstr."</select> &nbsp; ";
		}else if(preg_match("#^~#",$fieldtype))	//radio list
		{	$fieldtype= explode("~", preg_replace("#^~#",'',$fieldtype));
			foreach($fieldtype as $k1)
			{	$v1=$k1;
				if(preg_match("#\|#",$k1))
				{	$k1= trim(preg_replace("#\|.*#",'',$k1));
					$v1= preg_replace("#\|\s+\|#",'',$v1);
				}$str.= "<input type='radio' name='{$field}' value='{$k1}'".($k1==$value?" checked='checked'":'').">{$v1} &nbsp; ";
			}
		}else if(preg_match("#int$#i",$fieldtype)){
			$str.= "<input type='text' name='{$field}' value='{$value}' size='10' onkeyup='jsnumericreplace(this,event.keyCode);'{$fieldreadonly}>";
		}else if(preg_match("#decimal$#i",$fieldtype)){
			$str.= "<input type='text' name='{$field}' value='{$value}' size='10' onkeyup='jsnumericreplace(this,event.keyCode,true);'{$fieldreadonly}>";
		}else if(preg_match("#^phone$#i",$fieldtype)){
			$str.= "<input type='text' name='{$field}' value='{$value}' size='{$length}' onkeyup='jsnumericreplace(this,event.keyCode,\"phone\");'{$fieldreadonly}>";
		}else if(preg_match("#^text#i",$fieldtype)|| $length>=100)
		{	if(!empty($fieldreadonly))
			{	$fieldreadonly= " onkeydown='return false;'";
			}$str.= "<textarea name='{$field}' maxlength='{$length}' class='editformlong".(empty($fieldreadonly)?'':' readonlybg')."'{$fieldreadonly}>{$value}</textarea><br>";
		}else
		{	$str.= "<input type='text' name='{$field}' maxlength='{$length}' value='{$value}' size='{$length}'{$fieldreadonly}>";
		}$str.= "</span>";
		$str.=" &nbsp; &nbsp; ";
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
function sqlErrorHandler($errno,$errstr,$errfile,$errline)
{	global $sqlerror;
	if(preg_match("#.*?message:(.*?)\(severity.*#i",$errstr,$matches))
	{	if(empty($sqlerror)){$sqlerror='';}
		$sqlerror.= $matches[1];
	}else{$sqlerror.= $errstr."[Line {$errline}]";}
}

/*//Query DB Table structure and store it into 2D map of
	columna name-> (ORDER, TYPE, MAXLEN, DEFAULT, DECIMAL, NULL, IDENTITY) info-array
	Implemented Systems: MSSQL, MYSQL
*/
function sql_descmap($tablename,$extraflag=false)
{	global $dbtypestr;
	$descmap= array();
	$searchsql= "desc ".$tablename;
	if(preg_match("#mssql#",$dbtypestr))
	{	$dbname= '';
		if(preg_match("#(^|.*?\.)dbo\.(.*)#i",$tablename,$matches))
		{	$dbname= $matches[1];
			$tablename= $matches[2];
		}$searchsql= "select *,columnproperty(object_id(TABLE_SCHEMA+'.'+TABLE_NAME),COLUMN_NAME,'IsIdentity') as 'ISIDENTITY' from {$dbname}information_schema.columns where table_name='".$tablename."'";
	}$tabledesc= sqlrunquery($searchsql);
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
		if(!empty($dbtypestr)&& preg_match("#mssql#",$dbtypestr))
		{	$resetsql= "dbcc CHECKIDENT(".$tablename.",RESEED,".$maxid.")";
		}return var_export(current(sqlrunquery($resetsql)),true);
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
	{	//ensure group by fields added
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
		}if($dbtype=='mssql') //ensure apostrophe in values will be included in sql
		{	$sql= preg_replace("#(\w)'(\w)#","$1''$2",$sql);
			$sql= preg_replace("#^desc\s+('.+')$#i","desc where table_name in ($1)",$sql);
			$sql= preg_replace("#^desc\s+([^\s,']+)$#i","desc where table_name='$1' order by ordinal_position",$sql);
			$sql= preg_replace("#^desc#i","select *,columnproperty(object_id(TABLE_SCHEMA+'.'+TABLE_NAME),COLUMN_NAME,'IsIdentity') as 'ISIDENTITY' from information_schema.columns",$sql);
			$sql= preg_replace("#^show tables?$#i","select name from sysobjects where xtype='U'",$sql);
		}else
		{	$sql= preg_replace("#(\w)'(\w)#","$1\\'$2",$sql);
		}$old_error_handler='';
		if($dbtype=="mssql" && function_exists("sqlErrorHandler"))
		{	error_reporting(E_ALL);
			$old_error_handler= set_error_handler("sqlErrorHandler");
		}$query= call_user_func("{$dbtype}_query",$sql, $dblink);
		if(!empty($old_error_handler))
		{	set_error_handler($old_error_handler);
			if(empty($debugmode))
			{	error_reporting(E_ERROR);}
		}if(empty($query)&& empty($sqlerror))
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
?>