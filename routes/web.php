<?php

use Illuminate\Support\Facades\Route;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Carbon;
use App\User;
use App\Contest;
use App\Rank;

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
Route::get('/api/users', function(){
    $users = User::all();
    return $users->toJson();
});

Route::get('/api/contests', function(){
    $contests = Contest::all();
    return $contests->toJson();
});

Route::get('/api/ranks/{contest_id}', function($contest_id){
    $ranks = Rank::with('user')->where("contest_id", "=", $contest_id)->orderBy('rank', 'asc')->get();
    return $ranks->toJson();
});

Route::get('/api/user/{user_slug}/add', function($user_slug){
    $client = new Client();
    $response = $client->get("https://leetcode.com/$user_slug/");
    $body = $response->getBody();
    $user = new User;
    $user->user_slug = $user_slug;
    preg_match_all("/<p class=\"username\".+>\s*(.+)[\S\s]*?<\/p>/i", $body, $username);
    preg_match_all("/<span class=\"badge progress-bar-success\">\s*(\d+)\s*<\/span>[\s\S]+?Finished Contests/i", $body, $finished_contests);
    preg_match_all("/Finished Contests[\s\S]+?(\d+)\s*<\/span>[\s\S]+?Rating/i", $body, $rating);
    preg_match_all("/Rating[\s\S]+?(\d+) \/ \d+\s*<\/span>[\s\S]+?Global Ranking/i", $body, $global_rank);
    preg_match_all("/Global Ranking[\s\S]+?(\d+) \/ \d+\s*<\/span>[\s\S]+?Solved Question/i", $body, $solved_questions);
    $user->username = $username[1][0];
    $user->user_slug = $user_slug;
    $user->rating = $rating[1][0];
    $user->finished_contests = $finished_contests[1][0];
    $user->global_rank = $global_rank[1][0];
    $user->solved_questions = $solved_questions[1][0];
    $user->save();

    return $username;
});

Route::get('/api/contest/{contest_id}/rank', function($contest){
    $contest = Contest::firstOrNew(["name_id" => $contest]);

    $client = new Client();
    $response = $client->get("https://leetcode.com/contest/api/info/weekly-contest-202/");
    $contest_resp = json_decode($response->getBody(), true)["contest"];
    $contest->name = $contest_resp["title"];
    $contest->name_id = $contest_resp["title_slug"];
    $contest->start_time = Carbon::createFromTimestamp($contest_resp["start_time"]);
    $contest->duration = $contest_resp["duration"];
    $now = Carbon::now();
    if($now->lessThan($contest->start_time)){
        $contest->status = "NOT_STARTED";
    } elseif ($now->greaterThanOrEqualTo($contest->start_time) && $now->lessThanOrEqualTo($contest->start_time)){
        $contest->status = "IN_PROGRESS";
    } else {
        $contest->status = "FINISHED";
    }
    if($contest->status != "FINISHED"){
        return "Contest is not finished";
    }

    $response = $client->get("https://leetcode.com/contest/api/ranking/$contest->name_id/?pagination=1&region=global");
    $resp = json_decode($response->getBody(), true)["user_num"];
    $pages = ceil($resp/25);
    $contest->user_count = $resp;
    $contest->save();

    $usermap = [];
    $users = User::all();
    foreach($users as $user){
        $usermap[$user->user_slug] = $user;
    }
    $requests = function ($total) use ($contest) {
        for ($i = 1; $i <= $total; $i++) {
            yield new Request('GET', "https://leetcode.com/contest/api/ranking/$contest->name_id/?pagination=$i&region=global");
        }
    };

    $pool = new Pool($client, $requests($pages), [
        'concurrency' => 10,
        'fulfilled' => function (Response $response, $index) use ($usermap, $contest){
            $resp = json_decode($response->getBody(), true)["total_rank"];
            foreach($resp as $r){
                if(array_key_exists($r["user_slug"],$usermap)){
                    $user = $usermap[$r["user_slug"]];
                    $rank = Rank::firstOrNew(["contest_id"=>$contest->id, "user_id"=>$user->id]);
                    $rank->contest_id = $contest->id;
                    $rank->user_id = $user->id;
                    $rank->percentile = floor($r["rank"]/$contest->user_count*100);
                    $rank->rank = $r["rank"];
                    $rank->score = $r["score"];
                    $rank->finish_time = Carbon::createFromTimestamp($r["finish_time"]);
                    $rank->pagination = ceil($r["rank"]/25);
                    $rank->rating = $user->rating;
                    $rank->solved_questions = $user->solved_questions;
                    $rank->global_rank = $user->global_rank;
                    $rank->save();
                }
            }
        },
        'rejected' => function (RequestException $reason, $index) {
            // this is delivered each failed request
            echo $reason;
            echo "<br />";
        },
    ]);
    $promise = $pool->promise();
    $promise->wait();

    return $pages;
});

// Route::get('/async/{page}', function ($page) {
//     $time_start = microtime(true);
//     $client = new Client();
//     $requests = function ($total) {
//         for ($i = 1; $i <= $total; $i++) {
//             yield new Request('GET', "https://leetcode.com/contest/api/ranking/weekly-contest-200/?pagination=$i&region=global");
//         }
//     };
//     $pool = new Pool($client, $requests($page), [
//         'concurrency' => 10,
//         'fulfilled' => function (Response $response, $index) {
//             $resp = json_decode($response->getBody(), true)["total_rank"];
//             foreach($resp as $r){
//                 echo $r["rank"];
//                 echo " ";
//             }
//             echo "<br />";
//         },
//         'rejected' => function (RequestException $reason, $index) {
//             // this is delivered each failed request
//             echo $reason;
//             echo "<br />";
//         },
//     ]);
//     $promise = $pool->promise();
//     $promise->wait();

//     $time_end = microtime(true);
//     $time = $time_end - $time_start;
//     return "Takes $time seconds";
// });

// Route::get('/normal/{page}', function($page){
//     $time_start = microtime(true);
//     $client = new Client();
//     for ($x = 1; $x <= $page; $x++) {
//         $response = $client->get("https://leetcode.com/contest/api/ranking/weekly-contest-200/?pagination=$x&region=global");
//         $resp = json_decode($response->getBody(), true)["total_rank"];
//         foreach($resp as $r){
//             echo $r["rank"];
//             echo " ";
//         }
//         echo "<br />";
//     }

//     $time_end = microtime(true);
//     $time = $time_end - $time_start;
//     return "Takes $time seconds";
// });

Route::get('/{path?}', function(){
    return view('index');
});