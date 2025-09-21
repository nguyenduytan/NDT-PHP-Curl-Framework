<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use ndtan\Curl\Security\Backoff;
final class BackoffTest extends TestCase{
    public function testDecorrelatedJitterProducesWithinRange(){
        $sleep = Backoff::decorrelatedJitter(1, 100, 2000);
        $this->assertGreaterThanOrEqual(100, $sleep);
        $this->assertLessThanOrEqual(2000, $sleep);
    }
}
