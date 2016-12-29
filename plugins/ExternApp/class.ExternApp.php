<?php

//===============================================
// plugins to display External Applications
//===============================================


include_once "Savant3/Savant3.php";
include_once "app/class.URL.php";
include_once "app/class.PluginLib.php";
include_once "app/class.Debug.php";
include_once "app/class.Data.php";

class ExternApp {

  //===============================================
  // init
  //===============================================

  var $pluginlib;
  var $xappinfo;

  function __construct($pluglib) { 
    $this->pluginlib = $pluglib;

    $this->debug = new Debug();
   }

  //===============================================
  // display
  //===============================================

  function Display() {

      //$this->debug->Debug2("ExternApp-Display", $this->pluginlib);
      //exit(0);
      $this->ParseXapp();

      
      $urls = URL::GetURLByInfo($this->pluginlib->globalconf, $this->pluginlib->dosinfo);
      $tpl = new Savant3();
      $tpl->setMessages($this->pluginlib->langmessg);
      
      $tpl->assign("APPNAM",  basename($this->pluginlib->filename,".xapp"));

      if ( $this->xappinfo["URL"] == "generated" ) {
          $this->data = new Data($this->pluginlib->globalconf);
          $xappo = $this->data->GetXapp($this->xappinfo["CLASS"]);
          $url = $xappo->GenerateUrl($this->xappinfo,$this->pluginlib->lang);
          $tpl->assign("XURL",  $url);
      } else {
          $tpl->assign("XURL",  $this->xappinfo["URL"]);
      }
      
      if ( @$this->xappinfo["DispInNewTab"] ) {
          $tpl->assign("NEWTAB", 1);
      } else {
          $tpl->assign("NEWTAB", 0);
      }
      $tpl->display("plugins/ExternApp/tpl.ExternApp.html");
      
  }
  
  function ParseXapp() {
      
      $filpath = $this->pluginlib->globalconf->AbsDataDir."/".$this->pluginlib->dosinfo["rdir"]."/".$this->pluginlib->filename;
      $this->xappinfo = parse_ini_file($filpath);

      //$this->debug->Debug2("ExternApp-Xinfo", $this->xappinfo);
      //exit(0);
  }
  
}
