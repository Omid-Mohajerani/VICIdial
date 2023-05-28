#!/usr/bin/php -q
<?php
require "/usr/src/agi-scripts/phpagi.php";

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "asterisk";


$agi = new AGI();
$clid = $argv[1];


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "select lead_id from vicidial_list where phone_number = $clid limit 1";
$result = $conn->query($sql);
if ($result) {
	$row = mysqli_fetch_array($result);
	if($row){
   		$Lead_ID = $row["lead_id"];
		$agi->verbose(" --- caller $clid valid with lead id $Lead_ID --- ");
		return;
        }
	else{	
		$agi->verbose(" --- caller $clid is NOT VALID ---");
		$agi->hangup();
	}	

} else{
  	echo "Error: " . $sql . "\n" . $conn->error;
}

$conn->close();
?>
