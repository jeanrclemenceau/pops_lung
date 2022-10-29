<?php
/*Filename: pops_contact_process.php
 *Author: Jean Clemenceau
 *Date Created: 10/04/2016
 *Processes the "Contact Us" form to send the message to the appropriate recepient
*/

require_once('pops_conf.php');
check_login();

$returnValues = Array(); //values for the jquery request

// Validate the form token
$valid_token = false;
if( isset($_POST['token']) && isset($_POST['formName']) && $_POST['formName']=='contact_form' ){
  $valid_token = validate_form_token($_POST['formName'],$_POST['token']);
}
if(!$valid_token){
  $returnValues['modal_title'] = "ERROR: Expired Token";
  $returnValues['modal_content'] = "<div class='notificationAlert'><p>This form submission expired or came from an unrecognized referrer.</p><p>Refresh the page to try again</p><div>";
  echo json_encode($returnValues);
  error_log('Contact Form: Invalid Token');
  exit;
}

// Read form data
$name =(isset($_POST['name'])&&$_POST['name']!='')? sanitize($_POST['name']):'';
$mail =(isset($_POST['email'])&&$_POST['email']!='')? sanitize($_POST['email']):'';
$affi =(isset($_POST['affiliation'])&&$_POST['affiliation']!='')? sanitize($_POST['affiliation']):'';
$subj =(isset($_POST['subject'])&&$_POST['subject']!='')? sanitize($_POST['subject']):'';
$osub =(isset($_POST['subject_other'])&&$_POST['subject_other']!='')? sanitize($_POST['subject_other']):'';
$text =(isset($_POST['message'])&&$_POST['message']!='')? sanitize($_POST['message']):'';

//Validate form
$valid = true;
$valid = $valid &&(''!=$name) &&(''!=$mail) &&(''!=$text) &&(''!=$subj);
if($subj == 'Other'){
  $valid = $valid &&(''!=$osub);
}
if(!$valid){
  $returnValues['modal_title'] = "ERROR: Missing Fields";
  $returnValues['modal_content'] = "<div class='notificationAlert'><p>Some of the required values are missing.</p><p>Please fill up Name, E-mail, Subject, and Message.</p><div>";
  $returnValues['newToken'] = request_form_token($_POST['formName']);
  echo json_encode($returnValues);
  error_log('Contact Form: Missing Fields');
  exit;
}

//Establisch what subject goes to who
$destinationMap = Array(
'Collaboration_Request'=>POPS_ADMIN,
'Reagent_Request'=>POPS_ADMIN,
'Dataset_Request'=>POPS_ADMIN,
'Website_Error_Report'=>POPS_ADMIN,
'Report_Unauthorised_Account_Activity'=>POPS_ADMIN,
'Other'=>POPS_ADMIN
);

//Setup fields
$subject = ($subj!='Other')?str_replace('_',' ',$subj):$osub;
$subject = "POPS-LUNG SYSTEM MESAGE: ". $subject;
$to = $destinationMap[$subj];
$affiliation = ($affi!='')?" ($affi)":'';
$message = "You have a message from $name$affiliation: <p>\n$text</p>";

//Send Message
$messageSent = pops_email($to,$subject,$message,$mail,$name);
if($messageSent){
  $returnValues['modal_title'] = "Message Sent!";
  $returnValues['modal_content'] = "<div class='notificationAlert'><p>Your message has been sent to the corresponding investigator. We will contact you as soon as we're able to.</p><p>Thank you for contacting the POPS team!</p><div>";
}else{
  $returnValues['modal_title'] = "ERROR: Message NOT sent";
  $returnValues['modal_content'] = "<div class='notificationAlert'><p>An error ocurred while sending your message.</p><p>Please reload the page or try again later.</p><div>";
}
$returnValues['newToken'] = request_form_token($_POST['formName']);
echo json_encode($returnValues);
error_log('Contact Form: All Pass'); //TODO remove

exit;
?>
