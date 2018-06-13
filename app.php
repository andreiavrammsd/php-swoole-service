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

    $emails = array_unique($emails);
    $size = count($emails);
    $chan = new chan($size);

    foreach ($emails as $email) {
        go(function () use($email, $chan) {
            $valid = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
            $chan->push([$email => $valid]);
        });
    }

    $result = [];
    for ($i = $size; $i > 0; $i--) {
        $result = array_merge($result, $chan->pop());
    }

    $response->header("Content-Type", "application/json");
    $response->end(json_encode($result));
});

$http->start();
