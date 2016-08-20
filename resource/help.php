<html>
  <head>
    <title>Data - Orokonui monitoring</title>
  <!-- Import Styles CSS -->
    <?php
      // Read user agent and detect if mobile or not.
      /*
      if (strpos($_SERVER['HTTP_USER_AGENT'], "Mobile") > 0){ // CHANGE TO MOBILE
        if ($_GET['force_mode'] == 'DESKTOP'){
          echo "<link rel='stylesheet' type='text/css' href='../style.css'>";
        }else{
          echo "<link rel='stylesheet' type='text/css' href='../mobile.css'>";
        }
      }else{
        if ($_GET['force_mode'] == 'MOBILE'){
          echo "<link rel='stylesheet' type='text/css' href='../mobile.css'>";
        }else{
          echo "<link rel='stylesheet' type='text/css' href='../style.css' media='screen and (min-device-width: 600px)'/>\n<link rel='stylesheet' type='text/css' href='mobile.css' media='screen and (max-device-width: 599px)'/>";
        }
      }
      */
    ?>
  <!-- Font Import -->
    <link href='https://fonts.googleapis.com/css?family=Righteous' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
  </head>
    <body>
      <div id="header"> <!--I decided to but the header outside of the content wrapper so it would strech across the entire screen.-->
    <img src="header.png" width="100%">
    <p><a href="index.php">Data</a> &nbsp; | &nbsp; <a href="status.php">Status</a> &nbsp; | &nbsp; <a href="about.php">About</a> &nbsp; | &nbsp; <a href="rules.php">Setup</a> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </p>
    <hr>
  </div>
  <br>
  <div id="content">
      <div id="index">
        <h1>Help</h1>
            <h3>Contents</h3>
            <a href="#status">Using the status page</a><br>
            <a href="#graphing">Graphing Data</a><br>
            <a href="#table">Using the table</a><br>
            <a href="#csv">Downloading data as CSV</a><br>
        </div>
        <div id="status">
            <h3>Status Page</h3>
            <p>The status page is a way to quickly see what is happening at the Orokonui
              ecosantury. It titles the page with the real-time status, either Good, or Bad. If it is 'Good'
              that means everything is functioning normally. If it is bad, there is an unresovled alert.
              These alerts will show in red, and percist until the system detects them as resolved or an
              administrator manually overrides them. To view the full system logs, click the [show full logs] button.
            </p>
        </div>
        <div id="graphing">
            <h3>Grahping</h3>
            <p>Data can be graphed from the <a href='../index.php'>Data page</a>.
            To do so, select the sensor you would like data from by click the it's icon
            on the map.
            <br><img src="click-sensor.png"><br>
            This will refresh the page, and the new version will have your
            graph drawn according to default options.
            <br><br>To change the graph, click the
            three dots top right corner of the graph, under the map.
            <br><img src="click-options.png"><br>
            This will bring up an options dialogue.
            <img src="image-of-options-dialogue.png">
            To select different data from the sensor, tick the
            data types you want under the variables section. You can also change the
            colour of varause commpents, by inputing a base 16 number into the colour boxes.
            The first 2 digits refer to red, and can be 00 to FF, digits 3 and 4 are blue,
            and digits 5 and 6 are green.
            <br><br>
            To view data between a certain time, use the time section of the options dialogue as shown:
            <br><img src="how-to-use-time-parameters.png"><br>
            Other options are for customisation. All colour options need to be in hex format.<br><br>
            The Line Weight refers to the thickness ('weight') of the trend lines. This is a interger value
            reflecting the number of pixels used to form the line. (for no line use 0.)<br><br>
            The markers refer to the background gridlines, and amount of times value indicators are shown.
            <br><br>
            To share a graph, you can either:<br>
            Copy the url, since all options and customisations are reflected in the url, when you or someone else again browses
            to that url, it will draw the same graph. (this assumes you have set time parameters, if not, new data will be added next time.)
            <br><br>
            Right click -> Save image as. This saves to drawn canvas as an image.
            <br><br>
            Take a screenshot - see you operating system documentation for how to do this.
            </p><br>
        </div>
        <div id="table">
            <h3>Tables</h3>
            <p>After generating a graph on the data page, you will also see a table displaying the raw
              data below the graph. This always you to see exact values, as well data that is not sutied for a graph.
            </p>
        </div>
        <div id="csv">
            <h3>CSV Download</h3>
            <p>CSV stands for comma sepporated variables. It is raw data format, and allows you
              to download an view data in most spreadsheeting applications. Additionally is a very
              easy format for programmictic analysis.
            </p>
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
