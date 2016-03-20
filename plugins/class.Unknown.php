<script language="php">

//===============================================
// plugins to display unknown content 
// -> display just a message 
//===============================================


include_once "Savant3/Savant3.php";
include_once "app/class.URL.php";
include_once "app/class.Debug.php";

class Unknown {

  //===============================================
  // init
  //===============================================

  var $pdinfo;
  var $file;
  var $gconf;
  var $msgs;
  var $header;

  function __construct($pdinfo, $file, $conf, $msgs, $header=0) { 
    $this->pdinfo = $pdinfo;
    $this->file = $file;
    $this->gconf = $conf;
    $this->messages = $msgs;
    $this->header = $header;

    $this->debug = new Debug();
   }

  //===============================================
  // display
  //===============================================

  function Display() {

    //$this->debug->Debug2("plugin Image : pdinfo", $this->pdinfo);
    //$this->debug->Debug2("plugin Image : file", $this->file);
    //$this->debug->Debug2("plugin Image : conf", $this->gconf);
    //$this->debug->Debug2("plugin Image : lang", $this->lng);
    //exit(0);
 
    $urls = URL::GetURLByInfo($this->gconf, $this->pdinfo);
    $tpl = new Savant3();
 
    $tpl->setMessages($this->messages);

    $tpl->assign("URL", $urls );
    $tpl->assign("URL1", $urls->GetRawDosData($this->file) );
    $tpl->assign("URL2", $urls->GetDosDownload($this->file) );
    $tpl->display("plugins/tpl.Unknown.html");

  }

}
</script>

