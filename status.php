<html>
  <head>
    <title>Status - Orokonui monitoring</title>
    <!-- Import Styles CSS -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" type="text/css" href="style.css"/>
    <!-- Font Import -->
    <link href='https://fonts.googleapis.com/css?family=Righteous' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
  <head>
  <body bgcolor="#FFFFFF">

    <!--Comment this out when system is complete. Uncomment during mainince-->
		<!--<div id="warning">
			<p><strong>Warning:</strong> This system is under development. Any data is likely to
				be false test data. Any about information may refer to furture plans.</p>
		</div>-->

    <div id="header"> <!--I decided to but the header outside of the content wrapper so it would strech across the entire screen.-->
			<img src="resource/header.png" width="100%">
			<p><a href="index.php">Data</a> &nbsp; | &nbsp; <a href="status.php">Status</a> &nbsp; | &nbsp; <a href="about.php">About</a> &nbsp; | &nbsp; <a href="rules.php">Setup</a> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </p>
			<hr>
		</div>
    <div id="content">
      <div id="status">
<!-- Dynamic page -->
<?php
$errors = 0;
$db = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
$q = "SELECT * FROM rules";
$result = $db->query($q);
$node = array();
$sensor = array();
$operation = array();
$value = array();
$start_time = array();
$end_time = array();
$delay = array();
$msg = array();
while ($row = $result->fetch_assoc())
{
	array_push($node, $row['node']);
	array_push($sensor, $row['sensor']);
	array_push($operation, $row['operation']);
	array_push($value, $row['value']);
	array_push($start_time, $row['start_time']);
	array_push($end_time, $row['end_time']);
	array_push($delay, $row['delay']);
	array_push($msg, $row['message']);
}

function subVariables($text)
{
	//echo 'entered var sub';
    // get all variable data
    $dba = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
    $q = "SELECT * FROM sensor_data";
    $result = $dba->query($q);
    $node = array();
    $sensor = array();
    $lat = array();
    $lng = array();
    $time = array();
    $allSensors = array();
    while ($row = $result->fetch_assoc())
    {
        array_push($lat, $row['lat']);
        array_push($lng, $row['lng']);
        array_push($sensor, $row['sType']);
        array_push($time, $row['time']);
    }
    $q = "SELECT * FROM sensor_config";
    $result = $dba->query($q);
    while ($row = $result->fetch_assoc())
    {
        for ($i=0;$i<sizeof($lat);$i++)
        {
            if ($lat[$i] == $row['lat'])
            {
                if ($lng[$i] == $row['lng'])
                {
                    array_push($node, $row['hr_name']);
                }
            }
        }
    }
    $q = "SELECT * FROM sensor_types";
    $result = $dba->query($q);
    while ($row = $result->fetch_assoc())
    {
        array_push($allSensors, $row['name']);
    }
    $dba->close();

    $stext = explode(" ",$text);
    for ($i=0;$i<sizeof($stext);$i++)
    {
        $tmp=explode(".",$stext[$i]);
        if ($tmp[0]=="NODE")
        {
            for ($j=0;$j<sizeof($node);$j++)
            {	/*
		if ($tmp[1]=='all')
		{
			for ($k=0;$k<sizeof($sensor);$k++)
                        {
                                //echo $sensor[$k];
                                if ($tmp[2]==$sensor[$k])
                                {
                                        //echo "found match";
                                        if ($tmp[2]=='VALUE')
                                        {
                                                $stext[$i] = $value[$j];
                                        }
                                        elseif ($tmp[2]=='LAT')
                                        {
                                                $stext[$i] = $lat[$j];
                                        }
                                        elseif ($tmp[2]=='LNG')
                                        {
                                                $stext[$i] = $lng[$j];
                                        }
                                else
                                {
                                    $stext = "[ERROR; we couldn't find that, have a look at the docs, and if you want somethin$
                                }
                            }
                        }
		}
		*/
                if ($tmp[1]==$node[$j])
                {
		    if ($tmp[2]=="LAT")
		    {
			$stext[$i] = $lat[$j];
		    }
		    if ($tmp[2]=="LNG")
                    {
                        $stext[$i] = $lng[$j];
                    }
		    else
		    {
                    	for ($k=0;$k<sizeof($sensor);$k++)
                    	{
				//echo $sensor[$k];
                        	if ($tmp[2]==$sensor[$k])
                        	{
					//echo "found match";
                            		if ($tmp[3]=='VALUE')
                            		{
                                		$stext[$i] = $value[$j];
                            		}
                            		elseif ($tmp[3]=='LAT')
                           	 	{
                           	 		$stext[$i] = $lat[$j];
                            		}
                            		elseif ($tmp[3]=='LNG')
                            		{
                                		$stext[$i] = $lng[$j];
                            		}
                            	else
                            	{
                            	    $stext = "[ERROR; we couldn't find that, have a look at the docs, and if you want something that's not here, feel free to make a suggestion!. Or submit a bug, it could be our side.]";
                            	}
                            }
                    	}
		    }
                }
            }
        }
        if ($tmp[0]=="GET_NODES")
        {
            $names="[";
            for ($j=0;$j<sizeof($node);$j++)
            {
		if ($j==(sizeof($node)-1))
		{
			$names .= "'".$node[$j]."'";
		}
		else
		{
                	$names .= "'".$node[$j]."',";
		}
            }
            $names.="]";
            $stext[$i]=$names;
        }
        if ($tmp[0]=="GET_SENSORS")
        {
            $names="(";
            for ($j=0;$j<sizeof($allSensors);$j++)
            {
		if (sizeof($allSensors)-1>$j){
                	$names .= "'".$allSensors[$j]."',";
            	}else{
			$names .="'".$allSensors[$j]."'";
		}
	    }
            $names.=")";
            $stext[$i]=$names;
        }
    }
    $text = "";
    for ($i=0;$i<sizeof($stext);$i++)
    {
        $text .=$stext[$i]." ";
    }
    return $text;
}

