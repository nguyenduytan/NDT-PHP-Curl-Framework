<?php
declare(strict_types=1);
namespace ndtan\Curl\Security;
use Psr\SimpleCache\CacheInterface;
final class RateLimiter{
    public function __construct(private ?CacheInterface $cache=null){}
    public function tokenBucket(string $key, int $capacity, int $refillPerSec): bool {
        $now = microtime(true);
        $state = $this->cache?->get($key) ?? ['tokens'=>$capacity, 'ts'=>$now];
        $elapsed = max(0.0, $now - $state['ts']);
        $state['tokens'] = min($capacity, $state['tokens'] + $elapsed * $refillPerSec);
        if ($state['tokens'] >= 1) {
            $state['tokens'] -= 1;
            $state['ts'] = $now;
            $this->cache?->set($key, $state, 3600);
            return true;
        }
        $this->cache?->set($key, $state, 3600);
        return false;
    }
}
