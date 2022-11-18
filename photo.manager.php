<?php   
//sleep(1);
//root directiory declaration
$httpRoot = 'D:/HTTP_WEB/realfeedserver/';

// STRUCTURED FUNCTION SET 
require $httpRoot . 'sfs.php';




//function declrartions
function isValidNameToSave($name)
{
    $name = trim($name);
    if ($name == "") {
        return false;
    }
    if (strlen($name) > 23 || strlen($name) < 2) {
        return false;
    }
    if (preg_match("/[^0-9a-z\.\s\_]/i", $name)) {
        //echo 44;
        return false;
    }
    return true;
}
function isValidOtherNamesToSave($name)
{
    $name = trim($name);

    if (strlen($name) > 200) {
        return false;
    }
    if (preg_match("/[^0-9a-z\.\s\_\n]/i", $name)) {
        return false;
    }
    return true;
}
function altDir()
{
    $url = $_SERVER['PHP_SELF'];
    $base =  '/photo.manager.php/';
    $anyUrlDirComp = count(explode($base, $url)) - 1;
    if ($anyUrlDirComp) {
        $dirParts = explode('/', explode($base, $url, 2)[1]);
        return $dirParts;
    }
    return [];
}

//header stuff  -- no caching
header('cache-control:no-store,no-cache,must-revalidate');
header('pragma:no-cache');








//asking for page purpose
$language = $_GET['language'] ?? false;
$upload_ = $_GET["upload"] ?? false;
$suggesions = $_GET["suggesions"] ?? false;
$dp = $_GET["dp"] ?? false;
$people = $_GET["addpeople"] ?? false;
$addphoto = $_GET["addphotos"] ??  false;
$contentSearch = $_GET["contentsearch"] ??  false;
$favoRandomPhotos = $_GET['favouriterandome'] ?? false;
$favoriteFunction = $_GET['favourite'] ?? false;
$unfavoriteFunction = $_GET['unfavourite'] ?? false;
$profileSettingsAsked = $_GET['profilesettings'] ?? false;
$photosForDp = $_GET['photosfordp'] ?? false;
$dpcandidatesforupdatedp = $_GET['dpcandidatesforupdatedp'] ?? false;
$updateDp = $_GET['updatedp'] ?? false;
$updateProfileSettings = $_GET['updateprofilesettings'] ?? false;
$latestActivity = $_GET['latestactivity'] ?? false;


/*
//checking whether url is a fake alternative page
$pageURL = altDir();
print_r($pageURL);
exit();
*/

//checking language set request??
if(in_array($language,['english','sinhala']))
{
setcookie('language',$language,time()+3600*24*30,'/', '', false, false);
header('location:http://www.realfeed.com/photo.manager.php?refreshed_for_language_change');
exit();
}

$pageLanguage = 'english';
if(in_array($_COOKIE['language']??false,['english','sinhala']))
{
    $pageLanguage = $_COOKIE['language'];
}


