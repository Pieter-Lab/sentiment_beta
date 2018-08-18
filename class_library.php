<?php

/**
 * Supplies Base PDO DB Obecjet
 * Class dbWrapper
 */
class dbWrapper {
    /**
     * Static DB Connect Values
     * @var string
     */
    private static $dsn = 'mysql:dbname=sentiment_beta;host=127.0.0.1';
    private static $user = 'sentiment';
    private static $password = 'peter123';
    /**
     * Get php PDO DB Object
     * @return PDO
     */
    public static function dbh()
    {
        //try to keep safe
        try{
            /* Connect to a MySQL database using driver invocation */
            //Setup new connection https://phpdelusions.net/pdo_examples/select
            $dbh = new PDO(self::$dsn, self::$user, self::$password);
            // set the PDO error mode to exception
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //return PDO Object
            return $dbh;
        }catch (PDOException $e) { //Catch the exception
            //Kill
            die('Connection failed: ' . $e->getMessage());
        }
    }
}

/**
 * Utility Work Belt
 * Class UTILS
 */
class UTILS extends dbWrapper{
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
                    echo "<pre>";
                    print_r($val);
                    echo "</pre>";
                }else{
                    var_dump($val)."\r\n";
                }
            }else{
                return false;
            }
        }catch(Exception $e){die($e->getMessage()."\r\n".$e->getTrace()."\r\n");}
    }

    /**
     * Echos a STring to a new line
     * @param bool $val
     */
    public static function speak($val=false){
        if($val){
            echo $val."\r\n";
        }
    }
}
/**
 * Handles Natural Language Calls on News Articles
 * Class sentimentWrapper
 */
class sentimentWrapper extends UTILS {
    /**
     * APIS Credentials needed to Access IBM Watson
     * @var string
     */
    private static $NLusername = 'e03fa38d-524b-4e9e-97b5-07ea9fb9350b';
    private static $NLpassword = 'GjjXXy5LB1AU';
    private static $NLurl = 'https://gateway.watsonplatform.net/natural-language-understanding/api/v1/analyze?version=2017-02-27&features=sentiment,keywords';

    private static $username = '34ddbb34-f1d3-44e3-aae2-7fec78b404dd';
    private static $password = 'jLlos0A7C1yj';
    private static $url = 'https://gateway.watsonplatform.net/tone-analyzer/api/v3/tone?version=2017-09-21';
    /**
     * Start AI Sentiment Ananlysis of Articles within the db
     */
    public static function initAISentimentAnalysis(){
        //try
        try{
            //talk
            self::speak("-------Start AI Sentiment Ananlysis of Articles within the db-------");
                //#1 Get All Articles from The article Table
                $stmt = self::dbh()->query('SELECT pk_id,title FROM article WHERE title <> "" AND sentiment_tested = 0 ORDER BY publishedAt DESC')->fetchAll();
                //Test
                if($stmt && !empty($stmt)){
                    //talk
                    self::speak("*********** ".count($stmt)." ARTICLES FOUND IN DB ***********");
                    //Loop Articles
                    foreach ($stmt as $item) {
                        //Talk
                        self::speak("*********** RETRIVEING SENTIMENT FOR '".substr($item['title'],0,20)."' ***********");
                        //get the Sentiment Index
                        $response = self::interpret($item['title']);
                        //Check if w have a response
                        if($response){
                            //Get and Set Each Sentiment Pivot Linkage
                            if(isset($response->document_tone) && !empty($response->document_tone->tones)){
                                foreach ($response->document_tone->tones as $sentiment){
                                    //Insert
                                    self::insertSentimentLinkage($item,$sentiment->tone_name);
                                }
                            }else{
                                //USe NAtural Language AI
                                $response = self::extractNaturalLanguage($item['title']);
                                //Test
                                if($response && !empty($response)){
                                    if(isset($response->sentiment) && !empty($response->sentiment)){
                                        foreach ($response->sentiment as $sentiment){
                                            //Insert
                                            self::insertSentimentLinkage($item,$sentiment->label);
                                        }
                                    }
                                }else{
                                    self::printer($response);
                                }
                            }
                        }else{
                            //USe NAtural Language AI
                            $response = self::extractNaturalLanguage($item['title']);
                            //Test
                            if($response && !empty($response)){
                                if(isset($response->sentiment) && !empty($response->sentiment)){
                                    foreach ($response->sentiment as $sentiment){
                                        //Insert
                                        self::insertSentimentLinkage($item,$sentiment->label);
                                    }
                                }
                            }else{
                                self::printer($response);
                            }
                        }
                    }
                }else{
                    //talk
                    self::speak("################## NO ARTICLES IN DB ########################");
                }
            //talk
            self::speak("-------End AI Sentiment Ananlysis of Articles within the db-------");
        }catch(Exception $e){
            exit($e->getMessage().PHP_EOL.$e->getLine());
        }
    }

