<?php

include_once "Savant3/Savant3.php";
include_once "class.Data.php";
include_once "class.URL.php";
include_once "class.Debug.php";

class Mgmt {

  //===============================================
  // framework
  //===============================================

  var $public_functions;
  var $gconf;

  function __construct($conf, $lng) { 
    // define webbox callable function
    $this->public_functions = array(
				    "Index" => TRUE,
				    "Create1" => TRUE,
				    "Create2" => TRUE,
                                    "DosCreate" => TRUE,
                                    "MgtCreate" => TRUE,
                                    "CreateJQFU" => TRUE,
				    "FinishCreate" => TRUE,
				    "UnCreate" => TRUE,
				    "UnCreateMgt" => TRUE,
				    "SetLng" => TRUE,
				    "DosList" => TRUE,
				    "View" => TRUE,
				    "Detach" => TRUE,
				    "Delete" => TRUE,
				    "Account" => TRUE,
				    "CreAcc1" => TRUE,
				    "CreAcc2" => TRUE,
				    "CreAcc3" => TRUE,
				    "DispLogo" => TRUE,
				    "LostPassword" => TRUE,
				    "ResetPassword" => TRUE,
				    "Attach" => TRUE,
				    "AttachURL" => TRUE,
				    "AttachURLPass" => TRUE,
				    "Debug" => TRUE
				    );
    $this->gconf = $conf;
    $this->lng = $lng;
    $this->data = new Data($conf);
    $this->debug = new Debug();
   }

  function IsCallable($vars, $method) {
    return array_key_exists($method, $this->public_functions);
  }

  //===============================================
  // web app
  //===============================================

  function Index($vars) {


    $tpl = new Savant3();
    $urls = URL::GetURLSimple($this->gconf);
    $tpl->assign("URL", $urls);
    $messages = $this->data->GetMessages($this->lng);
    $tpl->assign("LNG", $this->lng);
    $tpl->assign("APPNAM", $this->gconf->name);
    $tpl->assign("FAVICO", $this->gconf->favico);
    $tpl->setMessages($messages);
    $tpl->assign("LANGSET", $this->gconf->LangSet);

    $tpl->display("tpl_mgmt/".$this->gconf->MainIndex);
  }

  // old creation method, no longer used ?
  function Create1($vars) {

    $tpl = new Savant3();
    $urls = URL::GetURLSimple($this->gconf);
    $tpl->assign("URL", $urls);
    $messages = $this->data->GetMessages($this->lng);
    $tpl->assign("LNG", $this->lng);
    $tpl->assign("APPNAM", $this->gconf->name);
    $tpl->assign("FAVICO", $this->gconf->favico);
    $tpl->setMessages($messages);

    $utyp = $this->data->CurrentUserStatus();
    $datinf = $this->data->GetDateInfo($utyp);

    $tpl->assign("EOL", date("Y-m-d", $datinf['datelim']) );
    $tpl->assign("DATESEL", $datinf['datesel'] );

    if ( isset($this->gconf->MeetJitSiUrl) && $this->gconf->MeetJitSiUrl != "" ) {
      $tpl->assign("MJITSI", 1);
    } else {
      $tpl->assign("MJITSI", 0);
    }
    if ( isset($this->gconf->FramaDateUrl) && $this->gconf->FramaDateUrl != "" ) {
      $tpl->assign("FRMDAT", 1);
    } else {
      $tpl->assign("FRMDAT", 0);
    }
   
    $tpl->display("tpl_mgmt/create.html");

  }

  // old creation method step 2, no longer used ?
  function Create2($vars) {

    //$this->debug->Debug1("create2");
    //exit(0);

    $pinf = $this->data->CreDosStruct($vars);

    //$this->debug->Debug2("ret crepdstruct = ", $pinf);
    //exit(0);

    if ( isset($vars['RUSERI']) and isset($vars['RUSERM']) ) {
      // created by registered user -> attach it writer mode
      $this->data->DosAttach($pinf['did'], $vars['RUSERI'], 'writer');
    }

    if ( isset($vars['RUSERI']) and isset($vars['RUSERM']) ) {
      // created by registered user -> redisplay list 

      $urls = URL::GetURLSimple($this->gconf);
      $gourl = $urls->GetMgmtMethod('DosList');      

      //echo $gourl."\n";
      header("Location: ".$gourl);

    } else {
      // created standalone -> display new doc

      // set auth before redirecting to the doc
      $this->data->UpdateAuth($pinf['did'], $pinf['passwd']); 

      $urls = URL::GetURLByInfo($this->gconf, $pinf);
      //$gourl = $urls->GetDosMethod('Display','PAGE=DispPage');
      $gourl = $urls->GetDosMethod('Display');
      
      //echo $gourl."\n";
      header("Location: ".$gourl);
    }
  }


