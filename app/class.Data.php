<?php

include_once "class.Debug.php";
include_once "class.URL.php";

define("ACCES_NO", 0);
define("ACCES_RW", 1);
define("ACCES_RO", 2);


class Data {

  //===============================================
  // framework
  //===============================================

  function __construct($conf) { 
    $this->gconf = $conf;
    $this->debug = new Debug();
  }

  //===============================================
  // internal dir structure
  //===============================================

  function CreHashDirs($name) {

    $now=time();
    $ht = array();
    $cnt = 0;


    //$h20 = hash("sha256", $name.$now); 
    $h20 = hash("md5", $name.$now); 
    $tmp = $this->GetAbsDirFromDID($h20);

    while ( file_exists($tmp) and $cnt < 10 ) {
      $morerand = $this->GetRandomString(6);
      $h20 = hash("md5", $name.$now.$morerand); 
      $tmp = $this->GetAbsDirFromDID($h20);
      $cnt += 1;
    }
    if ( $cnt >= 10 ) {
      echo "Ya comme un soucis\n";
      exit(1);
    }

    $ht['H'] = $h20;
    $ht['HD'] = $this->GetDirFromDID($h20);
    $ht['FHD'] = $tmp;
    mkdir($ht['FHD'], 02775, 1);

    return ($ht);
  }

  function GetDirFromDID($did) {
      $tdd = substr($did,0,1)."/".substr($did,1,1)."/".substr($did,2,1)."/". \
	substr($did,3,1)."/".substr($did,4);
      return($tdd);
  }

  function GetDIDFromDir($dir) {
    if ( preg_match("|.*/(.)/(.)/(.)/(.)/(.*)/\.struct|", $dir, $rm) ) {
      $did = $rm[1].$rm[2].$rm[3].$rm[4].$rm[5];
      return($did);
    } else {
      return(NULL);
    }
  }

  function GetAbsDirFromDID($did) {
    return( $this->gconf->AbsDataDir."/".$this->GetDirFromDID($did) );
  }

  //===============================================
  // multi step dos structure creation
  //===============================================

  function DosStructCreateBase($vars) {
    
    $hh = $this->CreHashDirs($vars['DOSNAM']);

    mkdir($hh['FHD']."/.struct", 02775);
    $fp = fopen($hh['FHD']."/.struct/.htaccess", 'w');
    fwrite($fp, "Require all denied\n");
    fclose($fp);

    $urls = URL::GetURLByDID($this->gconf, $hh['H']);

    $fp = fopen($hh['FHD']."/index.html", 'w');
    fwrite($fp, "<html><head>\n");
    fwrite($fp, "<meta http-equiv=\"refresh\" content=\"0; url=".$urls->GetDosMethod('Display')."\" />\n");
    fwrite($fp, "</head></html>\n");
    fclose($fp);
    $fp = fopen($hh['FHD']."/.struct/title", 'w');
    fwrite($fp, $vars['DOSNAM']."\n");
    fclose($fp);
    $fp = fopen($hh['FHD']."/.struct/passwd", 'w');
    fwrite($fp, $vars['DOSPSW']."\n");
    fclose($fp);
    $hh['PASSWD'] =  $vars['DOSPSW'];
    $fp = fopen($hh['FHD']."/.struct/comment", 'w');
    fwrite($fp, $vars['DOSCOM']."\n");
    fclose($fp);
    $fp = fopen($hh['FHD']."/.struct/endoflife", 'w');
    fwrite($fp, $vars['DOSLIM']."\n");
    fclose($fp);
    $this->SetupHtaccessFile($hh['FHD']);
 
    // create a pseudo (incomplete) dosinfo for return
    $dosret = Array();
    $dosret['did'] = $hh['H'];
    $dosret['passwd'] = $hh['PASSWD'];

    return($dosret); 
  }

  function DosStructCreateExtended($did,$vars) {

    $ddir = $this->GetAbsDirFromDID($did);
    if ( ! file_exists($ddir) ) {
      echo "Briefcase dir not created, should not happen, aborting...\n";
      exit(1);
    }

    // rewrite basic infos (in case of update)

    $fp = fopen($ddir."/.struct/title", 'w');
    fwrite($fp, $vars['DOSNAM']."\n");
    fclose($fp);
    $fp = fopen($ddir."/.struct/comment", 'w');
    fwrite($fp, $vars['DOSCOM']);
    fclose($fp);

    if ( isset($vars['DOSPSW']) && strlen($vars['DOSPSW']) > 0 ) {
      $fp = fopen($ddir."/.struct/passwd", 'w');
      fwrite($fp, $vars['DOSPSW']."\n");
      fclose($fp);
      // reset auth 
      $this->UpdateAuth($did, $vars['DOSPSW']);
      $tmppass = $vars['DOSPSW'];
    } else {
      $fp = fopen($ddir."/.struct/passwd", 'w');
      fclose($fp);
      $tmppass = "";
    }
    $fp = fopen($ddir."/.struct/endoflife", 'w');
    fwrite($fp, $vars['DOSLIM']."\n");
    fclose($fp);
 
    // adjust htaccess
    $this->SetupHtaccessFile($ddir);

    // finish extended infos

    if ( isset($vars['DOSBLG']) && $vars['DOSBLG'] == "on" ) {
      $this->ActivateBlog($did);
    } else {
      $this->DesactivateBlog($did);
    }

    if ( isset($vars['DOSDAT']) && $vars['DOSDAT'] == "on" ) {
      $cmd = $this->gconf->TopAppDir."/script/manage_framadate -c -t \"".$vars['DOSNAM']."\" -j ".$did;
      system($cmd);
    }

    // create a pseudo (incomplete) dosinfo for return
    $dosret = Array();
    $dosret['did'] = $did;
    $dosret['passwd'] = $tmppass;

    return($dosret); 
  }

