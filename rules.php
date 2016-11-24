<?php

// notes: add calibrate option
	//ini_set('display_errors', 'On');
	// This script gets a key pair from submit.php
	if ($_GET['hk'] == 1){
		if ($_GET['token'] != ""){
			/*
			 * Only a successful login will redirect with a token, however a user could manually add it to
			 * their url. This would display pages as if they are logged in, however, since any changes are
			 * submitted through the keys system, these would not be saved.
			 * Therefore, anyone is able to see the rules, however only those logged in can change them.
			 */
			$links = "rules.php?hk=1&token=" . $_GET['token'] . "&session=" . $_GET['session'] . "&ka=".$_GET['ka']."&kb=".$_GET['kb'];
			$links_auth = "resource/datahandling/submit.php?stage=3&token=" . $_GET['token'] . "&session=" . $_GET['session']. "&ka=".$_GET['ka']."&kb=".$_GET['kb'];
			// Always use <above> when linking to other pages, as it keeps track of token and session id for you.
			$sidebar = "<div id=sidebar>
					<p>
						<a href='" . $links . "&redirect=about:home'>Home</a><br>
						<a href='" . $links . "&redirect=about:logs'>Logs</a><br>
						<a href='" . $links . "&redirect=config:alerts'>Alert Config</a><br>
                                                <a href='" . $links . "&redirect=config:sensors'>Sensor config</a><br>
						<a href='" . $links . "&redirect=config:numbers'>Contacts</a><br>
						<a href='" . $links . "&redirect=config:status'>Status</a><br>
						<a href='" . $links . "&redirect=config:datainterpretation'>Interpretation</a><br>
					</p>
				</div>";
			if ($_GET['redirect'] == "about:home"){
				$page_contents = $sidebar . "<div id=main>
					<div id='sick' style='width: 75%'>
						<h1><span>Warning</span>. Please don't make me sick!</h1>
						<hr>
						<div id='tabin'>
							<p>No really. Changes to advanced setting could render the entire system useless, for example,
							changing sensor id's could could change the way it's data is interpreted, therefore sending the
							wrong alerts to the wrong people, causing a whole lot of panic, and sickness and badness and things.</p><br><br><br>
						</div>
					</div>
					<div id='about'>
						<p>This page allows you to configure system 'rules'. Rules are a command that the server will follow
						if a certain condition is meet. For example, what data should trigger an alert at what time and to whom.
						</p><br><br>
						<p>To get started, click the relevant link on the sidebar acording to your need.
						</p>
						<br><br><p>Also, this page doesn't use cookies by default, but you can click this button for an auto access cookie, (yum) that will automatically log you in when using this computer on the same ip address. Since most ip address are dynamic, and orokouni monitoring uses a token system based on a salted hash of your ip address, you may still need to re-enter it every day or so. <input type='submit' onclick='givecookie()' value='Give me a cookie!'></input></p>
						<script>
							function givecookie()
							{
								var d = new Date();
								d.setTime(d.getTime()+2592000000);
								document.cookie = 'autologintoken=".$_GET['token']."; expires='+d.toUTCString();
								document.cookie = 'autologinid=".$_GET['session']."; expires='+d.toUTCString();
							}
						</script>
					</div>
				</div>
				";
			}else if($_GET['redirect'] == "config:numbers"){
				$page_contents = $sidebar . "<div id=main>
                                        <h1>Contacts</h1>
                                        <br><br><p>Here you can add the phone numbers to be used for alerts. The phones you use must be capiable of SMS messaging, as voice alerts are not supported.</p>
					<table><tr><th>Name</th><th>Number</th></tr>";
					$db = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
                                        if ($db->connect_error){die("Cannot connect to database.");}
                                        $q = "SELECT number, name FROM contacts";
                                        $result = $db->query($q);
                                        while($row = $result->fetch_assoc()){
                                                $page_contents .= "<tr><td>" . $row['name'] . "</td>" . "<td>" . $row['number'] . "</td><td><a href='" . $links_auth . "&act=DEL_NUM&num=".$row['number']."'>Delete?</a></td></tr>";
                                        }


					$page_contents .= "</table><br><br><form method='post' action='" . $links_auth . "&act=ADD_NUMBER'>
						<p>Number: <input type='text' size='2' value='+64' name='cc'></input><input type='text' name='number'></input><br>
						Phone Name: <input type='text' name='phone_name'></input><br>
						<input type='submit' value='Add this phone.'></input><br>
						When you first add a phone, a text message will be sent to it. If you do not receive the message with a few minutes, adding the number has not worked and you should delete it and re-add it. Check numbers have thier valid country code, the first 0 is not needed, i.e: 022 becomes +6422</p>
					</form>
					<br>
					<br><h2>Send test message</h2>
					<p>Send a text to any number in order to test the SMS system is working (or if you ran out of credit)</p>
					<form method='post' action='" . $links_auth . "&act=TEST_SMS'>
                                                <p>Number (with country code): <input type='text' value='+64' name='number'><br>
                                                Message: <input type='text' name='msg'></input><br>
                                                <input type='submit' value='Send'></input><br>
                                        </form>
                                </div>
                                ";
			}else if($_GET['redirect'] == "config:sensors"){
				$page_contents = $sidebar . "<div id=main>
					<h1>Sensors</h1><br>
					<p>Each sensor node is assigned a number identifier between 0 and 15, which is matched to a latidude and longidude if applicable.</p>
					<br><p>These are the currently configured sensors:</p><br>
					<table><hr><th>ID</th><th>Latidude</th><th>Longitude</th><th>Human-readable name</th>";
					$db = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
					if ($db->connect_error){die("Cannot connect to database.");}
					$q = "SELECT sensor_id, lat, lng, hr_name FROM sensor_config";
					$result = $db->query($q);
					while($row = $result->fetch_assoc()){
						$page_contents .= "<tr><td>" . $row['sensor_id'] . "</td>" . "<td>" . $row['lat'] . "</td>" . "<td>" . $row['lng'] . "</td>" . "<td>" . $row['hr_name'] . "</td><td><a href='" . $links_auth . "&act=DEL_SENSOR&id=" . $row['sensor_id'] . "'>Delete?</a></td></tr>";
					}
					$page_contents .="</table><br>
					<p>To update a sensor, select it's id number, and enter the NEW details. To add a sensor select 'New'. Please fill out everything,
					even if it hasn't changed, as all old data will be overide. See backup page to retrive any lost data.</p>
					<br><form action='" . $links_auth . "&redirect=config:sensors&act=ADD_SENSOR' method='post'>
						Sensor: <select name='oid'>
							<option>New</option>";
							$q = "SECLECT sensor_id FROM sensor_config";
							$r = $db->query($q);
							while($row = $result->fetch_assoc()){
                                                		$page_contents .= "<option>" . $row['sensor_id'] . "</option>";
	                                		}
						$db->close();
						$page_contents .= "</select>
						New id: <input size='5' name='nid' type='text'></input> Latidute: <input name='lat' size='5' type='text'></input> Longitude: <input name='lng' size='5' type='text'></input> Human-readable name: <input name='hrn' size='10' type='text'></input><br>
						<input type='submit' value='Go. '>
					</form><br>
					<h2>Sensor type</h2>
					<p>Sensor type is also represented by a number between 0 and 15. This is number is for the type of sensor, not the device carrying the sensor.</p>
					<br>
					<p>Current Sensor types:</p>
					<table><hr><th>id</th><th>name</th></hr>";
					$dbn = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
                                        if ($dbn->connect_error){die("Cannot connect to database.");}
                                        $qu = "SELECT idnum, name FROM sensor_types";
                                        $res = $dbn->query($qu);
                                        while($ro = $res->fetch_assoc()){
                                                $page_contents .= "<tr><td>" . $ro['idnum'] . "</td>" . "<td>" . $ro['name'] . "</td></tr>";
                                        }
                                        $page_contents .="</table><br>
					<br><form action='" . $links_auth . "&redirect=config:sensors&act=SENSOR_TYPE' method='post'>
                                                Sensor Type Name: <input size='5' name='stn' type='text'></input>
                                                Sensor Type id: <input size='5' name='nid' type='text'></input>
                                                <input type='submit' value='Add. '>
                                        </form>
					<p>Note, this will not change past data, only what is to come.</p>
				</div>
				";
			}else if(($_GET['redirect'] == "config:alerts")){
				$page_contents = $sidebar . "<div id='main'>
  <h1>Alerts</h1>
  <p>Here you can setup alerts to be sent out if certain things happen within
  the ecosanctuary.</p>
  <p>
  <br>
    <strong>Current Alerts:</strong>";
        $db = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
        $q = "SELECT * FROM rules";
        $results = $db->query($q);
        while ($row = $results->fetch_assoc()){
		$sth = round($row['start_time']/(60*60));
		$stm = round($row['start_time']%(60*60));
		$eth = round($row['end_time']/(60*60));
		$etm = round($row['end_time']%(60*60));
                $page_contents .= "<br><p>If ".$row['node']."'s ".$row['sensor']." is ".$row['operation']." ".$row['value'].", then send an alert to ".$row['contact'].".";
		if(($row['start_time']-$row['end_time'])!=0){
			$page_contents .= "Only between the times of ".$sth.":".$stm." and ".$eth.":".$etm.".";
		}
		$page_contents .= "The alert will say '".$row['message']."'.";
		if($row['send_clear']==1){
			$page_contents.="An all clear message will be sent when the issue is resovled. <a href='".$links_auth."&act=DEL_RULE&id=".$row['id']."'>Delete?</a></p>";
		}else{
			$page_contents.="An all clear message will not be sent. <a href='".$links_auth."&act=DEL_RULE&id=".$row['id']."'>Delete?</a></p>";
		}
	}
	$sensortypes = array();
        $q = "SELECT name FROM sensor_types";
        $results = $db->query($q);
        while ($row = $results->fetch_assoc()){
                array_push($sensortypes, $row['name']);
        }
	$nodes = array();
        $q = "SELECT hr_name FROM sensor_config";
        $results = $db->query($q);
        while ($row = $results->fetch_assoc()){
                array_push($nodes, $row['hr_name']);
        }
	$contacts = array();
        $q = "SELECT name FROM contacts";
        $results = $db->query($q);
        while ($row = $results->fetch_assoc()){
                array_push($contacts, $row['name']);
        }
        $db->close();



$page_contents .="</p>
  <br>
  <br>
  <p>
    <strong>Add new:</strong>
  </p>
  <form method='post' action='".$links_auth."&redirect=config:sensor&act=RULE'>
    <p>*If <select name='node'><option name='1'>Select location</option>";
	// dynamically adds nodes
	for ($i=0; $i<sizeof($nodes); $i++){
		$page_contents .= "<option name='".($i+1)."'>".$nodes[$i]."</option>";
	}
	$page_contents .= "</select>'s
      <select name='sensor'><option name='1'>Select Sensor</option>";
	// dynamically adds sensor types
        for ($i=0; $i<sizeof($sensortypes); $i++){
                $page_contents .= "<option name='".($i+1)."'>".$sensortypes[$i]."</option>";
        }
	$page_contents .="</select> is
      <select name='op'>
        <option name='g'>Greater than</option>
        <option name='l'>Less than</option>
        <option name='e'>Equal to</option>
      </select>
      <input type='text' value='(value)' name='value' size='5'></input>
      Then send an alert to
      <select name='contact'>
        <option name='1'>Select phone</option>";
	// dynamically adds contacts
        for ($i=0; $i<sizeof($contacts); $i++){
                $page_contents .= "<option name='".($i+1)."'>".$contacts[$i]."</option>";
        }
      $page_contents .="</select>
      <br><br>
      Only between these times? <input type='text' value='Hour' name='sh' size='3'>:<input type='text' value='Min' name='sm' size='3'>
      and <input type='text' value='Hour' name='eh' size='3'>:<input type='text' value='Min' name='em' size='3'>
      <br><br>
      Delay before sending alert (alert will only be sent if the statment remains true after the time delay.) <input type='text' value='Min' name='delay' size='3'>
      <br><br>
      *The alert should say:
      <input type='text'name='msg' size='80'></input><br><br></p>
      <input type='submit' value='Add alert. '></input>
	<br><br>
	<input type='checkbox' name='send_clear'></input> Send all clear?
	<br><br>
  </form>
</div>";

			}else if ($_GET['redirect'] == "config:datainterpretation"){
				$page_contents = $sidebar . "<div id='main'>
				<h1>Data interpretation</h1>
				<p>This is where you can interpreate the raw data. Create PHP scripts here that will be executated when incoming data is added to the  system.</p><br>
				<h4>Current Files</h4>";
                // get the current files.
                // (ripped off my blog, hence 'post' variable names)
                $dir = "resource/user-data/data-interpretation-scripts/";
                $posts = scandir($dir);
                for ($i=0;$i<sizeof($posts);$i++){
                    if (strpos($posts[$i], '.php')!== false){
                        $mod_date = date("d/M/Y", filemtime($posts[$i]));
                        $page_contents .= "<a href='text-editor.php?mode=FILE&f=" .$dir.$posts[$i] . "'>" . $posts[$i] . " - $mod_date</a><br>";
                    }
                }
                $page_contents .="<br>
				<br>
				<p>For documentation of how to access the data base, please see the documentation <a href=''>repository</a></p>
				<a target='_blank' href='text-editor.php?hk=1&token=" . $_GET['token'] . "&session=" . $_GET['session'] . "&ka=".$_GET['ka']."&kb=".$_GET['kb']."&mode=FILE&f=".$dir.time().".php'>New File</a>
				</div>";
			}
			else if ($_GET['redirect'] == "config:status"){
				$page_contents = $sidebar . "<div id='main'>
				<h1>Status</h1>
				<p>The status pages by default provides automatic warns for all issues detected, as defined by your alert. If there is a text alert that will automatically create an alert on the status page. In addition to this you can customise the status page by creating custom html and accessing real-time data through the api.</p><br>
				<a target='_blank' href='text-editor.php?hk=1&token=" . $_GET['token'] . "&session=" . $_GET['session'] . "&ka=".$_GET['ka']."&kb=".$_GET['kb']."&db=status_display&row=1&exit=close'>Edit status html</a> Enabled: <input type='checkbox' value='1'></input><br>
				<p>Note this will be wrapped with the default template, which you cannot edit, without editing the source code. (Which your most welcome to do, under GPL3 conditions)</p>
				</div>";
			}else if ($_GET['redirect'] == "about:logs"){
				$page_contents = $sidebar . "<div id='main'>
  <h1>Logs</h1><br>
  <p>Log files help you check everything is working or debug the system,
    there are serveal different logs that the system records.</p>
  <br><p><strong>Rules log: </strong> The keeps track of logins to this page,
    as well as any changes made to system rules. It logs IP Adress of logins,
    pages accessed, and edits made.</p>
    <br><a href='".$links_auth."&act=DOWNLOAD&file=.htrules.log&name=rules.log'>Download Rules Log</a>
	<br><br>
    <p><strong>Technical Log: </strong> Keeps track of input from sensors,
    automatic anaylsis/decission made based on data, the following of rules,
    notifications send and to whom etc.</p>
    <br><a href='".$links_auth."&act=DOWNLOAD&file=.httechnical.log&name=technical.log'>Download Rules Log</a>
	<br><br>
    <p><strong>Status Log: </strong>The public log displayed on the status
      page. There should be no
    need to download it, but if needed in text form use the link below.</p>
    <br><a href='resource/datahandling/status.log'>Download Rules Log</a>
</div>";
			}else if($_GET['redirect']=='about:config'){
				/*$page_contents = $sidebar . "
<div id='main'>
	<script>
                        math.config({
                                number: 'BigNumber', // Default type of number:
                                                                         // 'number' (default), 'BigNumb$
                                precision: 99999999        // Number of significant digits for BigNumbers
                          });
                                function submitter() {
                                        var ac = document.getElementById('oac');
                                        var ac_list = ac.value.split('');
                                        var ac_num = 0;
                                        for (var i=0;i<ac_list.length;i++){
                                                ac_num += (ac_list[i].charCodeAt()*i);
                                        }
					alert(ac_num);
                                        var keypt1 = " . $_GET['kb'] . ";
                                        var keypt2 = " . $_GET['ka'] . ";
                                        var oac_c = math.mod(math.pow(math.bignumber(ac_num), math.bignumber(keypt1)), math.bignumber(keypt2));

										var ac = document.getElementById('nac');
                                        var ac_list = ac.value.split('');
                                        var ac_num = 0;
                                        for (var i=0;i<ac_list.length;i++){
                                                ac_num += (ac_list[i].charCodeAt()*i);
                                        }
                                        var keypt1 = " . $_GET['kb'] . ";
                                        var keypt2 = " . $_GET['ka'] . ";
                                        var nac_c = math.mod(math.pow(math.bignumber(ac_num), math.bignumber(keypt1)), math.bignumber(keypt2));
                                        window.location = '".$links_auth."&act=CH_AC&oac=' + oac_c + '&nac=' + nac_c;
                }
                        </script>
	<h1>Setup</h1><br>
	<h2>Change Access Code:</h2>
	<p>
		Current AC: <input id='oac' type='password'></input>
		New AC: <input id='nac' type='password'></input>
		<input type='submit' onclick='submitter()'></input>
	</p>
	<h2>Backups:</h2>
	<p>
		You can set backups to occur automatically after n data dumps. A data dump is when the collector upload any data to the server. The backup is done through a bash command you speify, be careful, you have the full power of the shell. If yon don't know what to done the default should be fine.</p>

</div>
				";*/
			}else{
				$page_contents = $sidebar . "<p>Sorry, we can't find that location. Try <a href='" . $links . "&redirect=about:home'>home</a>.</p>";
			}
		}else{
			$page_contents = "<p>This page allows the editing of critical config files and is therefore
        protected from the public.</p>
        <br>
        <br>
        <p>If you are on the Orokonui staff, please enter the staff access code below:</p>
        <br>
        <br>
		<script>
			'use strict';
			
			// check for auto login cookies
			function getCookie(cname) {
				var name = cname + '=';
				var ca = document.cookie.split(';');
				for(var i = 0; i <ca.length; i++) {
					var c = ca[i];
					while (c.charAt(0)==' ') {
						c = c.substring(1);
					}
					if (c.indexOf(name) == 0) {
						return c.substring(name.length,c.length);
					}
				}
				return '';
			}
			
			var tok = getCookie('autologintoken');
			var sess = getCookie('autologinid');
			if (tok!='')
			{
				document.location = 'rules.php?hk=1&token='+tok+'&session='+sess+'&redirect=about:home&loginsrc=cookie';
			}

			math.config({
				number: 'BigNumber', // Default type of number:
									 // 'number' (default), 'BigNumber', or 'Fraction'
				precision: 99999999        // Number of significant digits for BigNumbers
			  });
				function submitter() {
					var ac = document.getElementById('access_code'); // or form = document.formName
					var ac_list = ac.value.split('');
					var ac_num = 0;
					for (var i=0;i<ac_list.length;i++){
						ac_num += (ac_list[i].charCodeAt()+(i));
					}
					//alert(ac_num);
					var keypt1 = " . $_GET['kb'] . ";
					var keypt2 = " . $_GET['ka'] . ";
					//var ac_c = math.bignumber(ac_num).pow(keypt1).mod(keypt2);
					var ac_c = math.mod(math.pow(math.bignumber(ac_num), math.bignumber(keypt1)), math.bignumber(keypt2));
					//var ac_c = Math.pow(" . $_GET['ka'] . ",ac_num)%". $_GET['kb'] . ";
					//alert(ac_c);
					window.location = 'resource/datahandling/submit.php?stage=3&pass=' + ac_c + '&act=LOGIN&redirect=about:home&ka=' +" . $_GET['ka'] . " + '&kb=' + " .  $_GET['kb'] . ";
                }
			</script>
          <p>Access code: </p><input id='access_code' type='password'></input><br>
		  <input type='submit' onclick='submitter()' value='Submit '></input>
		<br><p>Note, while loading this page you were redirected through a key getting script. This page will not transmit your access code in plain text, however uses these keys to encode it before transmitting to the server, since an insecure HTTP protocol is used. Man-in-the-middle attacks are useless here - don't bother.</p>
        <br>
        <br>";
		}
	}else{
		header("Location: resource/datahandling/submit.php?stage=1&ref=rules.php");
	}
?>

<html>
	<head>
	<script src="resource/lib/math.js" type="text/javascript"></script>
	<title>Settings - Orokonui monitoring</title>
	<!-- Import Styles CSS -->
	<link rel="stylesheet" type="text/css" href="style.css" media="screen and (min-device-width: 800px)"/>
	<link rel="stylesheet" type="text/css" href="mobile.css" media="screen and (max-device-width: 799px)"/>
	<!-- Font Import -->
	<link href='https://fonts.googleapis.com/css?family=Righteous' rel='stylesheet' type='text/css'>
	<link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
	<link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet' type='text/css'>
	</head>
	<body bgcolor="white">

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
		<p><strong><?php echo $_GET['print']; ?></strong></p>
		<div id="content">
			<?php echo $page_contents; ?>
		</div>
	</body>
</html>
