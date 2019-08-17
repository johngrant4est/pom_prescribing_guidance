<?php

/**
 * @author J Grant Forrest
 * @copyright 2013
 */
 require_once("inc/auth_mgmt_funcs.php");
 require_once("inc/funcs.php");
 require_once("inc/const.php");
 require_once("inc/mail_funcs.php");
 // require_once("inc/auth_db.php");
 $error = null;
 $res = null;
 // Generic login page
 // This just takes an authenticated user to the protected area

 $login = isset($_REQUEST["login"]) ? $_REQUEST["login"] : null;
 $action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";
 $posted = isset($_REQUEST["posted"]) ? $_REQUEST["posted"] : "";
 $email = isset($_POST["email"]) ? $_POST["email"] : "";

 switch (TRUE) {
   case (!empty($login)) :
     // look for loginform variables
     $user = isset($_POST['username']) ? $_POST['username'] : "";
     $pass = isset($_POST['password']) ? $_POST['password'] : "";
     $res = auth($user,$pass);
     switch ($res) {
       case 0 : // incorrect pass for username
         $error = "password incorrect";
         break;
       case 1 :  // success
         header("Location:index.php");
         exit(0);
         break;
       case 2 :  // password change required
         $error = "Hmm, having trouble finding that email - did you type it correctly ?";
         break;
       case 3 :  // nothing entered for email
         $error = "please enter email";
         break;
       case 4 :  // no Password
         $error = "please enter your password";
         break;
       case 5 : // no email or password
         $error = "please enter your email and password";
         break;
       default : ;
     }
     break;
  case (!empty($posted)) :
    // Forgotten password
    if (!empty($email)) {
      if (!ValidateEmail($email)) {
        $error = "Sorry, the email " . $email . " is invalid.";
      } else {
        $res = SendPasswordResetLink($email);
        // $res is an array(0,1,2)
        // 0 => Duplicate User
        // 1 => No username
        // 2 => Result of MyPHPMailer()
        foreach ($res as $i=>$err) {
          if (!empty($res[$i])) {
            switch ($i) {
                case 0 :
                case 1 :
                    $error = $res[$i];
                    break;
                case 2 :
                    if ($res[2] == "sent") {
                       header("location:login.php?action=reauth");
                       exit;
                    } else $error = $res[2];
                    break;
            }
          }
        }
      }
    }
    break;
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
  <div id="left">
<?php
  switch ($action) {
    case "rpw" :
      echo GetReAuthenticationForm();
      if (!empty($error)) echo "<p class=\"err\">$error</p>\r\n";
      break;
    case "reauth" :
      echo "<p>Please check your email (including junk folder) for further instructions</p>\r\n";
      break;
    default :
      echo GetLoginForm($error,"standard",'login.php');
      break;
  }

?>
 </div>
</body>
</html>
