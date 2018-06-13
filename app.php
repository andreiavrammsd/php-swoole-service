<?php

$http = new swoole_http_server("0.0.0.0", 80);

$http->on("start", function ($server) {
    echo "Server is started\n";
});

$log = fopen("request.log", "a+");

$http->on("request", function ($request, $response) use($log) {
    $emails = json_decode($request->rawcontent(), true);

    go(function() use($log, $emails) {
        $message = sprintf(
            "Requested %d emails for validation at %s\n",
            count($emails),
            date('Y-m-d H:i:s')
        );
        co::fwrite($log, $message);
    });

    $result = [];
    foreach ($emails as $email) {
        $valid = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        $result[$email] = $valid;
    }

    $response->header("Content-Type", "application/json");
    $response->end(json_encode($result));
});

$http->start();
