<?php
  require_once("const.php");
  session_start();
  // if the user isn't authenticated, kick them back to the login page
  if (!isset($_SESSION[$session_name])) {
      header("Location:login.php");
      exit;
  }
?>
