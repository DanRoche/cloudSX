<script language="php">

//===============================================
// plugins to display html document 
//===============================================


include_once "Savant3/Savant3.php";
include_once "app/class.URL.php";
include_once "app/class.PluginLib.php";
include_once "app/class.Debug.php";

class Html {

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
    $tpl->setMessages($this->pluginlib->langmessg);

    $tpl->assign("FILENAM", $this->pluginlib->filename);
    $tpl->assign("HTURL", $urls->GetRawDosData($this->pluginlib->filename));
    $tpl->assign("DLURL", $urls->GetDosDownload($this->pluginlib->filename));
    $tpl->display("plugins/Html/tpl.Html.html");

  }

}
</script>

