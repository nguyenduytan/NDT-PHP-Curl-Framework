<?php
declare(strict_types=1);
namespace ndtan\Curl\Runner;
final class HedgeRunner {
    public static function run(callable $mkHandle, int $afterMs, int $maxDup = 1): array {
        $mh = curl_multi_init();
        $h1 = $mkHandle();
        curl_multi_add_handle($mh, $h1);
        $addedDup = 0;
        $start = microtime(true);
        $done = null;
        do {
            $status = curl_multi_exec($mh, $running);
            if ($running) curl_multi_select($mh, 0.05);
            if (!$running) break;
            if ($addedDup < $maxDup && (microtime(true)-$start) * 1000 >= $afterMs) {
                $h2 = $mkHandle();
                curl_multi_add_handle($mh, $h2);
                $addedDup++;
            }
            while ($info = curl_multi_info_read($mh)) {
                $done = $info['handle'];
                // first finished -> cancel others
                foreach ([$h1] as $h) {
                    if ($h !== $done) { curl_multi_remove_handle($mh, $h); curl_close($h); }
                }
                break 2;
            }
        } while ($running > 0);
        $winner = $done ?? $h1;
        $body = curl_multi_getcontent($winner);
        $errno = curl_errno($winner);
        $info = curl_getinfo($winner);
        curl_multi_remove_handle($mh,$winner); curl_close($winner);
        curl_multi_close($mh);
        return [$body, $info, $errno];
    }
}