  // called by the standalone empty dos create
  // will no longer be used 
  function CreDosStruct($vars) {

    $psinf1 = $this->DosStructCreateBase($vars);
    return($this->DosStructCreateExtended($psinf1['did'],$vars));

  }

  // called by new step 1 dos create
  function PreCreateDosStruct() {
    // create pseudo vars for create
    $pseudo = Array();
    $pseudo["DOSNAM"] = date("_Ymd_His");
    $pseudo["DOSPSW"] = "";
    $pseudo["DOSCOM"] = "";

    $utyp = $this->CurrentUserStatus();
    $datinf = $this->GetDateInfo($utyp);

    $pseudo["DOSLIM"] =  date("Y-m-d", $datinf['datelim']);

    return $this->DosStructCreateBase($pseudo);
  }

  // called by new step 2 dos create
  function PostCreateDosStruct($did,$vars) {
    
    $partial = $this->DosStructCreateExtended($did,$vars);
    return($partial);

  }
  
  // setup htaccess file for the briefcase datadir
  // for security and DAV access
  // according to passwd or status 
  function SetupHtaccessFile($ddir) {

    if ( file_exists($ddir."/.struct/passwd") ) {
      $passwd = chop(file_get_contents($ddir."/.struct/passwd"));
    } else {
      $passwd = "";
    }
    if ( file_exists($ddir."/.struct/passadm") ) {
      $passadm = chop(file_get_contents($ddir."/.struct/passadm"));
    } else {
      $passadm = "";
    }

    //$tmp1 = "passwd=".$passwd." passadm=".$passadm."--\n";
    //$this->debug->DebugToFile("/tmp/sethtfil.log", $tmp1);

    if ( $passwd != "" ) {
      // generate htpass for dav access
      $cmd = "/usr/bin/htpasswd -cbm ".$ddir."/.struct/htpass '*' \"".$passwd."\"";
      //$this->debug->DebugToFile("/tmp/sethtfil.log", $cmd);
      system($cmd);
    }

    $fp = fopen($ddir."/.htaccess", 'w');

    // in any case de-activate php+pl 
    fwrite($fp, "RemoveHandler .php .phtml .php3 .php4\n");
    fwrite($fp, "RemoveType .php .phtml .php3 .php4\n");
    fwrite($fp, "php_flag engine off\n");
    fwrite($fp, "\n");
    fwrite($fp, "<FilesMatch \.(pl|php|php3)$>\n");
    fwrite($fp, " Require all denied\n");
    fwrite($fp, "</FilesMatch>\n");
    fwrite($fp, "\n");

    if ( $passadm != "" ) {
      // locked briefcase, de-activate any DAV modification
      fwrite($fp, "<Limit PUT DELETE MKCOL PROPPATCH COPY MOVE LOCK UNLOCK>\n");
      fwrite($fp, " Require all denied\n");
      fwrite($fp, "</Limit>\n");
      if ( $passwd != "" ) {
	// allow dav read for with passwd
	fwrite($fp, "<Limit PROPFIND OPTIONS>\n");
	fwrite($fp, " AuthType Basic\n");
	fwrite($fp, " AuthName DAV\n");
	fwrite($fp, " AuthBasicProvider file\n");
	fwrite($fp, " AuthUserFile ".$ddir."/.struct/htpass\n");
	fwrite($fp, " Require valid-user\n");
	fwrite($fp, "</Limit>\n");
      } else {
	// allow dav read for all
	fwrite($fp, "<Limit PROPFIND OPTIONS>\n");
	fwrite($fp, " Require all granted\n");
	fwrite($fp, "</Limit>\n");
      }
    } else {
      // open briefcase, de-activate only mkcol
      fwrite($fp, "<Limit MKCOL>\n");
      fwrite($fp, " Require all denied\n");
      fwrite($fp, "</Limit>\n");
      if ( $passwd != "" ) {
	// allow dav read/write for with passwd
	fwrite($fp, "<Limit PROPFIND OPTIONS PUT DELETE PROPPATCH COPY MOVE LOCK UNLOCK>\n");
	fwrite($fp, " AuthType Basic\n");
	fwrite($fp, " AuthName DAV\n");
	fwrite($fp, " AuthBasicProvider file\n");
	fwrite($fp, " AuthUserFile ".$ddir."/.struct/htpass\n");
	fwrite($fp, " Require valid-user\n");
	fwrite($fp, "</Limit>\n");
      } else {
	// allow dav read/write for all
	fwrite($fp, "<Limit PROPFIND OPTIONS PUT DELETE PROPPATCH COPY MOVE LOCK UNLOCK>\n");
	fwrite($fp, " Require all granted\n");
	fwrite($fp, "</Limit>\n");
      }
    }
    fclose($fp);
  }

  //===============================================
  // dos struct mgmt
  //===============================================

  function UpdateDosStruct($did, $vars) {
    
    $ddir = $this->GetAbsDirFromDID($did);
    if ( ! file_exists($ddir) ) {
      echo "Ya comme un soucis\n";
      exit(1);
    }

    $fp = fopen($ddir."/.struct/title", 'w');
    fwrite($fp, $vars['DOSNAM']."\n");
    fclose($fp);
    $fp = fopen($ddir."/.struct/comment", 'w');
    fwrite($fp, $vars['DOSCOM']);
    fclose($fp);
    //$fp = fopen($ddir."/.struct/addr", 'w');
    //fwrite($fp, $vars['DOSADR']);
    //fclose($fp);

    if ( isset($vars['DOSPSW']) && strlen($vars['DOSPSW']) > 0 ) {
      $fp = fopen($ddir."/.struct/passwd", 'w');
      fwrite($fp, $vars['DOSPSW']."\n");
      fclose($fp);
      // reset auth 
      $this->UpdateAuth($did, $vars['DOSPSW']);
    } else {
      $fp = fopen($ddir."/.struct/passwd", 'w');
      fclose($fp);
    }
    $fp = fopen($ddir."/.struct/endoflife", 'w');
    fwrite($fp, $vars['DOSLIM']."\n");
    fclose($fp);

    // adjust htaccess
    $this->SetupHtaccessFile($ddir);

    if ( isset($vars['DOSBLG']) && $vars['DOSBLG'] == "on" ) {
      $this->ActivateBlog($did);
    } else {
      $this->DesactivateBlog($did);
    }

    if ( isset($vars['DOSORG']) && $vars['DOSORG'] != "----" ) {
      $fp = fopen($ddir."/.struct/orga", 'w');
      fwrite($fp, $vars['DOSORG']."\n");
      fclose($fp);
    }


  }
  
