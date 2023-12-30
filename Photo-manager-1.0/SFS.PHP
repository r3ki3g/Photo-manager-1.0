<?php 
//sleep(1);
/*
R3KI3GPRO : some useful functions

*/
header("X-powered-by:sfs 1.0 -php 7.1 - server IIS 10.0");
header("Security-Level:12/12");

//fundemental settings
$GLOBALS['sfs-file-max-updater-in-one-script-as-multiplier'] = 50;
define('endl',"\n");
define('br','<br/>' . endl);
define('hr','<hr/>' . endl);
    function Query($queryName)
    {
        $value = $_GET[$queryName] ?? 0;
        if ($value ===  0) {
            return "";
        }
        return $value;
    }
    function QueryFound($queryName)
    {
        $value = $_GET[$queryName] ?? 0;
        if ($value === 0) {
            return false;
        }
        return true;
    }

    function Post($name)
    {
        $value = $_POST[$name] ?? 0;
        if ($value ===  0) {
            return "";
        }
        return $value;
    }
    function PostFound($name)
    {
        $value = $_POST[$name] ?? 0;
        if ($value === 0) {
            return false;
        }
        return true;
    }

    function HTML($str)
    {/*R3KI3GPRO*/
        return htmlspecialchars(strval($str));
    }
    
    function toHTML($str)
    {/*R3KI3GPRO*/
        
        $str =  preg_replace('/\n/i',br,$str);
        $str = preg_replace('/\s/m','<div style="display:inline-block;width:12px;"></div>',$str);
        

        return  $str;
    }

    function print_rh($obj)
    {/*R3KI3GPRO*/
        echo toHTML(print_r($obj,1));
    }



    function encode($str)
    {

        return convert_uuencode($str);
    }
    function decode($str)
    {

        return convert_uudecode($str);
    }
    $errorLog = [];


    function timeLog($taskName,$subtaskName=null) 
    {
        /*
        R3KI3GPRO
        if taskname and subtask name is given : appends subtast and timestamp to task
        if only taskname is given returns all subtask - timestamp collection
        */
        if(isset($taskName) && isset($subtaskName))
        {   $taskName = strval($taskName);
            if(isset($GLOBALS['sfs-timelog-' . $taskName]))
            {
                $GLOBALS['sfs-timelog-' . $taskName][] = [$subtaskName,microtime(true)];
            }
            else
            {
                $GLOBALS['sfs-timelog-' . $taskName] = [[$subtaskName,microtime(true)]];
            }
        }
        else if(isset($taskName))
        {   
            $taskName = strval($taskName);
            
           for($i=0;$i<count($GLOBALS['sfs-timelog-' . $taskName]);$i++)
           {
               if($i)
               {
                $GLOBALS['sfs-timelog-' . $taskName][$i]["time for subtask: "] = round($GLOBALS['sfs-timelog-' . $taskName][$i][1]
                                                                - $GLOBALS['sfs-timelog-' . $taskName][$i-1][1],4);
               }

           }

            return $GLOBALS['sfs-timelog-' . $taskName];
        }


    }

    function uniqueId($len = 25)
    {/*R3KI3GPRO*/
        $id = strval(microtime(true)) . "-";
        while (strlen($id) <= $len) {
            $id .= mt_rand(0, 9);
        }
        return $id;
    }

    /*function addAsArray($array,$item)
    {
        if(isset($array)){array_push($array,$item);}
        else{$array = [$item];}

    }*/

function countsfs($varName,$weight = 1)
{
 $val = $GLOBALS[$varName];
 if($val){$GLOBALS[$varName] -= $weight;}//only if not 0
 if($GLOBALS[$varName] < 0){ $GLOBALS[$varName] = 0;}//never negetive
}




