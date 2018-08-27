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
                                    //Sentiment extraction
                                    if(isset($response->sentiment) && !empty($response->sentiment)){
                                        foreach ($response->sentiment as $sentiment){
                                            //Insert
                                            self::insertSentimentLinkage($item,$sentiment->label);
                                        }
                                    }
                                    //Keywords Extraction
                                    if(isset($response->keywords) && !empty($response->keywords)){
                                        //loop
                                        foreach($response->keywords as $keyword){
                                            //Insert
                                            self::insertSetTopicLinkage($item,$keyword);
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
                                //Sentiment extraction
                                if(isset($response->sentiment) && !empty($response->sentiment)){
                                    foreach ($response->sentiment as $sentiment){
                                        //Insert
                                        self::insertSentimentLinkage($item,$sentiment->label);
                                    }
                                }
                                //Keywords Extraction
                                if(isset($response->keywords) && !empty($response->keywords)){
                                    //loop
                                    foreach($response->keywords as $keyword){
                                        //Insert
                                        self::insertSetTopicLinkage($item,$keyword);
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
     * Checks and creates linkage to topics
     * @param $item
     * @param $keyword
     */
    private static function insertSetTopicLinkage($item,$keyword){
        //try
        try{
            //setter
            $topic_pk_id = false;
            //test
            $tesRes = self::dbh()->query('SELECT pk_id FROM topic WHERE name = "'.addslashes($keyword->text).'"')->fetch();
            //Test
            if(empty($tesRes)){
                //insert new topic
                self::dbh()->exec('INSERT INTO `topic`(`name`)VALUES("'.addslashes($keyword->text).'")');
                $tesRes = self::dbh()->query('SELECT pk_id FROM topic WHERE name = "'.addslashes($keyword->text).'"')->fetch();
                //get pk_id of new topic
                $topic_pk_id = $tesRes['pk_id'];
            }else{
                //get pk_id of new topic
                $topic_pk_id = $tesRes['pk_id'];
            }
            //Test
            if($topic_pk_id){
                //Check if the article pk if and the topic pk id are not in the pivot table
                $testRes = self::dbh()->query('SELECT pk_id FROM pivot_article_topic WHERE fk_article_id = '.$item['pk_id'].' AND fk_topic_id = '.$topic_pk_id)->fetch();
                //test
                if(empty($testRes)){
                    //if not then create the link
                    self::dbh()->exec('INSERT INTO `pivot_article_topic`(`fk_article_id`,`fk_topic_id`,`relevance`)VALUES('.$item['pk_id'].','.$topic_pk_id.',"'.$keyword->relevance.'")');
                    //Speak
                    self::speak("************** Article LIKAGE TO TOPIC ".$keyword->text." COMPLETED FOR ".substr($item['title'],0,20)." ***********");
                }
            }
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
            $res = self::dbh()->query('SELECT C.name, C.pk_id, C.code FROM countries AS C ORDER BY C.name ASC')->fetchAll();
            //loop
            foreach ($res as $country) {
                //Set
                $holder[$country['name']] = [];
                //ADD
                $holder[$country['name']]['name'] = $country['name'];
                $holder[$country['name']]['code'] = $country['code'];
                //Set the sentiments
                $holder[$country['name']]['sentiments'] = [];
                //Run subquery
                $sentTotalsRes = self::dbh()->query('SELECT SENT.name, COUNT(ART.pk_id) as total FROM article ART LEFT JOIN pivot_article_sentiment AS PAS on (PAS.pk_id=ART.pk_id) LEFT JOIN sentiment as SENT on (SENT.pk_id=PAS.fk_sentiment_id) WHERE ART.fk_country_id = '.$country['pk_id'].' GROUP BY PAS.fk_sentiment_id ORDER BY total DESC')->fetchAll();
                //total
                $total = 0;
                //loop
                foreach ($sentTotalsRes as $sentTotal){$total = $total + $sentTotal['total'];}
                //Break count
                $brCount = 0;
                //loop
                foreach ($sentTotalsRes as $sentTotal){
                    //LImit Sentiment Display
                    if($brCount>=3){
                        break;
                    }
                    //Add
                    $holder[$country['name']]['sentiments'][] = ['percentage'=>round(( $sentTotal['total'] / $total ) * 100),'name'=>$sentTotal['name']];
                    //add
                    $brCount++;
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

/**
 * Handles function for the tpics calls
 * Class TOPIC
 */
class TOPIC extends LIB {
    /**
     * COunt threshold
     * @var int
     */
    private static $topicThreshold = 3;

    /**
     * Get available Topics
     * @return array
     */
    public static function getTopics(){
        try{
            //Get all Topics of Highest Rank
            $query = 'SELECT PAT.fk_topic_id,COUNT(PAT.fk_article_id) as total FROM pivot_article_topic AS PAT GROUP BY PAT.fk_topic_id ORDER BY total DESC LIMIT 0, 5';
            $stmt = self::dbh()->query($query)->fetchAll();
            $topics = [];
            foreach($stmt as $item){

                $stmt2 = self::dbh()->query('SELECT `pk_id`,`name` FROM `topic` WHERE `pk_id`='.$item['fk_topic_id'].' ORDER BY `name`')->fetch();
                $topics[] = ['pk_id'=>$stmt2['pk_id'],'name'=>$stmt2['name']];
            }
            return $topics;
        }catch(Exception $e){
            exit($e->getMessage().PHP_EOL.$e->getLine());
        }
    }

    /**
     * Get the Topiccs and their sentiment toals
     * @return array
     */
    public static function getTopicGlobalTotals(){
        //Try
        try{
            //Holder
            $list = [];
            //Get Topics
            $topics = self::getTopics();
            //Loop
            foreach ($topics as $topic){
                //Add the Sentiment Count to the Topics
                $stm = self::dbh()->query('SELECT 
                                                    `SENT`.`name` AS `name`,
                                                    COUNT(`PAT`.`fk_article_id`) AS `total`,
                                                    ROUND(((COUNT(`PAT`.`fk_article_id`) / (SELECT 
                                                                    COUNT(`pivot_article_topic`.`fk_article_id`)
                                                                FROM
                                                                    `pivot_article_topic`)) * 100),
                                                            0) AS `percentage`
                                                FROM
                                                    (((`topic` `TOP`
                                                    LEFT JOIN `pivot_article_topic` `PAT` ON ((`PAT`.`fk_topic_id` = `TOP`.`pk_id`)))
                                                    LEFT JOIN `pivot_article_sentiment` `PAS` ON ((`PAS`.`fk_article_id` = `PAT`.`fk_article_id`)))
                                                    LEFT JOIN `sentiment` `SENT` ON ((`SENT`.`pk_id` = `PAS`.`fk_sentiment_id`)))
                                                WHERE
                                                    ((`SENT`.`name` IS NOT NULL)
                                                        AND (`TOP`.`pk_id` = '.$topic['pk_id'].'))
                                                GROUP BY `SENT`.`name`
                                                ORDER BY `percentage` DESC')->fetchAll();
                //Test
                if(!empty($stm)){
                    //test total articles held
                    $total = 0;
                    foreach($stm as $t){
                        $total = $total + $t['total'];
                    }
                    //Set THreshold
                    if($total >= self::$topicThreshold){
                        $list[] = [
                            'name'=>$topic['name'],
                            'sentiments' => $stm
                        ];
                    }
                }
            }
            //return found Topics
            return $list;
        }catch(Exception $e){
            exit($e->getMessage().PHP_EOL.$e->getLine());
        }
    }
}
