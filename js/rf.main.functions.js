

function dataTitleListenerAdd ()
{
    $("[data-title][data-title-function-set!=true]").mouseenter(function () {
       
       
        var msg = $(this).attr("data-title");
        $(this).attr("data-title-function-set", "true");
        var msgLength = msg.length * 5;
        var titlePopLeft = $(this).offset()["left"] ;
        var titlePopTop = $(this).offset()[ "top" ];
        
        titlePopTop = abs(titlePopTop - event.clientY) < 50 ? event.clientY -50: titlePopTop-84;
        titlePopLeft = abs(titlePopLeft - event.clientX) < 100 ? event.clientX : titlePopLeft;



        titlePopLeft = titlePopLeft - msgLength / 3;
        titlePopTop = titlePopTop + 11;
        
        //avoid covering
        var topMax = window.innerHeight, leftMax = window.innerWidth;
        if (titlePopTop + 50 > topMax)
        {
            titlePopTop -= 100; 
        }
        if (titlePopLeft + msgLength + 20> leftMax)
        {
            titlePopLeft = leftMax - msgLength - 20;
        }

       
        $("#dataTitleHolder").html('<div id="dataTitlePopUp" style="top:' + titlePopTop + 'px;left:' + titlePopLeft + 'px;">' + msg + '</div>');
        
        
        
    });
    $("[data-title][data-title-off-function-set!=true]").mouseout(function () { 
        $(this).attr("data-title-off-function-set", "true");
        $("#dataTitleHolder").html('');
        


    });
}

$(document).ready(function () { 
    dataTitleListenerAdd();


});

