function C(s) { console.log(s); }

function T(o) { console.table(o); }

function s(n, postfix) { if (n == 1) { return ''; } return postfix || 's'; }

function abs(n) {
    if (n >= 0) { return n; }
    return (-1) * n;
}

function forceNumberize(str) {
    str = String(str);
    str = str.replace(/[^0-9\.\-]/gim, "");
    return new Number(str);
}
var sessions = [{
    id: 1,
    images: []
}];
var currentSession = 0;
var search = { query: "" }
var newGuy = {};
var featuringList = [];
var featuringData = {};
var lastPhotoSearch = {}
var photoResultHtmlPostSet = [];
var photoShownNextIndex = 0;
var peopleFeatAdderFirstResult = [];
var addedPhotoFavouritedData = {};
var favoIdsInSearchResults = [];
var toastCount = 0;
var countOffavPhotosSent = 0;
var currentProfileOptionsShowingFor = null;
var profilePhotoUpdateData = null;
var subjectedGuyIdProfilePicUpdate = null;
var photoSearch = { people: [] };
var currentPeopleInSearchResults = null;
var currentSelectedForPeopleFilter = [];
var photoResultHtmlPostSetFiltered = [];
var photoFilteredShownNextIndex = 0;
var finalResponsiveWidthSetForWinWidth = 0;

function extractJSON(str) {
    var out = false;
    try { out = JSON.parse(str); } catch (e) {}
    return out;
}


$(document).ready(function() {

    //run and set interval out to responsive widths
    responsiveWidthSet();
    var responsiveWidth = window.setInterval(function() { responsiveWidthSet(); }, 1000);

    /*automate-inactive:
    $('#searchQuery').val('kali lathika rekieg nandun vinul');
    proceedSearch({'keyCode':13});*/






    //avoid mozilla from loading the damn file
    window.addEventListener('drop', function(event) { event.preventDefault(); }, true);
    window.addEventListener('dragover', function(event) { event.preventDefault(); }, true);

    //detecting file upload by drag and drop
    var mouthArea = document.getElementById("pageBody");
    mouthArea.addEventListener('dragenter', dragDropDetected1, false);

    function dragDropDetected1(data) {

        var files = data.dataTransfer.files; //not available ...
        // C(files);

        $("#backgroundDimPortal").html('<div id="dimmer" onclick="$(\'#backgroundDimPortal\').html(\'\');$(\'#dragDropBoxPortal\').html(\'\');"></div>');
        $("#dragDropBoxPortal").html('<div id="dragDropBox"><div id="popHead">Drop Image Files Here</div>\
        <textarea  id="dragDropEater" placeholder="Drop Files here!"></textarea></div>');

        var dragArea = document.getElementById("dragDropEater");
        // dragArea.innerHTML = "drop"
        dragArea.addEventListener("drop", dropDetect1, false);




    }

    function dropDetect1(e) {
        //not essential but just added again
        //e = e || event;
        //if (e.preventDefault()) { e.preventDefault(); }
        //else { e.stop(); }

        //alert('drop detect');
        // e.dataTransfer.effectAllowed = "none";
        //e.dataTransfer.dropeffect = "none";
        var files = e.dataTransfer.files;
        //C(files)
        var fileCount = files.length;
        var fileArray = [];
        for (var i = 0; i < fileCount; i++) {
            fileArray.push(files[i]);
        }


        C(fileArray)

        //var currentUploadElem = this;
        // var fileName = $(this).val();
        if (!fileArray) { return false; } //no files set

        for (f in fileArray) {
            var thisFormData = new FormData();
            var fileCont = fileArray[f];
            thisFormData.append("file", fileCont);

            C(fileCont);
            $.ajax({
                url: "http:\/\/www.realfeed.com\/photo.manager.php?upload=true",
                type: "post",
                data: thisFormData,
                contentType: false,
                processData: false,
                success: function(res) {
                    res = JSON.parse(res);
                    var status = res.status;

                    if (status != "success") {
                        alert("File upload was not success");
                    } else {
                        var fileURL = res.filepath,
                            h = res.height,
                            w = res.width,
                            photoId = res.photoid;
                        //if duplicate found at server
                        var duplicate = res.alreadyfound;
                        var fileNameRecieved = res.filenamerecieved;
                        var additionalClassStr = '';
                        if (duplicate) {
                            additionalClassStr = ' duplicateImg';
                            duplicateWarning(fileNameRecieved);
                        }

                        var className = new Number(h) > new Number(w) ? "imgPreview-tall" : "imgPreview-fat";
                        var myId = sessions[currentSession]["images"].push({ "photoid": photoId, "path": fileURL, "status": "active" }) - 1;
                        C("my id " + myId)
                        C(sessions)
                            //copy this to below one too
                        $("#addPreviews").prepend('<div id = "_' + myId + '" class="previewImgBox"><img ondblclick="toggleFavourite(\'' + photoId + '\')" id = "prevImg_' + myId + '" class="' + className + ' prevImgElem' + additionalClassStr + '"  data-real-height="' + h + '" data-real-width="' + w + '" src="' + fileURL + '"/><div class="imageRemoveButton" onclick="removePhoto(' + myId + ');">Remove</div> <div class="fullSizeButton"><span href="' + fileURL + '" target="_blank" style="text-decoration:none;" onclick="zoomPreviewPhoto(\'prevImg_' + myId + '\');">Zoom</span></div>\
                        \
                        \
                        <div style="display:none;" class="favouritedIconPreview" id="favoPreviewIco_' + photoId + '" data-title="Favourited : double click on photo to unfavourite"></div><div class="captionForEach"><textarea class="caption_text" id="caption_' + photoId + '" placeholder="Add a caption..."></textarea></div></div>');

                        C(sessions);
                        $("#addFeat").focus();

                        //favourite set to false
                        addedPhotoFavouritedData[photoId] = false;
                    }

                }
            });
        }














        //file upload over 
        $("#backgroundDimPortal").html('');
        $("#dragDropBoxPortal").html('');

        return false;
    }








    //auto matically upload when new file chosen
    setFileUploadivityForNewbies();

    /*$("#addMorePhoto").click(function () { 
        var count = $("#noOfFiles").val();
        count = !isNaN(count) ? new Number(count) : -200;
        count++;

        $("#noOfFiles").val(count);
        $("#uploadersContainer").append('<input type="file" name="file' + count + '" id="file' + count + '" class="fileUploaderInput" />');
        $('#file' + count).click();

        




        setFileUploadivityForNewbies();




    });*/

    $("#addFeat").keyup(function() {

        //if enter pressed -- select first guy suggested
        var key = event.keyCode ? event.keyCode : event.which;
        if (key == "13") {
            $('#addFeat').val('');
            addToFeaturingList(peopleFeatAdderFirstResult[0], peopleFeatAdderFirstResult[1], peopleFeatAdderFirstResult[2]);
            return false; //nothing more

        }



        $("#suggestions").hide();
        var val = $(this).val();
        if (val == '') { $("#suggestions").html(''); }
        //avoid double search due to ghost key press        
        if (search.query == val) { return false; }

        $.ajax({
            url: "http:\/\/www.realfeed.com/photo.manager.php?suggesions=people",
            type: "post",
            data: { "query": val },
            success: function(res) {
                C(res);

                if (search.query == val) {
                    res = JSON.parse(res);
                    var status = res.status;


                    if (status == "success") {

                        $("#suggestions").show();
                        var suggestions = res.suggestions;
                        C(suggestions);

                        var eachSugCode = "";
                        var firstGuy = true;
                        for (i in suggestions) {
                            var thisPerson = suggestions[i];
                            eachSugCode += '<div class="peopleSugRow" onclick="$(\'#addFeat\').val(\'\');$(\'#addFeat\').focus();addToFeaturingList(\'' + thisPerson.id + '\',\'' + thisPerson.name + '\',\'' + thisPerson.dp + '\');"><img class="dpSug" src="http:\/\/www.realfeed.com/photo.manager/' + thisPerson.dp + '.jpeg"/><div class="nameSug">' + thisPerson.name + '</div></div>';

                            //saving data of first guy to use in enter press
                            if (firstGuy) { peopleFeatAdderFirstResult = [thisPerson.id, thisPerson.name, thisPerson.dp]; }
                            firstGuy = false;
                        }





                        var addPeopleCode = '<div id="addPeopleRow" onclick="addNewGuy(\'' + res.query + '\');"> + Add <span class="peoplename">' + res.query + '</span> to list</div>';

                        C(addPeopleCode)

                        $("#suggestions").html(eachSugCode + addPeopleCode);
                    }

                }


            }

        });
        search.query = val;


    });

    //startup favouritephoto shower //favoRandPhotoShowerPortal  *******************************************
    requestAndShowFavoPhotos();
    // show last phot upload  as history
    showLastPhotoUploadHistoy();
    //prevoius discription paste button function setting
    $("#pastePreviousDiscriptionButton").click(function() {
        var prevDisc = $('#prevoiusDiscriptionStorage').html();
        C(prevDisc)
        $('#discription').val(prevDisc);
        $(this).hide();
    });
    //auto hide pevious discription paste button if user typed a long discription theirselves ... other wise show it
    $('#discription').keyup(function() {
        if ($(this).val().length > 41) {
            $('#pastePreviousDiscriptionButton').hide(154);
        } else {
            $('#pastePreviousDiscriptionButton').show(100);
        }
    });



});

