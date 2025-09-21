# NDT PHP cURL Framework

> Chainable HTTP client & PSR-18 adapter built on **ext-curl** — with **decorrelated jitter retries**, **hedged requests (API)**, **circuit breaker**, **HTTP/2**, **TLS pinning**, **large-file streaming**, **HTTP cache**, **VCR**, **logging/timings**, and **Laravel/Symfony** bridges.

## Features
- Retry with **decorrelated jitter**, respect `Retry-After`
- **Hedged requests** (API ready; curl_multi implementation planned for v0.2)
- **Circuit breaker** (half-open) per host/service
- **Happy Eyeballs** (cURL option), DoH, DNS override
- HTTP/2 multiplexing + connection reuse (pool-ready)
- Auto-decompression (gzip/br), **Content-Length sanity**
- Observability: timings (dns/connect/tls/ttfb/total), hooks
- Optional **OpenTelemetry** spans (hooks)
- Event hooks: `onRequest`, `onResponse`, `onRetry`, `onRedirect`
- HTTP cache (PSR-16) — respect Cache-Control/ETag/Last-Modified
- **VCR** record/replay for tests
- Auth helpers: Basic/Bearer, OAuth2 refresh hook (via hooks), (addon) AWS SigV4
- Redirect policy (cross-host whitelist, preserve auth on 307/308)
- Response schema validation (optional with `opis/json-schema`)
- Pagination helper (Link header)
- Proxy rotation & **rate limiter** per host/path (PSR-16)
- Large files: streaming download/upload, resume, progress, checksum

## Install
```bash
composer require ndtan/php-curl-framework
```

## Quick Start
```php
use ndtan\Curl\Http\Http;

$res = Http::to('https://httpbin.org/get')
  ->asJson()->expectJson()
  ->retry(3)->backoff('decorrelated', 100, 2000, true)
  ->get();

$data = $res->json();
```

## Large Download (resume + saveTo)
```php
Http::to('https://example.com/big.iso')
  ->resumeFromBytes(filesize('/tmp/big.iso') ?: 0)
  ->saveTo('/tmp/big.iso')
  ->get();
```

## Circuit
```php
use ndtan\Curl\Security\CircuitBreaker;

$cb = new CircuitBreaker(failureThreshold:5, coolDown:30, halfOpen:2);

$res = Http::to('https://api.example.com/pay')
  ->asJson()->data(['amount'=>1000])
  ->circuit($cb)
  ->post();
```

## Cache (PSR-16) & VCR
```php
$cache = /* CacheInterface */; 
$vcr   = new ndtan\Curl\Vcr\Vcr(__DIR__.'/storage/cassettes','record');

$res = Http::to('https://api.example.com/users')
  ->cache($cache, defaultTtl:120)
  ->vcr($vcr)
  ->get();
```

## Laravel
```bash
php artisan vendor:publish --tag=config --provider="ndtan\Curl\Integrations\Laravel\NdtCurlServiceProvider"
```

## Notes
- PSR-18 adapter provided; bring your own PSR-7 implementation (Nyholm/Guzzle).
- OpenTelemetry / JSON Schema / Cache are optional via `suggest` packages.

## License
MIT © Tony Nguyen
