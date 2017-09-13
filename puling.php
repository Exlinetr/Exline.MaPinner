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
    add_option("exMapinnerLocation", '[{"type":2,"lat":40.855541,"lng":29.295696}]');
}

function getLocation(){
    $location=get_option("exMapinnerLocation");
    if($location==null || $location==""){
        $location='[{"type":2,"lat":40.855541,"lng":29.295696}]';
    }
    return json_decode($location);
}

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
        $oldApiKey=get_option("exMapinnerApiKey");
        if($oldApiKey==null){
            update_option("exMapinnerApiKey",$apiKey);
        }else{
            add_option("exMapinnerApiKey",$apiKey);
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
    $location=json_encode($_POST["pins"]);
    $apiKey=$_POST["apiKey"];
    $result=array();
    if($location==null ||$location==""){
        $result["isOk"]=false;
        $result["message"]="You must select a location from the map";
    }else if($apiKey==null ||$apiKey==""){
        $result["isOk"]=false;
        $result["message"]="You must enter google api key";
    }
    else{
        update_option("exMapinnerApiKey",$apiKey);
        update_option("exMapinnerLocation",$location);
        $result["isOk"]=true;
        $result["message"]="success";
    }
    echo json_encode($result); 
    wp_die();
}

add_action("wp_ajax_getjscode","getJsCode");
function getJsCode(){
    ?>
    <link href="/wp-content/plugins/Exline.MaPinner/contents/css/web.style.css" rel="stylesheet" type="text/css"> 
    <script src="/wp-content/plugins/Exline.MaPinner/contents/js/web.js"></script> 
    <script> 
    var controller=new exMapinnerController(document.getElementById("map"),<?php echo get_option("exMapinnerLocation") ?>); 
    </script> 
    <script defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo get_option("exMapinnerApiKey"); ?>&callback=controller.mapInit"></script> 
    <div id="map"></div>
    <?php
}


function exMapinnerAdminHtml(){
    $apiKey=get_option("exMapinnerApiKey");
    $location=getLocation();
    ?>
    <link href="/wp-content/plugins/Exline.MaPinner/contents/css/admin.style.css" rel="stylesheet" type="text/css">
    <div class="context">
        <h2>Ex-MaPinner Managment</h2>
        <hr>
        <div class="content">
            <script>
                var postUrl="<?php echo admin_url('admin-ajax.php'); ?>";
            </script>
                    <script src="/wp-content/plugins/Exline.MaPinner/contents/js/admin.js"></script>
                    <script>
                        var controller=new controller();
                        controller.setPins(<?php echo json_encode($location) ?>);
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
                        <span class="field">Google ApiKey:</span>
                        <input id="apiKey" class="field" placeholder="google api keyi giriniz" type="textbox" value="<?php echo $apiKey; ?>"/>
                    </div>
                    
                    <?php 
                        if($apiKey==NULL || $apiKey==""){
                    ?>
                    <div>
                        <input type="button" value="Kaydet" onclick="controller.saveApiKey()" />
                    </div>
                    <?php 
                    }?>
        </div>
                <?php
            if($apiKey!=NULL && $apiKey!=""){
                ?>
                    <script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo $apiKey; ?>&libraries=places&callback=controller.mapInit"></script>
                    <script>
                        controller.setPins(<?php echo json_encode($location) ?>);
                    </script>
                    <div class="content">
                        <h4>Adres ve Gösterim Bilgileri</h4>
                        <!-- <div class="content">
                            <span class="field">Yaklaşım Oranı:</span>
                            <div>
                                <select class="field" id="map_zoom_option">
                                    <?php
                                        for($i=1;$i<5;$i++){
                                            ?>
                                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                            <?php
                                        }
                                    ?>
                                </select>
                            </div>
                        </div> -->
                        <div style="position: relative;" class="content">
                            <div id="map"></div>
                            <div class="mapTool" id="placeSearch">
                                <img style="float: left;" src="\wp-content\plugins\Exline.MaPinner\contents\img\search-icon.png"/>
                                <input id="placeSearchText" class="text" type="text" placeholder="Adres"/>
                            </div>
                            <div id="mapBluer" class="bluer" onclick="controller.closeGenereateWebSiteCode()"></div>
                            <div class="mapTool" id="generateWebSiteCodePopup">
                                <span class="close" onclick="controller.closeGenereateWebSiteCode()">X</span>
                                <h1>Kod</h1>
                                <hr>
                                <span id="webSiteEmbedCodeText"></span>
                            </div>
                            <div class="mapTool" id="pinOptions">
                                <span class="close" onclick="controller.closePinOptions()">X</span>
                                <ul id="pinControls" >
                                    <li onclick="controller.generateWebSiteCode(true)">
                                        <span>Web Site Yerleştirme Kodunu Al (PHP)</span>
                                    </li>
                                    <li onclick="controller.generateWebSiteCode(false)">
                                        <span>Web Site Yerleştirme Kodunu Al (JS)</span>
                                    </li>
                                    <li onclick="location.reload()">
                                        <span>Yapılan Tüm Değişikleri Geri Al</span>
                                    </li>
                                    <li onclick="controller.clearMapPin()">
                                        <span>Tüm Pinleri Kaldır</span>
                                    </li>
                                    <li class="pinBtn" onclick="controller.pinProperties()">
                                        <span>Pini Özelleştir</span>
                                    </li>
                                    <li class="pinBtn" onclick="controller.removePin()">
                                        <span>Pini Kaldır</span>
                                    </li>
                                </ul>
                                <ul id="pinProperties">
                                <span class="close back" onclick="controller.back()"><</span>
                                    <li>
                                        <input id="pinLabel" onchange="controller.pinLabelChange(this)" type="text" class="text" placeholder="Başlık" />
                                    </li>
                                    <li>
                                        <select id="pinType" onchange="controller.pinTypeChange(this)">
                                            <option value="1">Çember (Circles)</option>
                                            <option value="2">Pin</option>
                                            <option value="NaN">Özelleştir</option>
                                        </select>
                                    </li>
                                    <li class="circlesProperty">
                                        <input id="pinRadius" onchange="controller.pinRadiusChange(this)" type="number" placeholder="radius" />
                                    </li>
                                    <li>
                                        <input id="pinUrl" onchange="controller.pinUrlChange(this)" type="text" class="text" placeholder="Link" />
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="content">
                        <input style="float: right;" type="button" value="Güncelle" onclick="controller.save(this)" />
                    </div>
                <?php
            }
        ?>
    </div>
    <?php
}

?>