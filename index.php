<?php
//Pull in Composer php autoloader
require __DIR__ . '/vendor/autoload.php';
//--------------------------------------------------------------------------------------------------------
/* Connect to a MySQL database using driver invocation */
$dsn = 'mysql:dbname=sentiment_beta;host=127.0.0.1';
$user = 'sentiment';
$password = 'peter123';
/* Try the Connection */
try {
    //Setup new connection
    $dbh = new PDO($dsn, $user, $password);
    //Bring in Fluent DB Handler
    $fluent = new FluentPDO($dbh);
    //Get News Feed
    $counrty_code = "za";
    $industry = "general";
    $url = 'https://newsapi.org/v2/top-headlines?country='.$counrty_code.'&category='.$industry.'&apiKey=aedffb3d6d2241e8a81d701692e34680&pageSize=50';//NewsOrg
    $country_name = $country_title;
    //----------------------------------------------------------------------------------------------------------------------
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($ch);
    //            $this->printer($data);
    curl_close($ch);
    //----------------------------------------------------------------------------------------------------------------------
    //transform to array
    $newsArr = json_decode($data);

} catch (PDOException $e) { //Catch the exception
    //Kill
    die('Connection failed: ' . $e->getMessage());
}
//--------------------------------------------------------------------------------------------------------
?>
<html>
<head>
    <title>Sentiment Beta</title>
    <!-- Project Stylesheets -->
    <link rel="stylesheet" type="text/css" href="/sentiment-beta/stylesheets/ie.css">
    <link rel="stylesheet" type="text/css" href="/sentiment-beta/stylesheets/print.css">
    <link rel="stylesheet" type="text/css" href="/sentiment-beta/stylesheets/screen.css">
    <!-- https://fezvrasta.github.io/bootstrap-material-design/-->
    <!-- CSS -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons">
    <link rel="stylesheet" href="https://unpkg.com/bootstrap-material-design@4.1.1/dist/css/bootstrap-material-design.min.css" integrity="sha384-wXznGJNEXNG1NFsbm0ugrLFMQPWswR3lds2VeinahP8N0zJw9VWSopbjv2x7WCvX" crossorigin="anonymous">
    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/popper.js@1.12.6/dist/umd/popper.js" integrity="sha384-fA23ZRQ3G/J53mElWqVJEGJzU0sTs+SvzG8fXVWP+kJQ1lwFAOkcUOysnlKJC33U" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/bootstrap-material-design@4.1.1/dist/js/bootstrap-material-design.js" integrity="sha384-CauSuKpEqAFajSpkdjv3z9t8E7RlpJ1UP0lKM/+NdtSarroVKu069AlsRPKkFBz9" crossorigin="anonymous"></script>
</head>
<body>
<div class="bmd-layout-container bmd-drawer-f-l">
    <header class="bmd-layout-header">
        <div class="navbar navbar-light bg-faded">
            <button class="navbar-toggler" type="button" data-toggle="drawer" data-target="#dw-s1">
                <span class="sr-only">Toggle drawer</span>
                <i class="material-icons">menu</i>
            </button>
            <ul class="nav navbar-nav">
                <li class="nav-item">Sentiment Beta</li>
            </ul>
        </div>
    </header>
    <div id="dw-s1" class="bmd-layout-drawer bg-faded">
        <header>
            <a class="navbar-brand">Country</a>
        </header>
        <ul class="list-group">
            <a class="list-group-item">South Africa</a>
            <a class="list-group-item">United Kindgdom</a>
            <a class="list-group-item">Canada</a>
        </ul>
    </div>
    <main class="bmd-layout-content">
        <div class="container">
            <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
                <ol class="carousel-indicators">
                    <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
                    <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
                    <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
                </ol>
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img class="d-block w-100" src="http://2.bp.blogspot.com/-A6zpgJXpJDY/T6kxsH2kgsI/AAAAAAAACzA/lUd2PDu5oxA/s1600/zen+fox+smiling+animal+beautiful+nature+photo+photography+happiness+joy+inspiration+life.jpg" alt="First slide">
                    </div>
                    <?php
                        //Test that we have data
                        if($newsArr->status==="ok"){
                            //Loop through the articles
                            $count = 0;
                            foreach($newsArr->articles as $article){
                                echo'<div class="carousel-item">
                                        <img class="img-fluid" style="width: 100%;height: 100%;" src="'.$article->urlToImage.'" alt="'.$article->title.'">
                                        <div class="carousel-caption d-none d-md-block">
                                            <h5>'.$article->title.'</h5>
                                            <p>'.$article->description.'</p>
                                            <a href="'.$article->url.'" target="_blank" class="card-link">View Article</a>
                                        </div>
                                    </div>';
                            }
                        }
                    ?>
                </div>
                <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                </a>
            </div>
<!--            <div class="row">-->
<!--                --><?php
//                //Test that we have data
//                if($newsArr->status==="ok"){
//                    //Loop through the articles
//                    $count = 0;
//                    foreach($newsArr->articles as $article){
//                        //Extract Language
//                        $context = stream_context_create(array(
//                            'http' => array(
//                                'header'  => "Authorization: Basic " . base64_encode("583dc552-0981-4a7b-84fd-2ca58df44bc1:mRt5CacuPaio")
//                            )
//                        ));
//                        //call
//                        $data = file_get_contents('https://gateway.watsonplatform.net/natural-language-understanding/api/v1/analyze?version=2017-02-27&features=sentiment,keywords&text='.urlencode($article->description), false, $context);
//                        //Convert to JSON
//                        $json = json_decode($data);
//
//                        echo'<div class="col-sm-5">
//                                        <div class="card">
//                                          <img class="card-img-top" src="'.$article->urlToImage.'" alt="Card image cap" />
//                                          <div class="card-body">
//                                            <h5 class="card-title">'.$article->title.'</h5>
//                                            <p class="card-text">'.$article->description.'</p>
//                                          </div>
//                                          <ul class="list-group list-group-flush">
//                                            <li class="list-group-item">'.$json->sentiment->document->label.'</li>
//                                          </ul>
//                                          <div class="card-body">
//                                            <a href="'.$article->url.'" target="_blank" class="card-link">View Article</a>
//                                          </div>
//                                        </div>
//                                      </div>';
//                    }
//                }
//                ?>
<!--            </div>-->
    </main>
</div>


</body>
</html>
