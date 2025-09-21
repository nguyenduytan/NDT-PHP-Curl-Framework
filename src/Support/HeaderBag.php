<?php
declare(strict_types=1);
namespace ndtan\Curl\Support;
final class HeaderBag{
    private array $h=[];
    public function __construct(array $headers = []){ foreach($headers as $k=>$v){ $this->set($k,$v); } }
    public function set(string $k, string|array $v): void { $this->h[$k] = $v; }
    public function add(string $k, string $v): void { $cur=$this->h[$k]??[]; $cur=is_array($cur)?$cur:[$cur]; $cur[]=$v; $this->h[$k]=$cur; }
    public function get(string $k, mixed $default=null): mixed { return $this->h[$k] ?? $default; }
    public function all(): array { return $this->h; }
    public static function toArray(array $h): array { $o=[]; foreach($h as $k=>$v){ if(is_array($v)) foreach($v as $vv){ $o[] = $k.': '.$vv; } else $o[]=$k.': '.$v; } return $o; }
}
