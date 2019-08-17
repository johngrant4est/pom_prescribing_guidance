<?php

/**
 * @author Grant Forrest
 * @copyright 2007
 */
  require_once("auth_db.php");
  require_once("dbconnect.php");
    
  function auth($user='',$pass='') {
    // return vals: 1=success, 0=wrong pass 2=username notfound, -1=no data
    // if $authdata is filled, the user is authenticated
    if (isset($_SESSION['scata_authdata'])) {
      return 1;
    }
    if (empty($user) && empty($pass))
      return -1;
      
    // if a user is specified,
    if (!empty($user)) {
      // authenticate name and password against database
      return (auth_db($user,$pass));
	}
  }


function GetLoginForm($error,$layout,$action) {
  // returns the login form to the browser
  // $layout can be either "full" or "minimal" for the mini-form in the sidebar
  $input_box_width = ($layout=="minimal") ? "140" : "200";
  $cellpadding = ($layout=="minimal") ? 0 : 3;
  
  $s = "<form id=\"scata_memb_login\" method=\"post\" action=\"$action\" style=\"margin:0px;\">";
  $s .= "<table style=\"border:none\">";
  if (!empty($error)) {
    $s .= "<tr>\r\n";
    $s .= "<td colspan=\"2\"><p class=\"flat\" style=\"font-size:10px;color:#f00;text-align:center\">$error</p></td>\r\n";
    $s .= "<tr>\r\n";
  }
  $s .= "<tr>\r\n";
  $s .= "<td><p class=\"flat\" style=\"font-size:12px;text-align:right\">email</p></td>";
  $s .= "<td><input type=\"text\" name=\"scata_username\" value=\"";
  if (isset($_POST['scata_username']))
    $s .= $_POST['scata_username'];
  $s .=  "\" style=\"width:" . $input_box_width . "px\"/></td>";
  $s .= "</tr>\r\n";
  $s .= "<tr>\r\n";
  $s .= "<td><p class=\"flat\" style=\"font-size:12px;text-align:right\">pass</p></td>";
  $s .= "<td><input type=\"password\" name=\"scata_pass\" style=\"width:" . $input_box_width . "px;\"/></td>";
  $s .= "</tr>\r\n";
  $s .= "<tr>\r\n";
  $s .= "<td colspan=\"2\" style=\"text-align:center\"><input type=\"submit\" name=\"login_posted\" value=\"login\"/>";
  $s .= "<span style=\"font-size:10px;margin-left:8px;\"><a href=\"pass_resend.php\" class=\"smalllink\">forgotten?</a></span></td>";
  $s .= "</tr>\r\n";
  $s .= "</table>\r\n";
  $s .= "</form>\r\n";
  return $s;

}

?>