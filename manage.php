<?php
   require_once("inc/dbconnect.php");
   require_once("inc/funcs.php");
   require_once("inc/const.php");
   require_once("inc/auth_mgmt_funcs.php");
   // include("inc/session.php");
   session_start();
   $action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";
   $id = isset($_REQUEST["id"]) ? $_REQUEST["id"] : "";
   $posted = isset($_REQUEST["posted"]) ? $_REQUEST["posted"] : "";
   $search = isset($_REQUEST["search"]) ? $_REQUEST["search"] : "";
   $content = isset($_REQUEST["content"]) ? $_REQUEST["content"] : "";
   $e = "";


   // Handle POSTed edits and new record requests
   if (!empty($posted)) {
     switch ($action) {
       case "cpw" :
        // Change password
        switch (CheckMemberPWChangeSubmission()) {
          case 0 :
            // missing
            $e = "Sorry, something's missing";
            break;
          case 1 :
            $e = "Your current password is incorrect";
            break;
          case 2 :
            $e = "Sorry, your passwords don't match";
            break;
          case 3 :
            // It's all good
            UpdatePassword("users",$_SESSION[$session_name]["id"],$_POST["newpass1"]);
            $e = "<p>Your password was changed.</p>\r\n";
            $action = null;
            break;
          case 4 :
            $e = "Sorry, your new password doesn't meet the strength requirements:";
            $e .= "<br/>$pw_strength_req";
            break;
          default :
            break;
        }
      }
   }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title><?php echo $app_title;?></title>
  <link rel="stylesheet" href="index.css"/>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
  <?php echo GetHeader(); ?>
  <!-- <div id="top-bar">
  <?php // echo GetSearchBar($action); ?>
  </div> -->
  <div id="main">
  <?php
    switch ($action) {
      case "user" :
        echo GetItemEditForm("users",$id);
        break;
      case "cpw" :
        echo GetMemberPWChangeForm();
        echo (!empty($e)) ? "<p class=\"err\">$e</p>\r\n" : "";
        break;
      case "rpw" :
        echo GetReAuthenticationForm();
        break;
      default :
        // Any advisories
        if (!empty($e)) {
          echo $e;
        }
        break;
    }
   ?>
 </div>
 <div id="right">
   <?php
     echo "<p></p>";
   ?>
 </div>
 <div style="clear:both"></div>
</body>
</html>
