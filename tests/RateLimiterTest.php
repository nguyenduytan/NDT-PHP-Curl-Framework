<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use ndtan\Curl\Security\RateLimiter;

final class RateLimiterTest extends TestCase{
    public function testTokenBucket(){
        $cache = new _FakeCache();
        $rl = new RateLimiter($cache);
        $key = 'rl:test';

        $this->assertTrue($rl->tokenBucket($key, 2, 20));
        $this->assertTrue($rl->tokenBucket($key, 2, 20));
        $this->assertFalse($rl->tokenBucket($key, 2, 20));
        usleep(120000);
        $this->assertTrue($rl->tokenBucket($key, 2, 20));
    }
}
