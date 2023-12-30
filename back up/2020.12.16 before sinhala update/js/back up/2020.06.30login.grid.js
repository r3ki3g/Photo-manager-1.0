
 
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

setInterval(function () {
                


                var w = window.innerWidth;
                var h = window.innerHeight;
                C( w + " " + h);
                var leftBoxW = 800;
                var rightBoxW = 376;
    if (w > 1260)
    {
                    
        //undos
        document.getElementById("contentBox").style.position = "";
        document.getElementById("rightlogbox").style.position = "";
        document.getElementById("leftBox").style.position = "";
        document.getElementById("welcomelogin").style.width = "";
        document.getElementById("welcomelogin").style.left = "";

        document.getElementById("rightlogbox").style.right = "";
        $("#rightlogbox").css("width", "");
        $("#rightlogbox,#leftBox_tourbox,#leftBox").css("left", "");
        $("#loginformsubmitbutton").css("left", "");
        $("#rightlogbox").css("height", "");
        $("loginformcontainer").css("margin-top", "");


        //document.getElementById("rightlogbox").style.left = rightBoxWidth / 2 + "px";

        var tourTopPos = ($("#leftBox_tourbox").offset()[ "top" ]);
        $("#leftBox").css("top", "");
        $("#contentBox").css("left", "");

        //do s
                    var freePad = (w - leftBoxW - rightBoxW) / 2;

                    document.getElementById("contentBox").style.position = "absolute";
                    document.getElementById("rightlogbox").style.position = "absolute";

                   
                    document.getElementById("contentBox").style.left = justPositive(freePad - 52) + "px";
                    document.getElementById("leftBox_tourbox").style.left = justPositive(freePad - 52 + 39) + "px";
                    document.getElementById("welcomelogin").style.left = justPositive(freePad - 52 + 39) + "px";
                    document.getElementById("rightlogbox").style.right = justPositive(freePad - 52 + 35) + "px";

                    


    } else
    {
        document.getElementById("contentBox").style.left = justPositive(freePad - 52-39) + "px";
                    document.getElementById("contentBox").style.position = "absolute";
                    document.getElementById("rightlogbox").style.position = "relative";
                    document.getElementById("leftBox").style.position = "relative";
                    document.getElementById("welcomelogin").style.width = "800px";
                    document.getElementById("welcomelogin").style.left = (w - 800) / 2 + "px";

                    document.getElementById("rightlogbox").style.right = "unset";
                    $("#rightlogbox").css("width", "800px");
                    $("#rightlogbox,#leftBox_tourbox,#leftBox").css("left", (w - 800) / 2 + "px");
                   /* $(".logformfield").css("display", "inline-block");


                    $(".logformlabel").hide();
                    $("#loginusername").attr("placeholder", "User Name");
                    $("#loginpassword").attr("placeholder", "Password");
                    $("#loginformsubmitholder").css("display", "inline");

                    $(".logformfield").css("display", "inline-block")
                    $(".logformfield").css("margin-left", "11px")
                    $(".logformfield").css("margin-right", "22px")*/

                    //$("#contentBox").css("top", "263px");
                    $("#loginformsubmitbutton").css("left", "241px");
                    $("#rightlogbox").css("height", "266px");
                    $("loginformcontainer").css("margin-top", "14px");
                    

                    //document.getElementById("rightlogbox").style.left = rightBoxWidth / 2 + "px";

                    var tourTopPos = ($("#leftBox_tourbox").offset()[ "top" ]);
                    $("#leftBox").css("top", (tourTopPos-447+241)+"px");

                    //welcomelogin


        //signform adjusting
        $("#signupcontainer").css("position", "absolute")
        $("#signupcontainer").css("left", "437px")
        $("#signupcontainer").css("top", "18px")

        $("#lighttext0").attr("style", "\
    position: absolute;\
        top: 85px;\
        left: 21px;\
        z - index: 12;\
        font - weight: 100;\
        color: #cccccc;");
        $("#logincontainer").attr("style", "\
    position: absolute;\
        top: 82px;");






                }




            }, 100);