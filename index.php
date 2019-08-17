<?php
   require_once("inc/dbconnect.php");
   require_once("inc/funcs.php");
   require_once("inc/const.php");
   require_once("inc/fpdf-1.81/fpdf.php");

   session_start();
   $action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";
   $id = isset($_REQUEST["id"]) ? $_REQUEST["id"] : "";
   $posted = isset($_REQUEST["posted"]) ? $_REQUEST["posted"] : "";
   $search = isset($_REQUEST["search"]) ? $_REQUEST["search"] : "";
   $content = isset($_REQUEST["content"]) ? $_REQUEST["content"] : "";
   $letter = isset($_REQUEST["letter"]) ? $_REQUEST["letter"] : "";

   // Save the result of the last search/filter in the Session
   $_SESSION["filter"] = $search;
   if (empty($search) && !empty($_SESSION["filter"])) {
     $search = $_SESSION["filter"];
   }

   switch ($action) {
     case "clean" :
       // legacy data cleansing
       $res = CleanUp();
       break;
     case "delete" :
       DeleteRecord($table,$id);
       $action = null;
       $res = "<p>Item $id was deleted</p>\r\n";
       break;
    case "add" :
        // Add a drug to the session variable
        AddDrugToSession($id);
        $action = null;
        break;
    case "remove" :
        $res = RemoveFromSession($id);
        break;
    case "export" :
        $e = CreatePOMPDF($table);
        break;

   }
   // Handle POSTed edits and new record requests
   if (!empty($posted)) {
     switch ($action) {
       case "edit" :
         // update the item
         UpdateDrug($table,$id);
         $action = null;
         $res = "<p>Drug Updated</p>\r\n";
         break;
      case "new" :
        $res = CreateRecord($table);
        $action = null;
        break;
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
  <div id="top-bar">
    <?php
      echo GetSearchBar($action);
      echo GetAtoZLinks("");
      // Any advisories
      if (!empty($e)) {
        echo $e;
      }
    ?>
  </div>
  <div id="left">
  <?php
    switch ($action) {
      case "clean" :
        // legacy
        break;
      case "edit" :
        echo GetDrugEditForm($table,$id);
        break;
      case "new" :
        echo "<p>Create New Record</p>\r\n";
        echo GetNewDrugForm($table);
        break;
      case "content" :
        // Show some HTML blurb
        echo GetContent($id);
        break;
      case "search" :
        echo GetDoseInstructionTable($table,$search);
        break;
      default :
        break;
    }
   ?>
 </div>
 <div id="right">
   <?php
      if (($action != "new") && ($action != "edit") && ($action != "content"))
        echo GetCurrentDrugList($table);
   ?>
 </div>
 <div style="clear:both"></div>
 <div id="footer"><?php echo GetVersionInfo(); ?></div>
</body>
</html>
