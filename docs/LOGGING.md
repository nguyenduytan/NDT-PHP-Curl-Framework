# Logging & Redaction (Preview v0.2)

Mask sensitive headers/fields before logging.

```php
use ndtan\Curl\Log\Redactor;

$r = new Redactor(headers: ['authorization'=>true, 'x-api-key'=>true], fields: ['password','secret','token']);
$cleanHeaders = $r->headers($headers);
$cleanBody    = $r->body($bodyArray);
```
