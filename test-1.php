<?php
//sleep(1);
//root directiory declaration
$httpRoot = 'D:/HTTP_WEB/realfeedserver/';

// STRUCTURED FUNCTION SET 
require $httpRoot . 'sfs.php';

// $table = []; // clearing previous stored data
// $table["table"] = "D:/HTTP_WEB/realfeedServer/photo.manager/db/posts.sfs"; // select the table

//$table['*while' ] //:::::: *while is more recomended...to give the feeling like its a pointer to global var!
//however 'while' also works


// $posts = [];



// $table["while"] = "pro_0"; // optional ...  function repeats while this GLOBAL variable is true
// $GLOBALS["pro_0"] = true;  // setting that global variable = true *** compulsory if while used

// $table["function"] = function ($record)  // function to do each row
// {
//     $date = $record["date"];
//     $postid = $record["postid"];
//     $featuring = $record["featuring"];
//     $imageids = $record["imageids"];
//     $discription = $record["discription"];
//     $about = $record["about"];

//     $thisRecord = [
//         'date' => $date,
//         'postid' => $postid,
//         'featuring' => $featuring,
//         'imageids' => $imageids,
//         'discription' => $discription,
//         'about' => $about
//     ];

//     array_push($GLOBALS['posts'], $thisRecord);



// echo $record["postid"] . "<br/>";
// echo  $record["featuring"] . " <br/> "  . $record["imageids"] . "<hr/>";
//if ($record["number"] == 2) {$GLOBALS["pro_0"] = false;} // after reaching number=2 loop will end

// };

// echo "<hr/><hr/>";





//$table["reverse"] = true; // optional ... records will be considered from bottm to top

// runsfs($table); // proceed



// echo json_encode($posts);




$table = []; // clearing previous stored data
$table["table"] = "D:/HTTP_WEB/realfeedServer/photo.manager/db/people.sfs"; // select the table

//$table['*while' ] //:::::: *while is more recomended...to give the feeling like its a pointer to global var!
//however 'while' also works


$people = [];



$table["while"] = "pro_0"; // optional ...  function repeats while this GLOBAL variable is true
$GLOBALS["pro_0"] = true;  // setting that global variable = true *** compulsory if while used

$table["function"] = function ($record)  // function to do each row
{
    $id = $record['id'];
    $name = $record['name'];
    $othernames = $record['othernames'];
    $dp = $record['dp'];
    $ref = $record['ref'];
    $tags = $record['tags'];

    $thisRecord = [
        'id' => $id,
        'name' => $name,
        'othernames' => $othernames,
        'dp' => $dp,
        'ref' => $ref,
        'tags' => $tags
    ];

    array_push($GLOBALS['people'], $thisRecord);



    // echo $record["postid"] . "<br/>";
    // echo  $record["featuring"] . " <br/> "  . $record["imageids"] . "<hr/>";
    //if ($record["number"] == 2) {$GLOBALS["pro_0"] = false;} // after reaching number=2 loop will end

};

echo "<hr/><hr/>";





//$table["reverse"] = true; // optional ... records will be considered from bottm to top

runsfs($table); // proceed



echo json_encode($people);