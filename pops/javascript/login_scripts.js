/*Filename: login_scripts.js
 *Author: Jean R Clemenceau
 *Date Created: 03/20/2015
 *Contains the javascript scripts necessary for the log in process
*/

function loadLoginForms(confCode,redirect){
  $.ajax({
    url: "login_form.php",
    data: "cc="+confCode+"&rd="+redirect,
    type: 'POST',
    dataType: 'HTML',
    success: function( content ){
      $('#main_content').html(content);
    }
  });
}


$( document ).ajaxComplete(function( event, xhr, settings ) {
  // Log in button action
  $('#login').click(function(){
    $.ajax({
      url: 'login_process.php',
      data: $('#login_form').serialize(),
      type: 'POST',
      async: false,
      dataType: 'json',
      success: function(output){
        if(output.auth == true){
          window.location.replace(output.redirect); //redirect
        }else{
          alert(output.errors);
        }
      }
    });

    //Reset Login token
    $.ajax({
      url: 'access_functions.php',
      data: {func:'renew_token',form:'login_form'},
      type: 'POST',
      async: false,
      dataType: 'json',
      success: function(response){
        if( jQuery.isEmptyObject(response) ){
          console.log("AJAX_login_token_reset_ERROR: Token is empty");
        }else{
          $('#login_token').val(response.token);
        }
      },
      error: function(xhr, status, errorThrown){
        console.log("AJAX_login_token_reset_ERROR: "+ errorThrown +'-'+ status + '. ' + xhr.status+" - "+ xhr.statusText );
      },
      complete: function(xhr, status){
        console.log("AJAX: Login Token Reset - Run Completed. Status: "+ status);
      }
    });

    // return false; //Avoid redirect
  });


  // Registration button action
  $('#register_form').submit(function(){
    // Reset alerts
    $('.modal-content .alert').remove()

    // Validate empty fields
    var invalid = false;
    $("input.register[required]").each(function(){
      if($(this).val() == ''){
        $(this).css('box-shadow','1px 1px 1px red');
        invalid=true;
      }else{
        $(this).css('box-shadow','none');
      }
    });
    if(invalid){
      $('.modal-content').append("<div id='warningEmptyFields' class='alert alert-danger text-left'>Complete required fields.</div>");
    }

    // Validate passwords matching
    if($('#reg_password').val() != $('#confpw').val()){
      $('.modal-content').append("<div id='warningPwdConf' class='alert alert-danger text-left'>Password and confirmation do not match.</div>");
      $("input.register[type='password']").css('box-shadow','1px 1px 1px red');
      invalid = true;
    }

    if(invalid){
      return false;
    }

    // Process register form
    $.ajax({
      url: 'register.php',
      data: $('#register_form').serialize(),
      type: 'POST',
      async: false,
      dataType: 'HTML',
      success: function(content){
//Use JSON array
        $('#registration.modal').modal('hide');
        // loadLoginForms();
        // $('#register_form input').val('');

        $('#register_result_container').html(content);
        $('#notify_registration.modal').modal('show');
      },
      error: function(xhr, status, errorThrown){
        console.log("AJAX_Register_ERROR: "+ errorThrown +'-'+ status + '. ' + xhr.status+" - "+ xhr.statusText );
      },
      complete: function(xhr, status){
        console.log("AJAX: Register - Run Completed. Status: "+ status);
      }
    });

    // Reset token
    $.ajax({
      url: 'access_functions.php',
      data: {func:'renew_token',form:'register_form'},
      type: 'POST',
      async: false,
      dataType: 'json',
      success: function(response){
        if( jQuery.isEmptyObject(response) ){
          console.log("AJAX_Register_token_reset_ERROR: Token is empty");
        }else{
          $('#register_token').val(response.token);
        }
      },
      error: function(xhr, status, errorThrown){
        console.log("AJAX_Register_token_reset_ERROR: "+ errorThrown +'-'+ status + '. ' + xhr.status+" - "+ xhr.statusText );
      },
      complete: function(xhr, status){
        console.log("AJAX: Register Token Reset - Run Completed. Status: "+ status);
      }
    });

    return false; //Avoid redirect
  });

  // Forgot password link action
  $('#forgotpwd').click(function(){
    $.ajax({
      url: 'forgot_pw.php',
      dataType: 'HTML',
      success: function(output){
        $('#main_content').html(output);
      }
    });
  });

  // Submission of forgot Password form
  $('#sendResetPwdEmail').click(function(){
    if( $('#forgotpw_form')[0].checkValidity() == false){
      $("#email").css("box-shadow", "0  0 3px red");
      $("#email").attr('placeholder','You must enter an E-mail address')
      return false;
    }

    $.ajax({
      url: 'forgot_pw.php',
      data: $('#forgotpw_form').serialize() + "&sendReset=true",
      type: 'POST',
      dataType: 'text',
      success: function(output){
        alert(output);
        // Redirect
        $.ajax({
          url: "login_form.php",
          dataType: 'HTML',
          success: function( content ){
            $('#main_content').html(content);
          }
        });
      }
    });
    return false;
  });

  // cancellation of forgot Password form
  $('#cancelForgotPW').click(function(){
    $.ajax({
      url: "login_form.php",
      // data: "cc="+confCode+"&rd="+redirect,
      // type: 'POST',
      dataType: 'HTML',
      success: function( content ){
        $('#main_content').html(content);
      }
    });
  });


});
