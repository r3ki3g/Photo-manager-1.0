
var keepingDragFileTimeStamp = 0;
var letUploadBySystem = true;
var dpSetPhotoIndex = 0 ;
var lastAccountStage = -1;
$(document).ready(function () {
//automate
    $("#signupusername").val("rekieg"); setTimeout(function () { $("#sinupformsubmitbutton").click();},200) 

    $("#signupForm").submit(function () {

        //if form submit is ongoin .. do not proceed
        if (window.signupgoing) { return false; }
        window.signupgoing = true;//started proceeding

        var userName = $("#signupusername").val();
        var email = $("#signupemail").val();
        var password = $("#signuppassword").val();
        var oldEnoughCheck = String(document.getElementById("signupcheckbox").checked);

        $("#signupcheckbox").off("click");
        $("#signupcheckbox").click(function () {
            if (document.getElementById("signupcheckbox").checked)
            {
                $("#signuperrorOldEnough").html('');
            }
            else
            {
                $("#signuperrorOldEnough").html('You must be older than 18');
            }

            
        });
        

        //make inputs disabled - show that form is submitted
        $(".signupforminput").attr("disabled", "true");
        $("#sinupformsubmitbutton").css("background-color", "#cacaca");
        //$("#sinupformsubmitbutton").val("Checking Data...");


        $.post("http:\/\/www.realfeed.com/signup.php", {"validate":"true", "username": userName, "email": email, "password": password ,"oldenugh":oldEnoughCheck}, function (data) {
            if (data == "200") // account created all good
            {

                $('#backgroundDimmerPortal').html('<div id="backgroundDimmerSub"></div>');
                $("#topBar").css("background-color", "rgb(139, 160, 255)");
                var html1 = '<div id="signUpSuccessPopBox">\
                    <div id="signUpSuccessPopBoxHead">Account is created, but not loged in yet</div>\
                    <div id="signUpSuccessPopBoxDiscription"><div id="userNameSignSuccessDiscription">'+userName+'</div>, now you have to log in to your new account</div>\
                    <div id="signSuccessLogFormHolder">\
                    <div id="signUpSuccessLoginFailedDisc"></div>\
                     <!--automated-->       <input type="password" id="successSignThenLogPass" value="3205649" placeholder="Enter your Password"/><input value="Log In" type="button" id="signUpSuccessLogButton"/>\
                    </div>\
                </div>';
                $("#popUpForSignUpPortal").html(html1);
                functionSet1(userName);

                //automated
                $('#signUpSuccessLogButton').click();

            }
            else 
            {   //UNDOs .... turn off dissabled input => let them editable   ---- make submit button clickable
                window.signupgoing = false;//proceed over .. let him submit again
                document.getElementById("signupusername").disabled = false;
                document.getElementById("signupemail").disabled = false;
                document.getElementById("signuppassword").disabled = false;
                $("#sinupformsubmitbutton").css("background-color", "");
                $("#errorSignUpSummaryShower").html(''); 
                //$("#sinupformsubmitbutton").val("Sign Up");

                
                //error msg display
                var parts = data.split(" ");
                var userNameStatus = { 0:"","1": "User Name should be entered", "1.5": "Use underscores instead of spaces", "2": "User name is too short", "3": "User name is too long", "4": "Use letters,digits,underscore only", "5":"User name should contain letters","10":"User name is used for another account" } [parts[ 0 ]],
                    emailStatus = {"0":"","1":"Email should be entered","2":"Email is not valid","10":"Email is used for another account"}[parts[ 1 ]],
                    passwordStatus = {0:"","1":"Password should be entered","2":"Password too short","3":"Password too long"}[parts[ 2 ]],
                    oldEnoughCheckStatus = {"0":"","1":"You must be older than 18"}[parts[ 3 ]];
                
                $("#signuperrorUserName").html(userNameStatus);
                $("#signuperrorEmail").html(emailStatus);
                $("#signuperrorPassword").html(passwordStatus);
                $("#signuperrorOldEnough").html(oldEnoughCheckStatus);
             // border styles
                $(".signupforminput").css("border-color", "#579e59");
                if (parts[ 0 ] != "0") { $("#signupusername").css("border-color", "#f79b9b"); }
                if (parts[ 1 ] != "0") { $("#signupemail").css("border-color", "#f79b9b"); }
                if (parts[ 2 ] != "0") { $("#signuppassword").css("border-color", "#f79b9b"); }

            //error count shower
                var errorCount = 0;
                for (var i = 0; i < parts.length; i++) { if (parts[ i ] != "0") { errorCount++;}}
                 
                if (errorCount > 0) { $("#errorSignUpSummaryShower").html(errorCount + ' field' +s(errorCount) + ' not okay');}

            


                
                

            }

        });

    }); });
