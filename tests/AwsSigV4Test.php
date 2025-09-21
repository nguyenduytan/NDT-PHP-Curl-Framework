<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use ndtan\Curl\Auth\AwsSigV4;
final class AwsSigV4Test extends TestCase{
    public function testSignsHeaders(){
        $h = AwsSigV4::sign('GET','https://s3.amazonaws.com/mybucket/mykey', [], [], '', 'us-east-1', 's3', 'AKID', 'SECRET');
        $this->assertArrayHasKey('authorization', $h);
        $this->assertStringContainsString('AWS4-HMAC-SHA256', $h['authorization']);
    }
}
