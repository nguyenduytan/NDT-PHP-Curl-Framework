<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use ndtan\Curl\Http\Http;

$res = Http::to('https://httpbin.org/delay/2')
    ->retry(3)->backoff('decorrelated', 200, 3000, true)
    ->hedge(250, 1) // preview: duplicate after 250ms if no TTFB (v0.2+)
    ->get();

echo $res->status() . PHP_EOL;