function runsfs($data)
{
    $overwrite = $data["overwrite"] ?? false;
    $create = $data["create"] ?? false;
    //creating a new table
    if ($create && (!file_exists($create) || $overwrite)) {
        $fields = $data["fields"];
        $frontLine = "";
        for ($i = 0; $i < count($fields); $i++) {
            $frontLine .= (($frontLine != "") ? "?" : "") . $fields[$i];
        }
        $fs  = fopen($create, "w");
        fwrite($fs, $frontLine);
        fclose($fs);
    }

    //adding a record to a table
    if (isset($data["add"]) && isset($data["table"])) {
        if (file_exists($data["table"]))//only if the subject table exists
        {
            $fs = fopen($data["table"], "r");
            $fields = explode("?", strval(fgets($fs)));//getting only the first line
            fclose($fs);
            $newData = "";

            foreach ($fields as $f) {
                $f = trim($f);
                $isThisBinary = $data["binary"][$f] ?? false;
                if (!$isThisBinary) {
                    $newData .= ($newData == "" ? "" : "?") . urlencode($data["add"][$f] ?? "");
                } else {
                    //if binary directory not found
                    $folderName = pathinfo($data["table"])["dirname"] . "/binary";
                    if (!file_exists($folderName)) {
                        mkdir($folderName);
                    }

                    $uniqueName = false;
                    while ($uniqueName === false) {
                        $candidateFileName = uniqueId();
                        $candidateFilePath = $folderName . "/" . $candidateFileName;
                        if (!file_exists($candidateFilePath)) {
                            $uniqueName = $candidateFileName;
                        }
                    }

                    $fs = fopen($candidateFilePath, "w");
                    fwrite($fs, $data["add"][$f]);
                    fclose($fs);

                    $newData .= ($newData == "" ? "" : "?") . "@" . urlencode($uniqueName);
                }
            }

            $fs = fopen($data["table"], "a");
            fwrite($fs, "\n" . $newData);
            fclose($fs);
        }
    }
    //adding multiple  records to a table
    if (isset($data["addmultiple"]) && isset($data["table"])) 
    {
        if (file_exists($data["table"]))//only if the subject table exists
        {
            $fs = fopen($data["table"], "r");
            $fields = explode("?", strval(fgets($fs)));//getting only the first line
            fclose($fs);

            $addString ='';
            foreach($data['addmultiple'] as $recordToAdd):
                    $addString .= "\n";
                    $newData = "";
                    foreach ($fields as $f) 
                    {
                        $f = trim($f);
                        $isThisBinary = $data["binary"][$f] ?? false;
                        if (!$isThisBinary) {
                            $newData .= ($newData == "" ? "" : "?") . urlencode($recordToAdd[$f] ?? "");
                        } else {
                            //if binary directory not found
                            $folderName = pathinfo($data["table"])["dirname"] . "/binary";
                            if (!file_exists($folderName)) {
                                mkdir($folderName);
                            }

                            $uniqueName = false;
                            while ($uniqueName === false) {
                                $candidateFileName = uniqueId();
                                $candidateFilePath = $folderName . "/" . $candidateFileName;
                                if (!file_exists($candidateFilePath)) {
                                    $uniqueName = $candidateFileName;
                                }
                            }

                            $fs = fopen($candidateFilePath, "w");
                            fwrite($fs,$recordToAdd[$f]);
                            fclose($fs);

                            $newData .= ($newData == "" ? "" : "?") . "@" . urlencode($uniqueName);
                        }
                    }
                    $addString .= $newData;
            endforeach;

            $fs = fopen($data["table"], "a");
            fwrite($fs, $addString);
            fclose($fs);
        }
    }

    //repeating a function in each record
    if (isset($data["function"]) && isset($data["table"])) {
        if (file_exists($data["table"])) {
            //while atribute: key to follow the each function
            /*$whilePara = isset($data["while"]) ? $data["while"] : "sfs-true-fixed";
            if ($whilePara == "sfs-true-fixed") 
            {echo '__________';
                $whilePara = isset($data["*while"]) ? $data["*while"] : "sfs-true-fixed";
            }*/
            if(isset($data["while"]))//while is set for previous versions
            {
                $whilePara = $data["while"];
            }
            else if(isset($data["*while"]))//new version recomends *while
            {
                $whilePara = $data["while"];
            }
            else//nothing is set
            {
                $whilePara = "sfs-true-fixed";
            }
            $GLOBALS["sfs-true-fixed"] = true;

            $fs1 = fopen($data["table"], "r");
            //rewind($fs1);
            
            $fileContent = fread($fs1, $GLOBALS['sfs-file-max-updater-in-one-script-as-multiplier']*filesize($data["table"]));
            // echo $fileContent;
            $content = explode("\n", $fileContent);
            fclose($fs1);

            $fields = explode("?", $content[0]);
            $fields = array_map("trim", $fields);




            $reversed = $data["reversed"] ?? $data["reverse"] ?? false;
            if ($reversed == false)// not reversed ...  repeat from topto bottom
            {
                for ($i = 1; $i < count($content) && $GLOBALS[$whilePara]; $i++)//repeating each record
                {
                    repeatProccess($i, $data, $fields, $content);
                }
            }
            if ($reversed) {
                for ($i = count($content) - 1; $i > 0 && $GLOBALS[$whilePara]; $i--)//repeating each record
                {
                    repeatProccess($i, $data, $fields, $content);
                }
            }
        }
    }
    //manipulating records 
    if ((isset($data["*change"]) || isset($data["change"]) || isset($data["delete"])) && isset($data["table"])) {
        if (file_exists($data["table"])) {
            //while atribute: key to follow the each function(changing or deleteing is done only if true)
            //$whilePara = isset($data["while"]) ? $data["while"] : "sfs-true-fixed";
            if (isset($data["while"]))//for older versions---while works as before
            {
                $whilePara = $data["while"];
            }
            else if(isset($data["*while"]))//new recomended version
            {
                $whilePara = $data["*while"];
            }
            else//not set while( or *while)
            {
                $whilePara = "sfs-true-fixed";
            }
            $GLOBALS["sfs-true-fixed"] = true;

            $fs = fopen($data["table"], "r");
            //rewind($fs);
            $content = explode("\n", fread($fs, $GLOBALS['sfs-file-max-updater-in-one-script-as-multiplier']* filesize($data["table"])));
            fclose($fs);

            $fields = explode("?", $content[0]);
            $fields = array_map("trim", $fields);




            $reversed = $data["reversed"] ?? $data["reverse"] ?? false;
            if ($reversed == false)// not reversed ...  repeat from topto bottom
            {
                $newCont = $content[0];
                for ($i = 1; $i < count($content); $i++)//repeating each record
                {
                    $newAdd = repeatProccessManipulate($i, $data, $fields, $content, $whilePara);
                    $newCont .= ($newAdd == "" ? "" : "\n") . $newAdd;
                }
                $newF = fopen($data["table"], "w");
                fwrite($newF, $newCont);
                fclose($newF);
            }
            if ($reversed) {
                $newCont = "";

                for ($i = count($content) - 1; $i > 0; $i--)//repeating each record
                {
                    $newAdd = repeatProccessManipulate($i, $data, $fields, $content, $whilePara);
                    $newCont = $newAdd . ($newCont == "" ? "" : "\n") .  $newCont;
                }
                $newCont = $content[0]  .($newCont ==''?:"\n"). $newCont;
                $newF = fopen($data["table"], "w");
                fwrite($newF, $newCont);
                fclose($newF);
            }
        }
    }
    
    //show table function
    if(isset($data['show']))
    {
        $output ='<table><tr>';
        $fields = getsfs($data['show'])['fields'];
        foreach($fields as $field)
        {
            $output .= '<td style="">' . sfseditable($field) . '</td>';
        }
        $output .= '</tr>';
        $GLOBALS['sfs-output'] = $output;
        $GLOBALS['sfs-fields'] = $fields;

        $viewDB['table']= $data['show'];
        $viewDB['function'] = function($row)
        {
            $GLOBALS['sfs-output']  .= '<tr>';
            foreach ($GLOBALS['sfs-fields'] as $field) 
            {
                $GLOBALS['sfs-output']  .= '<td>' . sfseditable($row[$field]) . '</td>';
            }
            $GLOBALS['sfs-output']  .= '</tr>';
        };
        runsfs($viewDB);
        $GLOBALS['sfs-output'] .= '</table>';
        echo $GLOBALS['sfs-output'];
        


    }
}//end of runsfs
function sfseditable($val)
{
    $out = '<textarea>' . html($val) . '</textarea>';
    return $out;
}
function getsfs($filePath)
{if(!file_exists($filePath)){return false;}
$file = fopen($filePath,'r');
$cont = explode("\n",fread($file, $GLOBALS['sfs-file-max-updater-in-one-script-as-multiplier']*filesize($filePath)));
$fields = explode("?",$cont[0]);

return array('fields'=>$fields,'count'=>count($cont)-1);
}
function repeatProccessManipulate($i, $data, $fields, $content, $whilePara)
{
    $thisRow = explode("?", $content[$i]);
    $record = [];
    for ($u = 0; $u < count($fields); $u++) {
        $recordVal = $thisRow[$u] ?? "";
        if (preg_match("/^@/", $recordVal))  // hav stored a link to a binary file
        {
            $folderName = pathinfo($data["table"])["dirname"] . "/binary";
            $fileName = explode("@", $recordVal)[1];
            $externalFilePath = $folderName . "/" . $fileName;

            $needBinary = $data["extract-binary"] ?? false;


                    if ($needBinary) // if the data in the external file is requested
                    {
                                if (file_exists($externalFilePath)) //file shud exist
                                {
                                    $extFile = fopen($externalFilePath, "r");
                                    $binaryContent = fread($extFile, $GLOBALS['sfs-file-max-updater-in-one-script-as-multiplier']*filesize($externalFilePath));
                                    fclose($extFile);

                                    $record[$fields[$u]] = $binaryContent;
                                } else // no file found => return a blank
                                {
                                    $record[$fields[$u]] = "";
                                }
                    } 
                    else  // they dont request the file content... so return file path
                    {
                        $record[$fields[$u]] = $externalFilePath;
                    }
        }
        else // not a binary file.. just return stored string
        {
            $record[$fields[$u]] = urldecode($recordVal);
        }
    }
    $needChange =$GLOBALS[$whilePara] &&  $data["if"]($record);
    /* ^^^^ this code:
    if while has become false => no need to run the 'if' function
    */
    $delete = $data["delete"] ?? false;
    if (   !$delete  && $needChange)//change proceed
    {
        $returnStr = "";
        for ($y = 0; $y < count($fields); $y++) {
            $val = $thisRow[$y];
            if (isset($data["change"][$fields[$y]]) || isset($data["*change"][$fields[$y]])) {
                //if need to take the value out of a global var
                if(isset($data['*change'][$fields[$y]]))//checking *change is set
                {
                    $val = urlencode($GLOBALS[$data["*change"][$fields[$y]]]); 
                }
                //changed with same value for all records
                else
                {
                    $val = urlencode($data["change"][$fields[$y]]); 
                }
               
            }
            $returnStr .= ($returnStr == "" ? "" : "?") .$val;//no need to encode val
        }
        return $returnStr;
    } else if ($delete && $needChange)//delete =  give nothing
    {
        return "";
    } else {
        return $content[$i];
    }
}//end of repeat fnction -- all proceedure

