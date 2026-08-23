<?php

namespace App\Support;

use Illuminate\Http\Request;

final class Locale
{
    public const DEFAULT = 'vi';

    /**
     * Convert public BCP 47 locale codes to the translation keys stored in JSON columns.
     */
    public static function fromRequest(Request $request): string
    {
        $locale = $request->string(
            'locale',
            $request->string('lang', self::DEFAULT)->toString()
        )->toString();
        $locale = strtolower(trim(explode(',', $locale)[0]));

        return match ($locale) {
            'en', 'en-us', 'en-gb' => 'en',
            'zh', 'zh-cn', 'zh-hans' => 'zh',
            default => self::DEFAULT,
        };
    }

    public static function normalize(?string $locale): string
    {
        return match (strtolower(trim((string) $locale))) {
            'en', 'en-us', 'en-gb' => 'en',
            'zh', 'zh-cn', 'zh-hans' => 'zh',
            default => self::DEFAULT,
        };
    }
}
