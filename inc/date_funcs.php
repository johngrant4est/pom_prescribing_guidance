<?php

/**
 * @author Lunaria (Scotland) Limited
 * @copyright 2009
 */

   function us_date($value)
   // Returns a timestamp from a string.  Assumes en_GB format where ambiguous.
   {
     // If it looks like a UK date dd/mm/yy, reformat to US date mm/dd/yy so strtotime can parse it.
     $reformatted = preg_replace("/^\s*([0-9]{1,2})[\/\. -]+([0-9]{1,2})[\/\. -]+([0-9]{1,4})/", "\\2/\\1/\\3", $value);
     return strtotime($reformatted);
   }

   function getDateForMysqlDateField() {
     $date = getDate();
     foreach($date as $item=>$value) {
       if ($value < 10)
           $date[$item] = "0".$value;
     }
   return $date['year']."-".$date['mon']."-".$date['mday']." ".$date['hours'].":".$date['minutes'].":".$date['seconds'];
  }

  function GetDateTimePlus24H() {
    // takes the current datetime and adds 24H for e.g. an expiry date
     $date = new DateTime();
     $date->add(new DateInterval('P1D'));
     return date_format($date,"Y-m-d H:i:s");

  }

  function SQLDateToUKDate($SQLDate,$format) {
  	// takes a SQL date/datetime Y-m-d hh:mm:ss and convert to d-M Y
  	return date($format,strtotime($SQLDate));
  }

  function UKDateToSQLDate($ukdate) {
  	return date('Y-m-d',us_date($ukdate));
  }

  function GetTodaysDateAndTime($format='d-M Y H:i') {
    // At some point, I should format this to actually pay attention to the passed format argument
  	$date = getdate();
  	$now = $date["weekday"] . " " . $date["mday"]  . " " . $date["month"] . " " . $date["year"];
	  $now .= " " . $date["hours"] . ":";
	  $now .= ($date["minutes"] <= 9) ? "0" . $date["minutes"] : $date["minutes"];
    $now .= ":";
    $now .= ($date["seconds"] <= 9) ? "0" . $date["seconds"] : $date["seconds"];
    return $now;

  }

  function GetDateForFileName() {
    $date = getdate();
    $now = $date["year"] . "-";
    $now .= ($date["mon"] <= 9) ? "0" . $date["mon"] : $date["mon"];
    $now .= "-";
    $now .= ($date["mday"] <= 9) ? "0" . $date["mday"] : $date["mday"];
    $now .= "_";
    $now .= ($date["hours"] <= 9) ? "0" . $date["hours"] : $date["hours"];
    $now .= ($date["minutes"] <= 9) ? "0" . $date["minutes"] : $date["minutes"];
    return $now;
  }
?>
