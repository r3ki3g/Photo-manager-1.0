<?php
$analyticalTable["create"] = $httpRoot . "/db/analytics.sfs";
$analyticalTable["fields"] = ["datetime","url","details"];
runsfs($analyticalTable);


// add page data to table
$analyticalTable =[];
$analyticalTable["table"] =$httpRoot . "/db/analytics.sfs";
$analyticalTable["add"]["datetime"]= $_SERVER["REQUEST_TIME"];
$analyticalTable["add"]["url"] =  $_SERVER["REQUEST_URI"];
runsfs($analyticalTable);