  function DeleteDosStruct($did) {

    $ddir = $this->GetAbsDirFromDID($did);

    if ( ! file_exists($ddir) ) {
      return null;
    }
    if ( file_exists($ddir."/.struct/framadate") ) {
      $framadate = chop(file_get_contents($ddir."/.struct/framadate"));
      $cmd = $this->gconf->TopAppDir."/script/manage_framadate -d -i ".$framadate;
      system($cmd);
    }
    system("rm -rf ".$ddir);

    $this->DosDestroy($did);
   }

  //===============================================
  // 
  //===============================================

  function FetchDosInfo($did, $mod=0) {
    
    $dadir = $this->GetAbsDirFromDID($did);
    
    if ( ! file_exists($dadir) ) {
      return null;
    }

    $info = array('did' => $did);

    $info['rdir'] = $this->GetDirFromDID($did);

    if ( file_exists($dadir."/.struct/title") ) {
      $info['title'] = chop(file_get_contents($dadir."/.struct/title"));
    }
    if ( file_exists($dadir."/.struct/passwd") ) {
      $info['passwd'] = chop(file_get_contents($dadir."/.struct/passwd"));
    }
    if ( file_exists($dadir."/.struct/comment") ) {
      $info['comment'] = file_get_contents($dadir."/.struct/comment");
    }
    if ( file_exists($dadir."/.struct/passadm") ) {
      $info['passadm'] = chop(file_get_contents($dadir."/.struct/passadm"));
    }
    if ( file_exists($dadir."/.struct/endoflife") ) {
      $info['endoflife'] = chop(file_get_contents($dadir."/.struct/endoflife"));
    } else {
      $info['endoflife'] = "2040-12-31";
    }
    if ( file_exists($dadir."/.struct/blog") ) {
      $info['hasblog'] = 1;
      $info['blogcnt'] = $this->FetchBLogCount($dadir);
    } else {
      $info['hasblog'] = 0;
      $info['blogcnt'] = 0;
    }
    if ( file_exists($dadir."/.struct/orga") ) {
      $info['orga'] = chop(file_get_contents($dadir."/.struct/orga"));
    }

    if ( file_exists($dadir."/.struct/framadate") ) {
      $info['framadate'] = chop(file_get_contents($dadir."/.struct/framadate"));
    }

    if ( file_exists($dadir."/.logo") ) {
      $info['ownlogo'] = true;
    }

    // last modif time = max struct, datapath, blog
    if ( file_exists($dadir."/.struct/blog") ) {
      $r1 = stat($dadir."/.struct/blog");
      $t1 = $r1['mtime'];
    } else {
      $t1 = 0;
    }
    if ( file_exists($dadir."/.struct") ) {
      $r2 = stat($dadir."/.struct");
      $t2 = $r2['mtime'];
    } else {
      $t2 = 0;
    }
    $r3 = stat($dadir);
    $t3 = $r3['mtime'];
    $info['modified'] = date("Y-m-d", max($t1,$t2,$t3));

    // file list if needed
    if ( $mod > 0 ) {
      // fetch file list also

      $flist = array();

      if ($dh = opendir($dadir)) {
	while (false !== ($entry = readdir($dh))) {
	  if (substr($entry,0,1) !="." and $entry != "index.html" ) {
	    $flist[] = $entry;
	  }	
	}
	closedir($dh);
	sort($flist);
      }
      $info['filelist'] = $flist;
    }

    return ($info);
  }

  function FetchDosInfoFromUrl($ustr) {

    $pattern = "|.*/([0-9a-f]{32})$|";
    $ret = preg_match($pattern, $ustr, $ra);

    if ( $ret != 1 ) {
      // not a valid url
      return null;
    }

    return $this->FetchDosInfo($ra[1]);
  }

  function DelDosFiles($did, $dlist) {

    $ddir = $this->GetAbsDirFromDID($did);
    while (list($ind, $value) = each($dlist) ) {
      $dfil = $ddir."/".$value;
      unlink($dfil);
    }
    
  }

  function RenDosFiles($did, $olist, $nlist) {

    $cnt = 0;
    $ddir = $this->GetAbsDirFromDID($did);
    while (list($ind, $value) = each($nlist) ) {
      if ( $value != "" ) {
	$ofil = $ddir."/".$olist[$ind];
	$nfil = $ddir."/".$value;
	rename($ofil,$nfil);
	$cnt += 1;
      }
    }
    if ( $cnt > 0 ) {
      $this->SpoolUploaded($did);
    }
    
  }

  function AddClassicUploadFiles($did, $vars) {

    $nslot=8;
    $totalret = 0;

    for ($i = 1; $i <= $nslot; $i++) {
      $nfi = "nf".$i;

      if ( ! array_key_exists($nfi, $vars) ) {
	continue;
      }
      $ret = $this->ProcessUploadedFile($did, $vars[$nfi]);
      $totalret += $ret;
    }
    $this->SpoolUploaded($did);
    return($totalret);
  }