    /**
     * Links Articles to their tones
     * @param $item
     * @param $sentiment
     */
    private static function insertSentimentLinkage($item,$tone_name){
        //Get an set Sentiment
        $fk_sentiment_id = self::dbh()->query('SELECT pk_id FROM sentiment WHERE name = "'.addslashes(strip_tags($tone_name)).'"')->fetch();
        if(!$fk_sentiment_id && $fk_sentiment_id !== "0"){
            //insert new Sentiment
            self::dbh()->exec('INSERT INTO `sentiment`(`name`)VALUES("'.addslashes(strip_tags($tone_name)).'")');
            $fk_sentiment_id = self::dbh()->query('SELECT pk_id FROM sentiment WHERE name = "'.addslashes(strip_tags($tone_name)).'"')->fetch();
            $fk_sentiment_id = $fk_sentiment_id['pk_id'];
        }else{
            //reset
            $fk_sentiment_id = $fk_sentiment_id['pk_id'];
        }
        //Check if the Sentiment is already linked to the Article
        $pk_linkage_id = self::dbh()->query('SELECT pk_id FROM pivot_article_sentiment WHERE fk_article_id = '.$item['pk_id'].' AND fk_sentiment_id = '.$fk_sentiment_id)->fetch();
        //test if Linkage needs to be set
        if(!$pk_linkage_id && $pk_linkage_id !== "0"){
            //Insert New Linkage
            self::dbh()->exec('INSERT INTO `pivot_article_sentiment` (`fk_article_id`,`fk_sentiment_id`)VALUES('.$item['pk_id'].','.$fk_sentiment_id.')');
            $pk_linkage_id = self::dbh()->query('SELECT pk_id FROM pivot_article_sentiment WHERE fk_article_id = '.$item['pk_id'].' AND fk_sentiment_id = '.$fk_sentiment_id)->fetch();
            $pk_linkage_id = $pk_linkage_id['pk_id'];
        }
        //Mark Article as tested
        self::dbh()->exec("UPDATE `article` SET `sentiment_tested`='1' WHERE `pk_id`=".$item['pk_id']);
        //Speak
        self::speak("************** Article LIKAGE TO SENTIMENT ".$tone_name." COMPLETED FOR ".substr($item['title'],0,20)." ***********");
    }
    /**
     * Send text which is url en coded to the IBM Toner to retrieve emotional val
     * @param $text
     * @return mixed
     */
    public static function interpret($text){
        //st the content
        $context = stream_context_create(array(
            'http' => array(
                'header'  => "Authorization: Basic " . base64_encode(self::$username.":".self::$password)
            )
        ));
        //call
        $data = @file_get_contents(self::$url.'&text='.urlencode($text), false, $context);
        //Convert to JSON
        $json = json_decode($data);
        //convert and return
        return $json;
    }
    /**
     * Gets the natural language analysis of a sting
     * @param $string
     * @return mixed
     */
    public static function extractNaturalLanguage($string){
        //try
        try{
            //Extract Language
            $context = stream_context_create(array(
                'http' => array(
                    'header'  => "Authorization: Basic " . base64_encode(self::$NLusername.":".self::$NLpassword)
                )
            ));
            //call
            $data = @file_get_contents(self::$NLurl.'&text='.urlencode(strip_tags($string)), false, $context);
            //Convert to JSON
            return json_decode($data);
        }catch(Exception $e){
            exit($e->getMessage().PHP_EOL.$e->getLine());
        }
    }
}
/**
 * Handles the Article Insertion
 */
class LIB extends UTILS {

    /**
     * Returns list of Counries
     * @return array
     */
    public static function getCountries(){
        //Get all Countries
        $query = 'SELECT `pk_id`,`code` FROM `countries` ORDER BY `name`';
        $stmt = self::dbh()->query($query)->fetchAll();
        $countries = [];
        foreach($stmt as $country){
            $countries[$country['pk_id']] = $country['code'];
        }
        return $countries;
    }

    /**
     * Get teh List Of Industries
     * @return array
     */
    public static function getIndustries(){
        //Get all Industries
        $query = 'SELECT `pk_id`,`name` FROM `industries` ORDER BY `name`';
        $stmt = self::dbh()->query($query)->fetchAll();
        $list = [];
        foreach($stmt as $item){
            $list[$item['pk_id']] = $item['name'];
        }
        return $list;
    }

