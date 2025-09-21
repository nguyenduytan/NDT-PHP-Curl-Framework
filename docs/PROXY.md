# Proxy Guide

This guide covers using **HTTP/HTTPS** proxies and **SOCKS5/SOCKS5h** with optional authentication.
Examples show both the **high‑level helpers** (if enabled in your build) and **low‑level cURL options** (always available).

> TL;DR  
> - Use `->proxy('scheme://user:pass@host:port')` for one‑liner.  
> - Prefer `socks5h://` to resolve DNS **at the proxy** (avoid DNS leaks).  
> - For HTTPS proxy (TLS to the proxy), add `->proxyTls([...])` or set `CURLOPT_PROXYTYPE` + proxy SSL verify options.  
> - Bypass hosts via `->noProxy('localhost,.corp')` or `CURLOPT_NOPROXY`.  
> - If your credentials contain special characters, **don’t** put them raw in the URL — use `->proxyAuth()` or URL‑encode.

---

## Supported proxy schemes

- **HTTP**: `http://proxy.local:8080`  
- **HTTPS proxy** (TLS to proxy): `https://proxy.local:8443`  
- **SOCKS5**: `socks5://proxy.local:1080` (*DNS resolved locally*)  
- **SOCKS5h**: `socks5h://proxy.local:1080` (*DNS resolved at proxy — recommended*)

If you’re connecting to an HTTPS origin **through** an HTTP proxy, cURL will use **CONNECT tunneling** automatically if needed (or you can force it).

---

## 1) HTTP proxy + Basic auth

### High‑level (helper)
```php
Http::to('https://api.example.com')
    ->proxy('http://user:pass@proxy.local:8080')
    ->get();

// or split credentials + choose scheme auth:
Http::to('https://api.example.com')
    ->proxy('http://proxy.local:8080')
    ->proxyAuth('user', 'pass', CURLAUTH_BASIC /*| CURLAUTH_DIGEST | CURLAUTH_NTLM | CURLAUTH_ANY*/)
    ->get();
```

### Low‑level (cURL)
```php
Http::to('https://api.example.com')
    ->opt(CURLOPT_PROXY, 'http://proxy.local:8080')
    ->opt(CURLOPT_PROXYUSERPWD, 'user:pass')
    ->opt(CURLOPT_PROXYAUTH, CURLAUTH_BASIC)
    ->get();
```

---

## 2) HTTPS proxy (TLS to the proxy)

Use when your proxy is fronted by TLS (the hop from client → proxy is encrypted). Ensure libcurl supports HTTPS proxy.

### High‑level (helper)
```php
Http::to('https://api.example.com')
    ->proxy('https://user:pass@proxy.local:8443')
    ->proxyTls([
        'verify_peer' => true,
        'verify_host' => 2,
        'cainfo'      => __DIR__.'/proxy-ca.pem', // CA bundle if proxy uses private CA
    ])
    ->get();
```

### Low‑level (cURL)
```php
Http::to('https://api.example.com')
    ->opt(CURLOPT_PROXY, 'https://proxy.local:8443')
    ->opt(CURLOPT_PROXYUSERPWD, 'user:pass')
    ->opt(CURLOPT_PROXYTYPE, CURLPROXY_HTTPS)
    ->opt(CURLOPT_PROXY_SSL_VERIFYPEER, true)
    ->opt(CURLOPT_PROXY_SSL_VERIFYHOST, 2)
    ->opt(CURLOPT_PROXY_CAINFO, __DIR__.'/proxy-ca.pem')
    ->get();
```

---

## 3) HTTP proxy + HTTPS origin (CONNECT tunnel)

Most common enterprise setup: go through an HTTP proxy but the destination is HTTPS. Force CONNECT tunneling if you need to be explicit.

### High‑level (helper)
```php
Http::to('https://secure.example.com')
    ->proxy('http://proxy.local:8080')
    ->httpTunnel(true) // force CONNECT
    ->get();
```

### Low‑level (cURL)
```php
Http::to('https://secure.example.com')
    ->opt(CURLOPT_PROXY, 'http://proxy.local:8080')
    ->opt(CURLOPT_HTTPPROXYTUNNEL, true)
    ->get();
```

---

## 4) SOCKS5 vs SOCKS5h

- `socks5://` — The client resolves the target hostname locally and passes an IP to the proxy.  
- `socks5h://` — The proxy resolves the hostname (**safer**, prevents local DNS leaks).

### High‑level (helper)
```php
Http::to('https://api.example.com')
    ->proxy('socks5h://user:pass@socks.local:1080')
    ->get();
```

