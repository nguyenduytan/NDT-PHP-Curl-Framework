<?php
declare(strict_types=1);
namespace ndtan\Curl\Security;
final class Backoff{
    public static function decorrelatedJitter(int $attempt, int $baseMs=100, int $maxMs=2000): int {
        static $prev = 0;
        $randMax = max($baseMs, ($prev>0?$prev: $baseMs) * 3);
        $sleep = random_int($baseMs, $randMax);
        $sleep = min($sleep, $maxMs);
        $prev = $sleep;
        return $sleep;
    }
}
