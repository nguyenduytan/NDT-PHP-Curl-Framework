# Custom cURL Options

Use `->opt(CURLOPT_*, $value)` or `->opts([...])`.

```php
->opt(CURLOPT_SSL_VERIFYSTATUS, true)                  // OCSP
->opt(CURLOPT_DOH_URL, 'https://dns.google/dns-query') // DoH
->opt(CURLOPT_RESOLVE, ['api.example.com:443:1.2.3.4'])
->opt(CURLOPT_PINNEDPUBLICKEY, 'sha256//...')          // TLS pin
->opts([CURLOPT_TCP_FASTOPEN => true])
```
