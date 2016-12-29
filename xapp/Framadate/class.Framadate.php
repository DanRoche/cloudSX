<?php

// #######################################
// cloudSX external application : Framadate
// 


class Framadate {

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
          trigger_error("BAD arguments, abort Framadate Creation");
          return(null);
      }

      // fetch my own path 
      $mypath = dirname(__FILE__);

      // use a script to pre-create the poll in base

      $cmd = $mypath."/manage_framadate -c -t \"".$dosinfo['title']."\"";

      $pn = popen($cmd, "r");
      $tmpret = fgets($pn);
      pclose($pn);
      
      $retuid = chop($tmpret);
      $appurl = $this->baseurl."?poll=".$retuid;
      $appfile = $this->gconf->AbsDataDir."/".$dosinfo['rdir']."/".$name.".xapp";

      $fp = fopen($appfile, 'w');
      fwrite($fp, "CLASS = Framadate\n");
      //fwrite($fp, "URL = \"".$appurl."\"\n");
      fwrite($fp, "URL = generated\n");
      if ( $this->owntab == 1 ) {
          fwrite($fp, "DispInNewTab = yes\n");
      } else {
          fwrite($fp, "DispInNewTab = no\n");
      }
      fwrite($fp, "FRAMUID = ".$retuid."\n");
      fclose($fp);
      
      return(null);
  }

  function GenerateUrl($appconf,$lang) {

      if ( empty($appconf) || empty($lang) ) {
          trigger_error("BAD arguments, abort Framadate GenerateUrl");
          return(null);
      }

      $lconvert = Array(
          "FR" => "fr", 
          "EN" => "en"
      );

      $theu =  $this->baseurl."?poll=".$appconf['FRAMUID']."&lang=".$lconvert[$lang];

      return($theu);
  }

  function Delete($appconf) {

      if ( empty($appconf) ) {
          trigger_error("BAD arguments, abort Framadate Delete");
          return(null);
      }

      // fetch my own path 
      $mypath = dirname(__FILE__);

      // use the script to delete the poll in base

      $cmd = $mypath."/manage_framadate -d -i ".$appconf['FRAMUID'];
      // do not expect return
      system($cmd);
  }

  //===============================================
  // end
  //===============================================

}

