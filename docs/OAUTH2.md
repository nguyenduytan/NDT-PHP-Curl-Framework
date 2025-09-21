# OAuth2 Middleware (Preview v0.2)

Attach Bearer token & auto-refresh on 401 or near-expiry.

```php
use ndtan\Curl\Auth\OAuth2Middleware;

$oauth = new OAuth2Middleware(
    getToken: fn() => ['access_token' => load_token(), 'expires_at' => load_expiry()],
    refresh:  fn() => refresh_and_store_token(),
    expirySkew: 30
);

$headers = [];
$oauth->attach($headers);

$res = Http::to('https://api.example.com')
    ->headers($headers)
    ->asJson()->get();
```
