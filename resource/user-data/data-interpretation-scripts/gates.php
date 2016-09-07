<?php 
	/*
	 * This file is two things:
	 * 
	 * 1, It provides interpretation of magnetic gates. That is, gates with Hall Effect Sensors on them,
	 * which read in a magnetic feild strength. Since a magnet is attached to the swinging part of the gate
	 * and the sensor on the frame, this track whether the gate is closed, open, or closed but not fully latched.
	 * This is to provide the Orokonui ecostantuary with gate status data, which was why this project was created
	 * but...
	 * 
	 * 2. Provides an example of how data intepretation script can work.
	 * 
	 * For this second reason, it is provided with the source code of this project, and indeed considered part of it.
	 * However, you are most welcome to remove it without any harm coming to the rest of your system.
	 * Also, note that while this is considered part of source, and therefore under the GPL, anything you place in 
	 * here is not.
	 * 
	 * Note, it also comes with the data file, 'gate.dat' - If you are removing this, you will want to remove that as well.
	 * 
	 * Please keep this file if you are contributing upstream, or stop git from tracking it, as pull request that remove it
	 * will not be accepted.
	 */

	function load_data()
	{
		if(file_exists('gate.dat'))
		{
			$f = fopen('gate.dat', 'r');
			$dat = fread($f, filesize('gate.dat'));
			fclose($f);
			// reading in values
			$dat = explode("\n", $dat);
			$lines = array();
			for ($i=0;$i<sizeof($dat);$i++)
			{
				if (strpos($dat[$i],'#')===False)
				{
					array_push($lines, explode(':', $dat[$i]));
				}
			}
			return $lines;
		}
		else
		{
			// this is the initial startup, read in database and set base
			// values. Create data file.
			$buffer = '#gate data file, this is used by the gate magnetic tracker DO NOT MODIFIY, DO NOT DELETE.\n';
			//~ $db = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
			//~ $q = "SELECT * FROM sensor_data";
			//~ $r = $db->query($q);
			//~ while ($row = $r->fetch_assoc())
			//~ {
				//~ if ($row['sType'] == 'GateOpenReading')
				//~ {
					//~ $buffer .= 'initOpen:'.$row['sValue'].'\n';
				//~ }
				//~ if ($row['sType'] == 'GateUnlatchedReading')
				//~ {
					//~ $buffer .= 'initUnlatched'.$row['sValue'].'\n';
				//~ }
			//~ }
			$f = fopen('gate.dat', 'w');
			fwrite($f, $buffer);
			fclose($f);
			// load newly created dat file
			load_data();
		}
	}
	
	function save_data($lines, $checkvalue, $checktype)
	{
		$buffer = '#gate data file, this is used by the gate magnetic tracker DO NOT MODIFIY, DO NOT DELETE.\n';
		for ($i=0;$i<sizeof($lines);$i++) 
		{
			$buffer.=$lines[$i].'\n';
		}
		$f = fopen('gate.php', 'w');
		fwrite($f, $buffer); 
		fclose($f);
	}
	
	function calc_new_readings($oldc, $oldu, $avec, $aveu, $count)
	{
		if ($count < 50)
		{
			return $oldc, $oldu;
		}
		else
		{
			// adds a 50% buffer
			if ($oldc>$oldu)
			{
				$diff = $oldc-$oldu;
			} 
			else
			{
				$diff = $oldu-$oldc;
			}
			$diff = $diff*0.85;
			$buffer = $diff*0.5;
			if ($oldc>$avec)
			{
				$newc = $avec+$buffer;
			}
			else
			{
				$newc = $avec+$buffer;
			}
			if ($oldu>$aveu)
			{
				$newu = $aveu+$buffer;
			}
			else
			{
				$newu = $aveu+$buffer;
			}
			return $newc, $newu;
		}
	}
	
	function get_ranges($dat)
	{
		$aveo = 0;
		$aveu = 0;
		$avec = 0;
		$countc = 0;
		$countu = 0;
		$counto = 0;
		$reinitialclosedreading = 0;
		$reinitialunlatchedreading = 0;
		for ($i=0;$i<sizeof($dat);$i++)
		{
			if ($dat[$i][0]=="!init")
			{
				for ($j=$i;$j<sizeof($dat);$j++)
				{
					if ($dat[$j]=='!')
					{
						$i=$j;
						break;
					}
					if ($dat[$j][0]=='ic')
					{
						$initialclosedreading=$dat[$j][1];
					}
					if ($dat[$j][0]=='iu')
					{
						$initialunlatchedreading=$dat[$j][1];
					}
				}
			}
			if ($dat[$i][0]=="!reinit")
			{
				for ($j=$i;$j<sizeof($dat);$j++)
				{
					if ($dat[$j]=='!')
					{
						$i=$j;
						break;
					}
					if ($dat[$j][0]=='rc')
					{
						$reinitialclosedreading=$dat[$j][1];
					}
					if ($dat[$j][0]=='ru')
					{
						$reinitialunlatchedreading=$dat[$j][1];
					}
				}
			}
			if ($dat[$i][0]=='!norop')
			{
				for ($j=$i;$j<sizeof($dat);$j++)
				{
					if ($dat[$j]=='!')
					{
						$i=$j;
						break;
					}
					if ($dat[$j][0]=='o')
					{
						$aveo += $dat[$j][1];
						$counto++;
					}
					if ($dat[$j][0]=='c')
					{
						$avec += $dat[$j][1];
						$countc++;
					}
					if ($dat[$j][0]=='u')
					{
						$aveu += $dat[$j][1];
						$countu++;
					}
				}
			}
		}
		$aveo /=$counto;
		$avec /=$countc;
		$aveu /=$countu;
		// get +/- range, this is 90 percent of the half difference
		if ($reinitialunlatchedreading!=0)
		{
			$uuse = $reinitialunlatchedreading;
		}
		else
		{
			$uuse = $initialunlatchedreading;
		}
		if ($reinitialclosedreading!=0)
		{
			$cuse = $reintialclosedreading;
		}
		else
		{
			$cuse = $initialclosedreading;
		}
		if ($cuse>$uuse)
		{
			$diff = $cuse-$uuse;
		} 
		else
		{
			$diff = $uuse-$cuse;
		}
		$range = $diff*0.85;
		// checks if the average is within 80% of the max/min
		if ($avec>(($range*0.8)+$cuse)
		{
			$newc, $newo = calc_new_readings($cuse, $uuse, $avec, $aveu, $countc);
		}
		if ($avec<($cuse-($range*0.8))
		{
			$newc, $newo = calc_new_readings($cuse, $uuse, $avec, $aveu, $countc);
		}
		if ($aveu>(($range*0.8)+$uuse)
		{
			$newc, $newo = calc_new_readings($cuse, $uuse, $avec, $aveu, $countc);
		}
		if ($aveu<($uuse-($range*0.8))
		{
			$newc, $newo = calc_new_readings($cuse, $uuse, $avec, $aveu, $countc);
		}
	}
	
	
	// main program, always executes
	//$db = new mysqli("localhost", "bot", "TSMD4B6oy6BZPRyq", "orokonui");
	//$q = "SELECT * FROM sensor_data";
	//$r = $db->query($q);
	$mydat = load_data();
	get_ranges($mydat,0,0);
	//~ while ($row = $r->fetch_assoc())
	//~ {
		//~ if ($row['sType'] == 'Gate')
		//~ {
			//~ // find the latest gate reading, compare /w thresholds
			//~ 
		//~ }
	//~ }
	//~ print_r($mydat);
?>
