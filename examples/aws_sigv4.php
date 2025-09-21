<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use ndtan\Curl\Auth\AwsSigV4;
use ndtan\Curl\Http\Http;

// Demo only; replace with real keys / URL
$headers = AwsSigV4::sign(
    'GET',
    'https://s3.amazonaws.com/',
    [], [], '',
    'us-east-1', 's3',
    'AKID', 'SECRET'
);

$res = Http::to('https://httpbin.org/headers')
    ->headers($headers)
    ->get();

echo $res->status() . PHP_EOL;
echo $res->body() . PHP_EOL;
