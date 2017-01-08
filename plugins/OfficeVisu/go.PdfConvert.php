<?php

//===============================================
// convert office file to PDF
// and send it with appropriate mime type
//===============================================

$filpath = $_REQUEST['FILEPATH'];

// on centos server, unoconv does not work without wrapper ( env issue probably ) 
$command = "/opt/bin/wrappuno \"".$filpath."\" 2>&1";
//$command = "/usr/bin/unoconv --stdout -f pdf \"".$filpath."\" 2>&1";

//echo $command;
//exit(0);

// force download
header("MIME-Version: 1.0");
header("Content-type: application/pdf");   
//header("Content-Disposition: filename=".$this->file);
//header("Content-Description: File"); 

passthru($command); 
