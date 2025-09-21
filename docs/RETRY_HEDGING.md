# Retry & Hedged requests
- Retry uses decorrelated jitter backoff.
- Hedging: API fields ready. For v0.2, implement curl_multi to send duplicate after N ms if no TTFB.
