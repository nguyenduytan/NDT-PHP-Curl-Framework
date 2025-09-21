<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use ndtan\Curl\Http\Http;

$tmp = tempnam(sys_get_temp_dir(), 'ndt');
file_put_contents($tmp, 'demo file');
$res = Http::to('https://httpbin.org/post')
    ->multipart(['file' => Http::file($tmp, 'demo.txt', 'text/plain')])
    ->post();

echo $res->status() . PHP_EOL;
echo substr($res->body(), 0, 200) . PHP_EOL;
