# Retry & Hedged Requests

## Retry with decorrelated jitter
```php
Http::to('https://api.example.com')
  ->retry(5)
  ->backoff('decorrelated', baseMs:200, maxMs:5000, jitter:true)
  ->get();
```

## Hedged requests (API)
Duplicate after N ms if no TTFB to reduce tail latency.
(curl_multi concurrent execution planned for v0.2)
