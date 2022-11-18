<?php



$opts = array(
    'http'=>array(
      'method'=>"GET",
      'header'=>"Accept-language: en\r\n" .
                "Cookie:MoodleSession=57lff015drrgslrlsn9crm0num;"
    )
  );
  
  $context = stream_context_create($opts);
  
  // Open the file using the HTTP headers set above
  $file = file_get_contents('https://online.uom.lk/course/view.php?id=14272', false, $context);
   
  echo strlen($file);
  //echo $file;
?>
