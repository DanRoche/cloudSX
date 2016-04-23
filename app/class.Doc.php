<script language="php">

include_once "Savant3/Savant3.php";
include_once "class.Data.php";
include_once "class.URL.php";
include_once "class.PluginLib.php";
include_once "class.Debug.php";

class Doc {

  //===============================================
  // framework
  //===============================================

  var $public_functions;
  var $gconf;
  var $lng;

  function __construct($conf, $lng) { 
    // define webbox callable function
    $this->public_functions = array(
				    "ExamData" => TRUE,
				    "SetLng" => TRUE,
				    "Display" => TRUE,
				    "DispPlugin" => TRUE,
				    "DispThumbs" => TRUE,
				    "DispParam" => TRUE,
				    "DispShare" => TRUE,
				    "DispAttach" => TRUE,
				    "AddFile1" => TRUE,
				    "AddFile2" => TRUE,
				    "AddFileJQFU" => TRUE,
				    "ActionOnFiles" => TRUE,
				    "DelFile2" => TRUE,
				    "RenFile2" => TRUE,
				    "Update" => TRUE,
				    "Delete" => TRUE,
				    "SetAuth" => TRUE,
				    "PassAdm" => TRUE,
				    "Fige" => TRUE,
				    "Defige" => TRUE,
				    "GetZip" => TRUE,
				    "DispBlog" => TRUE,
				    "AddBlog" => TRUE,
				    "DelBlogAll" => TRUE,
				    "DelBlogOne" => TRUE,
				    "SendMail" => TRUE,
				    "GetData" => TRUE,
				    "Download" => TRUE,
				    "ExternUrl" => TRUE
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

  function ExamData($vars) {

    //phpinfo();
    //exit(0);

    $this->debug->Debug1("ExamData");
    exit(0);

    //$dosinf = $this->data->FetchDosInfo($vars['DID']);
    //echo "<pre>\n";
    //print_r($dosinf);
    //print_r($this->gconf);
    //echo "</pre>\n";

    //echo "<pre>".$_SERVER['HTTP_USER_AGENT']."</pre>\n";

    //exit(0);

    //echo "status = ".$this->data->CurrentUserStatus();
    //exit(0);
   
    $dosinf = $this->data->FetchDosInfo($vars['DID']);

    @session_start();
    $this->debug->Debug2("DOSINFO", $dosinf);
    $this->debug->Debug2("COOKIE", $_COOKIE);
    $this->debug->Debug2("SESSION", $_SESSION);
    return;

    $tpl = new Savant3();

    $urls = URL::GetURLByInfo($this->gconf, $dosinf);

    $tpl->assign("MSG", "deb url func");
    $tpl->assign("URL", $urls);
   
    $tpl->display("tpl_doc/debug3.html");
 

  }

  function SetLng($vars) {

    @session_start();
    $_SESSION['CSXLNG'] = $vars['LNG']; 
    setcookie('CSXLNG', $vars['LNG'], time()+315360000, "/");
 
    $urls = URL::GetURLByDID($this->gconf, $vars['DID']);
    $url = $urls->GetDosMethod('Display' );
  
    header("Location: ".$url);
  }

  function Display($vars) {
    $tpl = new Savant3();

    if ( ! isset($vars['DID']) ) {
      $tpl->assign("MSG", "Veuillez fournir un identifiant de porte-documents !");
      $urls = URL::GetURLSimple($this->gconf);
      $tpl->assign("URL", $urls);
      $tpl->assign("RURL", $urls->GetMgmtMethod('Index'));
      $messages = $this->data->GetMessages($this->lng);
      $tpl->setMessages($messages);
      $tpl->display("tpl_doc/error2.html");
      return;
    }

    $dosinf = $this->data->FetchDosInfo($vars['DID'], 1);

    if ( $dosinf == null ) {
      $tpl->assign("MSG", "Identifiant de porte-documents non valide !");
      $urls = URL::GetURLSimple($this->gconf);
      $tpl->assign("URL", $urls);
      $tpl->assign("RURL", $urls->GetMgmtMethod('Index'));
      $messages = $this->data->GetMessages($this->lng);
      $tpl->setMessages($messages);
      $tpl->display("tpl_doc/error2.html");
      return;

     } 
      
    $ra = $this->Authenticate($dosinf);

    $tpl->assign("DID", $vars['DID']);
    $tpl->assign("APPNAM", $this->gconf->name);
    $tpl->assign("SELFPROMO", $this->SelfPromoteCookie($dosinf));

    $urls = URL::GetURLByInfo($this->gconf, $dosinf);
    // for menu we need content display parameters and the lang
    $urls->InitContentDisplayParameters();
    $urls->SetLng($this->lng);

    $tpl->assign("URL", $urls);
    $tpl->assign("DOSINFO", $dosinf);
    $tpl->assign("LNG", $this->lng);
    $messages = $this->data->GetMessages($this->lng);
    $tpl->setMessages($messages);
    $tpl->assign("LANGSET", $this->gconf->LangSet);

    switch($ra) {
    case ACCES_NO:
      exit(0);
      break;
    case ACCES_RW:
      $tpl->assign("MODACC", 'RW');
      break;
    case ACCES_RO:
      $tpl->assign("MODACC", 'RO');
      break;
    }
    $tpl->assign("ATTACHARG", $dosinf['did']."/".$ra );

    if ( ! isset($vars['PAGE']) ) {
      /* 
       * guess what page to display 
       * if no file -> add
       * if message -> blog
       */
      $nbfil = count($dosinf['filelist']);
      if ( $nbfil == 0 ) {
	$tpl->assign("PAGE", "AddFile1");
      } else {
	if ( $dosinf['hasblog'] && $dosinf['blogcnt'] >= 1 ) {
	  $tpl->assign("PAGE", "DispBlog");
	} else {
	  $tpl->assign("PAGE", "DispThumbs");
	}
      }
    } else {
      $tpl->assign("PAGE", $vars['PAGE']);
    }

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

    $tpl->display("tpl_doc/pagedoc.html");
    
  }

  function DispPlugin($vars) {
    $tpl = new Savant3();

    //$this->debug->Debug2("DISPPLUGIN", $vars);
    //return;
 
    $dosinf = $this->data->FetchDosInfo($vars['DID'], 1);
    $ra = $this->AuthenticateInner($dosinf);

    $file = $vars['FILENAM'];
    $messages = $this->data->GetMessages($this->lng);

    $plugnam = $vars['PLUGIN'];
    $classplug = "./plugins/".$plugnam."/class.".$plugnam.".php";
    if ( file_exists($classplug) ) {

      $pluglib = new PluginLib($dosinf, $file, $this->gconf, $messages);

      include_once $classplug;
      $obj = new $plugnam($pluglib);
    
      $obj->Display();

      unset($classplug);
      unset($plugnam);
      unset($obj);

    } else {

      print ("plugin $classplug cannot be found\n");

    } 
  }

  function DispThumbs($vars) {
    $tpl = new Savant3();

    $dosinf = $this->data->FetchDosInfo($vars['DID'], 1);
    $ra = $this->AuthenticateInner($dosinf);

    //$this->debug->Debug2("DOSINFO", $dosinf);
    //return;

    $urls = URL::GetURLByInfo($this->gconf, $dosinf);
    // for thumbs we need content display parameters
    $urls->InitContentDisplayParameters();

    $tpl->assign("URL", $urls);
    $tpl->assign("DOSINFO", $dosinf);
    $messages = $this->data->GetMessages($this->lng);
    $tpl->setMessages($messages);

    $tpl->display("tpl_doc/part_thumbs.html");
  }

  function DispParam($vars) {
    $tpl = new Savant3();

    $dosinf = $this->data->FetchDosInfo($vars['DID']);
    $ra = $this->AuthenticateInner($dosinf);

    $urls = URL::GetURLByInfo($this->gconf, $dosinf);
    $tpl->assign("URL", $urls);
    $tpl->assign("DOSINFO", $dosinf);
    $tpl->assign("LNG", $this->lng);
    $messages = $this->data->GetMessages($this->lng);
    $tpl->setMessages($messages);

    switch($ra) {
    case ACCES_RW:
      $utyp = $this->data->CurrentUserStatus();
      switch($utyp) {
      case 'none':
      case 'std':
	$datesel = "no";
	break;
      case 'premium':
      case 'admin':
	$datesel = "yes";
	break;
      }
      if ( isset($dosinf['passadm']) ) {
	$tpl->assign("FIGACTION", "defige" );
      } else {
	$tpl->assign("FIGACTION", "fige" );
      }
      $tpl->assign("DATESEL", $datesel );
      $tpl->display("tpl_doc/part_paramw.html");
      break;
    case ACCES_RO:
      $tpl->display("tpl_doc/part_paramr.html");
      break;
    }

  }

  function DispShare($vars) {
    $tpl = new Savant3();

    $dosinf = $this->data->FetchDosInfo($vars['DID']);
    $ra = $this->AuthenticateInner($dosinf);

    $urls = URL::GetURLByInfo($this->gconf, $dosinf);
    $tpl->assign("URL", $urls);
    $tpl->assign("DOSINFO", $dosinf);
    $tpl->assign("LNG", $this->lng);
    $messages = $this->data->GetMessages($this->lng);
    $tpl->setMessages($messages);

    // generate the mailto url :
    $subj = $messages['ml2_subj'];
    $body = $tpl->fetch("tpl_doc/mailto_share.txt");
    $m2url = $urls->GetMailto($subj,$body);

    $tpl->assign("MAILTO", $m2url);

    $this->Authenticate($dosinf);
    $tpl->display("tpl_doc/part_share.html");
  }

  function DispAttach($vars) {
    
    //$this->debug->Debug1("DispAttach");
    $tpl = new Savant3();

    $dosinf = $this->data->FetchDosInfo($vars['DID']);
    $ra = $this->AuthenticateInner($dosinf);
 
    $urls = URL::GetURLByInfo($this->gconf, $dosinf);
    $tpl->assign("URL", $urls);
    $tpl->assign("DOSINFO", $dosinf);
    $tpl->assign("LNG", $this->lng);
    $messages = $this->data->GetMessages($this->lng);
    $tpl->setMessages($messages);
    $tpl->assign("MODE", $ra);

    $tpl->display("tpl_doc/part_attach.html");
 }


  function AddFile1($vars) {

    $tpl = new Savant3();

    $dosinf = $this->data->FetchDosInfo($vars['DID']);
    $ra = $this->AuthenticateInner($dosinf);

    $urls = URL::GetURLByDID($this->gconf, $vars['DID'] );
    $tpl->assign("URL", $urls);
    $tpl->assign("DID", $vars['DID']);
    $messages = $this->data->GetMessages($this->lng);
    $tpl->setMessages($messages);

    if ( isset($vars['mode']) ) {
      switch($vars['mode']) {
      case "classic":
	$tpl->display("tpl_doc/part_addfil_simple.html");
	break;
      case "jqfu":
	$tpl->display("tpl_doc/part_addfil_jqfu.html");
	break;
      }
    } elseif ( $this->gconf->JQFileUpload ) {
      $tpl->display("tpl_doc/part_addfil_jqfu.html");
    } else {
      $tpl->display("tpl_doc/part_addfil_simple.html");
    }

  }

 function AddFile2($vars) {

    //echo "<pre>\n";
    //print_r($vars);
    //echo "</pre>\n";
    //exit(0);

    //$this->debug->Debug1("addfile2");
    //exit(0);

    $dosinf = $this->data->FetchDosInfo($vars['DID']);
    $ra = $this->AuthenticateInner($dosinf);

    $res = $this->data->AddClassicUploadFiles($dosinf['did'], $vars);
 
    $urls = URL::GetURLByInfo($this->gconf, $dosinf);

    $url = $urls->GetDosMethod('Display');
    header("Location: ".$url);
  }

  function AddFileJQFU($vars) {

    //$this->debug->DebugToFile("/tmp/jqfu_deb.log", $vars);

    $dosinf = $this->data->FetchDosInfo($vars['DID']);
    $ra = $this->AuthenticateInner($dosinf);

    $ret = $this->data->AddJQFUFiles($dosinf['did'], $vars);
    
    if ( $ret == null ) {
      return;
    }

    // jquery file upload need a json return 
    header('Content-type: application/json');
    $tpl = new Savant3();
    $tpl->assign("NAME", $ret['name']);
    $tpl->assign("TYPE", $ret['type']);
    $tpl->assign("SIZE", $ret['size']);
    $tpl->display("tpl_doc/jqfu_ret.json");

  }

  function ActionOnFiles($vars) {
    //$this->debug->Debug2("ActionOnFile", $vars);
    
    switch($vars['ACTION']) {
    case "DEL":
      $this->DelFile1($vars);
      break;
    case "REN":
      $this->RenFile1($vars);
      break;
    default:
      echo "Unknown Action : Should not happen !";
      return;
    }
  }

  // called by ActionOnFiles
  function DelFile1($vars) {

    $tpl = new Savant3();

    $dosinf = $this->data->FetchDosInfo($vars['DID']);
    $ra = $this->AuthenticateInner($dosinf);

    $urls = URL::GetURLByDID($this->gconf, $vars['DID']);
    $tpl->assign("URL", $urls);
    $messages = $this->data->GetMessages($this->lng);
    $tpl->setMessages($messages);
    $tpl->assign("DID", $vars['DID']);

    if ( isset($vars["CFI"]) ) {
      $tpl->assign("DLIST", $vars["CFI"]);
      $tpl->display("tpl_doc/part_delfil1.html");
    } else {
      $tpl->assign("MSG", $messages['del_err1']);
      $tpl->display("tpl_doc/part_error1.html");
    }

  }

  function DelFile2($vars) {

    $dosinf = $this->data->FetchDosInfo($vars['DID']);
    $ra = $this->Authenticate($dosinf);

    $this->data->DelDosFiles($dosinf['did'], $vars['LDL']);

    $urls = URL::GetURLByInfo($this->gconf, $dosinf);
    $url = $urls->GetDosMethod('Display');
    header("Location: ".$url);
  }

  // called by ActionOnFiles
  function RenFile1($vars) {

    $tpl = new Savant3();

    $dosinf = $this->data->FetchDosInfo($vars['DID']);
    $ra = $this->AuthenticateInner($dosinf);

    $urls = URL::GetURLByDID($this->gconf, $vars['DID']);
    $tpl->assign("URL", $urls);
    $messages = $this->data->GetMessages($this->lng);
    $tpl->setMessages($messages);
    $tpl->assign("DID", $vars['DID']);

    if ( isset($vars["CFI"]) ) {
      $tpl->assign("RLIST", $vars["CFI"]);
      $tpl->display("tpl_doc/part_renfil1.html");
    } else {
      $tpl->assign("MSG", $messages['ren_err1']);
      $tpl->display("tpl_doc/part_error1.html");
    }

  }
  function RenFile2($vars) {

    //$this->debug->Debug2("RenFile2", $vars);
    //exit(0);

    $dosinf = $this->data->FetchDosInfo($vars['DID']);
    $ra = $this->Authenticate($dosinf);

    $this->data->RenDosFiles($dosinf['did'], $vars['ORN'], $vars['NRN']);

    $urls = URL::GetURLByInfo($this->gconf, $dosinf);
    $url = $urls->GetDosMethod('Display');
    header("Location: ".$url);
  }


  function Update($vars) {

    //$this->debug->Debug2("update", $vars);
    //exit(0);

    $dosinf = $this->data->FetchDosInfo($vars['DID']);
    $ra = $this->Authenticate($dosinf);

    $this->data->UpdateDosStruct($vars['DID'], $vars);

    $urls = URL::GetURLByInfo($this->gconf, $dosinf);
    $url = $urls->GetDosMethod('Display');
    header("Location: ".$url);
 
  }

  function Delete($vars) {

    //$this->debug->Debug2("update", $vars);
    //exit(0);

    $dosinf = $this->data->FetchDosInfo($vars['DID']);
    $ra = $this->Authenticate($dosinf);

    $this->data->DeleteDosStruct($vars['DID']);
 
    $urls = URL::GetURLSimple($this->gconf);
    $url = $urls->GetMgmtMethod('Index');
    header("Location: ".$url);
 
  }

  function SetAuth($vars) {

    //$this->debug->Debug2("setauth", $vars);
    //exit(0);

    @session_start();
    $appnam = $this->gconf->name;
    $_SESSION[$appnam.'_'.$vars['DID']] = $vars['PASSWD']; 
 
    $urls = URL::GetURLByDID($this->gconf, $vars['DID']);
    $url = $urls->GetDosMethod('Display' );
  
    header("Location: ".$url);
 
  }

  function PassAdm($vars) {

    //$this->debug->Debug2("passadm", $vars);
    //exit(0);

    $dosinf = $this->data->FetchDosInfo($vars['DID']);
    $ra = $this->Authenticate($dosinf);

    if ( $vars['PASSADM'] == $dosinf['passadm'] ) {
      // dont unlock here !
      //$this->data->UnlockDos($dosinf['did']);
      @session_start();
      $appnam = $this->gconf->name;
      $_SESSION[$appnam.'ADM_'.$vars['DID']] = $vars['PASSADM']; 

      $urls = URL::GetURLByInfo($this->gconf, $dosinf);
      $url = $urls->GetDosMethod('Display');
      header("Location: ".$url);

    } else {
      $tpl = new Savant3();

      $urls = URL::GetURLByInfo($this->gconf, $dosinf);
      $tpl->assign("URL", $urls);
      $messages = $this->data->GetMessages($this->lng);
      $tpl->setMessages($messages);
      $tpl->assign("MSG", "Identifiant Administrateur incorrect !");
      $tpl->assign("RURL", $urls->GetDosMethod('Display') );
      $tpl->display("tpl_doc/error2.html");
    }

  }


  function Fige($vars) {

    //$this->debug->Debug1("Fige");
    //exit(0);

    $dosinf = $this->data->FetchDosInfo($vars['DID']);
    $ra = $this->Authenticate($dosinf);

    $this->data->LockDos($vars['DID'], $vars['PASSADM']);

    $urls = URL::GetURLByDID($this->gconf, $vars['DID']);
    $url = $urls->GetDosMethod('Display', "PAGE=DispParam");
    header("Location: ".$url);

  }

  function Defige($vars) {

    //$this->debug->Debug1("Defige");
    //exit(0);

    $dosinf = $this->data->FetchDosInfo($vars['DID']);
    $ra = $this->Authenticate($dosinf);

    $this->data->UnLockDos($vars['DID']);

    $urls = URL::GetURLByDID($this->gconf, $vars['DID']);
    $url = $urls->GetDosMethod('Display', "PAGE=DispParam");
    header("Location: ".$url);

  }

  function GetZip($vars) {

    //$this->debug->Debug2("update", $vars);
    //exit(0);

    $dosinf = $this->data->FetchDosInfo($vars['DID']);
    $ra = $this->Authenticate($dosinf);
    
    $this->data->GenerateAndSendZip($vars['DID']);

  }

  function DispBlog($vars) {
    $tpl = new Savant3();

    $dosinf = $this->data->FetchDosInfo($vars['DID']);
    $ra = $this->Authenticate($dosinf);

    $blogdata = $this->data->FetchBlog($vars['DID']);

    $urls = URL::GetURLByInfo($this->gconf, $dosinf);
    $tpl->assign("URL", $urls);
    $tpl->assign("DOSINFO", $dosinf);
    $tpl->assign("BLOGDATA", $blogdata);
 
    $messages = $this->data->GetMessages($this->lng);
    $tpl->setMessages($messages);

    // forum is writable , whatever folder status !
    $tpl->display("tpl_doc/part_blog.html");

  }

  function AddBlog($vars) {

    //$this->debug->Debug1("AddBLog");
  
    $dosinf = $this->data->FetchDosInfo($vars['DID']);
    $ra = $this->Authenticate($dosinf);

    $this->data->AddBlog($vars['DID'], $vars['BLOGSIGN'],$vars['BLOGCOMM']);

    $urls = URL::GetURLByDID($this->gconf, $vars['DID']);
    $url = $urls->GetDosMethod('Display', "PAGE=DispBlog");
    header("Location: ".$url);
  }
  

  function DelBlogAll($vars) {

    //$this->debug->Debug1("DelBLogAll");

    $dosinf = $this->data->FetchDosInfo($vars['DID']);
    $ra = $this->Authenticate($dosinf);

    $this->data->DelBlogAll($vars['DID']);

    $urls = URL::GetURLByDID($this->gconf, $vars['DID']);
    $url = $urls->GetDosMethod('Display' );
    header("Location: ".$url);
  }


  function DelBLogOne($vars) {
    //$this->debug->Debug1("DelBlogOne");

    $dosinf = $this->data->FetchDosInfo($vars['DID']);
    $ra = $this->AuthenticateInner($dosinf);
    
    $this->data->DelBlogEntry($vars['DID'], $vars['COMMID']);

    $urls = URL::GetURLByDID($this->gconf, $vars['DID']);
    $url = $urls->GetDosMethod('Display', "PAGE=DispBlog");
    header("Location: ".$url);
  }

  function SendMail($vars) {
    //$this->debug->Debug2("SendMail", $vars);
    //exit(0);

    $dosinf = $this->data->FetchDosInfo($vars['DID']);
    $ra = $this->AuthenticateInner($dosinf);

    // multiline mail processing
    $mlm = explode("\n", $vars["SNDRMEL"]);

    //$this->debug->Debug2("MLM", $mlm);
    //exit(0);

    $tpl = new Savant3();
    $messages = $this->data->GetMessages($this->lng);
    $tpl->setMessages($messages);
    $urls = URL::GetURLByInfo($this->gconf, $dosinf);

    $cntmel = 0;
    foreach($mlm as $ind => $melto) {

      if ( preg_match("/.*@.*\..*/", $melto) ) {

	$tpl->assign("URL", $urls);
	$tpl->assign("FROM", $vars["SNDRNAM"] );
	$tpl->assign("MESSG", $vars["SNDRMSG"] );
	$tpl->assign("DOSINFO", $dosinf );
	$tpl->assign("APPNAM", $this->gconf->name );

	$tmpfname = tempnam("/tmp", "CSXmel");
	$fm = fopen($tmpfname, "a");
	fwrite($fm,$tpl->fetch("tpl_doc/mail_share.html"));
	fclose($fm);

	$launch=$this->gconf->TopAppDir."/script/melsnd ".$melto." \"".$messages['mel_tit']."\" ".$tmpfname;
	system($launch);
	$cntmel += 1;
      }
    }
        
    $tpl2 = new Savant3();
    $tpl2->assign("URL", $urls);
    $tpl2->setMessages($messages);
    $tpl2->assign("MSG", sprintf($messages['mel_sendok'],$cntmel));
    $tpl2->display("tpl_doc/part_error1.html");

  }

  //===============================================
  // display files and external url
  //===============================================

  function GetData($vars) {


    // A quoi sert cette fonction ??

    //  $this->debug->Debug2("GetData", $vars);

    header('Content-type: application/json');

    $tpl = new Savant3();
    $tpl->display("tpl_doc/jqfu_ret.json");


    exit(0);
  }

  function Download($vars) {

    //$this->debug->Debug2("Download", $vars);
    //exit(0);

    $dosinf = $this->data->FetchDosInfo($vars['DID']);
    $ra = $this->Authenticate($dosinf);

    $file = $vars['FILENAM'];

    //$this->debug->Debug2("Download", $dosinf);
    //exit(0);

    $filpath = $this->gconf->TopAppDir.$this->gconf->DataDir."/".$dosinf['rdir']."/".$file;

    //echo $filpath;
    //exit(0)

    // force download

    header("MIME-Version: 1.0");
    header("Expires: Sat, 01 Jan 2000 05:00:00 GMT");        // date in the past
    header("Last-Modified:".date("D, d M Y H:i:s")." GMT");  // always modified
    header("Cache-Control: no-cache, must-revalidate");      // HTTP/1.1
    header("Pragma: no-cache");                              // HTTP/1.0
    header("Content-type: application/download");   
    header("Content-Disposition: attachment; filename=$file");
    header("Content-Description: File"); 

    $fn=fopen($filpath , "r"); 
    return fpassthru($fn); 

    exit(0);
  }

  function ExternUrl($vars) {
    //$this->debug->Debug2("ExternUrl", $vars);
    //exit(0);

    $tpl = new Savant3();

    $tpl->assign("XU", $vars['XU']);
   
    $tpl->display("tpl_doc/part_xu.html");
 
  }

  //===============================================
  // tools
  //===============================================

  function Authenticate($dosinfo) {

    $ra = $this->data->CheckAuth($dosinfo);

    if ( $ra == ACCES_NO ) {

      $tpl = new Savant3();

      $urls = URL::GetURLByInfo($this->gconf, $dosinfo);
      $tpl->assign("URL", $urls);
      $tpl->assign("DID", $dosinfo["did"]);
      $tpl->assign("APPNAM", $this->gconf->name);

      $messages = $this->data->GetMessages($this->lng);
      $tpl->setMessages($messages);

      $tpl->display("tpl_doc/askpass.html");

      exit(0);
    }

    return($ra);

  }    

  function AuthenticateInner($dosinfo) {

    $ra = $this->data->CheckAuth($dosinfo);

    if ( $ra == ACCES_NO ) {

      $tpl = new Savant3();

      $urls = URL::GetURLByInfo($this->gconf, $dosinfo);
      $tpl->assign("URL", $urls);
      $tpl->assign("DID", $dosinfo["did"]);

      $messages = $this->data->GetMessages($this->lng);
      $tpl->setMessages($messages);

      $tpl->display("tpl_doc/part_auth.html");
      
      exit(0);
    }

    return($ra);

  }    

  function SelfPromoteCookie($dosinfo) {
    
    if ( @$this->gconf->SelfPromote != "on" ) {
      // none if not configured
      return('none');
    }

    if ( @$_COOKIE[$this->gconf->name."_Visited"] != 1 ) {
      setcookie($this->gconf->name."_Visited", 1, time()+315360000, "/");
      return('all');
    }

    if ( strpos(@$_SERVER['HTTP_REFERER'], "DosList") ) {
      // if coming from mgmt
      setcookie($this->gconf->name."_Visited_".$dosinfo['did'], 1, time()+315360000, "/");
      return('none');
    }

    if ( @$_COOKIE[$this->gconf->name."_Visited_".$dosinfo['did']] != 1 ) {
      setcookie($this->gconf->name."_Visited_".$dosinfo['did'], 1, time()+315360000, "/");
      return('doc');
    }

    return('none');

  }

  //===============================================
  // end
  //===============================================

}

</script>
