  <?php
//connection delay model
//sleep(1.2);

//root directiory declaration
$httpRoot = 'D:/HTTP_WEB/realfeedserver/';
//define('httpRoot',$httpRoot);

// STRUCTURED FUNCTION SET 
require $httpRoot . "/sfs.php";
// realfeed functions and shortcuts
require $httpRoot . 'realfeed_server_functions.php';
//page load counts and details
require $httpRoot . "/analytics.php";

//
$ajaxRequest = ($_POST['requestType']??'')=='ajax';
$logedUserId = getLogedUserId();



if(!$logedUserId)// no one has logged => can not upload anything
{

    //if ajax request
    if($ajaxRequest)
    {
        $out = json_encode(Array('error'=>true,'discription'=>'auth_failed'));
        echo $out;
    }
    //if normal response
    else
    {
        //redirect to login page
        header('location:http://www.realfeed.com/login.php?from=upload');
    }

    exit();

}

//authentication good!

$purpose = $_POST['purpose'] ?? false;

//file upload proccess is different by purpose
if($purpose == 'toSetAsDp')
    {
        $fileSent = $_FILES['image']??false;
        if(!$fileSent){deadError('fileNotRecieved');}//no file sent

        $filePath = $fileSent['tmp_name'];
        $fileOriginalName = $fileSent['name'];
        $fileImageInfo = getimagesize($filePath);     

        if(!$fileImageInfo){deadError('notAnImage');} //not an image file
        //now sure we are talking about an image file

        $imageWidth = $fileImageInfo[0];
        $imageHeight = $fileImageInfo[1];
        if($imageHeight < 150 || $imageWidth < 150){deadError('imageTooSmall');}// dimensions not enaf


        $folderNameForImage = randomFileName('', httpRoot . 'files', 4);
        mkdir(httpRoot . '/files' .'/'. $folderNameForImage);
        //image proccess
        require $httpRoot . "/php.api/SimpleImage.php";
        $IMG = new \claviska\SimpleImage();
        $IMG = $IMG->fromFile($filePath);
        $exifData = $IMG->getOrientation();
        
        //save original
        $IMG->toFile(httpRoot . '/files/' . $folderNameForImage . '/' . 'original.jpeg' ,'image/jpeg',100);
        //rotate 0 and best fit
        $IMG->rotate(0)->bestFit(669,398)->toFile(httpRoot . '/files/' . $folderNameForImage . '/' . '0.jpeg', 'image/jpeg', 100);
        //rotate 90 and best fit
        $IMG->rotate(90)->bestFit(669, 398)->toFile(httpRoot . '/files/' . $folderNameForImage . '/' . '1.jpeg', 'image/jpeg', 100);
        //rotate 180 and best fit
        $IMG->rotate(-180)->bestFit(669, 398)->toFile(httpRoot . '/files/' . $folderNameForImage . '/' . '2.jpeg', 'image/jpeg', 100);
        //rotate 270 and best fit
        $IMG->rotate(-90)->bestFit(669, 398)->toFile(httpRoot . '/files/' . $folderNameForImage . '/' . '3.jpeg', 'image/jpeg', 100);


        //sending the success note and file uRL
       $fileURL = 'http://www.realfeed.com/files/' . $folderNameForImage;

       //save record about file upload [1] => overall files uploaded [2] => save in activities of user*******************************
       $fileUploadsDB = [];
       $fileUploadsDB['create'] = httpRoot . 'db/fileUploads.sfs';
       $fileUploadsDB['fields'] = ['date','filepath','userId','status'];
       runsfs($fileUploadsDB);

       $fileUploadsDB = [];
       $fileUploadsDB['table'] = httpRoot . 'db/fileUploads.sfs';
       $fileUploadsDB['add']['date'] = time();
       $fileUploadsDB['add']['filepath'] = $folderNameForImage;
       $fileUploadDb['add']['userId'] = $logedUserId;
       $fileUploadsDB['add']['status'] = 'forDp';
       runsfs($fileUploadsDB);

       //to user's activities
       $userActi =[];
       $userActi['table'] = httpRoot . 'db/userdata/' . $logedUserId. '/useracti.sfs';
       $userActi['add']['date'] =  time();
       $userActi['add']['type'] = 'photoUpload';
       $userActi['add']['ref'] = $folderNameForImage;
       runsfs($userActi);


        //**************************************************************************************************************************


       $out = [];
       $out['status'] ='success';
       $out['fileURL'] = $fileURL;
       $out['exif'] =$exifData;
       $output = json_encode($out);
       echo $output;exit();
        

       
    }

else
    {
    //no purpose found => error
    }





?>