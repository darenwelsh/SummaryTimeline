/**
 * Uses canvas.measureText to compute and return the width of the given text of given font in pixels.
 *
 * @param text The text to be rendered.
 * @param {String} font The css font descriptor that text is to be rendered with (e.g. "bold 14px verdana").
 *
 * @see http://stackoverflow.com/questions/118241/calculate-text-width-with-javascript/21015393#21015393
 */
getTextWidth = function(text, font) {
    // if given, use cached canvas for better performance
    // else, create new canvas
    var canvas = getTextWidth.canvas || (getTextWidth.canvas = document.createElement("canvas"));
    var context = canvas.getContext("2d");
    context.font = font;
    var metrics = context.measureText(text);
    return metrics;
};

function assignBlockIDs(){
  $(".summary-timeline-tasks-row").each( function(i,e){
    var rowIDFull = $(e).attr('id');
    var rowID = rowIDFull.slice(21);
    $(e).find(".responsive-text").each( function(i,e){
      $(e).attr('summary-timeline-row-block-id',rowID + '-' + i);
    });
  });
}

function writeFooter(){
  var footerCols = []; //Titles of columns in footer
  $(".summary-timeline-compact-version").each( function(indexA,elementA){

    $(elementA).find(".summary-timeline-tasks-row").each( function(i,e){
      var rowIDFull = $(e).attr('id');
      var rowID = rowIDFull.slice(21); //"EV1", "EV2", etc
      var rowFooterContents = ""; //Contents of each column in footer
      var rowNeedsFooter = false;
      //Determine which rows (actors) have hidden text
      $(e).find(".responsive-text.has-hidden-text").each( function(index,el){
        rowNeedsFooter = true;
        var blockID = $(el).attr('summary-timeline-row-block-id');
        var blockIDSplit = blockID.split("-",2) //This could be problematic if someone uses "-"in the row title
        var blockRowLabel = blockIDSplit[0];
        var blockRowIndex = parseInt(blockIDSplit[1])+1;
        rowFooterContents += ( "[" + blockRowIndex + "] " + $(el).attr('hidden-text') + "<br />");

      });
      if(rowNeedsFooter==true){
        var tempFooterID = "#summary-timeline-footer-" + (indexA + 1);
        $(tempFooterID).append(
          "<div class='footer-column'>"
          + "<span style='font-weight: bold;'>" + rowID + ":</span><br />"
          + rowFooterContents
          + "</div>"
        );
      }
    });

  });

}

function evaluateBlockText() {
  $(".responsive-text").each( function(i,e){
    var text = $(e).text(); //Text in div "FHRC Prep (0:15)"
    var textWidth = getTextWidth(text, "10.5pt arial").width; //Width of entire text
    var divWidth = $(e).width(); //Width of div
    var textWords = text.split(" "); //split the text into individual words
    var textWordWidth = []; //Width of each word
    var textHeight = $(e).prop('scrollHeight');
    var cellHeight = $(e).parent().outerHeight();

    //get width of each word
    textWords.forEach(function(word){
      //compare wordWidth to divWidth and textHeight to cellHeight
      textWordWidth[i] = getTextWidth(word, "10.5pt arial").width;
      if(textWordWidth[i] > divWidth || textHeight > (.8 * cellHeight)){ // .8 multiplier for cellHeight to fix overlap issue
        var blockID = $(e).attr('summary-timeline-row-block-id');
        var blockIDSplit = blockID.split("-",2) //This could be problematic if someone uses "-"in the row title
        var blockRowLabel = blockIDSplit[0];
        var blockRowIndex = parseInt(blockIDSplit[1])+1;
        //Move text to footer
        $(e).text("[" + blockRowIndex + "]").attr('hidden-text',text).addClass('has-hidden-text');
      }
    });
  });
}

function reEvaluateBlockText() {
  //An attempt to increase the block width as necessary
  //Currently this doesn't work because the remaining blocks have already been created and positioned
  $(".task-block").each (function (index,element){
    $(element).find(".responsive-text").each( function(i,e){
      var text = $(e).text(); //Text in div "FHRC Prep (0:15)"
      var textWidth = getTextWidth(text, "9pt arial").width; //Width of entire text
      var divWidth = $(e).width(); //Width of div
      var textWords = text.split(" "); //split the text into individual words
      var textWordWidth = []; //Width of each word

      //get width of each word
      textWords.forEach(function(word){
        //compare wordWidth to divWidth
        textWordWidth[i] = getTextWidth(word, "9pt arial").width;
        if(textWordWidth[i] > divWidth){
          //Make div width bigger
          var newWidth = $(element).width() + 1;
          console.log(newWidth);
          $(element).width(newWidth);
          reEvaluateBlockText();
        }
      });
    });
  });
}

function clearFooter(){
  $(".footer").each( function(i,e){
    $(e).text("");
  });
}

function resetBlockText(){
  //Find all elements with hidden-text
  $(".has-hidden-text").each( function(i,e){
    //copy text back into div
    var tempText = $(e).attr('hidden-text');
    $(e).text(tempText).removeClass('has-hidden-text').removeAttr('hidden-text');
  });
}

/**
 *  Collapsible sections
 **/
$(function(){

    var addCollapsibleContent = function(){

        $(".ST-collapsible").each(function(index,element){

            var collapseText = $(element).attr("data-collapsetext") || "Collapse";
            var expandText = $(element).attr("data-expandtext") || "Expand";
            var buttonText;

            // if no <a> tags within collapsible, then it hasn't been setup yet
            // this only performed the first time on each collapsible
            if ( ! $(element).find(".collapsible-trigger").size() ) {

                if ($(element).hasClass("mw-collapsed")) {
                    $(element).children(".mw-collapsible-content").first().hide();
                    buttonText = expandText;
                }
                else {
                    $(element).children(".mw-collapsible-content").first().show();
                    buttonText = collapseText;
                }

                // if there is a pre-trigger element, insert the trigger after it
                // otherwise, insert the trigger as the first element (prepend)
                if( $(element).find(".pre-trigger").size() )
                    $(element).find(".pre-trigger").after("<a href='#' class='collapsible-trigger'>" + buttonText + "</a>");
                else
                        $(element).prepend("<a href='#' class='collapsible-trigger'>" + buttonText + "</a>");

            }

            $(element).find("a.collapsible-trigger").unbind("click").click(function(ev){

                if( $(ev.target).parent().hasClass("mw-collapsed") ) {
                    $(ev.target).parent().find(".mw-collapsible-content").first().slideDown("slow");

                    // change button to collapse
                    $(ev.target).text(collapseText);
                }
                else {
                    $(ev.target).parent().find(".mw-collapsible-content").first().slideUp("slow");

                    // change button to expand
                    $(ev.target).text(expandText);
                }

                $(ev.target).parent().toggleClass("mw-collapsed");

                return false;

            });

        });

    };


    // add these functions to the page initially
    addCollapsibleContent();

    // clicking the "add" button in a Semantic Form will not automatically apply Javascript to new elements
    // this re-fires certain functions so they can apply themselves to new elements
    $(".multipleTemplateAdder").click(function(){

        // setTimeout so we're sure the link-check is performed after new elements are added
        setTimeout(
            function(){
                addCollapsibleContent();
            },
            100
        );
    });

})();


$(document).ready( function(){
  assignBlockIDs();
  evaluateBlockText();
  writeFooter();
  // reEvaluateBlockText();
  addCollapsibleContent();
});

$(window).resize( function(){
  clearFooter();
  resetBlockText();
  evaluateBlockText();
  writeFooter();
});

// For each row id, run all the above
// Modify each function to use row id
