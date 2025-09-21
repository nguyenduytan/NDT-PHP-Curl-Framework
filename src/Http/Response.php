<?php
declare(strict_types=1);
namespace ndtan\Curl\Http;
use ndtan\Curl\Support\Json;
final class Response{
    public function __construct(
        private int $status,
        private array $headers,
        private string $body,
        private string $effectiveUrl,
        private array $timings = []
    ){}
    public function status(): int { return $this->status; }
    public function headers(): array { return $this->headers; }
    public function header(string $name, mixed $default=null): mixed { $n=strtolower($name); return $this->headers[$n] ?? $default; }
    public function body(): string { return $this->body; }
    public function json(): mixed { return Json::decode($this->body); }
    public function ok(): bool { return $this->status >= 200 && $this->status < 300; }
    public function effectiveUrl(): string { return $this->effectiveUrl; }
    public function timings(): array { return $this->timings; }
    public function nextLink(): ?string {
        $link = $this->header('link'); if (!$link) return null;
        if (preg_match('/<([^>]+)>;\s*rel="next"/i', is_array($link)?implode(',',$link):$link, $m)) return $m[1];
        return null;
    }
}
