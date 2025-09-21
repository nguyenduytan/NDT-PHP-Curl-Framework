# BODY MODES (JSON, Form, Multipart, Raw)

- `asJson()` — request **and** response are JSON (sets `Accept: application/json`, serializes body).
- `sendJson($data)` — request JSON only.
- `expectJson()` — response JSON only.
- `data($mixed)` — unified entry for string/array/resource/generator.

```php
Http::to('/v1')->asJson()->data(['a'=>1])->post();
Http::to('/submit')->asForm()->data(['a'=>1,'b'=>2])->post();
Http::to('/upload')->multipart(['file'=>Http::file('/path/img.png')])->post();
Http::to('/raw')->data(fopen('/path/1GB.bin','rb'))->put();
// JSON options
Http::to('/v1')->jsonFlags(JSON_THROW_ON_ERROR)->jsonAssoc(true);
```
