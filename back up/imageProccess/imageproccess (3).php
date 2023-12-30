<?php
//connection delay model
//sleep(1.2);

//root directiory declaration
$httpRoot = 'D:/HTTP_WEB/realfeedserver/';
define('httpRoot',$httpRoot);

// STRUCTURED FUNCTION SET 
require $httpRoot . "/sfs.php";



//now its a good file
require_once $httpRoot . "/php.api/SimpleImage.php";

//shortcut function to show a image in html
function showImg($fileName)
{
    echo '<div class="imgBox"><div class="imageName">'.  $fileName . '</div><img src="http://www.realfeed.com/imgPro/'.$fileName.'"/></div>';
}
?><!DOCTYPE html>
<html>
    <head>
        <title>IMAGE PROCCESSING</title>
        <style>
            .imageName 
            {
                padding:4px;
                color:#444444;
                font-size:15px;
                font-family:helvetica;
            }
            img
            {
              max-width: 535px;

            }
            .imgBox
            {
                display:inline-block;
                padding:12px
            }
        </style>
    </head>
    <body><?php




function simplify1($obj,$sensitivity)
{

    $simplified = $obj->resize($sensitivity,$sensitivity);
    //making the bightness array :array1 goes top to bottom
    $dataArray1 = [];
    for($x=0;$x<$sensitivity;$x++)
    {
        for($y=0;$y<$sensitivity;$y++)
        {
            $color = $simplified->getColorAt($x,$y);
            $r = intval($color['red']);
            $g = intval($color['green']);
            $b = intval($color['blue']);
            
            $colorArray = [$r,$g,$b];
            
           array_push($dataArray1,$colorArray);
        }
    }
    
    
    return $dataArray1;
}


function doesPhotosMatch($array1,$array2,$off=5)
{
    if(count($array1) != count($array2)) { return -1;}
    
    $boxCount = count($array1);
    $similarity =$boxCount;

    for($i=0;$i<$boxCount;$i++)
    {
        $box1 = $array1[$i];
        $box2 = $array2[$i];
        

        if(abs($box1[0]-$box2[0])>$off ||abs($box1[1]-$box2[1])>$off ||abs($box1[2]-$box2[2])>$off)
        {
            $similarity --;
        }
    }
    return $similarity/$boxCount;


}



$folder = scandir(httpRoot .  'imgPro');
$fileIndex =0;
$suitableFileNames =[];
while(count($suitableFileNames)<2 && $fileIndex<count($folder))
{
    if(preg_match('/\.jpeg$/',$folder[$fileIndex]))
    {
        array_push($suitableFileNames,$folder[$fileIndex]);        
    }
    $fileIndex++;
}

//two .jpeg s found
if (count($suitableFileNames)==2) 
{
    $t0 = microtime(true);

    $IMG1_ = new \claviska\SimpleImage();
    $IMG2_ = new \claviska\SimpleImage();
    

    for ($s=1;$s<=10;$s++) {
        $IMG1 = $IMG1_->fromFile(httpRoot .  'imgPro/'. $suitableFileNames[0]);
        $IMG2 = $IMG2_->fromFile(httpRoot .  'imgPro/'. $suitableFileNames[1]);

        $key1 = simplify1($IMG1, $s);
        $key2 = simplify1($IMG2, $s);

        echo 'Sensitivity: ' . $s . ' Similarity : ' . doesPhotosMatch($key1,$key2,50) . '<br/>';
    }


    $t=microtime(true);

    showImg($suitableFileNames[0]);
    showImg($suitableFileNames[1]);


    echo 'time:' . strval(($t-$t0)*1000) . 'ms';
}



?>
    
    
    
    
    
    </body>
</html>
