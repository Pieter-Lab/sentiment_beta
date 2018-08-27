<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
//Pull in Composer php autoloader
require __DIR__ . '/vendor/autoload.php';
//--------------------------------------------------------------------------------------------------------
//Library
require __DIR__.'/class_library.php';
//--------------------------------------------------------------------------------------------------------
//Set page as JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
/* Try the Connection */
try {
    //Run Lib Function
    $res = TOPIC::getTopicGlobalTotals();
    //Check
    if($res && !empty($res)){
        header('Content-Type: application/json');
        echo json_encode(['status'=>'success','content'=>$res]);
    }else{
        echo json_encode(['status'=>"fail",'reason'=>'No Sentiment Totals']);
    }
} catch (PDOException $e) { //Catch the exception
    //Kill
    echo json_encode(['status'=>"fail",'reason'=>'Connection failed: ' . $e->getMessage()]);
}
//--------------------------------------------------------------------------------------------------------
?>