  function DosCreate($vars) {

    $tpl = new Savant3();
    $fc = $this->data->PreCreateDosStruct();

    if ( ! isset($fc['did']) ) {
      $tpl->assign("MSG", "Pre-Create failure, contact administrator !");
      $urls = URL::GetURLSimple($this->gconf);
      $tpl->assign("URL", $urls);
      $tpl->assign("RURL", $urls->GetMgmtMethod('Index'));
      $messages = $this->data->GetMessages($this->lng);
      $tpl->setMessages($messages);
      $tpl->display("tpl_doc/error2.html");
      return;
    }

    $dosinf = $this->data->FetchDosInfo($fc['did']);
    $urls = URL::GetURLByInfo($this->gconf, $dosinf);

    $tpl->assign("URL", $urls);
    $messages = $this->data->GetMessages($this->lng);
    $tpl->assign("LNG", $this->lng);
    $tpl->assign("APPNAM", $this->gconf->name);
    $tpl->assign("FAVICO", $this->gconf->favico);
    $tpl->assign("DID", $dosinf['did']);
    $tpl->assign("DOSINF", $dosinf);
    $tpl->setMessages($messages);

    $utyp = $this->data->CurrentUserStatus();
    $datinf = $this->data->GetDateInfo($utyp);

    $tpl->assign("DATESEL", $datinf['datesel'] );

    if ( isset($this->gconf->MeetJitSiUrl) && $this->gconf->MeetJitSiUrl != "" ) {
      $tpl->assign("MJITSI", 1);
    } else {
      $tpl->assign("MJITSI", 0);
    }
    if ( isset($this->gconf->FramaDateUrl) && $this->gconf->FramaDateUrl != "" ) {
      $tpl->assign("FRMDAT", 1);
    } else {
      $tpl->assign("FRMDAT", 0);
    }

    $tpl->display("tpl_mgmt/fullcreate.html");

  }
  
  function MgtCreate($vars) {

    $tpl = new Savant3();
    $fc = $this->data->PreCreateDosStruct();

    if ( ! isset($fc['did']) ) {
      $tpl->assign("MSG", "Pre-Create failure, contact administrator !");
      $urls = URL::GetURLSimple($this->gconf);
      $tpl->assign("URL", $urls);
      $tpl->assign("RURL", $urls->GetMgmtMethod('Index'));
      $messages = $this->data->GetMessages($this->lng);
      $tpl->setMessages($messages);
      $tpl->display("tpl_doc/error2.html");
      return;
    }

    $uinfo = $this->data->UserInfo($_SERVER['PHP_AUTH_USER']);
    $dosinf = $this->data->FetchDosInfo($fc['did']);
    $urls = URL::GetURLByInfo($this->gconf, $dosinf);

    $tpl->assign("URL", $urls);
    $messages = $this->data->GetMessages($this->lng);
    $tpl->assign("LNG", $this->lng);
    $tpl->assign("APPNAM", $this->gconf->name);
    $tpl->assign("FAVICO", $this->gconf->favico);
    $tpl->assign("DID", $dosinf['did']);
    $tpl->assign("DOSINF", $dosinf);
    $tpl->assign("RUSERI", $uinfo['id']);
    $tpl->assign("RUSERM", $uinfo['mail']);
    $tpl->setMessages($messages);

    $utyp = $this->data->CurrentUserStatus();
    $datinf = $this->data->GetDateInfo($utyp);

    $tpl->assign("DATESEL", $datinf['datesel'] );

    if ( isset($this->gconf->MeetJitSiUrl) && $this->gconf->MeetJitSiUrl != "" ) {
      $tpl->assign("MJITSI", 1);
    } else {
      $tpl->assign("MJITSI", 0);
    }
    if ( isset($this->gconf->FramaDateUrl) && $this->gconf->FramaDateUrl != "" ) {
      $tpl->assign("FRMDAT", 1);
    } else {
      $tpl->assign("FRMDAT", 0);
    }

    $tpl->display("tpl_mgmt/part_mgtcreate.html");

  }
  