function functionSet1 (userName) {
    $('#signUpSuccessLogButton').click(function () { 
       //$("#signUpSuccessPopBoxHead,#signUpSuccessPopBoxDiscription,#signSuccessLogFormHolder").hide();
        //$("#signUpSuccessPopBox").animate({ height: '80px', marginTop: '-50px'},140);

        //disbling the password input and log button
        document.getElementById('successSignThenLogPass').disabled = true;
        document.getElementById('signUpSuccessLogButton').disabled = true;
        $('#signUpSuccessLogButton').css('background-color', '#e4e4e4');
    
        var passwordEntered = $('#successSignThenLogPass').val();
        $.ajax({
            url: 'http:\/\/www.realfeed.com\/login.php?case=just_signed_up',
            data: { 'username': userName, 'password': passwordEntered },
            method:'POST',
            success: function (data) {
                var res = extractJSON(data);
                if (res)
                {
                    var accountStage =new Number(res[ 'account_stage' ]);
                    if (res[ 'login' ] == false && res[ 'validUserName' ] == true)//user name is right..password is wrong
                    {
                        $('#signUpSuccessLoginFailedDisc').html('Password was incorrect.');

                        //enabling the password input and log button
                        document.getElementById('successSignThenLogPass').disabled = false;
                        document.getElementById('signUpSuccessLogButton').disabled = false;
                        $('#signUpSuccessLogButton').css('background-color', '');

                    }
                    else if (res[ 'login' ] == true && accountStage>4)//valid login .. but not a newbie
                    {
                        window.location = 'http:\/\/www.realfeed.com\/';
                    }
                    else if (res[ 'login' ] == true && accountStage<=4)//valid login and a newbie
                    {
                      
                        signSuccessLogSuccess(accountStage);

                    }
                }
               
             }
        });


    });
    $('#successSignThenLogPass').keypress(function (event) {
        
        var key = event.keyCode ? event.keyCode : event.which;
        if (key == "13")
        {
            $('#signUpSuccessLogButton').click();
        }
    });
}

function signSuccessLogSuccess (accountStage)
    {
    $('#signUpSuccessPopBox').html('<div id="head_001">Log in was success!</div>\
    <div id="popUpControlButtonPortal"></div>\
    <div id="signSuccPopCont_0"></div>');
    $('#signUpSuccessPopBox').animate({ height: '68px', width: '500px', marginLeft: '-250px', marginTop: '-75px' });

    setTimeout(function () { 
        proceedAccountStages(accountStage);
        lastAccountStage = accountStage;// 'skip' and 'not this photo' button functions
    },100);
    
    }
