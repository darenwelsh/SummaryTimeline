<?php
/**
 * The SummaryTimeline extension generates a graphic representation
 * of an EVA summary timeline within MediaWiki.
 * 
 * Documentation: https://github.com/darenwelsh/SummaryTimeline
 * Support:       https://github.com/darenwelsh/SummaryTimeline
 * Source code:   https://github.com/darenwelsh/SummaryTimeline
 *
 * @file SummaryTimeline.class.php
 * @addtogroup Extensions
 * @author Daren Welsh
 * @copyright © 2014 by Daren Welsh
 * @licence GNU GPL v3+
 */

class SummaryTimeline
{

	static function setup ( &$parser ) {

		$parser->setFunctionHook(
			// name of parser function
			// same as $magicWords value set in MasonryMainPage.i18n.php 
			'summary-timeline', 
			array(
				'SummaryTimeline',  // class to call function from
				'renderSummaryTimeline' // function to call within that class
			),
			SFH_OBJECT_ARGS // defines format of how data is passed to function
		);

		return true;

	}

	static function renderSummaryTimeline ( &$parser, $frame, $args ) {
		// self::addCSS(); // adds the CSS files 

		//Run extractOptions on $args
		$options = self::extractOptions( $frame, $args );

		//Define the MMC Coord column output
		$textIV = "";
		$textIVi = 1;
		foreach ( $options['rows']['iv']['tasks'] as $task ) {
			$textIV .= $textIVi . ". " 
			. $options['rows']['iv']['tasks'][$textIVi]['title'] . ": "
	    	. "(" . $options['rows']['iv']['tasks'][$textIVi]['durationHour'] . ":"
	    	. $options['rows']['iv']['tasks'][$textIVi]['durationMinute'] . ")" . "\r\n\r\n"
	    	. "Related articles: " . $options['rows']['iv']['tasks'][$textIVi]['relatedArticles'] . "\r\n\r\n"
	    	. "Details: " . $options['rows']['iv']['tasks'][$textIVi]['details'] . "\r\n\r\n";
	    	$textIVi++;
	    }

		//Define the EV1 column output
		$textEV1 = "";
		$textEV1i = 1;
		foreach ( $options['rows']['ev1']['tasks'] as $task ) {
			$textEV1 .= $textEV1i . ". " 
			. $options['rows']['ev1']['tasks'][$textEV1i]['title'] . ": "
	    	. "(" . $options['rows']['ev1']['tasks'][$textEV1i]['durationHour'] . ":"
	    	. $options['rows']['ev1']['tasks'][$textEV1i]['durationMinute'] . ")" . "\r\n\r\n"
	    	. "Related articles: " . $options['rows']['ev1']['tasks'][$textEV1i]['relatedArticles'] . "\r\n\r\n"
	    	. "Details: " . $options['rows']['ev1']['tasks'][$textEV1i]['details'] . "\r\n\r\n";
	    	$textEV1i++;
	    }

		//Define the EV2 column output
		$textEV2 = "";
		$textEV2i = 1;
		foreach ( $options['rows']['ev2']['tasks'] as $task ) {
			$textEV2 .= $textEV2i . ". " 
			. $options['rows']['ev2']['tasks'][$textEV2i]['title'] . ": "
	    	. "(" . $options['rows']['ev2']['tasks'][$textEV2i]['durationHour'] . ":"
	    	. $options['rows']['ev2']['tasks'][$textEV2i]['durationMinute'] . ")" . "\r\n\r\n"
	    	. "Related articles: " . $options['rows']['ev2']['tasks'][$textEV2i]['relatedArticles'] . "\r\n\r\n"
	    	. "Details: " . $options['rows']['ev2']['tasks'][$textEV2i]['details'] . "\r\n\r\n";
	    	$textEV2i++;
	    }

		//Define the main output
		$text = 

			"Compact Version:"

			// UPDATE CLASS AND CSS
			. "<table class='summary-timeline-full-version'>"

	        //End of table
	        . "</table>"


			. "Full Version:" 

			// UPDATE CLASS AND CSS
			. "<table class='summary-timeline-full-version'>"

	        //Header
	        . "<tr><th>[[" . $options['title'] 
	        . "]]" . " (" 
        	. $options['eva duration hours'] . ":" . $options['eva duration minutes']
        	. ")</th></tr>"

	        //Rows
	        // NEED TO ADD CSS STYLING - width=100%, etc
			. "<tr>"

			//IV Column
			. "<td>"
			. "<table class='summary-timeline-full-version'><tr><th>IV/MCC (" . $options['rows']['iv']['tasksDuration'] . " min)</th></tr>"
			. "<tr><td>" . $textIV . "</td></tr></table>"
	        . "</td>"

			//EV1 Column
			. "<td>"
			. "<table class='summary-timeline-full-version'><tr><th>EV1 (" . $options['rows']['ev1']['tasksDuration'] . " min)</th></tr>"
			. "<tr><td>Egress (0:" . $options['ev1 egress duration minutes'] . ")</td></tr>"
			. "<tr><td>" . $textEV1 . "</td></tr>"
			. "<tr><td>Ingress (0:" . $options['ev1 ingress duration minutes'] . ")</td></tr>"
			. "</table>"
	        . "</td>"

			//EV2 Column
			. "<td>"
			. "<table class='summary-timeline-full-version'><tr><th>EV2 (" . $options['rows']['ev2']['tasksDuration'] . " min)</th></tr>"
			. "<tr><td>Egress (0:" . $options['ev2 egress duration minutes'] . ")</td></tr>"
			. "<tr><td>" . $textEV2 . "</td></tr>"
			. "<tr><td>Ingress (0:" . $options['ev2 ingress duration minutes'] . ")</td></tr>"
			. "</table>"
	        . "</td>"

	        // End of rows
	        . "</tr>"

	        //End of table
	        . "</table>";
		return $text;

	}