  function AddJQFUFiles($did, $vars) {

    // Jquery File Upload process file 1 by 1 even if input multiple !

    // re-arrange input array for one file
    $finfo = Array();
    
    if ( array_key_exists('files', $vars) ) {
      $tmp = $vars['files'];
      $finfo['name'] = $tmp['name'][0];
      $finfo['type'] = $tmp['type'][0];
      $finfo['tmp_name'] = $tmp['tmp_name'][0];
      $finfo['error'] = $tmp['error'][0];
      $finfo['size'] = $tmp['size'][0];
    }

    //$this->debug->DebugToFile("/tmp/jqfu_deb.log", $finfo);

    $ret = $this->ProcessUploadedFile($did, $finfo);
    if ( $ret == 1 ) {
      // return the file info array
      // for XHR respons
      $this->SpoolUploaded($did);
      return($finfo);
    } else {
      return(null);
    }
    
  }


  // common file processing ( called by standard form :AddDosFile2
  // or by jquery file upload : AddDosFileJQFU
  // INPUT: did, filinfo = array with (name,type,tmp_name,error,size)
  // OUTPUT: 1 if file processed, 0 if not
  function ProcessUploadedFile($did, $filinfo) {

    $ddir = $this->GetAbsDirFromDID($did);

    if ( $filinfo['size'] > 0 && $filinfo['error'] == 0 ) {
      $dfil = $ddir."/".$filinfo['name'];

      // UNZIP if name = ___.xxx
	
      if ( strncmp($filinfo['name'],"___.",4) == 0 ) {
	$extn = substr($filinfo['name'], 4);
	switch($extn) {
	case "zip":
	case "ZIP":
	  $cmd = "cd ".$ddir."; unzip ".$filinfo['tmp_name'];
	echo $cmd;
	system($cmd);
	break;
	case "tgz":
	case "TGZ":
	  $cmd = "cd ".$ddir."; tar xzf ".$filinfo['tmp_name'];
	echo $cmd;
	system($cmd);
	break;
	case "tbz2":
	case "TBZ2":
	  $cmd = "cd ".$ddir."; tar xjf ".$filinfo['tmp_name'];
	echo $cmd;
	system($cmd);
	break;
	default:
	  rename($filinfo['tmp_name'], $dfil);
	}
	return(1);
      } else {
	rename($filinfo['tmp_name'], $dfil); 
      }
      // file processed
      return(1);

    } else {
      // empty file or error 
      return(0);
    }
     
  }

  // register uploaded dir in the spool
  // for later (incrontab) thumbnail processing 
  function SpoolUploaded($did) {
    
    $ddir = $this->GetAbsDirFromDID($did);
    $spoolf = $this->gconf->upspool."/".$did;

    $fp = fopen($spoolf, 'a');
    fwrite($fp, $ddir."\n");
    fclose($fp);

  }

  function CheckAuth($dosinfo) {
    
    $did = $dosinfo['did'];
    $passwd = @$dosinfo['passwd'];

    $appnam = $this->gconf->name;

    //$this->debug->Debug2("CheckAuth",  $this->gconf);
    //exit(0);

    @session_start();

    if ( $passwd == "" || ( isset($_SESSION[$appnam.'_'.$did]) && $_SESSION[$appnam.'_'.$did] == $passwd ) ) {

      if ( isset($dosinfo['passadm']) ) {

	$passadm = $dosinfo['passadm']; 
	if (  isset($_SESSION[$appnam.'ADM_'.$did]) && $_SESSION[$appnam.'ADM_'.$did] == $passadm ) {
	  return(ACCES_RW);
	} else {
	  return(ACCES_RO);
	}
      } else {
	return(ACCES_RW);
      }
    }
   
    return(ACCES_NO);

  }  

  function UpdateAuth($did, $passwd) {
    
    @session_start();
    $appnam = $this->gconf->name;
    $_SESSION[$appnam.'_'.$did] = $passwd; 
  }
 
 function LockDos($did, $passadm) {
    
    $ddir = $this->GetAbsDirFromDID($did);

    if ( ! file_exists($ddir) ) {
      echo "Ya comme un soucis\n";
      exit(1);
    }

    $fp = fopen($ddir."/.struct/passadm", 'w');
    fwrite($fp, $passadm."\n");
    fclose($fp);

    // adjust htaccess
    $this->SetupHtaccessFile($ddir);

  }

 function UnlockDos($did) {
    
   $ddir = $this->GetAbsDirFromDID($did);

   if ( ! file_exists($ddir) ) {
     echo "Ya comme un soucis\n";
     exit(1);
   }

    $pafil = $ddir."/.struct/passadm";
    if ( file_exists($pafil) ) {
      unlink($pafil);
    }

    // adjust htaccess
    $this->SetupHtaccessFile($ddir);

  }

 function GenerateAndSendZip($did, $filelist="*") {
    
    $ddir = $this->GetAbsDirFromDID($did);
    
    if ( ! file_exists($ddir) ) {
      return null;
    }

    $cmd = "cd ".$ddir.";zip -jq - ".$filelist;

    header("Content-type: application/zip");
    header("Content-Disposition: attachment; filename=".$did.".zip");
    header("Content-Description: File");

    $pn=popen($cmd , "r"); 
    return fpassthru($pn); 
  }
  
  function FetchBlog($did) {
 
    $blist = array();

    $ddir = $this->GetAbsDirFromDID($did);
    
    if ( ! file_exists($ddir) ) {
      return $blist;
    }
    
    $bldir = $ddir."/.struct/blog";
    
    if ( ! file_exists($bldir) ) {
      return $blist;
    }
    
    if ($dh = opendir($bldir)) {

      while (false !== ($entry = readdir($dh))) {
        if ($entry != "." && $entry != "..") {
	  $belem = array();
	  $bents = $bldir."/".$entry."/sign";
	  if ( file_exists($bents) ) {
	    $belem['signature'] = file_get_contents($bents);
	  } else {
	    $belem['signature'] = "inconnu";
	  }
	  $bentc = $bldir."/".$entry."/comment";
	  if ( file_exists($bentc) ) {
	    $belem['comment'] = file_get_contents($bentc);
	  } else {
	    $belem['comment'] = "";
	  }	
	  $blist[$entry] = $belem;
	  
	}
      }
      closedir($dh);
      krsort($blist);

    }

    return ($blist);
      
  }