  function CreateJQFU($vars) {

    //$this->debug->DebugToFile("/tmp/cre_jqfu_deb.log", $vars);
    //exit(0);

    $did = @$vars["DID"];
    if ( isset($did) && $did != "" ) {

      $ret = $this->data->AddJQFUFiles($did, $vars);
    
      $tmp = $vars['files'];
      $finfo['name'] = $tmp['name'][0];
      $finfo['type'] = $tmp['type'][0];
      $finfo['tmp_name'] = $tmp['tmp_name'][0];
      $finfo['error'] = $tmp['error'][0];
      $finfo['size'] = $tmp['size'][0];

      // jquery file upload need a json return 
      header('Content-type: application/json');
      $tpl = new Savant3();
      $tpl->assign("NAME", $finfo['name']);
      $tpl->assign("TYPE", $finfo['type']);
      $tpl->assign("SIZE", $finfo['size']);
      $tpl->display("tpl_doc/jqfu_ret.json");
    }

  }

  function FinishCreate($vars) {

    //$this->debug->Debug1("FinishCreate");
    //exit(0);

    if ( ! isset($vars['DID']) ) {
      $tpl->assign("MSG", "Post-Create failure, DID missing, contact administrator !");
      $urls = URL::GetURLSimple($this->gconf);
      $tpl->assign("URL", $urls);
      $tpl->assign("RURL", $urls->GetMgmtMethod('Index'));
      $messages = $this->data->GetMessages($this->lng);
      $tpl->setMessages($messages);
      $tpl->display("tpl_doc/error2.html");
      return;
    }

    $dosinf = $this->data->FetchDosInfo($vars['DID']);

    $dosinf2 = $this->data->PostCreateDosStruct($dosinf['did'], $vars);

    if ( isset($vars['RUSERI']) and isset($vars['RUSERM']) ) {
      // created by registered user -> attach it writer mode
      $this->data->DosAttach($dosinf2['did'], $vars['RUSERI'], 'writer');
    }

    if ( isset($vars['RUSERI']) and isset($vars['RUSERM']) ) {
      // created by registered user -> redisplay list 

      $urls = URL::GetURLSimple($this->gconf);
      $gourl = $urls->GetMgmtMethod('DosList');      

      //echo $gourl."\n";
      header("Location: ".$gourl);

    } else {
      // created standalone -> display new doc

      // set auth before redirecting to the doc
      $this->data->UpdateAuth($dosinf2['did'], $dosinf2['passwd']); 

      // fetch full dosinfo to see if files present
      $dosinffull = $this->data->FetchDosInfo($dosinf2['did'],1);

      $urls = URL::GetURLByInfo($this->gconf, $dosinf2);
      if ( count($dosinffull['filelist']) > 0 ) {
	$gourl = $urls->GetDosMethod('Display','PAGE=DispShare');
      } else {
	$gourl = $urls->GetDosMethod('Display');
      }
      
      //echo $gourl."\n";
      header("Location: ".$gourl);
    }

  }

  function UnCreate($vars) {

    if ( isset($vars['DID']) and $vars['DID'] != "" ) {
      $this->data->DeleteDosStruct($vars['DID']);
    }
    
    $urls = URL::GetURLSimple($this->gconf);
    $url = $urls->GetMgmtMethod('Index' );
  
    header("Location: ".$url);
  }

  function UnCreateMgt($vars) {

    if ( isset($vars['DID']) and $vars['DID'] != "" ) {
      $this->data->DeleteDosStruct($vars['DID']);
    }
    echo "DONE";
  }

