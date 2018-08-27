<?php
//Pull in Composer php autoloader
require __DIR__ . '/vendor/autoload.php';
//--------------------------------------------------------------------------------------------------------
//Library
require __DIR__.'/class_library.php';
//--------------------------------------------------------------------------------------------------------
//Set page as JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
/* Connect to a MySQL database using driver invocation */
$dsn = 'mysql:dbname=sentiment_beta;host=127.0.0.1';
$user = 'sentiment';
$password = 'peter123';
/* Try the Connection */
try {
    //Setup new connection
    $dbh = new PDO($dsn, $user, $password);
    //Get Articles
    $res = $dbh->query('SELECT IND.name FROM industries AS IND ORDER BY IND.name')->fetchAll();
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
