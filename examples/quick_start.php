<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use ndtan\Curl\Http\Http;

$res = Http::to('https://httpbin.org/get')
    ->asJson()->expectJson()
    ->get();

echo $res->status() . PHP_EOL;
print_r($res->json());
