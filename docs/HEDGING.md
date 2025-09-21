# Hedged Requests (Preview v0.2)

**What**: send a duplicate request after N ms if the first one hasn't produced TTFB yet. The faster response wins; the slower is cancelled.

**Usage (v0.2+):**
```php
$res = Http::to('https://slow.example.com')
    ->hedge(150, 1)   // after 150ms, send 1 duplicate
    ->get();
```
