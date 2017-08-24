<?php 

    header('Content-Type: application/json');
    echo json_encode(save($_POST["apikey"])); 

    function save($apiKey){
        $result=array();
        $result["data"]=$apiKey;
        if($apiKey==null ||$apiKey==""){
            $result["isOk"]=false;
            $result["message"]="You must enter google api key";
        }else{
            update_option("exMapinnerApiKey",$apiKey);
            $result["isOk"]=true;
            $result["message"]="success";
        }
        return $result;
    }
    exit();

?>