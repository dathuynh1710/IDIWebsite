<?php

namespace App\Livewire\Concerns;

use App\Support\Toast;

trait InteractsWithToasts
{
    protected function toast(string $message, string $type = 'success'): void
    {
        $this->dispatch('toast', ...Toast::payload($message, $type));
    }

    protected function flashToast(string $message, string $type = 'success'): void
    {
        Toast::flash($message, $type);
    }

    protected function toastState(bool $isActive, string $subject): void
    {
        $this->toast($isActive ? "Đã hiển thị {$subject}." : "Đã ẩn {$subject}.");
    }

    protected function toastBulk(string $action, string $subject): void
    {
        $this->toast(match ($action) {
            'show' => "Đã hiển thị {$subject} đã chọn.",
            'hide' => "Đã ẩn {$subject} đã chọn.",
            'reorder' => "Đã cập nhật thứ tự {$subject}.",
            'delete' => "Đã chuyển {$subject} đã chọn vào thùng rác.",
            default => "Đã cập nhật {$subject}.",
        });
    }
}
