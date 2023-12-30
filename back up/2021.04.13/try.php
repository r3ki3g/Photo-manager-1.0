<?php
$timeStart = microtime(true);

//root directiory declaration
$httpRoot = 'D:/HTTP_WEB/realfeedserver/';
define('httpRoot',$httpRoot);

// STRUCTURED FUNCTION SET 
require $httpRoot . "/sfs.php";


$table['create'] = 'D:/HTTP_WEB/realfeedServer/demo/1.sfs';
$table['fields'] = ['name','age','sex'];
runsfs($table);

$table =[];
$table['table'] = 'D:/HTTP_WEB/realfeedServer/demo/1.sfs';
for ($i=0;$i<10000;$i++)
{

$table['add']['name'] = 'person_' . strval($i+10000*1);
$table['add']['age'] = rand(12,60);
$table['add']['sex'] =rand(0,1)==1?'m':'f';
//runsfs($table);

}

$GLOBALS['current'] =0;
$table['if'] =  function($record)
{
    $GLOBALS['current'] = $record['age'];
    return true;
};
$table['change']['age'] = $GLOBALS['current'];
runsfs($table);







$timeEnd = microtime(true);

echo 'time taken was: ' . strval(($timeEnd-$timeStart)) . " seconds";
?>