<?php
declare(strict_types=1);

use Psr\SimpleCache\CacheInterface;
use DateInterval;

final class _FakeCache implements CacheInterface
{
    private array $s = [];

    public function get(string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $this->s) ? $this->s[$key] : $default;
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        $this->s[$key] = $value;
        return true;
    }

    public function delete(string $key): bool
    {
        unset($this->s[$key]);
        return true;
    }

    public function clear(): bool
    {
        $this->s = [];
        return true;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $out = [];
        foreach ($keys as $k) {
            $out[$k] = $this->get((string)$k, $default);
        }
        return $out;
    }

    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
    {
        foreach ($values as $k => $v) {
            $this->set((string)$k, $v, $ttl);
        }
        return true;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $k) {
            $this->delete((string)$k);
        }
        return true;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->s);
    }
}
