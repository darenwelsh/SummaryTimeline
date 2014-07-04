SummaryTimeline
=========================

This extension creates a graphic representation of an EVA summary timeline.
The input is an array of tasks and durations for two crew members.

=========================
USAGE
=========================

This is still in development.

The concept is to call the parser function from within MediaWiki like this:

```html
{{#summary-timeline: EV1 
 | Egress | 30 
 | SSRMS Setup  | 40 
 | FHRC Release  | 90
 | Maneuver from ESP-2 to S1 | 20
 | FHRC Install | 90
 | SSRMS Cleanup | 45
 | Get-Aheads | 30
 | Ingress | 45
 | EV2
 | Egress | 30
 | FHRC Prep | 40
 | FHRC Release | 90
 | MMOD Inspection | 20
 | FHRC Install | 110
 | Get-Aheads | 10
 | Ingress | 45
 }}
  ```