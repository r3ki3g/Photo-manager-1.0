<?php   
//sleep(1);
//root directiory declaration
$httpRoot = 'D:/HTTP_WEB/realfeedserver/';

// STRUCTURED FUNCTION SET 
require $httpRoot . 'sfs.php';

//if update requested
if( isset($_GET['update']) && $_GET['update'] === 'true')
{
    if(isset($_POST['left']) && isset($_POST['top']))
    {
        $left = $_POST['left'];
        $top = $_POST['top'];
        if(is_numeric($left) && is_numeric($top))
        {
            $dataFile = fopen($httpRoot . 'uploadsTemp/c.txt','w');
            fwrite($dataFile,$top .':'. $left);
            fclose($dataFile); 
        }
    }
    
    
    exit();
}

//if sync requested
if(isset($_GET['update']) && $_GET['update'] === 'request')
{
    readfile($httpRoot . 'uploadsTemp/c.txt');
  
    exit();
}


?><!DOCTYPE html>
<html>
    <head>
        <title>Updating Message Box</title>
        <script src="http://www.realfeed.com/js/jQuery.js?812662457"></script>
        <script>function C(s) { console.log(s); }

function T(o) { console.table(o); }

function s(n, postfix) { if (n == 1) { return ''; } return postfix || 's'; }

function abs(n) {
    if (n >= 0) { return n; }
    return (-1) * n;
}
function shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
}
function forceNumberize(str) {
    str = String(str);
    str = str.replace(/[^0-9\.\-]/gim, "");
    return new Number(str);
}function extractJSON(str) {
    var out = false;
    try { out = JSON.parse(str); } catch (e) {}
    return out;
}</script>

    </head>

    <body onload="syncCoords()" style="width:100%;height:100%;">
        <div id="cursorDot" style="height:16px;width:16px;border-radius:16px;background-color:#000000;position:fixed;"></div>




        <script>
        window.onmousemove=function()
        {
            var coords = {'top':event.clientY,'left':event.clientX};
           // C(coords);
           // $('#cursorDot').css(coords);
            $.ajax({
                url:'http:\/\/www.realfeed.com\/following-dot.php?update=true',
                data:coords,
                method:'POST',
                success:function(res)
                        {

                        }
            });
        }
        function syncCoords()
        {
            setInterval(function(){
                $.ajax({
                    url:'http:\/\/www.realfeed.com\/following-dot.php?update=request',
                    method:'post',
                    success:function(res){
                        var data =  res.split(':');
                        var left = data[1];
                        var top = data[0];
                        $('#cursorDot').css({'left':left+'px','top':top+'px'});

                    }
                });
            },100);
        }
        </script>
    </body>
</html>