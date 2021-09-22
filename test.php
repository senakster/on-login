<?php
$postdata = http_build_query(
    array(
        'user' => 'Robert',
        'id' => '1',
        'password' => '1234'
    )
);
$opts = array('http' =>
    array(
        'method' => 'POST',
        'header' => 'Content-type: application/x-www-form-urlencoded',
        'content' => $postdata
    )
);
$context = stream_context_create($opts);
$result = file_get_contents('http://localhost:8080/create', false, $context);
echo $result;