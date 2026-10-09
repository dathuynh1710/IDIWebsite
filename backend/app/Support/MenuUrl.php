<?php

namespace App\Support;

class MenuUrl
{
    public static function valid(?string $url, string $type): bool
    {
        if (! $url || preg_match('/[\x00-\x20\\\\]/', $url)) {
            return false;
        }
        if ($type === 'internal') {
            return str_starts_with($url, '/') && ! str_starts_with(rawurldecode($url), '//');
        }

        return $type === 'external' && filter_var($url, FILTER_VALIDATE_URL)
            && in_array(strtolower(parse_url($url, PHP_URL_SCHEME) ?? ''), ['http', 'https'], true)
            && ! parse_url($url, PHP_URL_USER);
    }
}
