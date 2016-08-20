<?php

//error_reporting(E_ALL);
//ini_set('display_errors', True);

// The sole purpose of this script is to handle the options form output.
// It is designed to take the get ids of the newly submitted options form,
// and old url, then decide which is most up to date, passing that back
// in the new get line.

// Get all get parameters. This is old url.
$old = array(
$_GET['lineweight'],
$_GET['pointsize'],
$_GET['lnclr0'],
$_GET['lnclr1'],
$_GET['lnclr2'],
$_GET['lnclr3'],
$_GET['lnclr4'],
$_GET['gridcolour'],
$_GET['keycolour'],
$_GET['xlbl'],
$_GET['ylbl'],
$_GET['start_date'],
$_GET['start_time'],
$_GET['end_date'],
$_GET['end_time'],
$_GET['type'],
$_GET['lat'],
$_GET['lng']
);

// note hash must be stripped from colours

$new = array(
$_POST['lineweight'],
$_POST['pointsize'],
trim($_POST['lnclr0'], "#"),
trim($_POST['lnclr1'], "#"),
trim($_POST['lnclr2'], "#"),
trim($_POST['lnclr3'], "#"),
trim($_POST['lnclr4'], "#"),
trim($_POST['gridcolour'], "#"),
$_POST['keycolour'],
$_POST['xlbl'],
$_POST['ylbl'],
$_POST['start_date'],
$_POST['start_time'],
$_POST['end_date'],
$_POST['end_time'],
$_POST['type'],
$_POST['lat'],
$_POST['lng']
);

$id = array(
     'lineweight',
     'pointsize',
     'lnclr0',
     'lnclr1',
     'lnclr2',
     'lnclr3',
     'lnclr4',
     'gridcolour',
     'keycolour',
     'xlbl',
     'ylbl',
     'start_date',
     'start_time',
     'end_date',
     'end_time',
     'type',
     'lat',
     'lng'
);

$redirect = "index.php?";
for($i=0;$i<sizeof($old);$i++){
    if ($new[$i] != ""){
        $redirect .= $id[$i] . "=" . $new[$i] . "&";
    }else{
        if ($old[$i] != ""){
            $redirect .= $id[$i] . "=" . $old[$i] . "&";
        }
    }
}

// Type work differently...
// There can be up to 5 data types, they are submitted as var<num> through post.
// There is no need to keep pre data as index.php creates a page with old data already selected.

$allTypes = array();

$db = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
else{
    //echo "connection successful.<br>";
}

// Location based on get
$getLat = $_GET['lat'];
$getLng = $_GET['lng'];
if ($getLat == ""){
    $location = 'not_set';
}else{
    $location = array($getLat, $getLng);
}

$sql = "SELECT lat, lng, sType FROM sensor_data";
$result = $db->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        if (($row['lat'] == $location[0]) and ($row['lng'] == $location[1])){
            if (!(in_array($row['sType'], $allTypes, FALSE))){
                array_push($allTypes, $row['sType']);
            }
        }
    }
}

$typesRedirect = "type=";

if ($_POST['var0']){
    $typesRedirect .= $allTypes[0] . ",";
}
if ($_POST['var1']){
    $typesRedirect .= $allTypes[1] . ",";
}
if ($_POST['var2']){
    $typesRedirect .= $allTypes[2] . ",";
}
if ($_POST['var3']){
    $typesRedirect .= $allTypes[3] . ",";
}
if ($_POST['var4']){
    $typesRedirect .= $allTypes[4];
}

$redirect .= $typesRedirect;

//echo $redirect;
header("Location: $redirect");

?>
