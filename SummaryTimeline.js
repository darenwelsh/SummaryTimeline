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
  $(".summary-timeline-row").each( function(i,e){
    $(".summary-timeline-row .responsive-text").each( function(i,e){
      $(e).attr('summary-timeline-row-block-id',i);
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

    //NEED TO ADD CHECK FOR MORE THAN THREE WORDS (PLUS TIME)

    //get width of each word
    // var i = 0; //counter
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
      $("#summary-timeline-footer").append( "[" + (i+1) + "] " + text + "<br />");
      //Change text to reference id for footer entry
      $(e).text("[" + (i+1) + "]").attr('hidden-text',text).addClass('has-hidden-text');
    }

  //Modify counter to be numerical for footer instead of just using i
  //Have sections for each row (EV1, EV2, etc): This can be left column with contents in right column
// class ev1
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
