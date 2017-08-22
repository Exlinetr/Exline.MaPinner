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
$exMapinnerLocationFieldName="exMapinnerLocation";
$exMapinnerApiKeyFieldName="exMapinnerApiKey";

register_activation_hook(__FILE__, 'exMapinnerSetLocation');
function exMapinnerSetLocation( ) {
    add_option($exMapinnerLocationFieldName, '[39.4335802,26.8087294]');
}
register_deactivation_hook(__FILE__, 'exMapinnerRemoveLocation');
function exMapinnerRemoveLocation( ) {
    delete_option($exMapinnerLocationFieldName);
}

add_action('admin_menu', 'exMapinnerAddAdminMenu');
function exMapinnerAddAdminMenu()
{
    //add_menu_page("Ex-Mapiner","Ex-Mappiner","",);
     add_options_page('Ex-Mapiner','Ex-Mapiner', '8', 'Ex-Mapiner', 'exMapinnerAdminHtml');
}

function exMapinnerAdminHtml(){
    $location=$_POST["location"];
    if($location!=null){
        update_option($exMapinnerLocationFieldName,$location);
        update_option($exMapinnerApiKeyFieldName,$apiKey);
        ?>
        <div>Güncellendi</div>
        <?php
    }
    //AIzaSyAzzjRCTj5adWXm0hXZwLagi8KVkdPDWeA
    ?>
    <div>
        <h2>ex-mapinner Yönetim Sayfası</h2>
        <div>
        <?php 
            $apiKey=get_option($exMapinnerApiKeyFieldName);
            if($apiKey==NULL && $apiKey==""){
                ?>
                    <div>
                        <span>harita pinlemesini kullanabilmeniz için google api keyinizi girmelisiniz.</span>
                    </div>
                    <div>
                        <span>Google ApiKey:</span>
                        <input id="googleKey" placeholder="google api keyi giriniz" type="textbox" value="<?php ?>"/>
                    </div>
                    <div>
                        <input type="button" value="Kaydet" />
                    </div>
                <?php
            }else{
                ?>
                    
                    <script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php apiKey ?>&callback=initMap">
                    </script>
                    <script>
                        function initMap() {
                           
                        }

                        function makerPin(latLng, map) {
                            var marker = new google.maps.Marker({
                            position: latLng,
                            map: map
                            });
                            map.panTo(latLng);
                        }
                        let controller=function(){
                            function getPostModel(){
                                return {
                                    lat:documment.getElementById("")
                                }
                            }
                            function setPin(position,map){
                                var marker=new google.maps.Marker({
                                    position:position,
                                    map:map
                                });
                                map.panTo(position);
                            }
                            this.mapInit=function(){
                                var map = new google.maps.Map(document.getElementById('map'), {
                                    zoom: 4,
                                    center: {lat: -25.363882, lng: 131.044922 }
                                });

                                map.addListener('click', function(e) {
                                placeMarkerAndPanTo(e.latLng, map);
                                });
                            }
                            this.save=function(){

                            }
                        }
                    </script>
                    <div>
                        <span>Harita / Adres Bilgileri</span>
                        <div style="width=100%;height=500px">
                            <div id="map"></div>
                        </div>
                    </div>
                    <div>
                        <input type="button" value="Güncelle" />
                    </div>
                <?php
            }
        ?>
        </div>
    </div>
    <?php
}

?>