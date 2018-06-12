<?php

$http = new swoole_http_server("0.0.0.0", 80);

$http->on("start", function ($server) {
    echo "Server is started\n";
});

$http->on("request", function ($request, $response) {
    $emails = json_decode($request->rawcontent(), true);

    $result = [];
    foreach ($emails as $email) {
        $valid = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        $result[$email] = $valid;
    }

    $response->header("Content-Type", "application/json");
    $response->end(json_encode($result));
});

$http->start();
