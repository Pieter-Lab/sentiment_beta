<?php
//Pull in Composer php autoloader
require __DIR__ . '/vendor/autoload.php';
//--------------------------------------------------------------------------------------------------------
//Library
require __DIR__.'/class_library.php';
//--------------------------------------------------------------------------------------------------------
//Start the Machine
$start = true;
//Start Infinate loop
while($start){
    //Marker
    echo "\r\n"."----------------------------------------------************************************------------------------------------------------------"."\r\n";
    //Loop Though Countries
    foreach(LIB::getCountries() as $fk_country_id => $counrty_code){
        //Talk
        echo PHP_EOL."********* NOW CALLING COUNTRY : ".strtoupper($counrty_code)." ************".PHP_EOL;
        //Loop through Industries per country
        foreach (LIB::getIndustries() as $fk_industry_id => $industry){
            //talk
            echo PHP_EOL."###########: NOW CALLING ".strtoupper($industry)." FOR ".$counrty_code." :##############".PHP_EOL;
            //NEWS API URL for topheadlines
            $url = 'https://newsapi.org/v2/top-headlines?country='.$counrty_code.'&category='.$industry.'&apiKey=451ed6d47caf4d52b8e867b97a2f76ee&pageSize=100';//NewsOrg
//            $url = 'https://newsapi.org/v2/top-headlines?country='.$counrty_code.'&category='.$industry.'&apiKey=aedffb3d6d2241e8a81d701692e34680&pageSize=100';//NewsOrg
            //----------------------------------------------------------------------------------------------------------------------
            $data = LIB::calCurl($url);
            //----------------------------------------------------------------------------------------------------------------------
            //transform to array
            $newsArr = json_decode($data);
            //Test for News
//            if(isset($newsArr->status)){
            if(isset($newsArr->status)){
                if($newsArr->status == "ok" && $newsArr->totalResults > 0){
                    foreach($newsArr->articles as $article){
                        //Insert Test Article
                        if(LIB::insertArticle($article,$fk_country_id,$fk_industry_id)){
                            echo PHP_EOL."---ARTICLE INSERTED: ".$article->title.": ".$article->source->name." -----".PHP_EOL;
                        }else{
                            echo ".";
                        }
                    }
                }else{
                    LIB::printer($newsArr);
                }
            }
        }
    }
    //talk
    echo "\r\n"."----------------------------------------------************************************------------------------------------------------------"."\r\n";
    //Sleep
    sleep(60);
}
//--------------------------------------------------------------------------------------------------------
?>