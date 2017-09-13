
var controller = function (api) {
    var markers = [];
    var selectedMarker = null;
    var currentMap = null;
    var memoryPins = [];
    var apiKey=api;

    function getPostModel() {
        var pins = [];
        if (markers != null) {
            for (i = 0; i < markers.length; i++) {
                pins.push(markerToPin(markers[i]));
            }
        }
        return {
            action: "locationSave",
            apiKey: document.getElementById("apiKey").value,
            //zoom: document.getElementById("map_zoom_option").value,
            pins: pins
        };
    }
    function setPinProperties(pin) {
        if (pin.label == null) {
            pin.label = "";
        }
        if (pin.url == null) {
            pin.url = "";
        }
        if (pin.type == null) {
            pin.type = 2;
        }
        document.getElementById("pinUrl").value = pin.url;
        document.getElementById("pinLabel").value = pin.label;
        document.getElementById("pinType").value = pin.type;
        document.getElementById("pinRadius").value = pin.radius;
    }
    function removePin(marker) {
        marker.setMap(null);;
        markerClick(null, null);
        if (marker != null) {
            var _pins = [];
            for (var i = 0; i < markers.length; i++) {
                if (markers[i].i != marker.i) {
                    _pins.push(markers[i]);
                }
            }
            markers = _pins;
        }
    }
    function createMarker(pin, map) {
        return new google.maps.Marker({
            position: new google.maps.LatLng(pin.lat, pin.lng),
            map: map,
            i: markers.length + 1,
            label: pin.label,
            url: pin.url
        });
    }
    function createCircle(pin, map) {
        return new google.maps.Circle({
            map: map,
            i: markers.length + 1,
            label: pin.label,
            url: pin.url,
            strokeColor: '#FF0000',
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: '#FF0000',
            fillOpacity: 0.35,
            center: { lat: pin.lat, lng: pin.lng },
            radius: pin.radius
        });
    }
    function createPin(pin, map) {
        var marker = null;
        if (pin.radius == null) {
            pin.radius = 1000;
        }
        pin.lat = parseFloat(pin.lat);
        pin.lng = parseFloat(pin.lng);
        pin.radius = parseInt(pin.radius);
        if (pin.type == 1) {
            marker = createCircle(pin, map);
        } else if (pin.type == 2) {
            marker = createMarker(pin, map);
        }
        else {
            marker = createMarker(pin, map);
        }
        marker.radius = pin.radius;
        marker.type = pin.type;
        return marker;
    }
    function setPin(pin, map) {
        var marker = createPin(pin, map);
        marker.setMap(map);
        marker.addListener("click", function () {
            markerClick(this, map);
        });
        markers.push(marker);
        return marker;
    }
    function markerToPin(marker) {
        var pin = {
            lat: 0,
            lng: 0,
            label: marker.label,
            url: marker.url,
            type: marker.type,
        };
        if (marker.type == 1) {
            pin.lat = marker.center.lat();
            pin.lng = marker.center.lng();
            pin.radius = marker.radius;
        } else if (marker.type == 2) {
            pin.lat = marker.position.lat();
            pin.lng = marker.position.lng();
        }
        return pin;
    }
    function markerClick(marker, map) {
        selectedMarker = marker;
        if (marker != null) {
            setPinProperties(markerToPin(marker));
        }
        var pinButtons = document.getElementsByClassName("pinBtn");
        if (pinButtons != null) {
            for (i = 0; i < pinButtons.length; i++) {
                pinButtons[i].style.display = "none";
            }
            if (marker != null) {
                for (i = 0; i < pinButtons.length; i++) {
                    pinButtons[i].style.display = "block";
                }
            }
        }
    }
    function searchInit(map) {
        var input = document.getElementById("placeSearchText");
        if (input != null) {
            var searchBox = new google.maps.places.SearchBox(input);
            searchBox.addListener("places_changed", function () {
                var result = searchBox.getPlaces();
                if (result == null || result.length == 0) {
                    return;
                }
                var bounds = new google.maps.LatLngBounds();
                for (var i = 0; i < result.length; i++) {
                    var place = result[i];
                    setPin({
                        type: 2,
                        lat: place.geometry.location.lat(),
                        lng: place.geometry.location.lng(),
                    }, map);
                    bounds.extend(place.geometry.location);
                }
                map.fitBounds(bounds);
                input.value = "";
            });
        }
    }
    this.setPins = function (pins, map) {
        memoryPins = pins;
        if (map == undefined) {
            map = currentMap;
        }
        if (pins != null && map != null) {
            for (var i = 0; i < pins.length; i++) {
                setPin(pins[i], map);
            }
        }
    }
    this.mapInit = function () {
        var map = new google.maps.Map(document.getElementById('map'), {
            zoom: 10,
            center: { lat: 40.9811277, lng: 29.0280334 }
        });
        searchInit(map);
        currentMap = map;
        this.setPins(memoryPins, map);
        map.addListener('click', function (e) {
            setPin({
                lat: e.latLng.lat(),
                lng: e.latLng.lng(),
                type: 2
            }, map);
            markerClick(null, map);
        });
    }
    this.removePin = function () {
        removePin(selectedMarker);
    }
    this.clearMapPin = function () {
        if (markers == null) {
            markers = [];
        }
        for (var i = 0; i < markers.length; i++) {
            removePin(markers[i]);
        }
        pins = [];
    }
    this.save = function (input,callBack) {
        if(input!=null){
            input.setAttribute("disabled","disabled");
            input.value="Güncelleniyor...";
        }
        jQuery.post(postUrl, getPostModel(),
            function (response) {
                if (!response.isOk) {
                    alert(response.message);
                }
                if(input!=null){
                    input.removeAttribute("disabled");
                    input.value="Güncelle";
                }
                if(callBack!=null){
                    callBack();
                }
            });
    }
    this.saveApiKey = function () {
        var element = document.getElementById("apiKey");
        if (element != null) {
            jQuery.post(postUrl,
                {
                    action: 'googleApiKeySave',
                    apiKey: element.value
                },
                function (response) {
                    console.log(response);
                    if (response.isOk) {
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                });
        }
    }
    this.closePinOptions = function () {
        markerClick(null, map);
        this.back();
    }
    this.pinProperties = function () {
        var pinProperties = document.getElementById("pinProperties");
        var pinControls = document.getElementById("pinControls");
        if (pinControls != null && pinProperties != null) {
            pinProperties.style.display = "block";
            pinControls.style.display = "none";
        }
    }
    this.back = function () {
        var pinProperties = document.getElementById("pinProperties");
        var pinControls = document.getElementById("pinControls");
        if (pinControls != null && pinProperties != null) {
            pinProperties.style.display = "none";
            pinControls.style.display = "block";
        }
    }
    this.pinTypeChange = function (input) {
        if (input.value == "NaN") {
            alert('çok yakında sizlerle');
            input.value = selectedMarker.type;
            return;
        }
        var map = selectedMarker.map;
        var marker = selectedMarker;
        removePin(selectedMarker);
        var pin = markerToPin(marker);
        pin.type = parseInt(input.value);
        selectedMarker = setPin(pin, map);
        var circlesProperties = document.getElementsByClassName("circlesProperty");
        for (var i = 0; i < circlesProperties.length; i++) {
            circlesProperties[i].style.display = "none";
        }
        if (pin.type == 1) {
            for (var i = 0; i < circlesProperties.length; i++) {
                circlesProperties[i].style.display = "block";
            }
        }
    }
    this.pinLabelChange = function (input) {
        if (input != null) {
            var map = selectedMarker.map;
            var marker = selectedMarker;
            removePin(selectedMarker);
            var pin = markerToPin(marker);
            pin.label = input.value;
            selectedMarker = setPin(pin, map);
        }
    }
    this.pinRadiusChange = function (input) {
        if (input != null) {
            var map = selectedMarker.map;
            var marker = selectedMarker;
            removePin(selectedMarker);
            var pin = markerToPin(marker);
            pin.radius = parseInt(input.value);
            selectedMarker = setPin(pin, map);
        }
    }
    this.pinUrlChange = function (input) {
        if (input != null) {
            var map = selectedMarker.map;
            var marker = selectedMarker;
            removePin(selectedMarker);
            var pin = markerToPin(marker);
            pin.url = input.value;
            selectedMarker = setPin(pin, map);
        }
    }
    this.generateWebSiteCode=function(isPhpCode){
        var popup=document.getElementById("mapBluer");
        var codePopup=document.getElementById("generateWebSiteCodePopup");
        if(isPhpCode===true){
            var embedCodeText=
            '<link href="/wp-content/plugins/Exline.MaPinner/contents/css/web.style.css" rel="stylesheet" type="text/css"> \n'+
            '<script src="/wp-content/plugins/Exline.MaPinner/contents/js/web.js"></script> \n'+  
            '<script> \n'+
            '    var controller=new exMapinnerController(document.getElementById("map"),<?php echo get_option("exMapinnerLocation") ?>); \n'+
            '</script> \n'+
            '<script defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo get_option("exMapinnerApiKey"); ?>&libraries=places&callback=controller.mapInit"></script> \n'+
            '<div id="map"></div> \n';
            document.getElementById("webSiteEmbedCodeText").innerText=embedCodeText;
            codePopup.style.display="block";
            popup.style.display="block";
        }else{
            this.save(null,function(){
                jQuery.post(postUrl, {action:"getjscode"},
                function (response) {
                    document.getElementById("webSiteEmbedCodeText").innerText=response;
                    codePopup.style.display="block";
                    popup.style.display="block";
                });
            });
        }
    }
    this.generateWebSiteCodeBySelectedPin=function(){
        if(selectedMarker==undefined){
            alert("kodun yaratılabilmesi için pin seçmeniz gereklidir");
            return;
        }
        var popup=document.getElementById("mapBluer");
        var codePopup=document.getElementById("generateWebSiteCodePopup");
        var pin=markerToPin(selectedMarker);
        var embedCodeText=
        '<link href="/wp-content/plugins/Exline.MaPinner/contents/css/web.style.css" rel="stylesheet" type="text/css"> \n'+
        '<script src="/wp-content/plugins/Exline.MaPinner/contents/js/web.js"></script> \n'+  
        '<script> \n'+
        '    var controller=new exMapinnerController(document.getElementById("map"),'+JSON.stringify(pin)+'); \n'+
        '</script> \n'+
        '<script defer src="https://maps.googleapis.com/maps/api/js?key='+apiKey+'&libraries=places&callback=controller.mapInit"></script> \n'+
        '<div id="map"></div> \n';
        document.getElementById("webSiteEmbedCodeText").innerText=embedCodeText;
        codePopup.style.display="block";
        popup.style.display="block";
    }
    this.closeGenereateWebSiteCode=function(){
        var popup=document.getElementById("mapBluer");
        popup.style.display="none";
        var codePopup=document.getElementById("generateWebSiteCodePopup");
        codePopup.style.display="none";

    }
    // this.selectedPinProperties=(function(){
    //     var selectedPin=null;
    //     var currentMap=null;

    //     this.setPin=function(pin){
    //         selectedPin=pin;
    //     }
    //     this.setMap=function(map){
    //         currentMap=map;
    //     }

    //     this.typeChange=function(pinType){

    //     }
    //     this.labelChange=function(element){
    //         if(element=!null){
    //             selectedPin.label=element.value;
    //         }
    //     }
    // })()
}
// var pinProperties=function(pin,map){
//     var selectedPin=pin;
//     var currentMap=map;
//     this.setPin=function(pin){
//         selectedPin=pin;
//     }
//     this.setMap=function(map){
//         currentMap=map;
//     }
//     this.typeChange=function(pinType){
//     }
//     this.labelChange=function(element)
//         if(element=!null){
//             selectedPin.label=element.value;
//         }
//     }
// }