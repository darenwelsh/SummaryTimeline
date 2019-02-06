<?php
/**
 * The SummaryTimeline generates a graphic representation
 * of an EVA summary timeline within MediaWiki
 *
 * Documentation: https://github.com/darenwelsh/SummaryTimeline
 * Support:       https://github.com/darenwelsh/SummaryTimeline
 * Source code:   https://github.com/darenwelsh/SummaryTimeline
 *
 * @file SummaryTimeline.php
 * @addtogroup Extensions
 * @author Daren Welsh
 * @copyright © 2014 by Daren Welsh
 * @licence GNU GPL v3+
 */

//Updates by Joe Bartos
//Working on making a different display output
//Working on allowing a user to select events to sync

# Not a valid entry point, skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
	die( "SummaryTimeline extension" );
}

$wgExtensionCredits['parserhook'][] = array(
	'path'           => __FILE__,
	'name'           => 'SummaryTimeline',
	'url'            => 'http://github.com/darenwelsh/SummaryTimeline',
	'author'         => '[https://www.mediawiki.org/wiki/User:Darenwelsh Daren Welsh]',
	'descriptionmsg' => 'summarytimeline-desc',
	'version'        => '0.2.1'
);

# $dir: the directory of this file, e.g. something like:
#	1)	/var/www/wiki/extensions/SummaryTimeline
# 	2)	C:/xampp/htdocs/wiki/extensions/SummaryTimeline
$dir = dirname( __FILE__ ) . '/';

# Location of "message file". Message files are used to store your extension's text
#	that will be displayed to users. This text is generally stored in a separate
#	file so it is easy to make text in English, German, Russian, etc, and users can
#	easily switch to the desired language.
$wgExtensionMessagesFiles['SummaryTimeline'] = $dir . 'SummaryTimeline.i18n.php';

# The "class" file will contain the bulk of a simple parser function extension.
#	NEED MORE INFO HERE.
#
$wgAutoloadClasses['SummaryTimeline'] = $dir . 'SummaryTimeline.class.php';

# This specifies the function that will initialize the parser function.
#	NEED MORE INFO HERE.
#
$wgHooks['ParserFirstCallInit'][] = 'SummaryTimeline::setup';

/**
 *  Add CSS and JS
 **/
$wgResourceModules['ext.summarytimeline.base'] = array(
	'scripts' => array( 'SummaryTimeline.js'  ),
	'styles' =>  array( 'SummaryTimeline.css' ),
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'SummaryTimeline',
	'position' => 'top',
);

$wgHooks['BeforePageDisplay'][] = 'SummaryTimeline::addScripts';
