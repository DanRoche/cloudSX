<script language="php">

include_once "conf.ContentDisplayParameters.php";

class URL {

  //===============================================
  // class
  //===============================================

  protected $gconf;
  protected $dosinfo;
  protected $lng;
  
  function __construct($conf) { 
    $this->gconf = $conf;
    $this->lng = "EN";   // default
  }
  
  //===============================================
  // setters
  //===============================================
  
  function InitByInfo($pinf)  { 
    if ( $pinf ) {
      $this->dosinfo = $pinf;
    }
  }
  
  function InitByDID($did)  { 
    // generate a pseudo dosinfo for url class ( faster than FetchDosInfo ?)
    $this->dosinfo = Array('did' => $did); 
  }
  
  function SetLng($lang)  { 
    $this->lng = $lang;
  }
  
  //===============================================
  // cloudsX urls
  //===============================================
  
  function GetDosMethod($method,$qs="") {
    return($this->GetDosMethodArg1($method,"",$qs));
  }
  
  function GetDosMethodArg1($method,$arg1,$qs="") {
    if ( $this->dosinfo == null ) {
      return("no_dosinfo");
    }
    $theu = $this->gconf->TopAppUrl;
    // type wbx|rewrite defined in Config
    $theu .= $this->gconf->csxDocClass;
    $theu .= "/".$method."/".$this->dosinfo['did'];
    if ( $arg1 != "" ) {
      $theu .= "/".$arg1;
    }
    if ( $qs != "" ) {
      $theu .= "?".$qs;
    }
    return($theu);
  }
  
  function GetDosStruct() {
    if ( $this->dosinfo == null ) {
      return("no_dosinfo");
    }
    $theu = $this->gconf->TopAppUrl;
    $theu .= $this->gconf->DataDir;
    $theu .= "/".$this->dosinfo['rdir'];
    return($theu);
  }
  
  function GetAbsDosStruct() {
    $tmp = $this->GetDosStruct();
    $theu = "http://".$_SERVER['SERVER_NAME'].$tmp;
    return($theu);
  }
  
  function GetDosData($file) {
    if ( $this->dosinfo == null ) {
      return("no_dosinfo");
    }
    
    $dsplug = $this->GetContentDisplayParameters($file);
    
    $theu = $this->GetPluginDosData($dsplug,$file);
    return($theu);
  }
  
  function GetDosDownload($file) {
    if ( $this->dosinfo == null ) {
      return("no_dosinfo");
    }
    
    $theu = $this->GetDosMethodArg1("Download",$file);
    return($theu);
    
  }

  function GetRawDosData($file) {
    // used only by plugins now ! 
    // since no-frame display !!
    if ( $this->dosinfo == null ) {
       return("no_dosinfo");
    }
    $theu = $this->GetDosStruct();
    $theu .= "/".$file;
    return($theu);
  }
 
  function GetAbsRawDosData($file) {
    // used only by mobile app ( json export )
    $tmp = $this->GetRawDosData($file);
    $theu = "http://".$_SERVER['SERVER_NAME'].$tmp;
    return($theu);
  }
  
  function GetDosThumb($file) {
    if ( $this->dosinfo == null ) {
      return("no_dosinfo");
    }
    
    $thumf = $this->gconf->AbsDataDir."/".$this->dosinfo['rdir'];
    $thumf .= "/.thumbs/".$file.".png";
    
    if ( file_exists($thumf) ) {
      $theu = $this->gconf->TopAppUrl;
      $theu .= $this->gconf->DataDir;
      $theu .= "/".$this->dosinfo['rdir'];
      $theu .= "/.thumbs/".$file.".png";
    } else {
      $theu = $this->gconf->TopAppUrl;
      $theu .= "/default_icons/file.png";
    }
    return($theu);
  }
  
  function GetPluginDosData($plugin,$file) {
    if ( $this->dosinfo == null ) {
      return("no_dosinfo");
    }
    $qs = "PLUGIN=".$plugin;
    $theu = $this->GetDosMethodArg1("DispPlugin",$file,$qs);
    return($theu);
  }

