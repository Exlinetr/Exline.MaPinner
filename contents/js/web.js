
var exMapinnerController = function (mapContext,pins,mapOptions) {
    this.mapContext=mapContext;
    this.mapOptions=mapOptions;
    
    var defaultMapOptions={
        zoom:14,
        draggable:true
    };
    function createMarker(pin, map) {
        return new google.maps.Marker({
            position: new google.maps.LatLng(pin.lat, pin.lng),
            map: map,
            i: pins.length + 1,
            label: pin.label,
            url: pin.url
        });
    }
    function createCircle(pin, map) {
        return new google.maps.Circle({
            map: map,
            i: pins.length + 1,
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
            if(this.url!=null){
                window.open(this.url,'_blank');
            }
        });
        return marker;
    }
    function setPins(pins, map) {
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
        if(this.mapContext==null){
            this.mapContext=document.getElementById("map");
        }
        if(this.mapOptions==null){
            this.mapOptions=defaultMapOptions;
        }
        var root=pins[0];
        this.mapOptions.center={ lat: parseFloat(root.lat), lng: parseFloat(root.lng) };
        var map = new google.maps.Map(this.mapContext, this.mapOptions);
        /*
            {
            zoom: .zoom,
            center: ,
            disableDefaultUI: true
        }
        */
        setPins(pins,map);
    }
}