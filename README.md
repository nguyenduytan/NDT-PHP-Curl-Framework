<div align="center">
  <h1>NDT PHP cURL Framework</h1>
  <p>Advanced PHP HTTP client powered by cURL — retries, backoff, redirects, cookies, cache, VCR, observability & more.</p>

  <p>
    <img alt="PHP" src="https://img.shields.io/badge/PHP-%5E8.1-777BB4?logo=php">
    <img alt="License" src="https://img.shields.io/badge/license-MIT-green">
    <img alt="PSR" src="https://img.shields.io/badge/PSR-7%2F16%2F18-6E9C49">
  </p>
  <p>
    <a href="https://github.com/YOUR_GH/NDT-PHP-Curl-Framework/actions"><img alt="CI" src="https://github.com/YOUR_GH/NDT-PHP-Curl-Framework/actions/workflows/ci.yml/badge.svg"></a>
  </p>
</div>

---

## Features (v0.1.0)
- Unified builder API: `->asJson()`, `->asForm()`, `->multipart()`, `->data()`
- Retries with **decorrelated jitter** backoff
- Circuit breaker (half-open probe)
- Rate limiter (token bucket)
- Cookie jar (Netscape / JSON), header helpers
- HTTP cache (PSR-16)
- VCR (record/replay) for integration tests
- Observability: basic timings & event hooks
- Security/TLS helpers & redirect policy

> Full docs: see `docs/` folder linked below.

## Quick Start
```php
use ndtan\Curl\Http\Http;

$res = Http::to('https://api.example.com/v1/users')
    ->asJson()
    ->retry(3)
    ->backoff('decorrelated', baseMs: 200, maxMs: 5000, jitter: true)
    ->get();

$data = $res->json();
```

## Cheat Sheet
- JSON request & response: `->asJson()` or separately `->sendJson()` / `->expectJson()`
- Form: `->asForm()->data(['a'=>1])`
- Multipart: `->multipart(['file' => Http::file('/path/img.png')])`
- Stream upload: `->data(fopen('/path/big.bin','rb'))`
- Resume download: `->resumeFromBytes(filesize($f)?:0)->saveTo($f)`

---

## Preview (v0.2)
The following features are available on the upcoming `v0.2` branch. See docs for usage & examples:

- **Hedged requests** — [docs/HEDGING.md](docs/HEDGING.md)
- **OAuth2 middleware** — [docs/OAUTH2.md](docs/OAUTH2.md)
- **AWS SigV4 signing** — [docs/AWS_SIGV4.md](docs/AWS_SIGV4.md)
- **Logging redaction** — [docs/LOGGING.md](docs/LOGGING.md)
- **CLI `ndt-http`** — [docs/CLI.md](docs/CLI.md)
- **HTTP/3 auto‑fallback** — [docs/HTTP3.md](docs/HTTP3.md)

For future **v0.3** utilities: [docs/ADVANCED_NEXT.md](docs/ADVANCED_NEXT.md).

---

## Documentation
- Body modes: [docs/BODY_MODES.md](docs/BODY_MODES.md)
- Cookies: [docs/COOKIES.md](docs/COOKIES.md)
- Custom cURL options: [docs/CURL_OPTIONS.md](docs/CURL_OPTIONS.md)
- Retry & hedging: [docs/RETRY_HEDGING.md](docs/RETRY_HEDGING.md)
- Circuit breaker: [docs/CIRCUIT_BREAKER.md](docs/CIRCUIT_BREAKER.md)
- HTTP cache: [docs/CACHE.md](docs/CACHE.md)
- VCR: [docs/VCR.md](docs/VCR.md)
- Observability: [docs/OBSERVABILITY.md](docs/OBSERVABILITY.md)
- Security/TLS: [docs/SECURITY.md](docs/SECURITY.md)
- PSR-18: [docs/PSR18.md](docs/PSR18.md)
- Laravel: [docs/LARAVEL.md](docs/LARAVEL.md)
- Symfony: [docs/SYMFONY.md](docs/SYMFONY.md)
- Config reference: [docs/CONFIG.md](docs/CONFIG.md)

---

## Tests & Coverage
- Run tests:
```bash
composer install
vendor/bin/phpunit --testdox
```
- Add coverage (example with Xdebug):
```bash
XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-text
```
- CI badge (replace `YOUR_GH` with your owner/org).

## License
MIT