function setFileUploadivityForNewbies() {
    $(".fileUploaderInput[data-upload-func-set!=true]").change(function() {

        var currentUploadElem = this;
        var fileName = $(this).val();
        if (!fileName) { return false; } //no file set

        var thisFormData = new FormData();

        var fileCont = $(this)[0].files[0];
        var originalFileName = fileCont.name;
        //alert(originalFileName)
        thisFormData.append("file", fileCont);

        C(fileCont);
        $.ajax({
            url: "http:\/\/www.realfeed.com\/photo.manager.php?upload=true",
            type: "post",
            data: thisFormData,
            contentType: false,
            processData: false,
            success: function(res) {
                res = JSON.parse(res);
                var status = res.status;

                if (status != "success") {
                    $(currentUploadElem).css("box-shadow", "0px 0px 4px 2px #ff3838");
                } else {
                    var fileURL = res.filepath,
                        h = res.height,
                        w = res.width,
                        photoId = res.photoid;
                    //if duplicate found at server
                    var duplicate = res.alreadyfound;
                    var fileNameRecieved = res.filenamerecieved;
                    var additionalClassStr = '';
                    if (duplicate) {
                        additionalClassStr = ' duplicateImg';
                        duplicateWarning(fileNameRecieved);
                    }

                    var className = new Number(h) > new Number(w) ? "imgPreview-tall" : "imgPreview-fat";
                    var myId = sessions[currentSession]["images"].push({ "photoid": photoId, "path": fileURL, "status": "active" }) - 1;
                    C("my id " + myId)
                    C(sessions)

                    $("#addPreviews").prepend('<div id = "_' + myId + '" class="previewImgBox"><img ondblclick="toggleFavourite(\'' + photoId + '\')" id = "prevImg_' + myId + '" class="' + className + ' prevImgElem' + additionalClassStr + '"  data-real-height="' + h + '" data-real-width="' + w + '" src="' + fileURL + '"/><div class="imageRemoveButton" onclick="removePhoto(' + myId + ');">Remove</div> <div class="fullSizeButton"><span href="' + fileURL + '" target="_blank" style="text-decoration:none;" onclick="zoomPreviewPhoto(\'prevImg_' + myId + '\');">Zoom</span></div>\
                        \
                        \
                        <div style="display:none;" class="favouritedIconPreview" id="favoPreviewIco_' + photoId + '" data-title="Favourited : double click on photo to unfavourite"></div><div class="captionForEach"><textarea class="caption_text" id="caption_' + photoId + '" placeholder="Add a caption..."></textarea></div></div>');

                    C(sessions);
                    $("#addFeat").focus();

                    //favourite set to false
                    addedPhotoFavouritedData[photoId] = false;
                }

            }
        });



    });

}

function removePhoto(id) {

    sessions[currentSession]["images"][id]["status"] = "removed";
    //alert(id);
    document.getElementById("_" + id).innerHTML = "";

}

function addNewGuy(name) {

    C(name);
    $("#backgroundDimPortal").html('<div id="dimmer"></div>');
    $('#popUpPortal').html('<div id="popUpBox"><div id="popHead">Add new person</div><div id="popCont">\
    <div id="newNameBox"><input type="text" id="newName" placeholder="Name" value="' + name + '"/></div>\
    <div id="otherNamesBox"><textarea id="otherNames"  value="" placeholder="Other Names (helps while searching) ... Seperate with newlines"></textarea></div>\
    <div id="photoNewPersonBox" onclick="selectFace();"><span id="selectPhotoAdvice">Select Photo of Face<br/></span></div>\
    <button id="submiterNewGuy">Add</button><button id="cancelNewGuy" onclick="cancelNewGuy();">Cancel</button>\
    </div></div>');
    $("#submiterNewGuy").click(function() {
        var name = $("#newName").val();
        var othernames = $("#otherNames").val();
        var dp = newGuy.setDP;

        $.ajax({
            url: "http://www.realfeed.com/photo.manager.php?addpeople=true",
            data: { "name": name, "othernames": othernames, "dp": dp },
            method: "post",
            success: function(res) {
                res = JSON.parse(res);
                if (res.status == "success") {
                    $("#backgroundDimPortal").html('');
                    $('#popUpPortal').html('');
                    newGuy = {};

                    //forcing search
                    $("#addFeat").val("");
                    $("#addFeat").val(name);
                } else {
                    alert("error can not add him!");
                }
            }


        });


    });

}

function cancelNewGuy() {
    $("#backgroundDimPortal").html('');
    $('#popUpPortal').html('');
}

function selectFace() {
    $("#dimmer").hide();
    $("#popUpBox").hide();

    $(".prevImgElem").click(function() {
        var thisImgElem = this;
        var posImg = $(thisImgElem).position();
        var mouseX = event.clientX,
            mouseY = event.clientY;
        // C(posImg)
        // C(mouseX)
        //C(mouseY)
        $(this).css("box-shadow", "0px 0px 5px 0px #0000ff")

        $("#dimmer").show();
        //$("#popUpBox").show();
        $('#imageCroperPortal').html('<div id="imageCroper"></div>');

        var left_ = (window.innerWidth - 700) / 2;
        $("#imageCroper").css("left", left_ + "px");
        newGuy.left = left_;

        var w = $(thisImgElem).attr("data-real-width"),
            h = $(thisImgElem).attr("data-real-height");

        var src = $(thisImgElem).attr("src");
        var maxH = 380,
            maxW = 680;

        //let propait
        var height = maxH;
        var width = w * maxH / h;

        if (width > maxW) {
            //error
            //landscape
            width = maxW;
            height = maxW * h / w;

        }

        var xScale = w / width,
            yScale = h / height;

        $("#imageCroper").html('<img  id="cropImg" src="' + src + '" />');

        var posTop = (400 - height) / 2
        var posLeft = (700 - width) / 2;


        $("#cropImg").css({ "height": height, "width": width, "top": posTop, "left": posLeft, "position": "absolute" });

        $("#cropImg").click(function() {
            var pos = $(this).position();
            var x = event.clientX;
            var y = event.clientY;
            C(pos)
            coordX = (x - posLeft - left_) * xScale;
            coordY = (y - posTop - 100) * yScale;

            newGuy.coordX = coordX;
            newGuy.coordY = coordY;
            newGuy.src = src;

            //undos
            $(".prevImgElem").off("click");
            $('#imageCroperPortal').html('');
            $("#popUpBox").show();

            getAllDPPreviews();








        });







    });
}

