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

/*
Considerations for improvement

STUFF TO DO BEFORE INITIAL RELEASE:
* Add key to denote color meanings (top of form, in both outputs)
* Explore options to add "actor" (SSRMS, IV, Eclipse, etc)
** Template:EV1 Task,etc becomes Template:Actor1/2/3/... 
** Pass Property:Actor name from form to template/php
** How does form allow for adding a column? Suppress for now?

ONE-PAGE VERSION (like page 1 of our timeline procedures)

FULL VERSION (more of an outline format)
* Add IV back in; rename all "COORD" references to "IV"
* Add if checks to only display when there is a value
* Color blocks
* Color key
* Make columns fill height

* Property:Depends on - should this only allow values from SIO-Task and Mission? Probably not.
*    Task depends on can be a list of {{EVA NAME}}{{TASK NAME}}
*    Hover over task and its dependencies flash/highlight

TEMPLATE
* Remove raw output? SIO is the only required content?

COMPACT OUTPUT:
* jQuery hover to highlight task block and footer entry on mouseover
* Try rounding biggest container div to nearest 5px to help rounding issues
* Allow titles to link to wiki pages (html vs text)

FORM:
* Related article for each task is not autocompleting, but EVA RA is - why?
* Check for min duration (10 min?)?
* Task depends on launch-date, task-completion, inhibit, etc
* Tasks coupled between EV1 and EV2
* Sync points
* This needs to have additional Get-Ahead block calculation just before the sync point
* Icons for new block or moving block are too big
* Consider shrink/expand on-click for each cell

FULL OUTPUT:
* Clean up divs and css
* Use same architecture as compact version for sizing
* Add link to Related article (US EVA 100 or C2V2)

CONCEPTS:
* Should compact version allow for "compact details" (different than full details)?

* Not just "IV" row/column, but be able to add more for SSRMS, eclipses, etc
* Eclipse constraints (shade cell, shade time rows?)

* How to implement in EVA pages? Sub-page is probably best with link on Template:EVA

* Split some parts into separate functions
* Clean up foreach() calls

* IV column needs to allow for events to sync with time or EV1/2 task begin

* Extra output to display interdependencies between tasks or even EVAs

* Update class names to allow for multiple summary timelines on one page with unique footers/styles (done?)

* Bingo time (red dashed line on both versions)

* Add logic to handle sum of tasks > EVA duration

* Change addCSS to addScripts

* jQueryUI for add-ons

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

		//Calculate relative position for hour time ticker marks
		$compactTimeTickerText = "";
		// $hours should be like 6.5 to get full duration properly
		$hours = $options['eva duration in minutes'] / 60;
		$hourTickerDivWidth = (100 / $hours);
		//Hour 0:00
		$compactTimeTickerText .= "<div class='time' "
			. "style='width:" . floor($hourTickerDivWidth) . "%;"
		    . " margin-left: 0%;"
		    . "'>0:00</div>";
	    //Hours in the middle
		for ($i = 1; $i < $hours-1; $i++) {
		    $compactTimeTickerText .= "<div class='time' style='width:" 
		    . ((floor($hourTickerDivWidth * ($i+1))) - (floor($hourTickerDivWidth * ($i))))
		    . "%;"
		    . " margin-left:" . (floor($hourTickerDivWidth * $i)) . "%;"
		    . "'>" . $i . ":00</div>";
		}
		//Final Hour ticker
		$compactTimeTickerText .= "<div class='time' "
			. "style='"
			. "width:" . (100 - (((floor($hourTickerDivWidth * ($i))) - (floor($hourTickerDivWidth * ($i-1)))) + (floor($hourTickerDivWidth * ($i-1))))) . "%;"
		    . " margin-left:" 
		    . (((floor($hourTickerDivWidth * ($i))) - (floor($hourTickerDivWidth * ($i-1)))) + (floor($hourTickerDivWidth * ($i-1))))
		    . "%;"
		    . "'>" . $i . ":00</div>";


		//COMPACT VERSION CONTENT DEFINITIONS
		//Define the EV1 column output
	    $compactTextEV1SumOfDurationMinutes = 0;
		$compactTextEV1 = "";

		// Egress
		// $compactTextEV1 .= "<div class='cell-border task-block' style='width:"
		// .	$options['ev1 egress duration minutes']['durationPercent']/* Calc % of EVA duration */
		// .	"%;"
	 //    .	" margin-left: 0%;"
		// .	"'>"
		// 	. 	"<div class='cell-body gray'>"
		// 	.	"<div row-id='EV1' class='responsive-text'>"
		// 	.	"Egress (0:" . $options['ev1 egress duration minutes']['durationMinutes'] . ")"
		// 	.	"</div>"
		// 	.	"</div>"
		// . "</div>";
		// $compactTextEV1SumOfDurationMinutes += $options['ev1 egress duration minutes']['durationMinutes'];

		// Tasks
		$compactTextEV1i = 1;
		foreach ( $options['rows']['ev1']['tasks'] as $task ) {
			$blockWidth = (/* margin-left of next block */
				(floor((($compactTextEV1SumOfDurationMinutes //Total tasks duration in minutes so far
					//Duration in minutes of next task
					+ ( (60 * $options['rows']['ev1']['tasks'][($compactTextEV1i)]['durationHour']) 
						+ $options['rows']['ev1']['tasks'][($compactTextEV1i)]['durationMinute'] ) )
					/ $options['eva duration in minutes'])*100))
				 - (floor(($compactTextEV1SumOfDurationMinutes / $options['eva duration in minutes'])*100)) );
			$blockMarginLeft = (floor(($compactTextEV1SumOfDurationMinutes / $options['eva duration in minutes'])*100));

			$compactTextEV1 .= 
			"<div class='cell-border task-block' style='width:"
			. $blockWidth
			. "%;"
			. " margin-left: "
			. $blockMarginLeft
			// . (floor(($compactTextEV1SumOfDurationMinutes / $options['eva duration in minutes'])*100)) //sum of widths so far
			. "%;"
			. "'>"
				. "<div class='cell-body " . $options['rows']['ev1']['tasks'][$compactTextEV1i]['color'] . "'>"
				//***********************************************
				//      TASK BLOCKS
				//***********************************************
				. "<div class='responsive-text'>"
				. $options['rows']['ev1']['tasks'][$compactTextEV1i]['title'] . " "
		    	. "(" . $options['rows']['ev1']['tasks'][$compactTextEV1i]['durationHour'] . ":"
		    	. $options['rows']['ev1']['tasks'][$compactTextEV1i]['durationMinute'] . ")"
				. "</div>"
				//***********************************************
				// 
				//***********************************************
				. "</div>"
			. "</div>";
	    	$compactTextEV1SumOfDurationMinutes += ( (60 * $options['rows']['ev1']['tasks'][$compactTextEV1i]['durationHour']) + $options['rows']['ev1']['tasks'][$compactTextEV1i]['durationMinute'] );
	    	$compactTextEV1i++;
	    }

		// Ingress
		// $compactTextEV1 .= "<div class='cell-border task-block' style='width:"
		// .	(100 - (floor(($compactTextEV1SumOfDurationMinutes / $options['eva duration in minutes'])*100)))
		// .	"%;"
	 //    .	" margin-left: "
	 //    .	(floor(($compactTextEV1SumOfDurationMinutes / $options['eva duration in minutes'])*100))
	 //    .	"%'>"
		// 	. 	"<div class='cell-body gray'>"
		// 	.	"<div class='responsive-text'>"
		// 	.	"Ingress (0:" . $options['ev1 ingress duration minutes']['durationMinutes'] . ")"
		// 	.	"</div>"
		// 	.	"</div>"
		// . "</div>";


		//Define the EV2 column output
	    $compactTextEV2SumOfDurationMinutes = 0;
		$compactTextEV2 = "";

		// Egress
		// $compactTextEV2 .= "<div class='cell-border task-block' style='width:"
		// .	$options['ev2 egress duration minutes']['durationPercent']/* Calc % of EVA duration */
		// .	"%;"
	 //    .	" margin-left: 0%;"
		// .	"'>"
		// 	. 	"<div class='cell-body gray'>"
		// 	.	"<div class='responsive-text'>"
		// 	.	"Egress (0:" . $options['ev2 egress duration minutes']['durationMinutes'] . ")"
		// 	.	"</div>"
		// 	.	"</div>"
		// . "</div>";
		// $compactTextEV2SumOfDurationMinutes += $options['ev2 egress duration minutes']['durationMinutes'];

		// Tasks
		// if( count($options['rows']['ev2']['tasks']) > 0 ){
			$compactTextEV2i = 1;
			foreach ( $options['rows']['ev2']['tasks'] as $task ) {
				$compactTextEV2 .= 
				"<div class='cell-border task-block' style='width:"
				// . $options['rows']['ev2']['tasks'][$compactTextEV2i]['durationPercent']
				. (/* margin-left of next block */
					(floor((($compactTextEV2SumOfDurationMinutes //Total tasks duration in minutes so far
						//Duration in minutes of next task
						+ ( (60 * $options['rows']['ev2']['tasks'][($compactTextEV2i)]['durationHour']) 
							+ $options['rows']['ev2']['tasks'][($compactTextEV2i)]['durationMinute'] ) )
						/ $options['eva duration in minutes'])*100))
					 - (floor(($compactTextEV2SumOfDurationMinutes / $options['eva duration in minutes'])*100)) )
				. "%;"
				. " margin-left: "
				. (floor(($compactTextEV2SumOfDurationMinutes / $options['eva duration in minutes'])*100)) //sum of widths so far
				. "%;"
				. "'>"
					. "<div class='cell-body " . $options['rows']['ev2']['tasks'][$compactTextEV2i]['color'] . "'>"
					//***********************************************
					//      TASK BLOCKS
					//***********************************************
					. "<div class='responsive-text'>"
					. $options['rows']['ev2']['tasks'][$compactTextEV2i]['title'] . " "
			    	. "(" . $options['rows']['ev2']['tasks'][$compactTextEV2i]['durationHour'] . ":"
			    	. $options['rows']['ev2']['tasks'][$compactTextEV2i]['durationMinute'] . ")"
					. "</div>"
					//***********************************************
					// 
					//***********************************************
					. "</div>"
				. "</div>";
		    	$compactTextEV2SumOfDurationMinutes += ( (60 * $options['rows']['ev2']['tasks'][$compactTextEV2i]['durationHour']) + $options['rows']['ev2']['tasks'][$compactTextEV2i]['durationMinute'] );
		    	$compactTextEV2i++;
		    }
		// }

		// Ingress
		// $compactTextEV2 .= "<div class='cell-border task-block' style='width:"
		// .	(100 - (floor(($compactTextEV2SumOfDurationMinutes / $options['eva duration in minutes'])*100)))
		// .	"%;"
	 //    .	" margin-left: "
	 //    .	(floor(($compactTextEV2SumOfDurationMinutes / $options['eva duration in minutes'])*100))
	 //    .	"%'>"
		// 	. 	"<div class='cell-body gray'>"
		// 	.	"<div class='responsive-text'>"
		// 	.	"Ingress (0:" . $options['ev2 ingress duration minutes']['durationMinutes'] . ")"
		// 	.	"</div>"
		// 	.	"</div>"
		// . "</div>";


		//FULL VERSION CONTENT DEFINITIONS
		//Define the MMC Coord column output
		$textIV = "";
		$textIVi = 1;
		foreach ( $options['rows']['iv']['tasks'] as $task ) {
			$textIV .= $textIVi . ". " 
			. $options['rows']['iv']['tasks'][$textIVi]['title'] . ": "
	    	. "(" . $options['rows']['iv']['tasks'][$textIVi]['durationHour'] . ":"
	    	. $options['rows']['iv']['tasks'][$textIVi]['durationMinute'] . ")" . "\r\n\r\n"
	    	. "Related articles: " . $options['rows']['iv']['tasks'][$textIVi]['relatedArticles'] . "\r\n\r\n"
	    	. "Details: " . $options['rows']['iv']['tasks'][$textIVi]['details'] . "\r\n\r\n\r\n";
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
	    	. "Details: " . $options['rows']['ev1']['tasks'][$textEV1i]['details'] . "\r\n\r\n\r\n";
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
	    	. "Details: " . $options['rows']['ev2']['tasks'][$textEV2i]['details'] . "\r\n\r\n\r\n";
	    	$textEV2i++;
	    }

		//Define the main output
		$text = "";

		if ($options['format'] == 'compact'){

			/**********************
			
			Compact Version Output

			**********************/

			// Using CSS "tables"
			// Title
			$text .= "<div style='position: relative; margin: 10px 10px 0px 10px;
				font-weight: bold;'>[[" . $options['title link'] . "|" . $options['title'] . "]] (" 
        	. $options['eva duration hours'] . ":";

	    	if ( strlen($options['eva duration minutes']) == 1 ){
	    		$text .= "0" . $options['eva duration minutes'];
	    	} else {
				$text .= $options['eva duration minutes'];
	    	}

        	$text .=  ")"
			. "</div>"

			// Task dependencies
			. "<div style='position: relative; margin: 0px 10px 0px 10px;
				font-size: 75%;'>Dependencies: " . $options['depends on']
			. "</div>"

			// EVA related articles
			. "<div style='position: relative; margin: 0px 10px 0px 10px;
				font-size: 75%;'>Related articles: " . $options['parent related article']
			. "</div>"

			// Begin main div
			. "<div class='summary-timeline-compact-version' id='summary-timeline-" . $options['st index'] . "'>"

			// Begin outer container
			. "<div class='container'>"

			// Begin left label column
				. "<div class='left column'>"
				. "<div class='summary-timeline-row' style='height: 0px; border-left-width: 0px; '>"
				. "</div>"
				. "<div class='tasks summary-timeline-row' style='font-weight: bold;'>"
					. "EV1"
				. "</div>"
				. "<div class='tasks summary-timeline-row' style='font-weight: bold;'>"
					. "EV2"
				. "</div>"

			// End left label column
			. "</div>"

			// Begin main body column
			. "<div class='right column'>"

			// Begin top time labels row
			. "<div class='summary-timeline-row'>"

			// Top time labels
			. $compactTimeTickerText

			// End top time labels row
			. "</div>"

			// Begin EV1 Row
			. "<div id='summary-timeline-row-EV1' class='summary-timeline-row summary-timeline-tasks-row'>"

			.	$compactTextEV1

			// End EV1 row
			. "</div>"

			// Begin EV2 Row
			. "<div id='summary-timeline-row-EV2' class='summary-timeline-row summary-timeline-tasks-row'>"

			// Tasks
			.	$compactTextEV2

			// End EV2 row
			. "</div>"

			// NEED TO ADD background-color:red(new $variable); ONCE COLOR OPTIONS ARE ADDED

			// Begin Footer Row
			. "<div class='summary-timeline-row'>"

			// Footer Entries
			// This is driven by SummaryTimeline.js
			// . "<div id='summary-timeline-footer-" . $options['st index'] . "' class='footer'>"
			. "<div id='summary-timeline-footer-" . $options['st index'] . "' class='footer'>"
				//Entries will be placed here by the JS
			. "</div>"

			// End Footer row
			. "</div>"

			// End of main body column div
			. "</div>"

			// End of outer container div
			. "</div>"

	        // End of main div
	        . "</div>";

	    } elseif ($options['format'] == 'full'){

			/*******************
			
			Full Version Output

			*******************/

			// NEED TO SPLIT TO "FULL" AND "ONE-PAGE" VERSION
			// Title
			$text .= "<div style='position: relative; margin: 10px 10px 0px 10px;
				font-weight: bold;'>[[" . $options['title link'] . "|" . $options['title'] . "]] (" 
        	. $options['eva duration hours'] . ":";

	    	if ( strlen($options['eva duration minutes']) == 1 ){
	    		$text .= "0" . $options['eva duration minutes'];
	    	} else {
				$text .= $options['eva duration minutes'];
	    	}       	

        	$text .= ")"
			. "</div>"

			// EVA related articles
			. "<div style='position: relative; margin: 0px 10px 0px 10px;
				font-size: 75%;'>Related articles: " . $options['parent related article']
			. "</div>"

			// Begin main div
			. "<div class='summary-timeline-full-version' id='summary-timeline-" . $options['st index'] . "'>"

			// Begin outer container
			. "<div class='container'>"
			. "<div class='content'>"

			// Begin header row
			. "<div class='summary-timeline-row'>"

			// Header
			//NEED TO SET LEFT/RIGHT TIME TICKERS TO STATIC PX WIDTH, LEAVE MIDDLE COLS TO remaining%
			// . "<div class='summary-timeline-column' style='width:5%; margin-left: 0%;'>"
				// . "<div class='summary-timeline-header'>PET</div>"
			// . "</div>"
			. "<div class='summary-timeline-column' style='width:33%; margin-left: 0%;'>"
				. "<div class='summary-timeline-header'>IV</div>"
			. "</div>"
			. "<div class='summary-timeline-column' style='width:33%; margin-left: 33%;'>"
				. "<div class='summary-timeline-header'>EV1</div>"
			. "</div>"
			. "<div class='summary-timeline-column' style='width:34%; margin-left: 66%;'>"
				. "<div class='summary-timeline-header'>EV2</div>"
			. "</div>"
			// . "<div class='summary-timeline-column' style='width:5%; margin-left: 95%;'>"
				// . "<div class='summary-timeline-header'>&nbsp;</div>"
			// . "</div>"

			// End header row
			. "</div>"

			// Begin main body row
			// . "<div class='right column'>"
			. "<div class='summary-timeline-row'>"

			//NEED TO SET LEFT/RIGHT TIME TICKERS TO STATIC PX WIDTH, LEAVE MIDDLE COLS TO remaining%
			// . "<div class='summary-timeline-column' style='width:5%; margin-left: 0%;'>"
				// . "<div class='summary-timeline-header'>0:00</div>"
			// . "</div>"
			. "<div class='summary-timeline-column' style='width:33%; margin-left: 0%;'>"
				. "<div class='summary-timeline-body'>". $textIV . "</div>"
			. "</div>"
			. "<div class='summary-timeline-column' style='width:33%; margin-left: 33%;'>"
				. "<div class='summary-timeline-body'>". $textEV1 . "</div>"
			. "</div>"
			. "<div class='summary-timeline-column' style='width:34%; margin-left: 66%;'>"
				. "<div class='summary-timeline-body'>". $textEV2 . "</div>"
			. "</div>"
			// . "<div class='summary-timeline-column' style='width:5%; margin-left: 95%;'>"
				// . "<div class='summary-timeline-header'>0:00</div>"
			// . "</div>"

			// Begin left time labels column
			// . "<div class='summary-timeline-row'>"

			// Left time labels
			// . $compactTimeTickerText



			// CONVERT ALL TABLE ELEMENTS TO DIVS
			// . "<table class='summary-timeline-full-version'>"

	        //Rows
			// . "<tr>"

			//IV Column
			// . "<td>"
			// . "<table class='summary-timeline-full-version'><tr><th>IV/MCC (" . $options['rows']['iv']['tasksDuration'] . " min)</th></tr>"
			// . "<tr><td>" . $textIV . "</td></tr></table>"
	  //       . "</td>"

			//EV1 Column
			// . "<td>"
			// . "<table class='summary-timeline-full-version'><tr><th>EV1 (" . $options['rows']['ev1']['tasksDuration'] . " min)</th></tr>"
			// . "<tr><td>Egress (0:" . $options['ev1 egress duration minutes']['durationMinutes'] . ")</td></tr>"
			// . "<tr><td>" . $textEV1 . "</td></tr>"
			// . "<tr><td>Ingress (0:" . $options['ev1 ingress duration minutes']['durationMinutes'] . ")</td></tr>"
			// . "</table>"
	  //       . "</td>"

			//EV2 Column
			// . "<td>"
			// . "<table class='summary-timeline-full-version'><tr><th>EV2 (" . $options['rows']['ev2']['tasksDuration'] . " min)</th></tr>"
			// . "<tr><td>Egress (0:" . $options['ev2 egress duration minutes']['durationMinutes'] . ")</td></tr>"
			// . "<tr><td>" . $textEV2 . "</td></tr>"
			// . "<tr><td>Ingress (0:" . $options['ev2 ingress duration minutes']['durationMinutes'] . ")</td></tr>"
			// . "</table>"
	        // . "</td>"

	        // End of rows
	        // . "</tr>"

	        //End of table
	        // . "</table>"

			// End main body row
			. "</div>"

			// End of outer container div
			. "</div>"
			. "</div>"

	        // End of main div
	        . "</div>";
	    }
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
		$tasksDurationPercentTotal = array();
		$tasksDurationPercentTotal['ev1'] = 0;
		$tasksDurationPercentTotal['ev2'] = 0;
		$tasksDurationPercentTotal['iv'] = 0; /* This will be removed once the IV section is fixed */
	 
		foreach ( $args as $arg ) {
			//Convert args with "=" into an array of options
			$pair = explode( '=', $frame->expand($arg) , 2 );
			if ( count( $pair ) == 2 ) {
				$name = strtolower(trim( $pair[0] )); //Convert to lower case so it is case-insensitive
				$value = trim( $pair[1] );

				//this switch could be consolidated
				switch ($name) {
					case 'format': 
						$value = strtolower($value);
						if ( $value=="full" ) {
				        	$options['format'] = "full";
				        } else {
				        	$options['format'] = "compact";
				        }
				        break;
			        case 'st index':
				        $options[$name] = $value;
				        break;
				    case 'title':
					    if ( !isset($value) || $value=="" ) {
				        	$options['title']= "No title set!";
				        } else {
				        	$titleParts = explode( '@@@', $value);
				        	$options[$name] = $titleParts[0];
				        	$options['title link'] = $titleParts[1];
				        }
				        break;
			        case 'depends on':
				        if ( isset($value) ) {
				        	$options[$name] = $value;
				        }
				        break;
			        case 'parent related article':
				        if ( isset($value) ) {
				        	$options[$name] = $value;
				        }
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
			        case 'ev1 ingress duration minutes':
				        $options[$name]['durationMinutes'] = $value;
				        $options[$name]['durationPercent'] = floor(($value / $options['eva duration in minutes']) * 100);
				        $tasksDurationPercentTotal['ev1'] += $options[$name]['durationPercent'];
				        break;
			        case 'ev2 egress duration minutes':
			        case 'ev2 ingress duration minutes':
				        $options[$name]['durationMinutes'] = $value;
				        $options[$name]['durationPercent'] = floor(($value / $options['eva duration in minutes']) * 100);
				        $tasksDurationPercentTotal['ev2'] += $options[$name]['durationPercent'];
				        break;
				    case 'iv': // NEED TO SPLIT OUT SO THIS DOESN'T HAVE GET-AHEADS ADDED
					    // this should have blocks with "Start time" (not duration)
					    // an option should be included to sync with a task on EV1 and/or EV2
					    // break;
				    case 'ev1':
				    case 'ev2':
					    $i = 1; /* Task id */
						$tasksDuration = 0;
					    if($value != ""){
						    $tempTasks = explode ( '&&&', $value, 2 );
						    $tasks = explode ( '&&&', $tempTasks[1] );
						    
						    foreach ( $tasks as $task ) {
						    	$taskDetails = explode( '@@@', $task);
						    	$options['rows'][$name]['tasks'][$i]['title'] = $taskDetails[0];
						    	if ($taskDetails[1] == ''){$taskDetails[1] = '0';}
						    	$options['rows'][$name]['tasks'][$i]['durationHour'] = $taskDetails[1];
						    	if ($taskDetails[2] == ''|'0'){$taskDetails[2] = '00';}
						    	if ( strlen($taskDetails[2]) == 1 ){
						    		$temp = $taskDetails[2];
						    		$taskDetails[2] = '0' . $temp;}
						    	$options['rows'][$name]['tasks'][$i]['durationMinute'] = $taskDetails[2];
						    	//Lame attempt to set min block width - move value out?
						    	// if ($options['rows'][$name]['tasks'][$i]['durationHour'] == 0 && $options['rows'][$name]['tasks'][$i]['durationMinute']<15){
						    	// 	$options['rows'][$name]['tasks'][$i]['blockWidth'] = 15;
						    	// }
						    	$options['rows'][$name]['tasks'][$i]['relatedArticles'] = $taskDetails[3];
						    	$options['rows'][$name]['tasks'][$i]['color'] = $taskDetails[4];
						    	$options['rows'][$name]['tasks'][$i]['details'] = $taskDetails[5];

						    	// Calc task duration as % of total EVA duration
						    	$options['rows'][$name]['tasks'][$i]['durationPercent'] = round((((60 * $taskDetails[1]) + $taskDetails[2]) / $options['eva duration in minutes']) * 100);

						    	// append task duration
						    	$tasksDuration += (60 * $taskDetails[1]) + $taskDetails[2];
						    	// append task duration percent
						    	$tasksDurationPercentTotal[$name] += $options['rows'][$name]['tasks'][$i]['durationPercent'];
						    	// print_r( $tasksDurationPercentTotal['ev1'] );
						    	$i++;
						    }
						}

					    // NEED TO ADD EGRESS/INGRESS DURATION TO $tasksDuration
					    // NEED TO ACCOUNT FOR EV1 vs EV2

					    // Commented out due to new template structure including egress/ingress as tasks
					    // $tasksDuration += $options['ev2 egress duration minutes']['durationMinutes'] + $options['ev2 ingress duration minutes']['durationMinutes'];

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

							// THE FOLLOWING MOVES GET-AHEADS TO SECOND-TO-LAST SPOT
					    	$options['rows'][$name]['tasks'][$i]['title'] = $options['rows'][$name]['tasks'][$i-1]['title'];
					    	$options['rows'][$name]['tasks'][$i]['durationHour'] = $options['rows'][$name]['tasks'][$i-1]['durationHour'];
					    	$options['rows'][$name]['tasks'][$i]['durationMinute'] = $options['rows'][$name]['tasks'][$i-1]['durationMinute'];
					    	$options['rows'][$name]['tasks'][$i]['relatedArticles'] = $options['rows'][$name]['tasks'][$i-1]['relatedArticles'];
					    	$options['rows'][$name]['tasks'][$i]['color'] = $options['rows'][$name]['tasks'][$i-1]['color'];
					    	$options['rows'][$name]['tasks'][$i]['details'] = $options['rows'][$name]['tasks'][$i-1]['details'];

					    	// Now set Get-Aheads block data
					    	$options['rows'][$name]['tasks'][$i-1]['title'] = 'Get-Aheads';
						    	if ($timeLeftHours == ''){$timeLeftHours = '0';}
					    	$options['rows'][$name]['tasks'][$i-1]['durationHour'] = $timeLeftHours;
						    	if ($timeLeftMinutes == ''|'0'){$timeLeftMinutes = '00';}
						    	if ( strlen($timeLeftMinutes) == 1 ){
						    		$temp = $timeLeftMinutes;
						    		$timeLeftMinutes = '0' . $temp;}
					    	$options['rows'][$name]['tasks'][$i-1]['durationMinute'] = $timeLeftMinutes;
					    	$options['rows'][$name]['tasks'][$i-1]['relatedArticles'] = 'Get-Ahead Task';
					    	$options['rows'][$name]['tasks'][$i-1]['color'] = 'white';
					    	$options['rows'][$name]['tasks'][$i-1]['details'] = 'Auto-generated block based on total EVA duration and sum of task durations';
					    	// Calc task duration as % of total EVA duration
					    	// $options['rows'][$name]['tasks'][$i]['durationPercent'] = round((((60 * $timeLeftHours) + $timeLeftMinutes) / $options['eva duration in minutes']) * 100);
							$options['rows'][$name]['tasks'][$i-1]['durationPercent'] = 100 - $tasksDurationPercentTotal[$name];

					    }

				        break;
			        case 'ev1':
				        // Unique things for this column? Would have to split above into these two (can't do both cases)
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
		// if ( !isset($options['title']) || $options['title']=="" ) {
		//         	$options['title']= "No title set!"; //no default, but left here for future options
	 //        }

	    //Logic for $duration
	    //Need logic for
	    //1. What to do if not 14:254? (e.g. 'Dog')
	    //2. split hours:minutes and sum minutes
	    //3. default = 6:30
	 //    if ( isset($value) ) {
	 //    	$input_time = explode( ':', $value , 2 );
		//     if ( count ( $input_time ) == 2) {
		//     	$hours = trim( $input_time[0] );
		//     	$minutes = trim( $input_time[1] );
		//     	$duration = ($hours * 60) + $minutes;
		//     } else {
		//     	$duration = $value;
		//     }
		// }

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

		$out->addScriptFile( $wgScriptPath .'/extensions/SummaryTimeline/SummaryTimeline.js' );

		$out->addLink( array(
			'rel' => 'stylesheet',
			'type' => 'text/css',
			'media' => "screen",
			'href' => "$wgScriptPath/extensions/SummaryTimeline/SummaryTimeline.css"
		) );
		
		return true;
	}
}
