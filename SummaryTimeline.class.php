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

MISSION PAGES (VV):
* Add Property:Required for EVA#list for values EVA title
** Modify Template:Manifest item on mission
** This can be queried via Template:Summary Timeline Output for Manifest item on mission::+
** where [[Required for EVA::EVA title]]
** Upgrade: Allow for listing [[Required for Task::Has text title]]
*
* ORIGINAL IDEA:
** Property:Depends on - should this only allow values from SIO-Task and Mission? Probably not.
**    Task depends on can be a list of
**     Actor1##{{TASK NAME}},
**     {{EVA NAME}}##{{EV2}}##{{TASK NAME}}, //[[SSU EVA]] ([[SSU EVA Summary Timeline]])
**     {{MISSION NAME}}##{{HARDWARE NAME}}  // Manifest <-- remove this becasue Mission Pages idea is better

COMPACT OUTPUT:
* Get-Aheads is currently always second-to-last. This doesn't work for non-EVA (w/ Ingress) timelines
* jQuery hover to highlight task block and footer entry on mouseover
** Popup (on hover) shows Details
* Try rounding biggest container div to nearest 5px to help rounding issues
* Allow titles to link to wiki pages (html vs text)
* Add JS to test actor name width vs col width and use JQuery to increase left width and right margin-left and reduce container margin-right

OVERVIEW PAGE
* EVA/Launch/ROBO Dates
* Dependencies

ONE-PAGE VERSION (like page 1 of our timeline procedures)
* Keep aspect ratio (11x8.5)
* Truncate details if it doesn't fit

FULL VERSION (more of an outline format)
* Add IV back in
** rename all "COORD" references to "IV"
** remove egress/ingress from all other actors
** Add if checks to only display when there is a value
* Color blocks
* Color key
* Make columns fill height
* HTML vs text (html currently breaks it)

TEMPLATE
* Remove raw output?
** SIO is the only required content?
** Better document how each component works

FORM:
* Related article for each task is not autocompleting, but EVA RA is - why? MM on ops does, on dev does not
* Check for min duration (10 min?)?
* Task depends on launch-date, task-completion, inhibit, etc
* ADD field TaskStartTime (add to form, Actor 1/2/3 Task template, query and results templates, php)
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
* Actors
** Allow for 3 actors now, add up to 5 or 6 later and add SemanticForms element
** Eclipse constraints (shade cell, shade time rows?)

* Split some parts into separate functions
* Clean up foreach() calls

* Sync points
** Is this the same as dependencies within an EVA?
** IV column needs to allow for events to sync with time or EV1/2 task begin

* Dependencies
** Extra output to display interdependencies between tasks or even EVAs
**    Hover over task and its dependencies flash/highlight on page above

* Bingo time (red dashed line on both versions)

* Add logic to handle sum of tasks > EVA duration

* jQueryUI for add-ons