  function SetLng($vars) {

    @session_start();
    $_SESSION['CSXLNG'] = $vars['LNG']; 
    setcookie('CSXLNG', $vars['LNG'], time()+315360000, "/");
 
    $urls = URL::GetURLSimple($this->gconf);
    $url = $urls->GetMgmtMethod('Index' );
  
    header("Location: ".$url);
  }

  function DosList($vars) {

    $this->AskAuth();

    $uinfo = $this->data->UserInfo($_SERVER['PHP_AUTH_USER']);

    //echo "<pre>\n";
    //print_r($uinfo);
    //echo "</pre>\n";
   
    $tpl = new Savant3();
    $urls = URL::GetURLSimple($this->gconf);
    $tpl->assign("URL", $urls);
    $messages = $this->data->GetMessages($this->lng);
    $tpl->assign("LNG", $this->lng);
    $tpl->assign("APPNAM", $this->gconf->name);
    $tpl->assign("FAVICO", $this->gconf->favico);
    $tpl->setMessages($messages);

    $dlist = $this->data-> GetDosListByUser($uinfo['mail']);
    //echo "<pre>\n";
    //print_r($dlist);
    //echo "</pre>\n";
    //exit(0);
   
    $tpl->assign("DLIST", $dlist);
    $tpl->assign("MYSELF", $uinfo['gvname']." ".$uinfo['name']);
    $tpl->assign("RUSERI", $uinfo['id']);
    $tpl->assign("RUSERM", $uinfo['mail']);
    $tpl->assign("UINFO", $uinfo);
 
    $utyp = $this->data->CurrentUserStatus();
    $datinf = $this->data->GetDateInfo($utyp);

    $tpl->assign("EOL", date("Y-m-d", $datinf['datelim']) );
    $tpl->assign("DATESEL", $datinf['datesel'] );

    if ( isset($this->gconf->MeetJitSiUrl) && $this->gconf->MeetJitSiUrl != "" ) {
      $tpl->assign("MJITSI", 1);
    } else {
      $tpl->assign("MJITSI", 0);
    }
    if ( isset($this->gconf->FramaDateUrl) && $this->gconf->FramaDateUrl != "" ) {
      $tpl->assign("FRMDAT", 1);
    } else {
      $tpl->assign("FRMDAT", 0);
    }
   
    $tpl->display("tpl_mgmt/doslist.html");

  }

  function View($vars) {

    $this->AskAuth();
 
    $verif = $this->data->VerifyDosAttach($vars['DID'], $_SERVER['PHP_AUTH_USER']);
    if ( $verif == 0 ) {
      $tpl = new Savant3();
      $tpl->assign("MSG", "Ce porte-documents ne vous est pas attaché !");
      $urls = URL::GetURLSimple($this->gconf);
      $tpl->assign("URL", $urls);
      $tpl->assign("RURL", $urls->GetMgmtMethod('Index'));
      $messages = $this->data->GetMessages($this->lng);
      $tpl->setMessages($messages);
      $tpl->display("tpl_mgmt/error2.html");
      exit(0);
   }

    //$this->debug->Debug2("Mgmt_View",$vars);
    //exit(0);

    $pinf = $this->data->FetchDosInfo($vars['DID']);

    //$this->debug->Debug2("Mgmt_View",$pinf);
    //exit(0);

     // set auth before redirecting to the doc
    $this->data->UpdateAuth($pinf['did'], $pinf['passwd']); 

    $urls = URL::GetURLByInfo($this->gconf, $pinf);
    $gourl = $urls->GetDosMethod('Display');
    
    //echo $gourl."\n";
    header("Location: ".$gourl);
  }

  function Detach($vars) {

    $this->AskAuth();
    
    $verif = $this->data->VerifyDosAttach($vars['DID'], $_SERVER['PHP_AUTH_USER']);
    if ( $verif == 0 ) {
      $tpl = new Savant3();
      $tpl->assign("MSG", "Ce porte-documents ne vous est pas attaché !");
      $urls = URL::GetURLSimple($this->gconf);
      $tpl->assign("URL", $urls);
      $tpl->assign("RURL", $urls->GetMgmtMethod('Index'));
      $messages = $this->data->GetMessages($this->lng);
      $tpl->setMessages($messages);
      $tpl->display("tpl_mgmt/error2.html");
      exit(0);
    }
    $this->data->DosDetach($vars['DID'], $vars['RUSERI']); 

    $urls = URL::GetURLSimple($this->gconf);
    $gourl = $urls->GetMgmtMethod('DosList');
    
    //echo $gourl."\n";
    header("Location: ".$gourl);

  }


