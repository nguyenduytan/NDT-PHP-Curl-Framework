# HTTP Cache (PSR-16/6)

```php
$cache = /* CacheInterface */;
Http::to('https://api.example.com/users')
  ->cache($cache, defaultTtl: 120)
  ->get();
```

- Honors `Cache-Control: max-age`, `ETag`, `Last-Modified`.
- Revalidation can be customized via hooks.