  function FetchBlogCount($ddir) {

    $cnt = 0;
 
    if ( ! file_exists($ddir) ) {
      return($cnt);
    }
    
    $bldir = $ddir."/.struct/blog";
    
    if ( ! file_exists($bldir) ) {
      return($cnt);
    }
    
    if ($dh = opendir($bldir)) {

      while (false !== ($entry = readdir($dh))) {
        if ($entry != "." && $entry != "..") {
	  $cnt++;
	}
      }
      closedir($dh);
    }

    return($cnt);
      
  }

  function ActivateBlog($did) {
    
    $ddir = $this->GetAbsDirFromDID($did);
    if ( ! file_exists($ddir) ) {
      return ;
    }
    $bldir1 = $ddir."/.struct/blog";
    $bldir2 = $ddir."/.struct/blog_no";

    if ( file_exists($bldir1) ) {
      return ;
    }
    if ( file_exists($bldir2) ) {
      rename($bldir2, $bldir1) ;
      return ;
    }
    mkdir($bldir1, 02775); 

  }
  
  function DesactivateBlog($did) {

    $ddir = $this->GetAbsDirFromDID($did);
    if ( ! file_exists($ddir) ) {
      return ;
    }
    $bldir1 = $ddir."/.struct/blog";
    $bldir2 = $ddir."/.struct/blog_no";

    if ( file_exists($bldir2) ) {
      return ;
    }
    if ( file_exists($bldir1) ) {
      rename($bldir1, $bldir2);
      return ;
    }
  }

  function AddBlog($did, $signature, $comment) {

    $ddir = $this->GetAbsDirFromDID($did);
    if ( ! file_exists($ddir) ) {
      return ;
    }
    $bldir = $ddir."/.struct/blog";
    if ( ! file_exists($bldir) ) {
      return ;
    }
    
    $nowu = date("U");
    $entdir = $bldir."/".$nowu;
    $entsign = $entdir."/sign";
    $entcomm = $entdir."/comment";
    
    mkdir($entdir, 02775);
    $fp = fopen($entsign, 'w');
    fwrite($fp, $signature);
    fclose($fp);
    $fp = fopen($entcomm, 'w');
    fwrite($fp, $comment);
    fclose($fp);
    
  }
  
  function DelBlogAll($did)  {
    
    $ddir = $this->GetAbsDirFromDID($did);
    if ( ! file_exists($ddir) ) {
      return ;
    }
    $bldir = $ddir."/.struct/blog";
    if ( ! file_exists($bldir) ) {
      return ;
    }
    
    system("rm -rf ".$bldir."/*");
    
  }

  function DelBlogEntry($did, $entry)  {
    
    $ddir = $this->GetAbsDirFromDID($did);
    if ( ! file_exists($ddir) ) {
      return ;
    }
    $bldir = $ddir."/.struct/blog";
    if ( ! file_exists($bldir) ) {
      return ;
    }
    $bedir = $bldir."/".$entry;
    if ( ! file_exists($bedir) ) {
      return ;
    }
    
    system("rm -rf ".$bedir);
    
  }
   
  //===============================================
  // language stuff
  //===============================================

  function GetMessages($lang) {
    include_once $this->gconf->LngDir."/".$lang.".php";

    return($msgs);
  }


  //===============================================
  // mgmt and BD stuff
  //===============================================

  function GetDosListByUser($mel) {

    try {
      $dbh = new PDO($this->gconf->dbpdo, $this->gconf->dbuser, $this->gconf->dbpass, $this->gconf->dbparams);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }

    try {

      $stmt = $dbh->query("select dos_id,mode from owner,user where owner.user_id = user.id and user.mail = '".$mel."';");
      $res = $stmt->fetchAll();

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }
 
    //echo "<pre>\n";
    //print_r($res);
    //echo "</pre>\n";

    $list = array();

    foreach ($res as $elem){
      $subd = Array();
      $pinf = $this->FetchDosInfo($elem['dos_id']);
      if ( $elem['mode'] == "reader" and @$pinf['passadm'] != "" ) {
	$pinf['passadm'] = "######";
      }
      $subd['mode'] = $elem['mode'];
      $subd['data'] = $pinf;
      $list[$elem['dos_id']] = $subd;
    }
 
    return($list);

  }

  function VerifUser($user, $passwd ) {
    return $this->VerifUserByType($user, $passwd, 'std');
  }

  function VerifAdmUser($user, $passwd ) {
    return $this->VerifUserByType($user, $passwd, 'adm');
  }

  function VerifUserByType($user, $passwd, $type ) {
    // return 0 if not OK
    // return 1 if user valid
    // type = std|adm

    try {
      $dbh = new PDO($this->gconf->dbpdo, $this->gconf->dbuser, $this->gconf->dbpass, $this->gconf->dbparams);

      $stmt0 = $dbh->query("select password from user where mail = '".$user."';");
      $res0 = $stmt0->fetchAll();

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }

    if ( count($res0) == 1 ) {
      $passhash = $res0[0]['password'];
    } else {
      // user not found 
      // will call UserAutoCreate eventually later
      return(0);
    }

    $uzv = $this->VerifUserPassword($user,$passwd,$passhash);
    if ( $uzv == 0 ) {
      // passwd not good 
      return(0);
    }
    
    // password verified, now check user type
    try {

      if ( $type == "std" ) {
	$sttest = "status != 'request'";
      }
      if ( $type == "adm" ) {
	$sttest = "status = 'admin'";
      }

      $stmt = $dbh->query("select id,status from user where mail = '".$user."' and ".$sttest.";");
      $res = $stmt->fetchAll();

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }
 
    if ( count($res) == 1 ) {
      // if user verified , put status in session for use with cloudsx display
      @session_start();
      $_SESSION['USER_STATUS'] = $res[0]['status']; 
      return(1);
    } else {
      return(0);
    }
  }

