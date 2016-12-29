<?php

// #######################################
// cloudSX external application : MeetJitsi
// 


class MeetJitsi {

  //===============================================
  // transported data
  //===============================================

    var $title;
    var $baseurl;
    var $owntab;

    var $gconf;

  //===============================================
  // init 
  //===============================================

  function __construct($conf) { 

      // fetch config
      $confil = dirname(__FILE__)."/config.ini";
      if ( ! file_exists($confil) ) {
          trigger_error("Cannot find XAPP config : ".$confil);
          exit(2);
      }
      $confdata =  parse_ini_file ($confil,true);

      // multilingual title
      $this->title = $confdata['LANG-TITLE'];

      // base url for the application
      $this->baseurl = $confdata['BASE']['baseurl'];

      // allow display app in it's own tab
      $this->owntab = $confdata['BASE']['owntab'];

      // global master config
      $this->gconf = $conf;
   }

  //===============================================
  // Utilities functions
  //===============================================

  function Create($name, $dosinfo) {

      if ( empty($name) || empty($dosinfo) ) {
          trigger_error("BAD arguments, abort MeetJitsi Creation");
          return(null);
      }

      // this one is also simple , juste add a prefix and the DID to the base url !

      $appurl = $this->baseurl.$this->gconf->name.$dosinfo['did'];
      $appfile = $this->gconf->AbsDataDir."/".$dosinfo['rdir']."/".$name.".xapp";

      $fp = fopen($appfile, 'w');
      fwrite($fp, "CLASS = MeetJitsi\n");
      fwrite($fp, "URL = \"".$appurl."\"\n");
      if ( $this->owntab == 1 ) {
          fwrite($fp, "DispInNewTab = yes\n");
      } else {
          fwrite($fp, "DispInNewTab = no\n");
      }
      fclose($fp);
      
      return(null);
  }

  //===============================================
  // end
  //===============================================

}

