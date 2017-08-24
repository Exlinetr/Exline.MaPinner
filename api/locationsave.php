<?php

    header('Content-Type: application/json');
    echo json_encode(save($_POST["location"])); 

    function save($location){
        $result=array();
        if($location==null ||$location==""){
            $result["isOk"]=false;
            $result["message"]="You must select a location from the map";
        }else{
            update_option("exMapinnerLocation",$location);
            $result["isOk"]=true;
            $result["message"]="success";
        }
        return $result;
    }

?>