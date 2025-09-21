<?php
declare(strict_types=1);
namespace ndtan\Curl\Support;
final class Arr{
    public static function merge(array ...$arrays): array {
        $out = [];
        foreach ($arrays as $a) { foreach ($a as $k=>$v) { $out[$k]=$v; } }
        return $out;
    }
}
