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
		//The $args array looks like this:
		//	[0] => 'Title = Block Title Example' (Such as MW page name), need to set default
		//	EV1 
		//  | Egress | 30 
		//  | SSRMS Setup  | 40 
		//  | FHRC Release  | 90
		//  | Maneuver from ESP-2 to S1 | 20
		//  | FHRC Install | 90
		//  | SSRMS Cleanup | 45
		//  | Get-Aheads | 30
		//  | Ingress | 45
		//  | EV2
		//  | Egress | 30
		//  | FHRC Prep | 40
		//  | FHRC Release | 90
		//  | MMOD Inspection | 20
		//  | FHRC Install | 110
		//  | Get-Aheads | 10
		//  | Ingress | 45

		self::addCSS(); // adds the CSS files 

		// $var1 = stuff
		// $var2 = stuff

		// $Title = trim( $frame->expand($args[0]) );

		// if ( count($args) > 1 )
		// 	$Body = trim( $frame->expand($args[1]) );
		// else
		// 	$Body = "";


		//***New method to create array of named args***
		//Run extractOptions on $args
		$options = self::extractOptions( $args );

		//Define the main output
		// *******Need to allow for item and item w2
	        // {{#if: {{{color|}}} | main-page-box-{{{color}}} | }}
	        // {{#if: {{{style|}}} | style="{{{style|}}}" | }}>
		$text = "<div class='item'>
	        <div class='item-content'>
	        <table class='main-page-box main-page-box-green'>" .

	        //This contains the heading of the masonry block (a wiki link to whatever is passed)
	        "<tr><th>[[" . $options['title'] . "]]</th></tr>" .
			
			//This contains the body of the masonry block
			//Wiki code like links can be include; templates and wiki tables cannot
			"<tr><td>"
	         . $options['body'] . "</td></tr></table></div></div>";
// print_r($options);
		return $text;

	}

	/**
	 * Converts an array of values in form [0] => "name=value" into a real
	 * associative array in form [name] => value
	 *
	 * @param array string $options
	 * @return array $results
	 */
	static function extractOptions( array $options ) {
		$results = array();
	 
		foreach ( $options as $option ) {
			$pair = explode( '=', $option, 2 );
			if ( count( $pair ) == 2 ) {
				//***issue right now with trim not working - FIXIT
				$name = strtolower(trim( $pair[0] )); //Convert to lower case so it is case-insensitive
				$value = trim( $pair[1] );
				$results[$name] = $value;
			}
		}
		//Now you've got an array that looks like this:
		//	[title] => Block Title Example
		//	[body]  => Body of block
		//  [color] => Blue
		//  [width] => 2

		return $results;
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