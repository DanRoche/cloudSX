<?php

   //=======================================================
   // Content Display Parameters Config
   // [browser|]file_extension  => display method 
   //=======================================================

 function Content_Display_Parameters() {

   $cdp = array(
		"default" => "Unknown",
		"chrome|pdf" => "Pdf",
		"firefox|pdf" => "Pdf",
		"edge|pdf" => "Pdf",
		"pdf" => "Unknown",
		"gif" => "Image",
		"jpg" => "Image",
		"jpeg" => "Image",
		"png" => "Image",
		"firefox|odt" => "PdfConvert",
		"firefox|ods" => "PdfConvert",
		"firefox|odg" => "PdfConvert",
		"firefox|odp" => "PdfConvert",
		"firefox|doc" => "PdfConvert",
		"firefox|ppt" => "PdfConvert",
		"firefox|xls" => "PdfConvert",
		"firefox|docx" => "PdfConvert",
		"firefox|pptx" => "PdfConvert",
		"firefox|xlsx" => "PdfConvert",
		"chrome|odt" => "PdfConvert",
		"chrome|ods" => "PdfConvert",
		"chrome|odg" => "PdfConvert",
		"chrome|odp" => "PdfConvert",
		"chrome|doc" => "PdfConvert",
		"chrome|ppt" => "PdfConvert",
		"chrome|xls" => "PdfConvert",
		"chrome|docx" => "PdfConvert",
		"chrome|pptx" => "PdfConvert",
		"chrome|xlsx" => "PdfConvert",
		"edge|odt" => "PdfConvert",
		"edge|ods" => "PdfConvert",
		"edge|odg" => "PdfConvert",
		"edge|odp" => "PdfConvert",
		"edge|doc" => "PdfConvert",
		"edge|ppt" => "PdfConvert",
		"edge|xls" => "PdfConvert",
		"edge|docx" => "PdfConvert",
		"edge|pptx" => "PdfConvert",
		"edge|xlsx" => "PdfConvert",
		"txt" => "Text",
		"html" => "Html"
		);
   return $cdp;
 }
 

