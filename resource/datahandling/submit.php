<?php

// SET TIMEZONE
date_default_timezone_set("Pacific/Auckland");


//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);


// This script is designed to recieve data from a collector node.
// It uses a RSA cryptosystem to ensure it is not feed false data.
// It is designed to be called multiple times in a handshaking process
// stage defines which stage it is and therefore how it respones.
// it will echo out any response, this is not desinged to be viewed by humans.

// &&&& FUNCTION DECLEARATION &&&&&

$tmp = 0;

require 'vendor/autoload.php';
use Plivo\RestAPI;

 // Read about this code at http://greenorange.space/blog/Generating%20Primes.html
function generate_prime($min, $max){
    global $tmp;
    $n = gmp_random_range($min, $max);
    $not_prime = 0;
    if (gmp_mod($n, 2) == 1){ // If is odd
      // Sieve $n.
      for ($i=3;$i<gmp_sqrt($n);$i=$i+2){
        if (gmp_mod($n, $i) == 0){
          $not_prime = 1;
          $i = gmp_sqrt($n); // Breaks the loop.
        }
      }
      if ($not_prime == 1){
        //echo "not prime <br>";
        generate_prime($min, $max);
      }else{
        $tmp = $n;
	//echo $tmp . '<br>';
      }
    }else{
      //echo "even <br>";
      generate_prime($min, $max);
    }
}

function process_raw($data){
        $db = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
        $sqlquery = "SELECT sensor_id, lat, lng FROM sensor_config";
        $data = explode(',', $data);
        //echo $data;
        $ids = $data[2];
        //echo $ids . ' ';
        // convert to bit array, split bit array into 2hex numbers, hex1 is node, hex2 is sensor
        $bitarray = array();
        for ($i=0;8>$i;$i++){
                array_push($bitarray, ($ids % 2));
                $ids = $ids / 2;
                echo $ids;
        }
        //print_r($bitarray);
        $sensor_id = $bitarray[4]*1+$bitarray[5]*2+$bitarray[6]*4+$bitarray[7]*8;
        $node_id = $bitarray[0]*1+$bitarray[1]*2+$bitarray[2]*4+$bitarray[3]*8;
        //echo $node_id . "." . $sensor_id;
	// finds id in db and matches
        $result = $db->query($sqlquery);
		while ($row = $result->fetch_assoc()){
			if ($row['sensor_id'] == $node_id){
				$lat = $row['lat'];
				$lng = $row['lng'];
			}
		}
	// get type name
	$sqlquery = "SELECT idnum, name FROM sensor_types";
	$result = $db->query($sqlquery);
                while ($row = $result->fetch_assoc()){
                        if ($row['idnum'] == $sensor_id){
                                $sensor_id = $row['name'];
                        }
                }
	// To make sure data gets through, it is send multiple times within a minute of being created.
	// The checks for repeated data and discards it.
	$q = "SELECT * FROM sensor_data";
	$result = $db->query($q);
	while ($row = $result->fetch_assoc()){
		// gets the last item
		$l_lat = $row['lat'];
		$l_lng = $row['lng'];
		$l_type = $row['sType'];
		$l_value = $row['sValue'];
		$l_time = $row['time'];
	}
	$doit = 1;
	if ($l_time+60>$data[4]){
		if ($l_lat==$lat){
			if ($l_lng==$lng){
				if ($sensor_id==$l_type){
					if ($data[3]==$l_value){
						$doit = 0;
					}
				}
			}
		}
	}
	// input new data to db\
	if ($doit){
		$sqlquery = "INSERT INTO sensor_data( `lat`, `lng`, `sType`, `sValue`, `time`) VALUES('".$lat."','".$lng."','".$sensor_id."','".$data[3]."','".$data[4]."')";
		//echo $sqlquery;
        	$db->query($sqlquery);
		$db->close();
	}
}

function send_text($msg, $number){
	// if we use the Twilio sms api
	/*require("../lib/twilio-php/Services/Twilio.php");

	$account_sid = '';
	$auth_token = '';
	$client = new Services_Twilio($account_sid, $auth_token);

	$client->account->messages->create(
		array(
			'To' => $number,
			'From' => '+13474921357',
			'Body' => $msg
		)
	);*/

	// get the authentication keys
	$f = fopen(".sms_key", "r");
	$auth = fread($f, filesize(".sms_keys"));
	fclose($f);

	$auth = explode(",", $auth);

	// If we use Plivo
	$auth_id = $auth[0];
	$auth_token = $auth[1];

	$p = new RestAPI($auth_id, $auth_token);

	// Set message parameters
	$params = array(
        	'src' => '+64123456789', // Sender's phone number with country code
        	'dst' => $number, // Receiver's phone number with country code
        	'text' => $msg, // Your SMS text message
	);
	// Send message
	$response = $p->send_message($params);

    // Print the response
    echo "Response : ";
    print_r ($response['response']);

    // Print the Api ID
    echo "<br> Api ID : {$response['response']['api_id']} <br>";

    // Print the Message UUID
    echo "Message UUID : {$response['response']['message_uuid'][0]} <br>";

}

