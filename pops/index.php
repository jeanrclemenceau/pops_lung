<!DOCTYPE html>
<html  lang="en">
<?php
/*Filename: index.php
 *Author: Jean R Clemenceau
 *Date Created: 03/11/2015
 *Contains the home page for the resicion Oncology Probe Set (POPSdbL) website.
*/
header("Content-Security-Policy: frame-ancestors 'self'",false);
require('pops_header.php');

//Setup redirection after login
check_login();

//Determine login type
$confCode = isset($_GET['cc'])? sanitize($_GET['cc']) : '';
$redirect = isset($_GET['rd'])? sanitize($_GET['rd']) : 'pops-activity.php';
?>

<head>
<?php add_setup(); ?>
<script>
  $(function(){
    loadLoginForms(<?php echo "'$confCode','$redirect'"; ?>);
  });
</script>
<script language='javascript' type='text/javascript' src='javascript/login_scripts.js'></script>

</head>


<?php
//Redirect if log in successful
if(isset($_SESSION["uid"]) && $_SESSION['uid'] != '') {
	header("location:".$redirect."");
}
//Process when not logged in
else {
    echo "<body>";
    print_navigation();
?>
<div class='container-fluid text-center' id='main_content' ></div>
<?php
    print_footer();
    echo "</body>";
}
?>
</html>