//core functions
if ($upload_ === "true") {
    $file_ = $_FILES["file"];
    //print_r($file_);
    $filePath = $file_["tmp_name"];

    //checking whether file alredy saved here -- > considering the original file name******************
    $originalFileName = $file_["name"];
    define('originalFileName', $originalFileName);
    //creation
    $fileNameLog["create"] = $httpRoot . '/photo.manager/db/photonamelog.sfs';
    $fileNameLog["fields"] = ['filename', 'date'];
    runsfs($fileNameLog);

    //checking whether a duplicate
    $fileNameLog = [];
    $fileNameLog["table"] = $httpRoot . '/photo.manager/db/photonamelog.sfs';
    $fileNameLog["while"] = "notFoundDuplicate";
    $GLOBALS["notFoundDuplicate"] =  true;
    $fileNameLog["function"] = function ($log) {
        $logSavedFileName = $log["filename"];
        if ($logSavedFileName == originalFileName) {
            $GLOBALS["notFoundDuplicate"] =  false;
        }
    };
    runsfs($fileNameLog);
    $duplicate = !$GLOBALS["notFoundDuplicate"];


    //if not duplicate add to table
    $fileNameLog = [];
    if ($GLOBALS["notFoundDuplicate"]) {
        $fileNameLog["table"] = $httpRoot . '/photo.manager/db/photonamelog.sfs';
        $fileNameLog["add"]["filename"] = originalFileName;
        $fileNameLog["add"]["date"] = time();
        runsfs($fileNameLog);
    }

    //duplicate finding over **********************************************************************************



    $imageData = getimagesize($filePath);
   

    if ($imageData == false) // not a image file
    {
        $ouput = [];
        $output["status"] = "not_an_image";
        $outText = json_encode($output);
        echo $outText;
        exit();
        return false;
    }

    //sure its an image  -- check a allowed mime type or not

    $imageMime = $imageData["mime"];
    $imageSize = $file_["size"];
    if ($imageMime != "image/gif" && $imageMime != "image/png" && $imageMime != "image/jpg" && $imageMime != "image/jpeg" && $imageMime != "image/bmp") {
        $ouput = [];
        $output["status"] = "mime_type_not_allowed";
        $outText = json_encode($output);
        echo $outText;
        exit();
        return false;
    }

    //now its a good file
    require $httpRoot . "/php.api/SimpleImage.php";
    $IMG = new \claviska\SimpleImage();
    $IMG = $IMG->fromFile($filePath);
    $height = $IMG->getHeight();
    $width =  $IMG->getWidth();
    //echo "height $height width $width";

    $newFileName = md5(uniqueId(67)) . md5(uniqueId(67));
    while (file_exists($httpRoot . "/photo.manager/" . $newFileName . ".jpeg")) {
        $newFileName = md5(uniqueId(67)) . md5(uniqueId(67));
    }

    $IMG->toFile($httpRoot . "/photo.manager/" . $newFileName . ".jpeg", "image/jpeg");


    //creating table allphotos to keep records about ALL UPLOADED PHOTOS
    $allPhotos["create"] = $httpRoot . "/photo.manager/db/allphotos.sfs";
    $allPhotos["fields"] = ["date", "filename", "size"];
    runsfs($allPhotos);

    //adding data about this image to allphotos.sfs
    $thisPhoto["table"] = $httpRoot . "/photo.manager/db/allphotos.sfs";
    $thisPhoto["add"]["date"] = time();
    $thisPhoto["add"]["filename"] = $newFileName . ".jpeg";
    $thisPhoto["add"]["size"] = $imageSize;
    runsfs($thisPhoto);

    //now send that the file upload is success
    $output["status"] = "success";
    $output["photoid"] = $newFileName;
    $output["filepath"] = "http://www.realfeed.com/photo.manager/" . $newFileName . ".jpeg";
    $output["height"] = $height;
    $output["width"] = $width;
    $output["alreadyfound"] = $duplicate;
    $output["filenamerecieved"] = htmlspecialchars(originalFileName);
    $outText = json_encode($output);
    echo $outText;
    exit();
    return false;
} else if ($suggesions == "people") //asking for people suggessions
{
    $key_ = $_POST["query"] ?? false;
    if (!$key_) {
        return false;
    }//no query recived

    $output["query"] = addslashes(htmlspecialchars($key_));

    //create people table
    $people["create"] = $httpRoot . "/photo.manager/db/people.sfs";
    $people["fields"] = ["id", "name", "othernames", "dp", "ref", "tags"];
    runsfs($people);

    $q = trim($key_);
    str_replace(" ", "", $q);

    if (strlen($q) > 55) //query too long  ... 35 was earlier
    {
        $outputErr["status"] = "query_too_long";
        $out = json_encode($outputErr);
        echo $out;
        exit();
        return false;
    }

    $GLOBALS["searchQuery"] = addcslashes($q, '.,\/\\+-*^%$#@!90[]\"\':;><?');
    $GLOBALS["suggestion"] = [];
    $search["table"] = $httpRoot . "/photo.manager/db/people.sfs";
    $search["while"] = "searchPeopleForSugg";
    $GLOBALS["searchPeopleForSugg"] = true;
    $search["function"] = function ($person) {

        $fullName = $person["name"] . $person["othernames"];
        $matching = preg_match("/" . $GLOBALS["searchQuery"] . "/i", $fullName);
        if ($matching) {
            $thisPerson = [];
            $thisPerson["name"] = $person["name"];
            $thisPerson["dp"] = $person["dp"];
            $thisPerson["id"] = $person["id"];
            array_push($GLOBALS["suggestion"], $thisPerson);


            //stop over searching
            if (count($GLOBALS["suggestion"]) >= 10) {
                $GLOBALS["searchPeopleForSugg"] = false;
            }
        }
    };
    runsfs($search);

    $output["status"] = "success";
    $output["query"] = htmlspecialchars($GLOBALS["searchQuery"]);
    $output["suggestions"] = $GLOBALS["suggestion"];
    $output["forcestopped"] = !$GLOBALS["searchPeopleForSugg"];

    $outText = json_encode($output);
    echo $outText;
    return false;









    $outText = json_encode($output);
    echo $outText;
    exit();
} else if ($dp == "true")//dp crop and save
{
    $x = $_POST["x"] ?? false;
    $y = $_POST["y"] ?? false;
    $src = $_POST["src"] ?? false;
    if ($x && $y && $src && preg_match("/^http:\/\/www.realfeed.com\/photo.manager\//i", $src)) {
        $path = preg_split("/^http:\/\/www.realfeed.com\/photo.manager\//i", $src)[1];
        if (file_exists($httpRoot . "/photo.manager/" . $path) && is_numeric($x) && is_numeric($y)) {
            require $httpRoot . "/php.api/SimpleImage.php";
            $IMG = new \claviska\SimpleImage();
            $IMG = $IMG->fromFile($httpRoot . "/photo.manager/" . $path);
            $height = $IMG->getHeight();
            $width =  $IMG->getWidth();

            if ($height > $y && $width > $x) {
                //find the max square  // radius
                $radius = intval(min($y, $height - $y, $x, $width - $x)) - 1;

                $out = [];
                for ($i = 1; $i < 9; $i++) {
                    $r =  intval($radius / $i);
                    $newFileName = md5(uniqueId(67)) . md5(uniqueId(67));
                    while (file_exists($httpRoot . "/photo.manager/" . $newFileName . ".jpeg")) {
                        $newFileName = md5(uniqueId(67)) . md5(uniqueId(67));
                    }


                    // print_r([$i,$r,$x-$r,$y+$r,$x+$r,$y-$r]);
                    $IMG = new \claviska\SimpleImage();
                    $IMG = $IMG->fromFile($httpRoot . "/photo.manager/" . $path);
                    $IMG->crop(intval($x - $r), intval($y + $r), intval($x + $r), intval($y - $r))->resize(200, 200)->brighten(20)->toFile($httpRoot . "/photo.manager/" . $newFileName . ".jpeg", "image/jpeg");
                    array_push($out, $newFileName);

                    //adding data about this image to allphotos.sfs
                    $thisPhoto["table"] = $httpRoot . "/photo.manager/db/allphotos.sfs";
                    $thisPhoto["add"]["date"] = time();
                    $thisPhoto["add"]["filename"] = $newFileName . ".jpeg";
                    $thisPhoto["add"]["size"] = "";
                    runsfs($thisPhoto);
                }
                $output["status"] = "success";
                $output["urls"] = $out;
                $out = json_encode($output);
                echo $out;
                exit();
            }
        }
    }
    $output["status"] = "error";
    $out = json_encode($output);
    echo $out;
    exit();
} else if ($people == "true")//add new people
{
    $name = $_POST["name"] ?? false;
    $othernames = $_POST["othernames"] ?? false;
    $dp = $_POST["dp"] ?? false;
    //echo isValidNameToSave($name);
    if ($name && $othernames && $dp) {
        if (isValidNameToSave($name) && isValidOtherNamesToSave($othernames)) {
            $dpURL = "";
            if (file_exists($httpRoot . "/photo.manager/" . $dp . ".jpeg")) {
                $dpURL = $httpRoot . "/photo.manager/" . $dp . ".jpeg";
            }


            $myId = uniqueId();;
            $peopleAdd["table"] = $httpRoot . "/photo.manager/db/people.sfs";
            $peopleAdd["add"]["id"] = $myId;
            $peopleAdd["add"]["name"] = $name;
            $peopleAdd["add"]["othernames"] = $othernames;
            $peopleAdd["add"]["dp"] = $dp;

            //= ["id", "name", "othernames", "dp", "ref", "tags"];
            runsfs($peopleAdd);

            $output["status"] = "success";
            $output["id"] = $myId;
            $outText = json_encode($output);
            echo $outText;
            exit();
        }
    }



    $output["status"] = "error";
    //$output["id"] = $myId;
    $outText = json_encode($output);
    echo $outText;
    exit();
} else if ($addphoto == "true") {
    $dataString = $_POST["datastring"] ?? "";
    $data = json_decode($dataString, true);

    //print_r($data->featuringlist);

    $featuringList = $data["featuringlist"];
    define("featuringList", $featuringList);

    $discription = $data["discription"];
    define("discription", $discription);


    $imageSet = $data["imageset"];
    //print_r($imageSet);
    define("imageSet", $imageSet);


    //check valid featutings
    $people["table"] =  $httpRoot . "/photo.manager/db/people.sfs";
    $GLOBALS["requestfeaturingscount"] = count($featuringList);
    $people["while"] = "requestfeaturingscount";
    $people["function"] = function ($person) {
        foreach (featuringList as $id) {
            if ($person["id"] == $id) {
                $GLOBALS["requestfeaturingscount"]--;
            }
        }
    };
    runsfs($people);
    if ($GLOBALS["requestfeaturingscount"] > 0) {
        echo '{"status":"error","reason":"Featuring list is invalid"}';
        exit();
    }


    //check for valid images
    $allphotos["table"] = $httpRoot . "/photo.manager/db/allphotos.sfs";
    $GLOBALS['requestedphotocount'] = count(imageSet);
    $allphotos["while"] = 'requestedphotocount';
    $allphotos["function"] = function ($photo) {

        foreach (imageSet as $image) {
            $id = $image["photoid"];
            //echo $id , $photo["filename"];

            if ($id . ".jpeg" == $photo["filename"]) {


                $GLOBALS['requestedphotocount']--;
            }
        }
    };
    runsfs($allphotos);

    if ($GLOBALS['requestedphotocount'] > 0) {
        echo $GLOBALS['requestedphotocount'];
        echo '{"status":"error","reason":"Trying to map to a photo that does not exist at server"}';
        exit();
    }

    //saving image data
    //creation of main post db
    /*
    post db contains all posts  
         not: images are stored in a different table
      


    */
    $posts["create"] = $httpRoot . "/photo.manager/db/posts.sfs";
    $posts["fields"] = ["date", "postid", "featuring", "imageids", "discription", "about"];
    runsfs($posts);

    $postId = uniqueId();



    //making all caption in to one about and *identifying favourited images*
    $favoImgPhotoIds = [];
    $about = "";
    foreach (imageSet as $image) {
        $about .= "|" . $image["caption"];
        if ($image["favourited"] == true) {
            array_push($favoImgPhotoIds, $image["photoid"]);
        }
    }


    //for all photos
    $posts = [];
    $posts["table"] = $httpRoot . "/photo.manager/db/posts.sfs";
    $posts["add"]["date"] = time();
    $posts["add"]["postid"] = $postId;
    $posts["add"]["imageids"] = json_encode(imageSet);
    $posts["add"]["featuring"] = json_encode(featuringList);
    $posts["add"]["discription"] = discription;
    $posts["add"]["about"] = $about;
    runsfs($posts);

    //in each persons table adding the photo id
    foreach (featuringList as $person) :

        $personPhotos["create"] = $httpRoot . "/photo.manager/db/eachperson/" . $person . ".sfs";
        $personPhotos["fields"] = ["postid"];
        runsfs($personPhotos);

        $personPhotos = [];
        $personPhotos["table"] = $httpRoot . "/photo.manager/db/eachperson/" . $person . ".sfs";
        $personPhotos["add"]["postid"] = $postId;
        runsfs($personPhotos);




    endforeach;

    //adding favourited images to favorites.sfs
    //creation
    $favoPhotos["create"] = $httpRoot . "/photo.manager/db/favourites.sfs";
    $favoPhotos["fields"] = ["postid", "photoid", "date"];
    runsfs($favoPhotos);
    //adding
    $favoPhotos = [];
    $favoPhotos["table"] = $httpRoot . "/photo.manager/db/favourites.sfs";
    $favoPhotos["add"]["postid"] = $postId;
    $favoPhotos["add"]["date"] = time();
    foreach ($favoImgPhotoIds  as $thisPhotoId) {
        $favoPhotos["add"]["photoid"] = $thisPhotoId;
        runsfs($favoPhotos);
    }



























    echo '{"status":"success"}';
    exit();
}