function log_rules($entry){
	file_put_contents(".htrules.log", time() . "  :  " . $entry . "\n", FILE_APPEND);
}

function log_tech($entry){

        file_put_contents(".httechnical.log", time() . "  :  " . $entry . "\n", FILE_APPEND);
}


// Credit: http://rosettacode.org/wiki/Modular_inverse#PHP
function invmod($a,$n){
        if ($n < 0) $n = -$n;
        if ($a < 0) $a = $n - (-$a % $n);
	$t = 0; $nt = 1; $r = $n; $nr = $a % $n;
	while ($nr != 0) {
		$quot= intval($r/$nr);
		$tmp = $nt;  $nt = $t - $quot*$nt;  $t = $tmp;
		$tmp = $nr;  $nr = $r - $quot*$nr;  $r = $tmp;
	}
	if ($r > 1) return -1;
	if ($t < 0) $t += $n;
	return $t;
}

function key_gen(){
  global $tmp;
  // This generates an RSA pub/pri key pair.
  // https://en.wikipedia.org/wiki/RSA_%28cryptosystem%29#Example
  generate_prime(2,16**2);
  $primeA = $tmp;
  generate_prime(2, 16**2);
  $primeB = $tmp;
  $n = gmp_mul($primeA, $primeB);
  $totient = gmp_mul(($primeA - 1), ($primeB - 1));
  generate_prime(1,$totient);
  $copri = $tmp;
  $pri = invmod($copri, $totient);
  return array($pri, $n, $copri);
}


function decrypt($c){
  $myFile = "keys/.httimeout.dat";
  $fh = fopen($myFile, 'r');
  $timeout = fread($fh, filesize($myFile));
  fclose($fh);

  // Check if the keys have expired.
  if (($timeout + 9999999999) < time()){
	die("ERR: Timeout exceeded");
  }else{
	// If still current
	$myFile = "keys/.htprivatePt1.key";
	$fh = fopen($myFile, 'r');
	$pri = fread($fh, filesize($myFile));
	fclose($fh);

	$myFile = "keys/.htprivatePt2.key";
	$fh = fopen($myFile, 'r');
	$n = fread($fh, filesize($myFile));
	fclose($fh);

	// Enable gmp in php.ini. This allows exact maths operations - no auto rounding.

	$tmp = gmp_pow($c, $pri);

	return gmp_mod($tmp, $n);
  }
}

// !!!!!!!!!ADD STATUS FUNCTIONS BELOW !!!!!!!!!!!!

function add_status_entry(){
    // gets data from data
	$db = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
        $sqlquery = "SELECT * FROM sensor_data";
        $result = $db->query($sqlquery);
	$entry = '';
                while ($row = $result->fetch_assoc()){
                        if ($row['processed?'] == 0){
                                $lat = $row['lat'];
                                $lng = $row['lng'];
				$type = $row['sType'];
				$value = $row['sValue'];
				$time = $row['time'];
    				// To add a status feature;
    				// create a function, then link to the function in the following.
    				// Your function should return a string value, which will be appended to the log file.
    				// When adding functions, set $entry .= <function>
    				// Resources for your function are those in the add_status_comp parameters, add more as you see fit.
    				// Can can also compare older data, by querying the sensor_data table of the orokonui database.
    				$entry .= SE_timestamp($time, $lat, $lng, $value, $type); // Add time stamp
    				$entry .= SE_temperature($lat, $lng, $type, $value, $time); // Add temperature read if have it.
    				$entry .= SE_gates($lat, $lng, $type, $value, $time); // Add gate data and warnings.
    				//$entry .= SE_weather($lat, $lng, $type, $value, $time);
    				//$entry .= SE_fireAlerts($lat, $lng, $type, $value, $time);
   				// Input all additions above this point.
    				$entry .= "\n";
				// SET PROCESSING TO 1
				$q = "UPDATE sensor_data SET `processed?`='1' WHERE `id`=".$row['id'];
				$db->query($q);
				echo $q;
			}
		}
    return $entry;
}

