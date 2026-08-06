<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_admin_can_log_in_with_username_and_password(): void
    {
        $user = User::factory()->create([
            'username' => 'admin',
            'password' => 'idi686868',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'idi686868',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('toast', fn (array $toast): bool => $toast === [
            'type' => 'success',
            'message' => 'Đăng nhập thành công.',
        ]);
        $this->assertAuthenticatedAs($user);
    }

    public function test_email_is_not_accepted_as_the_admin_login_identifier(): void
    {
        $user = User::factory()->create([
            'username' => 'admin',
            'password' => 'idi686868',
            'is_active' => true,
        ]);

        $this->post('/login', [
            'username' => $user->email,
            'password' => 'idi686868',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_inactive_admin_cannot_log_in(): void
    {
        User::factory()->create([
            'username' => 'admin',
            'password' => 'idi686868',
            'is_active' => false,
        ]);

        $this->post('/login', [
            'username' => 'admin',
            'password' => 'idi686868',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }
}
