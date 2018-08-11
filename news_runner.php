<?php
//Pull in Composer php autoloader
require __DIR__ . '/vendor/autoload.php';
//--------------------------------------------------------------------------------------------------------
//Library
class LIB {
    /**
     * Returns list of Counries
     * @return array
     */
    public static function getCountries(){
//        return ['au'];
        return ['za','gb','ca','nz','ie','au'];
    }
    /**
     * Get teh List Of Industries
     * @return array
     */
    public static function getIndustries(){
//        return ['science'];
        return ['general','business','science','technology','health'];
    }
    /**
     * CHecks and inserts a News Article
     * @param bool $article
     * @param $dbh
     * @return bool
     */
    public static function insertArticle($article=false,$dbh=false,$counrty_code=false,$industry) {
        //Test
        if($article && $dbh && $counrty_code & $industry){
            //TRY:: insert article
            try{
                //CHeck if Article is already in
                $query = 'SELECT `pk_id` FROM `article` WHERE title = "'.addslashes($article->title).'"';
                $stmt = $dbh->query($query)->fetch();
                if(empty($stmt)){
                    //------------------------------------------------------------------------------------------------------
                    //CHeck for and create source
                    $fk_source_id = NULL;
                    $query = 'SELECT `pk_id` FROM `source` WHERE name = "'.addslashes(strip_tags($article->source->name)).'"';
                    $stmt = $dbh->query($query)->fetch();
                    if (empty($stmt)){
                        $dbh->exec('INSERT INTO `source`(`name`)VALUES("'.addslashes(strip_tags($article->source->name)).'")');
                        $fk_source_id = $dbh->lastInsertId();
                    }else{
                        $fk_source_id = $stmt['pk_id'];
                    }
                    //------------------------------------------------------------------------------------------------------
                    //CHeck for and create author
                    $fk_author_id = NULL;
                    $query = 'SELECT `pk_id` FROM `author` WHERE name = "'.addslashes(strip_tags($article->author)).'"';
                    $stmt = $dbh->query($query)->fetch();
                    if (empty($stmt)){
                        $dbh->exec('INSERT INTO `author`(`name`)VALUES("'.addslashes(strip_tags($article->author)).'")');
                        $fk_author_id = $dbh->lastInsertId();
                    }else{
                        $fk_author_id = $stmt['pk_id'];
                    }
                    //------------------------------------------------------------------------------------------------------
                    //Insert article
                    $query = "INSERT INTO `article`(`fk_author_id`,`fk_source_id`,`counrty_code`,`industry`,`title`,`description`,`urlToImage`,`publishedAt`,`url`)VALUES(".$fk_author_id.",".$fk_source_id.",'".$counrty_code."','".$industry."','".addslashes($article->title)."','".addslashes($article->description)."','".$article->urlToImage."','".date("Y-m-d H:i:s",strtotime($article->publishedAt))."','".$article->url."')";
                    if($dbh->exec($query)){
                        return true;
                    }
                }else{
                    return false;
                }
            }catch(Exception $e){
                exit($e->getMessage().PHP_EOL.$e->getLine());
            }
        }else{
            return false;
        }
    }
    /**
     * DO a CUrl Call to get data
     * @param bool $url
     * @return bool|mixed
     */
    public static function calCurl($url=false){
        if($url){
            try{
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $data = curl_exec($ch);
                //            $this->printer($data);
                curl_close($ch);
                //return values
                return $data;
            }catch(Exception $e){die($e->getMessage()."\r\n".$e->getTrace()."\r\n");}
        }else{
            return false;
        }
    }
    /**
     * Display Values
     * @param bool $val
     * @return bool
     */
    public static function printer($val=false){
        try{
            if($val){
                if(is_array($val) || is_object($val)){
                    print_r($val);
                }else{
                    var_dump($val)."\r\n";
                }
            }else{
                return false;
            }
        }catch(Exception $e){die($e->getMessage()."\r\n".$e->getTrace()."\r\n");}
    }
}
//--------------------------------------------------------------------------------------------------------
/* Connect to a MySQL database using driver invocation */
$dsn = 'mysql:dbname=sentiment_beta;host=127.0.0.1';
$user = 'sentiment';
$password = 'peter123';
/* Try the Connection */
try {
    //Setup new connection https://phpdelusions.net/pdo_examples/select
    $dbh = new PDO($dsn, $user, $password);
    // set the PDO error mode to exception
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //Start the Machine
    $start = true;
    while($start){
        //Loop Though Countries
        foreach(LIB::getCountries() as $counrty_code){
            //Talk
            echo PHP_EOL."********* NOW CALLING COUNTRY : ".strtoupper($counrty_code)." ************".PHP_EOL;
            //Loop through Industries per country
            foreach (LIB::getIndustries() as $industry){
                //talk
                echo PHP_EOL."###########: NOW CALLING ".strtoupper($industry)." FOR ".$counrty_code." :##############".PHP_EOL;
                //NEWS API URL for topheadlines
                $url = 'https://newsapi.org/v2/top-headlines?country='.$counrty_code.'&category='.$industry.'&apiKey=aedffb3d6d2241e8a81d701692e34680&pageSize=100';//NewsOrg
                //----------------------------------------------------------------------------------------------------------------------
                $data = LIB::calCurl($url);
                //----------------------------------------------------------------------------------------------------------------------
                //transform to array
                $newsArr = json_decode($data);
                //Test for News
                if(isset($newsArr->status)){
                    if($newsArr->status == "ok" && $newsArr->totalResults > 0){
                        foreach($newsArr->articles as $article){
                            if(LIB::insertArticle($article,$dbh,$counrty_code,$industry)){
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
    }
    //CLOSE PDO
    $dbh = NULL;
} catch (PDOException $e) { //Catch the exception
    //Kill
    die('Connection failed: ' . $e->getMessage());
}
//--------------------------------------------------------------------------------------------------------
?>