  function Delete($vars) {

    $this->AskAuth();
 
    $verif = $this->data->VerifyDosAttach($vars['DID'], $_SERVER['PHP_AUTH_USER']);
    if ( $verif == 0 ) {
      $tpl = new Savant3();
      $tpl->assign("MSG", "Ce porte-documents ne vous est pas attaché !");
      $urls = URL::GetURLSimple($this->gconf);
      $tpl->assign("URL", $urls);
      $tpl->assign("RURL", $urls->GetMgmtMethod('Index'));
      $messages = $this->data->GetMessages($this->lng);
      $tpl->setMessages($messages);
      $tpl->display("tpl_mgmt/error2.html");
      exit(0);
    }

    $this->data->DeleteDosStruct($vars['DID']); 

    $urls = URL::GetURLSimple($this->gconf);
    $gourl = $urls->GetMgmtMethod('DosList');
    
    //echo $gourl."\n";
    header("Location: ".$gourl);


  }

  function Account($vars) {

    //$this->debug->Debug2("Account", $vars);
    //exit(0);

    $this->AskAuth();
 
    $udata=Array();
    $udata['mail'] = $vars['ACTMAIL'];
    $udata['gvname'] = $vars['ACTGVN'];
    $udata['name'] = $vars['ACTNAM'];
    
    $res = $this->data->UpdateUserInfo( $vars['RUSERI'], $udata);

    if ( isset( $vars['ACTPSW1'], $vars['ACTPSW2']) and $vars['ACTPSW1'] == $vars['ACTPSW2'] ) {
      $res = $this->data->UpdateUserPassword( $vars['RUSERI'], $vars['ACTPSW1']);
    }

    if ( isset($vars['DELLOGO']) ) {
      $this->data->UpdateUserLogo( $vars['RUSERI'], NULL);
    } else { 
      if ( $vars['ACTLOGO']['tmp_name'] != "" ) {
	$res = $this->data->UpdateUserLogo( $vars['RUSERI'], $vars['ACTLOGO']);
      }
    }

    $urls = URL::GetURLSimple($this->gconf);
    $gourl = $urls->GetMgmtMethod('DosList');
    
    //echo $gourl."\n";
    header("Location: ".$gourl);

  }

  function Attach($vars) {

    //$this->debug->Debug1("Attach");
    //exit(0);

    $this->AskAuth();
    $uinfo = $this->data->UserInfo($_SERVER['PHP_AUTH_USER']);

	if ( ! isset($vars['DID']) or ! isset($vars['MODE']) ) {
      $tpl = new Savant3();
      $tpl->assign("MSG", "Porte Document inconnu !");
      $urls = URL::GetURLSimple($this->gconf);
      $tpl->assign("URL", $urls);
      $tpl->assign("RURL", $urls->GetMgmtMethod('Index'));
      $messages = $this->data->GetMessages($this->lng);
      $tpl->setMessages($messages);
      $tpl->display("tpl_mgmt/error2.html");
      exit(0);
    }

    switch($vars['MODE']) {
    case ACCES_RW:
      $mode = 'writer';
      break;
    case ACCES_RO:
      $mode = 'reader';
      break;
    }
 
    $this->data->DosAttach($vars['DID'], $uinfo['id'], $mode);
   
    $urls = URL::GetURLSimple($this->gconf);
    $gourl = $urls->GetMgmtMethod('DosList');
    
    //echo $gourl."\n";
    header("Location: ".$gourl);

  }

