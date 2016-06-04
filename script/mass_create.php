#!/usr/bin/php
<?php
// =============
// some init 
// =============

include "app/Config.php";
include "app/class.Data.php";

// how many to create ?
$max = 200;

// =============
// main
// =============

$conf = new Config;
$dataobj = new Data($conf);

for($i=1; $i <=$max; $i++) {

  echo "creating ".$i."\n";

  $rand = $dataobj->GetRandomString(6);
  $dnam = "mass_created_".$rand;

  $info = Array("DOSNAM"=>$dnam,
		"DOSCOM"=> date("_Ymd_His"),
		"DOSPSW"=> "",
		"DOSLIM"=> date("Y-m-d", time() + 1296000)
		);

  $dataobj->CreDosStruct($info);
}

