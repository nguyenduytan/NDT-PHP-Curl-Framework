<?php
declare(strict_types=1);
namespace ndtan\Curl\Log;
final class Redactor {
    /** @var array<string, true> */
    private array $headers;
    /** @var array<int, string> */
    private array $fields;
    public function __construct(array $headers = ['authorization'=>true,'x-api-key'=>true], array $fields = ['password','secret','token']) {
        $this->headers = array_change_key_case($headers, CASE_LOWER);
        $this->fields = $fields;
    }
    public function headers(array $h): array {
        $out = [];
        foreach ($h as $k=>$v) {
            $lk = strtolower((string)$k);
            if (isset($this->headers[$lk])) $out[$k] = '***';
            else $out[$k] = $v;
        }
        return $out;
    }
    public function body(mixed $body): mixed {
        if (is_array($body)) {
            $out = [];
            foreach ($body as $k=>$v) {
                if (is_string($k) && in_array(strtolower($k), $this->fields, true)) $out[$k] = '***';
                else $out[$k] = $v;
            }
            return $out;
        }
        return $body;
    }
}