  function AttachURL($vars) {

    //$this->debug->Debug1("AttachURL");
    //exit(0);

    $this->AskAuth();
    $uinfo = $this->data->UserInfo($_SERVER['PHP_AUTH_USER']);

    $durl = $vars["DOSURL"];
    $dosinfo = $this->data->FetchDosInfoFromUrl($durl);
    
    if ( $dosinfo == null ) {
      $tpl = new Savant3();
      $messages = $this->data->GetMessages($this->lng);
      $tpl->setMessages($messages);
      $tpl->assign("MSG", $messages['matt_err1']);
      $urls = URL::GetURLSimple($this->gconf);
      $tpl->assign("APPNAM", $this->gconf->name);
      $tpl->assign("FAVICO", $this->gconf->favico);
      $tpl->assign("URL", $urls);
      $tpl->assign("AUTOPOP", "PopError");

      $tpl->display("tpl_mgmt/matt_annex.html");
      exit(0);
    }
       
    //$this->debug->Debug1("AttachURL");

    if ( $this->data->VerifyDosAttach($dosinfo['did'], $_SERVER['PHP_AUTH_USER'])) {
      $tpl = new Savant3();
      $messages = $this->data->GetMessages($this->lng);
      $tpl->setMessages($messages);
      $tpl->assign("MSG", $messages['matt_err2']);
      $urls = URL::GetURLSimple($this->gconf);
      $tpl->assign("APPNAM", $this->gconf->name);
      $tpl->assign("FAVICO", $this->gconf->favico);
      $tpl->assign("URL", $urls);
      $tpl->assign("AUTOPOP", "PopError");

      $tpl->display("tpl_mgmt/matt_annex.html");
      exit(0);
    }

    if ( $dosinfo['passwd'] != "" ) {
      $tpl = new Savant3();
      $messages = $this->data->GetMessages($this->lng);
      $tpl->setMessages($messages);
      $tpl->assign("MSG", $messages['matt_err2']);
      $urls = URL::GetURLSimple($this->gconf);
      $tpl->assign("APPNAM", $this->gconf->name);
      $tpl->assign("FAVICO", $this->gconf->favico);
      $tpl->assign("URL", $urls);
      $tpl->assign("AUTOPOP", "PopAskPass");
      $tpl->assign("DID", $dosinfo['did']);
      $tpl->assign("RUSERI", $uinfo['id']);

      $tpl->display("tpl_mgmt/matt_annex.html");
      exit(0);
    }

    if ( $dosinfo['passadm'] != "" ) {
      $mode = 'reader';
    } else {
      $mode = 'writer';
    }  
    $this->data->DosAttach($dosinfo['did'], $uinfo['id'], $mode);
   
    $urls = URL::GetURLSimple($this->gconf);
    $gourl = $urls->GetMgmtMethod('DosList');
    
    //echo $gourl."\n";
    header("Location: ".$gourl);

  }

  function AttachURLPass($vars) {

    //$this->debug->Debug1("AttachURLPass");
    //exit(0);

    $this->AskAuth();
    $uinfo = $this->data->UserInfo($_SERVER['PHP_AUTH_USER']);

    $dosinfo = $this->data->FetchDosInfo($vars['DID']);

    if ( $dosinfo['passwd'] != $vars['DOSPASS'] ) {

      //echo "|".$dosinfo['passwd']."|".$vars['DOSPASS']."|<br>\n";
      //exit(0);

      $tpl = new Savant3();
      $messages = $this->data->GetMessages($this->lng);
      $tpl->setMessages($messages);
      $tpl->assign("MSG", $messages['matt_err3']);
      $urls = URL::GetURLSimple($this->gconf);
      $tpl->assign("APPNAM", $this->gconf->name);
      $tpl->assign("FAVICO", $this->gconf->favico);
      $tpl->assign("URL", $urls);
      $tpl->assign("AUTOPOP", "PopError");

      $tpl->display("tpl_mgmt/matt_annex.html");
      exit(0);
    }

    if ( $dosinfo['passadm'] != "" ) {
      $mode = 'reader';
    } else {
      $mode = 'writer';
    }  
    $this->data->DosAttach($dosinfo['did'], $uinfo['id'], $mode);
   
    $urls = URL::GetURLSimple($this->gconf);
    $gourl = $urls->GetMgmtMethod('DosList');
    
    //echo $gourl."\n";
    header("Location: ".$gourl);
  }

