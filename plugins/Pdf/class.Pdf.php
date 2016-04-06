<script language="php">

//===============================================
// plugins to display pdf document 
//===============================================


include_once "Savant3/Savant3.php";
include_once "app/class.URL.php";
include_once "app/class.PluginLib.php";
include_once "app/class.Debug.php";

class Pdf {

  //===============================================
  // init
  //===============================================

  var $pluginlib;

  function __construct($pluglib) { 
    $this->pluginlib = $pluglib;

    $this->debug = new Debug();
   }

  //===============================================
  // display
  //===============================================

  function Display() {

    $urls = URL::GetURLByInfo($this->pluginlib->globalconf, $this->pluginlib->dosinfo);
    $tpl = new Savant3();

    $tpl->assign("PDFURL", $urls->GetRawDosData($this->pluginlib->filename));
    $tpl->display("plugins/Pdf/tpl.Pdf.html");

  }

}
</script>

