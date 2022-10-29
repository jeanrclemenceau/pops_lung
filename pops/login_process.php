<?php
/*Filename: login_form.php
 *Author: Jean Clemenceau
 *Date Created: 3/14/2016
 *Validates username and password,
 *sets up the session and activates account.
*/

require_once('pops_conf.php');
require_once('pops_dbi.php');

check_login();

$update='';
$notification='';
$user_authenticated = false;
$errors = Array();
$user = NULL;
$returnData = Array();

$email =(isset($_POST['email']) && $_POST['email']!='')? sanitize($_POST['email']) : '';
$pw = (isset($_POST['password']) && $_POST['password'] != '')? sanitize($_POST['password']) : '';
$url = (isset($_POST['rd']) && $_POST['rd'] != '')? sanitize($_POST['rd']) : 'pops.php';
$confCode = (isset($_POST['cc']) && $_POST['cc'] != '')? sanitize($_POST['cc']) : '';
$token = (isset($_POST['token']) && $_POST['token']!='')? sanitize($_POST['token']) :'';


//Validate CSRF token
if( validate_form_token('login_form',$token) ){
  //Validate authentication
  $user = authenticate($email,$pw,$errors,$confCode);
  $user_authenticated = isset($user) ? true : false;
}else{
  $errors[] = "This form submission has expired or comes from an unrecognized referer.";
}

if($user_authenticated){
  //Reset session
  $_SESSION['uid']= $user['uid'];
  $_SESSION['uname']= $user['uname'];
  $_SESSION['email']= $user['email'];
  $_SESSION['access']= $user['access'];
  $_SESSION['affiliaton']= $user['affiliation'];

  // Set update query
  $update = "UPDATE members SET login_attempts= 0,last_fail_login_attempt=NOW() WHERE email LIKE ?";

  // Redirect page
  $returnData['auth'] = $user_authenticated;
  $returnData['redirect'] = $url;
}else{
  $returnData['auth'] = $user_authenticated;
  $returnData['redirect'] = '';
  $returnData['errors'] = implode($errors,"\n");
}

echo json_encode($returnData);
?>
