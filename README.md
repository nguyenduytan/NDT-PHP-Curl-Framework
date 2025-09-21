<div align="center">

<h1>NDT PHP cURL Framework</h1>

<p>
Chainable HTTP client &amp; PSR‑18 adapter on top of <b>ext‑curl</b> — production‑ready with
<b>decorrelated jitter retries</b>, <b>(API) hedged requests</b>, <b>circuit breaker</b>,
<b>HTTP/2</b>, <b>TLS pinning</b>, <b>large‑file streaming</b>, <b>HTTP cache</b>, <b>VCR</b>,
and <b>Laravel/Symfony</b> integrations.
</p>

<p>
  <a href="https://www.php.net/releases/8.1/en.php"><img alt="PHP" src="https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php&logoColor=white"></a>
  <a href="LICENSE.md"><img alt="License" src="https://img.shields.io/badge/License-MIT-green"></a>
  <img alt="Status" src="https://img.shields.io/badge/HTTP-Retry%20%E2%80%A2%20Circuit%20%E2%80%A2%20Cache%20%E2%80%A2%20VCR-0b5fff">
  <img alt="Type" src="https://img.shields.io/badge/Frameworks-Laravel%20%7C%20Symfony-8A2BE2">
  <img alt="PSR" src="https://img.shields.io/badge/PSR-18%20client%20%7C%207%20messages-4D908E">
</p>

<p>
  <a href="https://github.com/nguyenduytan/NDT-PHP-Curl-Framework/actions"><img alt="CI" src="https://github.com/nguyenduytan/NDT-PHP-Curl-Framework/actions/workflows/php.yml/badge.svg"></a>
</p>

</div>

---

