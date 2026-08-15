<?php

namespace App\Livewire\Admin;

use App\Livewire\AdminComponent;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.admin')]
#[Title('Hồ sơ')]
class Profile extends AdminComponent
{
    public string $name = '';

    public string $username = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    /** @var list<string> */
    public array $roles = [];

    public function mount(): void
    {
        $user = User::with('roles')->findOrFail(auth()->id());

        $this->name = $user->name;
        $this->username = $user->username;
        $this->roles = $user->roles
            ->map(fn ($role): string => $role->display_name ?: $role->name)
            ->values()
            ->all();
    }

    public function changePassword(): void
    {
        $user = User::findOrFail(auth()->id());

        $validated = $this->validate([
            'current_password' => ['required', 'current_password:web'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'different:current_password',
                'regex:/(?=[\s\S]*[A-Z])(?=[\s\S]*(?:[0-9]|\p{P}|\p{S}))/u',
            ],
            'password_confirmation' => ['required', 'string'],
        ], [
            'current_password.current_password' => 'Mật khẩu hiện tại không chính xác.',
            'password.regex' => 'Mật khẩu mới phải có chữ hoa và có số hoặc ký tự đặc biệt.',
            'password.different' => 'Mật khẩu mới phải khác mật khẩu hiện tại.',
        ], [
            'current_password' => 'mật khẩu hiện tại',
            'password' => 'mật khẩu mới',
            'password_confirmation' => 'xác nhận mật khẩu mới',
        ]);

        $user->password = $validated['password'];
        $user->save();

        $this->resetPasswordForm();
        $this->toast('Đã đổi mật khẩu thành công.');
    }

    public function resetPasswordForm(): void
    {
        $this->reset('current_password', 'password', 'password_confirmation');
        $this->resetValidation();
    }
}
