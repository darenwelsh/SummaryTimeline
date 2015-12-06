<?php
# Ref https://mod2.jsc.nasa.gov/dm/DM33/TOPO_WEB/traj_data/stp/topo52.ISS.sun_lighting_events.txt

# TO-DO: Use web file instead of local
# this will require authentication
# $file =  file_get_contents('https://mod2.jsc.nasa.gov/dm/DM33/TOPO_WEB/traj_data/stp/topo52.ISS.sun_lighting_events.txt');

function renderDayNightRow( array $timeEvent, $inputTime, $timelineDuration ) {
	// These two variables are the first and last timestamps for data available via TOPO
	# TO-DO: Tell user range of dates which are eligible to use web data (in form)
	# TO-DO: Allow user to opt out of using web data if in that range
	$availableDataFirstTimestamp = $timeEvent[0]['timestamp'];
	$availableDataLastTimestamp  = $timeEvent[count($timeEvent)-1]['timestamp'];

	// Determine which row to use as first period (when PET = 000)
	// Start at first row of data and parse through until the input time passes the row time
	$timeIndex = 0;
	$rowTimestamp = $timeEvent[$timeIndex]['timestamp'];
	while( $inputTime > $rowTimestamp ){
		$rowTimestamp = $timeEvent[$timeIndex]['timestamp'];
		$timeIndex++; // increment row
	}
	$timeIndex = $timeIndex - 2; // Set $timeIndex to represent the period during which egress takes place (PET=000)

	print_r("\n\n=========================\n\n");

	print_r($timeIndex . ": " . date('Y-m-d H:i',$timeEvent[$timeIndex]['timestamp']) . " " . $timeEvent[$timeIndex]['event']);
	print_r("\n");
	print_r($timeIndex+1 . ": " . date('Y-m-d H:i',$timeEvent[$timeIndex+1]['timestamp']) . " " . $timeEvent[$timeIndex+1]['event']);
	print_r("\n\n");

	// $timeEvent is an array with all the events (sunset at 8:30, sunrise at 9:04, etc)
	// $dayNightOutput is a string with all the blocks to be displayed in the Summary Timeline
	$dayNightOutput = "";

	//TO-DO: If first block is <1%, don't do it
	// Generate first block (partial)
	// if PET = 000 time matches beginning of eclipse or insolation period, no need to handle partial period
	if( $timeEvent[$timeIndex+1]['timestamp'] != $inputTime ){
		// $dayNightBlockNumber = 1;
		$dayNightOutput .= $timeIndex;
		$dayNightOutput .= ": ";
		$dayNightOutput .= "left margin: ";
		$dayNightOutput .= "0% ";
		$dayNightOutput .= "width: ";
		$dayNightOutput .= floor(100 * (($timeEvent[$timeIndex + 1]['timestamp'] - $inputTime) / 60) / $timelineDuration);
		$dayNightOutput .= "% duration: ";
		$dayNightOutput .= ($timeEvent[$timeIndex + 1]['timestamp'] - $inputTime) / 60;
		$dayNightOutput .= " ";
		if( $timeEvent[$timeIndex]['event'] == "Full_Sunset" ){
			$dayNightOutput .= "eclipse";
		} else {
			$dayNightOutput .= "insolation";
		}
		$dayNightOutput .= "\n";

		$dayNightCumulativeDuration = ($timeEvent[$timeIndex + 1]['timestamp'] - $inputTime) / 60;
	} else {
		$dayNightCumulativeDuration = 0;
	}

	$dayNightAverages['eclipse']['value'] = ($timeEvent[$timeIndex + 1]['timestamp'] - $timeEvent[$timeIndex]['timestamp']) / 60;
	$dayNightAverages['eclipse']['num blocks'] = 1;


	// Determine if first block plus next block exceeds timeline duration. If so, don't add middle blocks.
	if( $dayNightCumulativeDuration + (($timeEvent[$timeIndex + 2]['timestamp'] - $timeEvent[$timeIndex + 1]['timestamp']) / 60) >= $timelineDuration ){
		$needMoreDayNightBlocks = false;
		if( $timeEvent[$timeIndex]['event'] == "Full_Sunset" ){
			$dayNightOutput .= "eclipse";
			$dayNightAverages['eclipse']['value'] = ($timeEvent[$timeIndex + 1]['timestamp'] - $timeEvent[$timeIndex]['timestamp']) / 60;
			$dayNightAverages['eclipse']['num blocks'] = 1;
			$dayNightAverages['insolation']['value'] = ($timeEvent[$timeIndex + 2]['timestamp'] - $timeEvent[$timeIndex + 1]['timestamp']) / 60;
			$dayNightAverages['insolation']['num blocks'] = 1;
		} else {
			$dayNightOutput .= "insolation";
			$dayNightAverages['eclipse']['value'] = ($timeEvent[$timeIndex + 2]['timestamp'] - $timeEvent[$timeIndex + 1]['timestamp']) / 60;
			$dayNightAverages['eclipse']['num blocks'] = 1;
			$dayNightAverages['insolation']['value'] = ($timeEvent[$timeIndex + 1]['timestamp'] - $timeEvent[$timeIndex]['timestamp']) / 60;
			$dayNightAverages['insolation']['num blocks'] = 1;
		}
	} else {
		$needMoreDayNightBlocks = true;
		$dayNightAverages['eclipse']['value'] = 0;
		$dayNightAverages['eclipse']['num blocks'] = 0;
		$dayNightAverages['insolation']['value'] = 0;
		$dayNightAverages['insolation']['num blocks'] = 0;
	}

	$timeIndex++;

	// Generate middle blocks
	while( $needMoreDayNightBlocks == true ){
		$dayNightOutput .= $timeIndex;
		$dayNightOutput .= ": ";
		$dayNightOutput .= "left margin: ";
		$dayNightOutput .= floor(100 * $dayNightCumulativeDuration / $timelineDuration);
		$dayNightOutput .= "% width: ";
		$dayNightOutput .= floor(100 * (($timeEvent[$timeIndex + 1]['timestamp'] - $timeEvent[$timeIndex]['timestamp']) / 60) / $timelineDuration);
		$dayNightOutput .= "% duration: ";
		$dayNightOutput .= ($timeEvent[$timeIndex + 1]['timestamp'] - $timeEvent[$timeIndex]['timestamp']) / 60;
		$dayNightOutput .= " ";
		if( $timeEvent[$timeIndex]['event'] == "Full_Sunset" ){
			$dayNightOutput .= "eclipse";
			$dayNightAverages['eclipse']['value'] += ($timeEvent[$timeIndex + 1]['timestamp'] - $timeEvent[$timeIndex]['timestamp']) / 60;
			$dayNightAverages['eclipse']['num blocks']++;
		} else {
			$dayNightOutput .= "insolation";
			$dayNightAverages['insolation']['value'] += ($timeEvent[$timeIndex + 1]['timestamp'] - $timeEvent[$timeIndex]['timestamp']) / 60;
			$dayNightAverages['insolation']['num blocks']++;
		}
		$dayNightOutput .= "\n";

		$dayNightCumulativeDuration = ($timeEvent[$timeIndex + 1]['timestamp'] - $inputTime) / 60;

		if( $dayNightCumulativeDuration + (($timeEvent[$timeIndex + 2]['timestamp'] - $timeEvent[$timeIndex + 1]['timestamp']) / 60) >= $timelineDuration ){
			$needMoreDayNightBlocks = false;
		}

		$timeIndex++;
	}

	// Generate final block
	if ( $dayNightCumulativeDuration < $timelineDuration ){
		$dayNightOutput .= $timeIndex;
		$dayNightOutput .= ": ";
		$dayNightOutput .= "left margin: ";
		$dayNightOutput .= floor(100 * $dayNightCumulativeDuration / $timelineDuration);
		$dayNightOutput .= "% width: ";
		$dayNightOutput .= 100 - floor(100 * $dayNightCumulativeDuration / $timelineDuration);
		$dayNightOutput .= "% duration: ";
		$dayNightOutput .= (($inputTime + (60 * $timelineDuration)) - $timeEvent[$timeIndex]['timestamp']) / 60;
		$dayNightOutput .= " ";
		if( $timeEvent[$timeIndex]['event'] == "Full_Sunset" ){
			$dayNightOutput .= "eclipse";
		} else {
			$dayNightOutput .= "insolation";
		}
		$dayNightOutput .= "\n";

	}


	// Calculate duration of eclipse and insolation for key
	$dayNightAverageEclipseDuration = $dayNightAverages['eclipse']['value'] / $dayNightAverages['eclipse']['num blocks']; // Used by Summary Timeline generation
	$dayNightAverageInsolationDuration = $dayNightAverages['insolation']['value'] / $dayNightAverages['insolation']['num blocks']; // Used by Summary Timeline generation

	print_r("Avg Eclipse   : " . round($dayNightAverageEclipseDuration) . " minutes\n");
	print_r("Avg Insolation: " . round($dayNightAverageInsolationDuration) . " minutes");

	print_r("\n\n=========================\n\n");

	print_r($dayNightOutput);

	print_r("\n\n=========================\n\n");

}

