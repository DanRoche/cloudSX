<script language="php">

//===============================================
// plugins to display office document 
// converted to pdf
//===============================================


include_once "Savant3/Savant3.php";
include_once "app/class.URL.php";
include_once "app/class.Debug.php";

class PdfConvert {

  //===============================================
  // init
  //===============================================

  var $pdinfo;
  var $file;
  var $gconf;
  var $msgs;

  function __construct($pdinfo, $file, $conf, $msgs, $header=0) { 
    $this->pdinfo = $pdinfo;
    $this->file = $file;
    $this->gconf = $conf;
    $this->msgs = $msgs;
    $this->header = $header;

    $this->debug = new Debug();
   }

  //===============================================
  // display
  //===============================================

  function Display() {

    $filpath = $this->gconf->TopAppDir.$this->gconf->DataDir."/".$this->pdinfo['rdir']."/".$this->file;

    $urls = URL::GetURLByInfo($this->gconf, $this->pdinfo);
    $tpl = new Savant3();
    $tpl->setMessages($this->msgs);

    $tpl->assign("FILENAM", $this->file);
    $tpl->assign("CONVURL", $urls->GetPluginMethod("go.PdfConvert.php", $filpath));
    $tpl->assign("DLURL", $urls->GetDosDownload($this->file));
    $tpl->display("plugins/tpl.PdfConvert.html");

  }

}
</script>

