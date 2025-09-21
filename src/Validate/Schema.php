<?php
declare(strict_types=1);

namespace ndtan\Curl\Validate;

final class Schema
{
    /**
     * Mini JSON schema checker (type + required)
     * $schema example: ['type'=>'object','required'=>['a']]
     */
    public static function validate(mixed $data, array $schema): bool
    {
        $type = $schema['type'] ?? null;
        if ($type) {
            $ok = match ($type) {
                'object'  => is_array($data) && array_keys($data) !== range(0, count($data) - 1),
                'array'   => is_array($data) && array_keys($data) === range(0, count($data) - 1),
                'string'  => is_string($data),
                'number'  => is_int($data) || is_float($data),
                'integer' => is_int($data),
                'boolean' => is_bool($data),
                'null'    => is_null($data),
                default   => true
            };
            if (!$ok) return false;
        }

        if (($schema['type'] ?? null) === 'object' && isset($schema['required']) && is_array($schema['required'])) {
            foreach ($schema['required'] as $r) {
                if (!is_array($data) || !array_key_exists($r, $data)) return false;
            }
        }
        return true;
    }
}
