<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use ndtan\Curl\Http\Http;

$res = Http::to('https://httpbin.org/post')
    ->asJson()->data(['hello' => 'world'])
    ->post();

echo $res->status() . PHP_EOL;
echo $res->body() . PHP_EOL;
