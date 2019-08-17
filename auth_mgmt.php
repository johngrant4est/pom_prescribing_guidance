<?php
  /* Some stuff for Managing Authentication - pass resets etc
  */
  require_once("inc/auth_mgmt_funcs.php");
  require_once("inc/navigation.php");
  // require_once("../functions.php");
  // Member ID
  $id = isset($_REQUEST["id"]) ? $_REQUEST["id"] : "";
  // One Time Code
  $otc = isset($_REQUEST["otc"]) ? $_REQUEST["otc"] : "";
  $posted = isset($_POST["posted"]) ? $_POST["posted"] : "";
  $action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";
  $pw1 = isset($_POST["pw1"]) ? $_POST["pw1"] : "";
  $pw2 = isset($_POST["pw2"]) ? $_POST["pw2"] : "";
  $res = NULL;
  $err = NULL;
  $s = "";
  switch (TRUE) {
    case (!empty($posted)) :
      // Form submitted - check the PWs are ok
      switch (FormOK($pw1,$pw2)) {
        case 0 :
          $err = "<p style=\"color:#f00\">Something's Missing...</p>";
          $s = GetPassResetForm($id,$otc);
          break;
        case 1 :
          $err = "<p style=\"color:#f00\">Sorry, passwords don't match</p>";
          $s = GetPassResetForm($id,$otc);
          break;
        case 2 :
          // All good
          $res = UpdatePassword("members",$id,$pw1);
          $s = "<p>Your password has been changed.<br/>\r\n";
          $s .= "You can now <a href=\"login.php\">login</a></p>\r\n";
          // Delete the authentication rule
          DeleteAuthentication($id);
          break;
        case 3 :
          // Doesn't meet strength requirements
          $err = "<p style=\"color:#f00\">Sorry, your password doesn't meet the strength requirements</p>\r\n";
          $s = GetPassResetForm($id,$otc);
          break;
        default :
          break;
      }
      break;
    case empty($id) :
    case empty($otc) :
        $err = "ID or OTC missing - can't perform this operation";
        break;
  }
  // Handle deletes
  if ($action == "delete") {
    DeleteAuthentication($id);
    header("location:pass_resend.php");
    exit(0);
  }
  // Are we just checking the otc expiry ?
  if (empty($posted) && (empty($id) || empty($otc))) {
    $err = "ID or OTC missing - can't perform this operation";
  } else if (empty($posted)) {
    // Check that the one-time code hasn't expired
    $res = CheckOTC($id,$otc);
    switch ($res) {
      case 0 :
        // expired
        $s = "<p>Sorry, the link has expired. You can <a href=\"login.php?action=rpw\"> try again</a>.</p>";
        break;
      case 1 :
        $s = GetPassResetForm($id,$otc);
        break;
      case 2 :
        // ID and OTC don't match - probably a hack
        header("location:403.php");
        exit(0);
      case -1 :
        $s = "<p>Zero or multiple entries for ID $id - Please contact the developer</p>";
        break;
    }
  }
?>
<!DOCTYPE html>
<html>
<head>
  <title>SCATA Authentication Management</title>
  <link rel="stylesheet" href="index.css" type="text/css"/>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SCATA Authentication Management</title>
</head>
<body>
  <?php echo GetHeader(); ?>
    <div id="main">
      <?php
        if (!empty($err)) echo "<p>$err</p>\r\n";
        echo $s;
        // Debugging
        // echo "<p>Res : $res</p>";
        // echo "<p>Posted : $posted</p>";
      ?>
    </div>
</body>
</html>
