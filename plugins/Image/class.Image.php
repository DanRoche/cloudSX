<?php

//===============================================
// plugins to display resized image 
// to fit in a page
//===============================================


include_once "Savant3/Savant3.php";
include_once "app/class.URL.php";
include_once "app/class.PluginLib.php";
include_once "app/class.Debug.php";

class Image {

  //===============================================
  // init
  //===============================================

  var $pluginlib;

  function __construct($pluglib) { 
    $this->pluginlib = $pluglib;

    $this->debug = new Debug();
   }

  //===============================================
  // display
  //===============================================

  function Display() {

    //$this->debug->Debug2("plugin Image : thyself", $this);
    //exit(0);
 
    $urls = URL::GetURLByInfo($this->pluginlib->globalconf, $this->pluginlib->dosinfo);
    $urls->InitContentDisplayParameters();
    $tpl = new Savant3();
    $tpl->setMessages($this->pluginlib->langmessg);

    $tpl->assign("FILENAM", $this->pluginlib->filename);
    $tpl->assign("URL", $urls);

    $prevf = $this->pluginlib->GetNearFile("prev");
    if ( $prevf != "" ) {
      $tpl->assign("PREVURL", $urls->GetInnerData($prevf) );
    }
    $nextf = $this->pluginlib->GetNearFile("next");
    if ( $nextf != "" ) {
      $tpl->assign("NEXTURL", $urls->GetInnerData($nextf) );
    }
    $tpl->assign("IMGURL", $urls->GetRawDosData($this->pluginlib->filename));
    $tpl->assign("DLURL", $urls->GetDosDownload($this->pluginlib->filename));
    $tpl->display("plugins/Image/tpl.Image.html");

  }

}
