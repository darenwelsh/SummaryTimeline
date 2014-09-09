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
    var textWidth = getTextWidth(text, "8pt arial").width; //Width of entire text
    var divWidth = $(e).width(); //Width of div
    var textWords = text.split(" "); //split the text into individual words
    var textWordWidth = []; //Width of each word

    //NEED TO ADD CHECK FOR HEIGHT ... or MORE THAN THREE WORDS (PLUS TIME)

    //get width of each word
    textWords.forEach(function(word){
      //compare wordWidth to divWidth
      textWordWidth[i] = getTextWidth(word, "8pt arial").width;
      if(textWordWidth[i] > divWidth){
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

$(document).ready( function(){
  assignBlockIDs();
  evaluateBlockText();
  writeFooter(); 
});

$(window).resize( function(){
  clearFooter();
  resetBlockText();
  evaluateBlockText();
  writeFooter();
});

// For each row id, run all the above
// Modify each function to use row id