  function GetPluginMethod($plugin,$pmethod,$filepath) {
     $theu = $this->gconf->TopAppUrl;
     $theu .= "/plugins/".$plugin."/";
     $theu .= $pmethod;
     $theu .= "?FILEPATH=".$filepath;
     return($theu);
  }  

  function GetMinimumURL() {
    $theu = "http://".$_SERVER['SERVER_NAME']."/".$this->dosinfo['did'];
    return($theu);
  }
  
  //===============================================
  // cloudSX inner content URL
  //===============================================
  
  function AddChangeContent($url) {
    $ur2 = "javascript:changeContent('".$url."')";
    return($ur2);
  }
  
  function GetInnerMethod($method,$qs="") {
    $ur1 = $this->GetDosMethod($method,$qs);
    return $this->AddChangeContent($ur1);
  }
  
  function GetInnerMethodArg1($method,$arg1,$qs="") {
    $ur1 = $this->GetDosMethod($method,$arg1,$qs);
    return $this->AddChangeContent($ur1);
  }
  
  function GetInnerData($file) {
    $ur1 = $this->GetDosData($file);
    return $this->AddChangeContent($ur1);
  }
   
   
  //===============================================
  // management urls
  //===============================================
  
  function GetMgmtMethod($method,$qs="") {
    $theu = $this->gconf->TopAppUrl;
    // type wbx|rewrite  defined in Config
    $theu .= $this->gconf->csxMgmtClass;
    $theu .= "/".$method;
    if ( $qs != "" ) {
      $theu .= "?".$qs;
    }
    return($theu);
  }

  function GetMgmtMethodWithDID($method,$did) {
    return( $this->GetMgmtMethod1Arg($method,$did) );
  }
  
  function GetMgmtMethod1Arg($method,$arg) {
    $p1 = $this->GetMgmtMethod($method);
    return($p1."/".$arg);
  }

  function GetAbsMgmtMethod1Arg($method,$arg) {
    $tmp = $this->GetMgmtMethod1Arg($method,$arg);
    $mgproto = $this->MgmtProto($method);
    $theu = $mgproto."://".$_SERVER['SERVER_NAME'].$tmp;
    return($theu);
  }
  
  function GetAbsMgmtMethod($method) {
    $tmp = $this->GetMgmtMethod($method);
    $mgproto = $this->MgmtProto($method);
    $theu = $mgproto."://".$_SERVER['SERVER_NAME'].$tmp;
    return($theu);
  }
  
  function MgmtProto($method) {
	// awfull kludge ! 
	// get http/s proto by the method
	if ( $method == "Index" ) {
		return("http");
	} else {
		return("https");
	}
  }
  

  //===============================================
  // media urls
  //===============================================

  function GetMedia($path) {
    $theu = $this->gconf->TopAppUrl;
    $theu .= $path;
    return($theu);
  }
  
  function GetDefLogo($size) {
    $theu = $this->gconf->TopAppUrl;
    switch($size) {
    case "small": 
      $theu .= $this->gconf->DefLogoSmall;
      break;
    case "large": 
      $theu .= $this->gconf->DefLogoLarge;
      break;
    }
    return($theu);
  }

  function GetAbsDefLogo($size) {
    // used only by mobile app ( json export )
    $tmp = $this->GetDefLogo($size);
    $theu = "http://".$_SERVER['SERVER_NAME'].$tmp;
    return($theu);
  }
  

  //===============================================
  // Static methods
  //===============================================
  
  static function getURLSimple($gconf) {
    
    $uobj = new URL($gconf);
    return($uobj);
  }
  
  static function getURLByInfo($gconf,$pdinf) {
    
    $uobj = new URL($gconf);
    $uobj->InitByInfo($pdinf);
    return($uobj);
  }
  
  static function getURLByDID($gconf,$did) {
    
    $uobj = new URL($gconf);
    $uobj->InitByDID($did);
    return($uobj);
  }

  //===============================================
  // Content Display Parameters
  // needed for plugins display
  //===============================================

