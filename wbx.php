<?php
include_once "app/Config.php";

//*************************************
//  anti pirate ip filter
//*************************************

//$excludadr = array(
//		   "127.0",
//		   );
//$remote = $_SERVER["REMOTE_ADDR"];
//foreach ($excludadr as $bip) {
//  if ( strncmp($remote,$bip,strlen($bip)) == 0 ) {
//    header("HTTP/1.1 410 Gone");
//    echo "fuck u";
//    exit();
//  }
//}

//*************************************
//   Arguments
//*************************************

if (@is_array($_REQUEST)) {
  $GPCVARS = $_REQUEST;
} else {
  $GPCVARS=array_merge($HTTP_COOKIE_VARS, $HTTP_GET_VARS, $HTTP_POST_VARS);
}

if ( isset($_FILES) ){
  $GPCVARS = array_merge($GPCVARS, $_FILES);
}

if ( isset($GPCVARS['PHP_AUTH_USER']) || isset($GPCVARS['PHP_AUTH_PW']) ) {
  // anti cracker protection
  unset($PHP_AUTH_USER);
  unset($PHP_AUTH_PW);
}

//
// Webbox : Get Action 
// prefered action : PATH_INFO [/Class/Method]
// alternate action : WBA parameter [WBA=Class.Method]
//

if (@isset($_SERVER['PATH_INFO'])) {
  $liact = explode('/',$_SERVER['PATH_INFO']);
  @list($dumb,$class,$method,$arg[0],$arg[1]) = $liact;
} elseif (@isset($GPCVARS['WBA']))  {
  $liact = explode('.',$GPCVARS['WBA']);
  list($class,$method) = $liact;
} else {
  print("url corrupted\n");
  exit (1);
}

//echo "<pre>\n";
//echo "dumb = ".$dumb.".\n";
//echo "class = ".$class.".\n";
//echo "method = ".$method.".\n";
//echo "arg1 = ".$arg[0].".\n";
//echo "arg2 = ".$arg[1].".\n";
//print_r($_SERVER);
//echo "</pre>\n";
//exit;


//********************************************
// Webbox : arguments mapping for class/method
//********************************************

$argsmapper = array(
    "Doc" => array("DID"),
    "Doc/GetData" => array("DID", "FILENAM"),
    "Doc/DispSplittedContent" => array("DID", "FILENAM"),
    "Doc/DispSplittedHeader" => array("DID", "FILENAM"),
    "Doc/DispPlugin" => array("DID", "FILENAM"),
    "Doc/Download" => array("DID", "FILENAM"),
    "Doc/AddXapp1" => array("XCLASS", "DID"),
    "Doc/Sign1Step2" => array("DID", "SIGNTEL"),
    "Mgmt/View" => array("DID"),
    "Mgmt/CreAcc3" => array("UCODE"),
    "Mgmt/Attach" => array("DID", "MODE"),
    "Mgmt/UnCreate" => array("DID"),
    "Mgmt/UnCreateMgt" => array("DID"),
    "Mgmt/GetPubSign" => array("SIGNENGINE"),
    "Admin/View " => array("DID"),
    "Admin/D4U" => array("UID"),
    "Admin/U4D" => array("DID"),
    "Admin/CloseRes" => array("TAG1", "TAG2")
);

if ( isset($argsmapper[$class."/".$method]) ) {
    $argtab = $argsmapper[$class."/".$method];
} else if ( isset($argsmapper[$class]) ) {
    $argtab = $argsmapper[$class];
} else {
    $argtab = array();
}

foreach ($argtab as $ind => $argi) {
   $GPCVARS[$argi] = $arg[$ind]; 
}

//***************************************
// Webbox : global config
//***************************************

$conf = new Config;

umask(0002);

//***************************************
// Webbox : lang management
//***************************************

// default lang
$lng="EN";

// prefered browser language
$pbl = strtoupper(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
if ( isset($conf->LangSet[$pbl]) ) {
  $lng = $pbl;
} 

// check cookie first
if ( isset($_COOKIE['CSXLNG']) ) {
  $lng = $_COOKIE['CSXLNG'];
} else {
  session_start();
  if ( isset($_SESSION['CSXLNG']) ) {
    $lng = $_SESSION['CSXLNG'];
  }
}

//***************************************
// Webbox : require class and call Action
//***************************************

if ($class && $method) {
  $classfile = "./app/class.".$class.".php";
  if ( file_exists($classfile) ) {
    include_once $classfile;
    $obj = new $class($conf, $lng);
    
    if ( method_exists($obj,"IsCallable") && $obj->IsCallable($GPCVARS,$method) ) {
 
      $obj->$method($GPCVARS);

      unset($class);
      unset($method);
      unset($classfile);
      unset($obj);
    } else {
      print("attempted to access private or unknown method $method\n");
    }
  } else {
    print ("class $classfile does not exists\n");
  }
}
    
