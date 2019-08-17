<?php
  class NavigationElement {
    public $page;
    public $text;
    public $params;
    public $visible;
    function __construct($page,$text,$params=null,$visible=true) {
      $this->page = $page;
      $this->text = $text;
      $this->params = $params;
      $this->visible = $visible;
    }
    function GetLink($css_class) {
      $s = "<a href=\"" . $this->page . ".php";
      if (!empty($this->params))
        $s .= "?" . $this->params;
      // Any CSS styling
      $s .= "\"";
      $s .=  (!empty($css_class)) ? " class=\"$css_class\"" : "";
      // Set visibility
      $s .= ($this->visible) ? "" : " style=\"visibility:hidden\"";
      $s .= ">" . $this->text . "</a>\r\n";
      return $s;
    }
  }

  class ControlButton {
    public $img;
    public $action;
    public $alt;
    public $style;
    public $title;
    public $js_action;
    public $js_link;
    function __construct($img,$action,$alt,$style,$title,$js_action,$js_link=null) {
      $this->img = $img;
      $this->action = $action;
      $this->alt = $alt;
      $this->style = $style;
      $this->title = $title;
      $this->js_action = $js_action;
      $this->js_link = $js_link;
    }

    function GetButton($params) {
      // params are added to the $js_link
      $s = "<img src=\"$this->img\" alt=\"$this->alt\" title=\"$this->title\"";
      $s .= " class=\"$this->style\" $this->js_action$this->js_link$params';\"/>\r\n";
      return $s;
    }
  }
 ?>
