<?php
/*Filename: pops_contact.php
 *Author: Jean Clemenceau
 *Date Created: 10/04/2016
 *Contains the page with a form to contact the people involved with the POPS project.
*/

require_once('pops_header.php');

check_login();


// Populate fields if logged in
$nameVal = isset($_SESSION['uname'])?$_SESSION['uname']:'';
$mailVal = isset($_SESSION['email'])?$_SESSION['email']:'';
$affiVal = isset($_SESSION['affiliaton'])?$_SESSION['affiliaton']:'';

$selectSubj = Array( 1=>'', 2=>'', 3=>'', 4=>'', 5=>'');
if(isset($_REQUEST['s']) && $_REQUEST['s']!=''){
  $selectSubj[sanitize($_REQUEST['s'])]='selected';
}

?>
<head>
  <?php
    add_setup('Contact US');
    add_scripts('result_manipulation.js');
  ?>
  <script>
  $(document).ready(function(){
    //Present extra subject line
    $('#subject').on('change',function(){
      if($('#subject option:selected').val() == 'Other'){
        $('#subject_other').show();
        $('#subject_other').attr('required',true);
      }else{
        $('#subject_other').hide();
        $('#subject_other').removeAttr('required');
      }
    });

    //submit Message
    $('#sendMessage').on('click', function(){
      $('#contact_form').unbind("submit").bind('submit', function(){
        $.ajax({
          url:  'pops_contact_process.php',
          data: $('#contact_form').serialize(),
          type: "POST",
          dataType: "json",
          success: function(response){
              console.log("AJAX: Message Processed Succesfully");
              $('#contact_token').val(response.newToken);
              $('#notification_modal_title').text(response.modal_title);
              $('#notification_modal_body').html(response.modal_content);
              $('#notification_modal').modal('show');
          },
          error: function(xhr, status, errorThrown){
            console.log("AJAX-ERROR: " + xhr.status + ' - ' + xhr.statusText );
            $('#notification_modal_title').text('ERROR: Message NOT Sent' );
            $('#notification_modal_body').html("<div class='notificationAlert'><p>There has been a server communication error. Your message could not be sent. We apologize for the inconvenience. </p><p>Please try again later. If the problem persists, please contact our webmaster:</p><p> "+'<?php echo POPS_ADMIN?>'+"</p><div>");
            renewToken('contact_form','contact_token');
            $('#notification_modal').modal('show');
          },
          complete: function(xhr, status){
            $('#contact_form')[0].reset();
            console.log(status);
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
  print_navigation('contact');
  $contact_token = request_form_token('contact_form');
?>
<div class='container-fluid row row-centered'>
  <h2 class='text-center pageTitle'>Contact Us</h2>
  <hr>
</div>
<div class='container-fluid text-center' id='main_content' >

  <form id='contact_form' class='form-horizontal col-sm-6 center-block' action='#' method='post' role='form' enctype='multipart/form-data'>

    <div class='form-group'>
      <label class='control-label col-sm-2' for='name'>Name:</label>
      <div class='col-sm-10'>
        <input class='form-control' type='text' id='name' name='name' maxlength=50 value='<?php echo $nameVal;?>' required/>
      </div>
    </div>

    <div class='form-group'>
      <label class='control-label col-sm-2' for='email'>E-mail:</label>
      <div class='col-sm-10'>
        <input class='form-control' type='text' id='email' name='email' maxlength=254  value='<?php echo $mailVal;?>'required/>
      </div>
    </div>

    <div class='form-group'>
      <label class='control-label col-sm-2' for='affiliation'>Affiliation:</label>
      <div class='col-sm-10'>
        <input class='form-control' type='text' id='affiliation' name='affiliation'  value='<?php echo $affiVal;?>' maxlength=50/>
      </div>
    </div>

    <div class='form-group'>
      <label class='control-label col-sm-2' for='subject'>Subject:</label>
      <div class='col-sm-10'>
        <select class='form-control' id='subject' name='subject' required>
          <option value=''>Select a Subject</option>
          <option <?php echo $selectSubj[1];?> value='Collaboration_Request'>Collaboration Request</option>
          <option <?php echo $selectSubj[2];?> value='Reagent_Request'>Reagent Request</option>
          <option <?php echo $selectSubj[3];?> value='Dataset_Request'>Dataset Request</option>
          <option <?php echo $selectSubj[4];?> value='Website_Error_Report'>Website Errors or Suggestions</option>
          <option <?php echo $selectSubj[5];?> value='Report_Unauthorised_Account_Activity'>Report Unauthorised Account Activity</option>
          <option value='Other'>Other</option>
        </select>
      </div>
    </div>

    <div class='form-group'>
      <div class='col-sm-10 col-sm-offset-2'>
        <input class='form-control' type='text' id='subject_other' name='subject_other' maxlength=100 placeholder='Enter Custom Subject' value='' style="display:none;"/>
      </div>
    </div>

    <div class='form-group'>
      <label class='control-label col-sm-2' for='message'>Message:</label>
      <div class='col-sm-10'>
        <textarea class="form-control" rows=5 cols=30 id='message' name='message' required></textarea>
      </div>
    </div>

    <div class='form-group text-center'>
      <button class='btn btn-default col-sm-6 col-sm-offset-3' name='send_message' id='sendMessage' style='font-weight:bold;'>Send!</button>
    </div>

    <input type='hidden' id='contact_token' name='token' value='<?php echo $contact_token; ?>' />
    <input type='hidden' id='formName' name='formName' value='contact_form' />

  </form>

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
