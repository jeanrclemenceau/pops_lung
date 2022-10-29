<?php
/*Filename: pops_header.php
 *Author: Jean R Clemenceau
 *Date Created: 03/11/2016
 *Contains functions that are used througout the Presicion Oncology Probe Set (POPSdbL) site.
 * -Links common styles and scripts
*/
require_once('pops_conf.php');
ini_set('date.timezone', 'America/Chicago');

/*Define mappers used througout website*/
$enetFeatureSetMap = Array('acgh'=>'copy_number','exp'=>'expression','met'=>'metabolomics','mut'=>'mutation','rna'=>'RNA_seq','rppa'=>'rppa','snp_cnv'=>'SNP_CNV','snp_mut'=>'SNP_MUT');

/*Prints links to the required files to setup the page:
 * -Adds page title.
 * -Adds favicon.
 * -Adds jQuery.
 * -Adds Bootstrap.
 * -Gets session data.
 *Assumes it will be called within the site's header.
*/
function add_setup($title=''){
    $title= ($title!= '')? ": $title" : '';
    print "<title>POPS-Lung$title</title>";
    print "<meta http-equiv='Content-Type' content='text/html;charset=ISO-8859-1'>";
    print "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    print "<link href='".POPS_HOME."favicon.ico' rel='shortcut icon' type='image/x-icon'>";

    print "<link rel='stylesheet' type='text/css' href='".POPS_HOME."javascript/bootstrap-3.3.6-dist/css/bootstrap.min.css'>";
    print "<link rel='stylesheet' type='text/css' href='".POPS_HOME."styles/pops_bootstrap.css'>";
    print "<script language='javascript' type='text/javascript' src='".POPS_HOME."javascript/jquery/jquery-1.11.1.min.js'></script>";
    print "<script language='javascript' type='text/javascript' src='".POPS_HOME."javascript/bootstrap-3.3.6-dist/js/bootstrap.min.js'></script>";

    return;
}

/*Prints a link to the specified style file, if it exists
 * -Assumes it will be called within the site's header.
*/
function add_styles($file=NULL){
    if( isset($file) && file_exists("styles/$file")){
        print "<link type='text/css' href='".POPS_HOME."styles/$file' rel='stylesheet'>";
    }
    return;
}

/*Prints a link to the specified javascript file, if it exists
 * -Assumes it will be called within the site's header.
*/
function add_scripts($file=NULL){
  if( isset($file) && file_exists("javascript/$file")){
      print "<script language='javascript' type='text/javascript' src='".POPS_HOME."javascript/$file'></script>";
  }
  return;
}

/*Prints the website's logos and navigation menu.
 *If logged in, It will display the user and a logout link.
 *Receives current user name and redirect location after logout
 * -Assumes HTML has been started and it is used within the <body> tag
 * -Assumes pops_style1.css has been included in the file.
 * -If logout is clicked, user will return to current site after login
*/
function print_navigation($index = NULL,$subIndex=NULL){
  $pages = array(
    "about POPS"=>"pops_about.php",
    "tutorial"=>"pops_tutorial.php",
    "query POPS-Lung"=>"pops-activity.php",
    "contact"=>"pops_contact.php"
  );
  $query_pages = array(
    "activity"=>"pops-activity.php",
    "elastic net"=>"pops-enet.php",
    "scanning KS"=>"pops-sks.php",
    "mutation"=>"pops-mutation.php"
  );

  // Output main header container
  echo<<<EOT
  <nav id="header" class="navbar navbar-inverse" role='navigation'>
    <div class='container-fluid'>

      <div class='navbar-header'>
        <button type='button' class='navbar-toggle collapsed' data-toggle='collapse' data-target='#navigation'>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class='navbar-brand' href='pops-activity.php'>
          <img class='img-responsive hdr_img' src="images/POPS_lung_logo.png"  alt='Presicion Oncology Probe Set - Lung Cancer'>
          <span class='hdr_name'>POPS-Lung</span>
        </a>
      </div>

      <div class='collapse navbar-collapse' id='navigation'>
        <ul class='nav navbar-nav navbar-left'>
EOT;

  // Output menu items
  foreach($pages as $title=>$link){
    $active = ($title == $index)? 'active' : '';
    $href = ($title != "query POPS-Lung")? POPS_HOME.$link : '#';
    $content = ucwords($title);

    //Deal with drop down menu option
    $content .= ($title == "query POPS-Lung")? "<span class='caret'/>" : '';
    $dropdown = ($title == "query POPS-Lung")? 'dropdown nav-dropdown' : '';
    $dropdown_toggle = ($title == "query POPS-Lung")? "data-toggle='dropdown'" : '';
    $dropdown_style = ($title == "query POPS-Lung")? 'display:inline-block !important;padding-right:0 !important;':'';

    echo "<li class='$dropdown'><a class='$active' style='$dropdown_style' $dropdown_toggle href='$href'>$content</a>";

    // Display dropdown menu for corresponding menu item
    if($dropdown != ''){
      echo "<ul class='dropdown-menu'>";
      foreach($query_pages as $q_title => $q_link){
        $q_active = ( isset($subIndex) && $q_title == $subIndex)? 'active active_query' : '';
        echo "<li><a class='$q_active' href='".POPS_HOME."$q_link'>".ucwords($q_title)."</a></li>";
      }
      echo "</ul>";
    }
    echo "</li>";
  }

  echo<<<EOT
        </ul>
        <ul class="nav navbar-nav navbar-right">
EOT;

  //Display session controls
  if(isset($_SESSION["uid"])){
    //TODO link to user settings
    echo "<li><a href='user_settings.php' title=\"{$_SESSION['uname']}'s User Settings\"><span class='glyphicon glyphicon-user'></span><span class='menu_icon_text hidden-sm'> {$_SESSION['uname']}</span></a></li>";
    //TODO link to log out
    echo "<li><a href='logout.php' title='Click to log out of session'><span class='glyphicon glyphicon-log-out'></span><span class='menu_icon_text hidden-sm'> Log Out</span></a></li>";
  }else{
    echo "<li><a href='index.php'><span class='glyphicon glyphicon-log-in' style='padding-right:5px;'></span>Log In</a></li>";
  }

  echo<<<EOT
        </ul>
      </div>
    </div>
  </nav>
EOT;

  return;
}

/*Prints the website's footer.
 * -Assumes pops_bootstrap.css has been included un the file.
*/
function print_footer(){
echo <<<EOT
  <div class='panel panel-footer'>
    Precision Oncology Probe Set - Lung Cancer
  </div>
  <div class='container-fluid text-center'>
    <img class='footer-img' src='images/utsw_logo_white.png' alt='UT Southwestern Medical Center'/>
  </div>
EOT;
    return;
}

/*Writes a javascript alert to the current file
*/
function alert($message, $close_window = false ){
    $close = ($close_window) ? 'window.close();' : '';
    echo "<script>alert('".$message."');$close</script>";
}

?>
