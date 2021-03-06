<?php

include_once "Savant3/Savant3.php";

class Debug {

  // hard coded time debug file, why not ?
  var $timedebfile = "/tmp/timedeb.txt";

  //===============================================
  // debug
  //===============================================

  function Debug1( $msg ) {
    $tpl = new Savant3();

    $tpl->assign("MSG", $msg);

    $tpl->display("tpl_deb/debug1.html");
  }
 
  function Debug2( $msg, $var ) {
    $tpl = new Savant3();

    $tpl->assign("MSG", $msg);
    $tpl->assign("VAR", $var);

    $tpl->display("tpl_deb/debug2.html");
  }

  function DebugToFile( $filedeb, $var ) {

    $data = print_r($var, true);
    
    $fd = fopen($filedeb, "a+");
    fwrite($fd, "==========================\n");
    fwrite($fd, $data);
    fwrite($fd, "\n");
    fclose($fd);

  }
 
  function TimeDebug( $msg ) {
    $fd = fopen($this->timedebfile, "a+");
    fwrite($fd, microtime(1));
    fwrite($fd, " ".$msg."\n");
    fclose($fd);
  }

  //===============================================
  // end
  //===============================================

}

