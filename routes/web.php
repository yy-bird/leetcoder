<?php

use Illuminate\Support\Facades\Route;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/async/{page}', function ($page) {
    $time_start = microtime(true);
    $client = new Client();
    $requests = function ($total) {
        for ($i = 1; $i <= $total; $i++) {
            yield new Request('GET', "https://leetcode.com/contest/api/ranking/weekly-contest-200/?pagination=$i&region=global");
        }
    };
    $pool = new Pool($client, $requests($page), [
        'concurrency' => 10,
        'fulfilled' => function (Response $response, $index) {
            $resp = json_decode($response->getBody(), true)["total_rank"];
            foreach($resp as $r){
                echo $r["rank"];
                echo " ";
            }
            echo "<br />";
        },
        'rejected' => function (RequestException $reason, $index) {
            // this is delivered each failed request
            echo $reason;
            echo "<br />";
        },
    ]);
    $promise = $pool->promise();
    $promise->wait();

    $time_end = microtime(true);
    $time = $time_end - $time_start;
    return "Takes $time seconds";
});

Route::get('/normal/{page}', function($page){
    $time_start = microtime(true);
    $client = new Client();
    for ($x = 1; $x <= $page; $x++) {
        $response = $client->get("https://leetcode.com/contest/api/ranking/weekly-contest-200/?pagination=$x&region=global");
        $resp = json_decode($response->getBody(), true)["total_rank"];
        foreach($resp as $r){
            echo $r["rank"];
            echo " ";
        }
        echo "<br />";
    }

    $time_end = microtime(true);
    $time = $time_end - $time_start;
    return "Takes $time seconds";
});