<?php

//===============================================
// plugins to display office document 
// converted to pdf
//===============================================


include_once "Savant3/Savant3.php";
include_once "app/class.URL.php";
include_once "app/class.PluginLib.php";
include_once "app/class.Debug.php";

class PdfConvert {

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

    $filpath = $this->pluginlib->globalconf->AbsDataDir."/".$this->pluginlib->dosinfo['rdir']."/".$this->pluginlib->filename;
    $starr = stat($filpath);
    
    $urls = URL::GetURLByInfo($this->pluginlib->globalconf, $this->pluginlib->dosinfo);
    $tpl = new Savant3();
    $tpl->setMessages($this->pluginlib->langmessg);

    if ( $starr['size'] == 0 ) {

        $tpl->display("plugins/PdfConvert/tpl.Empty.html");

        return;
    }

    if ( $starr['size'] >= 33554432 ) {

        $tpl->assign("URL2", $urls->GetDosDownload($this->pluginlib->filename) );
        $tpl->display("plugins/PdfConvert/tpl.TooBig.html");

        return;
    }
    
    $urls = URL::GetURLByInfo($this->pluginlib->globalconf, $this->pluginlib->dosinfo);
    $tpl = new Savant3();
    $tpl->setMessages($this->pluginlib->langmessg);

    $tpl->assign("FILENAM", $this->pluginlib->filename);
    $tpl->assign("CONVURL", $urls->GetPluginMethod("PdfConvert", "go.PdfConvert.php", $filpath));
    $tpl->assign("DLURL", $urls->GetDosDownload($this->pluginlib->filename));
    $tpl->display("plugins/PdfConvert/tpl.PdfConvert.html");

  }

}