$q = "SELECT hr_name, lat, lng FROM sensor_config";
$result = $db->query($q);
while ($row = $result->fetch_assoc())
{
	for ($i=0;$i<sizeof($node);$i++)
	{
		if ($node[$i] == $row['hr_name'])
		{
			$node[$i] = $row['lat'].$row['lng'];
		}
	}
}
//print_r($node);
$q = 'SELECT * FROM sensor_data';
$result = $db->query($q);
$messages = "";
$error_ids = array();
$Lvalue = array();
while ($row = $result->fetch_assoc())
{
	for ($i=0;$i<sizeof($node);$i++)
	{
		//echo "found";
		if ($node[$i]==($row['lat'].$row['lng']))
		{
			//echo "sesnor";
			if ($sensor[$i] == $row['sType'])
			{
				array_push($Lvalue, $row['sValue']);
				array_push($error_ids, $i);
			}
		}
	}
}

//sort found errors
for ($i=0;$i<sizeof($error_ids);$i++)
{
	if ($operation[$error_ids[$i]]=='Less than'){
        	if ($value[$error_ids[$i]]>$Lvalue[$i]){
                	$messages .="<h5 style='font-family: arial;'>".$msg[$error_ids[$i]]."</h5><br>";
                        $errors++;
                }
        }elseif ($operation[$error_ids[$i]]=='Equal to'){
                if ($value[$error_ids[$i]]==$Lvalue[$i]){
                      	$messages .="<h5 style='font-family: arial;'>".$msg[$error_ids[$i]]."</h5><br>";
                       	$errors++;
        	}
        }elseif ($operation[$error_ids[$i]]=='Greater than'){
		//echo "<br><br>greater than " . $value[$error_ids[$i]] . " la la " . $Lvalue[$i];
                if ($value[$error_ids[$i]]<$Lvalue[$i]){
                        $messages .="<h5 style='font-family: arial;'>".$msg[$error_ids[$i]]."</h5><br>";
                        $errors++;
                }
        }
}

if ($errors==0)
{
	echo "<h2 style='font-family: arial;'>System okay - no alerts found</h2>";
}elseif ($errors==1){
	echo "<h2 style='font-family: arial;'>Warning, $errors issue found.</h2>";
}else{
	echo "<h2 style='font-family: arial;'>Warning, $errors issues found.</h2>";
}

echo "<br><br>";
// displays
$q = "SELECT * FROM status_display";
$result = $db->query($q);
while ($row = $result->fetch_assoc())
{
	if ($row['enabled']==1)
	{
        echo subVariables($row['html']);
	}
}
//messages
echo $messages;


?>

<!-- End dynatic section -->
	</div>
		</div>
			<div id="footer">
				<hr>
				<a href="index.php#header">Home</a><br>
				<a href="about.html">About</a><br>
				<a href="phpmyadmin">DB Admin</a><br>
				<hr>
				<p>Design by <a href="https://twitter.com/WilliamSatterth">William Satterthwaite</a>, 2016. Fonts from <a href="https://www.google.com/fonts">google.com/fonts</a>, map from <a href="https://maps.google.com">maps.google.com</a>. All other context under
				<a href="https://creativecommons.org/licenses/by/4.0/">Creative Commons</a>.</p>
			</div>
		</div>
	</body>
</html>
