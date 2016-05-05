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
  @list($dumb,$class,$method,$arg1,$arg2) = $liact;
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
//echo "arg1 = ".$arg1.".\n";
//echo "arg2 = ".$arg1.".\n";
//print_r($_SERVER);
//echo "</pre>\n";
//exit;


//***************************************
// Webbox : cloudsx specific
//***************************************

// for class Doc, assume arg1 if present is DID
if ( $class == "Doc" and $arg1 != "" ) {
  $GPCVARS["DID"] = $arg1;
}

// for method Doc/GetData, assume arg2 if present is FILENAM
if ( $class == "Doc" and $method == "GetData" and $arg2 != "" ) {
  $GPCVARS["FILENAM"] = $arg2;
}

// for method Doc/DispSplittedContent, assume arg2 if present is FILENAM
if ( $class == "Doc" and $method == "DispSplittedContent" and $arg2 != "" ) {
  $GPCVARS["FILENAM"] = $arg2;
}

// for method Doc/DispSplittedHeader, assume arg2 if present is FILENAM
if ( $class == "Doc" and $method == "DispSplittedHeader" and $arg2 != "" ) {
  $GPCVARS["FILENAM"] = $arg2;
}

// for method Doc/DispPlugin, assume arg2 if present is FILENAM
if ( $class == "Doc" and $method == "DispPlugin" and $arg2 != "" ) {
  $GPCVARS["FILENAM"] = $arg2;
}

// for method Doc/Download assume arg2 if present is FILENAM
if ( $class == "Doc" and $method == "Download" and $arg2 != "" ) {
  $GPCVARS["FILENAM"] = $arg2;
}

// for method Mgmt/View , assume arg1 if present is DID
if ( $class == "Mgmt" and $method == "View" and $arg1 != "" ) {
  $GPCVARS["DID"] = $arg1;
}

// for method Mgmt/CreAcc3 , assume arg1 if present is UCODE
if ( $class == "Mgmt" and $method == "CreAcc3" and $arg1 != "" ) {
  $GPCVARS["UCODE"] = $arg1;
}

// for method Mgmt/Attach, assume arg1 = DID , arg2 = MODE
if ( $class == "Mgmt" and $method == "Attach" and $arg1 != "" and $arg2 != "" ) {
  $GPCVARS["DID"] = $arg1;
  $GPCVARS["MODE"] = $arg2;
}

// for method Admin/View , assume arg1 if present is DID
if ( $class == "Admin" and $method == "View" and $arg1 != "" ) {
  $GPCVARS["DID"] = $arg1;
}

// for method Admin/D4U , assume arg1 if present is UID
if ( $class == "Admin" and $method == "D4U" and $arg1 != "" ) {
  $GPCVARS["UID"] = $arg1;
}

// for method Admin/U4D , assume arg1 if present is DID
if ( $class == "Admin" and $method == "U4D" and $arg1 != "" ) {
  $GPCVARS["DID"] = $arg1;
}

// for method Admin/CloseRes , assume arg1/arg2 if present is TAG
if ( $class == "Admin" and $method == "CloseRes" and $arg1 != "" and $arg2 != "" ) {
  $GPCVARS["TAG"] = $arg1."/".$arg2;
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

#echo "prout";
#exit(0);

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
    
