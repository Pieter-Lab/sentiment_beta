<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
//Pull in Composer php autoloader
require __DIR__ . '/vendor/autoload.php';
//--------------------------------------------------------------------------------------------------------
//Library
require __DIR__.'/class_library.php';
//--------------------------------------------------------------------------------------------------------
//Set the Access ID
$API_ACCESS_ID = '8bfa9763-f3d4-4fa2-b21d-b3fb0dc60c8a';
/**
 * Stock Quote - https://clientapi.gcs-web.com/data/8bfa9763-f3d4-4fa2-b21d-b3fb0dc60c8a/Quotes
 */
LIB::speak('<b>Stock Quote</b> - https://clientapi.gcs-web.com/data/'.$API_ACCESS_ID.'/Quotes');
$res = LIB::calCurl('https://clientapi.gcs-web.com/data/'.$API_ACCESS_ID.'/Quotes');
$res = json_decode($res);
echo "<p>";
LIB::speak('<b>API RESPONSE:</b>');
echo "</p>";
LIB::printer($res);
echo "<hr />";
/**
 * Events - https://clientapi.gcs-web.com/data/8bfa9763-f3d4-4fa2-b21d-b3fb0dc60c8a/Events
 */
LIB::speak('<b>Events</b> - https://clientapi.gcs-web.com/data/'.$API_ACCESS_ID.'/Events');
$res = LIB::calCurl('https://clientapi.gcs-web.com/data/'.$API_ACCESS_ID.'/Events');
$res = json_decode($res);
echo "<p>";
LIB::speak('<b>API RESPONSE:</b>');
echo "</p>";
LIB::printer($res);
echo "<hr />";
/**
 * News - https://clientapi.gcs-web.com/data/8bfa9763-f3d4-4fa2-b21d-b3fb0dc60c8a/News
 */
LIB::speak('<b>News</b> - https://clientapi.gcs-web.com/data/'.$API_ACCESS_ID.'/News');
$res = LIB::calCurl('https://clientapi.gcs-web.com/data/'.$API_ACCESS_ID.'/News');
$res = json_decode($res);
echo "<p>";
LIB::speak('<b>API RESPONSE:</b>');
echo "</p>";
LIB::printer($res);
echo "<hr />";
/**
 * SEC Filings - https://clientapi.gcs-web.com/data/8bfa9763-f3d4-4fa2-b21d-b3fb0dc60c8a/filings
 */
LIB::speak('<b>SEC Filings</b> - https://clientapi.gcs-web.com/data/'.$API_ACCESS_ID.'/filings');
$res = LIB::calCurl('https://clientapi.gcs-web.com/data/'.$API_ACCESS_ID.'/filings');
$res = json_decode($res);
echo "<p>";
LIB::speak('<b>API RESPONSE:</b>');
echo "</p>";
LIB::printer($res);
echo "<hr />";
/**
 * Stock Lookup by Day - https://clientapi.gcs-web.com/data/8bfa9763-f3d4-4fa2-b21d-b3fb0dc60c8a/Quotes/lookup?date=yyyy-mm-dd&symbol=[insert ticker]
 */
LIB::speak('<b>Stock Lookup by Day</b> - https://clientapi.gcs-web.com/data/'.$API_ACCESS_ID.'/Quotes/lookup?date=2018-05-01&symbol=FOO');
$res = LIB::calCurl('https://clientapi.gcs-web.com/data/'.$API_ACCESS_ID.'/Quotes/lookup?date=2018-05-01&symbol=FOO');
$res = json_decode($res);
echo "<p>";
LIB::speak('<b>API RESPONSE:</b>');
echo "</p>";
LIB::printer($res);
echo "<hr />";
/**
 * Stock Lookup by Week - https://clientapi.gcs-web.com/data/8bfa9763-f3d4-4fa2-b21d-b3fb0dc60c8a/quotes/lookup/weekof?date=yyyy-mm-dd&symbol=[insert ticker]
 */
LIB::speak('<b>Stock Lookup by Week</b> - https://clientapi.gcs-web.com/data/'.$API_ACCESS_ID.'/quotes/lookup/weekof?date=2018-05-01&symbol=FOO');
$res = LIB::calCurl('https://clientapi.gcs-web.com/data/'.$API_ACCESS_ID.'/quotes/lookup/weekof?date=2018-05-01&symbol=FOO');
$res = json_decode($res);
echo "<p>";
LIB::speak('<b>API RESPONSE:</b>');
echo "</p>";
LIB::printer($res);
echo "<hr />";
/**
 * People - https://clientapi.gcs-web.com/data/8bfa9763-f3d4-4fa2-b21d-b3fb0dc60c8a/people (people/ID for specific person)
 */
LIB::speak('<b>People</b> - https://clientapi.gcs-web.com/data/'.$API_ACCESS_ID.'/people');
$res = LIB::calCurl('https://clientapi.gcs-web.com/data/'.$API_ACCESS_ID.'/people');
$res = json_decode($res);
echo "<p>";
LIB::speak('<b>API RESPONSE:</b>');
echo "</p>";
LIB::printer($res);
echo "<hr />";
/**
 * Assets - https://clientapi.gcs-web.com/data/8bfa9763-f3d4-4fa2-b21d-b3fb0dc60c8a/assets (assets/ID for specific asset)
 */
LIB::speak('<b>Assets</b> - https://clientapi.gcs-web.com/data/'.$API_ACCESS_ID.'/assets');
$res = LIB::calCurl('https://clientapi.gcs-web.com/data/'.$API_ACCESS_ID.'/assets');
$res = json_decode($res);
echo "<p>";
LIB::speak('<b>API RESPONSE:</b>');
echo "</p>";
LIB::printer($res);
echo "<hr />";
