<?php
//sleep(1);
//root directiory declaration
$httpRoot = 'D:/HTTP_WEB/realfeedserver/';

// STRUCTURED FUNCTION SET 
require $httpRoot . 'sfs.php';

$table = []; // clearing previous stored data
$table["table"] = "D:/HTTP_WEB/realfeedServer/photo.manager/db/posts.sfs"; // select the table

//$table['*while' ] //:::::: *while is more recomended...to give the feeling like its a pointer to global var!
//however 'while' also works


$table["while"] = "pro_0"; // optional ...  function repeats while this GLOBAL variable is true
$GLOBALS["pro_0"] = true;  // setting that global variable = true *** compulsory if while used

$table["function"] = function ($record)  // function to do each row
{
    echo  $record["featuring"] . " <br/> "  . $record["imageids"] . "<hr/>";
    //if ($record["number"] == 2) {$GLOBALS["pro_0"] = false;} // after reaching number=2 loop will end

};

//$table["reverse"] = true; // optional ... records will be considered from bottm to top

runsfs($table); // proceed