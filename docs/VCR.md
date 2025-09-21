# VCR (Record/Replay)

Useful for integration tests without hitting live endpoints.

```php
$vcr = new ndtan\Curl\Vcr\Vcr(__DIR__.'/storage/cassettes', 'record'); // or 'replay'
$res = Http::to('https://api.example.com/users')->vcr($vcr)->get();
```
