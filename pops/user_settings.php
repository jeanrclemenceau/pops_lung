<?php
/*Filename: user_settings.php
 *Author: Jean Clemenceau
 *Date Created:  10/04/2016
 *Contains the page with a form to Update the user profile in the project.
*/

require_once('pops_header.php');
require_once('pops_dbi.php');

// Verify that user is logged in
$notice = require_login('user_settings.php');
if(!isset($notice)){
  // Populate fields if logged in
  $udata = NULL;
  $uid = isset($_SESSION['uid'])?$_SESSION['uid']:'';
  $udata_query = "SELECT email, first_name,last_name, affiliation FROM members WHERE member_id = ?";
  $udata_res= query_db($udata_query,'d',$uid,'User Data Query');
  if($udata_res->num_rows == 1 ){
    $udata = $udata_res->fetch_object();
    $fname = isset($udata->first_name)?$udata->first_name:'';
    $lname = isset($udata->last_name)?$udata->last_name:'';
    $email = isset($udata->email)?$udata->email:'';
    $affil = isset($udata->affiliation)?$udata->affiliation:'';
  }else{
    $errorMsg = "<div class='failAlert'><p>We couldn't find your user information.</p><p>Please try to sign in again.<p></div>";
  }

}else{
  $errorMsg = "<div class='failAlert'>$notice</div>";
}

?>
<head>
  <?php
    add_setup('User Settings');
    add_scripts('result_manipulation.js');
  ?>
  <script>
  $(document).ready(function(){
    //Present extra subject line
    $('#new_password').on('change',function(){
      if($('#new_password').val() != ''){
        $('#cnf_password').removeAttr('disabled');
        $('#cnf_password').attr('required','true');
      }else{
        $('#cnf_password').removeAttr('required');
        $('#cnf_password').attr('disabled','true');
        $('#cnf_password').val('');
      }
    });

    //submit Message
    $('#update_profile').on('click', function(){
      // alert('clicked');//TODO remove
      $('#user_settings_form').unbind("submit").bind('submit', function(){
        $.ajax({
          url:  'user_settings_process.php',
          data: $('#user_settings_form').serialize(),
          type: "POST",
          dataType: "json",
          success: function(response){
              console.log("AJAX: Message Processed Succesfully");
              $('#user_settings_token').val(response.newToken);
              $('#notification_modal_title').text(response.modal_title);
              $('#notification_modal_body').html(response.modal_content);
              $('#notification_modal').modal('show');
          },
          error: function(xhr, status, errorThrown){
            console.log("AJAX-Error: " + xhr.status + ' - ' + xhr.statusText );
            $('#notification_modal_title').text('Server Error');
            if(xhr.status == 500){
              $('#notification_modal_body').html("<div class='failAlert'><p>Error: Our server could not process your request. Please try again.</p><p>If the error persist, please submit a ticket <a href='pops_contact.php?s=4'>HERE</a> </div>");
            }else{
              $('#notification_modal_body').html("<div class='failAlert'><p>There has been a server communication error. Your changes were not applied. We apologize for the inconvenience.<p>Please try again later. If the problem persists, please submit a ticket <a href='pops_contact.php?s=4'>HERE</a></p><div>");
            }
            renewToken('user_settings_form','user_settings_token');
            $('#notification_modal').modal('show');
          },
          complete: function(){
            $('#password').val('');
            $('#new_password').val('');
            $('#cnf_password').val('');
            $('#cnf_password').attr('disabled','true');
            $('#cnf_password').removeAttr('required');
          }
        });
        return false; //prevent redirect
      });
    });
  });
  </script>
</head>
<body>
<?php
  print_navigation('User Settings');
  $user_settings_token = request_form_token('user_settings_form');
?>
<div class='container-fluid row row-centered'>
  <h2 class='text-center pageTitle'>User Settings</h2>
  <hr>
</div>
<div class='container-fluid' id='main_content' >
  <?php
  if(!isset($udata)){
    echo $errorMsg;
  }else{
  ?>
  <form id='user_settings_form' class='col-sm-6 center-block' action='#' method='post' role='form' enctype='multipart/form-data'>

    <div class='row form-group'>
      <div class='col-sm-6'>
        <label class='control-label' for='first_name'>First Name:</label>
        <input class='form-control' type='text' id='first_name' name='first_name' maxlength=20 placeholder='Enter Your First Name' value='<?php echo $fname;?>' required/>
      </div>
      <div class='col-sm-6'>
        <label class='control-label' for='last_name'>Last Name:</label>
        <input class='form-control' type='text' id='last_name' name='last_name' maxlength=20 placeholder='Enter Your First Name' value='<?php echo $lname;?>' required/>
      </div>
    </div>

    <div class='form-group'>
      <label class='control-label' for='email'>E-mail:</label>
      <input class='form-control' type='text' id='email' name='email' maxlength=254  placeholder='Enter Your E-mail' value='<?php echo $email;?>' required/>
    </div>

    <div class='form-group'>
      <label class='control-label' for='affiliation'>Affiliation:</label>
      <input class='form-control' type='text' id='affiliation' name='affiliation' placeholder='Enter Your Organization ' value='<?php echo $affil;?>'maxlength=50/>
    </div>

    <div class='row form-group'>
      <div class='col-sm-6'>
        <label class='control-label' for='new_password'>New Password:</label>
        <input class='form-control' type='password' id='new_password' name='new_password' maxlength=32 placeholder='New Password'/>
      </div>
      <div class='col-sm-6'>
        <label class='control-label' for='cnf_password'>Confirm Password:</label>
        <input class='form-control' type='password' id='cnf_password' name='cnf_password' maxlength=32 placeholder='Confirm New Password' disabled/>
      </div>
    </div>

    <div class='row form-group'>
      <div class='col-sm-6'>
        <label class='control-label' for='password'>Password:</label>
        <input class='form-control' type='password' id='password' name='password' maxlength=32 placeholder='Current Password' required/>
      </div>
      <div class='col-sm-6'>
        <label class='control-label' for='update_profile'>&nbsp;</label>
        <button class='btn btn-default from-control' name='update_profile' id='update_profile' style='font-weight:bold;width:100%;'>Update Profile</button>
      </div>
    </div>



    <input type='hidden' id='user_settings_token' name='token' value='<?php echo $user_settings_token; ?>' />
    <input type='hidden' id='formName' name='formName' value='user_settings_form' />

  </form>
<?php } ?>
</div>
<?php
  print_footer();
?>

<!-- Notification box -->
<div id='notification_modal' class='modal fade' role='dialog'>
  <div class='modal-dialog'>
    <div class='modal-content'>
      <div class='modal-header' id='notification_modal_hdr'>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h3 class='modal-title' id='notification_modal_title'></h3>
      </div>
      <div class='modal-body' id='notification_modal_body'>
      </div>
      <div class='modal-footer' id='notification_modal_footer' style='display:none;text-align:center;'>
      </div>
    </div>
  </div>
</div>
</body>