* Task Homeless List

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
		//Run extractOptions on $args
		$options = self::extractOptions( $frame, $args );

		//Generate color key
		$colorKeyText = "";
		$colors = array("red","orange","yellow","green","blue","purple","pink","white","gray","black",);
		foreach ($colors as $value) {
			if ($options["color $value meaning"] != '') {
				$colorKeyText .= "<div class='color-key'>"
				. "<div style='height: 10px;' class='$value'></div>"
				. "<div style='padding: 2px;'>" . $options["color $value meaning"] . "</div></div>";
			}
		}

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


	    // *************************************
		//  COMPACT VERSION CONTENT DEFINITIONS
	    // *************************************
	    foreach ( $options['rows'] as &$actor ){
			if( $actor['display in compact view']=='true' && count( $actor['tasks']) > 0 ){
			    $compactTextActorSumOfDurationMinutes = 0;
				$actor['compact text'] = "";

				// Tasks
				$compactTexti = 1;
				foreach ( $actor['tasks'] as $task ) {
					$blockWidth = (/* margin-left of next block */
						(floor((($compactTextActorSumOfDurationMinutes //Total tasks duration in minutes so far
							//Duration in minutes of next task
							+ ( (60 * $actor['tasks'][($compactTexti)]['durationHour'])
								+ $actor['tasks'][($compactTexti)]['durationMinute'] ) )
							/ $options['eva duration in minutes'])*100))
						 - (floor(($compactTextActorSumOfDurationMinutes / $options['eva duration in minutes'])*100)) );
					$blockMarginLeft = (floor(($compactTextActorSumOfDurationMinutes / $options['eva duration in minutes'])*100));

					$actor['compact text'] .=
					"<div class='cell-border task-block' style='width:"
					. $blockWidth
					. "%;"
					. " margin-left: "
					. $blockMarginLeft
					. "%;"
					. "'>"
						. "<div class='cell-body'>"
						//***********************************************
						//      TASK BLOCKS
						//***********************************************
						. "<div style='height: 10px;' class='" . $actor['tasks'][$compactTexti]['color'] . "'></div>"
						. "<div class='responsive-text'>"
						. $actor['tasks'][$compactTexti]['title'] . " "
				    	. "(" . $actor['tasks'][$compactTexti]['durationHour'] . ":"
				    	. $actor['tasks'][$compactTexti]['durationMinute'] . ")"
						. "</div>"
						//***********************************************
						//
						//***********************************************
						. "</div>"
					. "</div>";
			    	$compactTextActorSumOfDurationMinutes += ( (60 * $actor['tasks'][$compactTexti]['durationHour']) + $actor['tasks'][$compactTexti]['durationMinute'] );
			    	$compactTexti++;
			    }
			}
		} unset($actor);

		//Manifest Dependencies
		//This only works for the EVA wiki (using Template:Mission pages with manifest info)
		//As such, page Property:Required for EVA is not included in the xml dump
		$manifestDependenciesText = "";
		$a = 1;
		foreach ( $options['hardware required for eva'] as $hardware ){
			if ($a>1){
				$manifestDependenciesText .=
					", "
					. $hardware['title']
					. " (" . $hardware['mission'] . ")";
			} else { //First item
				$manifestDependenciesText .=
					$hardware['title']
					. " (" . $hardware['mission'] . ")";
			}
			$a++;
		}
		//This is to remove any hidden newlines or carriage returns.
		//I have no idea why, but one sneaks in between the final 'mission' and ")"
		//Ref: http://stackoverflow.com/questions/10757671/removing-line-breaks-no-characters-from-string-retrieved-from-database
		$manifestDependenciesText = preg_replace( "/\r|\n/", "", $manifestDependenciesText );

		//FULL VERSION CONTENT DEFINITIONS
		//Define the Actor1 column output
		$textActor1 = "";
		$textActor1i = 1;
		foreach ( $options['rows']['actor1']['tasks'] as $task ) {
			$textActor1 .= $textActor1i . ". "
			. $options['rows']['actor1']['tasks'][$textActor1i]['title'] . ": "
	    	. "(" . $options['rows']['actor1']['tasks'][$textActor1i]['durationHour'] . ":"
	    	. $options['rows']['actor1']['tasks'][$textActor1i]['durationMinute'] . ")" . "\r\n\r\n"
	    	. "Related articles: " . $options['rows']['actor1']['tasks'][$textActor1i]['relatedArticles'] . "\r\n\r\n"
	    	. "Details: " . $options['rows']['actor1']['tasks'][$textActor1i]['details'] . "\r\n\r\n\r\n";
	    	$textActor1i++;
	    }

		//Define the Actor2 column output
		$textActor2 = "";
		$textActor2i = 1;
		foreach ( $options['rows']['actor2']['tasks'] as $task ) {
			$textActor2 .= $textActor2i . ". "
			. $options['rows']['actor2']['tasks'][$textActor2i]['title'] . ": "
	    	. "(" . $options['rows']['actor2']['tasks'][$textActor2i]['durationHour'] . ":"
	    	. $options['rows']['actor2']['tasks'][$textActor2i]['durationMinute'] . ")" . "\r\n\r\n"
	    	. "Related articles: " . $options['rows']['actor2']['tasks'][$textActor2i]['relatedArticles'] . "\r\n\r\n"
	    	. "Details: " . $options['rows']['actor2']['tasks'][$textActor2i]['details'] . "\r\n\r\n\r\n";
	    	$textActor2i++;
	    }

		//Define the Actor3 column output
		$textActor3 = "";
		$textActor3i = 1;
		foreach ( $options['rows']['actor3']['tasks'] as $task ) {
			$textActor3 .= $textActor3i . ". "
			. $options['rows']['actor3']['tasks'][$textActor3i]['title'] . ": "
	    	. "(" . $options['rows']['actor3']['tasks'][$textActor3i]['durationHour'] . ":"
	    	. $options['rows']['actor3']['tasks'][$textActor3i]['durationMinute'] . ")" . "\r\n\r\n"
	    	. "Related articles: " . $options['rows']['actor3']['tasks'][$textActor3i]['relatedArticles'] . "\r\n\r\n"
	    	. "Details: " . $options['rows']['actor3']['tasks'][$textActor3i]['details'] . "\r\n\r\n\r\n";
	    	$textActor3i++;
	    }

		//Define the main output
		$text = "";

		if ($options['format'] == 'compact'){

			/**********************

			Compact Version Output

			**********************/
			// Outer container
			$text .= "<div style='display: block;'><div style='display: inline-block; width: ";

			// If user specified a fixed width in pixels
			if ( $options['fixedwidth'] != "" ){
				$text .= $options['fixedwidth'] . "px";
			} else $text .= "100%";

			$text .= ";'>"

			// ST Title
			. "<div style='position: relative; margin: 10px 10px 0px 10px;
				font-weight: bold;'>" . $options['title'] . " ("
				// font-weight: bold;'>[[" . $options['title link'] . "|" . $options['title'] . "]] ("
        	. $options['eva duration hours'] . ":";

	    	if ( strlen($options['eva duration minutes']) == 1 ){
	    		$text .= "0" . $options['eva duration minutes'];
	    	} else {
				$text .= $options['eva duration minutes'];
	    	}

        	$text .= ") <span style='font-size: 80%; font-weight: normal;'>([[" . $options['title link'] . "|edit this summary timeline]])"
			. "</span></div>";

			// Only show Event page if user added one
			if ( strlen($options['eva title']) > 0 ){
				// Event Page (EVA title)
	    		$text .=
					"<div style='position: relative; margin: 0px 10px 0px 10px;
					font-size: 100%;'>Event page: [[" . $options['eva title']
					. "]]</div>";
	    	}

			// Only show EVA dependencies if user added one
			if ( strlen($options['depends on']) > 0 ){
				// EVA dependencies
				$text .= "<div style='position: relative; margin: 0px 10px 0px 10px;
					font-size: 100%;'>Dependencies: " . $options['depends on']
				. "</div>";
			}

			// Only show Manifest dependencies if user added one
			if ( strlen($manifestDependenciesText) > 0 ){
				// Hardware dependencies
				$text .= "<div style='position: relative; margin: 0px 10px 0px 10px;
					font-size: 100%;'>Manifest dependencies: " . $manifestDependenciesText
				. "</div>";
			}

			// Only show EVA related articles if user added one
			if ( strlen($options['parent related article']) > 0 ){
				// EVA related articles
				$text .= "<div style='position: relative; margin: 0px 10px 0px 10px;
					font-size: 100%;'>Related articles: " . $options['parent related article']
				. "</div>";
			}

			// Show day/night info, if user added it
			if( $options['include day night']=="yes" ){
				$text .= "<div style='position: relative; margin: 0px 10px 0px 10px; font-size: 100%;'>"
				. "Initial Condition: " . $options['first cycle duration'] . " minutes of " . $options['first cycle day night']
				. "</div>";
			}

			// Begin main div
			$text .= "<div class='summary-timeline-compact-version' id='summary-timeline-" . $options['st index'] . "'>";

			// Only show Color Key if user defined a color
			if ( strlen($colorKeyText) > 0 ){
				// Color key
				$text .= "<div style='"
				. "position: relative; margin: 0px 10px 0px 10px; font-size: 100%;'>Color key: "
					. $colorKeyText
				. "</div>";
			}

			// Begin outer container
			$text .= "<div class='container'>"

			// Begin left label column
			. "<div class='left column'>"
			. "<div class='summary-timeline-row' style='height: 20px; border-left-width: 0px; '>PET</div>";
			if( $options['include day night']=="yes" ){
				$text .= "<div class='summary-timeline-row' "
				. "style='width: 76px; margin: 0px 4px 0px 0px; height: 20px; "
				. "border-top: solid 1px #000000;"
				. "border-bottom: solid 1px #000000;"
				. "border-left: solid 1px #000000;"
				. "'>"
				. "<div style='display: inline-block; height: 20px; width:50%; background-color: #ffffff;'>"
				. $options['insolation duration'] . "</div>"
				. "<div style='display: inline-block; height: 20px; width:50%; background-color: #000000; color: #ffffff'>"
				. $options['eclipse duration'] . "</div>"
				. "</div>";
			}

				foreach ( $options['rows'] as $actor ){
					if( $actor['display in compact view']=='true' && count( $actor['tasks']) > 0 ){
					// Begin Actor Row
					$text .= "<div class='tasks summary-timeline-row' style='font-weight: bold;'>"

					. $actor['name']

					// End Actor row
					. "</div>";
					}
				}

			// End left label column
			$text .= "</div>"

			// Begin main body column
			. "<div class='right column'>"

			// Begin top time labels row
			. "<div class='summary-timeline-row'>"

			// Top time labels
			. $compactTimeTickerText

			// End top time labels row
			. "</div>";

			//***********************************************
			// Begin Day/Night Cycle Row
			//***********************************************
			if( $options['include day night'] == "yes" ){
				$text .= "<div class='summary-timeline-row' style='border: 1px solid #000000;'>";

				$evaDurationMinutes = $options['eva duration in minutes'];
				$insolationMinutes = $options['insolation duration'];
				$insolationWidth = ($insolationMinutes / $evaDurationMinutes) * 100;
				$eclipseMinutes = $options['eclipse duration'];
				$eclipseWidth = ($eclipseMinutes / $evaDurationMinutes) * 100;
				$dayNightSumOfDurationMinutes = 0;


				$dayNightRowOutput = ""; //init output for this row
				// $dayNightRowLength = 0; //init length of day/night row
				$firstCycleMinutes = $options['first cycle duration'];
				$firstCycleWidth = ($firstCycleMinutes / $evaDurationMinutes) * 100;


				// First day/night block
				$dayNightSumOfDurationMinutes += $firstCycleMinutes;
				$dayNightRowOutput .= "<div class='daynight " . $options['first cycle day night'] . "' "
					. "style='width:" . floor($firstCycleWidth) . "%;"
				    . " margin-left: 0%;"
				    . "'></div>";

				// Blocks in the middle
				$needMoreMiddleDayNightBlocks = true;
				$lastBlockType = $options['first cycle day night'];
				if( $firstCycleMinutes >= $evaDurationMinutes ){ // For a really, really short EVA
					$needMoreMiddleDayNightBlocks = false;
				}
				if( $lastBlockType == "insolation" ){
					$thisBlockType = "eclipse";
					$thisBlockMinutes = $eclipseMinutes;
				}
				if( $lastBlockType == "eclipse" ){
					$thisBlockType = "insolation";
					$thisBlockMinutes = $insolationMinutes;
				}
				if( $firstCycleMinutes + $thisBlockMinutes >= $evaDurationMinutes ){
					$needMoreMiddleDayNightBlocks = false;
				}

			    while( $needMoreMiddleDayNightBlocks == true ){
			    	if( $thisBlockType == "insolation" ){
						$nextBlockType = "eclipse";
						$nextBlockMinutes = $eclipseMinutes;
					} else {
						$nextBlockType = "insolation";
						$nextBlockMinutes = $insolationMinutes;
					}

			    	$blockMarginLeft = floor(($dayNightSumOfDurationMinutes / $evaDurationMinutes)*100);
					$thisBlockWidth = floor(($dayNightSumOfDurationMinutes + $thisBlockMinutes) / $evaDurationMinutes*100)
							- floor($dayNightSumOfDurationMinutes / $evaDurationMinutes*100);

					$dayNightRowOutput .= "<div class='daynight " . $thisBlockType . "' "
						. "style='width:" . $thisBlockWidth . "%;"
					    . " margin-left: "
					    . $blockMarginLeft
					    . "%;'></div>";

					if( $dayNightSumOfDurationMinutes + $nextBlockMinutes >= $evaDurationMinutes ){
						$needMoreMiddleDayNightBlocks = false;
					}

					$dayNightSumOfDurationMinutes += $thisBlockMinutes;
					$thisBlockType = $nextBlockType;
					$thisBlockMinutes = $nextBlockMinutes;
			    }

				// Final day/night block
				if( $dayNightSumOfDurationMinutes < $evaDurationMinutes ){
					$blockMarginLeft = floor(($dayNightSumOfDurationMinutes / $evaDurationMinutes)*100);
					$thisBlockWidth = 100
							- floor($dayNightSumOfDurationMinutes / $evaDurationMinutes*100);

					$dayNightRowOutput .= "<div class='daynight " . $thisBlockType . "' "
						. "style='width:" . $thisBlockWidth . "%;"
					    . " margin-left: "
					    . $blockMarginLeft
					    . "%;'></div>";
				}

				$text .= $dayNightRowOutput;

				$text .= "</div>";
			}
			//***********************************************
			// End Day/Night Cycle Row
			//***********************************************

			// Actor Rows
			foreach ( $options['rows'] as $actor ){
				if( $actor['display in compact view']=='true' && count( $actor['tasks']) > 0 ){
				// Begin Actor Row
				$text .= "<div id='summary-timeline-row-" . $actor['name'] . "' class='summary-timeline-row summary-timeline-tasks-row'>"

				. $actor['compact text']

				// End Actor row
				. "</div>";
				}
			}

			// Begin Footer Row
			$text .= "<div class='summary-timeline-row'>"

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
	        . "</div>"

	        // End of outer container
	        . "</div></div>";

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
				. "<div class='summary-timeline-header'>" . $options['actor 1 name'] . "</div>"
			. "</div>"
			. "<div class='summary-timeline-column' style='width:33%; margin-left: 33%;'>"
				. "<div class='summary-timeline-header'>" . $options['actor 2 name'] . "</div>"
			. "</div>"
			. "<div class='summary-timeline-column' style='width:34%; margin-left: 66%;'>"
				. "<div class='summary-timeline-header'>" . $options['actor 3 name'] . "</div>"
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
				. "<div class='summary-timeline-body'>". $textActor1 . "</div>"
			. "</div>"
			. "<div class='summary-timeline-column' style='width:33%; margin-left: 33%;'>"
				. "<div class='summary-timeline-body'>". $textActor2 . "</div>"
			. "</div>"
			. "<div class='summary-timeline-column' style='width:34%; margin-left: 66%;'>"
				. "<div class='summary-timeline-body'>". $textActor3 . "</div>"
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
		//initiate variables
		$options = array();
		$tempTasks = array();
		$tasks = array();
		$taskDetails = array();
		$options['eva duration in minutes'] = 0;
		$tasksDurationPercentTotal = array();
		$tasksDurationPercentTotal['actor1'] = 0;
		$tasksDurationPercentTotal['actor2'] = 0;
		$tasksDurationPercentTotal['actor3'] = 0;
		// $tasksDurationPercentTotal['ev2'] = 0; DELETE
		// $tasksDurationPercentTotal['iv'] = 0; /* This will be removed once the IV section is fixed */
		$options['number of colors designated'] = 0;
		$options["color red meaning"]="";
		$options["color white meaning"]="";
		$options["color orange meaning"]="";
		$options["color pink meaning"]="";
		$options["color green meaning"]="";
		$options["color yellow meaning"]="";
		$options["color blue meaning"]="";
		$options["color gray meaning"]="";
		$options["color black meaning"]="";
		$options["color white meaning"]="";
		$options["color purple meaning"]="";
        $options['rows']['actor1']['tasks']=array();
        $options['rows']['actor2']['tasks']=array();
        $options['rows']['actor3']['tasks']=array();
        $options['hardware required for eva']=array();
		$options['fixedwidth']="";
		$options['insolation duration']="";
		$options['eclipse duration']="";
		$options['include day night']="";
		$options['first cycle day night']="";
		$options['first cycle duration']="";

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
				        	$options[$name] = "full";
				        } else {
				        	$options[$name] = "compact";
				        }
				        break;
					case 'fixedwidth':
						if ( $value != "" ) {
				        	$options[$name] = $value;
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
				    case 'eva title':
					    if ( isset($value) && $value!="" ) {
				        	$options[$name] = $value;
				        }
				        break;
			        case 'depends on':
				        if ( isset($value) ) {
				        	$options[$name] = $value;
				        }
				        break;
			        case 'hardware required for eva':
				        $i = 1; /* Task id */
					    if( isset($value) && $value!="" ){
						    $tempHardware = explode ( '&&&', $value, 2 );
						    $hardware = explode ( '&&&', $tempHardware[1] );
						    foreach ( $hardware as $item ) {
						    	$itemDetails = explode( '@@@', $item);
						    	$options['hardware required for eva'][$i]['title'] = trim($itemDetails[0]);
						    	$options['hardware required for eva'][$i]['mission'] = trim($itemDetails[1]);
						    	$i++;
						    }
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
			        case 'actor 1 name':
				        if ( isset($value) &&  $value != "" ) {
				        	$options[$name] = $value;
				        	$options['rows']['actor1']['name'] = $value;
				        } else {
				        	$options[$name] = 'Actor 1';
				        	$options['rows']['actor1']['name'] = 'Actor 1';
				        }
				        break;
			        case 'actor 2 name':
				        if ( isset($value) &&  $value != "" ) {
				        	$options[$name] = $value;
				        	$options['rows']['actor2']['name'] = $value;
				        } else {
				        	$options[$name] = 'Actor 2';
				        	$options['rows']['actor2']['name'] = 'Actor 2';
				        }
				        break;
			        case 'actor 3 name':
				        if ( isset($value) &&  $value != "" ) {
				        	$options[$name] = $value;
				        	$options['rows']['actor3']['name'] = $value;
				        } else {
				        	$options[$name] = 'Actor 3';
				        	$options['rows']['actor3']['name'] = 'Actor 3';
				        }
				        break;
			        case 'actor 1 display in compact view':
				        if ( isset($value) ) {
				        	$options[$name] = $value;
				        	$options['rows']['actor1']['display in compact view'] = $value;
				        }
				        break;
			        case 'actor 2 display in compact view':
				        if ( isset($value) ) {
				        	$options[$name] = $value;
				        	$options['rows']['actor2']['display in compact view'] = $value;
				        }
				        break;
			        case 'actor 3 display in compact view':
				        if ( isset($value) ) {
				        	$options[$name] = $value;
				        	$options['rows']['actor3']['display in compact view'] = $value;
				        }
				        break;
			        case 'actor 1 enable get aheads':
				        if ( isset($value) ) {
				        	$options[$name] = $value;
				        	$options['rows']['actor1']['enable get aheads'] = $value;
				        }
				        break;
			        case 'actor 2 enable get aheads':
				        if ( isset($value) ) {
				        	$options[$name] = $value;
				        	$options['rows']['actor2']['enable get aheads'] = $value;
				        }
				        break;
			        case 'actor 3 enable get aheads':
				        if ( isset($value) ) {
				        	$options[$name] = $value;
				        	$options['rows']['actor3']['enable get aheads'] = $value;
				        }
				        break;
				    case 'actor1': // NEED TO SPLIT OUT SO THIS DOESN'T HAVE GET-AHEADS ADDED
					    // this should have blocks with "Start time" (not duration)
					    // an option should be included to sync with a task on EV1 and/or EV2
					    // break;
				    case 'actor2':
				    case 'actor3':
					    $i = 1; /* Task id */
						$tasksDuration = 0;
					    if( isset($value) && $value!="" ){
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
						    	$options['rows'][$name]['tasks'][$i]['details'] = trim($taskDetails[5]);

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
					    if( $options['rows'][$name]['enable get aheads']=='true' && $tasksDuration < $options['eva duration in minutes'] ){
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
					    	$options['rows'][$name]['tasks'][$i]['details'] = trim($options['rows'][$name]['tasks'][$i-1]['details']);

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
			        case 'color white meaning':
			        case 'color red meaning':
			        case 'color orange meaning':
			        case 'color yellow meaning':
			        case 'color blue meaning':
			        case 'color green meaning':
			        case 'color pink meaning':
			        case 'color purple meaning':
			        case 'color gray meaning':
			        case 'color black meaning':
				        if ( isset($value) && $value!="" ) {
				        	$options[$name] = $value;
				        	$options['number of colors designated'] ++;
				        }
				        break;
			        case 'ev1':
				        // Unique things for this column? Would have to split above into these two (can't do both cases)
				        break;
			        case 'ev2':
				        // Unique things for this column?
				        break;
			        case 'insolation duration':
			        case 'eclipse duration':
			        case 'first cycle duration':
			        case 'include day night':
			        case 'first cycle day night':
				        $value = strtolower(trim($value));
			        	if ( isset($value) && $value!="" ) {
				        	$options[$name] = $value;
				        }
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

	static function addScripts ( &$out, &$skin ){
		$out->addModules( 'ext.summarytimeline.base' );
	}
}
