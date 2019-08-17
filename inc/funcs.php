<?php
  /*
  @copyright Dr J Grant Forrest jgrant@lunaria.co.uk
  See Readme for license and provenance
  */
  require_once("dbconnect.php");
  require_once("utils.php");
  require_once("date_funcs.php");

  function GetDoseInstructionTable($table,$search=null) {
    global $dbh;
    global $session_name;
    $s = "";
    $toggle = true;
    // Buttons
    $buttons = array();
    $buttons[] = new ControlButton(
      "img/btn_add.png",
      "add",
      "add to list",
      "button",
      "click to add to the list",
      " onclick=\"window.document.location.href='",
      $_SERVER["PHP_SELF"]);
    if (!isset($_SESSION)) session_start();
    if (isset($_SESSION[$session_name])) {
      // extra goodies for admins
      $buttons[] = new ControlButton(
        // edit button
        "img/btn_edit_green.png",
        "edit",
        "edit this drug",
        "edit button",
        "click to edit this drug",
        " onclick=\"window.document.location.href='",
        $_SERVER["PHP_SELF"]);
    }
    // manage searches
    if (!empty($search)) {
      $where = " WHERE `drug_name` LIKE ?";
    } else $where = "";
    $sql = "SELECT * FROM $table $where ORDER BY `drug_name` ASC";
    $stmt = $dbh->prepare($sql);
    // $stmt->bindParam(1,$search . "%");
    try {
      $stmt->execute(array("$search%"));
    } catch (Exception $e) {
      return $e->getMessage();
    }

    $s .= "<table>\r\n";
    $s .= "<tr><th><p class=\"flat\">Drug Name</p></th>\r\n";
    // $s .= "<th><p class=\"flat\">Instruction</p></th>\r\n";
    // cols for editing
    $s .= "<th><p class=\"flat\">&#160;</p></th>\r\n";
    $s .= "<th><p class=\"flat\">&#160;</p></th>\r\n";
    $s .= "<th><p class=\"flat\">&#160;</p></th>\r\n";
    $s .= "</tr>\r\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $s .= "<tr class=\"";
      $s .= $toggle ? "col1" : "col2";
      $s .= "\">\r\n";
      $s .= "<td><p class=\"flat\">" . $row["drug_name"] . "</p></td>\r\n";
      // $s .= "<td><p class=\"flat\">" . $row["description"] . "</p></td>\r\n";
      foreach ($buttons as $btn) {
        $params =  "?action=$btn->action&amp;id=" . $row["id"];
        $s .= "<td class=\"data\">" . $btn->GetButton($params) . "</td>\r\n";
      }
      /*
      $s .= "<td><a href=\"" . $_SERVER["PHP_SELF"] . "?id=" . $row["id"] . "&amp;action=edit\">";
      $s .= "<img src=\"img/b_edit.png\" alt=\"edit\" title=\"click to edit\"/></a></td>\r\n";
      $s .= "<td><a href=\"" . $_SERVER["PHP_SELF"] . "?id=" . $row["id"] . "&amp;action=delete\">";
      $s .= "<img src=\"img/b_drop.png\" alt=\"delete\" title=\"click to delete\"";
      $s .= " onclick=\"if (confirm('Really Delete this Item?')==true) { window.document.location.href='";
      $s .= $_SERVER["PHP_SELF"] . "?action=delete';} else { return false; }\"/></a></td>\r\n";
      $s .= "<td><img src=\"img/b_insrow.png\" alt=\"add to list\" title=\"add to list\"";
      $s .= " onclick=\"javascript:window.document.location.href='" . $_SERVER["PHP_SELF"] . "?action=add&amp;id=" . $row["id"] . "';\" style=\"cursor:pointer\"";
      $s .= "/></td>\r\n";
      */
      $s .= "</tr>\r\n";
      $toggle = !$toggle;
    }
    $s .= "</table>\r\n";
    return $s;
  }

  function CleanUp() {
    // legacy function to imrove data quality
    global $dbh;
    $sql = "SELECT * FROM `drug_instructions` WHERE `drug_name`=''";
    $stmt = $dbh->query($sql);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      // get the previous row's description
      $id = $row["id"] - 1;
      $des = GetSomething("drug_instructions","description","`id`=" . $id);
      // add the current row's description.
      $des .= " ";
      $sql2 = "UPDATE `drug_instructions_clean` SET `description` = '" . $des . $row["description"] . "'";

      $sql2 .= " WHERE `id`=" . $id;
      // return $sql2;
      $dbh->query($sql2);
    }
    return "<p>Finished</p>";
  }

  function GetDrugEditForm($table,$id) {
    global $dbh;
    if (empty($id)) return;
    $fields_to_skip = array("id","drug_class","notes","reference");
    $read_only_fields = array("id");
    $sql = "SELECT * FROM `$table` WHERE `id`=$id";
    $stmt = $dbh->query($sql);
    $cols = GetFieldNamesAndLabels("drug_instructions");
    $s = "<form name=\"edit_form\" id=\"edit_form\" method=\"POST\" action=\"" . $_SERVER["PHP_SELF"] . "\">\r\n";
    $s .= "<input type=\"hidden\" id=\"id\" value=\"$id\"/>\r\n";
    $s .= "<input type=\"hidden\" name=\"action\" value=\"edit\"/>\r\n";
    $s .= "<table>\r\n";
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    foreach ($cols as $fn=>$label) {
      if (in_array($fn,$fields_to_skip)) continue;
      $s .= "<tr>\r\n";
      $s .= "<td class=\"label\"><p class=\"flat\">" . $label . "</p></td>\r\n";
      $s .= "<td class=\"data\">\r\n";
      $s .= "<p class=\"flat\">\r\n";
      switch ($fn) {
        case "drug_name" :
          $s .= "<input type=\"text\" name=\"$fn\" id=\"$fn\" value=\"" . $row[$fn] . "\"";
          $s .= "/>\r\n";
          break;
        case "description" :
          $s .= "<textarea id=\"description\" name=\"description\" rows=\"4\" cols=\"80\">";
          $s .= $row[$fn];
          $s .= "</textarea>\r\n";
          break;

      }
      $s .= "</p></td>\r\n";
      $s.= "</tr>\r\n";
    }
    $s .= "<tr><td colspan=\"2\"><p><input type=\"submit\" id=\"posted\" name=\"posted\" value=\"submit\"/>\r\n";
    $s .= "<input type=\"button\" name=\"btn_cancel\" id=\"btn_cancel\" value=\"cancel\" onclick=\"javascript:window.document.location.href='" . $_SERVER["PHP_SELF"] . "'\"/>\r\n";
    $s .= "</p></td></tr>\r\n";

    $s .= "</table>\r\n";
    $s .= "</form>\r\n";
    return $s;


  }

  function UpdateDrug($table,$id) {
    global $dbh;
    // $cols = GetFieldNamesAndLabels($table);
    $sql = "UPDATE `$table` SET `drug_name` = '" . $_POST["drug_name"] . "'";
    $sql .= ",`description`='" . $_POST["description"] . "' WHERE `id`=$id";
    $dbh->query($sql);
    return;

  }

  function GetFieldNamesAndLabels($table) {
    	// returns array of $fieldname=>$label
    	global $dbh;
    	$fields = array();
    	$sql = "SHOW FULL COLUMNS FROM `$table`";
    	$stmt = $dbh->query($sql);
    	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    	  $fields[$row["Field"]] = $row["Comment"];
    	}
    	return $fields;
    }

    function DeleteRecord($table,$id) {
      global $dbh;
      $sql = "DELETE FROM `$table` WHERE `id`=$id";
      $dbh->query($sql);
      return;
    }

    function GetNewDrugForm($table) {
      global $dbh;
      $fields_to_skip = array("id","drug_class","notes","reference","updated","updated_by");
      $s = "<form id=\"new_record_form\" name=\"new_record_form\" method=\"post\" action=\"";
      $s .= $_SERVER["PHP_SELF"] . "?action=new\">\r\n";
      // $s .= "<input type=\"hidden\" name=\"action\" value=\"new\"/>\r\n";
      $s .= "<table style=\"border:1 px solid #ccc\">\r\n";
      $cols = GetFieldNamesAndLabels($table);
      foreach ($cols as $fn=>$label) {
        if (in_array($fn,$fields_to_skip)) continue;
        $s .= "<tr>\r\n";
        // Labels
        $s .= "<td class=\"label\"><p class=\"flat\" style=\"text-align:right\">$label</p></td>\r\n";
        // Inputs
        $s .= "<td class=\"data\"><p class=\"flat\">\r\n";
        switch ($fn) {
          case "drug_name" :
            // Simple Text Inputs
            $s .= "<input type=\"text\" id=\"$fn\" name=\"$fn\" size=\"30\"/>\r\n";
            break;
          case "description" :
            // Memo
            $s .= "<textarea id=\"$fn\" name=\"$fn\" rows=\"5\" cols=\"80\"></textarea>\r\n";
            break;
        }
        $s .= "</p></td>\r\n";
        $s .= "</tr>\r\n";
      }
      $s .= "<tr><td colspan=\"2\" style=\"text-align:center\"><p class=\"flat\">\r\n";
      $s .= "<input type=\"submit\" name=\"posted\" id=\"posted\" value=\"create new record\" style=\"cursor:pointer\"/>";
      $s .= "<input type=\"button\" name=\"bt_cancel\" id=\"btn_cancel\" value=\"cancel\" style=\"cursor:pointer\"";
      $s .= " onclick=\"javascript:window.document.location.href='" . $_SERVER["PHP_SELF"] . "';\"/>";
      $s .= "</p></td></tr>\r\n";
      $s .= "</table>\r\n";
      $s .= "</form>\r\n";
      return $s;
    }

    function CreateRecord($table) {
      global $dbh;
      // $cols = GetFieldNamesAndLabels($table);
      // $fields_to_skip = array("id","drug_class");
      $stmt = $dbh->prepare("INSERT INTO `$table` (`drug_name`,`description`) VALUES (:drug_name,:description)");
      $stmt->bindParam(':drug_name',$_POST["drug_name"]);
      $stmt->bindParam(':description',$_POST["description"]);
      $stmt->execute();

      // $sql = "INSERT INTO `$table` (`drug_name`,`description`) VALUES ('" . $_POST["drug_name"] . "','" . $_POST["description"] . "')";
      // $dbh->query($sql);
      $id = $dbh->lastInsertId();
      return "<p>Inserted Record $id</p>";

    }

    function GetSearchBar($action) {
      // returns a search input and a button for creating new records
      global $session_name;
      $s = "<p class=\"small\">Start typing to match a drug name...</p>\r\n";
      $s .= "<form id=\"search_form\" name=\"search_form\" method=\"post\" action=\"";
      $s .= $_SERVER["PHP_SELF"] . "\">\r\n";
      $s .= "<p style=\"display:inline\"><input type=\"hidden\" name=\"action\" value=\"search\"/>\r\n";
      $s .= "<input type=\"text\" id=\"search\" name=\"search\">\r\n";
      $s .= "<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"search\" style=\"cursor:pointer\" title=\"search for a drug by name\"/></p>\r\n";

      // Just a plain input button for creating new records
      if (($action != "new") && isset($_SESSION[$session_name])) {
        $s .= "<p style=\"display:inline\"><input type=\"button\" id=\"btn_new\" value=\"create new\"";
        $s .= " style=\"cursor:pointer\" onclick=\"javascript:window.document.location.href='";
        $s .=  $_SERVER["PHP_SELF"] . "?action=new';\" title=\"create a new record\"/></p>\r\n";
      }
      $s .= "</form>\r\n";
      return $s;
    }

    function AddDrugToSession($id) {
      $_SESSION["drug_ids"][$id] = $id;
      return;
    }

    function GetCurrentDrugList($table) {
      // Pulls the currently selected
      $toggle = true;
      $buttons[] = new ControlButton(
        // Delete Button
        "img/b_drop.png",
        "delete",
        "delete from list",
        "delete button",
        "click to delete from the list",
        " onclick=\"window.document.location.href='",
        $_SERVER["PHP_SELF"]);

      $pdf_btn = new ControlButton(
        "img/create_pdf.png",
        "create PDF",
        "create PDF",
        "button",
        "click to export the selected list to a PDF",
        " onclick=\"window.document.location.href='",
        $_SERVER["PHP_SELF"]);

      $s = "<h3>Current Drug List</h3>\r\n";
      if (empty($_SESSION["drug_ids"])) {
        $s .= "<p>No drugs added to list</p>\r\n";
        return $s;
      }
      $s .= "<table>\r\n";
      $s .= "<tr><th><p class=\"flat\">Drug Name</p></th>\r\n";
      $s .= "<th><p class=\"flat\">Dosage Instructions</p></th>\r\n";
      $s .= "<th>&#160;</th>\r\n";
      $s .= "</tr>\r\n";
      foreach ($_SESSION["drug_ids"] as $id) {
        $s .= "<tr class=\"";
        $s .= $toggle ? "col1" : "col2";
        $s .= "\"><td><p class=\"flat\"><b>";
        $s .= GetSomething($table,"drug_name","`id`=$id");
        $s .= "</b></p><td><p class=\"flat\">";
        $s .= GetSomething($table,"description","`id`=$id");
        $s .= "</p></td>\r\n";
        foreach ($buttons as $btn) {
            $s .= "<td>" . $btn->GetButton("?action=remove&amp;id=$id") . "</td>\r\n";
        }
        $toggle = !$toggle;
      }
      $s .= "<tr><td colspan=\"3\"><p style=\"text-align:center\">\r\n";
      $s .= $pdf_btn->GetButton("?action=export");
      $s .= "</table>\r\n";
      return $s;
    }

  function RemoveFromSession($id) {
      foreach ($_SESSION["drug_ids"] as $did) {
        if ($did == $id) {
          unset($_SESSION["drug_ids"][$id]);
        }
      }
      return "<p>ID $id removed</p>";
    }

  function GetContent($id) {
    global $dbh;
    $sql = "SELECT `content` FROM `content` WHERE `id`=$id";
    $stmt = $dbh->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row["content"];
  }

  function CreatePOMPDF($table) {
    global $session_name;
    $default_cell_height = 5;
    $pdf = new FPDF();
    $pdf->AddPage();
  	$pdf->SetFont('Arial','B',12);
    $pdf->SetFillColor(220);
    $pdf->Cell(40,$default_cell_height+5,'Drug Name',0,0,'L',true);
    $pdf->Cell(0,$default_cell_height+5,'Dosage Instructions',0,1,'L',true);
    if (empty($_SESSION)) session_start();
    $toggle = 1;
    foreach ($_SESSION["drug_ids"] as $id) {
      $drug_name = GetSomething($table,"drug_name","`id`=$id");
      $drug_instructions = GetSomething($table,"description","`id`=$id");
      if ($toggle) {
        $pdf->Cell(40,$default_cell_height,$drug_name,0,0,'L',false);
        $pdf->MultiCell(0,$default_cell_height,$drug_instructions,0,'L',false);
      } else {
        $pdf->SetFillColor(240);
        $pdf->Cell(40,$default_cell_height,$drug_name,0,0,'L',true);
        $pdf->MultiCell(0,$default_cell_height,$drug_instructions,0,'L',true);
      }
      $toggle = !$toggle;
    }
    $fname = "files/" . GetDateForFileName() . ".pdf";
    $pdf->output($fname,"F");
    return "<p class=\"advisory\">PDF Created : <a href=\"$fname\" class=\"small\">$fname</a></p>\r\n";
  }

 ?>