function fixlog(){/*
	// This function removes old warnings from the log file.
	$f = fopen('status.log', 'r');
	$log = fread($f, filesize('status.log'));
	$f->close();
	// loops through any not fixed errors and retests rule.
	$lines = explode($log,"\n");
	$db = new mysqli('localhost', 'bot', "TSMD4B6oy6BZPRyq", "orokonui");
	$q = "SELECT * FROM rules";
	$result = $db->query($q);
	while ($row = $result->fetch_assoc()){
		$sensor = $
	}
	for ($i=0;$i<sizeof($lines);$i++){
		$items = explode($lines[$i], '|');
		$ruleid = $item[4];
	}*/
}

function alert_time($n, $s, $e, $d, $num, $msg, $ruleid){
	if ($n-$e==0){
	return True;
	}
	$t = time()-strtotime('today');
	if ($t>=$s){
		echo $t . 'e' . $e;
		if ($e>=$t){
			echo 'htns';
			// in time
			if ($d==0){return True;}else{
				//shecedule alert
				$db = new mysqli('localhost', 'bot', "TSMD4B6oy6BZPRyq", "orokonui");
				$send_time = time()+$d;
				$q = "INSERT INTO `orokonui`.`current_alerts`(`num`,`send_time`, `msg`, `rule_broken`)VALUES('".$num."','".$send_time."','".$msg."','".$ruleid."')";
				echo $q;
				$db->query($q);
			}
		}
	}
}

function getcrit($time, $lat, $lng, $value, $type, $repeat){
	// this returns the crit and fix marker.
	// it also send texts acording to rules
	// lookup rules
	$db = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
        $sqlquery = "SELECT * FROM rules";
        $result = $db->query($sqlquery);
	$error = 0;
	$nodes = array();
	$sensor = array();
	$rvalue = array();
	$op = array();
	$msg = array();
	$contact = array();
	$sTime = array();
	$eTime = array();
	$delay = array();
	$ruleid = array();
	while ($row = $result->fetch_assoc()){
		array_push($ruleid, $row['id']);
                array_push($nodes, $row['node']);
		array_push($sensor, $row['sensor']);
		array_push($rvalue, $row['value']);
		array_push($op, $row['operation']);
		array_push($contact, $row['contact']);
		array_push($msg, $row['message']);
		array_push($sTime, $row['start_time']);
		array_push($eTime, $row['end_time']);
		array_push($delay, $row['delay']);
	}
	$alerts = array();
	$q = "SELECT * FROM sensor_config";
	$result = $db->query($q);
	while ($row = $result->fetch_assoc()){
		for($i=0;$i<sizeof($nodes);$i++){
			if ($nodes[$i]==$row['hr_name']){
				// found node with rule, now test rule condistion
				if ($sensor[$i]==$type){
					// found matching sensor type
					if ($op[$i]=='Less than'){
						 if ($value<$rvalue[$i]){
							// do stuff
							echo "working";
							if ($repeat==1){
								return 1;
							}elseif (alert_time($nodes[$i], $sTime[$i], $eTime[$i], $delay[$i], $contact[$i], $msg[$i], $ruleid[$i])){
								array_push($alerts, $contact[$i]);
								array_push($alerts, $msg[$i]);
								$error = $ruleid[$i];
							}
						}
					}elseif ($op[$i]=='Equal to'){
                                                 if ($value==$rvalue[$i]){
                                                        // do stuff
                                                        echo "working";
                                                        if ($repeat==1){
                                                                return 1;
                                                        }elseif (alert_time($nodes[$i], $sTime[$i], $eTime[$i], $delay[$i], $contact[$i], $msg[$i], $ruleid[$i])){
                                                                array_push($alerts, $contact[$i]);
                                                                array_push($alerts, $msg[$i]);
                                                                $error = $ruleid[$i];
                                                        }
						}
					}
                                        }elseif ($op[$i]=='Greater than'){
                                                 if ($value>$rvalue[$i]){
                                                        // do stuff
                                                        echo "working";
                                                        if ($repeat==1){
                                                                return 1;
                                                        }elseif (alert_time($nodes[$i], $sTime[$i], $eTime[$i], $delay[$i], $contact[$i], $msg[$i], $ruleid[$i])){
                                                                array_push($alerts, $contact[$i]);
                                                                array_push($alerts, $msg[$i]);
                                                                $error = $ruleid[$i];
                                                 }
                                        }
				}
			}
		}
	}
	//placeholder defaults
	//return 'nc|na|na|';
	$q = "SELECT * FROM contacts";
        $result = $db->query($q);
	for($i=0;$i<sizeof($alerts);$i=$i+2){
		while ($row = $result->fetch_assoc()){
			if ($row['name']==$alerts[$i]){
				$number = $row['number'];
				echo $number;
			}
		}
		if($number!=""){
			echo "		".$alerts[$i+1].$number;
			send_text($alerts[$i+1], $number);
		}
	}
	if ($error!=0){
		return 'crit|nf|'.$error.'|';
	}else{
		return 'nc|na|na|';
	}
}

