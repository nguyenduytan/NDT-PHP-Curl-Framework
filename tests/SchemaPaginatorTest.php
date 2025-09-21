<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use ndtan\Curl\Validate\Schema;
use ndtan\Curl\Support\Paginator;
final class SchemaPaginatorTest extends TestCase{
    public function testSchemaBasic(){
        $ok = Schema::validate(['a'=>1,'b'=>2], ['type'=>'object','required'=>['a']]);
        $this->assertTrue($ok);
        $bad = Schema::validate(['x'=>[]], ['type'=>'object','required'=>['y']]);
        $this->assertFalse($bad);
    }
    public function testLinkNext(){
        $next = Paginator::fromLinkHeader('<https://api.example.com/p?page=2>; rel="next", <https://api.example.com/p?page=1>; rel="prev"');
        $this->assertSame('https://api.example.com/p?page=2', $next);
    }
}
