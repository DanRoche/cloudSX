#!/usr/bin/php
<?php
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

// =================
// do it 
// =================

$gconf = new Config;
$cfil = $gconf->CacheDir."/fulldoslist.data";
$serdata = file_get_contents($cfil);

// unserialise now
$dflist = unserialize($serdata);

print_r($dflist);
