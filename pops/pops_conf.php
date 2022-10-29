<?php
/*Filename: pops_conf.php
 *Author: Jean R Clemenceau
 *Date Created: 03/11/2015
 *Contains Information used accross the
 *  Presicion Oncology Probe Set (POPSdbL) project.
 *
*/

// Insert security settings from "SecuritySettings.php" here


// **************SECURITY FUNCTIONS**************

//Sanitize input to prevent XSS and SQL injection attacks
function sanitize( $text ){
    if( is_array($text) ){
        for( $i=0; $i < count($text); $i++){
            //Allow comparison operators
            if( preg_match("/^[!<=>]+$/",$text[$i]) < 1){
              $text[$i] = strip_tags($text[$i]);
              $text[$i] = htmlentities( $text[$i], ENT_QUOTES);
            }
        }
    }else{
      //Allow comparison operators
      if( preg_match("/^[!<=>]+$/",$text) < 1){
        $text = strip_tags($text);
        $text = htmlentities( $text, ENT_QUOTES);
      }
    }
    return $text;
}

/*Hashes plaintext according to algorithm specified in fusion_conf.php
 *Input:
 * -$plaintext: text to be hashed.
 *Output:
 * -Hashed data.
*/
function dbi_hash($plaintext){
    return hash(HASH_ALG,$plaintext);
}

/*Generates a salt for the user's password
 *Input:
 * -None
 *Output:
 * -new salt.
*/
function generate_salt(){
    mt_srand(time());
    return hash(SALT_ALG, mt_rand() );
}

/*Creates and stores a token value to prevent Cross Site Request Forging.
 *Input:
 * -$form_id: Unique id for form requesting token.
 *Output:
 * -the new token.
*/
function request_form_token($form_id){
  mt_srand(time());
  $new_token = hash(HASH_ALG, PEPPER.$form_id.mt_rand());

  // Save form's token in session data
  $_SESSION[$form_id] = $new_token;

  return $new_token;
}

/*Validates that input is not a result of Cross Site Request Forging.
 *Clears the stored token regardless of result.
 *Input:
 * -$form_id: Unique id for form requesting token.
 * -$received_token: Token received by the website processing the input.
 *Output:
 * -Wether or not the token is valid.
*/
function validate_form_token($form_id, $received_token){
  $valid = false;
  if(isset($_SESSION[$form_id]) && $received_token == $_SESSION[$form_id]){
    $valid = true;
  }
  unset($_SESSION[$form_id]);
  return $valid;
}

// **************SESION MANAGEMENT FUNCTIONS**************

/*Initializes the session to check if it is active
*/
function check_login(){
    //if (session_status() == PHP_SESSION_NONE) { //For PHP5.4+
    if (session_id() == '') {
        session_name(POPS_SESSION_NAME);
        session_start();
    }
    $loggedin =(isset($_SESSION["uid"]) && $_SESSION['uid'] != '')?true:false;
    return $loggedin;
}

/*Requires the session to be active in order to load the page
*Input:
* -$url: url of file to be redirected to if session not valid.
* -$embeded: True if the output is expected to be embeded in a container.
*Output:
* -NULL if session valid, if not, redirect or warning message.
*/
function require_login($url='',$embeded = false){
    //if (session_status() == PHP_SESSION_NONE) { //For PHP5.4+
    if (session_id() == '') {
        session_name(POPS_SESSION_NAME);
        session_start();
    }
    $valid_session = NULL;
    $url=$_SERVER['PHP_SELF']; //redirect back to current site after login
    if(!isset($_SESSION["uid"]) || $_SESSION['uid'] == ''){
      if(!$embeded){
        header ("location:index.php?rd=$url");
      }else{
        $valid_session = "Your session has expired, log back in to gain access to this page.";
      }
    }
    return $valid_session;
}

// **************NOTIFICATION FUNCTIONS**************

/*Sends a no-reply email using SMTP server in behalf of someone else, POPS by default
 *Returns true if successful, otherwise alerts fail message and returns false.
*/
function pops_email_SMTP($to, $subject, $body, $from="no-reply@utsouthwestern.edu", $fromName="POPS-Lung Admin"){
    //use phpmailer and setup email
    require_once ("class.phpmailer.php");
    $mail = new PHPMailer(true); //New instance, with exceptions enabled
    $mail->IsSMTP();                  // tell the class to use SMTP

    $mail->Port       = MAIL_PORT;    // set the SMTP server port
    $mail->Host       = MAIL_HOST;    // SMTP server
    $mail->Username   = MAIL_USR;     // SMTP server username
    $mail->Password   = MAIL_PWD;     // SMTP server password

    $mail->AddReplyTo($from,$fromName);
    $mail->From       = "no-reply@utsouthwestern.edu";
    $mail->FromName   = "POPS-Lung Admin";

    $mail->AddAddress($to);
    $mail->Subject  = $subject;
    $mail->MsgHTML($body);

    $mail->IsHTML(true); // send as HTML
    $mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
    $mail->WordWrap   = 80; // set word wrap

    if(!$mail->Send()){
      error_log("Mailer Error: {$mail->ErrorInfo}");
      return false;
    }else{
      return true;
    }
}

/*Sends a no-reply email using "sendmail" in behalf of someone else, POPS by default
 *Returns true if successful, otherwise alerts fail message and returns false.
*/
function pops_email($to, $subject, $body, $from="no-reply@utsouthwestern.edu", $fromName="POPS-Lung Admin"){
  $theMail = "From: $fromName<$from>\\nTo: $to\\nSubject: $subject\\nContent-Type: text/html\\nMIME-Version: 1.0\\n$body";
  exec("echo -e '$theMail'| sendmail -t");
  return true;
}

/*Stores an error summary in the designated "ERR_OUT" directory.
*/
function record_error($errors, $filename_root){
  //Setup contents
  $errorOutput = "Time: " . date('M/d/Y \a\t H:i:s') . "\n";
  if(is_array($errors)){
    $errorOutput.= implode("\n",$errors);
  }else{
    $errorOutput .= $errors;
  }

  //Write to file
  $tmpfname = tempnam(ERR_OUT, $filename_root."_".date('d-M-Y-H-i-s')."_");
  exec("mv $tmpfname $tmpfname.txt");
  $tmpfname .= ".txt";

  $handle = fopen($tmpfname, "w");
  fwrite($handle, $errorOutput);
  fclose($handle);
}
?>