  function VerifUserPassword($user,$passwd,$passhash) {
    // return 1 if passwd match, 0 otherwise

    if ( strncmp($passhash,"LDAP:",5) == 0 ) {
      // ===============================
      // authenticate user with LDAP
      // ===============================
      if ( preg_match('/(.*)@(.*)\.(.*)/', $user, $rm) != 1 ) {
	// user no match mail
	return(0);
      }
      
      $login = $rm[1];
      $domain = $rm[2];
      $tld = $rm[3];

      if ( preg_match('/LDAP:(.*):(.*):(.*)/', $passhash, $rm) != 1 ) {
	// no valid LDAP info
	return(0);
      }

      $ldpserv = $rm[1];
      if ( $rm[2] != "" ) {
	$ldpport = $rm[2];
      } else {
	$ldpport = 389;
      }
      $ldpdn = sprintf($rm[3],$login,$domain,$tld);

      $ldpconn = @ldap_connect($ldpserv, $ldpport);
      if ( @ldap_bind ($ldpconn, $ldpdn, $passwd) ) {
	// connected
	@ldap_unbind($ldpconn);
	return(1);
      } else {
	// connect failed
	return(0);
      }

    } else {
      // ===============================
      // local SHA1 user authentication
      // ===============================
      $h = sha1($passwd);
      if ( $h == $passhash ) {
	return(1);
      } else {
	return(0);
      }
    }
  }

  function UserInfo($user ) {

    try {
      $dbh = new PDO($this->gconf->dbpdo, $this->gconf->dbuser, $this->gconf->dbpass, $this->gconf->dbparams);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }

    try {

      $stmt = $dbh->query("select id,mail,name,gvname,status,IF(password like 'LDAP%', 1, 0) as pswldap,length(logo) as llogo from user where mail = '".$user."';");
      $res = $stmt->fetchAll();

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }
 
    if ( count($res) == 1 ) {
      return($res[0]);
    } else {
      return(null);
    }
  }

  function UserInfoByUid($uid ) {

    try {
      $dbh = new PDO($this->gconf->dbpdo, $this->gconf->dbuser, $this->gconf->dbpass, $this->gconf->dbparams);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }

    try {

      $stmt = $dbh->query("select id,mail,name,gvname,status,length(logo) as llogo from user where id = '".$uid."';");
      $res = $stmt->fetchAll();

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }
 
    if ( count($res) == 1 ) {
      return($res[0]);
    } else {
      return(null);
    }
  }

  function CurrentUserStatus() {

    if ( isset($_SERVER['PHP_AUTH_USER']) ) {
      $user = $_SERVER['PHP_AUTH_USER'];
    } else {
      // if no authenticated user , look user_status in session
      @session_start();
      if ( isset($_SESSION['USER_STATUS']) ) {
	return($_SESSION['USER_STATUS']);
      } else {
	return('none');
      }
    }
    

    try {
      $dbh = new PDO($this->gconf->dbpdo, $this->gconf->dbuser, $this->gconf->dbpass, $this->gconf->dbparams);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }

    try {

      $stmt = $dbh->query("select status from user where mail = '".$user."';");
      $res = $stmt->fetchAll();

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }
 
    if ( count($res) == 1 ) {
      return($res[0]['status']);
    } else {
      return('none');
    }

  }

  function UpdateUserInfo($uid,$udata) {

    try {
      $dbh = new PDO($this->gconf->dbpdo, $this->gconf->dbuser, $this->gconf->dbpass, $this->gconf->dbparams);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }

    try {

      $qry = "update user set mail='".$udata['mail']."',name='".$udata['name']."',gvname='".$udata['gvname']."' where id='".$uid."'";

      $cnt = $dbh->exec($qry);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }
 
    return($cnt);
  }

  function UpdateUserPassword($uid, $newpasswd ) {

    try {
      $dbh = new PDO($this->gconf->dbpdo, $this->gconf->dbuser, $this->gconf->dbpass, $this->gconf->dbparams);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }
    
    if ( empty($newpasswd) ) {
      return(0);
    }

    if ( strncmp("LDAP:", $newpasswd, 5) == 0 ) {
      $h = chop($newpasswd);
    } else {
      $h = sha1(chop($newpasswd));
    }

    try {

      $qry = "update user set password='".$h."' where id='".$uid."'";

      $cnt = $dbh->exec($qry);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }
 
    return($cnt);
  }

  function UpdateUserLogo($uid, $logouploaded ) {

    try {
      $dbh = new PDO($this->gconf->dbpdo, $this->gconf->dbuser, $this->gconf->dbpass, $this->gconf->dbparams);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }
    
    if ( empty($logouploaded) || $logouploaded['size'] == 0 ) {
      // null or empty file -> empty the blob !
      try {

	$qry = "update user set logo = NULL where id='".$uid."'";
	$cnt = $dbh->exec($qry);  

      } catch(PDOException $e)  {
	echo $e->getMessage();
	exit();
      }
     return(0);
    }

    // normal file -> update the blob
    try {

      $blob = fopen($logouploaded['tmp_name'],'rb');
 
      $qry = "update user set logo = :logo where id = :id";

      $stmt = $dbh->prepare($qry);
 
      $stmt->bindParam(':logo',$blob,PDO::PARAM_LOB);
      $stmt->bindParam(':id',$uid);
 
      $cnt = $stmt->execute();  

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }
 
    return($cnt);
  }