	/**
	 * Converts an array of values in form [0] => "name=value" into a real
	 * associative array in form [name] => value
	 *
	 * @param array string $options
	 * @return array $results
	 */
	static function extractOptions( $frame, array $args ) {
		$options = array();
		$tempTasks = array();
		$tasks = array();
		$taskDetails = array();
		$options['eva duration in minutes'] = 0;
	 
		foreach ( $args as $arg ) {
			//Convert args with "=" into an array of options
			$pair = explode( '=', $frame->expand($arg) , 2 );
			if ( count( $pair ) == 2 ) {
				$name = strtolower(trim( $pair[0] )); //Convert to lower case so it is case-insensitive
				$value = trim( $pair[1] );

				//this switch could be consolidated
				switch ($name) {
				    case 'title':
				        $options[$name] = $value;
				        break;
				    case 'eva duration hours':
				    	$options[$name] = $value;
				    	$options['eva duration in minutes'] += (60 * $value);
				        break;
				    case 'eva duration minutes':
				    	$options[$name] = $value;
				    	$options['eva duration in minutes'] += $value;
				        break;
			        case 'ev1 egress duration minutes':
			        case 'ev2 egress duration minutes':
			        case 'ev1 ingress duration minutes':
			        case 'ev2 ingress duration minutes':
				        $options[$name] = $value;
				        break;
				    case 'iv': // NEED TO SPLIT OUT SO THIS DOESN'T HAVE GET-AHEADS ADDED
				    case 'ev1':
				    case 'ev2':
					    $i = 1; /* Task id */
					    $tempTasks = explode ( '&&&', $value, 2 );
					    $tasks = explode ( '&&&', $tempTasks[1] );
						$tasksDuration = 0;
					    
					    foreach ( $tasks as $task ) {
					    	$taskDetails = explode( '@@@', $task);
					    	$options['rows'][$name]['tasks'][$i]['title'] = $taskDetails[0];
					    	$options['rows'][$name]['tasks'][$i]['durationHour'] = $taskDetails[1];
					    	$options['rows'][$name]['tasks'][$i]['durationMinute'] = $taskDetails[2];
					    	$options['rows'][$name]['tasks'][$i]['relatedArticles'] = $taskDetails[3];
					    	$options['rows'][$name]['tasks'][$i]['details'] = $taskDetails[4];

					    	// append task duration
					    	$tasksDuration += (60 * $taskDetails[1]) + $taskDetails[2];
					    	// print_r( $options['rows'][$name]['tasksDuration'] );
					    	$i++;
					    }

					    // NEED TO ADD EGRESS/INGRESS DURATION TO $tasksDuration
					    // NEED TO ACCOUNT FOR EV1 vs EV2
					    $tasksDuration += $options['ev2 egress duration minutes'] + $options['ev2 ingress duration minutes'];

					    // sum of time allotted to tasks
					    $options['rows'][$name]['tasksDuration'] = $tasksDuration;

					    // $options[$name] = self::extractTasks( $value );

					    // Check if $tasksDuration < $options['duration'] (EVA duration)
					    if( $tasksDuration < $options['eva duration in minutes'] ){
					    	// Need to add "Get Aheads" block to fill timeline gap

					    	// Calculate difference between EVA duration and tasksDuration
					    	$timeLeft = $options['eva duration in minutes'] - $tasksDuration;
					    	$timeLeftHours = floor($timeLeft/60);
					    	$timeLeftMinutes = $timeLeft%60;

					    	// Chose to place this block before last task assuming that will be "Ingress"
					    	// ADD LOGIC TO SWAP THIS BLOCK WITH ONE BEFORE IT
					    	$options['rows'][$name]['tasks'][$i]['title'] = $options['rows'][$name]['tasks'][$i-1]['title'];
					    	$options['rows'][$name]['tasks'][$i]['durationHour'] = $options['rows'][$name]['tasks'][$i-1]['durationHour'];
					    	$options['rows'][$name]['tasks'][$i]['durationMinute'] = $options['rows'][$name]['tasks'][$i-1]['durationMinute'];
					    	$options['rows'][$name]['tasks'][$i]['relatedArticles'] = $options['rows'][$name]['tasks'][$i-1]['relatedArticles'];
					    	$options['rows'][$name]['tasks'][$i]['details'] = $options['rows'][$name]['tasks'][$i-1]['details'];

					    	// Now set Get-Aheads block data
					    	$options['rows'][$name]['tasks'][$i-1]['title'] = 'Get-Aheads';
					    	$options['rows'][$name]['tasks'][$i-1]['durationHour'] = $timeLeftHours;
					    	$options['rows'][$name]['tasks'][$i-1]['durationMinute'] = $timeLeftMinutes;
					    	$options['rows'][$name]['tasks'][$i-1]['relatedArticles'] = 'Get-Ahead Task';
					    	$options['rows'][$name]['tasks'][$i-1]['details'] = 'Auto-generated block based on total EVA duration and sum of task durations';
					    }

				        break;
			        case 'ev1':
				        // Unique things for this column?
				        break;
			        case 'ev2':
				        // Unique things for this column?
				        break;
			        default: //What to do with args not defined above
				}

			}

		}

		//Check for empties, set defaults
		//Default 'title'
		if ( !isset($options['title']) || $options['title']=="" ) {
		        	$options['title']= "No title set!"; //no default, but left here for future options
	        }

	    //Logic for $duration
	    //Need logic for
	    //1. What to do if not 14:254? (e.g. 'Dog')
	    //2. split hours:minutes and sum minutes
	    //3. default = 6:30
	    if ( isset($value) ) {
	    	$input_time = explode( ':', $value , 2 );
		    if ( count ( $input_time ) == 2) {
		    	$hours = trim( $input_time[0] );
		    	$minutes = trim( $input_time[1] );
		    	$duration = ($hours * 60) + $minutes;
		    } else {
		    	$duration = $value;
		    }
		}

		// foreach ($variable as $key => $value) {
		// 	# code...
		// }

		return $options;
	}

	static function extractTasks( string $value ) {
		$tasks = array();
	 
		foreach ( $args as $arg ) {
			//Convert args with "=" into an array of options
			$pair = explode( '=', $frame->expand($arg) , 2 );
			if ( count( $pair ) == 2 ) {
				$name = strtolower(trim( $pair[0] )); //Convert to lower case so it is case-insensitive
				$value = trim( $pair[1] );

				//this switch could be consolidated
				switch ($name) {
			        case 'ev2':
				        $options['rows'][$name] = $value;
				        break;
			        default: //What to do with args not defined above
				}

			}

		}
	}

	static function addCSS ( $out ){
		global $wgScriptPath;

// 		$out->addScriptFile( $wgScriptPath .'/extensions/SummaryTimeline/summary-timeline.js' );

		$out->addLink( array(
			'rel' => 'stylesheet',
			'type' => 'text/css',
			'media' => "screen",
			'href' => "$wgScriptPath/extensions/SummaryTimeline/SummaryTimeline.css"
		) );
		
		return true;
	}
}
