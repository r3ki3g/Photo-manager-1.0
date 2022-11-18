<?php   
//sleep(1);
//root directiory declaration
$httpRoot = 'D:/HTTP_WEB/realfeedserver/';

// STRUCTURED FUNCTION SET 
require $httpRoot . 'sfs.php';


//header stuff  -- no caching
header('cache-control:no-store,no-cache,must-revalidate');
header('pragma:no-cache');

$purpose_toSetWatched = $_POST['watched']??false;
if($purpose_toSetWatched == 'true')
{

    //assigning expected post data to vars
    $photoId = $_POST['photoid']??false;
    $scrolledBack = $_POST['scrolled_back']??false;
    $watchedDura = $_POST['watched_duration']??false;

    //set these in to globals:to use in sfs
    $GLOBALS['photoId'] = $photoId;
    $GLOBALS['scrolledBack'] = $scrolledBack;
    $GLOBALS['watchedDura'] = $watchedDura;
    echo $GLOBALS['photoId']. ' : ' . $GLOBALS['scrolledBack'] . ' : ' . $GLOBALS['watchedDura'] . "\n";


    //check all required data is sent
    //if not stop
    if (!($photoId != false && $scrolledBack !== false && $watchedDura != false)) {return false;exit();}

    //check whether given photoId exists
    //if not send error
    if(!file_exists($httpRoot . 'photo.manager/' . $photoId . '.jpeg')){echo 'PHOTO NOT FOUND';exit();}

    //create the neew table(just once)
    $watchTimeDataTable['create'] = $httpRoot . 'photo.manager/db/watchedtime.sfs';
    $watchTimeDataTable['fields'] = ['photoid','watch_time','attention','scrolled_backs','last_watched_time','last_scrolled_back_time'];
    runsfs($watchTimeDataTable);

//MODIFICATION ::: not adding a new photo to data base
    //modify the old set data about photo...if this is first time new data will added later
        //required global vars to *change
        $GLOBALS['new_watch_time']='';
        $GLOBALS['new_attention']='';
        $GLOBALS['new_scrolled_backs']='';
        $GLOBALS['new_last_watched_time']='';
        $GLOBALS['new_last_scrolled_back_time']='';

    $watchTimeDataTable =[];
    $watchTimeDataTable['table'] = $httpRoot . 'photo.manager/db/watchedtime.sfs';
    $watchTimeDataTable['*while'] = 'stillNotFoundPhoto';$GLOBALS['stillNotFoundPhoto'] =1;
    $watchTimeDataTable['if'] = function($photo)
        {
            if($photo['photoid'] == $GLOBALS['photoId'])
            {   echo "modify \n";
                $GLOBALS['stillNotFoundPhoto'] = 0;//photo found no reason to seek more
                $GLOBALS['new_watch_time'] = floatval($photo['watch_time']) + floatval($GLOBALS['watchedDura']);
                $GLOBALS['new_last_watched_time'] = time();
                $GLOBALS['new_attention'] = floatval($photo['attention'])+floatval($GLOBALS['watchedDura'])/floatval($photo['attention']);//more the attention harder to gain more
                
                //scroll back update is: two cases
                    if ($GLOBALS['scrolledBack']=='true')//if scrolled back is sent
                    {
                        $GLOBALS['new_scrolled_backs'] = $photo['scrolled_backs'] + 1;
                        $GLOBALS['new_last_scrolled_back_time']=time();
                    }
                    else//not scrolled back
                    {
                        $GLOBALS['new_scrolled_backs'] = $photo['scrolled_backs'] ;
                        $GLOBALS['new_last_scrolled_back_time']=$photo['last_scrolled_back_time'];
                    }
            return true;//to tell this is to be changed
            }




        };
    $watchTimeDataTable['*change']['watch_time'] = 'new_watch_time';
    $watchTimeDataTable['*change']['attention'] = 'new_attention';
    $watchTimeDataTable['*change']['scrolled_backs'] = 'new_scrolled_backs';
    $watchTimeDataTable['*change']['last_watched_time'] ='new_last_watched_time';
    $watchTimeDataTable['*change']['last_scrolled_back_time'] = 'new_last_scrolled_back_time';

    runsfs($watchTimeDataTable);

//ADDING A NEW PHOTO : if phot in not in the db... that photo s first time!
    if ($GLOBALS['stillNotFoundPhoto'])//photo is not modified in above stage
    {  
        echo "add \n";
        $watchTimeDataTable=[];
        $watchTimeDataTable['table'] = $httpRoot . 'photo.manager/db/watchedtime.sfs';
        $watchTimeDataTable['add'] = Array( 'photoid'=>$photoId,
                                            'watch_time'=>$watchedDura,
                                            'attention'=>floatval($watchedDura)/0.9,
                                            'scrolled_backs'=>$GLOBALS['scrolledBack']=='true'?1:0,
                                            'last_watched_time'=>time(),
                                            'last_scrolled_back_time'=>0
                                          );
        runsfs($watchTimeDataTable);

    }

    echo "done\n";
    exit();
}



?>