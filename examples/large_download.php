<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use ndtan\Curl\Http\Http;

$target = sys_get_temp_dir() . '/ndt_big.bin';
$start = file_exists($target) ? filesize($target) : 0;

$res = Http::to('https://speed.hetzner.de/100MB.bin')
    ->resumeFromBytes($start)
    ->saveTo($target)
    ->get();

echo "Saved to: {$target} (" . filesize($target) . " bytes)\n";
