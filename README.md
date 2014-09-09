SummaryTimeline
=========================

This extension creates a graphic representation of an EVA summary timeline.
The input is an array of tasks and durations for two crew members.

=========================
DEPENDENCIES
=========================

Extension:Semantic Mediawiki
Extension:Semantic Forms
Extension:Variables
Extension:NumerAlpha
other extensions
form, templates, properties

=========================
USAGE
=========================

This is still in development.

The concept is to call the parser function from within MediaWiki like this:

```html
{{#summary-timeline: title=US EVA 100
	| duration = 6:30 (default = 6:30)
	| row=EV1 
	@ 30 Egress 
	@ 40 SSRMS Setup <bgcolor:blue>
	@ 1:30 FHRC Release
	 ESP-2 FHRC

	@ 20 Maneuver from ESP-2 to S1

	@ 90 FHRC Install
	@ 45 SSRMS Cleanup
	@ 30 Get-Aheads (make this auto-fill based on EVA duration)
	@ 45 Ingress
	| row=EV2
	@ 30 Egress
	@ 40 FHRC Prep
	@ 90 FHRC Release
	@ 20 MMOD Inspection
	@ 110 FHRC Install
	@ 10 Get-Aheads
	@ 45 Ingress
 }}
  ```

=========================
OPTIONS
=========================

You might want a row for SSRMS:

```html
{{#summary-timeline: title=US EVA 100
	| duration = 6:30 (default = 6:30)
	| row=EV1 
	| 30 Egress 
	| 40 SSRMS Setup## blue
	| 1:30 FHRC Release
	 ESP-2 FHRC
	| 20 Maneuver from ESP-2 to S1
	| 90 FHRC Install
	| 45 SSRMS Cleanup
	| 30 Get-Aheads (make this auto-fill based on EVA duration)
	| 45 Ingress
	| row=EV2
	| 30 Egress
	| 40 FHRC Prep
	| 90 FHRC Release
	| 20 MMOD Inspection
	| 110 FHRC Install
	| 10 Get-Aheads
	| 45 Ingress
	| row = SSRMS
	| 30 Setup
	| 40 SSRMS Setup
	Brakes on
	| 1:30 FHRC Release
	GCA as required
	| 20 Maneuver from ESP-2 to S1
	| 90 FHRC Install
	GCA as required
	| 45 SSRMS Cleanup
	Brakes on
	| 30 Maneuver to park position
 }}
  ```