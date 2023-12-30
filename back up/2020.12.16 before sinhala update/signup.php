<?php
//connection delay model
//sleep(1.2);

//root directiory declaration
$httpRoot = 'D:/HTTP_WEB/realfeedserver/';



// STRUCTURED FUNCTION SET 
require $httpRoot . "/sfs.php";
//page load counts and details
require $httpRoot . "/analytics.php";


// form validation
$isValidateRequest = $_POST["validate"] ?? false;
if ($isValidateRequest == "true")
 {
     //shorted
     echo "200";exit();

    $userName_ = $_POST["username"] ?? "";
    $email_ = $_POST["email"] ?? "";
    $password_ = $_POST["password"] ?? "";

    //echo "$userName_ $email_ $password_";

    $invalidUserName = 0 ; // 1:blank 1.5:spaces 2:too short 3:too long 4:invalid charactors  5:no letters 10:name in use
    $invalidEmail = 0;     // 1:empty  2:invalid email 10:email in use
    $invalidPass = 0;  //  1:empty 2: too short 3: too long

    //name check if invalid
    $userName_ = trim($userName_);
    if($userName_ == "")
    {
        $invalidUserName = 1;
    }
    if (!$invalidUserName && preg_match("/\s/",$userName_)) {
        $invalidUserName = 1.5;
    }
    if (!$invalidUserName && strlen($userName_) < 3) 
    {
        $invalidUserName = 2;
    }
    if (!$invalidUserName && strlen($userName_) > 23) {
        $invalidUserName = 3;
    }
    if (!$invalidUserName && preg_match("/[^0-9a-z\_]/i", $userName_)) {
        $invalidUserName = 4;
    }
    if (!$invalidUserName && !preg_match("/[a-z\_]/i", $userName_)) {
        $invalidUserName = 5;
    }

    //password validate
    if($password_ == "")
    {
        $invalidPass = 1;
    }
    if(!$invalidPass && strlen($password_) < 6 )
    {
        $invalidPass = 2;
    }
    if(!$invalidPass && strlen($password_) > 32)
    {
        $invalidPass = 3;
    }

    //email validation
    if(trim($email_) == "")
    {
        $invalidEmail = 1;
    }
    if(!$invalidEmail && (!preg_match("/^[a-z0-9\.\_]{3,}\@[a-z0-9]{3,}\.[a-z0-9]{3,}$/i",$email_) || strlen($email_) > 30))
    {
        $invalidEmail = 2;
    }


    //check whether username or email is on use in any account
    
    //create users.sfs if not exists --------rekieg unwanted part
    $userAccounts["create"] = $httpRoot . "/db/users.sfs";
    $userAccounts["fields"] = ["datecreated","id","username","email","hashedpassword"];
    runsfs($userAccounts);
    


    /*
    only if valid username and email has to be checked for previus use
    howmanyToFind is 1 - one of username or email to check 2 -  both has to be checked 0 - both no need to check
    */
    $GLOBALS["howmanyToFind"] = 0;
    if (!$invalidEmail) {
        $GLOBALS["howmanyToFind"]++;
    }
    if (!$invalidUserName) {
        $GLOBALS["howmanyToFind"]++;
    }

            define("signcheckusername",$userName_);
            define("signcheckemail",$email_);
            define("signcheckpass",$password_);

            $GLOBALS["emailused"] = false;
            $GLOBALS["usernameused"] = false;

            $userAccounts =[];
            $userAccounts["table"] = $httpRoot . "/db/users.sfs";
            $userAccounts["while"] = "howmanyToFind";
            $userAccounts["function"] = function($record)
            {

                            $thisUserName = $record["username"];
                            $thisEmail = $record["email"];
                           
                            if (strtolower($thisUserName) == strtolower(signcheckusername)) {
                                $GLOBALS["usernameused"] = true;
                                $GLOBALS["howmanyToFind"]--;
                                
                               
                            }
                            if ($thisEmail == signcheckemail) {
                                $GLOBALS["emailused"] = true;
                                $GLOBALS["howmanyToFind"]--;
                               
                            }


            };
            runsfs($userAccounts);


    $outEmailStatus = $invalidEmail;
    if ($GLOBALS["emailused"]) {
        $outEmailStatus = 10;
    }
    $outUsernameStatus = $invalidUserName;
    if ($GLOBALS["usernameused"]) {
        $outUsernameStatus = 10;
    }
    $outPasswordStatus = $invalidPass;


    //Old enough check box check
    $oldEnough = $_POST["oldenugh"] ?? "false";
    $outOldEnoughStatus = "1";
    if($oldEnough == "true")
    {
        $outOldEnoughStatus = "0";
    }
    


    $finalOut =  $outUsernameStatus . " " . $outEmailStatus . " " . $outPasswordStatus . " " . $outOldEnoughStatus;
    if($finalOut != "0 0 0 0")
      {
          echo $finalOut;
          exit();
      }
    if($finalOut == "0 0 0 0") //all valid form data => add as new user 
      { 
          $userCode = uniqueId();//user code for entire life

        /*
        * add to main users.sfs => date created,user code(id) , username , email , hashedpassword
        * create a private folder for user @ userdata/ 
            * userinfo table (property table)
           
            * notification table
            * activity table            
            * contacts table
            * masks folder and mask table


        */

        //ADD USER DETAILS IN TO MAIN USERS.SFS
          $newUser["table"] =  $httpRoot . "/db/users.sfs";
          $newUser["add"]["datecreated"] = time();
          $newUser["add"]["id"] = $userCode;
          $newUser["add"]["username"] = signcheckusername;
          $newUser["add"]["email"] = signcheckemail;
          $newUser["add"]["hashedpassword"] = md5(signcheckpass);
          runsfs($newUser);
          

        //making private folder for this new user
        $privateFolderPath = $httpRoot . "/db/userdata/" . $userCode;
        mkdir($privateFolderPath);

            //INSIDE THAT FOLDER

            //user info table
            $userInfo["create"] = $privateFolderPath ."/userinfo.sfs";
            $userInfo["fields"] = ["name","value"];
            runsfs($userInfo);

        //user notification table
        $userNoti["create"] = $privateFolderPath . "/usernoti.sfs";
        $userNoti["fields"] = ["date", "type", "from", "ref", "status", "seen", "about"];
        runsfs($userNoti);

        //user activity table
        $userActi["create"] = $privateFolderPath . "/useracti.sfs";
        $userActi["fields"] = ["date", "type", "to", "ref", "status", "seen", "about"];
        runsfs($userActi);

        //user contacts table
        $userCont["create"] = $privateFolderPath . "/usercont.sfs";
        $userCont["fields"] = ["date", "type", "with", "ref", "status", "pos", "about"];
        runsfs($userCont);

        //user mask table and mask folder
        $userMask["create"] = $privateFolderPath . "/usermask.sfs";
        $userMask["fields"] = ["date", "type", "name", "ref", "hashedpassword", "conn", "about"];
        runsfs($userMask);

        //create masks folder to keep all each mask tables
        mkdir($privateFolderPath . "/masks");




            









        echo "200";//feed back to browser that account is created succesfully
      }











 }
else
{   //if its is not about a sign up form submit then go to login page
    header("location:http://www.realfeed.com/login.php?rfid=0002");
    exit();


}

?>