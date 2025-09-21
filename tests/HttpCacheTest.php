<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use ndtan\Curl\Cache\HttpCache;

final class HttpCacheTest extends TestCase{
    public function testSetGet(){
        $cache = new _FakeCache();
        $hc = new HttpCache($cache, 60);
        $url = 'https://api.example.com/users';
        $req = ['Accept'=>'application/json'];
        $res = ['status'=>200,'headers'=>['cache-control'=>'max-age=30'],'body'=>'[]'];
        $hc->set($url,$req,$res);
        $hit = $hc->get($url,$req);
        $this->assertNotNull($hit);
        $this->assertSame('[]', $hit['body']);
    }
}
