<?php

namespace App\Enums;

enum ContactStatus: string
{
    case Unread = 'new';
    case Read = 'in_progress';

    public function label(): string
    {
        return match ($this) {
            self::Unread => 'Chưa xem',
            self::Read => 'Đã xem',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Unread => 'warning',
            self::Read => 'neutral',
        };
    }
}
