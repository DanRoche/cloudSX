<?php

//===============================================
// plugins to display office document 
// without pdf preview
//===============================================


include_once "Savant3/Savant3.php";
include_once "app/class.URL.php";
include_once "app/class.PluginLib.php";
include_once "app/class.Debug.php";

class OfficeNovisu {

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

    $tpl->assign("FILENAM", $this->pluginlib->filename);
    $tpl->assign("DLURL", $urls->GetDosDownload($this->pluginlib->filename));
    $tpl->assign("VNDURL", $urls->GetVndSunStar($this->pluginlib->filename));
    $tpl->assign("HELPURL", $urls->GetMedia('/help/vnd/'.$this->pluginlib->lang.'/index.html'));
    
    if ( $starr['size'] == 0 ) {
        $tpl->display("plugins/OfficeNovisu/tpl.Empty.html");
        return;
    }

    $tpl->display("plugins/OfficeNovisu/tpl.OfficeNovisu.html");

  }

}
