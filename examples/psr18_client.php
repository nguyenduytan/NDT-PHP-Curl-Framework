<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\RequestInterface;
use ndtan\Curl\Http\Http;
use ndtan\Curl\Integrations\PSR18\Client;

$factory = new class {
    public function createResponse(int $status){
        return new class($status){
            private int $s; private string $b=''; private array $h=[];
            public function __construct($s){$this->s=$s;}
            public function withHeader($k,$v){$n=clone $this; $n->h[$k]=$v; return $n;}
            public function getBody(){return new class($this){ private $p; public function __construct($p){$this->p=$p;} public function write($s){$this->p->b.=$s;} }; }
        };
    }
};

$client = new Client(
    mapper: function(RequestInterface $req) {
        $b = Http::to((string)$req->getUri())->method($req->getMethod());
        foreach ($req->getHeaders() as $k=>$vals) $b->header($k,$vals);
        $body = (string)$req->getBody(); if ($body !== '') $b->data($body);
        return $b;
    },
    responseFactory: function(\ndtan\Curl\Http\Response $res) use ($factory) {
        $psr = $factory->createResponse($res->status());
        foreach ($res->headers() as $k=>$v) $psr = $psr->withHeader($k, $v);
        $psr->getBody()->write($res->body());
        return $psr;
    }
);

// You would pass a real PSR-7 Request here; example kept minimal.
echo "PSR-18 client is wired (demo).\n";
