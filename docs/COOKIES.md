# Cookies (Netscape / JSON)

Use `->cookieJar($path, format: 'auto'|'netscape'|'json', persist: true)`.

- **auto**: detects by file extension (`.txt`→Netscape, `.json`→JSON) and falls back by signature.
- **Netscape**: native curl cookie engine.
- **JSON**: human‑readable; mapped to libcurl on request.

```php
Http::to('https://example.com')
  ->cookieJar(__DIR__.'/storage/cookies.txt', 'auto', true)
  ->get();
// Programmatic helpers:
->cookie('name','value'); 
->cookies(['a' => '1', 'b' => '2']);
->clearCookies();
```