## Table of Contents
- [Why NDT?](#why-ndt)
- [Features](#features)
- [Installation](#installation)
- [Quick Start (Plain PHP)](#quick-start-plain-php)
- [Cheat Sheet](#cheat-sheet)
- [JSON &amp; Body Modes](#json--body-modes)
- [Cookies (Netscape / JSON)](#cookies-netscape--json)
- [Custom cURL Options](#custom-curl-options)
- [Reliability: Retry · Backoff · Circuit · Hedging](#reliability-retry--backoff--circuit--hedging)
- [Large Files (Download/Upload &gt; 1GB)](#large-files-downloadupload--1gb)
- [HTTP Cache &amp; VCR](#http-cache--vcr)
- [Observability (Timings, Hooks, Otel)](#observability-timings-hooks-otel)
- [Security &amp; TLS](#security--tls)
- [Framework Integrations](#framework-integrations)
  - [Laravel](#laravel)
  - [Symfony](#symfony)
- [PSR‑18 Adapter](#psr18-adapter)
- [Config Reference](#config-reference)
- [Examples](#examples)
- [Testing](#testing)
- [License](#license)

---

## Why NDT?
- **DX first**: fluent builder like <code>Http::to(...)->asJson()->post(...)</code>.
- **Reliability**: retries (decorrelated jitter) · hedging (API) · circuit breaker · deadlines.
- **Performance**: HTTP/2, keep‑alive, happy‑eyeballs, streaming, resume, progress.
- **Security**: TLS policy, pinned public key, proxy/DoH, DNS overrides.
- **Ops friendly**: cache, VCR, hooks, timings, PSR‑3 logs, Otel‑ready.
- **Portable**: PSR‑18 client &amp; Laravel/Symfony bridges.

---

## Features
- ✅ <b>Retry with decorrelated jitter</b> — honors <code>Retry-After</code>
- ✅ <b>Circuit breaker</b> (half‑open probe) per host/service
- ✅ <b>Hedged requests (API ready)</b> — duplicate after X ms if no TTFB *(curl_multi execution lands in v0.2)*
- ✅ <b>HTTP/2</b>, keep‑alive, happy‑eyeballs (if supported by libcurl)
- ✅ <b>Auto‑decompression</b> (gzip/br/deflate), <b>Content‑Length sanity</b>
- ✅ <b>Large files</b>: streaming <b>download/upload</b>, resume, progress
- ✅ <b>Cookies</b>: <b>Netscape</b> or <b>JSON</b> jar (autodetect)
- ✅ <b>HTTP cache</b> (PSR‑16/PSR‑6), <b>VCR</b> record/replay
- ✅ <b>Security</b>: TLS policy, <b>pinned public key</b>, proxy &amp; DoH, DNS overrides
- ✅ <b>Observability</b>: timings (dns/connect/tls/ttfb/transfer/total), hooks, PSR‑3 logging, OpenTelemetry (optional)
- ✅ <b>PSR‑18</b> client + <b>Laravel/Symfony</b> bridges

> New preview features & examples are linked in docs at the end of each section.

---

## Installation
```bash
composer require ndtan/php-curl-framework
```
> Requires PHP **8.1+**, `ext-curl`, `ext-json`.

---

## Quick Start (Plain PHP)
```php
<?php
require __DIR__ . '/vendor/autoload.php';

use ndtan\Curl\Http\Http;

$res = Http::to('https://httpbin.org/get')
    ->asJson()->expectJson()   // request+response JSON
    ->retry(3)->backoff('decorrelated', 100, 2000, true)
    ->get();

if ($res->ok()) {
    $data = $res->json();
    print_r($data);
}
```
> Tip: Use <code>->deadline(microtime(true)+12.0)</code> for a hard cap across redirects/retries.

---

## Cheat Sheet
```php
Http::to($url)
  ->headers(['Authorization' => 'Bearer XXX'])
  ->query(['page'=>1])
  ->asJson()->data(['a'=>1])->post();   // JSON post

Http::to($fileUrl)->resumeFromBytes(0)->saveTo('/tmp/file')->get(); // stream download
Http::to($api)->multipart(['file'=>Http::file('/path/img.png')])->post(); // upload

Http::to($url)->retry(5)->backoff('decorrelated',200,5000,true)->get(); // retry+jitter
Http::to($url)->hedge(150,1)->get(); // hedge API (v0.2 adds curl_multi execution)

Http::to($url)->cookieJar(__DIR__.'/cookies.txt','auto',true)->get(); // Netscape/JSON
Http::to($url)->opt(CURLOPT_DOH_URL,'https://dns.google/dns-query')->get(); // DoH
```
See docs: [BODY_MODES](docs/BODY_MODES.md), [COOKIES](docs/COOKIES.md), [CURL_OPTIONS](docs/CURL_OPTIONS.md), [RETRY &amp; HEDGING](docs/RETRY_HEDGING.md).

---

## JSON &amp; Body Modes
Choose one of these patterns:

```php
// 1) Both request+response are JSON
Http::to('/v1/users')->asJson()->post(['name' => 'Tony'])->json();
// 2) Request is JSON only
Http::to('/v1/webhook')->sendJson(['event' => 'ping'])->post();
// 3) Response is JSON only
$data = Http::to('/v1/metrics')->expectJson()->get()->json();
// 4) Form / Multipart / Raw stream
Http::to('/submit')->asForm()->data(['a'=>1,'b'=>2])->post();
Http::to('/upload')->multipart(['file' => Http::file('/path/img.png')])->post();
Http::to('/raw')->data(fopen('/path/1GB.bin','rb'))->put();
// JSON options
Http::to('/v1')->jsonFlags(JSON_THROW_ON_ERROR)->jsonAssoc(true);
```
See **[docs/BODY_MODES.md](docs/BODY_MODES.md)**.

---

## Cookies (Netscape / JSON)
```php
Http::to('https://example.com')
  ->cookieJar(__DIR__.'/storage/cookies.txt', format: 'auto', persist: true)
  ->get();
```
- `auto` infers by extension (`.txt` → Netscape, `.json` → JSON) and falls back by signature.  
- Programmatic: `->cookie('name','value')`, `->cookies([...])`, `->clearCookies()`.
Read **[docs/COOKIES.md](docs/COOKIES.md)**.

---

## Custom cURL Options
```php
Http::to('https://example.com')
  ->opt(CURLOPT_SSL_VERIFYSTATUS, true)                        // OCSP stapling
  ->opt(CURLOPT_DOH_URL, 'https://dns.google/dns-query')       // DoH
  ->opts([CURLOPT_TCP_FASTOPEN => true])
  ->get();
```
See **[docs/CURL_OPTIONS.md](docs/CURL_OPTIONS.md)**.

---

## Reliability: Retry · Backoff · Circuit · Hedging
```php
use ndtan\Curl\Security\CircuitBreaker;

// Retry with decorrelated jitter
$res = Http::to('https://api.example.com/pay')
  ->asJson()->data(['amount'=>1000])
  ->retry(5)->backoff('decorrelated', baseMs: 200, maxMs: 5000, jitter: true)
  ->post();

// Circuit breaker (half-open probe)
$cb = new CircuitBreaker(failureThreshold: 5, coolDown: 30, halfOpen: 2);
$res = Http::to('https://api.users.com')
  ->circuit($cb)
  ->get();

// Hedged requests (API) – duplicate after 150ms if no TTFB
Http::to('https://slow.example.com')
  ->hedge(afterMs: 150, max: 1)
  ->get();
```
Docs: **[RETRY_HEDGING.md](docs/RETRY_HEDGING.md)**, **[CIRCUIT_BREAKER.md](docs/CIRCUIT_BREAKER.md)**.

---

## Large Files (Download/Upload &gt; 1GB)
```php
// Resume + streaming download
Http::to('https://cdn.example.com/big.iso')
  ->resumeFromBytes(filesize('/tmp/big.iso') ?: 0)
  ->saveTo('/tmp/big.iso')
  ->get();

// Streaming upload (PUT) + multipart
Http::to('https://api.example.com/put')
  ->data(fopen('/path/1GB.bin','rb'))
  ->put();

Http::to('https://api.example.com/upload')
  ->multipart(['file' => Http::file('/path/big.iso')])
  ->post();
```
See **[docs/LARGE_FILES.md](docs/LARGE_FILES.md)**.

---

## HTTP Cache &amp; VCR
```php
$cache = /* Psr\SimpleCache\CacheInterface */;
$vcr   = new ndtan\Curl\Vcr\Vcr(__DIR__.'/storage/cassettes','record');

$res = Http::to('https://api.example.com/users')
  ->cache($cache, defaultTtl: 120)   // RFC semantics (ETag, Last-Modified)
  ->vcr($vcr)                        // record/replay HTTP for tests
  ->get();
```
Docs: **[CACHE.md](docs/CACHE.md)**, **[VCR.md](docs/VCR.md)**.

---

## Observability (Timings, Hooks, Otel)
- **Timings**: `dns`, `connect`, `tls`, `ttfb`, `transfer`, `total` via `$res->timings()`  
- **Hooks**: `onRequest`, `onResponse`, `onRetry`, `onRedirect`  
- **OpenTelemetry**: instrument via hooks (optional package)  
Docs: **[OBSERVABILITY.md](docs/OBSERVABILITY.md)**.

---

## Security &amp; TLS
```php
Http::to('https://secure.example.com')
  ->tls([
    'min' => 'TLSv1.2',
    'pinned_pubkey' => 'sha256//...',
    'verify_peer' => true, 'verify_host' => 2,
  ])
  ->proxy('http://proxy.local:8080', 'localhost,127.0.0.1')
  ->get();
```
Docs: **[SECURITY.md](docs/SECURITY.md)**.

---

## Framework Integrations

### Laravel
- **Auto‑discovered** ServiceProvider: `ndtan\Curl\Integrations\Laravel\NdtCurlServiceProvider`
- Publish config:
```bash
php artisan vendor:publish --tag=config --provider="ndtan\Curl\Integrations\Laravel\NdtCurlServiceProvider"
```
Docs: **[LARAVEL.md](docs/LARAVEL.md)**

### Symfony
Minimal bundle placeholder with service wiring guide.  
Docs: **[SYMFONY.md](docs/SYMFONY.md)**

---

## PSR18 Adapter
Bring your own PSR‑7 (`nyholm/psr7` or `guzzlehttp/psr7`), map to builder, and convert back:
```php
$client = new ndtan\Curl\Integrations\PSR18\Client(
    mapper: function(Psr\Http\Message\RequestInterface $req) {
        $b = \ndtan\Curl\Http\Http::to((string)$req->getUri())->method($req->getMethod());
        foreach ($req->getHeaders() as $k=>$vals) $b->header($k,$vals);
        $body = (string)$req->getBody(); if ($body !== '') $b->data($body);
        return $b;
    },
    responseFactory: function(\ndtan\Curl\Http\Response $res) use ($psr7Factory) {
        $psr = $psr7Factory->createResponse($res->status());
        foreach ($res->headers() as $k=>$v) $psr = $psr->withHeader($k, $v);
        $psr->getBody()->write($res->body());
        return $psr;
    }
);
```
Docs: **[PSR18.md](docs/PSR18.md)**.

---

## Config Reference
Config file with header comments is provided at `config/ndt_curl.php`.  
Docs: **[CONFIG.md](docs/CONFIG.md)**.

---

## Examples
You can run the examples with plain PHP after `composer install`:

```bash
php examples/quick_start.php
php examples/json_post.php
php examples/file_upload.php
php examples/large_download.php
php examples/retry_hedge.php
php examples/cache_vcr.php
php examples/aws_sigv4.php
php examples/psr18_client.php
```

More in `examples/` folder.

---

## Testing
```bash
composer install
vendor/bin/phpunit --testdox
```
Add coverage:
```bash
XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-text
```

---

## License
MIT © Tony Nguyen
