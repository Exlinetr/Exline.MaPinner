<?php
/*
Plugin Name: Mapinner
Plugin URI: www.exlinetr.com/xyzt
Description: Map üzerinden pinli adres gösterme
Version: 1.0.0
Author: exlinetr
Author URI: www.exlinetr.com
License: MIT
*/

if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']))
{
    die('You are not allowed to call this page directly.');
}
register_activation_hook(__FILE__, 'exMapinnerSetLocation');
function exMapinnerSetLocation( ) {
    add_option("exMapinnerLocation", '[40.9811277,29.0280334,13]');
}

function getLocation(){
    $location=get_option("exMapinnerLocation");
    if($location==null || $location==""){
        $location="[40.9811277,29.0280334,13]";
    }
    return json_decode($location);
}

// register_deactivation_hook(__FILE__, 'exMapinnerRemoveLocation');
// function exMapinnerRemoveLocation( ) {
//     delete_option("exMapinnerLocation");
// }

add_action('admin_menu', 'exMapinnerAddAdminMenu');
function exMapinnerAddAdminMenu()
{
     add_options_page('Ex-Mapiner','Ex-Mapiner', '8', 'Ex-Mapiner', 'exMapinnerAdminHtml');
}

add_action("wp_ajax_googleApiKeySave","googleApiKeySave");
function googleApiKeySave(){
    header('Content-Type: application/json');
    $apiKey=$_POST["apiKey"];
    $result=array();
    $result["data"]=$apiKey;
    if($apiKey==null ||$apiKey==""){
        $result["isOk"]=false;
        $result["message"]="You must enter google api key";
    }else{
        $oldApiKey=get_option($exMapinnerApiKeyFieldName);
        if($oldApiKey==null ||$oldApiKey==""){
            update_option($exMapinnerApiKeyFieldName,$apiKey);
        }else{
            add_option($exMapinnerApiKeyFieldName,$apiKey);
        }
        $result["isOk"]=true;
        $result["message"]="success";
    }
    echo json_encode($result); 
    wp_die();
}

add_action("wp_ajax_locationSave","locationSave");
function locationSave(){
    header('Content-Type: application/json');
    $location=$_POST["location"];
    $result=array();
    if($location==null ||$location==""){
        $result["isOk"]=false;
        $result["message"]="You must select a location from the map";
    }else{
        update_option("exMapinnerLocation",$location);
        $result["isOk"]=true;
        $result["message"]="success";
    }
    echo json_encode($result); 
    wp_die();
}


function exMapinnerAdminHtml(){
    $apiKey=get_option("exMapinnerApiKey");
    $location=getLocation();
    //AIzaSyAzzjRCTj5adWXm0hXZwLagi8KVkdPDWeA
    ?>
    <div>
        <h2>ex-mapinner Yönetim Sayfası <?php echo "location".get_option("exMapinnerLocation"); ?></h2>
        <div>
        <script>
            var postUrl="<?php echo admin_url('admin-ajax.php'); ?>";
        </script>
                    <script>
                        var controller=function(){
                            this.save=function(){
                                var element=document.getElementById("apikey");
                                if(element!=null){
                                    jQuery.post(postUrl, 
                                    {
                                        action: 'googleApiKeySave',
                                        apiKey:element.value
                                    }, 
                                    function(response) {
                                        console.log(response);
                                        if(response.isOk){
                                            location.reload();
                                        }else{
                                            alert(response.message);
                                        }
                                    });
                                }
                            }
                        }
                        controller=new controller();
                    </script>
                    <?php 
                        if($apiKey==NULL || $apiKey==""){
                    ?>
                    <div>
                        <span>harita pinlemesini kullanabilmeniz için google api keyinizi girmelisiniz.</span>
                    </div>
                    <?php 
                    }?>
                    <div>
                        <span>Google ApiKey:</span>
                        <input id="apiKey" placeholder="google api keyi giriniz" type="textbox" value="<?php echo $apiKey; ?>"/>
                    </div>
                    
                    <?php 
                        if($apiKey==NULL || $apiKey==""){
                    ?>
                    <div>
                        <input type="button" value="Kaydet" onclick="controller.save()" />
                    </div>
                    <?php 
                    }?>
                <?php
            if($apiKey!=NULL && $apiKey!=""){
                ?>
                    
                    <script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo $apiKey; ?>&callback=controller.mapInit">
                    </script>
                    <script>
                        var controller=function(){
                            var pin=null;
                            function getPostModel(){
                                return {
                                    action:"locationSave",
                                    apiKey:document.getElementById("apiKey").value,
                                    zoom:document.getElementById("map_zoom_option").value,
                                    lat:location.lat,
                                    lng:location.lng
                                };
                            }
                            function clearMapPin(map){
                                if(pin!=null){
                                    pin=null;
                                }
                            }
                            function setPin(position,map){
                                clearMapPin();
                                pin=new google.maps.Marker({
                                    position:position,
                                    map:map
                                }).setMap(map);
                            }
                            this.mapInit=function(){
                                var map = new google.maps.Map(document.getElementById('map'), {
                                    zoom: 4,
                                    center: {lat: <?php echo $location[0] ?>, lng:  <?php echo $location[1] ?> }
                                });
                                setPin();
                                map.addListener('click', function(e) {
                                    location=e.latLng;
                                    console.log(location);
                                    setPin(e.latLng, map);
                                });
                            }
                            this.save=function(){
                                jQuery.post(postUrl,getPostModel(),
                                function(response) {
                                    console.log(response);
                                    if(response.isOk){
                                        location.reload();
                                    }else{
                                        alert(response.message);
                                    }
                                });
                            }
                        }
                        controller=new controller();
                    </script>
                    <div>
                        <span>Harita / Adres Bilgileri</span>
                        <div>
                            <span>Yaklaşım Oranı</span>
                            <div>
                                <select id="map_zoom_option">
                                    <?php
                                        for($i=1;$i<5;$i++){
                                            ?>
                                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                            <?php
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div>
                            <div style="height: 259px;width: 500px;" id="map"></div>
                        </div>
                    </div>
                    <div>
                        <input type="button" value="Güncelle" onclick="controller.save()" />
                    </div>
                <?php
            }
        ?>
        </div>
    </div>
    <?php
}

?>