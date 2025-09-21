<?php
declare(strict_types=1);
use Psr\SimpleCache\CacheInterface;
final class _FakeCache implements CacheInterface {
    private array $s = [];
    public function get($key, $default = null){ return $this->s[$key] ?? $default; }
    public function set($key, $value, $ttl = null){ $this->s[$key] = $value; return true; }
    public function delete($key){ unset($this->s[$key]); return true; }
    public function clear(){ $this->s = []; return true; }
    public function getMultiple($keys, $default = null){ $o=[]; foreach($keys as $k){ $o[$k]=$this->get($k,$default);} return $o; }
    public function setMultiple($values, $ttl = null){ foreach($values as $k=>$v){ $this->set($k,$v,$ttl);} return true; }
    public function deleteMultiple($keys){ foreach($keys as $k){ unset($this->s[$k]); } return true; }
    public function has($key){ return array_key_exists($key,$this->s); }
}
