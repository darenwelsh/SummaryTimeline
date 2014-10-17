SummaryTimeline
=========================

This extension creates a graphic representation of a summary timeline. It is designed for EVA planning, but can be used for any  kind of timeline planning. While the parser function could be used on its own, it is best to use the supplied semantic form to build timelines.

=========================
DEPENDENCIES
=========================

* Extension:Semantic Mediawiki
* Extension:Semantic Forms
* Extension:Semantic Internal Objects
* Extension:Variables
* Extension:NumerAlpha

Semantic form, templates, properties included in xml files

=========================
INSTALLATION
=========================

Download the extension files to your extensions directory. Add the following line to LocalSettings.php:

```html
require_once "$IP/extensions/SummaryTimeline/SummaryTimeline.php";
```

Import the following two files using the "Import files" feature in MediaWiki:
* SummaryTimeline-FormCategory&Templates.xml
* SummaryTimeline-Properties.xml

Additionally, you may import the following file to see some example pages:
* SummaryTimeline-ExtensionDemoPages.xml

For reference, these pages have been given the following categories to ease in the export process:

* Form Used for Summary Timeline Extension
* Category Used for Summary Timeline Extension
* Template Used for Summary Timeline Extension
* Property Used for Summary Timeline Extension
* Summary Timeline Extension Demo Page

=========================
ENTERING DATA
=========================

Begin by clicking a link to Special:FormEdit/Summary_Timeline. There you can give a name to the timeline like "Failed FHRC Removal EVA Version 2". You can list out dependencies, related articles, the duration of the EVA, and then the details of each task. If you choose to mark some tasks with a color, use the color key to explain what each color indicates. Clicking "Save page" creates the page with all the entered data.

The data is handled by the main Template:Summary Timeline and the multiple-instance templates for each actor. Currently, this extension is limited to three actors. In the future, I plan to allow more actors and make the form better accommodate this many columns.

The form will generate a page with content like the following example:

```html
{{Summary Timeline
|ST Title=IDA 2 Installation & Outfitting EVA
|Depends on=IDA 1 Route & IDA 2 Deploy
|Related article=IDA,
|EVA Duration hour=6
|EVA Duration minute=30
|Color Red Meaning=
|Color Orange Meaning=
|Color Yellow Meaning=
|Color Green Meaning=
|Color Blue Meaning=
|Color Purple Meaning=IDA 2
|Color Pink Meaning=
|Color Gray Meaning=
|Actor 1 name=
|Actor 2 name=EV1
|Actor 3 name=EV2
|Actor 1 Display in Compact View=No
|Actor 2 Display in Compact View=Yes
|Actor 3 Display in Compact View=Yes
|Actor 1 Enable Get Aheads=No
|Actor 2 Enable Get Aheads=Yes
|Actor 3 Enable Get Aheads=Yes
|Actor 1 Tasks=
|Actor 2 Tasks={{Actor 2 Task
|Title=Egress
|Duration hour=0
|Duration minute=30
|Color=gray
|Related article=
|Free text=
}}{{Actor 2 Task
|Title=IDA 2 Installation
|Duration hour=3
|Duration minute=00
|Color=purple
|Related article=IDA
|Free text=
}}{{Actor 2 Task
|Title=EWC Antenna Installation
|Duration hour=1
|Duration minute=00
|Color=gray
|Related article=EWC
|Free text=
}}{{Actor 2 Task
|Title=IDA Outfitting
|Duration hour=1
|Duration minute=15
|Color=purple
|Related article=IDA
|Free text=
}}{{Actor 2 Task
|Title=Ingress
|Duration hour=0
|Duration minute=45
|Color=gray
|Related article=
|Free text=
}}
|Actor 3 Tasks={{Actor 3 Task
|Title=Egress
|Duration hour=0
|Duration minute=30
|Color=gray
|Related article=
|Free text=
}}{{Actor 3 Task
|Title=IDA 2 Installation
|Duration hour=3
|Duration minute=00
|Color=purple
|Related article=IDA
|Free text=
}}{{Actor 3 Task
|Title=EWC Antenna Installation
|Duration hour=1
|Duration minute=30
|Color=gray
|Related article=EWC
|Free text=
}}{{Actor 3 Task
|Title=IDA Outfitting
|Duration hour=0
|Duration minute=45
|Color=purple
|Related article=IDA
|Free text=
}}{{Actor 3 Task
|Title=Ingress
|Duration hour=0
|Duration minute=45
|Color=gray
|Related article=
|Free text=
}}
}}
```

=========================
DISPLAYING DATA
=========================

The output of a summary timeline is handled by Template:Summary Timeline Output.

For the compact version (default):
```html
{{Summary Timeline Output | Page name of summary timeline to display | Compact }}
  ```

To force the compact version to a fixed pixel width to fit on a specific page:
```html
{{Summary Timeline Output | Page name of summary timeline to display | Compact | 123 }}
  ```

For the full version:
```html
{{Summary Timeline Output | Page name of summary timeline to display | Full }}
  ```

This template generates a call to the #summary-timeline parser function and passes all the necessary information about the EVA and its tasks. The generic information about the EVA is queried from the page generated by the form as mentioned above. The task-specific information is queried from the Semantic Internal Objects created for each task.

