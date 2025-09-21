<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use ndtan\Curl\Security\Backoff;
final class BackoffTest extends TestCase{
    public function testDecorrelatedJitterProducesWithinRange(){
        for ($i=1; $i<=10; $i++) {
            $sleep = Backoff::decorrelatedJitter($i, 100, 2000);
            $this->assertGreaterThanOrEqual(100, $sleep);
            $this->assertLessThanOrEqual(2000, $sleep);
        }
    }
}