function getAllDPPreviews() {

    $.ajax({
        method: "post",
        url: "http://www.realfeed.com/photo.manager.php?dp=true",
        data: { "src": newGuy.src, "x": newGuy.coordX, "y": newGuy.coordY },
        success: function(res) {
            res = JSON.parse(res);
            if (res.status == "success") {
                newGuy.dps = res.urls;
                newGuy.dpNo = 0;
                newGuy.setDP = res.urls[0];
                $("#photoNewPersonBox").html('<img id="dpSelecter" src="http:\/\/www.realfeed.com/photo.manager\/' + res.urls[0] + '.jpeg" style="height:200px;width:200px;"/>');
                $("#photoNewPersonBox").attr("onclick", "{}");

                $("#dpSelecter").click(function() {
                    var i = newGuy.dpNo + 1;
                    if (newGuy.dps.length <= i) { i = 0; }
                    C("i " + i)
                    newGuy.dpNo = i;
                    newGuy.setDP = newGuy.dps[i];
                    $("#dpSelecter").attr("src", 'http:\/\/www.realfeed.com/photo.manager\/' + newGuy.setDP + '.jpeg');

                });
            }

        }
    });
}

function addToFeaturingList(id, name, dp) {

    if (featuringList.includes(id)) {
        alert("Already Added To Featuring List");
        return false;
    }

    featuringList.push(id);
    featuringData[id] = { "name": name, "dp": dp };

    $("#addParticipants").prepend('<div data-id="profileFeatAdd_' + id + '" class="featuringPreviewPerson">\
        <div class="addedFeaturingPeopleRemiveButton" onclick="removeFromFeatList(\'' + id + '\');">X</div>\
        \
        <img src="http:\/\/www.realfeed.com/photo.manager/' + dp + '.jpeg" class="featuringPreviewPersonDp"/>\
        <div class="featuringPreviewPersonName">' + name + '</div>\
    \
    </div > ');



}

function removeFromFeatList(id) {
    var index = featuringList.indexOf(id);
    if (index > -1) { featuringList.splice(index, 1); }

    //show that removed by hiding the icon
    $('[data-id="profileFeatAdd_' + id + '"]').hide(100);
}

function saveAllToPhotos() {
    //featuringList ... each photo url with caption ..... description

    var sendingPackage = { "featuringlist": featuringList, "discription": "", "imageset": [] };

    //adding each photo id and each caption in to image set
    for (i in sessions[currentSession]["images"]) {
        var thisPhoto = sessions[currentSession]["images"][i];
        //C(thisPhoto)
        if (thisPhoto.status == "active") {
            var thisCaption = $("#caption_" + thisPhoto.photoid).val() || "";
            var isFaved = addedPhotoFavouritedData[thisPhoto.photoid];
            sendingPackage.imageset.push({ "photoid": thisPhoto.photoid, "caption": thisCaption, "favourited": isFaved });


        }
    }

    //adding overall discription
    var discription = $('#discription').val() || "";
    sendingPackage.discription = discription;


    //C("sending Pachage");

    var sendingPackageString = JSON.stringify(sendingPackage);
    //C(sendingPackageString)

    //upload to server
    $.ajax({
        method: "post",
        url: "http://www.realfeed.com/photo.manager.php?addphotos=true",
        data: { "datastring": sendingPackageString },
        success: function(res) {
            res = JSON.parse(res);
            if (res.status == "success") {
                window.location = "http:\/\/www.realfeed.com/photo.manager.php?placehold=" + encodeURI(discription);
            } else { alert("error: " + res.reason); }

        }
    });






}


function peopleFilterIfNeeded(peopleList) {

    var out = '';
    if (peopleList.length > 0) {
        out = '<div id="peopleFilterButton_0" onclick="filterByPeopleSetUp()">Filter by people</div>\
        <div id="hintToFilterBySearch">Select Which people you need</div>';
    }
    return out;
}

function filterByPeopleSetUp() {
    $('#hintToFilterBySearch').css('display', 'inline-block');

    for (var i = 0; i < currentPeopleInSearchResults.length; i++) {

        var thisPersonInSearchResults = currentPeopleInSearchResults[i];
        //alert(thisPersonInSearchResults.id);
        $(".searchPeopleResultRow[data-profileid='" + thisPersonInSearchResults.id + "']").css({ boxShadow: '0px 0px 2px 0px #000' });

    }

    $(".searchPeopleResultRow[data-profileid]").click(function() {

        var thisSelectablePersonId = $(this).attr('data-profileid');
        //if not selected yet => select and indicate :stop
        if (!currentSelectedForPeopleFilter.includes(thisSelectablePersonId)) {


            currentSelectedForPeopleFilter.push(thisSelectablePersonId);
            $(".searchPeopleResultRow[data-profileid='" + thisSelectablePersonId + "']").css({
                'box- shadow': 'rgb(82, 117, 199) 0px 0px 3px 2px',
                'background-color': '#f5f5f5',
                'color': '#565656',
                'text-shadow': '0px 0px 2px #a7a7a7'
            });
            C(currentSelectedForPeopleFilter)


        }

        //if selected already => unselect and indicate
        else {
            currentSelectedForPeopleFilter.splice(currentSelectedForPeopleFilter.indexOf(thisSelectablePersonId), 1);
            $(".searchPeopleResultRow[data-profileid='" + thisSelectablePersonId + "']").css({
                'box- shadow': 'rgb(0, 0, 0) 0px 0px 2px 0px;',
                'background-color': '#ffffff',
                'color': '',
                'text-shadow': ''
            });
            C(currentSelectedForPeopleFilter)

        }
        proceedSearchResultFilterByPeople();

    });


    $('#peopleFilterButton_0').hide();

}

function proceedSearchResultFilterByPeople() {
    var photoList = lastPhotoSearch.photos;
    var mensionedPeople = lastPhotoSearch.mensioned;
    var favoOnes = lastPhotoSearch.favoritedinsubjected;
    var filteredPhotoList = [];
    for (var i = 0; i < photoList.length; i++) {

        var thisPost = photoList[i];

        var thisFeatList = thisPost.featuring;


        var shouldSelect = 1;
        for (u in currentSelectedForPeopleFilter) //filtering: substract not syuitables
        {
            var thisNeededPerson = currentSelectedForPeopleFilter[u];
            if (!thisFeatList.includes(thisNeededPerson)) {
                shouldSelect = 0;
                break;
            }
        }

        if (shouldSelect) {
            filteredPhotoList.push(thisPost);
        }

    }
    //filteredPhotoList is Ready now

    //************************************************************************************* 
    var filterActivatedOrNotStatus = (currentSelectedForPeopleFilter.length > 0) ? ' (Filtered)' : '';
    var photoResultsHeadHtml = '<div class="searchSummaryLine">Found ' + filteredPhotoList.length + ' Photo' + s(filteredPhotoList.length) + filterActivatedOrNotStatus + '</div>';

    photoResultHtmlPostSetFiltered = [];
    photoFilteredShownNextIndex = 0;

    var photoResultsHtml = '';
    for (i in filteredPhotoList) {
        var thisPhoto = filteredPhotoList[i];
        var photoPath = thisPhoto.path; // also works as the id of this single photo
        var discription = thisPhoto.discription;
        var postId = thisPhoto.postid;
        var caption = thisPhoto.caption;
        var date = thisPhoto.date;
        var featuring = thisPhoto.featuring;
        var isFavouritedStyle = favoOnes.includes(photoPath) ? 'block' : 'none';
        var imageIsFavoStyle = favoOnes.includes(photoPath) ? "border: 4px solid #c0b9ff;" : "";


        var featuringProfileDpHtml = '';
        for (i in featuring) {
            var thisFeturingGuy = featuring[i]; // id of that guy
            var thisDpUrl = mensionedPeople[thisFeturingGuy].dp;
            var thisName = mensionedPeople[thisFeturingGuy].name;
            featuringProfileDpHtml += '<div data-photoid="' + photoPath + '" data-photo-type="searchresult"  data-profileid="' + thisFeturingGuy + '" data-title="' + thisName + '" class="photoResultfeaturingDpContainer">\
                            <div data-photoid="' + photoPath + '" data-photo-type="searchresult"  data-id="' + thisFeturingGuy + '" class="photoFeaturingProfileOptionProtal"></div>\
                            <img data-photoid="' + photoPath + '" data-photo-type="searchresult"  data-profileid="' + thisFeturingGuy + '"  src="http:\/\/www.realfeed.com\/photo.manager\/' + thisDpUrl + '.jpeg" draggable="false" class="photoResultfeaturingDp"/>\
                            \
                            </div>';
        }

        var thisFavoHtml = '<div class="favouritedIconPreview favoIconSearchresults" style="display:' + isFavouritedStyle + ';" id="favoIcon_' + photoPath + '" data-title="Favourited : double click on photo to unfavourite"></div>';


        photoResultsHtml += '<div class="photoResultRow">\
                       <div id="_' + photoPath + '" data-photo-type="searchresult" class="whitedBackground">' + thisFavoHtml + '<img data-photoid="' + photoPath + '" data-photo-type="searchresult" style="' + imageIsFavoStyle + '" ondblclick="toggleFavo(\'' + photoPath + '\',\'' + postId + '\')" onclick="toggleFeatList(\'' + photoPath + '\')" onmouseout="return false;hideFeatList(\'' + photoPath + '\')" draggable="false" src="http:\/\/www.realfeed.com\/photo.manager\/' + photoPath + '.jpeg" class="photoResultsImg tranparentEffected" data-title="' + discription + ' <br/> <b>' + caption + '</b> <br/> ' + date + '" onload="$(this).attr(\'class\',\'photoResultsImg\');$(\'#_' + photoPath + '\').css(\'background-color\',\'transparent\');" id="photoSearch_' + photoPath + '"/></div>\
                        <div id="featContainer_' + photoPath + '" class="featuringListContainer">' + featuringProfileDpHtml + '</div>\
                        </div>';

        photoResultHtmlPostSetFiltered.push(photoResultsHtml);
        photoResultsHtml = '';

    }
    var photoShowerContainer = '<div id="controlledPhotoShowerContainer"></div>';
    var showMoreHtml = '<div id="showMorePhotosFiltered" onmouseover="showMorePhotosFiltered();"></div>';

    $('#photoContentInResults').html(photoResultsHeadHtml + photoShowerContainer + showMoreHtml);
    showMorePhotosFiltered(); //show initialy
    //




    //************************************************************************************


}

