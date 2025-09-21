<?php
declare(strict_types=1);

namespace ndtan\Curl\Support;

final class Paginator
{
    /**
     * Parse Link header to get next url:
     * <https://api.example.com/p?page=2>; rel="next", <...>; rel="prev"
     */
    public static function fromLinkHeader(?string $link): ?string
    {
        if (!$link) return null;
        foreach (explode(',', $link) as $p) {
            if (str_contains($p, 'rel="next"')) {
                if (preg_match('/<([^>]+)>;\s*rel="next"/', $p, $m)) {
                    return $m[1];
                }
            }
        }
        return null;
    }

    /** Common JSON continuation token helper */
    public static function token(array $json, string $key = 'next'): ?string
    {
        return isset($json[$key]) && is_string($json[$key]) ? $json[$key] : null;
    }
}
