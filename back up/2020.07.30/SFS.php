<?php 

header("X-powered-by:sfs 1.0 -php 7.1 - server IIS 10.0");
header("Security-Level:12/12");

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
    {
        return htmlspecialchars(strval($str));
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



    function uniqueId($len = 25)
    {
        $id = strval(microtime(true)) . "-";
        while (strlen($id) <= $len) {
            $id .= mt_rand(0, 9);
        }
        return $id;
    }

    function addAsArray($array,$item)
    {
        if(isset($array)){array_push($array,$item);}
        else{$array = [$item];}

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

        //repeating a function in each record
        if (isset($data["function"]) && isset($data["table"])) {
            if (file_exists($data["table"])) {
                //while atribute: key to follow the each function
                $whilePara = isset($data["while"]) ? $data["while"] : "sfs-true-fixed";
                $GLOBALS["sfs-true-fixed"] = true;

                $fs1 = fopen($data["table"], "r");
                $content = explode("\n", fread($fs1, filesize($data["table"])));
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
        if ((isset($data["change"]) || isset($data["delete"])) && isset($data["table"])) {
            if (file_exists($data["table"])) {
                //while atribute: key to follow the each function
                $whilePara = isset($data["while"]) ? $data["while"] : "sfs-true-fixed";
                $GLOBALS["sfs-true-fixed"] = true;

                $fs = fopen($data["table"], "r");
                $content = explode("\n", fread($fs, filesize($data["table"])));
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
                        $newCont = ($newAdd == "" ? "" : "\n") . $newAdd . $newCont;
                    }
                    $newCont = $content[0]  . $newCont;
                    $newF = fopen($data["table"], "w");
                    fwrite($newF, $newCont);
                    fclose($newF);
                }
            }
        }
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
                        $binaryContent = fread($extFile, filesize($externalFilePath));
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
        $needChange = $data["if"]($record);
        $delete = $data["delete"] ?? false;
        if ($needChange == true && $GLOBALS[$whilePara] && !$delete)//change proceed
        {
            $returnStr = "";
            for ($y = 0; $y < count($fields); $y++) {
                $val = $thisRow[$y];
                if (isset($data["change"][$fields[$y]])) {
                    $val = $data["change"][$fields[$y]];
                }
                $returnStr .= ($returnStr == "" ? "" : "?") .$val;//no need to encode val
            }
            return $returnStr;
        } else if ($delete && $needChange && $GLOBALS[$whilePara])//delete =  give nothing
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
                        $binaryContent = fread($extFile, filesize($externalFilePath));
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

//repeating function for each row ***********************************
$table = []; // clearing previous stored data
$table["table"] = "I:/src/sfs/numbers.txt"; // select the table

$table["while"] = "pro_0"; // optional ...  function repeats while this GLOBAL variable is true
$GLOBALS["pro_0"] = true;  // setting that global variable = true *** compulsory if while used

$table["each"] = function($record)  // function to do each row
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

$table["while"] = "pro_1"; // optional ...  delete/change is while this GLOBAL variable is true
$GLOBALS["pro_1"] = true;  // setting that global variable = true *** compulsory if while used
after becoming false all other records wil be just copied
remember if you set the WHILE_VARIABLE false in 'IF' that will not be changed

$table["if"] = function($record){return $record["name"] == "";}; // condition to delete or change

    DELETE - priority
    $table["delete"] = true;// records satisfying the condition will be deleted

    CHANGE
    $table["change"]["name"] = "no name"; // name of all records satifying condition will become "no name";
    $table["change"]["errorname"] = "true"; //errorname "" "" "" "" ""   '"        ""       ""      "true"

$table["reverse"] =true; //optional --  start from bottom
runsfs($table); // proceed   ---- JUST REMEMBER NOTHING TO DO IF ALL GET DELETED!

     ************************************************************************


     */






    if (!true) {
    ?><!DOCTYPE html><html><head><title>RealFeed - Site Suspended Temporaly</title></head><body style="overflow:hidden;margin:0px;background-color:#000000;color:#ffffff;"><div id="msg" style="position:absolute;top:-4px;font-size:14px;left:20px;"><img src="http://www.realfeed.com/favicon1.png" style="height: 17px;width: 17px;vertical-align: middle;margin-left:4px;margin-right:5px;" /><code>Site susspended for sometime...Come back later</code></div><script>var p=-8;p=window.innerHeight*Math.random();setInterval(function(){var w=window.innerHeight;if(p>w){p=-15;}else{p+=0.3;}document.getElementById("msg").style.top=p+"px";},5);</script></body></html>
 <?php
        exit();
    }


    ?>