function repeatProccess($i, $data, $fields, $content)
{
    $thisRow = explode("?", $content[$i]);
    $record = [];
    for ($u = 0; $u < count($fields); $u++) {
        $recordVal = $thisRow[$u] ?? "";
        if (preg_match("/^@/", $recordVal))  // hav stored a link to a binary file
        {
            $folderName = pathinfo($data["table"])["dirname"] . "/binary";
            $fileName = explode("@", $recordVal)[1];
            $externalFilePath = $folderName . "/" . $fileName;

            $needBinary = $data["extract-binary"] ?? false;


            if ($needBinary) // if the data in the external file is requested
            {


                if (file_exists($externalFilePath)) //file shud exist
                {
                    $extFile = fopen($externalFilePath, "r");
                    $binaryContent = fread($extFile, $GLOBALS['sfs-file-max-updater-in-one-script-as-multiplier']* filesize($externalFilePath));
                    fclose($extFile);

                    $record[$fields[$u]] = $binaryContent;
                } else // no file found => return a blank
                {
                    $record[$fields[$u]] = "";
                }
            } else  // they dont request the file content... so return file path
            {
                $record[$fields[$u]] = $externalFilePath;
            }
        } else // not a binary file.. just return stored string
        {
            $record[$fields[$u]] = urldecode($recordVal);
        }
    }
    $data["function"]($record);
}//end of repeat fnction -- all proceedure



    /******
     * SFS MANUAL * DATABASE MANAGEMENT  * ALL IN ONE * SIMPLE * FAST  * CLEARER *********
 
//creating a table***************************************************
$table["create"] = "I:/src/sfs/numbers.txt";  // file path ...  any extension
$table["fields"] = ["number","square","cube","visual"];  // field names
$table["overwrite"] =true; //optional... if file already exists => overwrites a blank table

runsfs($table);// proceed
     ***********************************************************************


//adding a record********************************************************
$table = []; // clearing previous stored data
$table["table"] = "I:/src/sfs/numbers.txt"; // select the table
$table["add"]["number"] = 3; // store a string
$table["add"]["square"] = 9;
$table["add"]["cube"] = 27;

$table["add"]["visual"] = $photoBinary;// image file data
$table["binary"]["visual"]=true; // now that data will be stored in the /binary folder 

runsfs($table);//proceed
     ************************************************************************

//adding  multiple records at once ***************************just input the record i.e. (directory like )arrays in a array************
$table = []; // clearing previous stored data
$table["table"] = "I:/src/sfs/numbers.txt"; // select the table
$table["addmultiple"] = [array('name'=>'rekieg','age'=>'3','visual'=>$photoBinary1),
                         array('name'=>'nandun','age'=>'4','visual'=>$photoBinary2),
                         array('name'=>'xxx','age'=>'5','visual'=>$photoBinary3)];

$table["binary"]["visual"]=true; // now that data will be stored in the /binary folder 

runsfs($table);//proceed
****************************************************************

//repeating function for each row ***********************************
$table = []; // clearing previous stored data
$table["table"] = "I:/src/sfs/numbers.txt"; // select the table

$table['*while' ] :::::: *while is more recomended...to give the feeling like its a pointer to global var!
however 'while' also works
$table["while"] = "pro_0"; // optional ...  function repeats while this GLOBAL variable is true
$GLOBALS["pro_0"] = true;  // setting that global variable = true *** compulsory if while used

$table["function"] = function($record)  // function to do each row
   {
       echo "number " . $record["number"];
       if ($record["number"] == 2) {$GLOBALS["pro_0"] = false;} // after reaching number=2 loop will end

   }

$table["reverse"] = true; // optional ... records will be considered from bottm to top

runsfs($table); // proceed
     *****************************************************************************

//changing or deleteing a record *********************RISKY keep back up everytime you can***********
$table = []; // clearing previous stored data
$table["table"] = "I:/src/sfs/numbers.txt"; // select the table

$table["*while"] = "pro_1"; // optional ...  delete/change is while this GLOBAL variable is true
$GLOBALS["pro_1"] = true;  // setting that global variable = true *** compulsory if while used
after becoming false all other records wil be just copied
remember if you set the WHILE_VARIABLE false in 'IF' that will not be changed

$table["if"] = function($record){return $record["name"] == "";}; // condition to delete or change

    DELETE - priority
    $table["delete"] = true;// records satisfying the condition will be deleted

    CHANGE
    $table["change"]["name"] = "no name"; // name of all records satifying condition will become "no name";
    $table["change"]["errorname"] = "true"; //errorname "" "" "" "" ""   '"        ""       ""      "true"

    ::::using "*change" will get the value from the global variable named as that value...used to dynamic changing.

$table["reverse"] =true; //optional --  start from bottom
runsfs($table); // proceed   ---- JUST REMEMBER NOTHING TO DO IF ALL GET DELETED!

//counting technique:**********************************
the 'if' function will not run if the while para become false;

to delete or change 14 rows use code as follow:

  set  'if' => function($r){if(YOUR_CONDITION_TO_CHANGE_OR_DELETE){countsfs('whilePara');return true;}}
        'while' => 'whilePara'
    $GLOBALS['whilePara'] = 14;
    runsfs


    countsfs($varName,$weight)  : it will decriment the global set $varName by $weight if its not 0;(until it become 0)
    ex:count('xxx',5) will decriment global set 'xxx' by 5; but never giving negetives
       xxx initial    xxx final
       5               0
       19              14
       3               0
     *************************

SPECIAL INSTRUCTIONS TO AVOID errors**************************************************************error warning********
$GLOBALS['sfs-file-max-updater-in-one-script-as-multiplier'] = 50;
is already set in top..it means you shud not exceed updating a file 50 times of its initial filesize if you use that file again in same script/php file/page ---- KEEP THIS IN MIND WHILE USING SFS -----
     */






    if (!true) {
    ?><!DOCTYPE html><html><head><title>RealFeed - Site Suspended Temporaly</title></head><body style="overflow:hidden;margin:0px;background-color:#000000;color:#ffffff;"><div id="msg" style="position:absolute;top:-4px;font-size:14px;left:20px;"><img src="http://www.realfeed.com/favicon1.png" style="height: 17px;width: 17px;vertical-align: middle;margin-left:4px;margin-right:5px;" /><code>Site susspended until 2019 FIRST SEM EXAM @ UoM</code></div><script>var p=-8;p=window.innerHeight*Math.random();setInterval(function(){var w=window.innerHeight;if(p>w){p=-15;}else{p+=0.3;}document.getElementById("msg").style.top=p+"px";},5);</script></body></html>
 <?php
        exit();
    }


