<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

final class Toast
{
    public const TYPES = ['success', 'error', 'warning', 'info'];

    public static function payload(string $message, string $type = 'success'): array
    {
        return [
            'type' => in_array($type, self::TYPES, true) ? $type : 'info',
            'message' => $message,
        ];
    }

    public static function success(string $message): array
    {
        return ['toast' => self::payload($message, 'success')];
    }

    public static function error(string $message): array
    {
        return ['toast' => self::payload($message, 'error')];
    }

    public static function warning(string $message): array
    {
        return ['toast' => self::payload($message, 'warning')];
    }

    public static function info(string $message): array
    {
        return ['toast' => self::payload($message, 'info')];
    }

    public static function flash(string $message, string $type = 'success'): void
    {
        session()->flash('toast', self::payload($message, $type));
    }

    public static function json(string $message, string $type = 'success', array $data = [], int $status = 200): JsonResponse
    {
        return response()->json(array_merge($data, [
            'toast' => self::payload($message, $type),
        ]), $status);
    }
}
