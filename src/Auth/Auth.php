<?php
declare(strict_types=1);
namespace ndtan\Curl\Auth;
final class Auth{
    public static function basic(string $user, string $pass): string {
        return 'Basic '.base64_encode($user.':'.$pass);
    }
    public static function bearer(string $token): string {
        return 'Bearer '.$token;
    }
}