function proceedSearch(event) {
    var key = event.keyCode ? event.keyCode : event.which;
    var searchQuery = $("#searchQuery").val();
    if (key == "13" || true) //all key ups now do the search : love ther server power
    {
        if (searchQuery == '') {
            $('#searchResults').html('');
            return false;
        }

        //C("enter pressed");
        $.ajax({
            url: "http://www.realfeed.com/photo.manager.php?contentsearch=true",
            data: { "query": searchQuery },
            method: "post",
            success: function(res) {
                res = extractJSON(res);

                if (res && res.status == "success") {
                    //if same query searched..profile option box vanishes ... then clicking  again shud show it again
                    currentProfileOptionsShowingFor = null;
                    lastPhotoSearch = res;
                    var peopleList = res.people;

                    //for use at filterByPeopleSetUp()
                    currentPeopleInSearchResults = peopleList;
                    currentSelectedForPeopleFilter = []; //emptied

                    var photoList = res.photos;
                    C(photoList)
                    var mensionedPeople = res.mensioned;
                    var favoOnes = res.favoritedinsubjected;
                    favoIdsInSearchResults = favoOnes;


                    photoResultHtmlPostSet = [];
                    photoShownNextIndex = 0;



                    var peopleResultsHtml = '<div class="searchSummaryLine">Identified ' + peopleList.length + ' Person' + s(peopleList.length) + peopleFilterIfNeeded(peopleList) + '</div>';
                    for (i in peopleList) {
                        var thisPerson = peopleList[i];
                        var thisName = thisPerson.name,
                            thisDp = thisPerson.dp;
                        peopleResultsHtml += '<div class="searchPeopleResultRow" data-profileid="' + thisPerson['id'] + '" data-title="' + thisName + '">\
                        <div class="dpSearchResultPerson"><img draggable="false" src="http:\/\/www.realfeed.com\/photo.manager\/' + thisDp + '.jpeg" class="dpImgSearchResultPeople"/></div>\
                        <div class="nameOfSearchResultPeople">' + thisName + '</div>\
                        \
                        </div>';
                    }

                    var photoResultsHeadHtml = '<div class="searchSummaryLine">Found ' + photoList.length + ' Photo' + s(photoList.length) + '</div>';

                    var photoResultsHtml = '';
                    for (i in photoList) {
                        var thisPhoto = photoList[i];
                        var photoPath = thisPhoto.path; // also works as the id of this single photo
                        var discription = thisPhoto.discription;
                        var postId = thisPhoto.postid;
                        var caption = thisPhoto.caption;
                        var date = thisPhoto.date;
                        var featuring = thisPhoto.featuring;
                        var isFavouritedStyle = favoOnes.includes(photoPath) ? 'block' : 'none';
                        var imageIsFavoStyle = favoOnes.includes(photoPath) ? "border: 4px solid #c0b9ff;" : "";


                        var featuringProfileDpHtml = '';
                        for (i in featuring) {
                            var thisFeturingGuy = featuring[i]; // id of that guy
                            var thisDpUrl = mensionedPeople[thisFeturingGuy].dp;
                            var thisName = mensionedPeople[thisFeturingGuy].name;
                            featuringProfileDpHtml += '<div data-photoid="' + photoPath + '" data-photo-type="searchresult"  data-profileid="' + thisFeturingGuy + '" data-title="' + thisName + '" class="photoResultfeaturingDpContainer">\
                            <div data-photoid="' + photoPath + '" data-photo-type="searchresult"  data-id="' + thisFeturingGuy + '" class="photoFeaturingProfileOptionProtal"></div>\
                            <img data-photoid="' + photoPath + '" data-photo-type="searchresult"  data-profileid="' + thisFeturingGuy + '"  src="http:\/\/www.realfeed.com\/photo.manager\/' + thisDpUrl + '.jpeg" draggable="false" class="photoResultfeaturingDp"/>\
                            \
                            </div>';
                        }

                        var thisFavoHtml = '<div class="favouritedIconPreview favoIconSearchresults" style="display:' + isFavouritedStyle + ';" id="favoIcon_' + photoPath + '" data-title="Favourited : double click on photo to unfavourite"></div>';


                        photoResultsHtml += '<div class="photoResultRow">\
                       <div id="_' + photoPath + '" data-photo-type="searchresult" class="whitedBackground">' + thisFavoHtml + '<img data-photoid="' + photoPath + '" data-photo-type="searchresult" style="' + imageIsFavoStyle + '" ondblclick="toggleFavo(\'' + photoPath + '\',\'' + postId + '\')" onclick="toggleFeatList(\'' + photoPath + '\')" onmouseout="return false;hideFeatList(\'' + photoPath + '\')" draggable="false" src="http:\/\/www.realfeed.com\/photo.manager\/' + photoPath + '.jpeg" class="photoResultsImg tranparentEffected" data-title="' + discription + ' <br/> <b>' + caption + '</b> <br/> ' + date + '" onload="$(this).attr(\'class\',\'photoResultsImg\');$(\'#_' + photoPath + '\').css(\'background-color\',\'transparent\');" id="photoSearch_' + photoPath + '"/></div>\
                        <div id="featContainer_' + photoPath + '" class="featuringListContainer">' + featuringProfileDpHtml + '</div>\
                        </div>';

                        photoResultHtmlPostSet.push(photoResultsHtml);
                        photoResultsHtml = '';

                    }
                    var photoShowerContainer = '<div id="controlledPhotoShowerContainer"></div>';
                    var showMoreHtml = '<div id="showMorePhotos" onmouseover="showMorePhotosSearch();"></div>';

                    $('#searchResults').html(peopleResultsHtml + '<div id="photoContentInResults">' + photoResultsHeadHtml + photoShowerContainer + showMoreHtml + '</div>');
                    showMorePhotosSearch();

                }
            }





        });
    }
}

