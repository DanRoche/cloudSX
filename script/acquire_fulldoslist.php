#!/usr/bin/php
<?php
// =============
// some init 
// =============

include "app/Config.php";

// =================
// do it 
// =================

$serdata = file_get_contents(".cached_full_doslist");

// unserialise now
$dflist = unserialize($serdata);

print_r($dflist);
