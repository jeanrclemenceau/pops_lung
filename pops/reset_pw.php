<?php
/*Filename: reset_pw.php
 *Author: Jean Clemenceau
 *Date Created: 05/09/2016
 *Changes user password, resets from scratch session isn't set.
*/
ini_set('memory_limit', '-1');
require('pops_header.php');
require('pops_dbi.php');
$tablename = 'members';
check_login();

//Determine mode (embeded for change, stand-alone for reset)
$embeded = ( isset($_POST['embeded']) )? $_POST['embeded'] : false;
$embeded_txt = ($embeded)?'true':'false';
error_log("Embeded: $embeded_txt" ); //TODO remove

//Confirm email legitimacy with confirmation code
$emailconfCode = (isset($_GET['cc']))? sanitize($_GET['cc']):'';

if( isset($_POST['reset_token']) ){
  // Verify valid token
  if( !validate_form_token('resetpw_form',sanitize($_POST['reset_token']) ) ){
    echo "This form submission has expired or comes from an unrecognized referer.\n";
    exit;
  }
  //Input valid parameters
  $oldpw = isset($_POST['old_password'])? sanitize($_POST['old_password']) : '';
  $newpw = isset($_POST['password'])? sanitize($_POST['password']) : '';
  $cnfpw = isset($_POST['conf_password'])? sanitize($_POST['conf_password']) : '';
  $cnfcd = isset($_POST['confcd'])? sanitize($_POST['confcd']) : '';
  $email = isset($_POST['email'])? sanitize($_POST['email']) : '';

  //Validate all parameters
  if($email ==''){
    echo "Please enter a valid E-mail address.\n";
    exit;
  }
  if($newpw != $cnfpw){
    echo "Your new password does not match the confirmation. Try again.\n";
    exit;
  }
  if($embeded){
    if($oldpw == ''){
      echo "Please enter your current password.\n";
      exit;
    }
  }else{
    if($cnfcd == ''){
      echo "Please use the activation link provided to you by E-mail.\n";
      exit;
    }
  }

  //Validate user
  if($embeded){
    error_log("Validating with Old password" ); //TODO remove
    $errors=Array();
    $user = authenticate($email,$oldpw,$errors);
    if( isset($user)){
      $query = "SELECT member_id FROM $tablename WHERE email = ?";
      $res = query_db($query,'ss', $email, 'Reset PW Search query');
    }else{
      array_unshift($errors, "Authentication FAILED.");
      echo implode("\n",$errors) . "\n";
      exit;
    }
  }else{
    error_log("Validating with confirmation code" ); //TODO remove
    $query = "SELECT member_id FROM $tablename WHERE email = ? AND confirm_code = ?";
    $params = Array($email,$cnfcd);
    $res = query_db($query,'ss', $params, 'Reset PW Search query');
  }

  if($res->num_rows == 1){
    //Change set up new settings
    $usr_obj = $res->fetch_object();

    $newSettings = Array();
    $newSettings[0]= generate_salt();
    $newSettings[1]=dbi_hash(PEPPER.dbi_hash($newSettings[0].$newpw) );
    $newSettings[2] = 'OK';
    $newSettings[3] = $usr_obj->member_id;
    $update_q = "UPDATE $tablename SET salt=?,password=?,confirm_code=? WHERE member_id = ?";

    $dbh = connect_db(DB_USR_RW,DB_PWD_RW);
    $affected_rows= query_db($update_q,'sssi',$newSettings,'Reset PW update query',$dbh);
    if( $affected_rows == 1 ){
      $body = "Your password for the POPS Lung project has been successfuly changed.<br>If you did not request this change, We recommend you change your POPS password and email settings.";

      pops_email($email,'POPS Lung Password Change Confirmation',$body);

      echo "Your password has been successfuly changed!\n";
    }
    else{
      error_log("Reset Password Update Query for user {$usr_obj->member_id} affected $affected_rows rows.");
    }
    $dbh->close();

  }else if($res->num_rows > 1){
    error_log("Reset PW Search error: Query returned {$res->num_rows} results for address: '$email'.");
  }else{
    if($embeded){
      $notice = "Email or Current Password are not valid.";
    }else{
      $notice = "Confirm that your link has not expired.";
    }
    echo "Authentication has failed.\n$notice";
    error_log("Reset PW Search: $email Authentication Failed. $notice" ); //TODO remove
  }

}else{
  // Print the form and reqired javascript
  $reset_token = request_form_token('resetpw_form');
  if(!$embeded){?>

<head>
    <?php add_setup('Reset Password'); ?>
    <script>
$(document).ready(function(){
  $('#submitChangePassword').click(function(){
    // Validate form
    var valid = true;
    $('#resetpw_form input').each(function(){
      if( $(this)[0].checkValidity() == false){
        $(this).css("box-shadow", "0  0 3px red");
        $(this).attr('placeholder','This is a required field');
        valid = false;
      }
    });
    if( valid == true && $('#password').val() != $('#conf_password').val() ){
      $("#conf_password").css("box-shadow", "0  0 3px red");
      $('#conf_password').val('');
      $("#conf_password").attr('placeholder','Confirmation must match password');
      valid = false;
    }

    // submit the form
    if( valid == true){
      $.ajax({
        url:'reset_pw.php',
        data:$('#resetpw_form').serialize(),
        type:'POST',
        dataType:'text',
        success: function( output ){
          alert(output);
  <?php
    //Redirect to index
    if(!$embeded){
  ?>
          window.location.href='index.php';
  <?php
    }
  ?>
        }
      });
    }
    return false;
  });
});
    </script>
</head>
<body>

<?php
    print_navigation();
  }
?>
  <div class='container-fluid row row-centered'>
    <h2 class='text-center pageTitle'><?php echo ($embeded)?"Change":"Reset";?> POPS Lung Password</h2>
    <hr>
  </div>
  <div class='container-fluid row row-centered' id='resetpw_form_container'>
    <form id='resetpw_form' class='form-horizontal col-sm-12 center-block' action='#' method='post' role='form' enctype='multipart/form-data'>
      <div class='form-group'>
        <label class='control-label col-sm-1 col-sm-offset-3' for='email'>Email:</label>
        <div class='col-sm-5'>
          <input class='form-control' type='text' id='email' name='email' maxlength=254 required/>
        </div>
      </div>
<?php
  if($embeded){
?>
      <div class='form-group'>
        <label class='control-label col-sm-1 col-sm-offset-3' for='old_password'>Old Password:</label>
        <div class='col-sm-5'>
          <input class='form-control' type='password' id='old_password' name='old_password' maxlength=32 required/>
        </div>
      </div>
<?php
  }
?>
      <div class='form-group'>
        <label class='control-label col-sm-1 col-sm-offset-3' for='password'>New Password:</label>
        <div class='col-sm-5'>
          <input class='form-control' type='password' id='password' name='password' maxlength=32 required/>
        </div>
      </div>

      <div class='form-group'>
        <label class='control-label col-sm-1 col-sm-offset-3' for='conf_password'>Confirm Password:</label>
        <div class='col-sm-5'>
          <input class='form-control' type='password' id='conf_password' name='conf_password' maxlength=32 required/>
        </div>
      </div>

      <div class='form-group text-center' >
        <button class='btn btn-default' name='reset' id='submitChangePassword'><?php echo ($embeded)?"Change":"Reset";?> Password</button>

<?php
  //Allow canceling procedure if password change while logged in
  if($embeded){
?>
        <button class='btn btn-default' name='cancel' type='button' id='cancelResetPW'>Cancel</button>
<?php
  }
?>
      </div>

      <input type='hidden' id='confcd' name='confcd' value='<?php echo $emailconfCode; ?>' />
      <input type='hidden' id='reset_token' name='reset_token' value='<?php echo $reset_token; ?>' />
    </form>
  </div>

<?php
if(!$embeded){
  print_footer();
  echo "</body>";
}
}
?>
