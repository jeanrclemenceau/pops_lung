<?php
/*Filename: logout.php
 *Author: Jean Clemenceau
 *Date Created: 05/05/2016
 *Unsets the session and logs out the user from the account and redirects to index.
 *based on recommendations by the PHP manual.
*/
require_once('pops_conf.php');

//Request Session
check_login();

//Unset session values
$_SESSION = Array();

//Unset the session id in the cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

//Destroy the session
session_destroy();

//Redirect back to Referrer
header ("location:index.php");
?>
