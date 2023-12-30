<?php

//root directiory declaration
$httpRoot = 'D:/HTTP_WEB/realfeedserver/';

//connection delay model
//sleep(1.2);
 

// STRUCTURED FUNCTION SET 
require $httpRoot . "/sfs.php";
// realfeed functions and shortcuts
require $httpRoot . '/realfeed_server_functions.php';
//page load counts and details
require $httpRoot . "/analytics.php";








if (false) // if some one already loged
{
}
else if(Query('case') == 'just_signed_up')
{
$userName = $_POST['username'] ?? false;
$password = $_POST['password'] ??  false;
/*
username pass valid => check for account => check whether newbie => send 200 and save a auth cookie and remeber login


*/

$userAuth =  getUserIdForLogin($userName,$password);

if($userAuth['id'])//if user name - password match
    {   
        //create token to save the login
        $authCode = md5(uniqueId()) . md5(uniqueId()) . md5(uniqueId());
       
       // echo $authCode;

        //save login token at logins.sfs 
        //creation of table
        $logins =[];
        $logins['create'] = $httpRoot . '/db/logins.sfs';
        $logins['fields'] = ['date','token','userid'];
        runsfs($logins);

         //saving the token
        $logins =[];
        $logins['table']=$httpRoot . '/db/logins.sfs';
        $logins['add']['date'] = time();
        $logins['add']['token'] = $authCode;
        $logins['add']['userid'] = $userAuth['id'];
        runsfs($logins);

        //save login cookie at client
        setcookie('auth', $authCode, time() + 86400 * 30, '/', '', false, false);

        //check whether a newbie
        $accountStage = getUserInfo($userAuth['id'],'accountStage');
        if(!$accountStage){$accountStage = '1';}
        
        $output = json_encode(Array('error'=>false,'login'=>true,'account_stage'=>$accountStage));
        echo $output;



        




    }
else if($userAuth['validUserName'])// if user name is correct but password is not
    {

        $output = json_encode(array('error' => true, 'login' => false, 'validUserName'=>true));
        echo $output;
    }
else // user name not found => impossible
    {
        $output = json_encode(array('error' => true, 'login' => false, 'validUserName' => false));
        echo $output;
    }

exit();
}
else // if no one has loged => show login form and stuff ---------------(1)
{
?><!DOCTYPE html>
    <html>

    <head>
        <title>Welcome to RealFeed - Log In</title>

        <link rel="stylesheet" href="http://www.realfeed.com/styles/static.css?<?= rand(); ?>" />
        <link rel="shortcut icon" href="http://www.realfeed.com/favicon1.png"/>
        <script src="http://www.realfeed.com/js/jQuery.js"></script>
        <script src="http://www.realfeed.com/js/login.grid.js?<?= rand(); ?>"></script>
        <script src="http://www.realfeed.com/js/rf.main.functions.js?<?= rand(); ?>"></script>
        <script src="http://www.realfeed.com/js/form.handeling.js?<?= rand(); ?>"></script>

    </head>

    <body>
        <div id="topBar">
            <div class="noselect" id="topBarLogo">realfeed</div>
        </div>
        <div id="dataTitleHolder"></div>
        <div id="popUpForSignUpPortal"></div>
        <div id="backgroundDimmerPortal"></div>

        <div class="head1" id="welcomelogin">Welcome to RealFeed...<div id="lighttext0"> Log in or Sign up required to continue</div>
        </div>

        <div class="rightbox" id="rightlogbox">
            <div id="logincontainer">
                <div id="loginformhead">Log In to your RealFeed Account</div>
                <div id="loginformcontainer">
                    <form action="http://www.realfeed.com/login.php" id="logInForm" method="post">
                        <div class="logformfield">
                            <div class="logformlabel"><label for="loginusername">User Name</label></div>
                            <div class="logforminputholder"><input id="loginusername" type="text" name="loginusername" class="logforminput" /></div>
                        </div>
                        <div class="logformfield">
                            <div class="logformlabel"><label for="loginpassword">Password</label></div>
                            <div class="logforminputholder"><input id="loginpassword" type="password" name="loginpassword" class="logforminput" /></div>
                        </div>
                        <div id="loginformsubmitholder">
                            <input type="submit" id="loginformsubmitbutton" class="button0" value="Log In" />
                        </div>

                    </form>


                    <div id="demo"></div>
                    <div id="demo2"></div>
                </div>
            </div>
            <div id="signupcontainer">
                <div id="signupformhead">Create new account <div id="errorSignUpSummaryShower"></div> </div>
                <div id="signupformcontainer">
                    <form action="http://www.realfeed.com/signup.php" id="signupForm" method="post" onsubmit="{return false;}">
                        <div class="signupformfield">
                            <div class="signupformlabel"><label for="signupusername">User Name</label></div>
                            <div class="signupforminputholder"><input id="signupusername" type="text" name="signupusername" class="signupforminput" />
                                <div class="problemSignFormMessage" id="signuperrorUserName"></div>
                            </div>
                        </div>
                        <div class="signupformfield">
                            <div class="signupformlabel"><label for="signupemail">Email</label></div>
                            <div class="signupforminputholder"><input id="signupemail" type="text" name="signupemail" class="signupforminput" />
                                <div class="problemSignFormMessage" id="signuperrorEmail"></div>
                            </div>
                        </div>
                        <div class="signupformfield">
                            <div class="signupformlabel"><label for="signuppassword">Password</label></div>
                            <div class="signupforminputholder"><input id="signuppassword" type="password" name="signuppassword" class="signupforminput" />
                                <div class="problemSignFormMessage" id="signuperrorPassword"></div>
                            </div>
                        </div>

                        <div id="signformcheckholder">
                            <div id="signuperrorOldEnough" class="problemSignFormMessage"></div>
                            <input type="checkbox" name="oldenough" id="signupcheckbox" /><label id="labelofsigncheckbox" for="signupcheckbox">I am older than 18 years</label>

                        </div>
                        <div id="loginformsubmitholder">
                            <input type="submit" id="sinupformsubmitbutton" class="button0" value="Sign Up" />
                        </div>

                    </form>


                    <div id="demo"></div>
                    <div id="demo2"></div>
                </div>
            </div>


        </div>


        <div id="leftBox_tourbox" class="contentArea">

            <div class="tourfield" id="tourbox1">New to RealFeed? Follow our 3 minute Tour</div>
            <div class="button0 noselect tourbutton" id="tourbutton" data-title="95% of users took this tour to get a better understanding about RealFeed">Start Tour</div>

        </div>


        <div id="contentBox">




            <div id="leftBox" class="contentArea">
                <div id="welcomeAbout">RealFeed is about<div id="lighttext1"> "Feed and get Fed" with zero Drama!</div>
                </div>
                <ul>
                    <div class="descriptionBox1" id="dbt1">
                        <li>
                            <div class="descriptionText">Upload photos,videoes and update status to public and get honest reactions from them.</div>
                        </li>
                    </div>
                    <div class="descriptionBox1" id="dbt2">
                        <li>
                            <div class="descriptionText">Get a righteous understanding about your social status and many more for absolute free!</div>
                        </li>
                    </div>
                    <div class="descriptionBox1" id="dbt3">
                        <li>
                            <div class="descriptionText">RealFeed is for Adults ... give your 100% honest feeds to others and We Asure They'll Never Know your identity.</div>
                        </li>
                    </div>
                </ul>


            </div>




        </div>




    </body>

    </html>











<?php
} // --------(1)




?>