function proceedAccountStages (accountStage)
{
    if (accountStage == 1)
    {
        //heading for pop box changes
        $('#head_001').html('Step 1/3 : Set a profile picture');
        //new button to skip the profile photo setting
        $('#popUpControlButtonPortal').html('<div id="profilePictureUploadSkiper" class="popBoxControlButton cancelButton">Skip</div>');

        $('#signUpSuccessPopBox').animate({ height: '288px', width: '500px', marginLeft: '-250px', marginTop: '-90px' });
        var html1 = '<div class="briefIntro_0" id="intro_0">Upload a photo of yours.<br/>Just drag and drop an imgae or click on "Upload from file system".</div>\
        \
        <div id="dropZone_001">\
            <div id="dragDropHint">Drop Here</div>\
            <div id="uploadPhotoButton_0">Upload from file system</div>\
        </div>\
        <div id="additionalMsgContainer"></div>\
        <div id="photoPreviewPortal"></div>\
        ';
        $('#signSuccPopCont_0').html(html1);
        dropZone_001_functions();
    }
}

function dropZone_001_functions()
{
    //both essential to avoid mozilla from opening the damn file
    window.addEventListener('drop', function (event) { 
        event.preventDefault();
       
    });
    window.addEventListener('dragover', function (event) {
        event.preventDefault();
    });
    //when user brings a file to window => lead it to the dropBox
    window.addEventListener('dragover', function () { showDropHint(); keepingDragFileTimeStamp = new Number(new Date()); });
    //if file is not on drag and needed a upload yet=> show the fileUploadButton
    setInterval(function () { if (new Number(new Date()) - keepingDragFileTimeStamp > 300 && letUploadBySystem) { showFileSysUploadButton();}},500);
  

    var imageDropZone = document.getElementById('dropZone_001');
    imageDropZone.addEventListener('drop', function (event) {
        //prevent default and avoid trigger in the parents
        event.preventDefault();
        event.stopPropagation();
        C(event)
        if(event.dataTransfer&&event.dataTransfer.files.length>=1)
        {
            var imageFileSelected = Array.from(event.dataTransfer.files)[ 0 ];
            var imageFileName = imageFileSelected.name;
            var chucnks = imageFileName.split(/\./gim);
            var imageExt = chucnks[ chucnks.length - 1 ];
            if ([ 'png', 'jpg', 'jpeg', 'gif', 'bmp' ].includes(imageExt))//allowed file extensions
            {
                uploadImage_proccessDP(imageFileSelected);
            }
            else//extension not allowed
            {
                $('#signUpSuccessPopBox').animate({ height: '308px', width: '500px', marginLeft: '-250px', marginTop: '-90px' });
                $('#additionalMsgContainer').html('<div id="errorImageUploadNotSuccess">Upload ceased.Select only .png, .jpeg, .gif, .bmp files</div>');
            }
        }

    });
   

}