### Low‑level (cURL)
```php
Http::to('https://api.example.com')
    ->opt(CURLOPT_PROXY, 'socks5h://socks.local:1080')
    ->opt(CURLOPT_PROXYUSERPWD, 'user:pass')
    ->opt(CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME) // or CURLPROXY_SOCKS5
    ->get();
```

---

## 5) Bypass with NO_PROXY

Avoid using the proxy for specific hosts or domains. Separate items with commas.

### High‑level (helper)
```php
Http::to('https://intranet.local')
    ->noProxy('localhost,127.0.0.1,.internal,.corp')
    ->get();
```

### Low‑level (cURL)
```php
Http::to('https://intranet.local')
    ->opt(CURLOPT_NOPROXY, 'localhost,127.0.0.1,.internal,.corp')
    ->get();
```

> Note: cURL’s `NO_PROXY` does not support CIDR. Use hostnames / suffixes.

---

## 6) Authentication methods

Choose proxy auth mechanism supported by your environment:
```php
// Helpers
->proxyAuth('user','pass', CURLAUTH_BASIC | CURLAUTH_DIGEST | CURLAUTH_NTLM | CURLAUTH_NEGOTIATE | CURLAUTH_ANY);

// Raw cURL
->opt(CURLOPT_PROXYAUTH, CURLAUTH_BASIC | CURLAUTH_NTLM /* ... */);
```
If you embed credentials in the URL, URL‑encode reserved characters (`@`, `:`, `/`, `#`, …). Safer: pass them separately via `->proxyAuth()`.

---

## 7) IPv6 proxies

Wrap IPv6 in brackets:
```php
Http::to('https://api.example.com')
    ->proxy('http://user:pass@[2001:db8::1]:8080')
    ->get();
```

---

## 8) Environment variables (optional)

If you choose to support env fallback, common variables are:
- `HTTP_PROXY`, `HTTPS_PROXY` — proxy URL per scheme
- `NO_PROXY` — comma‑separated bypass list

Check your runtime policy; the framework can read these and call `->proxy()` / `->noProxy()` accordingly.

---

## 9) Logging & redaction

Never print `Proxy-Authorization` or raw credentials. Use the built‑in Redactor (if available):
```php
$redactor = new ndtan\Curl\Log\Redactor(headers: [
    'authorization' => true,
    'proxy-authorization' => true,
], fields: ['password','secret','token']);
```

---

## 10) Troubleshooting

- **407 Proxy Authentication Required**  
  Wrong credentials or unsupported auth mechanism — try `CURLAUTH_ANY`, verify policy.

- **Could not resolve host** behind SOCKS5 (but works with SOCKS5h)  
  You used `socks5://` which resolves locally. Switch to `socks5h://` to resolve at proxy.

- **SSL errors on HTTPS proxy**  
  Provide proxy CA bundle (`->proxyTls(['cainfo' => ...])` or `CURLOPT_PROXY_CAINFO`).

- **Timeouts**  
  Check `->connectTimeout()`, `->timeout()`, and hedging/retry policies.

- **DNS leak concerns**  
  Prefer `socks5h://` or use DoH: `->opt(CURLOPT_DOH_URL, 'https://dns.google/dns-query')`.

Enable verbose for deep debugging:
```php
Http::to($url)->opt(CURLOPT_VERBOSE, true)->get();
```

---

## 11) Examples

**HTTP proxy with Basic auth:**
```php
Http::to('https://api.example.com')
  ->proxy('http://proxy.local:8080')
  ->proxyAuth('svc_user', getenv('PROXY_PASS'))
  ->get();
```

**HTTPS proxy with custom CA:**
```php
Http::to('https://api.example.com')
  ->proxy('https://proxy.local:8443')
  ->proxyTls(['cainfo' => __DIR__.'/ca/proxy-ca.pem'])
  ->get();
```

**SOCKS5h with credentials:**
```php
Http::to('https://api.example.com')
  ->proxy('socks5h://user:pass@socks.local:1080')
  ->get();
```

**Bypass internal hosts:**
```php
Http::to('https://intranet.local')
  ->noProxy('localhost,127.0.0.1,.corp,.internal')
  ->get();
```

---

## 12) Security notes

- Use **SOCKS5h** or HTTPS‑proxy to avoid leaking DNS or credentials.  
- Keep credentials out of URLs when possible; prefer API methods (`->proxyAuth()`).  
- Redact secrets in logs; rotate credentials regularly.  
- Validate proxy CA if using HTTPS proxy with a private PKI.
