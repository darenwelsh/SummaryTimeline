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

function evaluateBlockText() {
$(".responsive-text").each( function(i,e){
  var text = $(e).text(); //Text in div "FHRC Prep (0:15)"
  var textWidth = getTextWidth(text, "8pt arial").width; //Width of entire text
  var divWidth = $(e).width(); //Width of div
  var textWords = text.split(" "); //split the text into individual words
  var textWordWidth = []; //Width of each word

  //get width of each word
  var i = 0; //counter
  textWords.forEach(function(word){
    //compare wordWidth to divWidth
    textWordWidth[i] = getTextWidth(word, "8pt arial").width;
    console.log(textWordWidth[i]);
    if(textWordWidth[i] > divWidth){
      //Erase the text in this div and write it elsewhere (TBD)
      $("#summary-timeline-footer").append( "[" + i + "] " + text + "<br />");
      $(e).text("[" + i + "]").attr('hidden-text',text);
      // console.log("It won't fit!");
    }
    i++;
  });

//Modify counter to be numerical for footer instead of just using i
//Have sections for each row (EV1, EV2, etc): This can be left column with contents in right column
//Address case when browser is resized bigger: Move text from footer back into block
//After resize, clear footer div ("reset")
//if(.attr('hidden-text')), test it again

  // console.log(text, ":", divWidth, "(div); ", textWidth, "(text)");
});
}

$(document).ready(evaluateBlockText);

$(window).resize(evaluateBlockText);