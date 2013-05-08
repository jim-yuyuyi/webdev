#MiniPO - Mini PHP Ordering Interface
Flexible and minimal Ordering GUI



## What is MiniPO?

Deisgned to be easily configurable, even by non-programmers.
Heavily intergrated to MySQL database for data storage.
Inspired by MyPhpAdmin; this is much simplified to become 
easy to to use for handling customer orders.

##Setup instructions
Just edit minipo_config.php. See the allowable "Configuration Variables" section below
for variables to be used inside the config file/

##Pages
Login page
Menu page
New Entry page
Search & Listing page 
Edit page
field_custom

##Configuration Variables

$table_user= '';			//db table for user logins. Otherwise is in text config file.
$table_data= '';			//db table for request data
$table_item= '';			//db table for request sub-item under each table_data entry
$fieldcookie= '';
$field_mand= '';					//mandatory fields/columns for new entry
$fieldsearch= '';
$field_noedit= '';				//readonly field *New* entries
$field_noupdate= '';			//readonly fields for updatings *Existing* entries
$field_summary_hide= '';		//fields to hide from summary list
$fieldid= '';
$field_user= '';
$field_pass= '';
$fieldorderby= '';
$fielddropdown= array();	//dropdown fields
$field_custom= array();		//dropdown fields that allow custom value entry
$fielddateonly= '';			//date only fields without time fields
$fieldupload= '';
$flagemailoptional= '';		//checkbox for user to decide whether or not to send email Superadmins in list.
$flagstatusbutton= '';
$flagaddbtn= '';			//indicate admin can add new entry
$flagdelbtn= '';			//indicate admin can delete entry
$flagnoback= '';
$flagnoscrollheader='';
$uploadloc= '';
$loginary= array();
$logincheck= false;			//force login for non-admin page as well
$dupecheck= '';
$webtitle=  '';
$objname= '';
$msg_newentry= '';
$msg_login= '';
$msg_submit= '';
$msg_email='';
$email_from= '';
$email_alert= '';
$email_adminalert= '';
$email_subject= '';
$email_cc= '';
$email_bcc= '';
$jscheckary= array();
$default_mode= '';