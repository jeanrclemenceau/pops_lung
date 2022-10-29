<?php
/*Filename: register.php
 *Author: Jean R Clemenceau
 *Date Created: 03/15/2016
 *Precoesses new user registration to POPs called by the Registration Form.
*/

  require_once('pops_conf.php');
  require_once('pops_dbi.php');
  check_login();

  $dbh = connect_db(DB_USR_RW,DB_PWD_RW);
  mt_srand(time());
  $errors = Array();
  $errors_private = Array();
  $notification = '';

  //Get form input
  $newUser = Array();
  $newUser['Email']=(isset($_POST['reg_email']) && $_POST['reg_email']!='')? sanitize($_POST['reg_email']) : '';
  $newUser['First Name']=(isset($_POST['fname']) && $_POST['fname']!='')? sanitize($_POST['fname']) : '';
  $newUser['Last Name']=(isset($_POST['lname']) && $_POST['lname']!='')? sanitize($_POST['lname']) : '';
  $newUser['Affiliation']=(isset($_POST['affi']) && $_POST['affi']!='')? sanitize($_POST['affi']) : '';
  $newUser['Password'] =(isset($_POST['reg_password']) && $_POST['reg_password']!='')? sanitize($_POST['reg_password']) : '';
  $newUser['Password Conf']=(isset($_POST['confpw']) && $_POST['confpw']!='')? sanitize($_POST['confpw']) : '';
  $newUser['Token']= (isset($_POST['token']) && $_POST['token']!='')? sanitize($_POST['token']) :'';
  $newUser['salt']= generate_salt();
  $newUser['newPwd']=dbi_hash(PEPPER . dbi_hash($newUser['salt'] . $newUser['Password']) );
  $newUser['confCode'] = dbi_hash( $newUser['Email'] . PEPPER . mt_rand() );

  // Validate input (non-empty, passwords match,valid email, verify token)
  foreach($newUser as $field => $val){
    if($val =='' && $field != 'Affiliation'){
      $errors[] = "'$field' has no value. This field is required.";
    }
  }
  if( !check_valid_email($newUser['Email']) ){
    $errors[] = "Email does not have valid format";
    $errors_private[] = "Email does not have valid format: {$newUser['Email']}";
  }
  if($newUser['Password'] != $newUser['Password Conf']){
    $errors[] = "Supplied password and confirmation do not match.";
  }
  if( !validate_form_token("register_form", $newUser['Token']) ){
    $errors[] = "This form submission has expired or comes from an unrecognized referer.";
  }

  // Check exisence in database
  $find_query = "SELECT first_name,last_name,email FROM members WHERE email LIKE ?";
  $find_res = query_db($find_query, 's', $newUser['Email'], "Register-Find User Query",$dbh);

  if($find_res->num_rows < 1 && empty($errors) ){
    //Save new user
    $insertQuery = "INSERT INTO members (email,first_name,last_name,affiliation,password,salt,confirm_code) VALUES(?,?,?,?,?,?,?)";
    $valueArray = Array($newUser['Email'],$newUser['First Name'],$newUser['Last Name'],$newUser['Affiliation'],$newUser['newPwd'],$newUser['salt'],$newUser['confCode']);

    $insert_res = query_db($insertQuery,'sssssss',$valueArray, "Register Insert",$dbh);
    if( isset($dbh->insert_id) ){
      //Insert successful, notify new user
      $confLink = "http://".HTTP_HOST.POPS_HOME."index.php?cc=".$newUser['confCode'];
      $subject = "POPS-Lung: Registration Confirmation";
      $body = "Hello {$newUser['First Name']} {$newUser['Last Name']},<br>";
      $body.= "Thank you for registering for the Precision Oncology Probe Set database for lung cancer (POPS-Lung). Click or enter the following link in your browser to confirm your email and gain access to the service:<br><br>";
      $body.= "<a target='_BLANK' title='Click to confirm email' href='$confLink'>$confLink</a>";
      $body.= "<br><br>Thank you for your interest,<br>The POPS Team";

      pops_email($newUser['Email'],$subject,$body);

      $notification= "Thank you for registering to POPS-Lung.<br>Check your E-mail for a confirmation link. If you don't see it, check your junk folder.";
    }else{
      $errors_private[] = "User INSERT was attempted, but failed.\n".$dbh->error;
      $errors[]="User was not entered into database. Please try again or notify the website admin.";
    }
  }else if($find_res->num_rows >= 1 && empty($errors) ){
    $existingUser = $find_res->fetch_object();

    //Notify user
    $notification= "Thank you for registering to POPS-Lung.<br>Check your E-mail for a confirmation link. If you don't see it, check your junk folder.";

    //User Exists. Email notice to user
    $pwChange = "http://".HTTP_HOST.POPS_HOME."forgotpw.php";
    $subject = "POPS-Lung: Registration Confirmation";
    $body = "Hello {$existingUser->first_name} {$existingUser->last_name},<br>";
    $body.= "We have received an attempt to register to POPS-Lung with your email on ".date('M/d/Y \a\t H:i').". If you have forgotten your password or you did not make this request, we recommend resetting your password using this link:<br><br>";
    $body.= "<a target='_BLANK' title='Click change password' href='$pwChange'>$pwChange</a>";
    $body.= "<br><br>Best regards,<br>The POPS Team";

    pops_email($newUser['Email'],$subject,$body);

  }else{
    //Send Error
    $errors_private[] = "Registration find query returned a negtive number of results.\n".$dbh->error;
  }

//Print all errors to error_file
  if( !empty($errors) ){
    array_unshift( $errors, "<strong>The following Errors were detected:</strong>");
  }
  array_unshift( $errors, $notification);

  echo implode("<br>",$errors);

  //Write to file
  if( !empty($errors_private) ){
    record_error($errors_private,'REGISTER-ERROR');
  }


?>