function showMorePhotosSearch() {
    var bulkLength = 6;

    if (photoShownNextIndex + bulkLength < photoResultHtmlPostSet.length) // there is bulkLength(10) more photos
    {
        for (var i = 0; i < bulkLength; i++) {
            $('#controlledPhotoShowerContainer').append(photoResultHtmlPostSet[photoShownNextIndex + i]);


        }
        photoShownNextIndex += bulkLength;

        var moreCount = photoResultHtmlPostSet.length - photoShownNextIndex;
        if (moreCount > 0) {
            $('#showMorePhotos').html(moreCount + ' more photo' + s(moreCount));
        } else {
            noMoreSearchPhotos();
        }

    } else // less than 10 photos
    {
        for (var i = photoShownNextIndex; i < photoResultHtmlPostSet.length; i++) {
            $('#controlledPhotoShowerContainer').append(photoResultHtmlPostSet[i]);
        }
        photoShownNextIndex = photoResultHtmlPostSet.length - 1; //over
        if (photoResultHtmlPostSet.length > 0) { noMoreSearchPhotos(); } //there shud be at least one photo


    }

    setDataTitleToNotSet();
    setProfileOptionToNotSet();
    setRightClickOptionsToNotSet();
}

function showMorePhotosFiltered() {
    var bulkLength = 6;

    if (photoFilteredShownNextIndex + bulkLength < photoResultHtmlPostSetFiltered.length) // there is bulkLength(10) more photos
    {
        for (var i = 0; i < bulkLength; i++) {
            $('#controlledPhotoShowerContainer').append(photoResultHtmlPostSetFiltered[photoFilteredShownNextIndex + i]);


        }
        photoFilteredShownNextIndex += bulkLength;

        var moreCount = photoResultHtmlPostSetFiltered.length - photoFilteredShownNextIndex;
        if (moreCount > 0) {
            $('#showMorePhotosFiltered').html(moreCount + ' more photo' + s(moreCount));
        } else {
            noMoreSearchPhotosFiltered();
        }

    } else // less than 10 photos
    {
        for (var i = photoFilteredShownNextIndex; i < photoResultHtmlPostSetFiltered.length; i++) {
            $('#controlledPhotoShowerContainer').append(photoResultHtmlPostSetFiltered[i]);
        }
        photoFilteredShownNextIndex = photoResultHtmlPostSetFiltered.length - 1; //over
        if (photoResultHtmlPostSetFiltered.length > 0) { noMoreSearchPhotosFiltered(); } //there shud be at least one photo


    }

    setDataTitleToNotSet();
    setProfileOptionToNotSet();
    setRightClickOptionsToNotSet();
}



function noMoreSearchPhotos() {
    $('#showMorePhotos').html('That\'s All .  No more Photos');

    $('#showMorePhotos').off('mouseover');
    document.getElementById('showMorePhotos').onmouseover = function() {};
    $('#showMorePhotos').attr('onmouseover', '');
}

function noMoreSearchPhotosFiltered() {
    $('#showMorePhotosFiltered').html('That\'s All .  No more Photos');

    $('#showMorePhotosFiltered').off('mouseover');
    document.getElementById('showMorePhotosFiltered').onmouseover = function() {};
    $('#showMorePhotosFiltered').attr('onmouseover', '');
}

function duplicateWarning(fileNameRecieved) {
    $("#backgroundDimPortal").html('<div id="dimmer" onclick="clearDuplicateWarning()"></div>');
    $('#dupplicateDitectedPortal').html('<div id="duplicateWarningBox">\
        <div id="duplicatePopHead">Duplicate Image Detected!</div>\
        <div id="duplicatePopCont">A file with file name of <div class="fileNameDuplicateBox">' + fileNameRecieved + '</div> was already saved at server.So may be you are uploading it again.It is marked by red border.</div>\
        <div id="okayStringDuplicate" onclick="clearDuplicateWarning()">Okay, I Understand</div>\
    </div>');

    // setTimeout(function () { clearDuplicateWarning(); },4000);
}

function clearDuplicateWarning() {
    $('#dupplicateDitectedPortal').html('');
    if ($("#backgroundDimPortal").html() == '<div id="dimmer" onclick="clearDuplicateWarning()"></div>') {
        $("#backgroundDimPortal").html('');
    }

}

function zoomPreviewPhoto(elemId) {
    $("#" + elemId).css("max-width", "523px");
    $("#" + elemId).css("max-height", "unset");
}

function toggleFeatList(path) {
    $("#featContainer_" + path).toggle();
}

function hideFeatList(path) {
    $("#featContainer_" + path).hide();
}


function toggleFavourite(id) {
    //favoPreviewIco_
    var isFaved = addedPhotoFavouritedData[id];
    if (isFaved) {
        $("#favoPreviewIco_" + id).hide();
        addedPhotoFavouritedData[id] = false;
    } else {
        $("#favoPreviewIco_" + id).show();
        addedPhotoFavouritedData[id] = true;
    }
}

function RemoveFavoAskConfirm(id, postId) {
    $('#popUpPortal').html('<div id="removeFavoAskingPopBox">\
        <div id="removeFromFavoAskConfirmHead">Confirm remove from favourites</div>\
        <div id="removeFavoAskPopCont">Are you sure to remove that photo from your favourited list?</div>\
        <div id="okayRemoveFromFavo" onclick="RemoveFavo(\'' + id + '\', \'' + postId + '\');hideRemoveFromFavoConfirmBoxAndDimmer()">Remove</div>\
        <div id="cancelRemoveFromFavo" onclick="hideRemoveFromFavoConfirmBoxAndDimmer();">Cancel</div>\
    </div>');
    $("#backgroundDimPortal").html('<div id="dimmer" data-onclick="$(\'#backgroundDimPortal\').html(\'\');$(\'#dragDropBoxPortal\').html(\'\');"></div>');

}

function hideRemoveFromFavoConfirmBoxAndDimmer() {
    $('#popUpPortal').html('');
    $("#backgroundDimPortal").html('');

}

function RemoveFavo(id, postId) {
    // we know its : unfavouriting
    $.ajax({
        url: "http://www.realfeed.com/photo.manager.php?unfavourite=true",
        data: { "photoid": id },
        method: "post",
        success: function(res) {
            res = extractJSON(res);
            if (res) //valid json
            {
                if (res.status == "success") {
                    //remove from favoIdsInSearchResults
                    //var removedId = favoIdsInSearchResults.splice(favoIdsInSearchResults.indexOf(id), 1);//no need of variable

                    //hide favorite icon
                    $("#favoIcon_" + id).hide();
                    //give a message
                    showToast('Removed from favourites!');

                    //remove that post or call randomize?
                    $('#favo_post_' + id).hide(154);
                    countOffavPhotosSent--;
                    if (!countOffavPhotosSent) // if no favourites (all existed and sent here are removed ...then re-request)
                    {
                        requestAndShowFavoPhotos();
                    }


                }
            }
        }
    });

}

function toggleFavo(id, postId) {

    var isCurrentlyFavorited = favoIdsInSearchResults.includes(id);
    //if already favorited => unfavorite it
    if (isCurrentlyFavorited) {
        $.ajax({
            url: "http://www.realfeed.com/photo.manager.php?unfavourite=true",
            data: { "photoid": id },
            method: "post",
            success: function(res) {
                res = extractJSON(res);
                if (res) //valid json
                {
                    if (res.status == "success") {
                        //remove from favoIdsInSearchResults
                        var removedId = favoIdsInSearchResults.splice(favoIdsInSearchResults.indexOf(id), 1); //no need of variable

                        //hide favorite icon
                        $("#favoIcon_" + id).hide();
                        //remove border style
                        $("#photoSearch_" + id).css("border", "");
                        //give a message
                        showToast('Removed from favourites!');
                    }
                }
            }
        });
    }



    //if not favoritred => favorite it
    else {
        $.ajax({
            url: "http://www.realfeed.com/photo.manager.php?favourite=true",
            data: { "photoid": id, "postid": postId },
            method: "post",
            success: function(res) {
                res = extractJSON(res);
                if (res) //valid json
                {
                    if (res.status == "success") {
                        //add favoIdsInSearchResults
                        favoIdsInSearchResults.push(id);
                        //show favorite icon
                        $("#favoIcon_" + id).show();
                        //show border style
                        $("#photoSearch_" + id).css("border", "4px solid #c0b9ff");
                        //give a message
                        showToast('Added to favourites!');
                    }
                }
            }
        });
    }
}

