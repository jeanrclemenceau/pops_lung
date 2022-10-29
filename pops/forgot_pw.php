<?php
/*Filename: forgot_pw.php
 *Author: Jean Clemenceau
 *Date Created: 05/09/2016
 *Sends a link to reset password.
*/

require('pops_dbi.php');
require('pops_header.php');
$tablename = 'members';
check_login();

if( isset($_POST['sendReset']) ){
  // Verify valid token
  $token = sanitize($_POST['token']);
  if( !validate_form_token('forgotpw_form',$token ) ){
    echo "You may have been subject to a XSRF attack, please verify the legitimacy of your links.";
    exit;
  }
  //Verify valid email
  $email = isset($_POST['email'])? sanitize($_POST['email']) : '';
  if($email ==''){
    echo "Please enter a valid E-mail address.";
    exit;
  }
  //Validate email address
  $query = "SELECT member_id FROM $tablename WHERE email = ?";
  $res = query_db($query,'s', $email, 'forgot PW Search query');
  if($res->num_rows == 1){

    //Change set up confirmation code
    $usr_obj = $res->fetch_object();
    mt_srand(time());
    $new_cc = dbi_hash( $email . PEPPER . mt_rand() );
    $update_q = "UPDATE $tablename SET confirm_code= ? WHERE member_id = ?";
    $params = Array($new_cc,$usr_obj->member_id);
    $dbh = connect_db(DB_USR_RW,DB_PWD_RW);

    $affected_rows= query_db($update_q,'si',$params,'forgot PW update query',$dbh);
    if( $affected_rows == 1 ){
      $link= "http://".HTTP_HOST.POPS_HOME."reset_pw.php?cc=$new_cc";
      $body = "Click on this link to reset your password:\n\n<a href='$link' title='Click to reset your Password'>$link<a/>";

      pops_email($email,'POPS Lung Password Reset',$body);
    }
    else{
      error_log("Forgot Password Update Query for user {$usr_obj->member_id} affected $affected_rows rows.");
    }

    $dbh->close();

  }else if($res->num_rows > 1){
    error_log("forgot PW Search error: Query returned {$res->num_rows} results for address: '$email'.");
  }

  // Always confirm to prevent user enumeration
  echo "A password reset link has been sent to your E-mail. If not found, make sure to check your junk folder.";

}else{

$forgot_token = request_form_token('forgotpw_form');
?>
<div class='container-fluid row row-centered' id='forgotpw_form_container'>
  <form id='forgotpw_form' class='form-horizontal col-sm-12 center-block' action='#' method='post' role='form' enctype='multipart/form-data'>
    <div class='form-group'>
      <label class='control-label col-sm-1 col-sm-offset-3' for='email'>Email:</label>
      <div class='col-sm-5'>
        <input class='form-control' type='text' id='email' name='email' maxlength=254 required/>
      </div>
    </div>
    <div class='form-group text-center' >
      <button class='btn btn-default' name='sendReset' type='submit' id='sendResetPwdEmail'>Reset Password</button>
      <button class='btn btn-default' name='cancel' type='button' id='cancelForgotPW'>Cancel</button>
    </div>

    <input type='hidden' id='forgot_token' name='token' value='<?php echo $forgot_token; ?>' />
  </form>
</div>
<?php
}
?>
