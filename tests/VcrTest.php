<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use ndtan\Curl\Vcr\Vcr;

final class VcrTest extends TestCase{
    public function testRecordAndReplay(){
        $dir = sys_get_temp_dir().'/ndt_vcr';
        @mkdir($dir, 0777, true);
        $vcr = new Vcr($dir, 'record');
        $req = ['headers'=>['Accept'=>'application/json'], 'body'=>'{"ping":1}'];
        $res = ['status'=>200,'headers'=>['content-type'=>'application/json'],'body'=>'{"pong":1}'];
        $vcr->save('POST','https://api.example.com/test',$req,$res);

        $vcr2 = new Vcr($dir, 'replay');
        $hit = $vcr2->find('POST','https://api.example.com/test','{"ping":1}');
        $this->assertIsArray($hit);
        $this->assertSame(200, $hit['response']['status']);
    }
}
