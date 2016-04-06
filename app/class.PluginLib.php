<script language="php">

   // #######################################
   // DATA transport and Utilities functions
   // for cloudSX plugins display system
   // 


class PluginLib {

  //===============================================
  // transported data
  //===============================================

  var $dosinfo;
  var $filename;
  var $globalconf;
  var $langmessg;

  //===============================================
  // init 
  //===============================================

  function __construct($dinfo, $file, $gconf, $msg) { 
    // init all
    $this->dosinfo = $dinfo;
    $this->filename = $file;
    $this->globalconf = $gconf;
    $this->langmessg = $msg;
   }

  //===============================================
  // Utilities functions
  //===============================================

  function GetNearFile($mode) {

    $flist = $this->dosinfo['filelist'];

    $curind = array_search($this->filename, $flist);

    if ( $curind === false ) {
      return("");
    }

    switch($mode) {
    case "next":
      $max = sizeof($flist);
      $newind = $curind + 1;
      if ( $newind >= $max ) {
	return("");
      }
      break;
    case "prev":
      $newind = $curind - 1;
       if ( $newind < 0 ) {
	 return("");
      }
      break;
    }

    return($flist[$newind]);
  }

  //===============================================
  // end
  //===============================================

}

</script>
