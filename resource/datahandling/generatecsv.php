<?php
    //connect to db
    $db = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");    // connection error handler
	if ($db->connect_error) {
        echo "Oops, there has been a problem connecting to the database, this will be logged.";
        // Add logging. 
        die($db->connect_error);
    }
    // pull data.
    $sql = "SELECT sValue, time, lat, lng, sType FROM sensor_data";
    $result = $db->query($sql);
    if ($_GET['sensor'] == "all"){
		//echo "Fetching data...<br>";
        $csv = "Lat,Lng,Type,Value,Time\r\n";
        $value =array();
        $type =array();
        $lat =array();
        $lng =array();
        $time =array();
        if ($result->num_rows > 0) {
			while($row = $result->fetch_assoc()) {
                array_push($value, $row['sValue']);
                array_push($time, $row['time']);
                array_push($type, $row['sType']);
                array_push($lat, $row['lat']);
                array_push($lng, $row['lng']);
            }
        }
        $db->close();
		//echo "Generating CSV from data...<br>";
        for ($i=0;$i<sizeof($time);$i++){
            $csv .= $lat[$i].",".$lng[$i].",".$type[$i].",".$value[$i].",".$time[$i]."\r\n";
        }
		// Write file for download
		//echo "Writing CSV file...<br>";
        $f = fopen("Orokonui_Sensor_data.csv", "w") or die("Error writing CSV.");
		fwrite($f, $csv);
		fclose($f);
		//echo "Redirecting...";
		header("Location: Orokonui_Sensor_data.csv");
    }
?>
