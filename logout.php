<?php
  require_once("inc/const.php");
  require_once("inc/navigation.php");
  require_once("inc/date_funcs.php");
  require_once("inc/utils.php");
  if (!isset($_SESSION))
    session_start();
  $res = SetSomething(
      "users",
      "previous_login",
      getDateForMysqlDateField(),
      "WHERE `id`=" . $_SESSION[$session_name]["id"]);
  if (!empty($res)) {
    echo $res;
  } else {
    if (isset($_SESSION[$session_name]))
      unset($_SESSION[$session_name]);
    $navigation["logout"]->visible = false;
    $navigation["login"]->visible = true;

    header("location:index.php");
    exit;
  }
?>
