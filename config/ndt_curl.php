<?php
/**
 * NDT PHP cURL Framework
 * Copyright (c) 2025 Tony Nguyen
 * Author: Tony Nguyen <admin@ndtan.net>
 * License: MIT
 *
 * Global defaults for HTTP client.
 */
return [
    'base_url'   => null,
    'headers'    => ['User-Agent' => 'NDT-Curl/1.0'],
    'timeouts'   => ['connect' => 2.0, 'read' => 15.0, 'total' => 20.0, 'deadline' => null],
    'retry'      => ['times' => 3, 'strategy' => 'decorrelated', 'base_ms' => 100, 'max_ms' => 2000, 'jitter' => true],
    'hedge'      => ['after_ms' => null, 'max' => 1],
    'redirects'  => ['max' => 10, 'cross_host' => false, 'preserve_auth_on' => [307, 308]],
    'cookies'    => ['enabled' => true, 'jar' => __DIR__.'/../storage/ndt_curl.cookies.txt', 'format' => 'auto', 'persist' => true],
    'proxy'      => ['url' => null, 'no_proxy' => null],
    'tls'        => ['min' => 'TLSv1.2', 'ciphers' => null, 'pinned_pubkey' => null, 'verify_peer' => true, 'verify_host' => 2],
    'http'       => ['version' => '2', 'decompression' => true, 'keepalive' => true],
    'pool'       => ['concurrency' => 8, 'per_host' => 4],
    'log'        => ['channel' => null, 'level' => 'info', 'redact' => ['Authorization', 'X-Api-Key']],
    'trace'      => ['otel' => false],
    'cache'      => ['psr16' => null, 'default_ttl' => 60],
];
