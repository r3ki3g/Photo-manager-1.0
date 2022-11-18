
//rekieg try to count how many seconds user seeing each photo
var trackPointCounter = 0;
var watchTimePeriodInPointers = 4*4;//4s blocks on view time
var photoTrackData ={};//contains all focused/propotions as history
var photoFocusTimeCounter = {};//photoId :[LastTimeStampinPointers,counted250msS]
var scrolledBackToPhotos ={};//photoId:timeStampInPointers
/*
d => 0.7<d<1
0 0 0 0 d d d d 0 0 0 d d d d d d d d d d d d  => seen it but scrolled..now went back to it and watching it!

*/
($RequestQuery('track') != 'true') || $(document).ready(function(){
    var  ti_1 = window.setInterval(function(){
        var focusedImgObjs = getFocusedPhotos();
        //C(focusedImgObjs)
        //focusedImgObjs.map(function(x){x.css('border','red solid 4px')});
        //CC(photoTrackData)
        
        CC('');
        for(var i=0;i<focusedImgObjs.length;i++)
        {
            var img = focusedImgObjs[i];
            var photoId =  img.attr('data-photoid');
            var toShow = '<img class="smallPrev" src="http://www.realfeed.com/photo.manager/'+ photoId +'.jpeg"/><br/>';
            var thisPhotoTrackData = photoTrackData[photoId]
           
            for (var u=0;u<thisPhotoTrackData.length;u++)
            {
                toShow+= ', ' + Math.round((thisPhotoTrackData[u][1]||0)*10)/10;
            }
            
            if(detectAny_scrollBackToFocus(photoId))
            {
               // C('scrolled back to the photo within given time period')
                scrolledBackToPhotos[photoId] = trackPointCounter;
                toShow += '<hr/> SCROLED BACK!!!';
            }

            //collecting watch time
                if(photoFocusTimeCounter[photoId] == null || 
                   trackPointCounter - photoFocusTimeCounter[photoId][0] > 2*watchTimePeriodInPointers)
                {
                    photoFocusTimeCounter[photoId] = [trackPointCounter,1];
                }
                else//already set and can be considered as one block of view time
                {
                    var currentWatchTimeInPointers = photoFocusTimeCounter[photoId][1] ;
                    photoFocusTimeCounter[photoId] = [trackPointCounter,currentWatchTimeInPointers + 1];
                }
                if (photoFocusTimeCounter[photoId][1] > watchTimePeriodInPointers)
                {   photoFocusTimeCounter[photoId] = [trackPointCounter,0];
                    toShow += '<br/><b>watchedLong</b>';
                    //sending watched data to server
                        //identify whether scrolled or not -
                        /*
                            should know whether user scrolled back to the photo, and then watched it for a long time?
                        */
                       var scrolledAndWatched =0;
                       if(scrolledBackToPhotos[photoId]!=null 
                          && trackPointCounter - scrolledBackToPhotos[photoId]<watchTimePeriodInPointers - 3)
                          {
                            scrolledAndWatched = 1;
                          }
                    //now scrolledAndWatched is a working:bool
                    $.ajax({
                        type: "POST",
                        url: 'http://www.realfeed.com/photo.manager.tracking.php',
                        data: {
                                'photoid':photoId,
                                'watched':'true',
                                'watched_duration':watchTimePeriodInPointers*250,
                                'scrolled_back':(scrolledAndWatched?'true':'false')
                              },
                        success: function(res)
                                {
                                    C(res);
                                }                        
                      });

                }
            CC(toShow)
        }
            
       



    },250);



    //add a floating body to visualize things
    $('body').append('<div class="floatingConsole"></div>');
    function CC(x)
    {
        $('.floatingConsole').html(x + '<hr/><br/>');
        $('.floatingConsole').animate({ scrollTop: 10000000 }, 200);
    }
});

