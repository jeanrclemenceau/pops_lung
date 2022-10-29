<?php
/*Filename: login_form.php
 *Author: Jean Clemenceau
 *Date Created: 3/14/2016
 *Prints out a form to log in to the POPS website.
 *Links to registration form.
*/
  require('pops_conf.php');
  check_login();
  $redirect = isset($_POST['rd'])? sanitize($_POST['rd']) : '';
  $confCode = isset($_POST['cc'])? sanitize($_POST['cc']) : '';

  $login_token = request_form_token('login_form');
  $register_token = request_form_token('register_form');
?>
<img class='img-responsive mainLoginImg' src='images/POPS_lung_logo.png'/>
<div class='container-fluid collapse navbar-collapse mainLoginTitle'>
  <h1>Precision Oncology Probe Set -&nbsp;Lung&nbsp;Cancer</h1>
</div>


<div class='container-fluid row row-centered' id='login_form_container'>
  <form id='login_form' class='form-horizontal col-sm-12 center-block' action='#' method='post' role='form' enctype='multipart/form-data'>
    <div class='form-group'>
      <label class='control-label col-sm-1 col-sm-offset-3' for='email'>Email:</label>
      <div class='col-sm-5'>
        <input class='form-control' type='text' id='email' name='email' maxlength=254 required tabindex=1/>
      </div>
    </div>

    <div class='form-group'>
      <label class='control-label col-sm-1 col-sm-offset-3' for='password'>Password:</label>
      <div class='col-sm-5'>
        <input class='form-control' type='password' id='password' name='password' maxlength=32 required tabindex=2/>
      </div>
    </div>

    <div class='form-group' style='margin-bottom:0;'>
      <button class='btn btn-default col-sm-3 col-sm-offset-3' type='button' data-toggle='modal' data-target='#registration' name='register_show' id='register_show' onClick="$(''#register_form input:not([id*=\'token\'])');"  tabindex=4>Register</button>
      <button class='btn btn-default col-sm-3' name='login' id='login'  tabindex=3>Log In</button>
    </div>

    <input type='hidden' id='rd' name='rd' value='<?php echo $redirect; ?>'/>
    <input type='hidden' id='cc' name='cc' value='<?php echo $confCode; ?>'/>
    <input type='hidden' id='login_token' name='token' value='<?php echo $login_token; ?>' />
  </form>

  <div class='container-fluid row'>
    <div class='col-sm-3 col-sm-offset-3 text-left' style='font-size:small;'>
      Forgot&nbsp;password? <a href='#' id='forgotpwd'>Click&nbsp;Here.</a>
    </div>
  </div>
</div>

<!-- Registration BS modal -->
<div id='registration' class='modal fade' role='dialog'>
  <div class='modal-dialog'>
    <div class='modal-content'>
      <div class='modal-header'>
        <button type="button" class='close' data-dismiss='modal'>&times;</button>
        <h4 class='modal-title'>Register for POPS Lung</h4>
      </div>

      <div class='modal-body' id='register_form_container'>
        <!-- <div class='container-fluid row' id='register_form_container'> -->

          <form id='register_form' class='' action='#' method='post' role='form' enctype='multipart/form-data' data-toggle="validator">
            <div class='form-group text-left'>
              <label class='control-label' for='reg_email'>Email:</label>
                <input class='form-control register' type='email' id='reg_email' name='reg_email' maxlength=254 value='' required/>
            </div>

            <div class='form-group text-left'>
              <label class='control-label' for='fname'>First Name:</label>
                <input class='form-control register' type='text' id='fname' name='fname' maxlength=20 value='' required/>
            </div>

            <div class='form-group text-left'>
              <label class='control-label' for='lname'>Last Name:</label>
                <input class='form-control register' type='text' id='lname' name='lname' maxlength=20  value='' required/>
            </div>

            <div class='form-group text-left'>
              <label class='control-label' for='affi'>Affiliation:</label>
                <input class='form-control register' type='text' id='affi' name='affi' maxlength=50  value=''/>
            </div>

            <div class='form-group text-left'>
              <label class='control-label' for='reg_password'>Password:</label>
                <input class='form-control register' type='password' id='reg_password' name='reg_password' maxlength=32  value='' required/>
            </div>

            <div class='form-group text-left'>
              <label class='control-label' for='confpw'>Confirm Password:</label>
                <input class='form-control register' type='password' id='confpw' name='confpw' maxlength=32  value='' required/>
            </div>

            <input type='hidden' id='register_token' name='token' value='<?php echo $register_token; ?>' />

          </form>
        <!-- </div> -->
      </div>
      <div class='modal-footer'>
        <button type='submit' form="register_form" class='btn btn-default btn-block col-offset-sm-1 col-sm-10' name='register' id='register' >Register</button>
      </div>
    </div>
  </div>
</div>


<!-- Registration BS modal -->
<div id='notify_registration' class='modal fade' role='dialog'>
  <div class='modal-dialog'>
    <div class='modal-content'>
      <div class='modal-header'>
        <button type="button" class='close' data-dismiss='modal'>&times;</button>
        <h4 class='modal-title'>Register for POPS Lung</h4>
      </div>
      <div class='modal-body' id='register_result_container'>
      </div>
      <div class='modal-footer'>
        <button type='button' class='btn btn-default btn-block col-offset-sm-1 col-sm-10' data-dismiss='modal' name='finish_reg' id='finish_reg' >Finish</button>
      </div>
    </div>
  </div>
</div>
