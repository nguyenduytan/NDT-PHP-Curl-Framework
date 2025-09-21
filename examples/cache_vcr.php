<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use ndtan\Curl\Http\Http;
use ndtan\Curl\Vcr\Vcr;
use Psr\SimpleCache\CacheInterface;

// Mini in-memory cache for demo
class DemoCache implements CacheInterface {
    private array $s = [];
    public function get(string $key, mixed $default = null): mixed { return $this->s[$key] ?? $default; }
    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool { $this->s[$key]=$value; return true; }
    public function delete(string $key): bool { unset($this->s[$key]); return true; }
    public function clear(): bool { $this->s=[]; return true; }
    public function getMultiple(iterable $keys, mixed $default = null): iterable { $o=[]; foreach($keys as $k){$o[$k]=$this->get((string)$k,$default);} return $o; }
    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool { foreach($values as $k=>$v){$this->set((string)$k,$v,$ttl);} return true; }
    public function deleteMultiple(iterable $keys): bool { foreach($keys as $k){unset($this->s[(string)$k]);} return true; }
    public function has(string $key): bool { return array_key_exists($key, $this->s); }
}

$cache = new DemoCache();
$cass = sys_get_temp_dir().'/ndt_cassettes';
@mkdir($cass, 0777, true);
$vcr = new Vcr($cass, 'record'); // switch to 'replay' to reuse

$res = Http::to('https://httpbin.org/get')
    ->cache($cache, 60)
    ->vcr($vcr)
    ->get();

echo $res->status() . PHP_EOL;
echo substr($res->body(), 0, 160) . PHP_EOL;
