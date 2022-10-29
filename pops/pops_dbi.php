<?php
/*Filename: pops_dbi.php
 *Author: Jean R Clemenceau
 *Date Created: 03/15/2015
 *Contains interface to use the POPS project database.
 *
*/

require_once('pops_conf.php');
date_default_timezone_set('US/Central');

/*Connects to a database and returns a mysqli database handler
 *Input:
 *  -$db: Desired database.
 *  -$user, $pass, $host: Connection specifications.
 *       Default has read only permissions.
 *Output:
 * -Database handler.
*/
function connect_db($user=DB_USR_R, $pass=DB_PWD_R, $db=DB_NAME, $host=DB_HOST){
    $dbh = new mysqli($host,$user,$pass,$db);

    if($dbh->connect_errno){
        error_log("Database Connection Error " .$dbh->connect_errno.": ".$dbh->connect_error);
        return;
    }
    else{
        return $dbh;
    }
}

/*Runs a SELECT, UPDATE or INSERT query
 *$types and $values must be non-NULL and non-empty to run query with binding.
 *Input:
 *  -$query: the query to be executed.
 *  -$types: String describing the types of params. Null implies no binding.
 *  -$values: The array of values to be bound. Null array implies no binding.
 *  -$query_name: The name of the query to be displayed in case of error.
 *  -$db_handle: database handle to be used. New connection opened if not given.
 *Output:
 *  -If SELECT query: the query's result object, else: number of affected rows.
*/
function query_db($query, $types = NULL, $values = NULL, $query_name='Query', $db_handle = NULL){
    //Connect if no handle available
    $dbh = isset($db_handle)? $db_handle : connect_db();

    $stmt = $dbh->prepare($query);
    if($stmt === false){
        error_log("ERROR: $query_name could not be prepared correctly.");
        error_log($dbh->error);
        exit; }

    //If parameters are available, prepare and bind them
    if( isset($types) && isset($values) && $types!='' && !empty($values) ){
        $params = array($types);

        //If values is an array assign references, else the value
        if(is_array($values)){
            for($i=0; ($i < count($values) && $i <10); $i++){
               $params[] = &$values[$i];
             }
        }else{
            $params[] = &$values;
        }

        // Check parameter binding
        if(false=== call_user_func_array( array($stmt,'bind_param'), $params) ){
            error_log("ERROR: $query_name could not bind values correctly.");
            error_log( $stmt->error);
            exit; }
    }
    if(!$stmt->execute() ){
        error_log ("ERROR: $query_name did not execute correctly.");
        error_log ($dbh->error);
        error_log ($stmt->error);
        exit; }

    $result=( preg_match("/SELECT|DESCRIBE/i",$query) == 1 )? $stmt->get_result() : $stmt->affected_rows;
    $stmt->close();
    if( !isset($db_handle) ){ $dbh->close(); }

    return $result;
}

