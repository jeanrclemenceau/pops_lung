<?php
/*Filename: user_settings_process.php
 *Author: Jean Clemenceau
 *Date Created: 10/04/2016
 *Processes the "Contact Us" form to send the message to the appropriate recepient
*/

require_once('pops_dbi.php');
$returnValues = Array(); //values for the jquery request

// Verify that user is logged in
$notice = require_login('user_settings.php');
if (isset($notice)){
  $returnValues['modal_title'] = "ERROR: Account Not Found";
  $returnValues['modal_content'] = "<div class='notificationAlert'><p>$notice</p><div>";
  $returnValues['error'] = TRUE;
  echo json_encode($returnValues);
  exit;
}
// Validate the form token
$valid_token = false;
if( isset($_POST['token']) && isset($_POST['formName']) && $_POST['formName']=='user_settings_form' ){
  $valid_token = validate_form_token($_POST['formName'],$_POST['token']);
}
if(!$valid_token){
  $returnValues['modal_title'] = "ERROR: Form Expired";
  $returnValues['modal_content'] = "<div class='notificationAlert'><p>This form submission has expired or comes from an unrecognized referer.</p><p>Refresh the page to try again</p><div>";
  $returnValues['error'] = TRUE;
  echo json_encode($returnValues);
  exit;
}

//Authenticate the user making changes
$authUSR = authenticate($_SESSION['email'],$_POST['password'], $errors);
if(!isset($authUSR)){
  $errorMsg = implode('<br>',array_reverse($errors));
  $returnValues['modal_title'] = "Update Failed: User Not Authenticated";
  $returnValues['modal_content'] = "<div class='notificationAlert'><p>$errorMsg</p><div>";
  $returnValues['newToken'] = request_form_token($_POST['formName']);
  $returnValues['error'] = TRUE;
  echo json_encode($returnValues);
  exit;
}

// Read form data
$fname =(isset($_POST['first_name'])&&$_POST['first_name']!='')? sanitize($_POST['first_name']):'';
$lname =(isset($_POST['last_name'])&&$_POST['last_name']!='')? sanitize($_POST['last_name']):'';
$email =(isset($_POST['email'])&&$_POST['email']!='')? sanitize($_POST['email']):'';
$affil =(isset($_POST['affiliation'])&&$_POST['affiliation']!='')? sanitize($_POST['affiliation']):'';
$npwd =(isset($_POST['new_password'])&&$_POST['new_password']!='')? sanitize($_POST['new_password']):'';
$cpwd =(isset($_POST['cnf_password'])&&$_POST['cnf_password']!='')? sanitize($_POST['cnf_password']):'';

//Validate form
$valid = true;
$valid = $valid &&(''!=$fname) &&(''!=$lname) &&(''!=$email);

if(!$valid){
  $returnValues['modal_title'] = "Update Failed: Missing Fields";
  $returnValues['modal_content'] = "<div class='notificationAlert'><p>Some of the required values are missing.</p><p>Please fill up First Name, Last Name, and E-mail.</p><div>";
}
else if($npwd != '' && $cpwd == ''){
  $returnValues['modal_title'] = "Update Failed: Missing Confirmation";
  $returnValues['modal_content'] = "<div class='notificationAlert'><p>You're trying to change your password. You must confirm your new password.</p><div>";
  $valid = false;
}
else if($npwd != $cpwd){
  $returnValues['modal_title'] = "Update Failed: Passwords do NOT Match";
  $returnValues['modal_content'] = "<div class='notificationAlert'><p>You're trying to change your password, but the confirmation does not match the new password.</p><div>";
  $valid = false;
}

if(!$valid){
  $returnValues['newToken'] = request_form_token($_POST['formName']);
  echo json_encode($returnValues);
  exit;
}

//Get user values
$udata = NULL;
$uid = isset($_SESSION['uid'])?$_SESSION['uid']:'';
$udata_query = "SELECT email, first_name,last_name, affiliation FROM members WHERE member_id = ?";
$udata_res= query_db($udata_query,'i',$uid,'User Data Query');
if($udata_res->num_rows == 1 ){
  $udata = $udata_res->fetch_object();
}else{
  $returnValues['modal_title'] = "ERROR: User Not Found";
  $returnValues['modal_content'] = "<div class='notificationAlert'><p>Your user data was not found. Please try logging out and logging back in.</p><div>";
  $returnValues['error'] = TRUE;
  echo json_encode($returnValues);
  exit;
}