function showToast(message, type) {
    type = type || 'normal';
    var typeStyleClass = ({ 'normal': 'toastBox', 'formError': 'toastBox_formError' }[type]) || 'toastBox';
    var thisToastid = toastCount;
    toastCount++;
    var toastHtml = '<div class="' + typeStyleClass + '" id="toast_' + thisToastid + '">' + message + '</div>';
    $("#toastPortal").append(toastHtml);
    setTimeout(function() { $("#" + "toast_" + thisToastid).hide(154); }, 4000);

}

function requestAndShowFavoPhotos() {
    $.ajax({
        url: 'http:\/\/www.realfeed.com\/photo.manager.php?favouriterandome=true&time=' + String(new Number(new Date())), //to stop caching
        method: 'post',
        success: function(res) {
            res = extractJSON(res);
            if (res) //valid json
            {
                var status = res.status;
                if (status == "success") //success
                {
                    //var favoOnes = res.favoritedinsubjected;
                    // favoIdsInSearchResults = favoOnes;


                    var photoList = res.photos;
                    countOffavPhotosSent = photoList.length; //global var

                    //if  no favourites found
                    if (countOffavPhotosSent < 1) {
                        var noFavoYetHtml = '<div id="noFavoYetReminder">No favourites Found<div id="smallNoteNoFavo">Double click on photos to make them favourited</div></div>';
                        $("#favoRandPhotoShowerPortal").html(noFavoYetHtml);
                        return false; //over--following code shud not run
                    }



                    var mensionedPeople = res.mensioned;




                    var photoResultsHtml = '';
                    for (i in photoList) {
                        var thisPhoto = photoList[i];
                        var photoPath = thisPhoto.path;
                        var discription = thisPhoto.discription;
                        var postId = thisPhoto.postid;
                        var caption = thisPhoto.caption;
                        var date = thisPhoto.date;
                        var featuring = thisPhoto.featuring;
                        var isFavouritedStyle = true ? 'block' : 'none'; // we know these are favourited :-)


                        var featuringProfileDpHtml = '';
                        for (i in featuring) {
                            var thisFeturingGuy = featuring[i];
                            var thisDpUrl = mensionedPeople[thisFeturingGuy].dp;
                            var thisName = mensionedPeople[thisFeturingGuy].name;
                            featuringProfileDpHtml += '<div data-photoid="' + photoPath + '" data-photo-type="favourited" data-profileid="' + thisFeturingGuy + '" data-title="' + thisName + '" class="photoResultfeaturingDpContainer">\
                            <div data-photoid="' + photoPath + '" data-photo-type="favourited" data-id="' + thisFeturingGuy + '" class="photoFeaturingProfileOptionProtal"></div>\
                            <img src="http:\/\/www.realfeed.com\/photo.manager\/' + thisDpUrl + '.jpeg" draggable="false" class="photoResultfeaturingDp" data-photoid="' + photoPath + '" data-photo-type="favourited" data-profileid="' + thisFeturingGuy + '"/>\
                            \
                            </div>';
                        }


                        var thisFavoHtml = '<div class="favouritedIconPreview favoIconSearchresults" style="display:' + isFavouritedStyle + ';" id="favoIcon_' + photoPath + '" data-title="Favourited : double click on photo to unfavourite"></div>';


                        photoResultsHtml += '<div id="favo_post_' + photoPath + '" class="photoResultRow">\
                       <div id="_' + photoPath + '" data-photo-type="favourited" class="whitedBackground">' + thisFavoHtml + '<img data-photoid="' + photoPath + '" data-photo-type="favourited" ondblclick="RemoveFavoAskConfirm(\'' + photoPath + '\',\'' + postId + '\')" onclick="toggleFeatList(\'' + photoPath + '\')" onmouseout="return false;hideFeatList(\'' + photoPath + '\')" draggable="false" src="http:\/\/www.realfeed.com\/photo.manager\/' + photoPath + '.jpeg" class="photoResultsImg tranparentEffected" data-title="' + discription + ' <br/> <b>' + caption + '</b> <br/> ' + date + '" onload="$(this).attr(\'class\',\'photoResultsImg\');$(\'#_' + photoPath + '\').css(\'background-color\',\'transparent\');"/></div>\
                        <div id="featContainer_' + photoPath + '" class="featuringListContainer">' + featuringProfileDpHtml + '</div>\
                        </div>';



                    }

                    $("#favoRandPhotoShowerPortal").html(photoResultsHtml);
                    setDataTitleToNotSet();
                    setProfileOptionToNotSet();
                    setRightClickOptionsToNotSet();

                }

            }
        }


    });
}

function setRightClickOptionsToNotSet() {
    $(".photoResultsImg[data-mouse-middle-function-set!='true']").mousedown(function(event) {


        if (event.which == 2) {
            event.preventDefault();
            var photoURL = $(this).attr('src');
            C(photoURL)
            var photoOpenInNewTab = window.open(photoURL, '_blank');
            if (!photoOpenInNewTab) { alert('Allow to open in new tab!') }



        }
    });
    $(".photoResultsImg").attr('data-mouse-middle-function-set', 'true');
}

function setDataTitleToNotSet() {
    $('#dataTitlePortal').html(''); //emptying as well
    $('[data-title][data-title-set-function!=true]').mousemove(function(event) {

        var thisElem = this;
        var mouseX = event.clientX;
        var mouseY = event.clientY;
        //C(mouseX);
        // C(mouseY);
        var H = window.innerHeight;
        var W = window.innerWidth;


        var boxLeft = mouseX + 20;
        var boxTop = mouseY + 20;
        if (W - boxLeft < 100) { boxLeft = mouseX - 110; }
        if (H - boxTop < 50) { boxTop = mouseY - 60; }





        var message = $(thisElem).attr('data-title');

        if (message == "<nothing>") { return false; } //noeven parent message
        if (message.match(/\<innerhtml\>/gim)) // to load content from somewhere else (if theres any line breaks )
        {
            var contElem = message.split(/\<innerhtml\>/gim)[1];
            message = $(contElem).html();
        }

        var messageHtml = ' <div id="dataTitleBox" style="top:' + boxTop + 'px;left:' + boxLeft + 'px;">' + message + '</div>';


        $('#dataTitlePortal').html(messageHtml);



    });
    $('[data-title][data-title-set-function!=true]').attr('data-title-set-function', 'true');

    $('[data-title][data-title-unset-function!=true]').mouseout(function() {
        var thisElem = this;


        $('#dataTitlePortal').html('');
    });
    $('[data-title][data-title-unset-function!=true]').attr('data-title-unset-function', 'true');

}

