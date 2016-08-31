<?php
date_default_timezone_set("Pacific/Auckland");
include 'submit.php';

// This script must be run every minute, I recommend using cron.
// It's purpose it to check the queued alerts, it there time has expired, and if they are still needed.
echo "Oh hello there web browser, this page is not for you. It's an automatic process made to run on a scheduled system. Sorry, nothing here.<br><br>";

function send($msg, $num){
	$db = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
	$q = "SELECT * FROM contacts";
	$result = $db->query($q);
	while ($row = $result->fetch_assoc()){
		if ($num==$row['name']){
			$num = $row['number'];
		}
	}
	send_text($msg, $num);
	$db->close();
}

function checkrule($rid){
	$db = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
	$q = "SELECT * FROM rules";
	$result = $db->query($q);
	while ($row = $result->fetch_assoc()){
		if($row['id']==$rid){
			$sensor = $row['sensor'];
		}
	}
	$q = "SELECT * FROM sensor_data";
	$result=$db->query($q);
	while ($row=$result->fetch_assoc()){
		if ($row['sType']==$sensor){
			// this gets the latest data for rule testing
			$lat=$row['lat'];
			$lng=$row['lng'];
			$value=$row['sValue'];
			$time=$row['time'];
		}
	}
	$db->close();
	return getcrit($time, $lat,$lng,$value,$sensor,1);
}

$db = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
$q = "SELECT * FROM current_alerts";
$result = $db->query($q);
while ($row = $result->fetch_assoc()){
	if ($row['send_time']<=time()){
		// check validity (error may have been corrected)
		if (checkrule($row['rule_broken'])==1){
			//send (returns true if still broken)
			//send($row['msg'], $row['num']);
			echo 'would send text - disabled';
		}
		//remove from db
		$q="DELETE FROM `orokonui`.`current_alerts` WHERE `current_alerts`.`id`='".$row['id']."'";
		$db->query($q);
	}
}
$db->close();

// reoccuring side jobs

// remove tmp files
if (file_exists('rules.log')){
	unlink('rules.log');
}
if (file_exists('techical.log')){
	unlink('technical.log');
}

echo "<br><br>Or, if your debuging, ignore the above. Your data interpretation scripts have been executed below:<br><br>";

// run user made tasks
$dir = "../user-data/data-interpretation-scripts/";
$posts = scandir($dir);
for ($i=0;$i<sizeof($posts);$i++){
	if (strpos($posts[$i], '.php')!== false)
	{
		echo exec("php ".$dir.$posts[$i]);
	}
}

?>
