<?php
declare(strict_types=1);
namespace ndtan\Curl\Cache;
use Psr\SimpleCache\CacheInterface;
final class HttpCache{
    public function __construct(private CacheInterface $cache, private int $defaultTtl=60){}
    private function key(string $url, array $headers): string {
        $vary = strtolower((string)($headers['Vary'] ?? ''));
        return 'httpcache:'.sha1($url+'|'+$vary);
    }
    public function get(string $url, array $reqHeaders): ?array {
        return $this->cache->get($this->key($url, $reqHeaders));
    }
    public function set(string $url, array $reqHeaders, array $res): void {
        $ttl = $this->defaultTtl;
        if (isset($res['headers']['cache-control']) && preg_match('/max-age=(\d+)/', $res['headers']['cache-control'], $m)) {
            $ttl = (int)$m[1];
        }
        $this->cache->set($this->key($url, $reqHeaders), $res, $ttl);
    }
}
