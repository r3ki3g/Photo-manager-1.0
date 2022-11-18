//at start and every 100 ms
$(document).ready(function () { gridify(); setInterval(function () { gridify(); }, 500); });
 
function C (v) {
                console.log(v);
            }

            function justPositive(v) {
                if (v > 0) {
                    return v;
                }
                return 0;
            }

            function getNum(str) {
                //C(str)

                return new Number(str.replace(/[^0123456789\.]/, ""));

            }


window.currentStylesBasedOn = [ -1, -1 ];
window.gridAdjusted = false;
function gridify ()
{





    var w = window.innerWidth;
    var h = window.innerHeight;



    if (window.currentStylesBasedOn[ 0 ] != w || window.currentStylesBasedOn[ 1 ] != h || !window.gridAdjusted)
    {
        window.currentStylesBasedOn = [ w, h ];
        window.gridAdjusted = true;


        var leftItemsWidth = 800;
        var rightBoxW = 376;
        if (w > 1360)
        {

            //undos
            $("#leftBox_tourbox").css("top", "");
            $("#contentBox").css("top", "");
            $("#contentBox,#leftBox_tourbox,#welcomelogin").css("left", "");
            $("#rightlogbox").css("left", "");
            $("#rightlogbox").css("width", "");
            $("#rightlogbox").css("top", "");
            $("#leftBox_tourbox").css("top", "");
            $("#contentBox").css("top", "");
            $("#loginformsubmitbutton").css("left", "");
            $("#rightlogbox").css("height", "");
            $("#signupcontainer").css("top", "");
            $("#signupcontainer").css("left", "");
            $("#rightlogbox").css("padding-left", "");

            //dos
            $("#rightlogbox").css("width", rightBoxW + "px");

            var leftPos = (w - leftItemsWidth - rightBoxW - 30) / 2;
            $("#contentBox,#leftBox_tourbox,#welcomelogin").css("left", leftPos + "px");
            $("#rightlogbox").css("left", leftPos + leftItemsWidth + 30 + "px");







        }

        else if (true)
        {
            window.gridAdjusted = true;
            var leftPos = (w - leftItemsWidth) / 2;
            $("#contentBox,#leftBox_tourbox,#welcomelogin").css("left", leftPos + "px");
            $("#rightlogbox").css("left", leftPos + "px");
            $("#rightlogbox").css("width", leftItemsWidth + "px");
            $("#rightlogbox").css("top", 189 + "px");
            $("#leftBox_tourbox").css("top", 540 + "px");
            $("#contentBox").css("top", 590 + "px");
            $("#loginformsubmitbutton").css("left", 242 + "px");
            $("#rightlogbox").css("height", "268px");
            $("#signupcontainer").css("top", "18px");
            $("#signupcontainer").css("left", "427px");
            $("#rightlogbox").css("padding-left", "36px");
            $("#leftBox_tourbox").css("top", "480px");
            $("#contentBox").css("top", "571px");

            //box shadow stuff
            $("#welcomelogin,#leftBox_tourbox").css("box-shadow", "0px 0px 1px 0px #000000");
            $("#rightlogbox").css("box-shadow", "0px 0px 5px 0px rgb(57, 67, 106)");
            $("#leftBox").css("box-shadow", "0px 1px 1px 0px #000000");





        }

        else
        {

        }

    }



}



