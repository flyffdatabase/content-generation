<?php
$http = new swoole_http_server("0.0.0.0", 9501);

$http->set([
    'worker_num' => 4,      // The number of worker processes to start
    'task_worker_num' => 4,  // The amount of task workers to start
    'backlog' => 128,       // TCP backlog connection number
]);

$http->on("Start", function ($server) {
    echo "Swoole http server is started at http://127.0.0.1:9501\n";
});

$http->on("request", function ($request, $response) {
    $uri = substr($request->server['request_uri'], 1);
    $uri = explode("/", $uri);
    $uri = implode(" ", $uri);

    $payloadBuffer = [
        "search_type" => "fuzzy",
        "query" =>
        [
            "term" => $uri
        ],
        "from" => 0, # use together with max_results for paginated results.
        "max_results" => 20
    ];
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL,            "http://mvs-1.flyffdb.info:4080/api/items/_search" );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($ch, CURLOPT_POST,           1 );
    curl_setopt($ch, CURLOPT_POSTFIELDS,     json_encode($payloadBuffer) ); 
    curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/plain')); 
    curl_setopt($ch, CURLOPT_USERPWD, "admin:testpass"); 

    $result=curl_exec ($ch);

    $response->header("Content-Type", "application/json");
    $response->header("Access-Control-Allow-Origin", "*");
    $response->end($result);
});

$http->on("task", function ($request, $response) {
    var_dump($request);
});

$http->start();