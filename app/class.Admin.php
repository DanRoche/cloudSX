<?php

include_once "Savant3/Savant3.php";
include_once "class.Data.php";
include_once "class.URL.php";
include_once "class.Debug.php";

class Admin {

  //===============================================
  // framework
  //===============================================

  var $public_functions;
  var $gconf;

  function __construct($conf, $lng) { 
    // define webbox callable function
    $this->public_functions = array(
				    "DosList" => TRUE,
				    "UserList" => TRUE,
				    "View" => TRUE,
				    "Delete" => TRUE,
				    "CreUser" => TRUE,
				    "ModUser" => TRUE,
				    "DelUser" => TRUE,
				    "D4U" => TRUE,
				    "U4D" => TRUE,
				    "CloseRes" => TRUE,
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

  function DosList($vars) {

    $this->AskAuth();

    $tpl = new Savant3();
    $urls = URL::GetURLSimple($this->gconf);
    $tpl->assign("URL", $urls);
    $messages = $this->data->GetMessages($this->lng);
    $tpl->assign("LNG", $this->lng);
    $tpl->assign("APPNAM", $this->gconf->name);
    $tpl->setMessages($messages);

    $dflist = $this->data-> GetDosListFull();
    //echo "<pre>\n";
    //print_r($dflist);
    //echo "</pre>\n";
    //exit(0);
   
    $tpl->assign("DFLIST", $dflist);
    $tpl->assign("MYSELF", "DosList");
    @session_start();
    $tpl->assign("RTABS", $_SESSION['RESTAB']);

    $tpl->display("tpl_adm/doslist.html");

  }

  function D4U($vars) {

    $this->AskAuth();

    $tpl = new Savant3();
    $urls = URL::GetURLSimple($this->gconf);
    $tpl->assign("URL", $urls);
    $messages = $this->data->GetMessages($this->lng);
    $tpl->assign("LNG", $this->lng);
    $tpl->assign("APPNAM", $this->gconf->name);
    $tpl->setMessages($messages);

    $uinfo = $this->data->UserInfoByUid($vars['UID']);
    if ( $uinfo == null ) {
      echo "ERROR: user not found";
      exit(1);
    }

    $curtag = "D4U/".$vars['UID'];
    $this->ResultTab($curtag);

    $dplist = $this->data->GetDosListByUser($uinfo['mail']);

    $tpl->assign("SEARCHINFO", $uinfo);

    $tpl->assign("DPLIST", $dplist);
    $tpl->assign("MYSELF", "D4U");
    @session_start();
    $tpl->assign("RTABS", $_SESSION['RESTAB']);
    $tpl->assign("CURTAB", $curtag);

    $tpl->display("tpl_adm/res_d4u.html");

  }

  function UserList($vars) {

    $this->AskAuth();

    $tpl = new Savant3();
    $urls = URL::GetURLSimple($this->gconf);
    $tpl->assign("URL", $urls);
    $messages = $this->data->GetMessages($this->lng);
    $tpl->assign("LNG", $this->lng);
    $tpl->assign("APPNAM", $this->gconf->name);
    $tpl->setMessages($messages);

    $uflist = $this->data->GetUserListFull();
    //echo "<pre>\n";
    //print_r($dflist);
    //echo "</pre>\n";
    //exit(0);
   
    $tpl->assign("UFLIST", $uflist);
    $tpl->assign("MYSELF", "UserList");
    $tpl->assign("STATUSES", $this->data->GetUserStatuses());
    @session_start();
    $tpl->assign("RTABS", $_SESSION['RESTAB']);

    $tpl->display("tpl_adm/uzrlist.html");

  }

  function U4D($vars) {

    $this->AskAuth();

    $tpl = new Savant3();
    $urls = URL::GetURLSimple($this->gconf);
    $tpl->assign("URL", $urls);
    $messages = $this->data->GetMessages($this->lng);
    $tpl->assign("LNG", $this->lng);
    $tpl->assign("APPNAM", $this->gconf->name);
    $tpl->setMessages($messages);

    $dinfo = $this->data->FetchDosInfo($vars['DID']);
    if ( $dinfo == null ) {
      echo "ERROR: briefcase not found";
      exit(1);
    }
    
    $curtag = "U4D/".$vars['DID'];
    $this->ResultTab($curtag);

    $uplist = $this->data-> GetUserListByDid($dinfo['did']);

    $tpl->assign("SEARCHINFO", $dinfo);

    $tpl->assign("UPLIST", $uplist);
    $tpl->assign("MYSELF", "U4D");
    @session_start();
    $tpl->assign("RTABS", $_SESSION['RESTAB']);
    $tpl->assign("CURTAB", $curtag);

    $tpl->display("tpl_adm/res_u4d.html");

  }

  function View($vars) {

    $this->AskAuth();

    // no attachment verification, we are admin !
 
    $dinf = $this->data->FetchDosInfo($vars['DID']);

    // set auth before redirecting to the doc
    $this->data->UpdateAuth($dinf['did'], $dinf['passwd']); 

    $urls = URL::GetURLByInfo($this->gconf, $dinf);
    $gourl = $urls->GetDosMethod('Display');
    
    //echo $gourl."\n";
    header("Location: ".$gourl);
  }

  function Delete($vars) {

    //echo "<pre>\n";
    //print_r($vars);
    //echo "</pre>\n";
    //exit(0);

    $this->AskAuth();
 
    // no attachment verification, we are admin !
 
    $this->data->DeleteDosStruct($vars['DID']); 

    $urls = URL::GetURLSimple($this->gconf);
    $gourl = $urls->GetAdmMethod('DosList');
    
    //echo $gourl."\n";
    header("Location: ".$gourl);
  }

  function CreUser($vars) {

    //echo "<pre>\n";
    //print_r($vars);
    //echo "</pre>\n";
    //exit(0);

    $this->AskAuth();
 
    $udata=Array();
    $udata['mail'] = $vars['UZMEL'];
    $udata['gvname'] = $vars['UZGVN'];
    $udata['name'] = $vars['UZNAM'];
    $udata['password'] = $vars['UZPWD'];
    $udata['status'] = $vars['UZSTS'];
 
    $uid = $this->data->CreateUserValid($udata);
    if ( $uid == 0 ) {
      echo "ERROR user creation";
      return;
    }

    $urls = URL::GetURLSimple($this->gconf);
    $gourl = $urls->GetAdmMethod('UserList');
    
    //echo $gourl."\n";
    header("Location: ".$gourl);
  }

  function ModUser($vars) {

    //echo "<pre>\n";
    //print_r($vars);
    //echo "</pre>\n";
    //exit(0);

    $this->AskAuth();

    $udata=Array();
    $udata['mail'] = $vars['UZMEL'];
    $udata['gvname'] = $vars['UZGVN'];
    $udata['name'] = $vars['UZNAM'];

    $this->data->UpdateUserInfo($vars['UID'], $udata);

    $tmpass = chop($vars['UZPWD']);
    if ( ! empty($tmpass) ) {
      $this->data->UpdateUserPassword($vars['UID'], $vars['UZPWD']);
    }

    $this->data->UpdateUserStatus($vars['UID'], $vars['UZSTS']);

    $urls = URL::GetURLSimple($this->gconf);
    $gourl = $urls->GetAdmMethod('UserList');
    
    //echo $gourl."\n";
    header("Location: ".$gourl);
  }

  function DelUser($vars) {

    //echo "<pre>\n";
    //print_r($vars);
    //echo "</pre>\n";
    //exit(0);

    $this->AskAuth();
 
    $this->data->DeleteUserByUid($vars['UID']);

    $urls = URL::GetURLSimple($this->gconf);
    $gourl = $urls->GetAdmMethod('UserList');
    
    //echo $gourl."\n";
    header("Location: ".$gourl);
  }

  function CloseRes($vars) {

    //echo "<pre>\n";
    //print_r($vars);
    //echo "</pre>\n";
    //exit(0);

    $this->AskAuth();
 
    $this->ResultDel($vars['TAG']);

    $urls = URL::GetURLSimple($this->gconf);
    $gourl = $urls->GetAdmMethod('UserList');
    
    //echo $gourl."\n";
    header("Location: ".$gourl);
  }

  //===============================================
  // result tabs stuff
  //===============================================

  function ResultTab($tag) {
    @session_start();
    if ( ! is_array($_SESSION['RESTAB']) ) {
      $_SESSION['RESTAB'] = array();
    }
    if ( ! in_array($tag, $_SESSION['RESTAB']) ) {
      $_SESSION['RESTAB'][] = $tag; 
    }
  }

  function ResultDel($tag) {
    @session_start();
    foreach ($_SESSION['RESTAB'] as $ind => $val) {
      if ( $val == $tag ) {
	unset($_SESSION['RESTAB'][$ind]);
      }
    }
  }

  //===============================================
  // auth stuff
  //===============================================

  function AskAuth(){

    $realm = "ADM:".$this->gconf->name;
    
    // demande d'identification
    if ( !isset($_SERVER['PHP_AUTH_USER']) ) {
      $this->Authenticate($realm);
    } else {
      $uok = $this->data->VerifAdmUser($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
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
    $messages = $this->data->GetMessages($this->lng);
    $tpl->setMessages($messages);
    
    $tpl->display("tpl_mgmt/unauth.html");
   
    exit;
  }


  //===============================================
  // debug
  //===============================================

  function Debug($vars) {

    //$this->debug->Debug1("Debug");
    //exit(0);

    @session_start();
    $tabs = $_SESSION['RESTAB'];
    echo "<pre>\n";
    print_r($tabs);
    echo "</pre>\n";

    //$alist = $this->data-> GetDosListFull();
    //echo "<pre>\n";
    //print_r($alist);
    //echo "</pre>\n";

    //$u4list =  $this->data-> GetUserListByDID('0134c1d28abe9dac47537c55aac3a4ef');
    //echo "<pre>\n";
    //print_r($u4list);
    //echo "</pre>\n";

    //$ulist =  $this->data-> GetUserListFull();
    //echo "<pre>\n";
    //print_r($ulist);
    //echo "</pre>\n";

    //$d4list =  $this->data-> GetDosListByUser('dan.y.roche@gmail.com');
    //echo "<pre>\n";
    //print_r($d4list);
    //echo "</pre>\n";

    exit(0);
    
  }

  //===============================================
  // end
  //===============================================

}

