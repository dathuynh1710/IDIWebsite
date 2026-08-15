<?php

namespace Tests\Feature;

use App\Livewire\Admin\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_profile_page(): void
    {
        $this->get('/admin/profile')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_open_profile_and_menu_has_no_password_link(): void
    {
        $user = User::factory()->create(['name' => 'Nguyễn Minh Anh']);

        $this->actingAs($user)->get('/admin/profile')
            ->assertOk()
            ->assertSee('Hồ sơ')
            ->assertSee('Đổi mật khẩu')
            ->assertSee('Nguyễn Minh Anh')
            ->assertSee(route('admin.profile'), false)
            ->assertDontSee('wire:model="name"', false)
            ->assertDontSee('wire:model="email"', false);
    }

    public function test_user_can_change_password(): void
    {
        $user = User::factory()->create([
            'username' => 'admin.idi',
            'password' => 'OldPassword1!',
        ]);

        Livewire::actingAs($user)
            ->test(Profile::class)
            ->set('current_password', 'OldPassword1!')
            ->set('password', 'NewPassword2!')
            ->set('password_confirmation', 'NewPassword2!')
            ->call('changePassword')
            ->assertHasNoErrors();

        $this->assertTrue(password_verify('NewPassword2!', $user->fresh()->password));
    }

    public function test_current_password_must_be_correct(): void
    {
        $user = User::factory()->create(['password' => 'OldPassword1!']);

        Livewire::actingAs($user)
            ->test(Profile::class)
            ->set('current_password', 'incorrect')
            ->set('password', 'NewPassword2!')
            ->set('password_confirmation', 'NewPassword2!')
            ->call('changePassword')
            ->assertHasErrors(['current_password']);

        $this->assertTrue(password_verify('OldPassword1!', $user->fresh()->password));
    }

    public function test_new_password_must_meet_security_rules_and_be_confirmed(): void
    {
        $user = User::factory()->create(['password' => 'OldPassword1!']);

        Livewire::actingAs($user)
            ->test(Profile::class)
            ->set('current_password', 'OldPassword1!')
            ->set('password', 'weakpass')
            ->set('password_confirmation', 'different')
            ->call('changePassword')
            ->assertHasErrors(['password']);
    }
}
