<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



// This script is designed to recieve data from a collector node.
// It uses a RSA cryptosystem to ensure it is not feed false data.
// It is designed to be called multiple times in a handshaking process
// stage defines which stage it is and therefore how it respones.
// it will echo out any response, this is not desinged to be viewed by humans.

// &&&& FUNCTION DECLEARATION &&&&&

$tmp = 0;

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
      }
    }else{
      //echo "even <br>";
      generate_prime($min, $max);
    }
  }



// Credit: http://rosettacode.org/wiki/Modular_inverse#PHP
function invmod($a,$n){
        if ($n < 0) $n = -$n;
        if ($a < 0) $a = gmp_sub($n, gmp_mod(-$a, $n));
	$t = 0;
	$nt = 1;
	$r = $n;
	$nr = gmp_mod($a, $n);
	while ($nr != 0) {
		$quot= intval($r/$nr);
		$tmp = $nt;  $nt = gmp_sub($t, gmp_mul($quot, $nt));  $t = $tmp;
		$tmp = $nr;  $nr = gmp_sub($r, gmp_mul($quot, $nr));  $r = $tmp;
	}
	if ($r > 1) return -1;
	if ($t < 0) $t += $n;
	return $t;
}

function key_gen(){
  global $tmp;
  // This generates an RSA pub/pri key pair.
  // https://en.wikipedia.org/wiki/RSA_%28cryptosystem%29#Example
  generate_prime(2**8, 2**12);
  $primeA = $tmp;
  generate_prime(2**8, 2**12);
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
  if (($timeout + 120) < time()){
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

function add_status_entry($lat, $lng, $type, $value, $time){
    // To add a status feature;
    // create a function, then link to the function in the following.
    // Your function should return a string value, which will be appended to the log file.
    // When adding functions, set $entry .= <function>
    // Resources for your function are those in the add_status_comp parameters, add more as you see fit.
    // Can can also compare older data, by querying the sensor_data table of the orokonui database.
    $entry = "";
    $entry .= SE_timestamp($time); // Add time stamp
    $entry .= SE_temperature($lat, $lng, $type, $value, $time); // Add temperature read if have it.
    $entry .= SE_gates($lat, $lng, $type, $value, $time); // Add gate data and warnings.
    //$entry .= SE_weather($lat, $lng, $type, $value, $time);
    //$entry .= SE_fireAlerts($lat, $lng, $type, $value, $time);

    // Input all additions above this point.
    $entry .= "\n";
    return $entry;
}

function SE_timestamp($time){
    return date("d/m/Y h:i:sa", $time).": "; // Datestamp
}
function SE_temperature($lat, $lng, $type, $value, $time){
    $text = "";
    if($type == "temp"){
        $text .= "Temperature at ($lat, $lng) is $value°C ";
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

// Extracts csv
function process_data($dat){
  $row = explode(";", $dat);
  if ($row[0] == "lat,lng,stype,value,time"){
    for ($i=1;$i<sizeof($row);$i++){
      $tmp = explode(",", $row);
      $lat = $tmp[0];
      $lng = $tmp[1];
      $type = $tmp[2];
      $value = $tmp[3];
      $timestamp = $tmp[4];
      // add new data to database
    	$db = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
    	$sqlquery = "INSERT INTO sensor_data(`id`, `lat`, `lng`, `sType`, `sName`, `sValue`, `time`)VALUES(NULL, '$lat', '$lng', '$type', 'N/A', '$value', '$timestamp')";
    	$result = $db->query($sqlquery);
    	$db->close();

      // add new entire to log file.
    	// Technical log, this shows raw data input, key system info, ip,  etc.
    	$entry = time().": got data(lat>".$lat.",lng>".$lng.",type>".$type.",value>".$value.",timestamp>".$timestamp.") from ".$_SERVER['REMOTE_ADDR']. " using passcode '".$pass."' ecrypted as '".$_GET['pass']."', private key: '".$pri.",".$n."'. End of entry.\n";
    	$TechLog = "technical.log";
    	file_put_contents($TechLog, $entry, FILE_APPEND);
    	// Status log file
      $logentry = add_status_entry($lat, $lng, $type, $value, $time);
      $logfile = "status.log";
      file_put_contents($logfile, $logentry, FILE_APPEND);
    }
  }else{echo "incorrect csv format";}
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

  echo $my_keys[1] . "," . $my_keys[2];
}

if ($stage == 2){
  $pass = decrypt($_GET['pass']);
  // Get acceptable password.
  $myFile = "keys/.htaccepted-passcode";
  $fh = fopen($myFile, 'r');
  $c_pass = fread($fh, filesize($myFile));
  fclose($fh);
  // check if passcode correct
  if ($c_pass != $pass){
	die("Access Denied"); // stop on incorrect passcode
  }else{
	// countunie
	// Get all other data.
    /*//This was before the data was transmitted in csv format.
  	$lat = $_GET['lat'];
  	$lng = $_GET['lng'];
  	$type = $_GET['type'];
  	$value = $_GET['value'];
  	$timestamp = $_GET['time'];
  	// add new data to database
  	$db = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
  	$sqlquery = "INSERT INTO sensor_data(`id`, `lat`, `lng`, `sType`, `sName`, `sValue`, `time`)VALUES(NULL, '$lat', '$lng', '$type', 'N/A', '$value', '$timestamp')";
  	$result = $db->query($sqlquery);
  	$db->close();

    // add new entire to log file.
  	// Technical log, this shows raw data input, key system info, ip,  etc.
  	$entry = time().": got data(lat>".$lat.",lng>".$lng.",type>".$type.",value>".$value.",timestamp>".$timestamp.") from ".$_SERVER['REMOTE_ADDR']. " using passcode '".$pass."' ecrypted as '".$_GET['pass']."', private key: '".$pri.",".$n."'. End of entry.\n";
  	$TechLog = "technical.log";
  	file_put_contents($TechLog, $entry, FILE_APPEND);
  	// Status log file
    $logentry = add_status_entry($lat, $lng, $type, $value, $time);
    $logfile = "status.log";
    file_put_contents($logfile, $logentry, FILE_APPEND);*/
    process_data($_GET['dat']);
  }
	// Tell collector success
	echo "success";
  }

}
?>
