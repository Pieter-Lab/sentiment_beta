<?php
//Pull in Composer php autoloader
require __DIR__ . '/vendor/autoload.php';
//--------------------------------------------------------------------------------------------------------
//Library
require __DIR__.'/class_library.php';
//--------------------------------------------------------------------------------------------------------
//Start the Sentiment Process
$start = true;
while($start){
    //Call init
    sentimentWrapper::initAISentimentAnalysis();
    //Sleep
    sleep(60);
}