  function DispLogo($vars) {
    
    if ( isset($vars['UID']) ) {
	
      $ldata = $this->data->GetUserLogo($vars['UID']);
	
      if ( strlen($ldata) > 0 ) {
	header("MIME-Version: 1.0");
	header("Expires: Sat, 01 Jan 2000 05:00:00 GMT");        // date in the past
	header("Last-Modified:".date("D, d M Y H:i:s")." GMT");  // always modified
	header("Cache-Control: no-cache, must-revalidate");      // HTTP/1.1
	header("Pragma: no-cache");                              // HTTP/1.0
	header("Content-type: image/");   
	
	echo $ldata;
      }
    }
  }

  //===============================================
  // account creation stuff
  //===============================================

  function CreAcc1($vars) {

    //$this->debug->Debug1("CreAcc1");
    //exit(0);

    $tpl = new Savant3();
    $urls = URL::GetURLSimple($this->gconf);
    $tpl->assign("URL", $urls);
    $messages = $this->data->GetMessages($this->lng);
    $tpl->assign("LNG", $this->lng);
    $tpl->assign("APPNAM", $this->gconf->name);
    $tpl->assign("FAVICO", $this->gconf->favico);
    $tpl->setMessages($messages);

    $tpl->display("tpl_mgmt/acccre1.html");

  }

  function CreAcc2($vars) {

    //$this->debug->Debug1("CreAcc2");
    //exit(0);

    $tpl = new Savant3();
    $urls = URL::GetURLSimple($this->gconf);
    $tpl->assign("URL", $urls);
    $messages = $this->data->GetMessages($this->lng);
    $tpl->assign("LNG", $this->lng);
    $tpl->assign("APPNAM", $this->gconf->name);
    $tpl->assign("FAVICO", $this->gconf->favico);
    $tpl->setMessages($messages);

    if ( $vars['ACCPASS'] != $vars['ACCPAS2'] ) {
      $tpl->assign("ERR", "PASS"); 
      $tpl->display("tpl_mgmt/acccre2.html");
      return;
    }

    $udata=Array();
    $udata['mail'] = $vars['ACCMAIL'];
    $udata['gvname'] = $vars['ACCGVN'];
    $udata['name'] = $vars['ACCNAME'];
    $udata['password'] = $vars['ACCPASS'];
    
    $uid = $this->data->CreateUserRequest($udata);
    if ( $uid == 0 ) {
      $tpl->assign("ERR", "LOGIN"); 
      $tpl->display("tpl_mgmt/acccre2.html");
      return;
   }

    $tpl->display("tpl_mgmt/acccre2.html");

    // send the mail for validation account 

    $tpl->assign("GVN", $udata['gvname']); 
    $tpl->assign("NAM", $udata['name']); 
    $mskid = sprintf("%s_%06d",$this->data->GetRandomString(5),$uid);
    $tpl->assign("RANDID", base64_encode($mskid) ); 

    $tmpfname = tempnam("/tmp", "CSXmelAcre");
    $fm = fopen($tmpfname, "a");
    fwrite($fm,$tpl->fetch("tpl_mgmt/mail_creacc.html"));
    fclose($fm);
 
    $launch=$this->gconf->TopAppDir."/script/melsnd ".$udata['mail']." \"".$messages['melacc_tit']."\" ".$tmpfname;
    system($launch);
 

  }

  function CreAcc3($vars) {

    //$this->debug->Debug2("CreAcc3",$vars);
    //exit(0);

    $udec1 = base64_decode($vars['UCODE']);
    preg_match('/.*_([0-9]*)/', $udec1, $arres);
    $uid0 = $arres[1];
    $uid = (Int)$uid0;

    $tpl = new Savant3();
    $urls = URL::GetURLSimple($this->gconf);
    $tpl->assign("URL", $urls);
    $messages = $this->data->GetMessages($this->lng);
    $tpl->assign("LNG", $this->lng);
    $tpl->assign("APPNAM", $this->gconf->name);
    $tpl->assign("FAVICO", $this->gconf->favico);
    $tpl->setMessages($messages);

    $ret = $this->data->ValidateUser($uid);
    
    switch($ret) {
    case 0:
      $tpl->assign("ERR", "NONE"); 
      break;
    case 1:
      $tpl->assign("ERR", "ALREADY"); 
      break;
    case 2:
      $tpl->assign("ERR", "NOUSER"); 
      break;
    case 2:
      $tpl->assign("ERR", "UNKNOWN"); 
      break;
    }

    $tpl->display("tpl_mgmt/acccre3.html");

  }

