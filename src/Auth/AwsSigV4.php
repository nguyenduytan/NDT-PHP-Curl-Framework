<?php
declare(strict_types=1);
namespace ndtan\Curl\Auth;
/**
 * Minimal AWS Signature V4 signer for headers.
 */
final class AwsSigV4 {
    public static function sign(string $method, string $uri, array $query, array $headers, string $payload, string $region, string $service, string $accessKey, string $secretKey, ?string $sessionToken = null): array {
        $t = gmdate('Ymd\THis\Z');
        $d = substr($t,0,8);
        $u = parse_url($uri);
        $host = $u['host'] ?? '';
        $headers = array_change_key_case($headers, CASE_LOWER);
        $headers['host'] = $host;
        $headers['x-amz-date'] = $t;
        if ($sessionToken) $headers['x-amz-security-token'] = $sessionToken;

        ksort($query);
        $canonicalQuery = http_build_query($query, '', '&', PHP_QUERY_RFC3986);

        ksort($headers);
        $canonicalHeaders = '';
        $signedHeaders = [];
        foreach ($headers as $k=>$v) { $canonicalHeaders .= $k.':'.trim((string)$v)."\n"; $signedHeaders[]=$k; }
        $signedHeadersStr = implode(';', $signedHeaders);
        $path = $u['path'] ?? '/';
        $canonicalRequest = strtoupper($method)."\n".$path."\n".$canonicalQuery."\n".$canonicalHeaders."\n".$signedHeadersStr."\n".hash('sha256', $payload);

        $scope = $d."/$region/$service/aws4_request";
        $strToSign = 'AWS4-HMAC-SHA256' . "\n" . $t . "\n" . $scope . "\n" . hash('sha256', $canonicalRequest);

        $kDate = hash_hmac('sha256', $d, 'AWS4'.$secretKey, true);
        $kRegion = hash_hmac('sha256', $region, $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
        $signature = hash_hmac('sha256', $strToSign, $kSigning);

        $headers['authorization'] = 'AWS4-HMAC-SHA256 Credential='.$accessKey.'/'.$scope.', SignedHeaders='.$signedHeadersStr.', Signature='.$signature;
        return $headers;
    }
}
