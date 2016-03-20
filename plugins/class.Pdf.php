<script language="php">

//===============================================
// plugins to display pdf document 
//===============================================


include_once "Savant3/Savant3.php";
include_once "app/class.URL.php";
include_once "app/class.Debug.php";

class Pdf {

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

    $urls = URL::GetURLByInfo($this->gconf, $this->pdinfo);
    $tpl = new Savant3();

    $tpl->assign("PDFURL", $urls->GetRawDosData($this->file));
    $tpl->display("plugins/tpl.Pdf.html");

  }

}
</script>

