<?php
declare(strict_types=1);
namespace ndtan\Curl\Support;
final class Json{
    public static int $encodeFlags = JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRESERVE_ZERO_FRACTION|JSON_THROW_ON_ERROR;
    public static bool $decodeAssoc = true;
    public static function encode(mixed $v): string { return json_encode($v, self::$encodeFlags); }
    public static function decode(string $s): mixed { return json_decode($s, self::$decodeAssoc, 512, JSON_THROW_ON_ERROR); }
}
