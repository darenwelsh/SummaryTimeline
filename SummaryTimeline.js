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

console.log(getTextWidth("hello there! asdf asdf ", "bold 12pt arial"));  // reports 84

$(".responsive-text").each( function(i,e){
  var text = $(e).text();
  var textWidth = getTextWidth(text, "8pt arial");
  var divWidth = $(e).width();

  //split the text into individual words
  var textWords = text.split(" ");
  //get width of each word
  textWords.forEach(function(word){
    //compare wordWidth to divWidth
    console.log(getTextWidth(word, "8pt arial"));
  });

  console.log(text, ":", divWidth, "(div); ", textWidth, "(text)");
});