function getFocusedPhotos()//however stores data about all images(search results)...returns only currently focused ones
{
    
    //lastScrolledTimeStamp
    var thisTimeStamp = new Number(new Date());
    if(thisTimeStamp - lastScrolledTimeStamp < 250){return false;}//atleast 250 ms between two scrolls:to avoid stucking
    lastScrolledTimeStamp = thisTimeStamp;

    var scrolledY = $(window).scrollTop();
    var targetPhotos = $('.photoResultsImg').map(function(){return this.id;}).toArray().filter(function(a){return a!='';});

    var focusedImgList =[];
    var windowHeight = window.innerHeight;
    for(var i=0;i<targetPhotos.length;i++)
    {
      
        var thisTarget = $('#'+targetPhotos[i]);
  
        var dataPhotoId = thisTarget.attr('data-photoid');
        var thisImagePositionY = thisTarget.offset()['top'];

        var thisImageHeight = forceNumberize($(thisTarget).css('height'));
        var propotionVisible =0;//how much  propotion of window is took by image OR how much propotion of image is shown
        if(thisImagePositionY + thisImageHeight <= scrolledY + windowHeight)
        {
            var visibleHeight = thisImageHeight - scrolledY + thisImagePositionY;
            //C('visibale height 1',visibleHeight)
            var limitedVisibleHeight = limitter(0,visibleHeight,thisImageHeight);//limit to 0 - windowHeight
            propotionVisible = Math.min(1,Math.max(limitedVisibleHeight / windowHeight,limitedVisibleHeight / thisImageHeight));
        }
        else
        {
            var visibleHeight = windowHeight + scrolledY - thisImagePositionY;
           // C('visibale height 2',visibleHeight)
            var limitedVisibleHeight = limitter(0,visibleHeight,thisImageHeight);//limit to 0 - windowHeight
            propotionVisible = Math.min(1,Math.max(limitedVisibleHeight / windowHeight,limitedVisibleHeight / thisImageHeight));
        }
        
        //C(propotionVisible)
        //now select the photos can be considered focused in screen
        appendUnderName_createArrayIfDNE(photoTrackData,dataPhotoId,[trackPointCounter,propotionVisible],100);
        if (propotionVisible > 0.7)
        {
            focusedImgList.push(thisTarget);
        }
        
    }
    //C(focusedImgList);
    //focusedImgList.map(function(x){x.css('border','red solid 4px')});
    trackPointCounter++;
    return focusedImgList;

}

function appendUnderName_createArrayIfDNE(obj,name,value,maxLength)
//append the value to array under name 'name' in obj.if no array for 'name' will create it first:delete 1st elements to limit at maxLengh
{
if(obj[name] == null) {obj[name] = [];}
obj[name].push(value);
var listLength = obj[name].length;
if(listLength > (maxLength||listLength))
    {
        obj[name].splice(0,listLength - maxLength);
    }
}

function detectAny_scrollBackToFocus(photoId)
{
    var thisPhotoTrackData_difference =differencer(photoTrackData[photoId]);
   // C(thisPhotoTrackData_difference);

    var bumpCount = 0;
    var lastDiff_Y;
    /*if visible propotion looks like this =>return  true
        .           ..............
        . .        .
    ......   .......

    */
    for(var i=0;i<thisPhotoTrackData_difference.length;i++)
        {  
            var thisDiff = thisPhotoTrackData_difference[i];
           // C(thisDiff)
            if (trackPointCounter - thisDiff[0] > 10) {continue;}//only consider last 2.5s = 250ms*10
            if (lastDiff_Y == null) {lastDiff_Y = thisDiff[1];continue;} //can't be  1st point(nothing to compare)
           // C(lastDiff_Y * thisDiff[1])
            if(lastDiff_Y * thisDiff[1] <0) {bumpCount++;}
           
        }
  //C('bump count : ' + bumpCount)
    if (bumpCount > 0) {return true;}
    return false;
}


function differencer(arrayWith_x_y)
{
var diff =[];
for (var i=1;i<arrayWith_x_y.length;i++)
    {   var point_1 = arrayWith_x_y[i], point_0 =  arrayWith_x_y[i-1];
        diff.push([point_1[0],point_1[1] - point_0[1]]);
    }
    return diff;
}