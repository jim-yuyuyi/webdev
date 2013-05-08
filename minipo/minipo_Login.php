<?php
/*Template for GUI to manage database, PHP back-end
Much-Simplified version of MyphpAdmin
Mainly requires MySQL database table to be setuped,
and input fields / database connection variables in this script
*/
/* ------------------Common Functions imported from another PHP library file---------------- */
include_once("minipo_phplib.php");
include_once("minipo_config.php");
/* -----------------------------Specific Functions ------------------------------------ */
/* -----------------------------------Web Start---------------------------------------- */
ob_start();
sess_start();
noncache_header();

if(empty($objname))
{	$objname= 'Test';
	if(!empty($table_data))
	{	$objname= $table_data;
	}
}
if(empty($webtitle))
{	$webtitle= $objname;
}

$loggedin_flag= true;
//check user login if applicable
if(!empty($table_user)&& !empty($field_user))
{	if(empty($field_pass))
	{	$field_pass= '';
	}$loggedin_flag= minipo_checklogin_sess($table_user,$field_user,$field_pass);
}

//User already logged in- leave the Login page and go directly to Edit screen or Summary screen (default).
if(!empty($loggedin_flag))
{	$redir= "minipo_Summary.php";
	if(!empty($default_page)&& preg_match("#edit#i",$default_page))
	{	$redir= "minipo_Edit.php";
	}
	echo htmlredirect($redir,1);
	echo "Succesfully Logged In as :".$_SESSION[$field_userck];
	die;
}

echo printhtmlheader(strip_tags($webtitle),"minipo_csslib.css");
minipo_printtopinfo();

//Login failed or logout messages
if(!empty($_POST[$field_userck]))
{	if(empty($_POST['__logout']))
	{	echo "<div class='error'>Invalid Login for '".$_POST[$field_userck]."'!</div>";
	}else
	{	echo "You have logged off the account '".$_POST[$field_userck]."'<br><br>";
	}
}
//Login Screen
echo "<form enctype='multipart/form-data' method='post'>".EOL_CHR;
if(!empty($msg_login))
{	echo $msg_login;
}else
{	echo "<br><div>Please Login: </div>".EOL_CHR;
}
echo "<input type='hidden' name='".$default_mode."' value='true'>";
echo "<span class='lbl1'>User ID:</span>".EOL_CHR.
	"<input type='text' name='".$field_userck."' value='".htmlasciiencode($_SESSION[$field_userck])."'><br>";
echo "<span class='lbl1'>Password:</span>".EOL_CHR.
	"<input type='password' name='".$field_passck."'><br>".EOL_CHR;
echo "<span class='lbl1'> &nbsp; </span>".EOL_CHR.
	"<input type='submit' name='__login' value='Login'
		onclick='var p1= document.createElement(\"p\"); p1.innerHTML=\"Logging in, Please wait.\";this.parentNode.appendChild(p1);this.style.display=\"none\";'>
		<br>".EOL_CHR;
printhtmlbottom();
?>