function setProfileOptionToNotSet() {
    $(".photoResultfeaturingDp[data-profileid][data-profile-option-set!=true]").click(function() {
        //currentProfileOptionsShowingFor

        var thisElem = this;
        var id = $(thisElem).attr("data-profileid");
        var photoId = $(thisElem).attr("data-photoid");
        var photoType = $(thisElem).attr("data-photo-type");

        var uniqueIdForThisProfileOptionBox = id + "_" + photoId + "_" + photoType;
        if (currentProfileOptionsShowingFor == uniqueIdForThisProfileOptionBox) //hide the box and stop
        {
            return false; //nothing doing
            $('.photoFeaturingProfileOptionProtal[data-id="' + id + '"][data-photoid="' + photoId + '"][data-photo-type="' + photoType + '"]').html('');
            currentProfileOptionsShowingFor = null;
            return false;
        }

        //if new profile options are asked
        $('.photoFeaturingProfileOptionProtal').html(''); //clearing all 
        //showing required box

        C(id);
        C(photoId);
        C(photoType);

        //trying to keep the profile option box in side the image
        var limiterRight = 10; //padding like ting avoiding overlap with right image border
        var limiterLeft = 15; //padding like ting avoiding overlap with left image border
        var profileOptionPopBoxLeft = 0;
        var postBoxPositionLeftCoord = $("#_" + photoId + "[data-photo-type='" + photoType + "']").offset()["left"];
        var imageWidth = forceNumberize($("img[data-photo-type='" + photoType + "'][data-photoid='" + photoId + "']").css("width"));
        var dpIconLeft = $(thisElem).offset()["left"];
        C(postBoxPositionLeftCoord + " " + imageWidth + " " + dpIconLeft);
        //if leaving the border in right side 
        if (dpIconLeft + 200 > postBoxPositionLeftCoord + imageWidth - limiterRight) {
            profileOptionPopBoxLeft = postBoxPositionLeftCoord + imageWidth - 200 - dpIconLeft - limiterRight;
        }
        //if leaving the border left side
        if (dpIconLeft < postBoxPositionLeftCoord + limiterLeft + 200) {

            profileOptionPopBoxLeft = postBoxPositionLeftCoord + limiterLeft + 200 - dpIconLeft;
        }





        var arrrowHtml = '<div class="arrowUpProfileBox"></div><div class="arrowUpBorderProfileBox"></div>';
        var profileOptionHtml = '<div data-title="<nothing>" data-profileid="' + id + '" data-photoid="' + photoId + '" data-photo-type="' + photoType + '" style="left:' + profileOptionPopBoxLeft + 'px;" class="profileOptionPopBox">Loading Profile Settings...</div>';





        $('.photoFeaturingProfileOptionProtal[data-id="' + id + '"][data-photoid="' + photoId + '"][data-photo-type="' + photoType + '"]').html(arrrowHtml + profileOptionHtml);
        currentProfileOptionsShowingFor = uniqueIdForThisProfileOptionBox; //saving about current showing box

        requestAndShowProfileSettings(id, photoId, photoType);
        setDataTitleToNotSet();




    });

    $("[data-profileid][data-profile-option-set!=true]").attr('data-profile-option-set', 'true');
}

function requestAndShowProfileSettings(id, photoId, photoType) {

    $.ajax({
        url: 'http:\/\/www.realfeed.com\/photo.manager.php?profilesettings=true',
        method: 'post',
        data: { "profileid": id },
        success: function(res) {

            res = extractJSON(res);
            if (res) //valid json
            {
                var status = res.status;
                if (status == "success") {
                    C(res);
                    var profile = res.profiledata;
                    var dpURL = 'http:\/\/www.realfeed.com/photo.manager/' + profile.dp + '.jpeg';
                    var attrSet1 = 'data-profileid="' + id + '" data-photoid="' + photoId + '" data-photo-type="' + photoType + '"';
                    var profileSettingHtml = '<div class="profileSettingsHead" ' + attrSet1 + '><img ' + attrSet1 + ' src="' + dpURL + '" data-title="Change DP" class="dpPRofileSettingsHEad"/><input ' + attrSet1 + ' type="text" id="" class="editableNameProfileSettings" placeholder="Enter Name" value="' + profile.name + '"/></div>\
                    <div class="profileSettingsContent" ' + attrSet1 + '>\
                        <div class="profileSettingsOtherNamesContainer" ' + attrSet1 + '><textarea ' + attrSet1 + ' class="pofileSttingsOtherNamesBox" >' + profile.othernames + '</textarea></div>\
                    <div class="profileSettingsButtonRow">\
                        <div class="updateProfileSettingsButton" ' + attrSet1 + '>Update</div>\
                        <div class="cancelProfileSettingsButton" ' + attrSet1 + '>Cancel</div>\
                    </div>\
                    </div>\
                    \
                    ';


                    $('.profileOptionPopBox[data-profileid="' + id + '"][data-photoid="' + photoId + '"][data-photo-type="' + photoType + '"]').html(profileSettingHtml);


                    setDataTitleToNotSet();
                    setProfilePhotoUpdateSelectPhoto();
                    setProfileSettingUpdateButtonFunctions();

                }
            }


        }

    });


}

function setProfilePhotoUpdateSelectPhoto() {
    $('.dpPRofileSettingsHEad').click(function() {
        var profileId = $(this).attr('data-profileid');
        subjectedGuyIdProfilePicUpdate = profileId; //setting profile id globally ... useful after selecting a photo
        $("#backgroundDimPortal").html('<div id="dimmer" data-onclick="$(\'#backgroundDimPortal\').html(\'\');$(\'#dragDropBoxPortal\').html(\'\');"></div>');
        $("#popUpPortal").html('<div id="POPdPuPDATERbOX"><div id="popHead">Select a photo to continue</div><div id="cancelProfileDpUpdateProccessIcon" onclick="cancelProfileDpUpdateProccess();">Cancel</div><div id="popCont">\
        <div id="profilePhotoUpdatePhotoSelectCont"><div id="loadingPhotosText0">Loading Photos...</div></div>\
        \
        </div></div>');

        $.ajax({
            url: 'http:\/\/www.realfeed.com\/photo.manager.php?photosfordp=true',
            method: 'post',
            data: { 'profileid': profileId },
            success: function(res) {

                res = extractJSON(res);

                if (res) //valid jason
                {

                    if (res.status == 'success') {

                        var photolist = res.photolist;
                        profilePhotoUpdateData = new Object();
                        profilePhotoUpdateData.count = photolist.length;
                        profilePhotoUpdateData.offset = 0;
                        profilePhotoUpdateData.photolist = photolist;


                        $('#profilePhotoUpdatePhotoSelectCont').html('<div id="proPicUpdatePhtoSuggContainer"></div>');
                        proPhotoUpdateSHowSuggPhotosInControl();
                        $("body").css('overflow', 'hidden');






                    }
                }

            }


        });


    });
}

function proPhotoUpdateSHowSuggPhotosInControl() {
    var bulkLength = 10;
    if (profilePhotoUpdateData.offset + bulkLength < profilePhotoUpdateData.count) //there is more than bulklength photos
    {
        var additionalSuggPhotoHml = '';
        for (var i = profilePhotoUpdateData.offset; i < profilePhotoUpdateData.offset + bulkLength; i++) {
            var thisPhoto = profilePhotoUpdateData.photolist[i];
            var photoURL = 'http:\/\/www.realfeed.com/photo.manager/' + thisPhoto + '.jpeg';
            additionalSuggPhotoHml += htmlFor_addSuggProPics(photoURL, thisPhoto);

        }
        var nextHtml = '<div id="showMoreProPicUpdateChooseImg" onclick="$(this).hide();proPhotoUpdateSHowSuggPhotosInControl();">Show More</div>';
        $('#proPicUpdatePhtoSuggContainer').append(additionalSuggPhotoHml + nextHtml);
        profilePhotoUpdateData.offset += bulkLength;


    } else if (profilePhotoUpdateData.offset < profilePhotoUpdateData.count) // less than bulk length but therere are more photos
    {
        var additionalSuggPhotoHml = '';
        for (var i = profilePhotoUpdateData.offset; i < profilePhotoUpdateData.count; i++) {
            var thisPhoto = profilePhotoUpdateData.photolist[i];
            var photoURL = 'http:\/\/www.realfeed.com/photo.manager/' + thisPhoto + '.jpeg';
            additionalSuggPhotoHml += htmlFor_addSuggProPics(photoURL, thisPhoto);
        }

        profilePhotoUpdateData.offset = profilePhotoUpdateData.count;

        var noMorePhotosHtml = '<div id="noMorePhotosProPicUpdateSugg">That\'s All.No more photos!</div>';
        $('#proPicUpdatePhtoSuggContainer').append(additionalSuggPhotoHml + noMorePhotosHtml);
    } else {
        //nomore photos
    }



}


function htmlFor_addSuggProPics(photoURL, photoId) {
    return '<img onclick="selectToUpdatePropic(\'' + photoId + '\');"  src="' + photoURL + '"  class="suggProPicToChoose"/>';
}

