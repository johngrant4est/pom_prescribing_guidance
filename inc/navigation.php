<?php
  require_once("classes.php");
  $navigation = array();
  $navigation["home"] = new NavigationElement("index","home","");
  $navigation["about"] = new NavigationElement("index","about","action=content&amp;id=1");
  $navigation["login"] = new NavigationElement("login","login","",true);
  $navigation["logout"] = new NavigationElement("logout","logout","",false);

?>