  function GetUserLogo($uid, $ofile="") {

    try {
      $dbh = new PDO($this->gconf->dbpdo, $this->gconf->dbuser, $this->gconf->dbpass, $this->gconf->dbparams);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }
    
    try {

     $qry = "select logo from user where id = :id";

     $stmt = $dbh->prepare($qry);
     $stmt->execute(array(":id" => $uid));
     $stmt->bindColumn(1, $ldata, PDO::PARAM_LOB);
 
     $stmt->fetch(PDO::FETCH_BOUND);
 
    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }
 
    if ( $ofile != "" && strlen($ldata) > 0 ) {
      $fp = fopen($ofile, 'w');
      fwrite($fp, $ldata);
      fclose($fp);
      return;
    } else {
      return($ldata);
    }
  }

  function DosAttach($did,$uid,$mode ) {

    try {
      $dbh = new PDO($this->gconf->dbpdo, $this->gconf->dbuser, $this->gconf->dbpass, $this->gconf->dbparams);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }

    try {

      $qry = "replace into owner (user_id, dos_id, mode) values(".$uid.",'".$did."', '".$mode."')";
      $cnt = $dbh->exec($qry);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }
    
    return($cnt);
  }

  function DosDestroy($did ) {

    try {
      $dbh = new PDO($this->gconf->dbpdo, $this->gconf->dbuser, $this->gconf->dbpass, $this->gconf->dbparams);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }

    try {

      $qry = "delete from owner where dos_id = '".$did."'";
      $cnt = $dbh->exec($qry);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }
    
  }

  function DosDetach($did, $uid) {

    try {
      $dbh = new PDO($this->gconf->dbpdo, $this->gconf->dbuser, $this->gconf->dbpass, $this->gconf->dbparams);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }

    try {

      $qry = "delete from owner where dos_id = '".$did."' and user_id = '".$uid."'";
      $cnt = $dbh->exec($qry);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }
    
  }

  function VerifyDosAttach($did,$mel ) {

    try {
      $dbh = new PDO($this->gconf->dbpdo, $this->gconf->dbuser, $this->gconf->dbpass, $this->gconf->dbparams);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }

    try {

      $stmt = $dbh->query("select mode from owner,user where owner.user_id = user.id and user.mail = '".$mel."' and dos_id = '".$did."';");
      $res = $stmt->fetchAll();

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }
    
    if ( count($res) == 1 ) {
      return(1);
    } else {
      return(0);
    }
  }

  function GetDateInfo($utyp ) {
    switch($utyp) {
    case 'none':
      $dateahead = time() + 1296000; // 15 days ahead
      $datesel = "no";
      break;
    case 'std':
      $dateahead = time() + 1814400; // 21 days ahead
      $datesel = "no";
      break;
    case 'premium':
    case 'admin':
      $dateahead = time() + 16070400;  // 6 months ahead
      $datesel = "yes";
      break;
    }
    $ret = array('datelim' => $dateahead, 'datesel' => $datesel);
    return($ret);
  }
 

  //===============================================
  // mgmt user stuff
  //===============================================

  function CreateUserRequest($udata) {
    // return 0 if not OK
    // return ID if user created

    try {
      $dbh = new PDO($this->gconf->dbpdo, $this->gconf->dbuser, $this->gconf->dbpass, $this->gconf->dbparams);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }

    $h = sha1(chop($udata['password']));

    try {

      $qry = "insert into user (mail, name, gvname, password, status) values ('".$udata['mail']."', '".$udata['name']."', '".$udata['gvname']."', '".$h."', 'request');";
      $cnt = $dbh->exec($qry);

      if ( $cnt == 1 ) {
	$stmt = $dbh->query("select last_insert_id() as LID;");
	$res = $stmt->fetch();
      }

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }
 
    if ( $cnt == 1 ) {
      return($res['LID']);
    } else {
      return(0);
    }
  }

  function CreateUserValid($udata) {
    // return 0 if not OK
    // return ID if user created

    try {
      $dbh = new PDO($this->gconf->dbpdo, $this->gconf->dbuser, $this->gconf->dbpass, $this->gconf->dbparams);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }

    if ( strncmp("LDAP:", $udata['password'], 5) == 0 ) {
      $h = chop($udata['password']);
    } else {
      $h = sha1(chop($udata['password']));
    }

    try {

      $qry = "insert into user (mail, name, gvname, password, status, credate) values ('".$udata['mail']."', '".$udata['name']."', '".$udata['gvname']."', '".$h."', '".$udata['status']."', NOW() );";
      $cnt = $dbh->exec($qry);

      if ( $cnt == 1 ) {
	$stmt = $dbh->query("select last_insert_id() as LID;");
	$res = $stmt->fetch();
      }

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }
 
    if ( $cnt == 1 ) {
      return($res['LID']);
    } else {
      return(0);
    }
  }

  function ValidateUser($uid) {
    // return 0 if user validated
    // return 1 if user already validated
    // return 2 if non existing user
    // return 3 other error ( should not happen )


    try {
      $dbh = new PDO($this->gconf->dbpdo, $this->gconf->dbuser, $this->gconf->dbpass, $this->gconf->dbparams);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }

    try {
      
      $stmt = $dbh->query("select id,mail,name,gvname,status from user where id = '".$uid."';");
      $resu = $stmt->fetchAll();
      
    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }

    if ( count($resu) == 0 ) {
      return(2);
    }

    $udata = $resu[0];

    if ( $udata['status'] != 'request' ) {
      return(1);
    }
      
    try {

      $qry = "update user set status='std', credate=now() where id='".$udata['id']."';";
      $cnt = $dbh->exec($qry);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }
 
    if ( $cnt == 1 ) {
      return(0);
    } else {
      return(3);
    }
  }

