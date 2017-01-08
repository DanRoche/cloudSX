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
		"firefox|odt" => "OfficeVisu",
		"firefox|ods" => "OfficeNovisu",
		"firefox|odg" => "OfficeVisu",
		"firefox|odp" => "OfficeVisu",
		"firefox|doc" => "OfficeVisu",
		"firefox|ppt" => "OfficeVisu",
		"firefox|xls" => "OfficeNovisu",
		"firefox|docx" => "OfficeVisu",
		"firefox|pptx" => "OfficeVisu",
		"firefox|xlsx" => "OfficeNovisu",
		"chrome|odt" => "OfficeVisu",
		"chrome|ods" => "OfficeNovisu",
		"chrome|odg" => "OfficeVisu",
		"chrome|odp" => "OfficeVisu",
		"chrome|doc" => "OfficeVisu",
		"chrome|ppt" => "OfficeVisu",
		"chrome|xls" => "OfficeNovisu",
		"chrome|docx" => "OfficeVisu",
		"chrome|pptx" => "OfficeVisu",
		"chrome|xlsx" => "OfficeNovisu",
		"edge|odt" => "OfficeVisu",
		"edge|ods" => "OfficeNovisu",
		"edge|odg" => "OfficeVisu",
		"edge|odp" => "OfficeVisu",
		"edge|doc" => "OfficeVisu",
		"edge|ppt" => "OfficeVisu",
		"edge|xls" => "OfficeNovisu",
		"edge|docx" => "OfficeVisu",
		"edge|pptx" => "OfficeVisu",
		"edge|xlsx" => "OfficeNovisu",
		"txt" => "Text",
		"html" => "Html",
		"xapp" => "ExternApp"
		);
   return $cdp;
 }
 

