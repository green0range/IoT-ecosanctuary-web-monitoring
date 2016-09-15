<!DOCTYPE html>
<html>
	<head>
	<meta charset="utf-8">
	<title>Data - Orokonui monitoring</title>
	<!-- Import Styles CSS -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>

	<meta charset='UTF-8'>

	<!-- date picker css-->
	<link rel='stylesheet' type='text/css' href='resource/lib/Pikaday/css/pikaday.css'>

	<?php
		// Read user agent and detect if mobile or not.
		if (strpos($_SERVER['HTTP_USER_AGENT'], "Mobile") > 0){ // CHANGE TO MOBILE
			if ($_GET['force_mode'] == 'DESKTOP'){
				$mobile = 0;
				echo "<link rel='stylesheet' type='text/css' href='style.css'>";
			}else{
				$mobile = 1;
				echo "<link rel='stylesheet' type='text/css' href='mobile.css'>";
			}
		}else{
			if ($_GET['force_mode'] == 'MOBILE'){
				$mobile = 1;
				echo "<link rel='stylesheet' type='text/css' href='mobile.css'>";
			}else{
				$mobile = 0;
				echo "<link rel='stylesheet' type='text/css' href='style.css' media='screen and (min-device-width: 600px)'/>\n<link rel='stylesheet' type='text/css' href='mobile.css' media='screen and (max-device-width: 599px)'/>";
			}
		}
	?>
	<!-- Font Import -->
	<link href='https://fonts.googleapis.com/css?family=Righteous' rel='stylesheet' type='text/css'>
	<link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>

	<!-- Settings script -->
	<script>
	// Toggles setting div between size 0 and full.
		function settingsClick(){
			var set = document.getElementById("options");
			if (set.clientWidth == 0){
				set.setAttribute("style","width: 100%; float: right; display: 'inline'; overflow: hidden; align: right;");
			} else {
				set.setAttribute("style","width: 0px; float: right; display: 'inline'; overflow: hidden; align: right; height: 0px;");
			}
		}
	</script>
	<!--PHP, setup the database and graphing variables-->
	<?php

		//error_reporting(E_ALL);
		//ini_set('display_errors', True);

		// Setup mySQL connect
		// Real connection
		$db = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
		//@mysqli_select_db("orakanui");
		// Add error handler here
		if ($db->connect_error) {
			//die("Connection failed: " . $db->connect_error);
		}
		else{
			//echo "connection successful.<br>";
		}
		// get data
		// Prepare arrays
		$seriesy = array();
		$seriesy1 = array();
		$seriesy2 = array();
		$seriesy3 = array();
		$seriesy4 = array();
		$seriesx = array();
		$seriesx1 = array();
		$seriesx2 = array();
		$seriesx3 = array();
		$seriesx4 = array();
		$sType = array();
		$sName = array();
		$lat = array();
		$lng = array();
		$allTypes = array();

		// Defaults
		$lineWeight = 1;
		$pointSize = 4;
		$lineColour = 'FF3333';
		$lineColour1 = '00FF00';
		$lineColour2 = '0000FF';
		$lineColour3 = 'FE2EF7';
		$lineColour4 = '2EFEF7';


		$keyColour = '000000';
		$gridColour = 'CCCCCC';
		$xLabels = -1;
		$yLabels = -1;

		// Replace default with get options
		if ($_GET['lineweight'] != ""){
			$lineWeight = $_GET['lineweight'];
		}
		if ($_GET['pointsize'] != ""){
			$pointSize = $_GET['pointsize'];
		}

		if ($_GET['lnclr0'] != ""){
			$lineColour = $_GET['lnclr0'];
		}
		if ($_GET['lnclr1'] != ""){
			$lineColour1 = $_GET['lnclr1'];
		}
		if ($_GET['lnclr2'] != ""){
			$lineColour2 = $_GET['lnclr2'];
		}
		if ($_GET['lnclr3'] != ""){
			$lineColour3 = $_GET['lnclr3'];
		}
		if ($_GET['lnclr4'] != ""){
			$lineColour4 = $_GET['lnclr4'];
		}

		if ($_GET['gridcolour'] != ""){
			$gridColour = $_GET['gridcolour'];
		}
		if ($_GET['keycolour'] != ""){
			$keyColour = $_GET['keycolour'];
		}
		if ($_GET['xlbl'] != ""){
			$xLabels = $_GET['xlbl'];
		}
		if ($_GET['ylbl'] != ""){
			$yLabel = $_GET['ylbl'];
		}

		function convert_am_pm_time($t){
			if ($t=="midnight"){return 0;}
                        if ($t=="1am"){return 3600;}
                        if ($t=="2am"){return 3600*2;}
                        if ($t=="3am"){return 3600*3;}
                        if ($t=="4am"){return 3600*4;}
                        if ($t=="5am"){return 3600*5;}
                        if ($t=="6am"){return 3600*6;}
                        if ($t=="7am"){return 3600*7;}
                        if ($t=="8am"){return 3600*8;}
                        if ($t=="9am"){return 3600*9;}
			if ($t=="10am"){return 3600*10;}
                        if ($t=="11am"){return 3600*11;}
                        if ($t=="midday"){return 3600*12;}
                        if ($t=="1pm"){return 3600*13;}
                        if ($t=="2pm"){return 3600*14;}
                        if ($t=="3pm"){return 3600*15;}
                        if ($t=="4pm"){return 3600*16;}
                        if ($t=="5pm"){return 3600*17;}
                        if ($t=="6pm"){return 3600*18;}
			if ($t=="7pm"){return 3600*19;}
                        if ($t=="8pm"){return 3600*20;}
                        if ($t=="9pm"){return 3600*21;}
                        if ($t=="10pm"){return 3600*22;}
                        if ($t=="11pm"){return 3600*23;}

		}

		// Time and date stuff
		if ($_GET['start_date'] != ""){
			$startTimeUNIX = strtotime($_GET['start_date']);
			$startTimeUNIX .= convert_am_pm_time($_GET['start_time']);
			echo $startTimeUNIX;
			$startTimeEnabled = 5;
		}

		// Time and date stuff
                if ($_GET['end_date'] != ""){
                        $endTimeUNIX = strtotime($_GET['end_date']);
                        $endTimeUNIX .= convert_am_pm_time($_GET['end_time']);
                        echo $endTimeUNIX;
			$endTimeEnabled = 5;
                }

		/*if ($_GET['enTmd'] != ""){
			$endTimeDay = $_GET['enTmd'];
			$endTimeEnabled++;
		}
		if ($_GET['enTmm'] != ""){
			$endTimeMonth = $_GET['enTmm'];
			$endTimeEnabled++;
		}
		if ($_GET['enTmy'] != ""){
			$endTimeYear = $_GET['enTmy'];
			$endTimeEnabled++;
		}
		if ($_GET['enTmh'] != ""){
			$endTimeHour = $_GET['enTmh'];
			$endTimeEnabled++;
		}
		if ($_GET['enTmmi'] != ""){
			$endTimeMinute = $_GET['enTmmi'];
			$endTimeEnabled++;
		}
		if ($endTimeEnabled > 4){
			// build time string
			$timestring = $endTimeHour . ":" . $endTimeMinute . " " . $endTimeDay;
			$dateObj = DateTime::createFromFormat('!m', $endTimeMonth);
			$monthName = $dateObj->format('F');
			$timestring .= " " . $monthName . " " . $endTimeYear;
			$endTimeUNIX = strtotime($timestring);
			echo $endTimeUNIX;
		}*/



		// Select data types, based on get:
		$selectedTypes = array();
		if ($_GET['type'] == ""){
			array_push($selectedTypes, "temp"); // Default to temperature readings.
		} else {
			$selectedTypes = explode(",", $_GET['type']); // different data types sepportated by comma
		}

		// Location based on get
		$getLat = $_GET['lat'];
		$getLng = $_GET['lng'];
		if ($getLat == ""){
			$location = 'not_set';
		}else{
			$location = array($getLat, $getLng);
		}

		$sql = "SELECT sValue, time, sName, lat, lng, sType FROM sensor_data";
		$result = $db->query($sql);

		if ($result->num_rows > 0) {
			while($row = $result->fetch_assoc()) {
				if (($row['lat'] == $location[0]) and ($row['lng'] == $location[1])){
					// colects all data types for type options menu
					if (!(in_array($row['sType'], $allTypes, FALSE))){
							array_push($allTypes, $row['sType']);
						}
					for ($i=0;$i<sizeof($selectedTypes);$i++){
						if ($selectedTypes[$i] == $row['sType']){
							if ($i == 0){
								array_push($seriesy, $row["sValue"]);
								array_push($seriesx, $row["time"]);
								array_push($sName, $row["sName"]);
							}
							if ($i == 1){
								array_push($seriesy1, $row["sValue"]);
								array_push($seriesx1, $row["time"]);
							}
							if ($i == 2){
								array_push($seriesy2, $row["sValue"]);
								array_push($seriesx2, $row["time"]);
							}
							if ($i == 3){
								array_push($seriesy3, $row["sValue"]);
								array_push($seriesx3, $row["time"]);
							}
							if ($i == 4){
								array_push($series4, $row["sValue"]);
								array_push($seriesx4, $row["time"]);
							}
						}
					}
				}
				if ((! in_array($row['lat'], $lat)) or (! in_array($row['lng'], $lng))){ // Check not already accounted for
					array_push($lat, $row['lat']); // Add new lat
					array_push($lng, $row['lng']);
				}
			}
		}
		$db->close();
		/*
		//Find best time range (min to max of all time series)
		$timemax = array(max($seriesx), max($seriesx1), max($seriesx2), max($seriesx3), max($seriesx4));
		$timemin = array(min($seriesx), min($seriesx1), min($seriesx2), min($seriesx3), min($seriesx4));
		$seriesx = array($timemin, $timemax);*/
		// Take off times not in start/end time range
		if($startTimeEnabled > 4){
			for($i=0;$i<sizeof($seriesx);$i++){
				if($seriesx[$i] < $startTimeUNIX){
					$seriesx[$i] = null;
					$seriesy[$i] = null;
				}
			}
		}
		if($endTimeEnabled > 4){
			for($i=0;$i<sizeof($seriesx);$i++){
				if($seriesx[$i] > $endTimeUNIX){
					$seriesx[$i] = null;
					$seriesy[$i] = null;
				}
			}
		}
		// Convert unix time to a more resomable number
		$lowest = $seriesx[0]; // sets up a baseline time
		if ($lowest == null){$lowest = 0;} // If lowest has no value, JavaScript crashes.
		for($i=0;$i<sizeof($seriesx);$i++){
			// Assumes first value is lowest
			$seriesx[$i] = ($seriesx[$i] + -$lowest); #sub low
		}
		for($i=0;$i<sizeof($seriesx1);$i++){
			// Assumes first value is lowest
			$seriesx1[$i] = ($seriesx1[$i] + -$lowest); #sub low
		}
		for($i=0;$i<sizeof($seriesx2);$i++){
			// Assumes first value is lowest
			$seriesx2[$i] = ($seriesx2[$i] + -$lowest); #sub low
		}
		for($i=0;$i<sizeof($seriesx3);$i++){
			// Assumes first value is lowest
			$seriesx3[$i] = ($seriesx3[$i] + -$lowest); #sub low
		}
		for($i=0;$i<sizeof($seriesx4);$i++){
			// Assumes first value is lowest
			$seriesx4[$i] = ($seriesx4[$i] + -$lowest); #sub low
		}
		//Canvas size setting
		$canvasx = 200;
		$canvasy = 500;
		$plotsize = 5;
		$ydatapoints = 5;
	?>
	</head>
	<body onload="makeGraph()">

		<!--Comment this out when system is complete. Uncomment during mainince-->
		<!--<div id="warning">
			<p><strong>Wanring:</strong> This system is under development. Any data is likely to
				be false test data. Any about information may refer to furture plans.</p>
		</div>-->

		<!--All body is in contents div, then applicable div, i.e header, map, etc.-->
		<!--Contents sets up styling constants, i.e page with, while others setup styling
		variables relevant to ech div, i.e text colour-->
		<div id="header"> <!--I decided to but the header outside of the content wrapper so it would strech across the entire screen.-->
			<img src="resource/header.png" width="100%">
			<p><a href="index.php">Data</a> &nbsp; | &nbsp; <a href="status.php">Status</a> &nbsp; | &nbsp; <a href="about.php">About</a> &nbsp; | &nbsp; <a href="rules.php">Setup</a> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </p>
			<hr>
		</div>
		<br>
		<div id="content">

			<div id="mapandgraph">
				<div id="map">
				<p>Select where you would like to see data from:</p>
					<!-- Hold the imported google map -->
					<div id="mapcontainer" style="height:600;">
					</div>
					<!-- script to setup map -->
					<script>
						function newSensorLocation(lat, lng) {
							if ((window.location.href.indexOf("?")==-1)) {
								location = "index.php?lat=" + lat.toString() + "&lng=" + lng.toString();
							}else{
								location = window.location.href + "&lat=" + lat.toString() + "&lng=" + lng.toString();
							}
						}
						function initMap() {
							var mapDiv = document.getElementById('mapcontainer');
							// Create the map object
							var map = new google.maps.Map(mapDiv, {center:{lat: -45.772000, lng: 170.5982972}, zoom: 15, mapTypeId: google.maps.MapTypeId.HYBRID, scrollwheel: false});
							// Create markers
							<?php
								for($i=0;$i<sizeof($lat);$i++){
									echo "var marker$i = new google.maps.Marker({ Position: new google.maps.LatLng($lat[$i], $lng[$i]), map: map, icon: 'resource/sensor-icon.png'});";
									echo "google.maps.event.addListener(marker$i, 'click', function(){newSensorLocation($lat[$i], $lng[$i]);});";
								}
							?>
						}
					</script>
					<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDTmTBVYGUcAaFM25-sM41NlK_ZClKGhxw&callback=initMap" async defer>
					</script>
				</div>
				<div id="data">
					<script>
						var sensorlocation = <?php if($location == "not_set"){echo "'not_set'";}else{echo "($location[0], $location[1])";} ?>;
						if (sensorlocation != "not_set") {

<?php
// gets hr sensor names
$db = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
$q = "SELECT * FROM sensor_config";
$r = $db->query($q);
while ($row = $r->fetch_assoc())
{
	if ($location[0] == $row['lat'])
	{
		$nodeName = $row['hr_name'];
	}
}
$selected_str = $selectedTypes[0];
if (selectedTypes[1] != "")
{
	$selected_str .= ", ".$selectedTypes[1];
}
if (selectedTypes[2] != "")
{
        $selected_str .= ", ".$selectedTypes[2];
}
if (selectedTypes[3] != "")
{
        $selected_str .= ", ".$selectedTypes[3];
}
if (selectedTypes[4] != "")
{
        $selected_str .= ", ".$selectedTypes[4];
}
?>
							document.write("<h2><?php echo $_GET['type']; ?> vs Time, for <?php echo $nodeName; ?> (<?php echo $location[0]; ?>, <?php echo $location[1]; ?>)");
						}
					</script>
					<!--contains options button-->
					<div id="dataoptionsbar", align="right">
						<img src="resource/options.png", title="Settings", width='64px', height='64px', onclick="settingsClick()">
						<br><div id='help-button' onclick='settingsClick()'>Settings </div>
					</div>
				<div id="graphDiv" style="overflow: hidden; width: 50%;">
					<!-- Graph options-->
					<div id="options", style="width: 0px; float: right; overflow: hidden; align: right; height: 0px;">
						<form style="display:inline;", method="post", action="optionsHandler.php?<?php echo $_SERVER["QUERY_STRING"]; ?>">
							<!--Options

							This div has a width of 0, which effectively hides it. When the options button is pressed, js changes the width, making this div visiable.
							-->
							<hr>
							<div id="options.date">
								<fieldset>
								<legend>Time</legend>
								<p><strong>Start:</strong></p>
								<script src='resource/lib/Pikaday/pikaday.js'></script>
								<p>
								<input id='start_date' name='start_date'></input>
								<script>
									var picker = new Pikaday({field:document.getElementById('start_date')});
								</script>
								</p>
								<p><select id='start_time' name='start_time'>
									<option>Midnight</option>
									<option>1am</option>
                                                                        <option>2am</option>
                                                                        <option>3am</option>
                                                                        <option>4am</option>
                                                                        <option>5am</option>
                                                                        <option>6am</option>
                                                                        <option>7am</option>
                                                                        <option>8am</option>
                                                                        <option>9am</option>
                                                                        <option>10am</option>
                                                                        <option>11am</option>
                                                                        <option>Midday</option>
									<option>1pm</option>
                                                                        <option>2pm</option>
                                                                        <option>3pm</option>
                                                                        <option>4pm</option>
                                                                        <option>5pm</option>
                                                                        <option>6pm</option>
                                                                        <option>7pm</option>
                                                                        <option>8pm</option>
                                                                        <option>9pm</option>
                                                                        <option>10pm</option>
                                                                        <option>11pm</option>
								</select></p>
								<p><strong>End:</strong></p>
								<input name='end_date' id='end_date'></input>
                                                                <script>
                                                                        var picker2 = new Pikaday({field:document.getElementById('end_date')});
                                                                </script>
                                                                </p>
                                                                <p><select name='end_time' id='end_time'>
                                                                        <option>Midnight</option>
                                                                        <option>1am</option>
                                                                        <option>2am</option>
                                                                        <option>3am</option>
                                                                        <option>4am</option>
                                                                        <option>5am</option>
                                                                        <option>6am</option>
                                                                        <option>7am</option>
                                                                        <option>8am</option>
                                                                        <option>9am</option>
                                                                        <option>10am</option>
                                                                        <option>11am</option>
                                                                        <option>Midday</option>
                                                                        <option>1pm</option>
                                                                        <option>2pm</option>
                                                                        <option>3pm</option>
                                                                        <option>4pm</option>
                                                                        <option>5pm</option>
                                                                        <option>6pm</option>
                                                                        <option>7pm</option>
                                                                        <option>8pm</option>
                                                                        <option>9pm</option>
                                                                        <option>10pm</option>
                                                                        <option>11pm</option>
                                                                </select></p>
								</fieldset>
							</div>
							<script src='resource/lib/colourpicker.js'></script>
							<div id="options.variables">'
								<fieldset>
									<legend>Variables</legend>
									<p>Tick the variables you want displayed.</p>
									<?php
										for($i=0;$i<sizeof($allTypes);$i++){
											if (in_array($allTypes[$i], $selectedTypes, FALSE)){
												echo "<div id='vardiv$i'>" .$allTypes[$i] . " : " . "<input type='checkbox' checked name='var$i'></input> &nbsp; &nbsp; &nbsp; Colour <input type='text' name='lnclr$i'></input></p></div><script>loadColourPicker(document.getElementById('vardiv$i'), document.getElementById('lnclr$i'));</script>";
											}else{
												echo "<div id='vardiv$i'>". $allTypes[$i] . " : " . "<input type='checkbox' name='var$i'></input> &nbsp; &nbsp; &nbsp; Colour <input type='text' name='lnclr$i'></input></p></div><script>loadColourPicker(document.getElementById('vardiv$i'),document.getElementById('lnclr$i'));</script>";
											}
										}
									?>
								</fieldset>
							</div>
							<div id="options.customisation">
								<fieldset>
								<legend>Customisation</legend>
								<p>Grid colour: <div id='grid_col_div'><input type="text", id="grid_colour" name="gridcolour"></input></div></p>
								<p>key colour: <div id='key_col_div'><input type="text", id="key_colour" name="keycolour"></input></div></p>
								<script>
									loadColourPicker(document.getElementById('key_col_div'), document.getElementById('key_colour'));
									loadColourPicker(document.getElementById('grid_col_div'), document.getElementById('grid_colour'));
								</script>
								<p>Line weight:<select id='lineweight'><option name='1'>Thin</option><option name='2'>Meduim</option><option name='3'>Thick</option></select></p>
								<p>Number of x markers:<input type="number" maxlenght='2' name="xlbl"></input></p>
								<p>Number of y markers:<input type="number" maxlenght='2' name="ylbl"></input></p>
								</fieldset>
							</div>
							<div id="options.submit">
								<input class='button' value='Go!' type="submit"></input>
							</div>
						</form>
						<hr>
					</div>
				</div>
					<!--Graph data here-->
					<canvas id="graph"height="<?php echo $canvasy + 5; ?>" width="25%"></canvas>
					<div id="graph.key" style="width: 0px; overflow: hidden;">
					<p><?php
						for ($i=0;$i<sizeof($selectedTypes);$i++){
							if ($i==0){
								$tmp = $lineColour;
							}
							if ($i==1){
                                $tmp = $lineColour1;
							}
							if ($i==2){
								$tmp = $lineColour2;
                            }
							if ($i==3){
                                $tmp = $lineColour3;
                            }
							if ($i==4){
                                $tmp = $lineColour4;
                            }
							echo "<span style='color:$tmp;'>" . $selectedTypes[$i] . "</span><br>";
						}
					?></p>
					</div>
				</div>
				<div id="help-button"><a href="resource/help.php#graphing">Help</a></div>
				<hr>
				<div id="table" style="width: 0px; overflow: hidden;">
					<br><br>
					<table style="width:100%" border="1">
						<?php
							$max_table_size = 10;
							// Start title row
							echo "<tr>";
							for ($i=0; $i < sizeof($selectedTypes); $i++){
								// Start a column
								//echo "<tr>";
								// Add rows for column
								// Title row
								if($i==0){
									echo "<th>Time</th>";
								}else{
									$tmp = $selectedTypes[$i-1];
									echo "<th>$tmp</th>";
								}
							}
							//end title row
							echo "</tr>";

							// Add data
							$myurl=$_SERVER['REQUEST_URI'];
							if ($_GET['tablepage']!=''){
								$page=$_GET['tablepage'];
							}
							if(sizeof($seriesx)<$max_table_size){
								$rowcount = sizeof($seriesx);
							}else{
								$rowcount = $max_table_size;
								echo "Showing ".($page*$max_table_size)." to ".(($page*$max_table_size)+$max_table_size)." of ".sizeof($seriesx).". <a href='".$myurl."&tablepage=".($page+1)."'>Next</a> | <a href='".$myurl."&tablepage=0'>Start</a>";
							}
							for ($i=($page*$max_table_size); $i < (($page*$max_table_size)+$max_table_size); $i++){
								echo "<tr>";
								$xseriestime = $seriesx[$i]+$lowest;
								$xseriestimeform = date('d-m-y, H:i:s',$xseriestime);
								echo "<td>$xseriestimeform</td>";
								for ($j=0; $j < sizeof($selectedTypes); $j++){
									if ($j == 0){
										echo "<td>$seriesy[$i]</td>";
									}
									if ($j == 1){
										echo "<td>$seriesy1[$i]</td>";
									}
									if ($j == 2){
										echo "<td>$seriesy2[$i]</td>";
									}
									if ($j == 3){
										echo "<td>$seriesy2[$i]</td>";
									}
									if ($j == 4){
										echo "<td>$seriesy4[$i]</td>";
									}
								}
								echo "</tr>";
							}
						 ?>
					</table>
					<br><br><p>Download CSV: <a href="resource/datahandling/generatecsv.php?sensor=all">All sensors</a></p><br><br>
				</div>
		</div>
			<div id="footer">
				<hr>
				<p>Design by <a href="https://twitter.com/WilliamSatterth">William Satterthwaite</a>, 2016. Fonts from <a href="https://www.google.com/fonts">google.com/fonts</a>, map from <a href="https://maps.google.com">maps.google.com</a>. All other context under
				<a href="https://creativecommons.org/licenses/by/4.0/">Creative Commons</a>.</p>
				<br>
			</div>
			<!-- This is used to store resources for drawing to canvas, it is hidden from view, yet still loads images ready for use.-->
			<div id="resources", style="width: 0px; overflow: hidden; height: 0px;">
				<img src="resource/arrow_w-n.png" id="arrow_w-n">
				<img src="resource/sensor-icon.png" id="sensor-icon">
				<img src="resource/arrow_e-s.png" id="arrow_e-s">
				<img src="resource/left_arrow.png" id="left_arrow">
			</div>
		</div>
		<!-- PHP, draw graph -->
		<!-- All relaevant database data is passed to JavaScript -->
		<script>
		// Get db data from php

			function makeGraph(){
				if (sensorlocation != "not_set") {
					//Show data table
					document.getElementById("table").setAttribute("style","width:100%");
					document.getElementById("graph.key").setAttribute("style","width:100%");
                    var canvasy = <?php echo $canvasy; ?>;
					// Change canvas size acording to screen width
					var container = document.getElementById("graphDiv");
					var canvasx = (container.clientWidth);
					<?php
						echo "var yseries = [];";
						echo "var yseries1 = [];";
						echo "var yseries2 = [];";
						echo "var yseries3 = [];";
						echo "var yseries4 = [];";
						echo "var xseries = [];";
						echo "var xseries1 = [];";
						echo "var xseries2 = [];";
						echo "var xseries3 = [];";
						echo "var xseries4 = [];";
						echo "var yseriescount = 0;
						";
						if ($selectedTypes[0]){
							for ($i=0;$i<sizeof($seriesy);$i++){
								echo "yseries.push($seriesy[$i]); xseries.push($seriesx[$i]);";
							}
							echo "yseriescount++;";
						}
						if ($selectedTypes[1]){
							for ($i=0;$i<sizeof($seriesy1);$i++){
								echo "yseries1.push($seriesy1[$i]); xseries1.push($seriesx1[$i]);";
							}
							echo "yseriescount++;";
						}
						if ($selectedTypes[2]){
							for ($i=0;$i<sizeof($seriesy2);$i++){
								echo "yseries2.push($seriesy2[$i]); xseries2.push($seriesx2[$i]);";
							}
							echo "yseriescount++;";
						}
						if ($selectedTypes[3]){
							for ($i=0;$i<sizeof($seriesy3);$i++){
								echo "yseries3.push($seriesy3[$i]); xseries3.push($seriesx3[$i]);";
							}
							echo "yseriescount++;";
						}
						if ($selectedTypes[4]){
							for ($i=0;$i<sizeof($seriesy4);$i++){
								echo "yseries4.push($seriesy4[$i]); xseries4.push($seriesx4[$i]);";
							}
							echo "yseriescount++;";
						}
					?>
					var plotsize = <?php echo $pointSize; ?>;
					var canvas = document.getElementById("graph");
					var cd = canvas.getContext("2d");
					cd.canvas.width = canvasx;
					//alert(canvasx);
					cd.font="15px Georgia";
					cd.fillText("Time ->", canvasy, 0);
					cd.fillStyle = "<?php echo $keyColour; ?>";
					// Finds the highest for each series
					var highest = [0,0,0,0,0];
					for (var i=0;i<yseriescount;i++){
						if (i==0){
							for (var j=0;j<yseries.length;j++){
								if (highest[i] < yseries[j]) {
									highest[i] = yseries[j];
								}
							}
						}
						if (i==1){
							for (var j=0;j<yseries1.length;j++){
								if (highest[i] < yseries1[j]) {
									highest[i] = yseries1[j];
								}
							}
						}
						if (i==2){
							for (var j=0;j<yseries2.length;j++){
								if (highest[i] < yseries2[j]) {
									highest[i] = yseries2[j];
								}
							}
						}
						if (i==3){
							for (var j=0;j<yseries3.length;j++){
								if (highest[i] < yseries3[j]) {
									highest[i] = yseries3[j];
								}
							}
						}
						if (i==4){
							for (var j=0;j<yseries4.length;j++){
								if (highest[i] < yseries4[j]) {
									highest[i] = yseries4[j];
								}
							}
						}
					}
					// Finds the overall highest
					var tmp = 0;
					for (var i=0;i<highest.length;i++) {
                        if (tmp < highest[i]) {
                            tmp = highest[i];
                        }
                    }
					highest = tmp;
					var longest = 0;
					for (var i=0;i<xseries.length;i++){
						if (longest < xseries[i]) {
							longest = xseries[i];
						}
					}
					//Create Data points, scaled to size
					// Sacle y values to fill cnavas size
					var scale = canvasy / highest;
					var ypoint = [];
					var ypoint1 = [];
					var ypoint2 = [];
					var ypoint3 = [];
					var ypoint4 = [];
					for (var i=0;i<yseries.length;i++) {
						ypoint.push(canvasy-yseries[i]*scale);
					}
					for (var i=0;i<yseries1.length;i++) {
						ypoint1.push(canvasy-yseries1[i]*scale);
					}
					for (var i=0;i<yseries2.length;i++) {
						ypoint2.push(canvasy-yseries2[i]*scale);
					}
					for (var i=0;i<yseries3.length;i++) {
						ypoint3.push(canvasy-yseries3[i]*scale);
					}
					for (var i=0;i<yseries4.length;i++) {
						ypoint4.push(canvasy-yseries4[i]*scale);
					}
					// Sacle x values to fill cnavas size
					var scale = canvasx / longest;
					var xpoint = [];
					var xpoint1 = [];
					var xpoint2 = [];
					var xpoint3 = [];
					var xpoint4 = [];
					for (var i=0;i<xseries.length;i++) {
						xpoint.push(xseries[i]*scale);
					}
					for (var i=0;i<xseries1.length;i++) {
						xpoint1.push(xseries1[i]*scale);
					}
					for (var i=0;i<xseries2.length;i++) {
						xpoint2.push(xseries2[i]*scale);
					}
					for (var i=0;i<xseries3.length;i++) {
						xpoint3.push(xseries3[i]*scale);
					}
					for (var i=0;i<xseries4.length;i++) {
						xpoint4.push(xseries4[i]*scale);
					}
					// Add y gridlines and labels
					var ylabels = <?php echo $yLabels; ?>;
					if (ylabels == -1){
						for (var i=10;window.screen.availWidth < i*200;i--)
							ylabels = i;
					}
					cd.beginPath();
					cd.strokeStyle="<?php echo $gridColour; ?>";
					cd.lineWidth = 1;
					for (var i=0;i<ylabels;i++) {
						cd.moveTo(0, canvasy-(i*(canvasy/ylabels)));
						cd.lineTo(canvasx, canvasy-(i*(canvasy/ylabels)));
						cd.stroke();
						var tmptext = Math.round(highest-(highest-(i*(highest/ylabels)))).toString();
						// Detect if mobile
						if(<?php echo $mobile;?> == true){
							cd.font = 'italic 40pt Calibri';
						}
						cd.fillText(tmptext, 0, canvasy-(i*(canvasy/ylabels)));
					}
					// add x gridlines and labels
					var xlabels = <?php echo $xLabels; ?>;
					if (xlabels == -1){
						for (var i=10;window.screen.availWidth < i*325;i--){
							xlabels = i;}
					}
					cd.beginPath();
					cd.strokeStyle="<?php echo $gridColour; ?>";
					cd.lineWidth = 1;
					for (var i=0;i<xlabels;i++) {
						cd.moveTo(canvasx-(i*(canvasx/xlabels)), 0);
						cd.lineTo(canvasx-(i*(canvasx/xlabels)), canvasy);
						cd.stroke();
						var date = new Date(((longest-(i*(longest/xlabels)))+<?php echo $lowest; ?>)*1000);
						var day = date.getDay();
						var month = date.getMonth()+1; // Months are array with 0 offset
						var year = date.getFullYear();
						var hours = date.getHours();
						var minutes = "0" + date.getMinutes();
						var seconds = "0" + date.getSeconds();
						var formattedTime = hours + ':' + minutes.substr(-2) + ':' + seconds.substr(-2) + ' ' + day + '/' + month + '/' + year;
						// Detect if mobile
						if(<?php echo $mobile;?> == 1){
							cd.font = 'italic 40pt Calibri';
						}
						cd.fillText(formattedTime, canvasx-(i*(canvasx/xlabels)), canvasy);
					}
					// Graph data points
					for (var i=0;i<yseries.length;i++) {
						cd.fillRect(xpoint[i], ypoint[i], plotsize, plotsize);
					}
					for (var i=0;i<yseries1.length;i++) {
						cd.fillRect(xpoint1[i], ypoint1[i], plotsize, plotsize);
					}
					for (var i=0;i<yseries2.length;i++) {
						cd.fillRect(xpoint2[i], ypoint2[i], plotsize, plotsize);
					}
					for (var i=0;i<yseries3.length;i++) {
						cd.fillRect(xpoint3[i], ypoint3[i], plotsize, plotsize);
					}
					for (var i=0;i<yseries4.length;i++) {
						cd.fillRect(xpoint4[i], ypoint4[i], plotsize, plotsize);
					}
					// draw line between points.
					cd.beginPath();
					cd.moveTo(xpoint[0], ypoint[0]);
					cd.lineWidth = <?php echo $lineWeight; ?>;
					cd.strokeStyle="#<?php echo $lineColour; ?>";
					for (var i=0;i<yseries.length;i++) {
						cd.lineTo(xpoint[i], ypoint[i]);
						cd.stroke();
					}
					cd.beginPath();
					cd.moveTo(xpoint1[0], ypoint1[0]);
					cd.lineWidth = <?php echo $lineWeight; ?>;
					cd.strokeStyle="#<?php echo $lineColour1; ?>";
					for (var i=0;i<yseries1.length;i++) {
						cd.lineTo(xpoint1[i], ypoint1[i]);
						cd.stroke();
					}
					cd.beginPath();
					cd.moveTo(xpoint2[0], ypoint2[0]);
					cd.lineWidth = <?php echo $lineWeight; ?>;
					cd.strokeStyle="#<?php echo $lineColour2; ?>";
					for (var i=0;i<yseries2.length;i++) {
						cd.lineTo(xpoint2[i], ypoint2[i]);
						cd.stroke();
					}
					cd.beginPath();
					cd.moveTo(xpoint3[0], ypoint3[0]);
					cd.lineWidth = <?php echo $lineWeight; ?>;
					cd.strokeStyle="#<?php echo $lineColour3; ?>";
					for (var i=0;i<yseries3.length;i++) {
						cd.lineTo(xpoint3[i], ypoint3[i]);
						cd.stroke();
					}
					cd.beginPath();
					cd.moveTo(xpoint4[0], ypoint4[0]);
					cd.lineWidth = <?php echo $lineWeight; ?>;
					cd.strokeStyle="#<?php echo $lineColour4; ?>";
					for (var i=0;i<yseries4.length;i++) {
						cd.lineTo(xpoint4[i], ypoint4[i]);
						cd.stroke();
					}
                }else{ // If there is not set location
					var container = document.getElementById("graphDiv");
					var canvasx = container.clientWidth;
					var canvas = document.getElementById("graph");
					var canvasy = <?php echo $canvasy; ?>;
					var cd = canvas.getContext("2d");
					cd.canvas.width = canvasx;
					if(<?php echo $mobile;?> == 1){
							cd.font = 'italic 35pt Calibri';
							var text_h_gap = 40;
							var text_w_factor = 2;
						}else{
							cd.font="20px Georgia";
							var text_h_gap = 25;
							var text_w_factor = 1;
						}
					// get images
					var arrow_wn = document.getElementById("arrow_w-n");
					var sensor = document.getElementById("sensor-icon");
					var arrow_es = document.getElementById("arrow_e-s");
					var arrow_left = document.getElementById('left_arrow');
					// map
					cd.fillText("Click a sensor ", 64*text_w_factor, 125);
					cd.drawImage(sensor, 214*text_w_factor,75);
					cd.drawImage(arrow_left, (0*text_w_factor),75, 64,64);
					cd.fillText("of interest.", (280*text_w_factor)-(64*(text_w_factor-1)), 125);
					// options
					if(<?php echo $mobile;?> == 1){
							cd.font = 'italic 39pt Calibri';
							var text_h_gap = 40;
							var text_w_factor = 2;
						}else{
							cd.font="22px Georgia";
							var text_h_gap = 25;
							var text_w_factor = 1;
						}
					//cd.fillText("Options. ", (canvasx-(160*text_w_factor)), 100);
					if(<?php echo $mobile;?> == 1){
							cd.font = 'italic 35pt Calibri';
							var text_h_gap = 40;
							var text_w_factor = 2;
						}else{
							cd.font="20px Georgia";
							var text_h_gap = 25;
							var text_w_factor = 1;
						}
					cd.drawImage(arrow_wn, canvasx-64,0, 64,64);
					cd.fillText("This lets you select", canvasx-(200*text_w_factor), 0+text_h_gap);
					cd.fillText("data types and", canvasx-(200*text_w_factor), 0+(2*text_h_gap));
					cd.fillText("customise", canvasx-(200*text_w_factor), 0+(3*text_h_gap));
					// table
					cd.fillText("After selecting a", (canvasx-(200*text_w_factor)), canvasy-300);
					cd.fillText("sensor, a data", (canvasx-(200*text_w_factor)), canvasy-(300-(text_h_gap)));
					cd.fillText("table will be.", (canvasx-(200*text_w_factor)), canvasy-(300-(2*text_h_gap)));
					cd.fillText("shown below.", (canvasx-(200*text_w_factor)), canvasy-(300-(3*text_h_gap)));
					// key
					cd.fillText("A colour coded",64, canvasy-150);
					cd.fillText("key will be", 64, canvasy-(150-(text_h_gap)));
					cd.fillText("shown here.", 64, canvasy-(150-(2*text_h_gap)));
					cd.drawImage(arrow_es, 0,canvasy-74, 64,64);
				}
			}
		</script>
	</body>
</html>
