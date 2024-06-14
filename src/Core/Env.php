<?php
declare(strict_types=1);

namespace App\Core;

final class Env
{
    private array $params;

    public function __construct()
    {
        $this->load();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    public function load(): void
    {
        $path = Helper::basePath('.env');
        $file = fopen($path, 'r');
        while (($string = fgets($file)) !== false) {
            $string = trim($string);
            if (empty($string)) {
                continue;
            }
            [$key, $value] = explode('=', $string, 2);
            $this->params[$key] = $value;
        }
    }
}