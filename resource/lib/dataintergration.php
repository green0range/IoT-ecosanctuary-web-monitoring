<?php
// This script provides data in the form of java script vars
//ini_set('display_errors', 'On');
function check_exists($array, $str)
{
	for ($i=0;$i<sizeof($array);$i++)
	{
		if ($str==$array[$i])
		{
			return $i;
		}
	}
	return -1;
}

    $dba = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
    $q = "SELECT * FROM sensor_data";
    $result = $dba->query($q);
    $node = array();
    $sensor = array();
    $lat = array();
    $lng = array();
    $time = array();
    $allSensors = array();
    $value = array();
    $data = array();
    while ($row = $result->fetch_assoc())
    {
        array_push($lat, $row['lat']);
        array_push($lng, $row['lng']);
        array_push($sensor, $row['sType']);
        array_push($time, $row['time']);
	array_push($value, $row['sValue']);
	array_push($data, $row['data']);
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
		    if (check_exists($node, $row['hr_name'])==-1)
		    {
                    	array_push($node, $row['hr_name']);
		    }
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

// Merges data and time
$datatime = array();
for ($i=0;$i<sizeof($time);$i++)
{
        $tmp = array($lat[$i].$lng[$i], $value[$i], $data[$i], $time[$i], $sensor[$i]);
        array_push($datatime, $tmp);
}
//print_r($datatime);
$added_data = array();
$node_order = array();
for ($i=0;$i<sizeof($datatime);$i++)
{
	$existant = check_exists($node_order, $datatime[$i][0]);
	if ($existant!=-1)
	{
		$added_data[$existant] = array($datatime[$i][2],$datatime[$i][4], $datatime[$i][3]);
		$node_order[$existant] = $datatime[$i][0];
	}
	else
	{
		array_push($added_data, array($datatime[$i][2],$datatime[$i][4], $datatime[$i][3]));
		array_push($node_order, $datatime[$i][0]);
	}
}

//list of nodes
// gets the hr names
$dba = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
$q = "SELECT * FROM sensor_config";
    $result = $dba->query($q);
    while ($row = $result->fetch_assoc())
    {
        for ($i=0;$i<sizeof($node_order);$i++)
        {
            if($node_order[$i]==$row['lat'].$row['lng'])
	    {
		$node_order[$i] = $row['hr_name'];
	    }
	}
    }
echo "var di_nodes = [";
for ($i=0;$i<sizeof($node_order);$i++)
{
	if ($i!=sizeof($node_order)-1)
	{
		echo "'".$node_order[$i]."', ";
	}
	else
	{
		echo "'".$node_order[$i]."'];";
	}
}

//list sensors
echo "var di_sensors = [";
for ($i=0;$i<sizeof($allSensors);$i++)
{
        if ($i!=sizeof($allSensors)-1)
        {
                echo "'".$allSensors[$i]."', ";
        }
        else
        {
                echo "'".$allSensors[$i]."'];";
        }
}

//list data
echo "var di_data = [";
for ($i=0;$i<sizeof($added_data);$i++)
{
        if ($i!=(sizeof($added_data)-1))
        {
		echo "[";
                for ($k=0;$k<3;$k++)
		{
        		if ($k!=2)
        		{
                		echo "'".$added_data[$i][$k]."', ";
        		}
        		else
       			{
                		echo "'".$added_data[$i][$k]."'],";
		        }
		}
        }
        else
        {
		echo "[";
                for ($k=0;$k<3;$k++)
                {
                        if ($k!=2)
                        {
				//echo $added_data[$i][$k];
                                echo "'".$added_data[$i][$k]."', ";
                        }
                        else
                        {
                                echo "'".$added_data[$i][$k]."']";
                        }
                }
		echo "]";
        }
}

// see all node entries

$dba = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
    $q = "SELECT * FROM sensor_data";
    $result = $dba->query($q);
    $time = array();
    $type = array();
    $data = array();
    while ($row = $result->fetch_assoc())
    {
        array_push($time, $row['time']);
        array_push($data, $row['data']);
	array_push($type, $row['sType']);
    }


if ($_GET['see'] == "Workshop_Gate")
{
	if ($_GET['aspect'] == "data")
	{
		echo ";var di_Workshop_Gate_data = [";
		for ($i=0;$i<sizeof($time);$i++)
		{
			$d="['".$type[$i]."','".$data[$i]."','".$time[$i]."'],";
			echo $d;
		}
	}
	echo "[0,0,0]];";
}


?>
