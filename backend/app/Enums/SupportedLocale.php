<?php

namespace App\Enums;

enum SupportedLocale: string
{
    case Vietnamese = 'vi';
    case English = 'en';
    case Chinese = 'zh';

    public static function fromPublicCode(string $locale): self
    {
        return match (strtolower($locale)) {
            'en', 'en-us', 'en-gb' => self::English,
            'zh', 'zh-cn', 'zh-hans' => self::Chinese,
            default => self::Vietnamese,
        };
    }
}
