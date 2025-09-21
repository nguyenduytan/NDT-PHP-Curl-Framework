# HTTP/3 Auto‑Fallback (Preview v0.2)

```php
Http::to('https://example.com')
  ->http3Auto()   // tries HTTP/3 if libcurl supports; otherwise falls back
  ->get();
```
