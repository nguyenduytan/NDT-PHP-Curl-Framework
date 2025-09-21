<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use ndtan\Curl\Security\CircuitBreaker;

final class CircuitBreakerTest extends TestCase{
    public function testOpenHalfOpenClose(){
        $cb = new CircuitBreaker(failureThreshold:2, coolDown:1, halfOpen:1);
        $key = 'svc';

        $this->assertTrue($cb->allow($key));
        $cb->onFailure($key);
        $cb->onFailure($key);
        $this->assertTrue($cb->isOpen($key));
        $this->assertFalse($cb->allow($key));

        sleep(1);
        $this->assertTrue($cb->allow($key)); // half-open
        $cb->onSuccess($key);
        $this->assertFalse($cb->isOpen($key));
    }
}
