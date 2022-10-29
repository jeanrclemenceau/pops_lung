<?php
/*Filename: access_functions.php
 *Author: Jean Clemenceau
 *Date Created: 5/02/2016
 *Gives access to php functions to asynchronous browser requests
*/

require ('pops_conf.php');
check_login();

$func = (isset($_POST['func']) && $_POST['func']!='')? sanitize($_POST['func']) : '';

/*Renews the desired form token
 *INPUT (from $_POST):
 *  -form: The unique form ID that requires the token.
 *OUTPUT:
 *  -The new token or null if error
*/
if($func == 'renew_token'){
  check_login();
  $results = Array();

  if(isset($_POST['form']) && $_POST['form']!=''){
    $results['token'] = request_form_token( sanitize($_POST['form']));
  }
  echo json_encode($results);
}

/*In case of undefined option*/
else{
  //Do Nothing
}
?>
