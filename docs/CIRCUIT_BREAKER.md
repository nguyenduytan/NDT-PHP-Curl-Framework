# Circuit Breaker

- Tracks failures per **service key** (usually the host).
- After `failureThreshold` is hit: **open** for `coolDown` seconds.
- After cooldown: allow a few **half‑open** probes.
- Success closes the circuit; failure re‑opens it.

```php
use ndtan\Curl\Security\CircuitBreaker;
$cb = new CircuitBreaker(failureThreshold:5, coolDown:30, halfOpen:2);
Http::to('https://api.example.com')->circuit($cb)->get();
```