    /**
     * Gets the available countries and their sentiment totals
     * @return array
     */
    public static function getCountrySentimentTotals(){
        //exception handling
        try{
            //HOlder
            $holder = [];
            //get countries
            $res = self::dbh()->query('SELECT C.name, C.pk_id FROM countries AS C ORDER BY C.name')->fetchAll();
            //loop
            foreach ($res as $country) {
                //Set
                $holder[$country['pk_id']] = [];
                //ADD
                $holder[$country['pk_id']]['name'] = $country['name'];
                //Set the sentiments
                $holder[$country['pk_id']]['sentiments'] = [];
                //Run subquery
                $sentTotalsRes = self::dbh()->query('SELECT SENT.name, COUNT(ART.pk_id) as total FROM article ART LEFT JOIN pivot_article_sentiment AS PAS on (PAS.pk_id=ART.pk_id) LEFT JOIN sentiment as SENT on (SENT.pk_id=PAS.fk_sentiment_id) WHERE ART.fk_country_id = '.$country['pk_id'].' GROUP BY PAS.fk_sentiment_id ORDER BY total DESC')->fetchAll();
                //loop
                foreach ($sentTotalsRes as $sentTotal){
                    //Add
                    $holder[$country['pk_id']]['sentiments'][$sentTotal['name']] = $sentTotal['total'];
                }
            }
            //RETURN
            return $holder;
        }catch(Exception $e){
            exit($e->getMessage().PHP_EOL.$e->getLine());
        }
    }

    /**
     * Gets and Sets a Source, returns the fk_source id
     * @param bool $name
     * @return bool|null
     */
    private static function getSetSource($name=false){
        try{
            //CHeck for and create source
            $fk_source_id = 51;
            $query = 'SELECT `pk_id` FROM `source` WHERE name = "'.addslashes(strip_tags($name)).'"';
            $stmt = self::dbh()->query($query)->fetch();
            if (empty($stmt)){
                self::dbh()->exec('INSERT INTO `source`(`name`)VALUES("'.addslashes(strip_tags($name)).'")');
                $fk_source_id = self::dbh()->query('SELECT `pk_id` FROM `source` WHERE name = "'.addslashes(strip_tags($name)).'"')->fetch();
                $fk_source_id = $fk_source_id['pk_id'];
            }else{
                $fk_source_id = $stmt['pk_id'];
            }
            return $fk_source_id;
        }catch(Exception $e){
            self::printer($query);
            exit($e->getMessage().PHP_EOL.$e->getLine());
        }
    }

    /**
     * Sets and gets an author and returns thr fk_authur_id
     * @param bool $name
     * @return bool|null
     */
    private static function getSetAuthor($name=false){
        try{
            $fk_author_id = 1;
            $query = 'SELECT `pk_id` FROM `author` WHERE name = "'.addslashes(strip_tags($name)).'"';
            $stmt = self::dbh()->query($query)->fetch();
            if (empty($stmt)){
                self::dbh()->exec('INSERT INTO `author`(`name`)VALUES("'.addslashes(strip_tags($name)).'")');
                $fk_author_id = self::dbh()->query('SELECT `pk_id` FROM `author` WHERE name = "'.addslashes(strip_tags($name)).'"')->fetch();
                $fk_author_id = $fk_author_id['pk_id'];
                if(is_null($fk_author_id)){
                    exit("a:1");
                }
            }else{
                $fk_author_id = $stmt['pk_id'];
                if(is_null($fk_author_id)){
                    exit("a:2");
                }
            }
            return $fk_author_id;
        }catch(Exception $e){
            self::printer($query);
            exit($e->getMessage().PHP_EOL.$e->getLine());
        }
    }

    /**
     * CHecks and inserts a News Article
     * @param bool $article
     * @return bool
     */
    public static function insertArticle($article=false,$fk_country_id=false,$fk_industry_id) {
        //Test
        if($article){
            //TRY:: insert article
            try{
                //CHeck if Article is already in
                $query = 'SELECT `pk_id` FROM `article` WHERE title = "'.addslashes($article->title).'" AND `fk_country_id`='.$fk_country_id." AND `fk_industry_id`=".$fk_industry_id;
                //fetch
                $stmt = self::dbh()->query($query)->fetch();
                //Test
                if(empty($stmt)){
                    //------------------------------------------------------------------------------------------------------
                    //Get the fk_source_id
                    $fk_source_id = self::getSetSource($article->source->name);
                    //------------------------------------------------------------------------------------------------------
                    //CHeck for and create author
                    $fk_author_id = self::getSetAuthor($article->author);
                    //------------------------------------------------------------------------------------------------------
                    //Insert article
                    $query = "INSERT INTO `article`(`fk_author_id`,`fk_source_id`,`fk_country_id`,`fk_industry_id`,`title`,`description`,`urlToImage`,`publishedAt`,`url`)VALUES(".$fk_author_id.",".$fk_source_id.",".$fk_country_id.",".$fk_industry_id.",'".addslashes($article->title)."','".addslashes($article->description)."','".$article->urlToImage."','".date("Y-m-d H:i:s",strtotime($article->publishedAt))."','".addslashes($article->url)."')";
                    //run and test
                    if(self::dbh()->exec($query)){
                        return true;
                    }else{
                        return false;
                    }
                }else{
                    return false;
                }
            }catch(Exception $e){
                self::printer($query);
                exit($e->getMessage().PHP_EOL.$e->getLine().PHP_EOL);
            }
        }else{
            return false;
        }
    }
}