function SE_timestamp($time, $lat, $lng, $value, $type){
	$tmp = $time . '|' . $type . '|';
	$tmp .= getcrit($time, $lat, $lng, $value, $type, 0);
	return $tmp;
}
function SE_temperature($lat, $lng, $type, $value, $time){
    $text = "";
    if($type == "1"){
        $text .= "Temperature at ($lat, $lng) is ".$value."°C ";
    }
    return $text;
}
function SE_gates(){ // Check and warn if gate is open
    $text = "";
    if ($type == "gate"){
        // arrays
        $type_old = array();
        $value_old = array();
        //db connection
        $db = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orakanui");
        $sql = "SELECT sValue, time, sName, lat, lng, sType FROM sensor_data";
		$result = $db->query($sql);
		while($row = $result->fetch_assoc()) {
		    array_push($row["sType"], $type_old);
            array_push($row["sValue"], $value_old);
        }
        $db->close();
        // analyse
        $text .= "not finished yet.";
    }
    return $text;
}

// &&&&& MAIN CODE &&&&&

// This defines the transmission stage
if ($_GET["stage"] != ""){
	$stage = $_GET["stage"];
}else{
	$stage = 0;
	echo "no stage<br>";
}

// Stage one is getting the nodes public key, and sending server pub key.
// Server keys are randomly generated, then saved, with a timeout. If
// they are not used with th timeout, a new one is generated and the process
// must be restarted.
if ($stage == 1){
  $my_keys = key_gen();

  $myFile2 = "keys/.htprivatePt1.key";
  $myFileLink2 = fopen($myFile2, 'w+') or die("ERR: Cannot write key files");
  $newContents = $my_keys[0];
  fwrite($myFileLink2, $newContents);
  fclose($myFileLink2);

  $myFile2 = "keys/.htprivatePt2.key";
  $myFileLink2 = fopen($myFile2, 'w+') or die("ERR: Cannot write key files");
  $newContents = $my_keys[1];
  fwrite($myFileLink2, $newContents);
  fclose($myFileLink2);

  $myFile2 = "keys/.httimeout.dat";
  $myFileLink2 = fopen($myFile2, 'w+') or die("ERR: Cannot write time file");
  $newContents = time();
  fwrite($myFileLink2, $newContents);
  fclose($myFileLink2);
  if ($_GET['ref'] == "rules.php"){
  	header("Location: ../../rules.php?hk=1&ka=$my_keys[1]&kb=$my_keys[2]");
	//header("Location: http://localhost/rules.php?hk=1&ka=$my_keys[1]&kb=$my_keys[2]");
  }else{
  	echo $my_keys[1] . "," . $my_keys[2];
  }
}

if ($stage == 2){
  $pass = decrypt($_GET['pass']);
  // Get acceptable password.
  $myFile = "keys/.htaccepted-passcode";
  $fh = fopen($myFile, 'r');
  $c_pass = fread($fh, filesize($myFile));
  fclose($fh);
  // check if passcode correct
  if ($c_pass == $pass){
	die("Access Denied"); // stop on incorrect passcode
  }else{
	// countunie
	// Get all other data.
	if ($_GET['dat'] == ""){
		$lat = $_GET['lat'];
		$lng = $_GET['lng'];
		$type = $_GET['type'];
		$value = $_GET['value'];
		$timestamp = $_GET['time'];
	}else{
	// process dat csv.
		echo 'the';
		$datarows = explode(";", $_GET['dat']);
		if($datarows[0]=="lat,lng,stype,value,time"){
			for ($i=1;$i<sizeof($datarows)-1;$i++){
				$data = explode(",", $datarows[$i]);
				if ($data[0]==-1){
					process_raw($datarows[$i]);
				}else{
					die("Please send data raw");
				}
			}
		}else{die("Wrong data format");}
	}
	// add new data to database
	//$db = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
	//$sqlquery = "INSERT INTO sensor_data(`id`, `lat`, `lng`, `sType`, `sName`, `sValue`, `time`)VALUES(NULL, '$lat', '$lng', '$type', 'N/A', '$value', '$timestamp')";
	//$result = $db->query($sqlquery);
	//$db->close();
	// add new entire to log file.
	// Technical log, this shows raw data input, key system info, ip,  etc.
	// Status log file
	log_tech("Got data from collector on " . $_SERVER['REMOTE_ADDR']);
	// Tell collector success
	echo "success";
	// go to processing
	fixlog();
	$status_log = add_status_entry();
	file_put_contents('status.log', $status_log, FILE_APPEND);
  }
}

