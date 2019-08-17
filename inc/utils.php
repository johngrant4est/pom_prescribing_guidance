<?php
require_once("navigation.php");
require_once("date_funcs.php");
require_once("dbconnect.php");

function GetSomething($table,$what,$where) {
  global $dbh;
  $sql = "SELECT `$what` FROM `$table` WHERE $where";
  $stmt = $dbh->query($sql);
  // should really check that query returns just one result
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  return $row[$what];
}

function SetSomething($table,$what,$value,$where) {
  global $dbh;
  $sql = "UPDATE `$table` SET `$what`=$value $where";
  try {
    $dbh->query($sql);
  } catch (Exception $e) {
    return $e->getMessage() . "<br/>SQL: $sql";
  }
  return;
}

function GetHeader() {
  global $version;
  global $last_update;
  global $app_title;
  global $session_name;
  global $navigation;
  $s = "";
  $s .= "<div class=\"header\">\r\n";
  $s .= "<h3 style=\"display:inline\">" . $app_title . "</h3>\r\n";
  $s .= "<p style=\"display:inline\">\r\n";
  if (isset($_SESSION[$session_name])) {
    $navigation["login"]->visible = false;
    // show the "logout" nav NavigationElement
    $navigation["logout"]->visible = true;
  }
  foreach ($navigation as $nav) {
    if ($nav->visible) {
      $s .=  $nav->GetLink("nav");
    }
  }
  $s .= "</p>\r\n";
  $s .= "<p class=\"logged-in\">" . GetLoggedInUser() . "</p>\r\n";
  $s .= "</div>\r\n";
  $s .= "<div style=\"clear:both\"></div>\r\n";
  return $s;
}

function generatePassword ($length = 8)
{
  // start with a blank password
  $password = "";
  // define possible characters
  $possible = "0123456789bcdfghjkmnpqrstvwxyz";
  // set up a counter
  $i = 0;
  // add random characters to $password until $length is reached
  while ($i < $length) {
    // pick a random character from the possible ones
    $char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
    // we don't want this character if it's already in the password
    if (!strstr($password, $char)) {
      $password .= $char;
      $i++;
    }
  }
  // done!
  return $password;
}

function GetLoggedInUser() {
  global $dbh;
  global $session_name;
  if (!isset($_SESSION[$session_name]))
    return "";
  $s =  "Hi,&#160;<a class=\"small\" href=\"manage.php?action=user&amp;id=";
  $s .= $_SESSION[$session_name]["id"] . "\">" . $_SESSION[$session_name]["fname"];
  $s .= "</a>\r\n";
  return $s;
}

function iGetColumnNamesAndComments($dbh,$table) {
  // returns an array $array["FieldName"] = $comment
  $a = array();
  $sql = "SHOW FULL COLUMNS FROM `$table`";
  $res = $dbh->query($sql);
  while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
      $a[$row["Field"]] = $row["Comment"];
  }
  return $a;
}

function GetItemEditForm($table,$id) {
   global $dbh;
   $fields_to_skip = array("id");
   $close_action = "onclick=\"window.document.location.href='index.php';\"";
   $sql = "SELECT * FROM `$table` WHERE `id`=$id";
   $stmt = $dbh->query($sql);
   // This function assumes only one
   $row = $stmt->fetch(PDO::FETCH_ASSOC);
   $fields = iGetColumnNamesAndComments($dbh,$table);
   $s = "<form method=\"post\" action=\"" . $_SERVER["PHP_SELF"] . "\" id=\"item_edit_form\">\r\n";
   $s .= "<input type=\"hidden\" id=\"id\" value=\"$id\"/>\r\n";
   $s .= "</form>\r\n";
   $s .= "<table>\r\n";
   foreach ($fields as $fn=>$label) {
     if (in_array($fn,$fields_to_skip)) continue;
     $s .= "<tr>\r\n";
     $s .= "<td class=\"label\"><p class=\"label\">$label</p></td>\r\n";
     // Data
     $s .= "<td class=\"data\"><p class=\"data\">\r\n";
     switch ($fn) {
       // constrain your field types here
       case "last_login" :
       case "previous_login" :
         $s .= SQLDateToUKDate($row[$fn],"d-M Y H:i");
         break;
       case "ip" :
         // Read-$read_only
         $s .= $row[$fn];
         break;
       case "password" :
          $s .= "<a class=\"small\" href=\"manage.php?action=cpw\">change</a>\r\n";
          break;
       default :
         $s .= "<input type=\"text\" size=\"30\" id=\"$fn\" value=\"" . $row[$fn] . "\"/>\r\n";
         break;
     }
     $s .= "</p></td>\r\n";
     $s .= "</tr>\r\n";
   }
   $s .= "<tr><td colspan=\"2\"><p class=\"flat\" style=\"text-align:center\">\r\n";
 	 $s .= "<input type=\"submit\" id=\"posted\" value =\"update\" style=\"cursor:pointer\"/>\r\n";
 	 $s .= "<input type=\"button\" id=\"btn_close\" value =\"close\" $close_action style=\"cursor:pointer\"/>\r\n";
 	 $s .= "</p></td></tr>\r\n";
   $s .= "</table>\r\n";
   return $s;
 }

 function GetAtoZLinks($letter = null) {
   // returns an A to Z line of links that select acronyms beginning with that letter
   $alphabet = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
   $s = "<p>";
   foreach ($alphabet as $l) {
    $s .= ($l != $letter) ? "<a href=\"" . $_SERVER["PHP_SELF"] . "?action=search&amp;search=" . $l . "\" class=\"letter-norm\">" . $l . "</a>" : "<span class=\"letter-bold\">" . $l . "</span>";
    $s .= " ";
   }
   $s .= "</p>\r\n";
   return $s;
 }
 function GetVersionInfo() {
   global $dbh;
   global $table;
   $version_info = GetSomething("content","content","`id`=2");
   $last_update = SQLDateToUKDate(GetSomething("content","last_update","`id`=2"),'d-M Y');
   switch ($table) {
     case "drug_instructions_fife" :
       $s = "Fife";
       break;
    case "drug_instructions_clean" :
      $s = "Cornwall";
      break;
     }
   return "<p class=\"version-info\">$version_info&#160;$last_update&#160;using table: $s</p>\r\n";
   }
?>
