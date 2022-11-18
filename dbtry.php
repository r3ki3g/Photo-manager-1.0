<?php
define('httpRoot','D:/HTTP_WEB/realfeedserver/');

require_once httpRoot . 'sfs.php';


timelog("timelogcheck","1");
timelog("timelogcheck","2");
timelog("timelogcheck","3");
print_rh(timelog("timelogcheck"));


print_rh(explode('c','saca'));
echo __LINE__;

exit();


$timeStart = microtime(true);


$dbServer = 'localhost';
$dbUserName = 'root';
$dbPassword ='';
$dbName ='rekiegtest';

$conn = new mysqli($dbServer, $dbUserName, $dbPassword, $dbName);
if ($conn->connect_error)
{
    echo "Error connecting to database!";exit();
}

/*
//adding records
$sql = 'insert into students(name,major) values';
$s = 8;
for ($i = 100000*$s;$i<100000+100000*$s;$i++)
{   if ($i-100000*$s){$sql .= ',';}
    $sql .= "('student_{$i}','discipline_{$i}')";
}
$conn->query($sql);
print_rh($conn);
*/



if(!isset($_GET['q'])) exit();
$stmt = $conn->prepare("(select name,major from students where name like ? limit 100) order by name;");
$stmt->bind_param('s',$studentNameWildcard);
$studentNameWildcard = "{$_GET['q']}%";
$stmt->execute();

/*
$resultArray = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
foreach($resultArray as $result)
{
    echo "{$result['name']} learnt {$result['major']}".br;
}
*/

$stmt->bind_result($name,$major);
while ($res = $stmt->fetch()) echo "$name learnt $major".br;








$timeEnd = microtime(true);
$timeTaken = $timeEnd - $timeStart;
echo br.hr;
echo "Time Taken: $timeTaken (s)";






?>