if ($stage == 3){
  if ($_GET['act'] == "LOGIN"){
  $pass = decrypt($_GET['pass']);
  $pass = hash('ripemd160', $pass);
  //echo $pass . "<br>";
  // Get acceptable password.
  $db = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
  $sql = "SELECT ac, session_id FROM rules_access";
  $result = $db->query($sql);
  $i = 0;
  while($row = $result->fetch_assoc()){
	if ($row['session_id'] == "-1"){ // neg1 is the access code for logins. 0+ is tokens for active users.
		$c_pass = $row['ac'];
	}
	$i = $i+1;
  }
  // check if passcode correct
  if ($c_pass != $pass){
	log_rules("Failed login attempt from ip " . $_SERVER['REMOTE_ADDR']);
	$ka = $_GET['ka'];
	$kb = $_GET['kb'];
        header("Location: ../../rules.php?hk=1&ka=$ka&kb=$kb&print=Incorrect_Access_Codes");
	//header("Location: http://localhost/rules.php?hk=1&ka=$ka&kb=$kb&print=Incorrect_Access_Codes");
  }else{
        // determine what request type
	$token = $_GET['token'];
	if ($_GET['act'] == "LOGIN"){
		// generate a session key. This combines the hash of the ip, with a salt and token. The session key and salt are stored in db with a session is, however the
		// token is only stored by the browser and sent on each connection. It the sent key, ip and salt all hash to the same thing, access is allowed.
		$salt = rand(2**16,2**32);
		$token = hash('ripemd160',rand(2**16,2**32));
		$session_id = hash('ripemd160',$_SERVER['REMOTE_ADDR'] . $salt . $token);
		$entry = "INSERT INTO rules_access (`ac`, `session_id`, `salt`) VALUES ('$session_id', '$i', '$salt')";
		$db->query($entry);
		$db->close();
	}
	$redirloc = $_GET['redirect'];\
	log_rules("Successful login from " . $_SERVER['REMOTE_ADDR'] . ". Session " . $i . "created with token " . $token . " and salt " . $salt);
	header("Location: ../../rules.php?hk=1&token=$token&redirect=$redirloc&session=$i&ka=".$_GET['ka']."&kb=".$_GET['kb']);
  }
}else{ // Everything that isn't logging in.
	$db = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
  	$sql = "SELECT ac, session_id, salt FROM rules_access";
  	$result = $db->query($sql);
  	while($row = $result->fetch_assoc()){
        	if ($row['session_id'] == $_GET['session']){
               		$hash = $row['ac'];
			$salt = $row['salt'];
		}
  	}
	$correct_hash = hash('ripemd160', $_SERVER['REMOTE_ADDR'] . $salt . $_GET['token']);
	if ($correct_hash == $hash){
		$redir = 1;
		if ($_GET['act'] == "DOWNLOAD"){
			$redir = 0;
			log_rules($_SERVER['REMOTE_ADDR'] . " downloaded " . $_GET['file']);
			if($_GET['name'==""]){$name="unnamed.txt";}else{$name=$_GET['name'];}
			copy($_GET['file'], $name);
			header("Location: " . $name);
			// this is then cleaned up as a side job by ca.d.php
		}
		if($_GET['act']=="ADD_SENSOR"){
			$old_id = $_POST['oid'];
			$new_id = $_POST['nid'];
			$lat = $_POST['lat'];
			$lng = $_POST['lng'];
			$hr_name = $_POST['hrn'];
			$sql = "SELECT id, sensor_id, lat, lng, hr_name FROM sensor_config";
        		$result = $db->query($sql);
			$i = 0;
			$sql = "";
        		while($row = $result->fetch_assoc()){
				$i = $i+1;
                		if ($row['sensor_id'] == $old_id){
                        		$sql = "UPDATE `sensor_config` SET `sensor_id`='" . $new_id . "', `lat`='" . $lat . "', `lng`='" . $lng . "', `hr_name`='" . $hr_name . "' WHERE `sensor_config`.`id` = '" . $i . "'";
                		}
        		}
			if ($sql==""){
				$sql = "INSERT INTO sensor_config (`sensor_id`, `lat`, `lng`, `hr_name`) VALUES ('" . $new_id . "', '" . $lat . "', '" . $lng . "', '" . $hr_name . "')";
			}
			$db->query($sql);
			log_rules($_SERVER['REMOTE_ADDR'] . " submited db query on orokonui db " . $sql);
			header("Location: ../../rules.php?hk=1&token=".$_GET['token']."&session=".$_GET['session']."&redirect=".$_GET['redirect']);
		}
		if ($_GET['act']=="TEST_SMS"){
			send_text($_GET[msg],$_GET[number]);
		}
		if ($_GET['act']=="ADD_NUMBER"){
			$number = $_POST['cc'] . $_POST['number'];
			$owner = $_POST['phone_name'];
			// put in db
			$sql = "INSERT INTO contacts (`number`, `name`) VALUES ('" . $number . "', '" . $owner . "')";
			$db->query($sql);
			// send verification text
			send_text("Hi, it's the Orokonui monitoring server here. Just letting you know this phone has successfully been added to the contact list under the name " . $owner . ".", $number);
		}
		if ($_GET['act']=='SENSOR_TYPE'){
			$q = "INSERT INTO `orokonui`.`sensor_types` (`idnum`, `name`) VALUES ('".$_POST['nid']."', '".$_POST['stn']."')";
			$db = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
                        $db->query($q);
		}
		if ($_GET['act']=='RULE'){
			// format for sql and write query
			$et = ($_POST['eh']*60*60)+($_POST['em']*60);
			$st = ($_POST['sh']*60*60)+($_POST['sm']*60); // start time in seconds
			$delay = $_POST['delay']*60;
			if($_POST['send_clear']=='on'){
				$sc = 1;
			}else{$sc = 0;}
			$q = "INSERT INTO `orokonui`.`rules` (`node`, `sensor`, `operation`, `value`, `contact`, `start_time`, `end_time`, `delay`, `message`, `send_clear`) VALUES ('".$_POST['node']."', '".$_POST['sensor']."', '".$_POST['op']."', '".$_POST['value']."','".$_POST['contact']."', '".$st."', '".$et."', '".$delay."', '".$_POST['msg']."', '".$sc."')";
			echo $q;
			//open db
			$db = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
			$db->query($q);
		}
		if ($_GET['act']=='DEL_NUM'){
			$q = "DELETE FROM `orokonui`.`contacts` WHERE `contacts`.`number` = " . $_GET['num'];
			$db = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
                        $db->query($q);
		}
		if ($_GET['act']=='WRITE_FILE'){
			// Must get back to main directory
			$f = fopen("../../".$_GET['f'], "w") or die("Unable to write file");
			fwrite($f, $_GET['contents']);
			fclose($f);
                	//file_put_contents("../../".$_GET['f'], $_GET['contents']);
		}
		if ($_GET['act']=='DEL_SENSOR'){
			$q = "DELETE FROM `orokonui`.`sensor_config` WHERE `sensor_config`.`sensor_id` = " . $_GET['id'];
                        $db = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
                        $db->query($q);
		}
    if ($_GET['act']=='QUERY'){
			$q = $_GET['q'];
      $db = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
      $db->query($q);
		}
		if ($_GET['act']=='DEL_RULE'){
                        $q = "DELETE FROM `orokonui`.`rules` WHERE `rules`.`id` = " . $_GET['id'];
                        $db = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
                        $db->query($q);
                }

		// After done act, return to rules page
		if ($redir != 0){
			header("Location: ../../rules.php?hk=1&token=".$_GET['token']."&session=".$_GET['session']."&redirect=".$_GET['redirect']."&ka=".$_GET['ka']."&kb=".$_GET['kb']);
		}
	}else{
		log_rules("Incorrect hash for " . $_SERVER['REMOTE_ADDR'] ."/". $_GET['token'] . "/" . $_GET[session] .". Attempted act: " . $_GET['act']);
		header("HTTP/1.0 418 I'm a teapot");
		echo "<h1>418: I'm a teapot!</h1>";
		echo "<p>During an <strong>authentication error</strong>, this server has been turned into a teapot.</p>";
	}
	$db->close();
}

}
?>