function getData(){
	$file = file_get_contents('topo52.ISS.sun_lighting_events.txt');
	$rows = explode("\n", $file);

	// Parse rows
	foreach($rows as $row => $data){
		// split the data elements by whitespace
		$rowData[$row] = preg_split("/[\s]+/", $data);

		// if row has >10 elements and starts with numbers, then it is likely real data and not a header
		// also only keep rows where 'event' == Start_Sunrise or Full_Sunset
		if( count($rowData[$row]) > 10
		&& preg_match("/^[0-9]/", $rowData[$row][1])
		&& ( $rowData[$row][10] == "Start_Sunrise" || $rowData[$row][10] == "Full_Sunset" ) ){
			$timeEvent[$row]['timestamp'] = $rowData[$row][1];
			$timeEvent[$row]['event']     = $rowData[$row][10];

			// reformat timestamp
			$tempTimeArray = preg_split("/:/", $timeEvent[$row]['timestamp']);
			$tempYear   = $tempTimeArray[0];
			$tempMonth  = $tempTimeArray[1];
			$tempDay    = $tempTimeArray[2];
			$tempHour   = $tempTimeArray[3];
			$tempMinute = $tempTimeArray[4];
			$timeEvent[$row]['timestamp'] = strtotime($tempYear . "-" . $tempMonth . "-" . $tempDay . " " . $tempHour . ":" . $tempMinute);
		}

	}

	// Remove all the rows (keys) we don't want
	$timeEvent = array_values($timeEvent);

	return $timeEvent;

}


date_default_timezone_set('UTC');
$inputTime = strtotime($argv[1]); // "YYYY-MM-DD HH:MM" when PET = 000
$timelineDuration = $argv[2]; // Timeline duration in minutes

$timeEvent = getData();

// These two variables are the first and last timestamps for data available via TOPO
# TO-DO: Tell user range of dates which are eligible to use web data (in form)
# TO-DO: Allow user to opt out of using web data if in that range
$availableDataFirstTimestamp = $timeEvent[0]['timestamp'];
$availableDataLastTimestamp  = $timeEvent[count($timeEvent)-1]['timestamp'];

$canUseData = "no"; // Used by Summary Timeline generation
// Determine if $inputTime exists within the available data (between first row and last row)
if( ($timeEvent[0]['timestamp'] < $inputTime) && (($inputTime + (60 * $timelineDuration)) < $timeEvent[count($timeEvent)-1]['timestamp']) ){
	renderDayNightRow($timeEvent, $inputTime, $timelineDuration);

	$canUseData = "yes"; // Used by Summary Timeline generation

} else {
	// We can't use this data from TOPO
	// So we'll have to use data from user

}






?>