  function ResetPassword($umail) {
    // return udata struct with new passwd

    try {
      $dbh = new PDO($this->gconf->dbpdo, $this->gconf->dbuser, $this->gconf->dbpass, $this->gconf->dbparams);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }
    
    try {

      $stmt = $dbh->query("select id,mail,name,gvname,password from user where mail = '".$umail."' and status != 'request';");
      $res = $stmt->fetchAll();

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }
 
    if ( count($res) == 0 ) {
      // user not found , do nothing
      return(null);
    }

    $udata=Array();
    $udata['uid'] = $res[0]['id'];
    $udata['mail'] =  $res[0]['mail'];
    $udata['gvname'] =  $res[0]['gvname'];
    $udata['name'] =  $res[0]['name'];
    $udata['password'] = $res[0]['password'];

    if ( strncmp("LDAP:", $udata['password'], 5) == 0 ) {
      // DO NOT RESET ldap password
      return(NULL);
    }
 
    $udata['newpasswd'] = $this->GetRandomString(6);
    $h = sha1($udata['newpasswd']);

    try {

      $qry = "update user set password='".$h."' where id='".$udata['uid']."'";

      $cnt = $dbh->exec($qry);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }

    return($udata);
  }

  //===============================================
  // admin stuff
  //===============================================

  function GetDosListFull() {
    
    $top = $this->gconf->AbsDataDir;
    
    //$this->debug->TimeDebug("DosListFull-start");
    $lst0 = glob($top."/?/?/?/?/*/.struct");
    $lst1 = array();
    
    //$this->debug->TimeDebug("DosListFull-loop");
    foreach ( $lst0 as $dir) {
      $did = $this->GetDIDFromDir($dir);
 
      $dosinf = $this->FetchDosInfo($did);
      $ucnt = $this->GetUserCntByDID($did);

      $lst1[$did] = array('info' => $dosinf, 'count' => $ucnt);
    }
    //$this->debug->TimeDebug("DosListFull-end");

    return($lst1);
  }

  function GetDosListFullCached() {

    $cfil = $this->gconf->CacheDir."/fulldoslist.data";

    // if cache not here get the real data
    if ( ! file_exists($cfil) ) {
      return($this->GetDosListFull());
    }

    $sr = stat($cfil);
    // if cache too small get the real data
    if ( $sr['size'] < 512 ) { 
      return($this->GetDosListFull());
    }
    // if cache too old get the real data
    $now = date("U");
    $delta = $now - $sr['mtime'];
    if ( $delta > 3600 ) { 
      return($this->GetDosListFull());
    }

    // otherwise get the cache
    $serdata = file_get_contents($cfil);
    $dflist = unserialize($serdata);
    return($dflist);
  }

  function GetUserCntByDID($did) {
    try {
      $dbh = new PDO($this->gconf->dbpdo, $this->gconf->dbuser, $this->gconf->dbpass, $this->gconf->dbparams);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }

    try {

      $stmt = $dbh->query("select count(*) as CNT from owner where dos_id='".$did."';");
      $res = $stmt->fetch();

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }
 
    return($res['CNT']);
  }
 
  function GetUserListByDID($did) {
    try {
      $dbh = new PDO($this->gconf->dbpdo, $this->gconf->dbuser, $this->gconf->dbpass, $this->gconf->dbparams);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }

    try {

      $stmt = $dbh->query("select user.id,mail,name,gvname,status,credate,paydate from owner,user where owner.user_id = user.id and dos_id='".$did."';");
      $res = $stmt->fetchAll();

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }
 
    return($res);
  }
 
  function GetUserListFull() {
    try {
      $dbh = new PDO($this->gconf->dbpdo, $this->gconf->dbuser, $this->gconf->dbpass, $this->gconf->dbparams);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }

    try {

      //$stmt = $dbh->query("select id,mail,name,gvname,status,credate,paydate from user;");
      $stmt = $dbh->query("select user.id,mail,name,gvname,IF(password like 'LDAP%', password, '') as passinf,status,credate,paydate,count(dos_id) as cnt from user left join owner on user.id=owner.user_id group by id;");
      $res = $stmt->fetchAll();

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }
 
    return($res);
  }

  function DeleteUserByUid($uid) {

    try {
      $dbh = new PDO($this->gconf->dbpdo, $this->gconf->dbuser, $this->gconf->dbpass, $this->gconf->dbparams);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }

    try {

      $qry1 = "delete from user where id='".$uid."'";
      $cnt1 = $dbh->exec($qry1);

      $qry2 = "delete from owner where user_id='".$uid."'";
      $cnt2 = $dbh->exec($qry2);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }
 
    return($cnt1);
  }

  function UpdateUserStatus($uid,$newstatus) {

    try {
      $dbh = new PDO($this->gconf->dbpdo, $this->gconf->dbuser, $this->gconf->dbpass, $this->gconf->dbparams);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }

    try {

      $qry = "update user set status='".$newstatus."' where id='".$uid."'";

      $cnt = $dbh->exec($qry);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }
 
    return($cnt);
  }

  function UpdateUserSubscription($uid,$enddate) {

    try {
      $dbh = new PDO($this->gconf->dbpdo, $this->gconf->dbuser, $this->gconf->dbpass, $this->gconf->dbparams);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }

    if ( empty($enddate) ) {
        $ed = "NULL";
    } else {
        $ed = "'".$enddate." 23:59:59'";
    }
    
    try {

      $qry = "update user set paydate=".$ed." where id='".$uid."'";

      $cnt = $dbh->exec($qry);

    } catch(PDOException $e)  {
      echo $e->getMessage();
      exit();
    }
 
    return($cnt);
  }

  //===============================================
  // utilities
  //===============================================

  function GetRandomString($len) {
    $chartab = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    return (substr(str_shuffle($chartab), 0, $len));
  }
 
  function GetUserStatuses($full=0) {
    if ( $full ) {
      return array("request","std","premium","admin");
    } else {
      return array("std","premium","admin");
    }
  }

  //===============================================
  // end
  //===============================================

    
}

