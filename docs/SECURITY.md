# Security & TLS

```php
->tls([
  'min' => 'TLSv1.2',
  'ciphers' => null,
  'pinned_pubkey' => 'sha256//...',
  'verify_peer' => true,
  'verify_host' => 2,
]);
->proxy('http://proxy.local:8080', 'localhost,127.0.0.1');
```

- Consider HTTPS‑only mode in production.
- Redact sensitive headers (`Authorization`, `X-Api-Key`) in logs.
