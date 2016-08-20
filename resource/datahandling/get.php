
<?php
phpinfo();
/*

// This script is designed to recieve data from a collector node.
// It uses a RSA cryptosystem to ensure it is not feed false data.
// It is designed to be called multiple times in a handshaking process
// stage defines which stage it is and therefore how it respones.
// it will echo out any response, this is not desinged to be view by humans.

// &&&& FUNCTION DECLEARATION &&&&&

// Credit: http://www.stoimen.com/blog/2012/05/08/computer-algorithms-determine-if-a-number-is-prime/
function isPrime($n) {
  // Elimate all even
  if ($n % 2 != 0){
	// If 2
	$i = 2;
	if ($n == 2) {
	 return true;
	}
	// Elimate all multi3
	if ($n % 3 != 0){
	  // Brute force check
	 while ($i < $n) {
	  if ($n % $i == 0) {
	   return false;
	  }
	  $i++;
	 }
	 return true;
	}
  }else{
	return false;
  }
 }

function generate_prime($min, $max){
  $done = false;
  while($done != true){
    $p = rand($min, $max);
    if (isPrime($p)==true){
      $done = true;
    }else{
      $done = false;
    }
  }
  return $p;
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
  // This generates an RSA pub/pri key pair.
  // https://en.wikipedia.org/wiki/RSA_%28cryptosystem%29#Example
  $primeA = generate_prime(255,4096); // between 8 & 12 bit
  $primeB = generate_prime(255,4096);
  $n = $primeA * $primeB;
  $totient = ($primeA - 1) * ($primeB - 1);
  $copri = generate_prime(1,$totient);
  $pri = invmod($copri, $totient);
  return array($pri, $n, $copri);
}

function decrypt($c){
  $myFile = "keys/.ht-timeout.dat";
  $fh = fopen($myFile, 'r');
  $timeout = fread($fh, filesize($myFile));
  fclose($fh);

  // Check if the keys have expired.
  if (($timeout + 50) < time()){
	die("ERR: Timeout exceeded");
  }else{
	// If still current
	$myFile = "keys/.ht-privatePt1.key";
	$fh = fopen($myFile, 'r');
	$pri = fread($fh, filesize($myFile));
	fclose($fh);

	$myFile = "keys/.ht-privatePt2.key";
	$fh = fopen($myFile, 'r');
	$n = fread($fh, filesize($myFile));
	fclose($fh);

	// Enable gmp in php.ini. This allows exact maths operations - no auto rounding.

	$tmp = gmp_pow($c, $pri);

	return gmp_mod($tmp, $n);
  }
}

// &&&&& MAIN CODE &&&&&

// This defines the transmission stage
$stage = $_GET['stage'];


// Stage one is getting the nodes public key, and sending server pub key.
// Server keys are randomly generated, then saved, with a timeout. If
// they are not used with th timeout, a new one is generated and the process
// must be restarted.
if ($stage == 1){
  $my_keys = key_gen();

  $myFile2 = "keys/.ht-privatePt1.key";
  $myFileLink2 = fopen($myFile2, 'w+') or die("ERR: Cannot write key files");
  $newContents = $my_keys[0];
  fwrite($myFileLink2, $newContents);
  fclose($myFileLink2);

  $myFile2 = "keys/.ht-privatePt2.key";
  $myFileLink2 = fopen($myFile2, 'w+') or die("ERR: Cannot write key files");
  $newContents = $my_keys[1];
  fwrite($myFileLink2, $newContents);
  fclose($myFileLink2);

  $myFile2 = "keys/.ht-timeout.dat";
  $myFileLink2 = fopen($myFile2, 'w+') or die("ERR: Cannot write time file");
  $newContents = time();
  fwrite($myFileLink2, $newContents);
  fclose($myFileLink2);

  echo $my_keys[1] . "," . $my_keys[2];
}
if ($stage == 2){
  $pass = decrypt($_GET['pass']);
  // Get acceptable password.
  $myFile = "keys/.ht-accepted-passcode";
  $fh = fopen($myFile, 'r');
  $c_pass = fread($fh, filesize($myFile));
  fclose($fh);
  // check if passcode correct
  if ($c_pass != $pass){
	die("Access Denied"); // stop on incorrect passcode
  }else{
	// countunie
	// Get all other data.
	$lat = $_GET['lat'];
	$lng = $_GET['lng'];
	$type = $_GET['type'];
	$value = $_GET['value'];
	$time = $_GET['time'];
	// add new data to database

	// add new entire to log file.

  }

}

*/
?>
