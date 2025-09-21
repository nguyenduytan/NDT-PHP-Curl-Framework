# v0.2 / v0.3 Feature Notes

## v0.2
- Hedged request: `Http::to(...)->hedge(150,1)->get();`
- OAuth2: `new OAuth2Middleware($getToken, $refresh)` then `$mw->attach($headers)`.
- AWS SigV4: `AwsSigV4::sign(...)` -> merged headers.
- CLI: `ndt-http GET https://httpbin.org/get --json`
- HTTP/3: `->http3Auto()`

## v0.3 preview
- Proxy rotation: `ProxyPool` pick/mark/healthyList
- JSON schema helper: `Schema::validate($data,$schema)` (basic)
- Pagination: `Paginator::fromLinkHeader()` / `::token()`
- HAR: `Har::fromResponse(...)`
