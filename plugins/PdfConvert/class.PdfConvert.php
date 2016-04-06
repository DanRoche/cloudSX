<script language="php">

//===============================================
// plugins to display office document 
// converted to pdf
//===============================================


include_once "Savant3/Savant3.php";
include_once "app/class.URL.php";
include_once "app/class.PluginLib.php";
include_once "app/class.Debug.php";

class PdfConvert {

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

    $filpath = $this->pluginlib->globalconf->TopAppDir.$this->pluginlib->globalconf->DataDir."/".$this->pluginlib->dosinfo['rdir']."/".$this->pluginlib->filename;

    $urls = URL::GetURLByInfo($this->pluginlib->globalconf, $this->pluginlib->dosinfo);
    $tpl = new Savant3();
    $tpl->setMessages($this->pluginlib->langmessg);

    $tpl->assign("FILENAM", $this->pluginlib->filename);
    $tpl->assign("CONVURL", $urls->GetPluginMethod("PdfConvert", "go.PdfConvert.php", $filpath));
    $tpl->assign("DLURL", $urls->GetDosDownload($this->pluginlib->filename));
    $tpl->display("plugins/PdfConvert/tpl.PdfConvert.html");

  }

}
</script>

