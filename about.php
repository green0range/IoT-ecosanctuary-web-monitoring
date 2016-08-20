<html>
	<head>
	<title>About - Orokonui monitoring</title>
	<!-- Import Styles CSS -->
	<link rel="stylesheet" type="text/css" href="style.css" media="screen and (min-device-width: 800px)"/>
	<link rel="stylesheet" type="text/css" href="mobile.css" media="screen and (max-device-width: 799px)"/>
	<!-- Font Import -->
	<link href='https://fonts.googleapis.com/css?family=Righteous' rel='stylesheet' type='text/css'>
	<link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
	</head>
	<body>

		<!--Comment this out when system is complete. Uncomment during mainince-->
		<div id="warning">
			<p><strong>Wanring:</strong> This system is under development. Any data is likely to
				be false test data. Any about information may refer to furture plans.</p>
		</div>

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
      <div id="about">
        <p>Orokonui monitor is a project to monitor some aspects of the <a href="http://orokonui.nz/">Orokonui</a> ecosanctuary though Internet of things (Iot) devices.
          This doubles in warning the Orokonui staff if issues arise, with an early warning SMS and <a href="status.php">status</a> page, as well as letting the public
          and researchers know about the status of the ecosanctuary.</p>
          <br>
          <h3>What's Monitored?</h3>
          <br>
            <div id="tabin">
              <ul>
                <li>Kiwi gates</li>
                <div id="tabin">
                  <br>
                  <p>These are 4 gates sepportate the kiwi's, the openings and closings of these are tracks,
                  as well as a warning system for when they are left open too long.</p>
                  <br>
                </div>
                <li>Tuatara temperature</li>
                <div id="tabin">
                  <br>
                  <p>The temperature inside Tuatara borrows.</p>
                  <br>
                </div>
                <li>weather</li>
                <div id="tabin">
                  <br>
                  <p>The weather of the area, this includes:
                    <ul>
                      <li>Average Wind Speed</li>
                      <li>Highest Gust per 10 minutes</li>
                      <li>Humitity</li>
                      <li>Rainfall</li>
                      <li>Temperature</li>
                    </ul>
                  </p>
                  <br>
                </div>
              </ul>
          </div>
          <br>
          <h3>How's it work?</h3>
          <br>
          <p>Data is collected by sensors on microcontrollers in the field. These raw recordings
            are then sent to a collector system over a radio link. The collecter formats and sorts
            the data before sending it to the server. The server checks the data, sends alerts,
            and then puts it on this website for us all to learn from!</p>
            <br>
            <br>
	<a href='resource/source.html'>Source Code</a>
<br>
<br>
      </div>
			<div id="footer">
				<hr>
				<p>Design by <a href="https://twitter.com/WilliamSatterth">William Satterthwaite</a>, 2016. Fonts from <a href="https://www.google.com/fonts">google.com/fonts</a>, map from <a href="https://maps.google.com">maps.google.com</a>. All other context under
				<a href="https://creativecommons.org/licenses/by/4.0/">Creative Commons</a>.</p>
				<br>
			</div>
		</div>
	</body>
</html>