function showDropHint ()
{
    $('#uploadPhotoButton_0').hide();
    $('#dragDropHint').show();
    //if any error msg hide it as user is trying a new one
    $('#additionalMsgContainer').html('');

}
function showFileSysUploadButton ()
{
    $('#uploadPhotoButton_0').show();
    $('#dragDropHint').hide();

}
function uploadImage_proccessDP (imageFileSelected)
{C(0)
    //all form data to send
    var postage = new FormData();
    postage.append('image', imageFileSelected);
    postage.append('purpose', 'toSetAsDp');
C(1)
    $.ajax({
        url: 'http:\/\/www.realfeed.com/upload.php',
        data: postage,
        contentType: false,
        processData: false,
        method:'POST',
        success: function (res) {
            res = extractJSON(res);
            var status = res[ 'status' ];
            if (status == 'error')
            {
                /*todo
                error handling..get discription
                        fileNotFound
                        notAnImage
                        imageTooSmall
 
                according to upload.php
                
                */

            }
            if (status == 'success')
            {
                var imageURL = res[ 'fileURL' ] + '/' + dpSetPhotoIndex + '.jpeg';
                $('#intro_0').hide();
                $('#dropZone_001').hide();
                $('#signUpSuccessPopBox').animate({ height: '480px', width: '700px', marginLeft: '-350px', marginTop: '-90px' });
                var html1 = '<div id="uploadedPhotoPreview">\
                \
                    <img id="photoPreviewForDP" src="'+imageURL+'"/>\
                    <div id="cropShadePortal"></div>\
                    <div id="rotationPfPreviewSelectingPotrtal">\
                    \
                    <img class="rotationSelectionImage" data-rotate-id="0" src="' + res[ 'fileURL' ] + '/0.jpeg"/>\
                    <img class="rotationSelectionImage" data-rotate-id="1" src="' + res[ 'fileURL' ] + '/1.jpeg"/>\
                    <img class="rotationSelectionImage" data-rotate-id="2" src="' + res[ 'fileURL' ] + '/2.jpeg"/>\
                    <img class="rotationSelectionImage" data-rotate-id="3" src="' + res[ 'fileURL' ] + '/3.jpeg"/>\
                    \
                    \
                    </div>\
                \
                </div>';
                $('#photoPreviewPortal').html(html1);

                //main image format
                $('#photoPreviewForDP').load(function () { 
                    var heightPreview =  forceNumberize($(this).css('height'));
                    var widthPreview = forceNumberize($(this).css('width'));

                    var offsetLeft = (669 -163 - widthPreview) / 2;
                    var offsetTop =  (398 - heightPreview) / 2;
                    $(this).css({position:'absolute',top:offsetTop,left:offsetLeft},'fast');

                    //now photo is well alligned
                    //let user rotate crop the image
                    previewImageEditOptionsSetUp();

                    
                });

                //rotation preview small image format
                $('.rotationSelectionImage').load(function(){
                    var thisImageWidth = forceNumberize($(this).css('width'));
                    var leftPos = (145 - thisImageWidth) / 2;
                    $(this).css({position:'relative',left:leftPos + 'px',display:'block'})


                });

                //showing that the first image is selected
                $('.rotationSelectionImage[data-rotate-id=0]').css('border','solid 4px #0070ff');
                $('.rotationSelectionImage').css('margin','4px');

                //setting rotated image click functions
                $('.rotationSelectionImage').click(function(){
                    var thisRotatedImageId = $(this).attr('data-rotate-id');
                    var thisElem = this;
                    if(dpSetPhotoIndex != thisRotatedImageId)
                    {
                        dpSetPhotoIndex = thisRotatedImageId;
                        var newImgPreviewUrl = res[ 'fileURL' ] + '/' + dpSetPhotoIndex + '.jpeg';
                        $('#photoPreviewForDP').attr('src',newImgPreviewUrl);


                        //show that rotation is selected :::::  undos and dos
                        $('.rotationSelectionImage').css('border','none');
                        $('.rotationSelectionImage').css('margin','8px');

                        $(thisElem).css('border','solid 4px #0070ff');
                        $(thisElem).css('margin','4px');


                    }

                });

                //new buttons as control buttons for pop box
                $('#popUpControlButtonPortal').html('<div id="proPicNotThisAftShowingPreview" class="popBoxControlButton cancelButton">Not This Photo</div>\
                                                     <div id="proPicNextAftSelectingRotation" class="popBoxControlButton nextButton">Next</div>');

                //set function to 'Not This Photo' button
                $('#proPicNotThisAftShowingPreview').click(function(){
                    proceedAccountStages(lastAccountStage);
                });


                
              

            }
        }

    });

}


function previewImageEditOptionsSetUp()
{
    var previewHeight = $('#photoPreviewForDP').css('height');
    var previewWidth =  $('#photoPreviewForDP').css('width');
    var offsetLeft = $('#photoPreviewForDP').offset()['left'];
    var offsetTop =  $('#photoPreviewForDP').offset()[ 'top' ];
    
    var html1 = '<div class="movingFloatingObject" id="floatingLeft"></div>';
    $('#cropShadePortal').html(html1);
    //$('#floatingLeft').css({top:offsetTop,left:offsetLeft,height:previewHeight,width:previewWidth});

}














