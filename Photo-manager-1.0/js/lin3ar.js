jQuery.fn.tagName = function() {
    return this.prop("tagName");
  };

$(document).ready(function(){

    $("*").mouseenter(function(elem){

      if((elem["currentTarget"]).tagName == "DIV")
      {
          var topOfThisDiv = $(this).position()["top"];
          var leftOfThisDiv = $(this).position()["left"];

          //(leftOfThisDiv);
      }

    });


});