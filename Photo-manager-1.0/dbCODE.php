<?php

require_once 'sfs.php';

$dbHostName = 'localhost';
$dbUserName = 'root';
$dbPassword = '';
$dbName ='rekiegtest';


$conn = new mysqli($dbHostName,$dbUserName,$dbPassword,$dbName);
if( $conn->connect_error)
{
    die('db connection failed');
}
$stmt = $conn->prepare('select name,email from boy where  name like ? limit 100;');
$stmt->bind_param('s',$name);

$name="r%";
$stmt -> execute();
//$stmt -> bind_result($name,$email);
print_rh($stmt -> get_result() -> fetch_all(MYSQLI_ASSOC));
/*while($stmt -> fetch())
{
    echo "$name : $email";
    echo br;
}*/


?>