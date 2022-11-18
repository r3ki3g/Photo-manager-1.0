<?php
//root directiory declaration
$httpRoot = 'D:/HTTP_WEB/realfeedserver/';
define('httpRoot',$httpRoot);

/*
this file just get included or required ... not good to request straight

SUMMARRY

getUserInfo($userId,$name):$name of the property ;if property found value as string; if not found false
setUserInfo($userId,$name,$value)

getUserIdForLogin($userName,$password):raw password
if username found. shearch stops : ['validUserName'] will be true;otherwise false;
if password match as well : ['id'] will be the USER ID;otherwise false;

getLogedUserId() : if someone logged USER-ID otherwise false;

deadError($discription) => sends json status=error and discription = $discription ; exits!

randomFileName($extension,$folderPath,$fileNameLengthLevel = 2) // works for folders as well
*/
function getUserInfo($userId,$name)
{
define('getUserInfoName',$name);

$table['table'] =httpRoot . '/db/userdata/' . $userId . '/userinfo.sfs';
$GLOBALS['getUserInfoOut'] = false;

$table['while'] = 'getUserInfoWhile';$GLOBALS['getUserInfoWhile'] = true;
$table['function'] = function($property)
{
if($property['name'] == getUserInfoName)
{
$GLOBALS['getUserInfoOut'] = $property['value'];
$GLOBALS['getUserInfoWhile'] = false;

}
};
runsfs($table);

return $GLOBALS['getUserInfoOut'];
}
function setUserInfo($userId,$name,$value)
{
define('setUserInfoName',$name);
define('setUserInfoValue',$value);

$GLOBALS['propertyChanged'] = false;

$table['table'] = httpRoot . '/db/userdata/' . $userId . '/userinfo.sfs';
$table['change']['value'] = setUserInfoValue;
$table['if'] = function($property)
{
if($property['name'] == setUserInfoName)
{
$GLOBALS['propertyChanged'] = true;
return true;

}

};
runsfs($table);

//if not changed : i.e this property is a new one
if(!$GLOBALS['propertyChanged'])
{
$table =[];
$table['table'] = httpRoot . '/db/userdata/' . $userId . '/userinfo.sfs';
$table['add']['name'] = setUserInfoName;
$table['add']['value'] = setUserInfoValue;
runsfs($table);
}

}

/*


*/
function getUserIdForLogin($userName,$password)
{
    //slower 
    sleep(1);
    
    define('userNameLogin',$userName);
    define('hashedPasswordLogin',md5($password));

    $GLOBALS['loginMatch'] = [];
    $GLOBALS['loginMatch']['validUserName'] = false;
    $GLOBALS['loginMatch']['id']  = false;



    $auth["table"] =  httpRoot . "/db/users.sfs";
    $auth['while'] = 'notFoundALoginMatch';$GLOBALS['notFoundALoginMatch'] = true;//whether a userName found ... noNeedOfPassword
    $auth['function'] = function($user)
        {
            if($user['username'] == userNameLogin)
            {
                $GLOBALS['loginMatch']['validUserName'] = true;
                $GLOBALS['notFoundALoginMatch']  = false;

                if($user['hashedpassword'] == hashedPasswordLogin)
                {
                    $GLOBALS['loginMatch']['id'] = $user['id'];
                }
            
            }
        };
    runsfs($auth);
    
    return $GLOBALS['loginMatch'];

}

function getLogedUserId()
{
    $loginToken = $_COOKIE['auth'] ?? false;
    define('loginToken',$loginToken);


    if(!$loginToken){return false;}

    $GLOBALS['matchedLogedUser'] =  false;
    $logins = [];
    $logins['table'] = httpRoot . '/db/logins.sfs';
    $logins['reversed'] = true;
    $logins['while'] = 'loginUserNotFound';$GLOBALS['loginUserNotFound'] = true;
    $logins['function'] = function($record)
    {
        if($record['token'] == loginToken)
        {
            $GLOBALS['loginUserNotFound'] = false;
            $GLOBALS['matchedLogedUser'] = $record['userid'];

        }
    };
    runsfs($logins);
    
    return $GLOBALS['matchedLogedUser'];

}

function deadError($discription)
{
    $out = [];
    $out['status'] = 'error';
    $out['discription'] = $discription;
    echo json_encode($out);
    exit();

}

function randomFileName($extension,$folderPath,$fileNameLengthLevel = 2)
{
    while(true)
    {
        $candidateFileName ='';
        for($i=0;$i<$fileNameLengthLevel;$i++)//adding the required ammount of hashed content
        {
            $candidateFileName .= md5(uniqid(30));
        }
        if(!preg_match('/^\./',$extension) && $extension){$extension = '.' . $extension;}//if there is an extension ... careful about dot
        $candidateFileName .= $extension;
        //file name ready => check whether its used already/folders too => if not good to go....
        if(!file_exists($folderPath .'/'. $candidateFileName)){return $candidateFileName;break;}



    }
}


?>