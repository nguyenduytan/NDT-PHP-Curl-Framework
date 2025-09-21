<?php
declare(strict_types=1);
namespace ndtan\Curl\Security;
final class RateLimiterHelpers {
    public static function fromRetryAfter(?string $retryAfter): int {
        if (!$retryAfter) return 0;
        if (ctype_digit($retryAfter)) return (int)$retryAfter;
        $ts = strtotime($retryAfter); if ($ts === false) return 0;
        $delta = $ts - time(); return $delta > 0 ? $delta : 0;
    }
}
