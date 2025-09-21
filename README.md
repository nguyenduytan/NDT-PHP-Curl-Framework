<div align="center">

<h1>NDT PHP cURL Framework</h1>

<p>
Chainable HTTP client & PSR‑18 adapter on top of <b>ext‑curl</b> — production‑ready with
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

</div>

---

## Table of Contents
- [Why NDT?](#why-ndt)
- [Features](#features)
- [Installation](#installation)
- [Quick Start (Plain PHP)](#quick-start-plain-php)
- [Cheat Sheet](#cheat-sheet)
- [JSON & Body Modes](#json--body-modes)
- [Cookies (Netscape / JSON)](#cookies-netscape--json)
- [Custom cURL Options](#custom-curl-options)
- [Reliability: Retry · Backoff · Circuit · Hedging](#reliability-retry--backoff--circuit--hedging)
- [Large Files (Download/Upload > 1GB)](#large-files-downloadupload--1gb)
- [HTTP Cache & VCR](#http-cache--vcr)
- [Observability (Timings, Hooks, Otel)](#observability-timings-hooks-otel)
- [Security & TLS](#security--tls)
- [Framework Integrations](#framework-integrations)
  - [Laravel](#laravel)
  - [Symfony](#symfony)
- [PSR‑18 Adapter](#psr18-adapter)
- [Config Reference](#config-reference)
- [Testing](#testing)
- [Roadmap](#roadmap)
- [License](#license)

---

## Why NDT?
- **DX first**: fluent builder like `Http::to(...)->asJson()->post(...)`.
- **Reliability**: retries (decorrelated jitter) · hedging (API) · circuit breaker · deadlines.
- **Performance**: HTTP/2, keep‑alive, happy‑eyeballs, streaming, resume, progress.
- **Security**: TLS policy, pinned public key, proxy/DoH, DNS overrides.
- **Ops friendly**: cache, VCR, hooks, timings, PSR‑3 logs, Otel‑ready.
- **Portable**: PSR‑18 client & Laravel/Symfony bridges.

---

## Features
- ✅ **Retry with decorrelated jitter** — honors `Retry-After`
- ✅ **Circuit breaker** (half‑open probe) per host/service
- ✅ **Hedged requests (API ready)** — duplicate after X ms if no TTFB *(curl_multi execution planned in v0.2)*
- ✅ **HTTP/2**, keep‑alive, happy‑eyeballs (if supported by libcurl)
- ✅ **Auto‑decompression** (gzip/br/deflate), **Content‑Length sanity**
- ✅ **Large files**: streaming **download/upload**, resume, progress
- ✅ **Cookies**: **Netscape** or **JSON** jar (autodetect)
- ✅ **HTTP cache** (PSR‑16/PSR‑6), **VCR** record/replay
- ✅ **Security**: TLS policy, **pinned public key**, proxy & DoH, DNS overrides
- ✅ **Observability**: timings (dns/connect/tls/ttfb/transfer/total), hooks, PSR‑3 logging, OpenTelemetry (optional)
- ✅ **PSR‑18** client + **Laravel/Symfony** bridges

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
use ndtan\Curl\Http\Http;

$res = Http::to('https://httpbin.org/get')
    ->asJson()->expectJson()   // request+response JSON
    ->retry(3)->backoff('decorrelated', 100, 2000, true)
    ->get();

if ($res->ok()) {
    $data = $res->json();
    // ...
}
```
> Tip: Use `->deadline(microtime(true)+12.0)` for a hard cap across redirects/retries.

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

---

## JSON & Body Modes
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
See **[docs/BODY_MODES.md](docs/BODY_MODES.md)** for full details.

---

## Cookies (Netscape / JSON)
Use a cookie jar file; format can be **auto**, **netscape** or **json**:
```php
Http::to('https://example.com')
  ->cookieJar(__DIR__.'/storage/cookies.txt', format: 'auto', persist: true)
  ->get();
```
- `auto` infers by extension (`.txt` → Netscape, `.json` → JSON) and falls back by signature.  
- Programmatic: `->cookie('name','value')`, `->cookies([...])`, `->clearCookies()`.
Read **[docs/COOKIES.md](docs/COOKIES.md)** for caveats.

---

## Custom cURL Options
You can pass any low‑level **CURLOPT\_*** with guardrails:
```php
Http::to('https://example.com')
  ->opt(CURLOPT_SSL_VERIFYSTATUS, true)                        // OCSP stapling
  ->opt(CURLOPT_DOH_URL, 'https://dns.google/dns-query')       // DoH
  ->opts([CURLOPT_TCP_FASTOPEN => true])
  ->get();
```
See **[docs/CURL_OPTIONS.md](docs/CURL_OPTIONS.md)** for a curated list & presets.

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
Learn more in **[docs/RETRY_HEDGING.md](docs/RETRY_HEDGING.md)** and **[docs/CIRCUIT_BREAKER.md](docs/CIRCUIT_BREAKER.md)**.

---

## Large Files (Download/Upload > 1GB)
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
More recipes in **[docs/LARGE_FILES.md](docs/LARGE_FILES.md)**.

---

## HTTP Cache & VCR
```php
$cache = /* Psr\SimpleCache\CacheInterface */;
$vcr   = new ndtan\Curl\Vcr\Vcr(__DIR__.'/storage/cassettes','record');

$res = Http::to('https://api.example.com/users')
  ->cache($cache, defaultTtl: 120)   // RFC semantics (ETag, Last-Modified)
  ->vcr($vcr)                        // record/replay HTTP for tests
  ->get();
```
Read **[docs/CACHE.md](docs/CACHE.md)** and **[docs/VCR.md](docs/VCR.md)**.

---

## Observability (Timings, Hooks, Otel)
- **Timings**: `dns`, `connect`, `tls`, `ttfb`, `transfer`, `total` via `$res->timings()`  
- **Hooks**: `onRequest`, `onResponse`, `onRetry`, `onRedirect`  
- **OpenTelemetry**: instrument via hooks (optional package)
Details & examples in **[docs/OBSERVABILITY.md](docs/OBSERVABILITY.md)**.

---

## Security & TLS
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
See **[docs/SECURITY.md](docs/SECURITY.md)** for TLS, pinning, HTTPS‑only mode, redaction, limits.

---

## Framework Integrations

### Laravel
- **Auto‑discovered** ServiceProvider: `ndtan\Curl\Integrations\Laravel\NdtCurlServiceProvider`
- Publish config:
```bash
php artisan vendor:publish --tag=config --provider="ndtan\Curl\Integrations\Laravel\NdtCurlServiceProvider"
```
- Facade‑style (via container): `app('ndt.http')` → `ndtan\Curl\Http\Http`
Examples: **[docs/LARAVEL.md](docs/LARAVEL.md)**

### Symfony
Minimal bundle placeholder with service wiring guide: **[docs/SYMFONY.md](docs/SYMFONY.md)**

---

## PSR‑18 Adapter
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
See **[docs/PSR18.md](docs/PSR18.md)** for a full example.

---

## Config Reference
Config file with header comments is provided at `config/ndt_curl.php`.  
Full reference in **[docs/CONFIG.md](docs/CONFIG.md)**.

---

## Testing
```bash
composer install
composer test
```
Includes a basic PHPUnit test; add your own integration tests with **VCR**.

---

## Roadmap
- **v0.2**: curl_multi pool + real **hedging**, OAuth2 refresh middleware, AWS SigV4 addon, richer logging/redaction, rate limiter helpers, CLI `ndt-http`, HTTP/3 auto‑fallback.
- **v0.3**: Proxy rotation, JSON schema validation helpers, pagination utilities, HAR export.

---

## License
MIT © Tony Nguyen