//Track changes
$changed_item_names = Array();
$changed_item_clauses = Array();
$changed_item_types = Array();

if($udata->first_name != $fname){
  $changed_item_names[] = 'First Name';
  $changed_item_clauses[] = 'first_name = ?';
  $changed_item_values[] = $fname;
  $changed_item_types[] = 's';
}
if($udata->last_name != $lname){
  $changed_item_names[] = 'Last Name';
  $changed_item_clauses[] = 'last_name = ?';
  $changed_item_values[] = $lname;
  $changed_item_types[] = 's';
}
if($udata->email != $email){
  if(check_valid_email($email)){
    $changed_item_names[] = 'E-mail';
    $changed_item_clauses[] = 'email = ?';
    $changed_item_values[] = $email;
    $changed_item_types[] = 's';
  }else{
    $returnValues['modal_title'] = "ERROR: Invalid E-mail";
    $returnValues['modal_content'] = "<div class='notificationAlert'><p>Please provide a valid E-mail address.</p><div>";
    $returnValues['error'] = TRUE;
    echo json_encode($returnValues);
    exit;
  }
}
if($udata->affiliation != $affil){
  $changed_item_names[] = 'Affiliation';
  $changed_item_clauses[] = 'affiliation = ?';
  $changed_item_values[] = $affil;
  $changed_item_types[] = 's';
}

//process new password
if($npwd != '' && $cpwd != '' && $npwd==$cpwd){
  $salt= generate_salt();
  $pwd=dbi_hash(PEPPER . dbi_hash($salt.$npwd) );

  $changed_item_names[] = 'Password';
  $changed_item_clauses[] = 'password = ?';
  $changed_item_values[] = $pwd;
  $changed_item_types[] = 's';
  $changed_item_clauses[] = 'salt = ?';
  $changed_item_values[] = $salt;
  $changed_item_types[] = 's';
}

//Execute query
$dbi = connect_db(DB_USR_RW,DB_PWD_RW);
$updates = implode(', ',$changed_item_clauses);
$changed_item_values[] = $uid;
$changed_item_types[] = 'i';
$update_query = "UPDATE members SET $updates WHERE member_id = ?";
$lines_changed = query_db($update_query,implode('',$changed_item_types),$changed_item_values,"Update User Query",$dbi);// TODO Check lines changed

error_log($lines_changed);
//Send Message
if($lines_changed == 1){
  //Update session data
  $_SESSION['uname']= $fname.' '.substr($lname,0,1);
  $_SESSION['email']= $email;
  $_SESSION['affiliaton']= $affil;

  //Notify User by modal
  $changes = implode('<br>',$changed_item_names);
  $itemTitle=(count($changed_item_names) > 1)?'settings have ':'setting has';
  $returnValues['modal_title'] = "Update Successful";
  $returnValues['modal_content'] = "<div class='notificationAlert'><p>Your following $itemTitle been updated:<br>$changes</p><div>";

  //Notify User by Email
  $subject = "POPS-Lung: User settings successfully updated";
  $message = "Your new user settings have been updated. The following fields have been changed:<br>$changes<br><br>If you did not request these changes, please submit a ticket <a href='http://".HTTP_HOST.POPS_HOME."pops_contact.php?s=5'>HERE</a></p><div></p><div>";
  $messageSent = pops_email($udata->email,$subject,$message);

  //Notify new email
  if($udata->email != $email){
    $subject = "POPS-Lung: New registered E-mail";
    $message = "Your new user E-mail address has been updated to this one.<br><br>If you did not request these changes, please submit a ticket <a href='http://".HTTP_HOST.POPS_HOME."pops_contact.php?s=5'>HERE</a></p><div></p><div>";
    $messageSent = pops_email($email,$subject,$message);
  }
}else{
  $returnValues['modal_title'] = "ERROR: Updates Failed";
  $returnValues['modal_content'] = "<div class='notificationAlert'><p>An error ocurred while updating your settings.</p><p>Please reload the page or try again later.If the problem persists, please submit a ticket <a href='pops_contact.php?s=4'>HERE</a></p><div></p><div>";
   if($lines_changed > 1){
     $errContent=Array("Fields were changed for more than one user: $lines_changed");
     $errContent[] = "Fields to change: ".implode(', ',$changed_item_names);
     $errContent[] = "Values: ".implode(', ',$changed_item_values);
     record_error($errContent,'USER_SETTINGS');
   }
}
$returnValues['newToken'] = request_form_token($_POST['formName']);
echo json_encode($returnValues);
exit;
?>