/*Authenticates a user by its email and password.
 *Input:
 *  -$email: user's email.
 *  -$pw: user's plaintext password.
 *  -$error: Array containing all errors. Passed by reference.
 *Output:
 *  -$user: User's member information. NULL if not authenticated user.
*/
function authenticate($email,$pw, &$errors, $confCode=NULL){
  $the_user = NULL;
  $update_conf = '';
  $dbh = connect_db(DB_USR_RW,DB_PWD_RW);

  // Query data needed for authentication
  $info_query = "SELECT member_id,salt,status,login_attempts,last_fail_login_attempt FROM members WHERE email LIKE ?";
  $info_res = query_db($info_query,'s',$email,"U-data fetch query",$dbh);

  $salt = 'NO_SALT';
  $uid=0;
  $status = 0;
  $login_atts = 0;
  if($info_res->num_rows == 1){
    $user_info = $info_res->fetch_object();
    $uid=$user_info->member_id;
    $salt = $user_info->salt;
    $status = $user_info->status;
    $login_atts = $user_info->login_attempts;
    $last_att = new DateTime($user_info->last_fail_login_attempt);
  }
  $hashed_pw = dbi_hash(PEPPER . dbi_hash($salt . $pw) );

  //Block brute force attacks
  if($login_atts >= LOGIN_ATTEMPTS){
    $now = new DateTime();
    $elapsed_time = $last_att->diff($now,true);
    $time_left = LOGIN_DELAY - $elapsed_time->h;

    //Block if not enough time has elapsed
    if( $time_left > 0){
      $errors[] = "Your access has been blocked to prevent a brute force attack on your Account. You must wait $time_left hours.";
      $info_res->close();
      $dbh->close();
      return $the_user;
    }
    // Reset if time has passed
    $login_atts = 0;

  //Warn user if account will be blocked soon
  }else if($login_atts == LOGIN_ATTEMPTS - 2){
    $login_atts = $login_atts+1;
    $errors[] = "You have attempted to log in $login_atts times. Your account will be blocked for ".LOGIN_DELAY." hours after ".LOGIN_ATTEMPTS." attempts. Consider resetting your password.";
  }

  // Get user data according to authentication parameters
  $auth_query = "SELECT member_id,email,first_name,last_name,affiliation,status,confirm_code FROM members WHERE email = ? AND password = ?";
  $auth_res = query_db($auth_query,'ss',Array($email,$hashed_pw), 'Auth query',$dbh);

  //Verify user is elligible and passes authentication
  if($auth_res->num_rows == 1 && $status > ACCESS_NONE){
    $auth_user = $auth_res->fetch_object();

    $login_atts = 0;
    $the_user = Array(
      "uid"=>$auth_user->member_id,
      "email"=>$auth_user->email,
      "uname"=> $auth_user->first_name . ' '. substr($auth_user->last_name,0,1),
      "affiliation"=>$auth_user->affiliation,
      "access"=>$auth_user->status
    );

    // Activate account if pending status and confirmation code valid
    if( $auth_user->status == ACCESS_PENDING){
      if(isset($confCode) && $confCode!='' && $confCode == $auth_user->confirm_code){
        $update_conf = ",status=2,confirm_code='OK'";
      }else{
        $the_user = NULL;
        $login_atts = $user_info->login_attempts + 1;
        $errors[] = "Confirmation Code is not valid. Please use the link in your confirmation email.";
      }
    }

  }elseif($auth_res->num_rows > 1){
    error_log("POPS Log in Error: User '$email' has more than one entry.");
    $errors[]= "Sorry, There has been a server error. Please try again later.";
  }else{
    $login_atts = $user_info->login_attempts + 1;
    $errors[]= "Username or Password is incorrect.";
  }

  query_db("UPDATE members SET login_attempts=?,last_fail_login_attempt=NOW()$update_conf WHERE member_id = ?", 'ii', Array($login_atts, $uid), "Authentication Update",$dbh);

  $auth_res->close();
  $info_res->close();
  $dbh->close();

  return $the_user;
}

/*Queries complete mapping of colors for heatmap values.
 *Input:
 * $table: Name of table containing the color scheme
 * $metric: Name of the metric (for alert purposes).
 *Output:
 *  -$color_map: array containing all color mappings.
*/
function query_colors($table, $metric){
  $result_color= query_db("SELECT * FROM $table",NULL,NULL,"Color $metric Query");
  $color_map=Array(Array(),Array(),Array());
  while( $color_row=$result_color->fetch_row()){
          $color_map[0][]=$color_row[0];
          $color_map[1][]=$color_row[1];
          $color_map[2][]=$color_row[2];
  }
  return $color_map;
}

//Validates email address (checks pattern and DNS)
function check_valid_email($email) {
    $valid = false;
    if( false != filter_var($email,FILTER_VALIDATE_EMAIL) ){
        list($username,$domain)=explode('@',$email);
        if(!checkdnsrr($domain,'MX')) {
            $valid= false;
        }
        $valid= true;
    }
    return $valid;
}

?>
