<?php
declare(strict_types=1);
namespace ndtan\Curl\Http;
use ndtan\Curl\Core\RequestBuilder;
final class Http{
    public static function to(string $url): RequestBuilder { return new RequestBuilder($url); }
    public static function baseUrl(string $base): RequestBuilder { return (new RequestBuilder(''))->baseUrl($base); }
    public static function file(string $path): \CURLFile { return new \CURLFile($path); }
    public static function pool(int $concurrency=8): array { return []; } // placeholder
}