// search photos
else if ($contentSearch == "true") {
    // query and query validation
    $query = trim($_POST["query"] ?? "");
    $query = addcslashes($query, '.,\/\\+-*^%$#@!90[]\"\':;><?');
    $queryLength = strlen($query);
    if ($queryLength > 50  || $queryLength < 1) {
        echo '{"status":"error","reason":"invalid query lenght"}';
        exit();
        return false;
    }
    $queryWords = explode(' ', $query);
    define('query', $query);
    define('queryWords', $queryWords);



    /*
     two types of results => PEOPLE and PHOTOS

     PEOPLE have to show number of photos and link to see their photos and search his photos

     Discription all matching photos with featuring 


     */

    //people search
    $GLOBALS["matchingPeopleList"] = [];
    $GLOBALS['matchingPeopleIds'] = []; //  to use in photo search
    $peopleSearch['table'] = $httpRoot . '/photo.manager/db/people.sfs';
    $peopleSearch['while'] = 'neededPeopleCount';
    $GLOBALS['neededPeopleCount'] = 20;
    $peopleSearch['function'] = function ($person) {
        //matching algorithm
        $points = 0;
        foreach (queryWords as $word) {
            $delimiter = '/' . $word . '/i';
            if (preg_match($delimiter, $person["name"])) :
                $points += 10;

            endif;

            if (preg_match($delimiter, $person['othernames'])) :
                $points += 5;

            endif;

            //additional 10 points if name matches at beggining
            $delimiter = '/^' . $word . '/i';
            if (preg_match($delimiter, $person["name"])) :
                $points += 10;

            endif;
        }

        if ($points > 0) {
            $GLOBALS['neededPeopleCount']--;
            array_push($GLOBALS['matchingPeopleList'], array(
                'name' => $person['name'],
                'id' => $person['id'],
                'points' => $points,
                'dp' => $person['dp']
            ));

            array_push($GLOBALS['matchingPeopleIds'], $person['id']);// to use in photo search
        }
    };
    runsfs($peopleSearch);

    function matchingPeopleSortFunction($left, $right)
    {
        $key  =   $right['points'] - $left['points'];
        return $key;
    }

    usort($GLOBALS['matchingPeopleList'], 'matchingPeopleSortFunction');


    $output['people'] = $GLOBALS['matchingPeopleList'];
    $output['peopleForceStoppedd'] =  !$GLOBALS['neededPeopleCount'];
    unset($peopleSearch);// no more needed

    //photo search  ****** photo search 

    //as a side program favourited image ids are loaded here
    $GLOBALS['allFavedPhotoIds'] = [];
    $favoListDB["table"] = $httpRoot . '/photo.manager/db/favourites.sfs';
    $favoListDB["function"] = function ($favedPhoto) {
        array_push($GLOBALS['allFavedPhotoIds'], $favedPhoto["photoid"]);
    };
    runsfs($favoListDB);
    define('allFavedPhotoIds', $GLOBALS['allFavedPhotoIds']);




    $GLOBALS["matchingPhotoList"] = [];
    $GLOBALS['featuringPeople'] = [];
    $GLOBALS['favedPhotosInSearchResult'] =  [];
    $photoSearch['table'] = $httpRoot . '/photo.manager/db/posts.sfs';
    $photoSearch['while'] = 'neededPhotoCount';
    $GLOBALS['neededPhotoCount'] = 20;//no use
    $photoSearch['reversed'] = true;
    $photoSearch['function'] = function ($post) {
        $photoDate = $post['date'];
        $photoPostId = $post['postid'];
        $photoDiscription = $post['discription'];
        $featuringList = json_decode($post['featuring'], true);
        $photoSet = json_decode($post['imageids'], true); // containd id,caption pairs


        //matching algorithm
        $points = 0;
        //overall discription matching checked here
        foreach (queryWords as $word) :
            $delimiter = '/' . $word . '/i';
            if (preg_match($delimiter, $photoDiscription)) :
                $points += 7;

            endif;
        endforeach;

        //checking whether matching a identified person as well
        foreach ($GLOBALS['matchingPeopleIds'] as $foundPerson) :
            if (in_array($foundPerson, $featuringList)) :
                $points += 13;

            endif;

        endforeach;

        //all / each photos has a caption attached .. giving priority to them
        //$imageSetOutput =[];***********************
        foreach ($photoSet as $photo) :
            $thisCaption = $photo['caption'];
            $thisId = $photo['photoid'];

            //if in favourited list add to favedPhotosInSearchResult
            if (in_array($thisId, allFavedPhotoIds)) {
                array_push($GLOBALS['favedPhotosInSearchResult'], $thisId);
            }


            $thisPoints = $points;

            foreach (queryWords as $word) :
                $delimiter = '/' . $word . '/i';
                if (preg_match($delimiter, $thisCaption)) :
                    $thisPoints += 15;

                endif;
            endforeach;

            if ($thisPoints > 0) {
                //$GLOBALS['neededPhotoCount']--; no limit for search

                //mensioned people remembering
                foreach ($featuringList  as $member) :
                    if (!in_array($member, $GLOBALS['featuringPeople'])) :
                        array_push($GLOBALS['featuringPeople'], $member);
                    endif;
                endforeach;


                array_push($GLOBALS['matchingPhotoList'], array(
                    'postid' => $photoPostId,
                    'path' => $thisId,
                    'caption' => htmlspecialchars($thisCaption),
                    'discription' => htmlspecialchars($photoDiscription),
                    'date' => $photoDate,
                    'featuring' => $featuringList,
                    'points' => $thisPoints

                ));
            }





        endforeach;
    };



    runsfs($photoSearch);

    function matchingPhotoSortFunction($left, $right)
    {
        $key  =   $right['points'] - $left['points'];
        return $key;
    }

    usort($GLOBALS['matchingPhotoList'], 'matchingPhotoSortFunction');


    $output['photos'] = $GLOBALS['matchingPhotoList'];
    $output['peopleForceStoppedd'] =  !$GLOBALS['neededPhotoCount'];

    //getting dps and names of people mensioned
    $GLOBALS['mensionedPeople'] = [];
    $mensioned['table'] = $httpRoot . '/photo.manager/db/people.sfs';
    $mensioned['while'] = 'remainingMensioned';
    $GLOBALS['remainingMensioned'] = count($GLOBALS['featuringPeople']);
    $mensioned['function'] = function ($thisPerson) {
        $thisPersonId = $thisPerson['id'];

        foreach ($GLOBALS['featuringPeople'] as $featuringPerson) :

            if ($featuringPerson == $thisPersonId) :
                // no  need to search more
                unset($GLOBALS['featuringPeople'][array_search($thisPersonId, $GLOBALS['featuringPeople'])]);

                $thisPersonName = $thisPerson['name'];
                $thisPersonDp = $thisPerson['dp'];
                $GLOBALS['mensionedPeople'][$thisPersonId] = array(
                    'name' => $thisPersonName,
                    'dp' => $thisPersonDp

                );



            endif;
        endforeach;
    };
    runsfs($mensioned);
    $output['mensioned'] = $GLOBALS['mensionedPeople'];
    $output['favoritedinsubjected'] = $GLOBALS['favedPhotosInSearchResult'];

    $output['status'] = 'success';


    $outText = json_encode($output);
    echo $outText;
    exit();
} else if ($favoRandomPhotos == "true")//asking some favourited photos randomly
{
    /*
data in favourites.sfs
    add them to array => select 8 from them randomly
*/

    $GLOBALS['favoAllArray'] = [];
    $favoRandomeDB["table"] = $httpRoot . '/photo.manager/db/favourites.sfs';
    $favoRandomeDB["function"] = function ($favoPhoto) {
        array_push($GLOBALS['favoAllArray'], $favoPhoto);
    };
    runsfs($favoRandomeDB);

    shuffle($GLOBALS['favoAllArray']);
    // selecting 8 items
    $selectedFavoPhotos = [];//selecting 8
    if (count($GLOBALS['favoAllArray']) >= 8) {
        $selectedFavoPhotos = array_slice($GLOBALS['favoAllArray'], 0, 8);
    } else {
        $selectedFavoPhotos = $GLOBALS['favoAllArray'];
    }


    //print_r($selectedFavoPhotos);
    $selectedFavoPhotoIds = [];
    foreach ($selectedFavoPhotos as $selectedFavePhoto) {
        array_push($selectedFavoPhotoIds, $selectedFavePhoto["photoid"]);
    }
    define('selectedFavoPhotoIds', $selectedFavoPhotoIds);
    //getting data photo data and feat data
    $GLOBALS["matchingPhotoList"] = [];
    $GLOBALS['featuringPeople'] = [];
    $photoSearch['table'] = $httpRoot . '/photo.manager/db/posts.sfs';
    $photoSearch['while'] = 'neededPhotoCount';
    $GLOBALS['neededPhotoCount'] = 20;
    $photoSearch['function'] = function ($post) {
        $photoDate = $post['date'];
        $photoPostId = $post['postid'];
        $photoDiscription = $post['discription'];
        $featuringList = json_decode($post['featuring'], true);
        $photoSet = json_decode($post['imageids'], true); // containd id,caption pairs






        // each single photo
        //$imageSetOutput =[];***********************
        foreach ($photoSet as $photo) :
            $thisCaption = $photo['caption'];
            $thisId = $photo['photoid'];




            if (in_array($thisId, selectedFavoPhotoIds)) {
                //$GLOBALS['neededPhotoCount']--; no limit for search

                //mensioned people remembering
                foreach ($featuringList  as $member) :
                    if (!in_array($member, $GLOBALS['featuringPeople'])) :
                        array_push($GLOBALS['featuringPeople'], $member);
                    endif;
                endforeach;


                array_push($GLOBALS['matchingPhotoList'], array(
                    'postid' => $photoPostId,
                    'path' => $thisId,
                    'caption' => htmlspecialchars($thisCaption),
                    'discription' => htmlspecialchars($photoDiscription),
                    'date' => $photoDate,
                    'featuring' => $featuringList


                ));
            }





        endforeach;
    };



    runsfs($photoSearch);




    $output['photos'] = $GLOBALS['matchingPhotoList'];


    //getting dps and names of people mensioned
    $GLOBALS['mensionedPeople'] = [];
    $mensioned['table'] = $httpRoot . '/photo.manager/db/people.sfs';
    $mensioned['while'] = 'remainingMensioned';
    $GLOBALS['remainingMensioned'] = count($GLOBALS['featuringPeople']);
    $mensioned['function'] = function ($thisPerson) {
        $thisPersonId = $thisPerson['id'];

        foreach ($GLOBALS['featuringPeople'] as $featuringPerson) :

            if ($featuringPerson == $thisPersonId) :
                // no  need to search more
                unset($GLOBALS['featuringPeople'][array_search($thisPersonId, $GLOBALS['featuringPeople'])]);

                $thisPersonName = $thisPerson['name'];
                $thisPersonDp = $thisPerson['dp'];
                $GLOBALS['mensionedPeople'][$thisPersonId] = array(
                    'name' => $thisPersonName,
                    'dp' => $thisPersonDp

                );



            endif;
        endforeach;
    };
    runsfs($mensioned);
    $output['mensioned'] = $GLOBALS['mensionedPeople'];

    $output['status'] = 'success';

    $outText = json_encode($output);
    echo $outText;
    exit();
} else if ($unfavoriteFunction) {
    $photoId_ = $_POST['photoid'] ?? false;

    if ($photoId_ && strlen($photoId_) < 100) {
        define("photoId_", $photoId_);

        $unfavoriteFuncDB["table"] = $httpRoot . '/photo.manager/db/favourites.sfs';
        $unfavoriteFuncDB["delete"] = true;

        $unfavoriteFuncDB["if"] = function ($thisFavedPhoto) {
            return $thisFavedPhoto["photoid"] == photoId_;
        };

        runsfs($unfavoriteFuncDB);

        echo '{"status":"success"}';
        exit();
    }

    echo '{"status":"error"}';
    exit();
} else if ($favoriteFunction) // have to check whether sent file exists or not before adding to favourites.sfs
{
    $photoId_ = $_POST['photoid'] ?? false;
    $postId_  =  $_POST['postid'] ?? false;


    if ($photoId_ && $postId_ &&  strlen($photoId_) < 100  && strlen($postId_) < 100) {

        // echo $photoId_ . " | " . $postId_;
        define("photoId_", $photoId_);
        define("postId_", $postId_);

        //checking whther specific post and photo exists at server  
        // first find the post => find the  photo in it
        $GLOBALS['foundPhotoAtServer'] = false;
        $findPostDB["table"] = $httpRoot . '/photo.manager/db/posts.sfs';
        $findPostDB["while"] = "specificPostNotFound";
        $GLOBALS['specificPostNotFound'] =  true;
        $findPostDB["function"] = function ($thisPost) {
            if ($thisPost["postid"] == postId_) // post found
            {
                $GLOBALS['specificPostNotFound'] = false;
                $imageIds = json_decode($thisPost['imageids'], true);
                foreach ($imageIds as $singleImg) {
                    if ($singleImg["photoid"] == photoId_) {
                        $GLOBALS['foundPhotoAtServer']  = true; // exact that photo found ************good to go
                    }
                }
            }
        };
        runsfs($findPostDB);

        if ($GLOBALS['foundPhotoAtServer'])//good to go **** can favourite it
        {
            $favoriteFuncDB["table"] = $httpRoot . '/photo.manager/db/favourites.sfs';
            $favoriteFuncDB["add"]["date"] = time();
            $favoriteFuncDB["add"]["photoid"] = photoId_;
            $favoriteFuncDB["add"]["postid"] = postId_;
            runsfs($favoriteFuncDB);

            echo '{"status":"success"}';
            exit();
        } else {
            echo '{"status":"error","reason":"specified photo does not exist at server"}';
            exit();
        }

        exit();
    }

    echo '{"status":"error"}';
    exit();
} else if ($profileSettingsAsked == "true") {
    $profileId = $_POST['profileid'] ?? false;
    if ($profileId) {
        define('profileId', $profileId);
        $GLOBALS['personData'] = [];


        //get the details from people.sfs
        $profileDB['table'] = $httpRoot . '/photo.manager/db/people.sfs';
        $profileDB['while'] = 'notFoundTheGuy';
        $GLOBALS['notFoundTheGuy'] = true;
        $profileDB['function'] = function ($thisPerson) {
            if ($thisPerson['id'] == profileId) {
                $GLOBALS['notFoundTheGuy'] = false;
                $GLOBALS['personData']['name'] = htmlspecialchars($thisPerson['name']);
                $GLOBALS['personData']['othernames'] = htmlspecialchars($thisPerson['othernames']);
                $GLOBALS['personData']['dp'] = $thisPerson['dp'];
            }
        };
        runsfs($profileDB);

        if ($GLOBALS['notFoundTheGuy']) {
            echo '{"status":"error","reason":"profile not found"}';
            exit();
        }

        $output['status'] =  'success';
        $output['profiledata'] = $GLOBALS['personData'];
        $outText = json_encode($output);
        echo  $outText;
        exit();
    }
} else if ($photosForDp == 'true') {
    $profileId = $_POST['profileid'] ?? false;
    $GLOBALS['posts'] = [];
    $allPostsDB['table'] = $httpRoot . '/photo.manager/db/eachperson/' . $profileId . '.sfs';
    $allPostsDB['function'] = function ($post) {
        array_push($GLOBALS['posts'], $post['postid']);
    };
    // $allPostsDB['reversed']= true;
    runsfs($allPostsDB);
    $GLOBALS['posts'] = array_unique($GLOBALS['posts']);


    $GLOBALS['photos'] = [];
    $allPhotosDB['table'] = $httpRoot . '/photo.manager/db/posts.sfs';
    $allPhotosDB['while'] = 'posts';
    $allPhotosDB['reversed']  = true;
    $allPhotosDB['function'] = function ($post) {
        $postId = $post['postid'];
        foreach ($GLOBALS['posts'] as $postIdRequired) :
            if ($postIdRequired == $postId) :
                array_splice($GLOBALS['posts'], array_search($postIdRequired, $GLOBALS['posts']), 1);//removed found one

                $photos = json_decode($post['imageids'], true);
                foreach ($photos as $photo) :
                    array_push($GLOBALS['photos'], $photo['photoid']);


                endforeach;
            endif;

        endforeach;
    };
    runsfs($allPhotosDB);



    $output["status"] = "success";
    $output['photolist'] = $GLOBALS['photos'];
    $outText = json_encode($output);
    echo $outText;
    exit();
} else if ($dpcandidatesforupdatedp == 'true') {
    //y and x rae not in real scale ...  re-evaluate after getting original file dimensions
    $x = $_POST["x"] ?? false;
    $y = $_POST["y"] ?? false;
    // $src = $_POST["p"] ?? false;
    $photoId = $_POST['photoid'] ?? false;
    $previewHeight = $_POST['h'] ?? false;
    $previewWidth = $_POST['w'] ?? false;
    $proccessHighlights = [];

    //echo  $x,$y,$photoId,$previewWidth,$previewHeight;



    if ($x && $y && $photoId && $previewHeight && $previewWidth) {

        array_push($proccessHighlights, 'all required data received');


        if (file_exists($httpRoot . "/photo.manager/" . $photoId . '.jpeg') && is_numeric($x) && is_numeric($y) && is_numeric($previewWidth) && is_numeric($previewHeight)) {
            array_push($proccessHighlights, 'all required data received are valid on see');

            require_once $httpRoot . "/php.api/SimpleImage.php";
            $IMG = new \claviska\SimpleImage();
            $IMG = $IMG->fromFile($httpRoot . "/photo.manager/" . $photoId . '.jpeg');
            $height = $IMG->getHeight();
            $width =  $IMG->getWidth();
            unset($IMG);

            //print_r(array('x' => $x, 'y' => $y));
            // scaling y and x
            $y *= $height / $previewHeight;
            $x *= $width / $previewWidth;
            // print_r(array('x'=>$x,'y'=>$y,'width'=>$width,'height'=>$height,'preH'=>$previewHeight,'pevW'=>$previewWidth));
            // echo $y . ' ' . $x . '<br/>';

            if ($height > $y && $width > $x) {
                array_push($proccessHighlights, 'all required data received and valid in use');
                //find the max square  // radius
                $radius = intval(min($y, $height - $y, $x, $width - $x)) - 1;

                $out = [];
                for ($i = 1; $i < 9; $i++) {
                    $r =  intval($radius / $i);
                    $newFileName = md5(uniqueId(67)) . md5(uniqueId(67));
                    while (file_exists($httpRoot . "/photo.manager/" . $newFileName . ".jpeg")) {
                        $newFileName = md5(uniqueId(67)) . md5(uniqueId(67));
                    }


                    // print_r([$i,$r,$x-$r,$y+$r,$x+$r,$y-$r]);
                    // print_r([intval($x - $r), intval($y + $r), intval($x + $r), intval($y - $r)]);
                    $IMG = new \claviska\SimpleImage();
                    $IMG = $IMG->fromFile($httpRoot . "/photo.manager/" . $photoId . '.jpeg');
                    $IMG->crop(intval($x - $r), intval($y + $r), intval($x + $r), intval($y - $r))->resize(200, 200)->brighten(20)->toFile($httpRoot . "/photo.manager/" . $newFileName . ".jpeg", "image/jpeg");
                    unset($IMG);
                    array_push($out, $newFileName);

                    //adding data about this image to allphotos.sfs
                    $thisPhoto["table"] = $httpRoot . "/photo.manager/db/allphotos.sfs";
                    $thisPhoto["add"]["date"] = time();
                    $thisPhoto["add"]["filename"] = $newFileName . ".jpeg";
                    $thisPhoto["add"]["size"] = "";
                    runsfs($thisPhoto);
                }
                $output["status"] = "success";
                $output["urls"] = $out;
                $outText = json_encode($output);
                echo $outText;
                exit();
            }
        }
    }
    $output["status"] = "error";
    $output["highlights"] =  $proccessHighlights;
    $out = json_encode($output);
    echo $out;
    exit();
} else if ($updateDp == 'true') {
    $profileId = $_POST['profileid'] ?? false;
    $photoId = $_POST['dpphotoid'] ?? false;
    $GLOBALS['dpNotUpdated'] = true;
    if ($profileId && $photoId) {
        //checking for given photo url is valif
        $photoPath = $httpRoot . '/photo.manager/' . $photoId . '.jpeg';
        define('profileId', $profileId);
        if (file_exists($photoPath)) {
            $peopleDB['table'] = $httpRoot . '/photo.manager/db/people.sfs';
            $peopleDB['change']['dp'] = $photoId;
            // $peopleDB['while'] = 'dpNotUpdated';//variable defined at begining of this proccess
            $peopleDB['if'] = function ($person) {
                //echo $person['id'], profileId;
                if ($person['id'] == profileId) {

                    $GLOBALS['dpNotUpdated']  =  false;
                    return true;
                }
                return false;
            };
            runsfs($peopleDB);
        }
    }
    if (!$GLOBALS['dpNotUpdated'])//is dp udated
    {
        echo '{"status":"success"}';
        exit();
    }
    echo '{"status":"error"}';
    exit();
} else if ($updateProfileSettings == 'true')//update profile settings (name and othername change)
{
    $profileId = $_POST['profileid'] ?? false;
    define('profileId', $profileId);
    $newName = $_POST['newname'] ?? false;
    $newOtherNames = $_POST['newothernames'] ?? false;
    $GLOBALS['profileSettingUpdated'] = false;
    if ($profileId && $newName && $newOtherNames)//all three mus be sent
    {
        if (isValidNameToSave($newName) && isValidOtherNamesToSave($newOtherNames))//both shud be valid
        {
            //change in people.sfs
            $peopleDB = [];
            $peopleDB['table'] = $httpRoot . '/photo.manager/db/people.sfs';
            $peopleDB['change']['name'] = $newName;
            $peopleDB['change']['othernames'] = $newOtherNames;
            $peopleDB['if'] = function ($thisPerson) {
                if ($thisPerson['id'] == profileId) {
                    $GLOBALS['profileSettingUpdated']  = true;
                    return true;
                }
                return false;
            };
            runsfs($peopleDB);
        }
    }

    $status = $GLOBALS['profileSettingUpdated'] ? 'success' : 'error';
    $out['status'] = $status;
    $outText = json_encode($out);
    echo $outText;
    exit();
} 
else if($latestActivity == "true")
{

    $posts = [];$historyPostsData =[];
    $posts['table'] = $httpRoot . '/photo.manager/db/posts.sfs';
    $posts['reversed'] = true;
    $posts['while'] = 'postCountForHistory';$GLOBALS['postCountForHistory'] = 4;
    $posts['function'] = function($post){
        countsfs('postCountForHistory');
        //date?postid?featuring?imageids?discription?about
        $postDate = $post['date'];
        $postId = $post['postid'];
        $postFeat =$post['featuring'];
        $postImgIds = $post['imageids'];
        $postDisc = $post['discription'];
        $postAbout = $post['about'];

        print_r($post);


        

    };
    runsfs($posts);
}


