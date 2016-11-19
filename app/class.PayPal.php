<?php

include_once "class.Data.php";

//===============================================
// implemented as a TEST for now
//===============================================


class PayPal {

  //===============================================
  // framework
  //===============================================

  var $public_functions;
  var $gconf;
  var $IPNlogdir;
  var $PPurl;

  function __construct($conf) { 
    // define webbox callable function
    $this->public_functions = array(
				    "IPNreceptor" => TRUE
				    );
    $this->gconf = $conf;

    $this->IPNlogdir = $this->gconf->TopAppDir."/pp/log/";

    // ------ SANDBOX ------
    //$this->PPhost = "www.sandbox.paypal.com";
    // ------   PROD  ------
    $this->PPhost = "www.paypal.com";

    $this->PPurl = "https://".$this->PPhost."/cgi-bin/webscr";

    $this->data = new Data($conf);
  }

  function IsCallable($vars, $method) {
    return array_key_exists($method, $this->public_functions);
  }

  //===============================================
  // web app
  //===============================================

  function IPNreceptor($vars) {

      $now = date("YmdHis");
      $ipnlog = $this->IPNlogdir.$now;
      $cnt = 0;
      
      while ( file_exists($ipnlog) and $cnt < 10 ) {
          $ipnlog = $this->IPNlogdir.$now."_".$cnt;
          $cnt += 1;
      }
      if ( $cnt >= 10 ) {
          echo "Ya comme un soucis \n";
          exit(1);
      }
      
      $data = print_r($vars, true);

      $retdata = $this->GetReturnData();

      $ppresp = $this->PostBack($retdata);
      
      $fd = fopen($ipnlog , "a+");
      fwrite($fd, "==========================\n");
      fwrite($fd, $data);
      fwrite($fd, "\n");
      fwrite($fd, "==========================\n");
      fwrite($fd, $retdata);
      fwrite($fd, "\n");
      fwrite($fd, "==========================\n");
      fwrite($fd, $ppresp);
      fwrite($fd, "\n");

      $uinfo = $this->data->UserInfo($vars['option_selection2']);
      if ( empty($uinfo['id']) ) {
          fwrite($fd, "==========================\n");
          fwrite($fd, "USER ".$vars['option_selection2']." NOT FOUND aborting \n");
          fclose($fd);
          return;
      }

      if ( $vars['item_name'] != "JOTABUG_PAY" )  {
          fwrite($fd, "==========================\n");
          fwrite($fd, "JOTABUG_PAY item NOT FOUND:  aborting \n");
          fclose($fd);
          return;
      }

      fwrite($fd, "==========================\n");
      fwrite($fd, "processing user=".$vars['option_selection2']." id=".$uinfo['id']."\n");
      fclose($fd);

      $this->data->IncreaseUserSubscription($uinfo['id'],$vars['option_selection1']);

      echo "OK";
      
  }

  //===============================================
  // tools
  //===============================================

  function GetReturnData() {
      // use $_POST rather than wbx variable, to be sure to respect order ?
      
      $req = 'cmd=_notify-validate';

      foreach ($_POST as $key => $value) {
          $value = trim(urlencode(stripslashes($value)));
          $req .= "&$key=$value";
      }
      
      // reponse a PayPal pour validation
      $header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
      $header .= "Host: ".$this->PPhost.":443\r\n";
      $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
      $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

      return($header . $req);
  }

  function PostBack($data) {
      $fp = fsockopen ($this->PPhost, 443, $errno, $errstr, 30);

      $ret = "unknown";
      if (!$fp) {
          $ret = "cannot open back socket";
      } else {
          fputs ($fp, $data);
          while (!feof($fp)) {
              $res = fgets ($fp, 1024);
              if (strcmp ($res, "VERIFIED") == 0) {
                  $ret = "PPreturn = VERIFIED";
              } else if (strcmp ($res, "INVALID") == 0) {
                  $ret = "PPreturn = INVALID";
              } else {
                  $ret = $res;
              }
          }
      }
      return($ret);
  }

  
  //===============================================
  // end
  //===============================================

}

