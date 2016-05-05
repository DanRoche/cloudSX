<?php

//===============================================
// plugins to display unknown content 
// -> display just a message 
//===============================================


include_once "Savant3/Savant3.php";
include_once "app/class.URL.php";
include_once "app/class.PluginLib.php";
include_once "app/class.Debug.php";

class Unknown {

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

    //$this->debug->Debug2("plugin Image : pdinfo", $this->pdinfo);
    //$this->debug->Debug2("plugin Image : file", $this->file);
    //$this->debug->Debug2("plugin Image : conf", $this->gconf);
    //$this->debug->Debug2("plugin Image : lang", $this->lng);
    //exit(0);
 
    $urls = URL::GetURLByInfo($this->pluginlib->globalconf, $this->pluginlib->dosinfo);
    $tpl = new Savant3();
 
    $tpl->setMessages($this->pluginlib->langmessg);

    $tpl->assign("URL", $urls );
    $tpl->assign("URL1", $urls->GetRawDosData($this->pluginlib->filename) );
    $tpl->assign("URL2", $urls->GetDosDownload($this->pluginlib->filename) );
    $tpl->display("plugins/Unknown/tpl.Unknown.html");

  }

}
