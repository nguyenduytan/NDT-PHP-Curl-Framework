# Laravel Integration

- Service provider: `ndtan\Curl\Integrations\Laravel\NdtCurlServiceProvider` (auto-discovery)
- Publish config:
```bash
php artisan vendor:publish --tag=config --provider="ndtan\Curl\Integrations\Laravel\NdtCurlServiceProvider"
```

Usage:
```php
use ndtan\Curl\Http\Http;
$data = Http::to('https://api.example.com')->asJson()->get()->json();
```
