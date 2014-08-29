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

function evaluateBlockText() {
  $(".responsive-text").each( function(i,e){
    var text = $(e).text(); //Text in div "FHRC Prep (0:15)"
    var textWidth = getTextWidth(text, "8pt arial").width; //Width of entire text
    var divWidth = $(e).width(); //Width of div
    var textWords = text.split(" "); //split the text into individual words
    var textWordWidth = []; //Width of each word
    var itWontFit = false; //Will the text fit in the div?

    //NEED TO ADD CHECK FOR HEIGHT ... or MORE THAN THREE WORDS (PLUS TIME)

    //get width of each word
    textWords.forEach(function(word){
      //compare wordWidth to divWidth
      textWordWidth[i] = getTextWidth(word, "8pt arial").width;
      // console.log(textWordWidth[i]);
      if(textWordWidth[i] > divWidth){
        itWontFit = true;
      }
      // i++;
    });
    if(itWontFit == true){
      //Move text to footer
      var blockID = $(e).attr('summary-timeline-row-block-id');
      var blockIDSplit = blockID.split("-",2) //This could be problematic if someone uses "-"in the row title
      var blockRowLabel = blockIDSplit[0];
      var blockRowIndex = parseInt(blockIDSplit[1])+1;
      $("#summary-timeline-footer").append( blockRowLabel + ": [" + blockRowIndex + "] " + text + "<br />");
      //Change text to reference id for footer entry
      $(e).text("[" + (i+1) + "]").attr('hidden-text',text).addClass('has-hidden-text');
    }

  //Have sections for each row (EV1, EV2, etc): This can be left column with contents in right column
  // div id = ev1, ev2, etc
    // console.log(text, ":", divWidth, "(div); ", textWidth, "(text)");
  });
}

// function textToFooter(text){

// }

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
    $(e).text(tempText).removeClass('has-hidden-text');
  });
}

$(document).ready( function(){
  assignBlockIDs();
  evaluateBlockText();
});

$(window).resize( function(){
  clearFooter();
  resetBlockText();
  evaluateBlockText();
});

// For each row id, run all the above
// Modify each function to use row id
