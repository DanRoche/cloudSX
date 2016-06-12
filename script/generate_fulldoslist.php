#!/usr/bin/php
<?php
// =============
// some init 
// =============

include "app/Config.php";
include_once "app/class.Data.php";

// =================
// do it 
// =================

$gconf = new Config;
$dataobj = new Data($gconf);

$dflist = $dataobj->GetDosListFull();

// serialise now
$fd = fopen(".cached_full_doslist", "w");
fwrite($fd, serialize($dflist));
fclose($fd);