  function InitContentDisplayParameters() {
    $this->ContDisParam = Content_Display_Parameters();
  }
  
  //===============================================
  // external URLs
  //===============================================
  
  function GetMeetJitsi() {
    // meetjitsi no longer work inside frame/iframe !!
    $theu = $this->gconf->MeetJitSiUrl."/".$this->gconf->name.$this->dosinfo['did'];
    return($theu);
  }

  function GetFramaDate() {
    $lconvert = Array("FR" => "fr_FR", 
		      "EN" => "en_GB" );
    if ( isset($this->dosinfo['framadate']) ) {
      $theu = $this->gconf->FramaDateUrl."/adminstuds.php?sondage=".$this->dosinfo['framadate']."&lang=".$lconvert[$this->lng];
    } else {
      $theu = "about:blank";
    }
     return($theu);
  }

  function GetInnerDate() {
    $ur1 = $this->GetFramaDate();
    $ur2 = $this->GetDosMethod("ExternUrl","XU=".$ur1);
    return $this->AddChangeContent($ur2);
  }

  //===============================================
  // mailto URL
  //===============================================

  function GetMailto($subj, $body) {
    $theu = "mailto:?to=&subject=";
    $tmp1 = str_replace("\n","%0A",str_replace(" ","%20",$subj));
    $theu .= $tmp1;
    $theu .= "&body=";
    $tmp2 = str_replace("\n","%0A",str_replace(" ","%20",$body));
    $theu .= $tmp2;

    return($theu);
  }
  
  //===============================================
  // internals
  //===============================================
  
  function GetBrowser() {
    
    $ua = $_SERVER['HTTP_USER_AGENT'];
    
    if ( stristr($ua, "edge") ) {
      return "edge";
    }
    if ( stristr($ua, "chrome") ) {
      return "chrome";
    }
    if ( stristr($ua, "firefox") ) {
      return "firefox";
    }
    if ( stristr($ua, "msie") || stristr($ua, "trident") ) {
      return "msie";
    }
    if ( stristr($ua, "safari") ) {
      return "safari";
    }
    if ( stristr($ua, "opera") || stristr($ua, "presto") ) {
      return "opera";
    }
    
  }
   
  function GetFileType($file) {
    return strtolower(substr(strrchr($file,'.'),1)); 
  }

  function GetContentDisplayParameters($file) {
    
    if ( ! isset($this->ContDisParam) ) {
      echo "Content Display Parameters not initialized !";
      exit();
    }
    
    $fext = $this->GetFileType($file);
    
    $step1 = $this->GetBrowser()."|".$fext;
    if ( isset($this->ContDisParam[$step1]) ) {
      return $this->ContDisParam[$step1];
    }
    
    $step2 = $fext;
    if ( isset($this->ContDisParam[$step2]) ) {
      return $this->ContDisParam[$step2];
    }
    
    return $this->ContDisParam["default"];
  }

  //===============================================
  // admin urls
  //===============================================
  
  function GetAdmMethod($method,$qs="") {
    $theu = $this->gconf->TopAppUrl;
    // type wbx|rewrite  defined in Config
    $theu .= $this->gconf->csxAdmClass;
    $theu .= "/".$method;
    if ( $qs != "" ) {
      $theu .= "?".$qs;
    }
    return($theu);
  }

  function GetAdmMethodWithDID($method,$did) {
    return( $this->GetAdmMethod1Arg($method,$did) );
  }
  
  function GetAdmMethod1Arg($method,$arg) {
    $p1 = $this->GetAdmMethod($method);
    return($p1."/".$arg);
  }

  function GetAbsAdmMethod1Arg($method,$arg) {
    $tmp = $this->GetAdmMethod1Arg($method,$arg);
    $theu = "https://".$_SERVER['SERVER_NAME'].$tmp;
    return($theu);
  }
  
  function GetAbsAdmMethod($method) {
    $tmp = $this->GetAdmMethod($method);
    $theu = "https://".$_SERVER['SERVER_NAME'].$tmp;
    return($theu);
  }
  

  
  //===============================================
  // end
  //===============================================
  
}

</script>