function selectToUpdatePropic(photoId) {

    $('#popHead').html('Point where the face is ... Just click on the nose!');
    var photoURL = 'http:\/\/www.realfeed.com/photo.manager/' + photoId + '.jpeg';

    var selectFaceHtml = '<img src="' + photoURL + '" data-title="Click On The Face" id="faceClickImage" onclick="clickOnFaceInDpUpdate(event,\'' + photoId + '\');"/>';
    $('#proPicUpdatePhtoSuggContainer').html(selectFaceHtml);
    setDataTitleToNotSet();

}

function clickOnFaceInDpUpdate(event, photoId) {
    /*  NEW LESSON!
       to calcultae coordX and coordY => image position is given respect to POPdPuPDATERbOX (as its fixed )
                                      => consider margins as well


        */
    var mouseX = (forceNumberize(event.clientX));
    var mouseY = (forceNumberize(event.clientY));
    var photoPos = $('#faceClickImage').position();
    var popBoxPos = $('#POPdPuPDATERbOX').position();
    var popBoxX = popBoxPos['left'] - 400; //substarcted margin-left
    var popBoxY = popBoxPos['top'] - 180; //substarcted margin-top
    var photoX = forceNumberize(photoPos['left']) + popBoxX + 14; //margin 14
    var photoY = forceNumberize(photoPos['top']) + popBoxY + 14; //margin 14
    // T({ 'mouseX':mouseX,'mouseY': mouseY,'photoX': photoX,'photoY': photoY });

    //face coords relative to photo --- photo is not in real size
    var coordX = (mouseX - photoX);
    var coordY = (mouseY - photoY);

    var photoH = forceNumberize($('#faceClickImage').css('height'));
    var photoW = forceNumberize($('#faceClickImage').css('width'));
    T({ 'mouseX': mouseX, 'mouseY': mouseY, 'popBoxX': popBoxX, 'popBoxY': popBoxY, 'photoX': photoX, 'photoY': photoY, 'coordX': coordX, 'coordY': coordY, 'photoH': photoH, 'photoW': photoW });

    $('#popHead').html('Waiting for DP selection list');
    $('#proPicUpdatePhtoSuggContainer').html('<div id="waitingForDpSuggDPUpdate">Loading DP suggestions.Please wait...</div>');
    setDataTitleToNotSet();

    // requesting face photos with difference sizes
    $.ajax({
        url: 'http:\/\/www.realfeed.com\/photo.manager.php?dpcandidatesforupdatedp=true',
        data: { 'x': coordX, 'y': coordY, 'h': photoH, 'w': photoW, 'photoid': photoId },
        method: 'post',
        success: function(res) {
            res = extractJSON(res);
            if (res) {
                if (res.status == 'success') {
                    var urls = res.urls;
                    var photoDPSelectionFromSentOnesHtml = '';
                    for (i in urls) {
                        var thisURL = urls[i];
                        var photoURL = 'http:\/\/www.realfeed.com/photo.manager/' + thisURL + '.jpeg';
                        photoDPSelectionFromSentOnesHtml += '<img data-url="' + thisURL + '" src="' + photoURL + '" class="selectForDpImg" onclick="updateDpFinalConfirmed(this);"/>';

                    }
                    $('#popHead').html('Select Most Suitable Image as DP');

                    photoDPSelectionFromSentOnesHtml += '<div id="settingIndicatorDpUpdtaeSelection"></div>';
                    $('#proPicUpdatePhtoSuggContainer').html(photoDPSelectionFromSentOnesHtml);


                } else {
                    $("#backgroundDimPortal").html('');
                    $('#popUpPortal').html('');
                    $('body').css('overflow', 'unset');
                    alert("status:error");
                }

            } else {
                $("#backgroundDimPortal").html('');
                $('#popUpPortal').html('');
                $('body').css('overflow', 'unset');
                alert("server error!");
            }
        }

    });

}

function updateDpFinalConfirmed(thisElem) {
    var profileId = subjectedGuyIdProfilePicUpdate; //from global
    var dpPhotoId = $(thisElem).attr('data-url');
    $(thisElem).css({ 'box-shadow': '0px 0px 12px 1px #6662b3' });
    $('.selectForDpImg').hide();
    $(thisElem).show();
    $('#settingIndicatorDpUpdtaeSelection').html('Saving as DP.Please wait...');
    $('#popHead').html('Saving DP');
    $('#proPicUpdatePhtoSuggContainer').css('height', '266px');
    $('#POPdPuPDATERbOX').css('height', '317px');

    setDataTitleToNotSet();

    $.ajax({

        url: 'http:\/\/www.realfeed.com\/photo.manager.php?updatedp=true',
        method: 'post',
        data: { 'profileid': profileId, 'dpphotoid': dpPhotoId },
        success: function(res) {
            res = extractJSON(res);
            if (res) {
                if (res.status == 'success') {

                    $('#settingIndicatorDpUpdtaeSelection').html('<div id="dpUpdateSuccessMsg">Successfuly Updated!</div>');
                    setTimeout(function() {
                        $("#backgroundDimPortal").html('');
                        $('#popUpPortal').html('');
                        $('body').css('overflow', 'unset');
                    }, 1000);
                }

            }

        }

    });
}

function setProfileSettingUpdateButtonFunctions() { //update button
    $(".updateProfileSettingsButton").click(function() {
        var profileId = $(this).attr('data-profileid');
        var newName = $('.editableNameProfileSettings[data-profileid="' + profileId + '"]').val();
        var newOtherNames = $('.pofileSttingsOtherNamesBox[data-profileid="' + profileId + '"]').val();
        //send them to server
        $.ajax({
            url: 'http:\/\/www.realfeed.com\/photo.manager.php?updateprofilesettings=true',
            method: 'post',
            data: { 'profileid': profileId, 'newname': newName, 'newothernames': newOtherNames },
            success: function(res) {
                res = extractJSON(res);
                if (res) {
                    var status = res.status;
                    if (status == 'success') {
                        showToast('Profile Setting Updated!');
                        $('.photoFeaturingProfileOptionProtal[data-id="' + profileId + '"]').html('');
                        currentProfileOptionsShowingFor = null; //to let allow show settings after clicking again!
                    } else {
                        showToast('Please Recheck Entered Names.Invalid values recieved!', 'formError');

                    }
                } else {
                    showToast('Server Error...Failed to update profile settings!', 'formError');
                }
            }
        });

    });
    //cancel button
    $(".cancelProfileSettingsButton").click(function() {
        var profileId = $(this).attr('data-profileid');

        $('.photoFeaturingProfileOptionProtal[data-id="' + profileId + '"]').html('');
        currentProfileOptionsShowingFor = null; //to let allow show settings after clicking again!

    });



}

function cancelProfileDpUpdateProccess() {
    $("#backgroundDimPortal").html('');
    $("#popUpPortal").html('');
    $('body').css('overflow', 'unset');
}

function showLastPhotoUploadHistoy() {
    $.ajax({
        url: 'http:\/\/www.realfeed.com\/photo.manager.php?latestactivity=true',
        method: 'post',
        data: {},
        success: function(res) {

        }
    });
}

function responsiveWidthSet() {
    var winWidth = window.innerWidth;
    if (finalResponsiveWidthSetForWinWidth != winWidth) {

        //- 572 is for rightBox permanent  ...  other values are just calibration due to padding and stuff
        var availWidthForContBoxes = winWidth - 602 - 30;
        var availWidthForImages = winWidth  - 602 - 100 + 56;
        var availWidthForSearchBar = winWidth - 602 - 154;
        var availWidthForSearchQuery = winWidth - 602 - 129;
        var availWidthForSearchResults = winWidth - 602 - 113 + 89;
       

        var newCssForClasses = '#searchBox,#starredPhotoRandomShowerBox{width:'+availWidthForContBoxes+'px;}\
        .photoResultsImg{width:'+availWidthForImages+'px;}\
        #searchBar{width:'+availWidthForSearchBar+'px;}\
        #searchQuery{width:'+availWidthForSearchQuery+'px;}\
        #searchResults{width:'+availWidthForSearchResults+'px;}\
        ';
        $('head').append('<style>'+newCssForClasses+'</style>');


        finalResponsiveWidthSetForWinWidth = winWidth;
        C('window width change ditected and acted')
    }
}