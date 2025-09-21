<?php
declare(strict_types=1);
namespace ndtan\Curl\Auth;
/**
 * Attach Bearer token, refresh on 401/expiry via provided callbacks.
 */
final class OAuth2Middleware {
    private $getToken;    // fn(): array{access_token:string, expires_at:int}|null
    private $refresh;     // fn(): array{access_token:string, expires_at:int}
    private int $skew;
    public function __construct(callable $getToken, callable $refresh, int $expirySkew = 30) {
        $this->getToken = $getToken; $this->refresh = $refresh; $this->skew = $expirySkew;
    }
    public function attach(array &$headers): void {
        $tok = ($this->getToken)();
        if (!$tok || !isset($tok['access_token'])) { $tok = ($this->refresh)(); }
        if (isset($tok['expires_at']) && $tok['expires_at'] - time() < $this->skew) {
            $tok = ($this->refresh)();
        }
        $headers['Authorization'] = 'Bearer '.$tok['access_token'];
    }
    public function onResponse(int $status, callable $setHeader): void {
        if ($status === 401) {
            $tok = ($this->refresh)();
            $setHeader('Authorization', 'Bearer '.$tok['access_token']);
        }
    }
}
