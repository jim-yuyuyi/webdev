<?php
/*Template for GUI to manage database, PHP back-end
Much-Simplified version of MyphpAdmin
Mainly requires MySQL database table to be setuped,
and input fields / database connection variables in this script
*/
/* -------------------------------Global variables ---------------------------------- */
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

$updateresult= '';
$condary= array();
$field_summary= array();
if(!empty($field_noedit))
{	$field_noedit= getregexpmatchstr($field_noedit);
}

//check user login if applicable
$loggedin_flag= true;
if(!empty($table_user)&& !empty($field_user))
{	if(empty($field_pass))
	{	$field_pass= '';
	}$loggedin_flag= minipo_checklogin_sess($table_user,$field_user,$field_pass);
}
//failed login
if(empty($loggedin_flag))
{	$redir= "minipo_Login.php";
	echo htmlredirect($redir,1);
	die;
}

	//get all input fields from DB table, parse POST input into condary
	if(!empty($field_mand))
	{	if(is_array($field_mand))
		{	$field_mand= implode("|",$field_mand);
		}$field_mand= str_replace(",","|",trim($field_mand));
	}if(!empty($fielddateonly))
	{	if(is_array($fielddateonly))
		{	$fielddateonly= implode("|",$fielddateonly);
		}$fielddateonly= str_replace(",","|",trim($fielddateonly));
	}$field_mandmissing= '';
	$descmap= sql_descmap($table_data);
	
	debugprint('descmap: '.var_export($descmap,true));
	foreach(array_keys($descmap) as $field)
	{	$fieldtype= $descmap[$field]['TYPE'];
		$defaultval= $descmap[$field]['DEFAULT'];
		//ID field - use the first column if identity field not specified
		if(empty($fieldid)|| !empty($descmap[$field]['IDENTITY']))
		{	$fieldid= $field;			
		}
		if(!empty($descmap[$field]['IDENTITY']))
		{	$maxid= true;
		}
		$length=50;
		if(!empty($descmap[$field]['MAXLEN'])&& !preg_match("#\D#",$descmap[$field]['MAXLEN']))
		{	$length= $descmap[$field]['MAXLEN'];
		}//custom override value
		if(!empty($_POST[$field.'_custom']))
		{	$_POST[$field]= $_POST[$field.'_custom'];
		}if(array_key_exists($field,$_POST))
		{	if(is_array($_POST[$field]))
			{	$condary[$field]= implode(" ",$_POST[$field]);
			}else
			{	$condary[$field]= $_POST[$field];
			}if(preg_match("#^int|^decimal#i",$fieldtype)&& !is_numeric($condary[$field]))
			{	unset($condary[$field]);
			}
		}if(array_key_exists($field,$condary))
		{	$condary[$field]= str_replace("'","\\'",trim($condary[$field]));
			if($length>=255)
			{	$condary[$field]= preg_replace("#\s\s+#"," ",$condary[$field]);
			}
			//Do not save pue-whitespaces input, or auto-incrment IDs to DB
			if(!preg_match("#\S#",$condary[$field])&& empty($_POST['__save']))
			{	unset($condary[$field]);
			}if(!empty($descmap[$field]['IDENTITY'])&& !empty($_POST['__save']))
			{	unset($condary[$field]);
			}
		}
		/*file upload handling - insert to database as BLOB.
			Format: filename+"|"+hex of data.
		*/
		if(!empty($_FILES[$field]['name']))
		{	if(!is_array($_FILES[$field]['name']))
			{	$_FILES[$field]['name']= array($_FILES[$field]['name']);
				$_FILES[$field]['tmp_name']= array($_FILES[$field]['tmp_name']);
				if(!empty($_FILES[$field]['type']))
				{	$_FILES[$field]['type']= array($_FILES[$field]['type']);
				}
			}foreach(array_keys($_FILES[$field]['name']) as $j)
			{	$fname= basename($_FILES[$field]['name'][$j]);
				$ftmp= $_FILES[$field]['tmp_name'][$j];
				$ftype= empty($_FILES[$field]['type'][$j])?'':$_FILES[$field]['type'][$j];
				if(empty($fname)|| empty($ftmp))
				{	continue;
				}$fh= fopen($ftmp,'r');
				$data='';
				while(!empty($fh)&& !feof($fh))
				{	$data.= fgets($fh);
				}fclose($fh);
				if(empty($data))
				{	continue;
				}
				$datahex= bin2hex($data);
				if(empty($condary[$field]))
				{	$condary[$field]= '';
				}
				$condary[$field].= $fname."|".$datahex;
			}
		}
		if(preg_match('#^bit|^tinyint#i',$fieldtype))
		{	if(empty($fieldbit))
			{	$fieldbit= array();
			}
		}//check mandatory fields on submission
		if(!empty($field_mand)&& preg_match("#^({$field_mand})$#i",$field)&& empty($condary[$field]))
		{	$field_mandmissing.= $field.", ";
		}
		$field_summary[$field]=1;
		//auto-pouplate time field
		if(preg_match('#datetime#i',$fieldtype)&& preg_match("#^creat#i",$field))
		{	if(!empty($_POST['__save'])&& empty($condary[$field]))
			{	$str= new DateTime();
				$condary[$field]= $str->format("Y-m-d H:i:s");
				debugprint("Auto time fill in: ".$field." ".$condary[$field]);
			}
		}if(preg_match('#datetime#i',$fieldtype))
		{	$fielddate[$field]= 1;
			if(array_key_exists($field,$condary)&& !preg_match("#\d#",$condary[$field]))
			{	unset($condary[$field]);
			}
		}//search time filter
		if(preg_match('#datetime#i',$fieldtype)&& !empty($_POST[$field.'>']))
		{	$condary[$field.'>']= trim($_POST[$field.'>']);
		}if(preg_match('#datetime#i',$fieldtype)&& !empty($_POST[$field.'<']))
		{	$condary[$field.'<']= trim($_POST[$field.'<']);
		}
	}$field_summary= implode(',',array_keys($field_summary));
	
	//get maximum auto-incrment ID field
	if(!empty($maxid))
	{	$result= current(sqlrunquery("select max(".$fieldid.") as MAXID from ".$table_data));
		if(!empty($result['MAXID']))
		{	$maxid= $result['MAXID'];
		}
	}

	echo printhtmlheader(strip_tags($webtitle),"minipo_csslib.css");
	minipo_printtopinfo();

	//add new entry
	if(!empty($_POST['__save'])&& !array_key_exists('__updateid',$_POST)&& !empty($field_mandmissing))
	{	echo "<div class='error'>Following mandatory fields are missing: ".preg_replace("#,$#",".",$field_mandmissing)."</div>".EOL_CHR;
		unset($_POST['__save']);
	}if(!empty($_POST['__save'])&& !array_key_exists('__updateid',$_POST))
	{	unset($_POST['__save']);
		$updatesql= "insert into {$table_data} (".implode(',',array_keys($condary)).") values ('".implode("','",$condary)."');";
		$result= current(sqlrunquery($updatesql));
		if(!empty($result['ERROR']))
		{	echo "<div class='error'>ERROR add new {$objname}: {$result['ERROR']}</div>".EOL_CHR;
		}else
		{	echo "Successfully added new {$objname} <br>".EOL_CHR;			
			if(!empty($condary[$fieldid]))
			{	$_POST['__updateid']= $condary[$fieldid];
			}else
			{	$_POST['__updateid']= $maxid+1;
			}
		}
	}

	//save update on existing entry
	if(!empty($_POST['__save'])&& array_key_exists('__updateid',$_POST))
	{	unset($_POST['__save']);
		$result= array();
		$searchsql= "select {$field_summary} from {$table_data} where {$fieldid}='{$_POST['__updateid']}'";
		$oldentry= current(sqlrunquery($searchsql));
		if(count($oldentry)<=0)
		{	$fieldlabel= $fieldid;
			$oldentry['ERROR']= "No Record with ".$fieldlabel."='{$_POST['__updateid']}' found in database";
		}if(!empty($oldentry['ERROR']))
		{	$result['ERROR']= $oldentry['ERROR'];
		}else
		{	//only update changed fields
			$condary2= $condary;
			foreach(array_keys($condary2) as $field)
			{	if(array_key_exists($field,$oldentry)&& $oldentry[$field]==$condary2[$field])
				{	unset($condary2[$field]);
				}
			}$condstr= implode(", ",array_map("aryjoinupdatesql",array_keys($condary2),$condary2));
			if(!empty($condstr))
			{	$updatesql= "update ".$table_data." set ".$condstr." where ".$fieldid."='".$_POST['__updateid']."' ";
				$result= current(sqlrunquery($updatesql));
			}
		}if(empty($result['ERROR']))
		{	echo "Successfully updated {$objname} #{$_POST['__updateid']}<br>".EOL_CHR;
		}else
		{	echo "<div class='error'>ERROR updating {$objname} #{$_POST['__updateid']}: {$result['ERROR']}</div>".EOL_CHR;
		}echo "<br>".EOL_CHR;
	}

	//delete entry
	if(array_key_exists('__updateid',$_POST)&& !empty($_POST['__delete']))
	{	$updatesql= "delete from {$table_data} where {$fieldid}='{$_POST['__updateid']}' ";
		$result= current(sqlrunquery($updatesql));
		if(empty($result['ERROR']))
		{	echo "<br><div class='successmsg'>Successfully Deleted entry with {$fieldid} #{$_POST['__updateid']}</div><br>".EOL_CHR;
			//subtable delete as well
			if(!empty($table_item))
			{	$updatesql= "delete from {$table_item} where {$fieldid}='{$_POST['__updateid']}' ";
				$result= current(sqlrunquery($updatesql));
			}
			$condary= array();
			echo "Succesfully Deleted {$objname} with {$fieldid}: ".$_POST['__updateid']."<br>".EOL_CHR;
			echo "<a href='minipo_Summary.php'>Click here to return to Listing</a><br>".EOL_CHR;
			printhtmlbottom();
			die;
			
		}else
		{	echo "<div class='error'>Database Error on deleteing {$fieldid} #{$_POST['__updateid']}: ".$result['ERROR']."</div>".EOL_CHR;
		}
	}
	
	
	echo minipo_menu(!empty($table_user));
	//input form for adding new or updating existing entry
	if(array_key_exists('__updateid',$_POST))
	{	$oldentry= current(sqlrunquery("select {$field_summary} from {$table_data} where {$fieldid}='{$_POST['__updateid']}'"));
	}
	echo "<form enctype='multipart/form-data' method='post'>".EOL_CHR;
	echo "<input type='hidden' name='table_data' value='{$table_data}'>".EOL_CHR;
	if(array_key_exists('__updateid',$_POST))
	{	echo "<input type='hidden' name='__updateid' value='".htmlasciiencode($_POST['__updateid'])."'>".EOL_CHR;
		echo "Updating existing {$objname}: {$fieldid} = {$_POST['__updateid']}: <br>".EOL_CHR;
	}else
	{	echo "Adding new {$objname}:<br>".EOL_CHR;
	}if(!empty($field_mand))
	{	echo "<div class='notetxt'>Mandatory fields are denoted by: <span class='mandicon'>*</span></div>".EOL_CHR;
	}

	foreach(array_keys($descmap) as $field)
	{	$fieldtype= $descmap[$field]['TYPE'];
		$defaultval= $descmap[$field]['DEFAULT'];
		$value= $defaultval;
		if(!empty($oldentry)&& array_key_exists($field,$oldentry))
		{	$value= $oldentry[$field];
		}$length= 50;
		if(!empty($descmap[$field]['MAXLEN'])&&
			!preg_match("#\D#",$descmap[$field]['MAXLEN']))
		{	$length= $descmap[$field]['MAXLEN'];
		}$fieldlabel= $field.":";
		if(!empty($field_mand)&& preg_match("#^({$field_mand})$#i",$field))
		{	$fieldlabel.= "<span class='mandicon'>*</span>";
		}echo "<span class='lbl1'>".$fieldlabel." &nbsp; </span>".EOL_CHR;
		//Auto incrment ID field - readonly		
		if(!empty($descmap[$field]['IDENTITY']))
		{	echo ($maxid+1)."<span class='notetxt'>(Auto-populated)</span>".EOL_CHR;
		}//non-editable field
		else if(!empty($field_noedit)&& preg_match("#^({$field_noedit})$#i",$field))
		{	echo $value;
			echo "<input type='hidden' name='".$field."' value='".htmlasciiencode($value)."'>".EOL_CHR;
		}//dropdown field
		else if(!empty($fielddropdown[$field]))
		{	echo "<select name='{$field}'".EOL_CHR;
			if($length>100)
			{	echo " style='width:55%;' onfocus='this.style.width=\"100%\";this.focus();'
					onchange='this.style.width=\"55%\";' onblur='this.onchange();'";
			}echo ">".EOL_CHR;
			if(!empty($field_custom[$field]))
			{	echo "<option value=''>----- Not in List (May Enter Custom Value)----</option>".EOL_CHR;
			}
			$tmpary= $fielddropdown[$field];
			$valuecustom= $value;
			//dropdown selection is specified from database query
			if(!is_array($tmpary)&& preg_match("#^\s*\[SQL\]\s*#",$tmpary))
			{	$searchsql= preg_replace("#^\s*\[SQL\]\s*#","",$tmpary);
				$tmpary= array();
				$resultitem= sqlrunquery($searchsql);
				foreach(array_keys($resultitem) as $j)
				{	$tmpary[]= $resultitem[$j];
				}
			}
			foreach($tmpary as $entry)
			{	$val1= $entry;
				$str= $entry;
				if(is_array($entry))
				{	$val1= current($entry);
					if(count($entry)<=1)
					{	$str= $val1;
					}else
					{	$str= implode("|",array_slice($entry,1));
					}
				}$val1= htmlasciiencode(trim($val1));
				if($val1=='')
				{	continue;
				}if($value==$val1)
				{	$valuecustom= '';
				}echo "<option value='".$val1."'".($value==$val1?" selected='selected'":'').">".$str."</option>".EOL_CHR;
			}echo "</select>".EOL_CHR;

			if(!empty($field_custom[$field]))
			{	echo " OR custom value: <input type='text' name='{$field}_custom' value='".$valuecustom."'>".EOL_CHR;
			}
		}else if(!empty($fieldradio[$field]))
		{	if(empty($value))
			{	$value= current($fieldradio[$field]);
			}foreach($fieldradio[$field] as $str)
			{	$str= htmlasciiencode(trim($str));
				if(strlen($str)<=0)
				{	continue;
				}echo "<input type='radio' name='{$field}' value='".$str."' ".($value==$str?" checked='checked'":'').">".$str." &nbsp; ".EOL_CHR;
			}
		}else if(preg_match("#datetime#i",$fieldtype))
		{	$timestr= ",\"t H:i\"";
			if(!empty($fielddateonly)&& preg_match("#^(".$fielddateonly.")$#",$field))
			{	$timestr= "";
				$value= preg_replace("#\d{1,2}:.*#",'',$value);
			}if(!preg_match("#[123456789]#",$value))
			{	$value='';
			}echo "<input type='text' id='{$field}' name='{$field}' value='".htmlasciiencode($value)."' size='20' onfocus='NewCssCal(this.id,\"yyyy-mm-dd\",\"arrow\"{$timestr});'>".EOL_CHR;
			echo "<img src='btn_cal.gif' class='button' onclick='NewCssCal(\"{$field}\",\"yyyy-mm-dd\",\"arrow\"{$timestr});' alt='PickDate' onerror='var newbtn= document.createElement(\"input\");newbtn.type=\"button\";newbtn.value=this.alt;this.parentNode.replaceChild(newbtn,this);'>".EOL_CHR;
		}else if(preg_match("#^bit|^tinyint#i",$fieldtype))
		{	echo "<input type='radio' name='{$field}' value='1'".(empty($value)?'':" checked='checked'")."> Yes  &nbsp; ".EOL_CHR;
			echo "<input type='radio' name='{$field}' value='0'".(empty($value)?" checked='checked'":'')."> No".EOL_CHR;
		}else if(preg_match("#int$#i",$fieldtype))
		{	echo "<input type='text' name='{$field}' value='".htmlasciiencode($value).
				"' size='14' onkeyup='jsnumericreplace(this,event.keyCode);'>".EOL_CHR;
			echo "<span class='notetxt'>(Numbers only)</span>".EOL_CHR;
		}else if(preg_match("#^decimal#i",$fieldtype))
		{	echo "<input type='text' name='{$field}' value='".htmlasciiencode($value).
				"' size='14' onkeyup='jsnumericreplace(this,event.keyCode,true);'>".EOL_CHR;
			echo " <span class='notetxt'>(Numbers with decimals only)</span>".EOL_CHR;
		}//assumbe blob are for file uploads
		else if(preg_match("#blob$#i",$fieldtype))
		{	echo "<input type='file' name='{$field}[]' value='".htmlasciiencode($value)."' size='40'>".EOL_CHR;
			echo " &nbsp; <input type='button' value='More Uploads' onclick='jsaddupload1(this.parentNode,\"{$field}[]\")'><br>".EOL_CHR;
		}else if(preg_match("#^text#i",$fieldtype)|| $length>=100)
		{	if($length<100||$length>350)
			{	$length=350;
			}echo "<textarea name='{$field}' rows='".(ceil($length/75))."' maxlength='{$length}' class='textarea1'>{$value}</textarea>".EOL_CHR;
		}else
		{	echo "<input type='text' name='{$field}' maxlength='{$length}' size='{$length}' value='".htmlasciiencode($value)."'>".EOL_CHR;
		}echo "<br>".EOL_CHR;
	}
	//table_item subtable display
	if(!empty($table_item)&& array_key_exists('__updateid',$_POST))
	{	$searchsqlitem= "select * from {$table_item} where {$fieldid}='{$_POST['__updateid']}';";
		$resultitem= sqlrunquery($searchsqlitem);
		echo "<br><br>List of sub-items:<br>".dbtable2htmltable($resultitem).'<br>';
	}

	//Update & Delete entry buttons
	echo "<br>";
	if(empty($field_noedit)|| $field_noedit!='.*')
	{	echo "<input type='submit' class='btnleftspace' name='__save' value='".(!array_key_exists('__updateid',$_POST)?'Add ':'Update ').$objname."'>".EOL_CHR;
	}if(!empty($flagdelbtn)&& ($flagdelbtn===true||preg_match('#'.$flagdelbtn.'#',$table_data)))
	{	echo "<input type='submit' class='btnleftspace' name='__delete' value='Delete' onclick='return confirm(\"Are you sure you want to delete this entry?\");'>".EOL_CHR;
	}echo "</form>".EOL_CHR;
	
	printhtmlbottom();

?>