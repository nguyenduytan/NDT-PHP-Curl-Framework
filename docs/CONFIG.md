# Config Reference

Located at `config/ndt_curl.php` with header comments (project, copyright, author).
- `timeouts`: `connect`, `read`, `total`, `deadline`
- `retry`: `times`, `strategy`, `base_ms`, `max_ms`, `jitter`
- `hedge`: `after_ms`, `max`
- `redirects`: `max`, `cross_host`, `preserve_auth_on`
- `cookies`: `enabled`, `jar`, `format`, `persist`
- `proxy`: `url`, `no_proxy`
- `tls`: `min`, `ciphers`, `pinned_pubkey`, `verify_peer`, `verify_host`
- `http`: `version`, `decompression`, `keepalive`
- `pool`: `concurrency`, `per_host`
- `cache`: `psr16`, `default_ttl`
- `log` / `trace`: logging & otel flags
