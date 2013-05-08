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

	echo printhtmlheader(strip_tags($webtitle),"minipo_csslib.css");
	minipo_printtopinfo();		


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
		//ID field - use the first column if not specified
		if(empty($fieldid)|| !empty($descmap[$field]['IDENTITY']))
		{	$fieldid= $field;
		}$length=50;
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
			if(!preg_match("#\S#",$condary[$field])&& empty($_POST['.EOL_CHR;']))
			{	unset($condary[$field]);
			}if(!empty($descmap[$field]['IDENTITY'])&& !empty($_POST['.EOL_CHR;']))
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
		{	if(!empty($_POST['.EOL_CHR;'])&& empty($condary[$field]))
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

/* ------ start admin portion ------ */
	echo minipo_menu(!empty($table_user));

	//add new entry
	if(!empty($_POST['.EOL_CHR;'])&& !array_key_exists('__updateid',$_POST)&& !empty($field_mandmissing))
	{	echo "<div class='error'>Following mandatory fields are missing: ".preg_replace("#,$#",".",$field_mandmissing)."</div>";
		unset($_POST['.EOL_CHR;']);
		$_POST['__add']= true;
	}if(!empty($_POST['.EOL_CHR;'])&& !array_key_exists('__updateid',$_POST))
	{	unset($_POST['.EOL_CHR;']);
		$_POST['__add']= true;
		$updatesql= "insert into {$table_data} (".implode(',',array_keys($condary)).") values ('".implode("','",$condary)."');";
		$result= current(sqlrunquery($updatesql));
		if(!empty($result['ERROR']))
		{	echo "<div class='error'>ERROR add new {$objname}: {$result['ERROR']}</div>";
		}else
		{	echo "Successfully added new {$objname} <br>";
			$_POST['__updateid']= $result[$fieldid];
		}
	}

	//save update on existing entry
	if(!empty($_POST['.EOL_CHR;'])&& array_key_exists('__updateid',$_POST))
	{	unset($_POST['.EOL_CHR;']);
		$_POST['__add']= true;
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
		{	echo "Successfully updated {$objname} #{$_POST['__updateid']}<br>";
		}else
		{	echo "<div class='error'>ERROR updating {$objname} #{$_POST['__updateid']}: {$result['ERROR']}</div>";
		}echo "<br>";
	}

	//delete entry
	if(array_key_exists('__updateid',$_POST)&& !empty($_POST['__delete']))
	{	$updatesql= "delete from {$table_data} where {$fieldid}='{$_POST['__updateid']}' ";
		$result= current(sqlrunquery($updatesql));
		if(empty($result['ERROR']))
		{	echo "<br><div class='successmsg'>Successfully Deleted entry with {$fieldid} #{$_POST['__updateid']}</div><br>";
			//subtable delete as well
			if(!empty($table_item))
			{	$updatesql= "delete from {$table_item} where {$fieldid}='{$_POST['__updateid']}' ";
				$result= current(sqlrunquery($updatesql));
			}
			$condary= array();
		}else
		{	echo "<div class='error'>Database Error on deleteing {$fieldid} #{$_POST['__updateid']}: ".$result['ERROR']."</div>";
		}
	}


/* ----------------- search page / record summary ---------------------- */	
		if(array_key_exists('__updateid',$_POST))
		{	$condary= array($fieldid=>$_POST['__updateid']);
			unset($field_summary_hide);
		}$htmlsearchfilter='';
		if(!empty($fieldsearch))
		{	if(is_array($fieldsearch))
			{	$fieldsearch= implode("|",$fieldsearch);
			}$fieldsearch= str_replace(",","|",trim($fieldsearch));
		}//show search filtering options
		foreach(array_keys($descmap) as $field)
		{	$fieldtype= $descmap[$field]['TYPE'];			
			$value='';
			if(array_key_exists($field,$condary))
			{	$value= $condary[$field];
			}//non-searchable fields are filtered out
			if(!empty($fieldsearch)&& !preg_match("#^({$fieldsearch})$#i",$field))
			{	continue;
			}
			$fieldlabel= $field;
			$htmlsearchfilter.= "<span class='lbl1'>{$fieldlabel}:</span>";
			//dropdown field
			if(!empty($fielddropdown[$field]))
			{	$tmpary= $fielddropdown[$field];
				if(!is_array($tmpary)&& preg_match("#^\s*\[SQL\]\s*#",$tmpary))
				{	$searchsql= preg_replace("#^\s*\[SQL\]\s*#","",$tmpary);
					$tmpary= array();
					$resultitem= sqlrunquery($searchsql);
					foreach(array_keys($resultitem) as $j)
					{	$tmpary[]= $resultitem[$j];
					}
				}
				$htmlsearchfilter.= "<select name='{$field}'><option value=''>--Any--</option>";
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
					}$htmlsearchfilter.=  "<option value='".$val1."'".($value==$val1?" selected='selected'":'').">".$str."</option>";
				}
				$htmlsearchfilter.= "</select>";
			}else if(!empty($fieldradio[$field]))
			{	
				$htmlsearchfilter.= "<input type='radio' name='{$field}' value='' ".(empty($value)?" checked='checked'":'').">[ Any ] &nbsp; ";
				foreach($fieldradio[$field] as $str)
				{	$str= trim($str);
					if($str=='')
					{	continue;}
					$htmlsearchfilter.= "<input type='radio' name='{$field}' value='".htmlasciiencode($str)."' ".($value==$str?" checked='checked'":'').">{$str} &nbsp; ";
				}
			}//date time range filter
			else if(preg_match("#datetime#i",$fieldtype))
			{	if(!empty($fielddateonly)&& preg_match("#^(".$fielddateonly.")$#",$field))
				{	$timestr= "";
				}else
				{	$timestr= ",\" H:i\"";
				}
				$htmlsearchfilter.= " From: <input type='text' id='{$field}>' name='{$field}>' size='20' value='".(empty($condary[$field.'>'])?'':$condary[$field.'>'])."' onfocus='NewCssCal(this.id,\"yyyy-mm-dd\",\"arrow\"{$timestr});'>";
				$htmlsearchfilter.= "  &nbsp; To <input type='text' id='{$field}<' name='{$field}<' size='20' value='".(empty($condary[$field.'<'])?'':$condary[$field.'>'])."' onfocus='NewCssCal(this.id,\"yyyy-mm-dd\",\"arrow\"{$timestr});'>";
			}
			else if(preg_match("#int$|^decimal#i",$fieldtype))			
			{	$decopt= '';
				if(preg_match("#^decimal#i",$fieldtype))
				{	$decopt= ",true";
				}$htmlsearchfilter.=  "<input type='text' name='{$field}' value='".htmlasciiencode($value)."' size='14'".(empty($descmap[$field]['IDENTITY'])?'':" onkeyup='jsnumericreplace(this,event.keyCode{{$decopt});'").">";
				$htmlsearchfilter.=  "<span class='notetxt'>(Numbers only)</span>".EOL_CHR;
				
			}else if(preg_match("#^bit|^tinyint#i",$fieldtype))
			{	$htmlsearchfilter.=  "<input type='checkbox' name='{$field}' value='1'".(empty($value)?'':" checked='checked'").">";
				
			}else
			{	$htmlsearchfilter.= "<input type='text' name='{$field}' value='".htmlasciiencode($value)."' >";				
			}$htmlsearchfilter.= "<br>".EOL_CHR;
		}//end search options
		echo "<form enctype='multipart/form-data' method='post' id='searchform'>";				
		echo "<input type='hidden' class='hidden' name='orderby' value='".(empty($_POST['orderby'])?'':$_POST['orderby'])."'>";
		if(!empty($htmlsearchfilter))
		{	echo "<span class='lbl1'>{$objname} search options</span><br>".EOL_CHR;
			echo "<br>";
			echo $htmlsearchfilter;			
			echo "<br><span class='lbl1'><input type='submit' value='Search'></span>".EOL_CHR;
			
		}echo "</form>";

		unset($htmlsearchfilter);		

		echo "<br><table border='1' id='searchlisttbl'><tr>";
		echo "<th> </th>";
		if(!empty($field_summary_hide))
		{	if(is_array($field_summary_hide))
			{	$field_summary_hide= implode("|",$field_summary_hide);
			}$field_summary_hide= str_replace(",","|",trim($field_summary_hide));
		}$field_summary= array();

		foreach(array_keys($descmap) as $field)
		{	$fieldlabel= $field;
			$fieldtype= $descmap[$field]['TYPE'];
			$length=50;
			if(!empty($descmap[$field]['MAXLEN'])&&
				!preg_match("#\D#",$descmap[$field]['MAXLEN']))
			{	$length= $descmap[$field]['MAXLEN'];
			}//skip hidden field from summary
			if(!empty($field_summary_hide)&& preg_match("#^({$field_summary_hide})$#i",$field))
			{	continue;
			}

			$field_summary[$field]= 50+$descmap[$field]['ORDER'];
			//orderby field buttons
			if(empty($_POST['__excel'])&& $length<255)
			{	$orderdescend= '';
				if(!empty($_POST['orderby'])&& preg_match("#^".$field."(| asc| desc)$#",$_POST['orderby']))
				{	$fieldorderby= $_POST['orderby'];
					if(preg_match("# desc$#i",$fieldorderby))
					{	$orderdescend= ' asc';
					}else
					{	$orderdescend= ' desc';
					}
				}echo "<th><input type='button' value='{$fieldlabel}' onclick='changeorderby(\"".$field.$orderdescend."\");' class='plain'>";
				if($orderdescend==' desc')
				{	echo "<span class='sortbtn'>/\</span>";
				}else if(!empty($orderdescend))
				{	echo "<span class='sortbtn'>\/</span>";
				}echo "</th>";
			}else
			{	echo "<th>".$fieldlabel."</th>";
			}
		}$condstr= '';
		if(count($condary)>0)
		{	$condstr.= " where ".implode(" and ",array_map("aryjoinquery",array_keys($condary),$condary));
		}if(!empty($fieldorderby))
		{	$condstr.= " order by ".$fieldorderby;
		}$field_summary= implode(',',array_keys($field_summary));
		$result= sqlrunquery("select {$field_summary} from {$table_data}{$condstr};");
		echo "</tr>";
		$idvalary= array();
		foreach(array_keys($result) as $i)
		{	if(!empty($result[$i][$fieldid]))
			{	$idvalary[$result[$i][$fieldid]]= 1;
			}echo "<tr>";
			//Update button on left-most column
			echo "<td><form enctype='multipart/form-data' method='post' action='minipo_Edit.php'>".
					"<input type='hidden' name='table_data' value='{$table_data}'>".
					"<input type='hidden' name='__updateid' value='".htmlasciiencode($result[$i][$fieldid])."'>".
					"<input type='submit' name='__add' value='Update'></form></td>";			
			foreach(array_keys($result[$i]) as $field)
			{	if(!empty($fieldbit[$field]))
				{	$result[$i][$field]= empty($result[$i][$field])?"No":"Yes";
				}
				//datetime - date only
				if(!empty($fielddateonly)&& preg_match("#^(".$fielddateonly.")$#",$field)&& !empty($result[$i][$field]))
				{	$result[$i][$field]= preg_replace("#\d{1,2}:.*#",'',$result[$i][$field]);
				}
				$result[$i][$field]= str_replace(array("&apos;","&#39;"),array("'","'"),$result[$i][$field]);
				//if it does not have html content, html encode its table cell display
				if(!preg_match("#<a [^>]*href|<tr>#i",$result[$i][$field]))
				{	$result[$i][$field]= htmlasciiencode($result[$i][$field]);
				}if(strlen($result[$i][$field])>=255)
				{	echo "<td><div class='tdoverflow'>".$result[$i][$field]."</div></td>";
				}else
				{	echo "<td>".$result[$i][$field]."</td>";
				}
			}
			if(!empty($flagdelbtn))
			{	echo "<td><form method='post' onsubmit='return confirm(\"Are you sure you want to delete entry #{$result[$i][$fieldid]}?\");'>".
					EOL_CHR."<input type='hidden' name='table_data' value='{$table_data}'>".
					"<input type='hidden' name='__updateid' value='".htmlasciiencode($result[$i][$fieldid])."'>".
					"<input type='submit' name='__delete' value='Delete'></form></td>".EOL_CHR;
			}echo "</tr>".EOL_CHR;
		}if(is_array($result)&& count($result)<=0)
		{	echo "<tr><td colspan='10' class='error'>No Records in database: ".$condstr."/td></tr>";
		}echo "</table>";


		//table_item subtable display: only if sub items not included in sub columns earlier
		if(!empty($table_item)&& empty($entryitemmap)&& empty($checkitemary))
		{	$searchsqlitem= "select * from {$table_item}";
			if(!empty($idvalary)&& count($idvalary)>0)
			{	$searchsqlitem.= " where ".$fieldid." in ('".implode("','",array_keys($idvalary))."')";
			}$resultitem= sqlrunquery($searchsqlitem);
			if(count($resultitem>0))
			{	echo "<br><br>List of sub-items:<br>".dbtable2htmltable($resultitem).'<br>';
			}
		}

	
	printhtmlbottom();
?>