<?php

class Config {

  // init config
  function __construct() { 

    // ============================
    // CUSTOMIZABLE VARIABLES
    // ============================

    // application name & icon
    $this->name = "CloudSX";
    $this->favico = "/img/cloudsx.ico";

    // application topdir & topurl
    //     TopAppDir should match the PATH where you installed this software
    //     TopAppUrl should be empty on a virtual host,
    //               use the subdirectory of the site otherwise
    $this->TopAppDir = "/path/to/webroot/cloudsx";
    $this->TopAppUrl = "";

    // database : self explanatory
    $this->dbserv = "myhost";
    $this->dbbase = "cloudsx";
    $this->dbpdo = "mysql:host=".$this->dbserv.";dbname=".$this->dbbase;
    
    $this->dbuser = "mylogin";
    $this->dbpass = "mypass";
    
    $this->dbparams = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'');

    // database 2 : framadate partner database 
    // used only by manage_framadate script 
    $this->db2serv = "my2host";
    $this->db2base = "framadate";
    $this->db2user = "my2login";
    $this->db2pass = "my2pass";
    
    // lang set  only EN and FR available for now !
    //           you may add some translation file in the lang sub-directory
    $this->LangSet = Array(
			   "EN" => "English",
			   "FR" => "Français",
			   );

    // should we use JQuery File Upload ? 
    // better to say yes (1), unless you use very old browser !
    $this->JQFileUpload = 1;

    // CloudSX spool directory
    // used by batch script for thumbnail generation
    // DO NOT forget to initialise incrontab for this directory (see script/initincrontab)
    // if you modify this value, change the script accordingly 
    $this->upspool = $this->TopAppDir."/spool";
   
    // CloudSX meet.jit.si companion  URL
    // it's possible to associate a meet.jit.si webconf (https://meet.jit.si/)
    // to an briefcase
    // if you wich so, define the URL here, otherwise , let it undefined (or empty).
    $this->MeetJitSiUrl = "http://meet.jit.si";
   
    // CloudSX framadate companion  URL
    // it's possible to embed an external framadate (https://framadate.org/)
    // website into cloudSX.
    // if you wich so, define the URL here, otherwise , let it undefined (or empty).
    $this->FramaDateUrl = "http://demo.framadate.net";
   
    // clousdSX from mail
    // for mail emitted by cloudSX
    $this->MailFrom = "someone@some.where";
   
    // cloudSX main index template name 
    // you may change this to customize the landing page
    $this->MainIndex = "index.html";
    // cloudSX default logo : 2 sizes
    $this->DefLogoSmall = "/img/cloudsx_h80.png";
    $this->DefLogoLarge = "/img/cloudsx_w200.png";

    // cloudSX self promotion
    $this->SelfPromote = "on";

    // ============================
    // LESS CUSTOMIZABLE VARIABLES
    // ============================

    // inner subdirectories
    // modify only if you have changed the software layout
    $this->DataDir = "/doc";
    $this->AbsDataDir = $this->TopAppDir.$this->DataDir;
    $this->LngDir = $this->TopAppDir."/lang";

    // URL generation method
    // simple URLs, pretiest, but require apache mod-rewrite  ( see .htaccess )
    $this->csxDocClass = "/doc";
    $this->csxMgmtClass = "/mgt";
    $this->csxAdmClass = "/adm";
    // long URLs, use it if apache mod-rewrite not available for your system
    // $this->csxDocClass = "/wbx.php/Doc";
    // $this->csxMgmtClass = "/wbx.php/Mgmt";
    // $this->csxAdmClass = "/wbx.php/Admin";

  }
    
}

