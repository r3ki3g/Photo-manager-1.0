# Photo-manager 1.0 with Face Recognition

## Introduction
This is a platform where you can manage all your photos privately. With Photo-manager, you can upload photos/photo-collections with a description, and people in the photo been tagged, so you can easily search and filter your photos.
### Languages
Both Sinhala and English languages are supported!
### Upload
Just Drag and drop your images! Provide a breif description, Tag the people in images by search people bar, add captions per each individual image if you want.
### Search
Just search by the description or peoples name, use the filter by people option to search by people groups.
### Favorite
Favorites will be shown in the home page when you visit the Photo-manager.

## Technical details for developers
### Face Recognition
[See the demo video](https://youtu.be/9TQLQlWcoSg?si=ojBd54QFrmM8HpTL)

Face recognition is handled by a separate Python-Flask API server dedicated for AI. Requests are made when new photos are uploaded.
Using a MTCNN face detector [link](https://github.com/ipazc/mtcnn) followed by a pre-processor followed by a Face-Net [link](https://github.com/davidsandberg/facenet)
In the pre-processor the faces detected in by the MTCNN are warped/rotated to improve the performence of Face-Net. 
Face identities are calculated by the distance between teh embeddings output by Face-Net.
When creating an account per person, face has to be set as display picture. This will be initially taken as the anchor. 
Later all the images that person get tagged will be used as anchors iff the distance is considerebly large. (A diverese set of face examples per person!)
### Server - PHP
Using a PHP 7 server (on Windows IIS 10), while most of the PHP logic is concentarted in "photo.manager.php" file.
### Database - SFS (Structured Function Set)
SFS is my own database system which saves tables as .sfs files. Source code is available at "SFS.PHP" file.
Quick intro to SFS. Really easy to manage (create tables, add records, see records, edit/delete records)
### Front end
jQuery application. Code at '/js/photomanager.js'
### SFS - Documentation

<pre>
```
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

```
</pre>
