#!/usr/bin/php
<?php
// =============
// args processing 
// =============

function usage() {
  echo "usage: generate_fulldoslist [-1|-D] [-h]\n";
  echo "where: -1 , run only once  ( default )\n";
  echo "       -D , run as daemon\n";
  echo "       -h , display this help\n";
  exit(1);
}

// since only one arg, does not worth using getopt !
$daemon=0;
if ( isset($argv[1]) ) {
  if ( $argv[1] == "-D" ) {
    $daemon=1;
  } elseif ( $argv[1] == "-1" ) {
    $daemon=0;
  } else {
    usage();
  }
}

// =============
// some init 
// =============

$itself = __FILE__;
$appdir = dirname(dirname($itself));

if ( ! file_exists($appdir."/app/Config.php") ) {
  echo "Can't find application config file - aborting !\n";
  exit(3);
}

chdir($appdir);

include "app/Config.php";
include_once "app/class.Data.php";

// =================
// processing 
// =================

function GenerateOne() {
  global $dataobj, $gconf;
  $dflist = $dataobj->GetDosListFull();
  $cfil = $gconf->CacheDir."/fulldoslist.data";

  // serialise now
  $fd = fopen($cfil, "w");
  fwrite($fd, serialize($dflist));
  fclose($fd);
}

// =================
// do it 
// =================

$gconf = new Config;
$dataobj = new Data($gconf);

if ( $daemon == 1 ) {
  while ( 1 ) {
    GenerateOne();
    sleep(60);
  }
} else {
  GenerateOne();
}