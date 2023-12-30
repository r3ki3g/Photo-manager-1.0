<?php
/*
need to plant the httpRoot . '/sub_system/updateChecker/data.txt'  as {"pages":[],"pageData":{}}

*/

define('httpRoot', 'D:/HTTP_WEB/realfeedServer');
function getDB()
{
    $file = fopen(httpRoot . '/sub_system/updateChecker/data.txt', 'r');
    $cont = fread($file, filesize(httpRoot . '/sub_system/updateChecker/data.txt'));
    fclose($file);

    $cont = json_decode($cont, true);
    return $cont;
}
function setDB($data)
{
    $data =  json_encode($data);
    $file = fopen(httpRoot . '/sub_system/updateChecker/data.txt', 'w');
    fwrite($file, $data);
    fclose($file);
}
function goodId($name)
{
    return preg_replace('/ /', '_', $name);
}




//if add to update list request
if ($_POST['request'] ?? '' == 'addToUpdateList') {
    $pageURL = $_POST['pageURL'] ?? '';
    $pageName = $_POST['pageName'] ?? '';

    //check data valid
    if (!($pageURL != '' && $pageName != '')) {
        echo 'Not saved (Not Valid data) ';
        exit();
    }

    $DB = getDB();

    //check each page name
    foreach ($DB['pages'] as $page) {
        if ($pageName == $page[1]) //name already used
        {
            echo 'Not saved (Name already used) ';
            exit();
            break;
        }
    }

    //if good
    $DB['pages'][] = [$pageURL, $pageName];
    setDB($DB);
    echo 'saved!';
    exit();
} else if ($_POST['check'] ?? '' == 'true') {
    $moodleSession = $_POST['moodleSession'] ?? '';
    $DB = getDB();
    $old = $DB['pageData'];
    $new = array();



    $opts = array(
        'http' => array(
            'method' => "GET",
            'header' => "Accept-language: en\r\n" .
                "Cookie:MoodleSession=$moodleSession;"
        )
    );
    $context = stream_context_create($opts);


    //open each page
    foreach ($DB['pages'] as $page) {
        $file = file_get_contents($page[0], false, $context);
        $new[$page[1]] = strlen($file);
    }

    $noticed = array();
    foreach ($DB['pages'] as $page) {
        if (abs(intval($old[$page[1]] ?? 0) - $new[$page[1]]) > 10) {
            $noticed[$page[1]] = $new[$page[1]];
        }
    }

    echo json_encode($noticed);
}

//if going to see the update
else if ($_GET['go'] ?? '' != '') {
    $pageName = $_GET['go'];
    $pageNowSize = $_GET['size'];

    $DB = getDB();
    $DB['pageData'][$pageName] = $pageNowSize;
    setDB($DB);
    foreach ($DB['pages'] as $page) {
        if ($page[1] == $pageName) {
            header('location:' . $page[0]);
            exit();
            break;
        }
    }
} else {
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <script src="http://www.realfeed.com/js/jQuery.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update checker</title>
    <style>
    #prev {}

    .prevPageBox {
        background-color: #d4e1ff;
        font-size: 21px;
        font-family: helvetica;
        padding-top: 9px;
        padding-bottom: 17px;
        padding-left: 35px;
        margin-bottom: 5px;

    }

    a {
        text-decoration: none;
    }
    </style>
</head>

<body onload="check();setInterval(function(){check();},300000);">
    <h2>Add new page to check in 5 min intervals</h2>
    <form action="" method="post" target="_blank">
        <input name="request" value="addToUpdateList" type="hidden" />
        Page URL:<input type="text" name="pageURL" /> Name: <input type="text" name="pageName" /> <input
            type="submit" />
    </form>
    <hr />
    MoodleSession: <input type="text" id="moodleSession" />
    <hr />
    <div id="prev">
        <?php

            $DB = getDB();
            foreach ($DB['pages'] as $page) {
                echo '<a id="a_' . goodId($page[1]) . '" href="' . $page[0] . '" onclick="window.checkUpOn=false;" target="_blank"><div id="d_' . goodId($page[1]) . '" class="prevPageBox">' . $page[1] . '</div></a>';
            }

            ?>


    </div>

    <script>
    var audio = new Audio('http:\/\/www.realfeed.com/sub_system/updateChecker/ping.mp3');

    function goodId(name) {
        return name.replace(/\s/g, '_');
    }
    window.checkUpOn = false;
    window.lastCheck = 0;

    function check() {

        if (window.checkUpOn == false || new Number(new Date()) - window.lastCheck >
            60000) // no check up going or 60 passed
        {
            $.ajax({
                url: '',
                method: 'post',
                data: {
                    'check': 'true',
                    'moodleSession': $('#moodleSession').val()
                },
                success: function(res) {
                    window.checkUpOn = false;
                    res = JSON.parse(res);
                    $('.prevPageBox').attr('style', 'background-color:#d4ffd7');
                    for (name in res) {
                        audio.play();

                        setTimeout(function() {
                            $('#d_' + goodId(name)).css({
                                backgroundColor: '#ff0000'
                            });
                            $('#a_' + goodId(name)).attr('href', '?go=' + name + '&size=' + res[
                                name]);
                        }, 400);

                    }

                }

            });
            window.checkUpOn = true;
            window.lastCheck = new Number(new Date());
        }

    }
    </script>




</body>

</html>



<?php
}

?>