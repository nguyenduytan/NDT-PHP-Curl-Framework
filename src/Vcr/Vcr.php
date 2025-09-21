<?php
declare(strict_types=1);
namespace ndtan\Curl\Vcr;
final class Vcr{
    public function __construct(private string $dir, private string $mode = 'record'){}
    private function key(string $method, string $url, string $body=''): string{
        return sha1($method.'\n'.$url.'\n'.$body);
    }
    public function find(string $method, string $url, string $body=''): ?array {
        $file = $this->dir.'/'. $this->key($method,$url,$body).'.json';
        if (is_file($file)) return json_decode((string)file_get_contents($file), true);
        return null;
    }
    public function save(string $method, string $url, array $req, array $res): void {
        if (!is_dir($this->dir)) @mkdir($this->dir, 0777, true);
        $file = $this->dir.'/'. $this->key($method,$url,$req['body'] ?? '').'.json';
        file_put_contents($file, json_encode(['request'=>$req,'response'=>$res], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    }
    public function mode(): string { return $this->mode; } // 'record' | 'replay' | 'auto'
}