else // normal front page ---language shud be considered
{
    define('basepath', "http://www.realfeed.com/");

    if($pageLanguage == 'english')
    {
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="<?= basepath; ?>styles/photomanager.css?<?= rand(); ?>" />
        <script src="<?= basepath; ?>js/jQuery.js?<?= rand(); ?>"></script>
        <script src="<?= basepath; ?>js/photomanager.js?<?= rand(); ?>"></script>
        <script src="<?= basepath; ?>js/photo.manager.tracking.js?<?= rand(); ?>"></script>
        
        



        <title>Photo Manager 1.0 from RealFeed</title>
    </head>

    <body id="pageBody">
        <div id="topbar">
            <div id="logo" onclick="window.location='http:\/\/www.realfeed.com\/photo.manager.php?ref=logoclick'">Photo Manager 1.0</div>
            <div id="rightLanguageBox">
                <select id="languageSettinSelection" onchange="languageSet();">
                    <option value="english" selected="true">English</option>
                    <option value="sinhala">සිංහල</option>
                </select>
            </div>
        </div>
        <div id="popUpPortal"></div>
        <div id="imageCroperPortal"></div>
        <div id="dragDropBoxPortal"></div>
        <div id="dupplicateDitectedPortal"></div>
        <div id="backgroundDimPortal"></div>
        <div id="toastPortal"></div>
        <div id="dataTitlePortal"></div>
        <div id="rightclickOptionPopBoxPortal"></div>
       
        <div id="rightBox">
            <div id="uploadHead">Upload New Photos</div>
            <div id="uploaderForm" onsubmit="return false;" action="?jsfail=true" target="" method="post" enctype="multipart/form-data">
                <div id="uploader">

                    <div id="uploaders">
                        <div id="uploadersContainer">
                            <input type="hidden" name="noOfFiles" id="noOfFiles" value="1" />
                            <input type="file" name="file1" id="file1" class="fileUploaderInput" />

                        </div>
                        <div id="addMorePhoto">+</div>
                        <div id="addFeaturing">
                            <input type="text" id="addFeat" onblur="{if($('#addFeat').val()!=''){return false;}$('#suggestions').html('');}" placeholder="Add Participants" autocomplete="off" />
                            <div id="suggestions">


                            </div>



                        </div>
                        <div id="addPhotoDiscriptionHolder">
                            <textarea id="discription" placeholder="Short discription #tags or key words"></textarea><?php
                                                                                                                        //show a button to paste the previous discription
                                                                                                                        $placehold = $_GET['placehold'] ?? false;
                                                                                                                        if ($placehold && strlen($placehold) < 500) {
                                                                                                                            echo '<div data-title="<innerHTML>#prevoiusDiscriptionStorage" id="pastePreviousDiscriptionButton">Paste Previous Discription</div><div id="prevoiusDiscriptionStorage" style="display:none;">' . htmlspecialchars($placehold) . '</div>';
                                                                                                                        }

                                                                                                                        ?>
                        </div>





                    </div>
                    <div id="discriptionBox">



                    </div>







                </div>
                <div id="uploadedPreviewBox">
                    <span id="uploadedPreviewHeader">Preview</span>
                    <div id="addPreviews"></div>


                </div>
                <div id="participantsPreviewBox">
                    <span id="participantsPreviewHeader">Participants:</span>
                    <div id="addParticipants"></div>


                </div>
                <div id="submitBox"> <button onclick="saveAllToPhotos();" value="Save" id="submitButton">Save</button></div>
            </div>
            <!---history -->
            <div id="latestUploadHead">Latest Uploads</div>
                <div id="latestUploadPortal">
                    
                </div>

        </div>
        <div id="leftBox">
            <div id="searchBox">
                <div id="searchHead">Search for images in your collection</div>
                <div id="searchContent">

                    <div id="searchBar">
                        <input type="text" id="searchQuery" onkeyup="proceedSearch(event);" placeholder="Search By Names or Discriptions ..." />
                        <div id="featImgBox"></div>
                    </div>
                    <div id="searchResults">


                    </div>
                </div>




            </div>
            <div id="starredPhotoRandomShowerBox">
                <div id="favoShowerRandHead">From your Favourites</div>
                <div id="favoRandPhotoShowerPortal"></div>


            </div>
        </div>







    </body>

    </html><?php
    }//language =  english over

    if($pageLanguage == 'sinhala')
    {
?>
    <!DOCTYPE html>
    <html lang="sin">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="<?= basepath; ?>styles/photomanager.css?<?= rand(); ?>" />
        <script src="<?= basepath; ?>js/jQuery.js?<?= rand(); ?>"></script>
        <script src="<?= basepath; ?>js/sinhala/photomanager.sinhala.js?<?= rand(); ?>"></script>
        <script src="<?= basepath; ?>js/photo.manager.tracking.js?<?= rand(); ?>"></script>


        <title>Photo Manager 1.0 from RealFeed - ෆොටෝ මැනේජර් 1.0 , රියල්ෆීඩ් වෙතින්</title>
    </head>

    <body id="pageBody">
        <div id="topbar">
            <div id="logo" onclick="window.location='http:\/\/www.realfeed.com\/photo.manager.php?ref=logoclick'">Photo Manager 1.0<div style="font-size: 10px;color: #8081d7;">ෆොටෝ මැනේජර් 1.0, රියල්ෆීඩ් වෙතින්</div></div>
            <div id="rightLanguageBox">
                <select id="languageSettinSelection" onchange="languageSet();">
                    <option value="english">English</option>
                    <option value="sinhala" selected="true">සිංහල</option>
                </select>
            </div>
        </div>
        <div id="popUpPortal"></div>
        <div id="imageCroperPortal"></div>
        <div id="dragDropBoxPortal"></div>
        <div id="dupplicateDitectedPortal"></div>
        <div id="backgroundDimPortal"></div>
        <div id="toastPortal"></div>
        <div id="dataTitlePortal"></div>
        <div id="rightclickOptionPopBoxPortal"></div>
       
        <div id="rightBox">
            <div id="uploadHead">නව ඡායාරූප උඩුගත කරන්න</div>
            <div id="uploaderForm" onsubmit="return false;" action="?jsfail=true" target="" method="post" enctype="multipart/form-data">
                <div id="uploader">

                    <div id="uploaders">
                        <div id="uploadersContainer">
                            <input type="hidden" name="noOfFiles" id="noOfFiles" value="1" />
                            <input type="file" name="file1" id="file1" class="fileUploaderInput" style="color:#ffffff" data-title="ඡායාරූප උඩුගත කිරීම"/>

                        </div>
                        <div id="addMorePhoto">+</div>
                        <div id="addFeaturing">
                            <input type="text" id="addFeat" onblur="{if($('#addFeat').val()!=''){return false;}$('#suggestions').html('');}" placeholder="රූපයේ සිටින අය ඇතුලත් කරන්න" autocomplete="off" />
                            <div id="suggestions">


                            </div>



                        </div>
                        <div id="addPhotoDiscriptionHolder">
                            <textarea id="discription" placeholder="කුඩා හැදින්වීමක් හෝ #ටැග් එකක් හෝ යතුරක් දමන්න"></textarea><?php
                                                                                                                        //show a button to paste the previous discription
                                                                                                                        $placehold = $_GET['placehold'] ?? false;
                                                                                                                        if ($placehold && strlen($placehold) < 500) {
                                                                                                                            echo '<div data-title="<innerHTML>#prevoiusDiscriptionStorage" id="pastePreviousDiscriptionButton">පසුගිය හැදින්වීම ම අලවන්න</div><div id="prevoiusDiscriptionStorage" style="display:none;">' . htmlspecialchars($placehold) . '</div>';
                                                                                                                        }

                                                                                                                        ?>
                        </div>





                    </div>
                    <div id="discriptionBox">



                    </div>







                </div>
                <div id="uploadedPreviewBox">
                    <span id="uploadedPreviewHeader">රූප අයිතම</span>
                    <div id="addPreviews"></div>


                </div>
                <div id="participantsPreviewBox">
                    <span id="participantsPreviewHeader">රූපයේ සිටින අය</span>
                    <div id="addParticipants"></div>


                </div>
                <div id="submitBox"> <button onclick="saveAllToPhotos();" value="Save" id="submitButton" style="left: 438px;">සුරකින්න</button></div>
            </div>
            <!---history -->
            <div id="latestUploadHead">ලගදීම කල උඩුගත කිරීම්</div>
                <div id="latestUploadPortal">
                    
                </div>

        </div>
        <div id="leftBox">
            <div id="searchBox">
                <div id="searchHead">ඔබගේ ඡායාරූප එකතුව තුල සොයන්න</div>
                <div id="searchContent">

                    <div id="searchBar">
                        <input type="text" id="searchQuery" onkeyup="proceedSearch(event);" placeholder="නම් හෝ හැදින්වීම් මගින් සොයන්න ..." />
                        <div id="featImgBox"></div>
                    </div>
                    <div id="searchResults">


                    </div>
                </div>




            </div>
            <div id="starredPhotoRandomShowerBox">
                <div id="favoShowerRandHead">ඔබගේ ප්‍රියතම ඡායාරූප</div>
                <div id="favoRandPhotoShowerPortal"></div>


            </div>
        </div>







    </body>

    </html><?php
    }//language =  sinhala over






        }

            ?>