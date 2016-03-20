<script language="php">

   // need to be seriously improved/rewriten !!
   // kept as reminder


include_once "class.Data.php";

class API {

  //===============================================
  // framework
  //===============================================

  var $public_functions;
  var $gconf;

  function __construct($conf) { 
    // define webbox callable function
    $this->public_functions = array(
				    "Ping" => TRUE,
				    "Create" => TRUE,
				    "AddFile" => TRUE
				    );
    $this->gconf = $conf;
    $this->data = new Data($conf);
   }

  function IsCallable($vars, $method) {
    return array_key_exists($method, $this->public_functions);
  }

  //===============================================
  // web app
  //===============================================

  function Ping($vars) {
    echo "pong\n";
  }


  function Create($vars) {

    //$this->Debug2("VARS=", $vars);

    $ok = 1;
    if ( ! array_key_exists("DOSNAM", $vars) or $vars['DOSNAM'] == "" ) {
      $ok = 0;
    }
    if ( ! array_key_exists("DOSLIM", $vars) or $vars['DOSLIM'] == "" ) {
      $twomonthahead = time() + 5270400; 
      $vars['DOSLIM'] = date("d-m-Y", $twomonthahead);
    }

    if ( $ok != 1 ) {
      echo "usage: the following variables must be provided :\n";
      echo "  DOSNAM: briefcase name \n";
      exit();
    }

    $res = $this->data->CreDosStruct($vars);

    echo "CLOUDSX created : PDID=".$res['H1']." PASSWD=".$res['PASSWD']."\n";

    //print_r($res);

  }

  function AddFile($vars) {


    //$this->Debug2("VARS", $vars);

    $pdinf = null;
    
    if ( array_key_exists('DID', $vars) ) {
      $dosinf = $this->data->FetchDosInfo($vars['DID']);
    }

    if ( $dosinf == null ) {
      echo "CLOUDSX error : bad or unknown DID \n";
      exit();
    }
    
    $this->data->AddDosFiles($dosinf, $vars);

    echo "CLOUDSX ".$dosinf['did']." / ".$dosinf['title'].", file added\n";

  }


  //===============================================
  // debug
  //===============================================

  function Debug1( $msg ) {
    
    echo "<pre>\n";
    echo $msg;
    echo "</pre>\n";

    $tpl->display("tpl/debug1.html");
  }
 
  function Debug2( $msg, $var ) {

    echo "<pre>\n";
    echo $msg."\n";
    print_r($var);
    echo "</pre>\n";

  }
 


  //===============================================
  // end
  //===============================================

}

</script>