  function LostPassword($vars) {

    //$this->debug->Debug1("LostPassword");
    //exit(0);

    $tpl = new Savant3();
    $urls = URL::GetURLSimple($this->gconf);
    $tpl->assign("URL", $urls);
    $messages = $this->data->GetMessages($this->lng);
    $tpl->assign("LNG", $this->lng);
    $tpl->assign("APPNAM", $this->gconf->name);
    $tpl->assign("FAVICO", $this->gconf->favico);
    $tpl->setMessages($messages);

    $tpl->display("tpl_mgmt/lostpass.html");

  }

  function ResetPassword($vars) {

    //$this->debug->Debug1("ResetPassword");
    //exit(0);

    $ret = $this->data->ResetPassword($vars['ACCMAIL']);

    $tpl = new Savant3();
    $urls = URL::GetURLSimple($this->gconf);
    $tpl->assign("URL", $urls);
    $messages = $this->data->GetMessages($this->lng);
    $tpl->assign("LNG", $this->lng);
    $tpl->setMessages($messages);
    $tpl->assign("LANGSET", $this->gconf->LangSet);

    $tpl->display("tpl_mgmt/index.html");

    if ( isset($ret['newpasswd'] ) ) {
 
      // send the mail with new passwd 

      $tpl->assign("GVN", $ret['gvname']); 
      $tpl->assign("NAM", $ret['name']); 
      $tpl->assign("NEWPASS", $ret['newpasswd']); 
      
      $tmpfname = tempnam("/tmp", "CSXmelPass");
      $fm = fopen($tmpfname, "a");
      fwrite($fm,$tpl->fetch("tpl_mgmt/mail_passwd.html"));
      fclose($fm);
      
      $launch=$this->gconf->TopAppDir."/script/melsnd ".$ret['mail']." \"".$messages['melpass_tit']."\" ".$tmpfname;
      system($launch);
    }
 

  }

  //===============================================
  // auth stuff
  //===============================================

  function AskAuth(){

    $realm = $this->gconf->name;
    
    // demande d'identification
    if ( !isset($_SERVER['PHP_AUTH_USER']) ) {
      $this->Authenticate($realm);
    } else {
      $uok = $this->data->VerifUser($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
      if ( ! $uok ) {
	$this->Authenticate($realm);
      }
    }
  }

  function Authenticate($realm){
    Header("status: 401 Unauthorized"); 
    Header("WWW-Authenticate: Basic realm=\"$realm\" ");
    Header("HTTP/1.0 401 Unauthorized");
 
    // display if authentication canceled
    $tpl = new Savant3();
    $urls = URL::GetURLSimple($this->gconf);
    $tpl->assign("URL", $urls);
    $tpl->assign("APPNAM", $this->gconf->name);
    $tpl->assign("FAVICO", $this->gconf->favico);
    $messages = $this->data->GetMessages($this->lng);
    $tpl->setMessages($messages);
    
    $tpl->display("tpl_mgmt/unauth.html");
   
    exit;
  }


  //===============================================
  // debug
  //===============================================

  function Debug($vars) {

    $this->debug->Debug1("Debug");
    exit(0);

    //$dlist = $this->data->GetDosListByUser('dan.y.roche@gmail.com');
    
    //echo "<pre>\n";
    //print_r($dlist);
    //echo "</pre>\n";
    
    $uinfo = $this->data->UserInfo($_SERVER['PHP_AUTH_USER']);

    echo "<pre>\n";
    print_r($uinfo);
    echo "</pre>\n";

    //echo "status = ".$this->data->CurrentUserStatus();

    //$this->debug->Debug1("zz"); 
 
    //$ret = $this->data->GetUserLogo(2,"/tmp/zz.data");
    //echo "returned : ".strlen($ret);

    //echo "<img src=\"DispLogo?UID=1\">";
  }

  //===============================================
  // end
  //===============================================

}

