<script language="php">

//===============================================
// plugins to display resized image 
// to fit in a page
//===============================================


include_once "Savant3/Savant3.php";
include_once "app/class.URL.php";
include_once "app/class.Debug.php";

class Image {

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
    $this->msgs = $msgs;
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
    $tpl->setMessages($this->msgs);

    $tpl->assign("FILENAM", $this->file);
    $tpl->assign("IMGURL", $urls->GetRawDosData($this->file));
    $tpl->assign("DLURL", $urls->GetDosDownload($this->file));
    $tpl->display("plugins/tpl.Image.html");

  }

}
</script>
