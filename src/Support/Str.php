<?php
declare(strict_types=1);
namespace ndtan\Curl\Support;
final class Str{
    public static function startsWith(string $haystack, string $needle): bool { return strncmp($haystack,$needle,strlen($needle))===0; }
    public static function contains(string $haystack, string $needle): bool { return strpos($haystack,$needle)!==false; }
    public static function toLowerKeys(array $headers): array { $o=[]; foreach($headers as $k=>$v){ $o[strtolower($k)]=$v; } return $o; }
}