//echo urlencode("\n");exit();


/*
//trying sfs database editis

$creatingTable = array('create'=>'table.sfs','fields'=>['number','square','cube','marked']);
runsfs($creatingTable);


for($i=0;$i<40;$i++):
    $table = array('table'=>'table.sfs','add'=>array('number'=>$i,'square'=>$i*$i,'cube'=>$i*$i*$i));
//  runsfs($table);
endfor;


$GLOBALS['w'] = 5;
$GLOBALS['f'] = 0;
$deleteProcess = array('table' =>'table.sfs','while'=>'w','change'=>array('marked' => 'true'),'if'=>function($thisRow){

    $GLOBALS['f']++;
    $number = intval($thisRow['number']);
    if($number < 100 && $number%2 && $thisRow['marked'] != 'true')
    {
        countsfs('w');
        return true;
    }
   
},'reversed'=>!false);
runsfs($deleteProcess);






echo '<b>Table Data</b><br/><code><table><tr><td>Number</td><td>Square</td><td>Cube</td><td>Marked</td></tr>';
$getTable=array('table'=>'table.sfs','function'=>function($thisRow){
    $marked = ($thisRow['marked'] == 'true' ? '<b>Marked</b>':'');
    echo '<tr><td>'.$thisRow['number']. '</td><td>' . $thisRow['square'] . '</td><td>' . $thisRow['cube'] . '</td><td>' . $marked . '</td></tr>';
});
runsfs($getTable);

echo '</table></code>';
echo '<br/> function run count: ' . $GLOBALS['f'];






exit();
*/
/*
//show function checkings
$table = array('show' =>'table.sfs');runsfs($table);
exit();


*/


?>