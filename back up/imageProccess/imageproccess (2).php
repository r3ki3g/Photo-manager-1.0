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
            $r = $color['red']/255;
            $g = $color['green']/255;
            $b = $color['blue']/255;

            $prominent = '0';
            if($r > 0.65){$prominent = 'r';}
            else if($g > 0.55){$prominent = 'g';}
            else if($b > 0.55){$prominent = 'b';}
            else if($r > 0.25 && $g > 0.35){$prominent = 'y';}
            else if($g > 0.25 && $b > 0.35){$prominent = 'c';}
            else if($b > 0.25 && $r > 0.35){$prominent = 'm';}
            else{$prominent = '1';}



            //$brightness = 0.21 * $r + 0.72 * $g + 0.07 * $b;
            //$brightness = $r;
            
           array_push($dataArray1,$prominent);
        }
    }
    //compairing consecetive bighteness bits and making the key
    $key1 ='';
    for($i = 0;$i<count($dataArray1);$i++)
    {
        $key1 .= $dataArray1[$i];
    }

    return $key1;
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

        echo 'Sensitivity : ' . strval($s) . ' Images are ' . ($key1 == $key2 ?'<b>similar</b>! <code>' .$key1. '</code>' :'<span style="color:red">not similar</span>') . '<br/>' ;
    }


    $t=microtime(true);

    showImg($suitableFileNames[0]);
    showImg($suitableFileNames[1]);


    echo 'time:' . strval(($t-$t0)*1000) . 'ms';
}



?>
    
    
    
    
    
    </body>
</html>
