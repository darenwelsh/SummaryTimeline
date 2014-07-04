<?php
/**
 * Internationalisation file for extension SummaryTimeline.
 *
 * @file
 * @ingroup Extensions
 */
$magicWords = array();
$messages = array();

/** English **/
$messages['en'] = array(
	'summarytimeline'         => 'summarytimeline',
	'summarytimeline-desc'    => 'Generates graphic representation of EVA summary timeline.',
);

/** German **/
$messages['de'] = array(
	'summarytimeline'         => 'zusammenfassung-des-zeitplans',
	'summarytimeline-desc'    => 'German translation of "Generates graphic representation of EVA summary timeline"'
);

# The $magicWords array is where we'll declare the name of our parser function
# Below we've declared that it will be called "masonry-block", and thus will be
# called in wikitext by doing {{#masonry-block: example | parameters }}
$magicWords['en'] = array(
   'summary-timeline' => array(
   		0,              // zero means case-insensitive, 1 means case sensitive
   		'summary-timeline' // parser function in this language. For English this will probably be the same as above
   	),
);

$magicWords['de'] = array(
   'summary-timeline' => array( 0, 'zusammenfassung-des-zeitplans' ),
);