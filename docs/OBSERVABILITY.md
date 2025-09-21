# Observability

## Timings
`$res->timings()` returns: `dns`, `connect`, `tls`, `ttfb`, `transfer`, `total` (seconds).

## Hooks
```php
->on('onRequest', fn($builder) => /* inspect or log */);
->on('onResponse', fn($res) => /* metrics */);
->on('onRetry', fn($builder, $res, $attempt) => /* backoff info */);
->on('onRedirect', fn($builder, $res) => /* audit */);
```

## OpenTelemetry (optional)
Use hooks to create spans/timings. Suggested package: `open-telemetry/api`.
