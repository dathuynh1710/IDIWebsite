<?php

namespace Tests\Feature;

use App\Livewire\Admin\Access\ActivityLogs;
use App\Livewire\Admin\Access\Roles as RoleManager;
use App\Livewire\Admin\Access\Users as UserManager;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class AccessControlManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_access_pages_require_the_corresponding_permission(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/access/users')->assertForbidden();
        $this->actingAs($user)->get('/admin/access/roles')->assertForbidden();
        $this->actingAs($user)->get('/admin/access/permissions')->assertForbidden();
        $this->actingAs($user)->get('/admin/activity-logs')->assertForbidden();

        $user->givePermissionTo($this->permission('users.view'));
        $this->actingAs($user)->get('/admin/access/users')->assertOk()->assertSee('Quản lý quản trị viên');
    }

    public function test_authorized_admin_can_create_a_user_and_assign_a_role(): void
    {
        $admin = $this->adminWith(['users.view', 'users.create']);
        $role = Role::create(['name' => 'content-manager', 'display_name' => 'Quản lý nội dung', 'guard_name' => 'web']);

        Livewire::actingAs($admin)->test(UserManager::class)
            ->call('create')
            ->set('name', 'Nguyễn Minh Anh')
            ->set('username', 'minhanh')
            ->set('email', 'minhanh@example.com')
            ->set('password', 'secret123')
            ->set('password_confirmation', 'secret123')
            ->set('roleIds', [(string) $role->id])
            ->call('save')
            ->assertHasNoErrors();

        $user = User::where('username', 'minhanh')->firstOrFail();
        $this->assertTrue($user->hasRole('content-manager'));
        $this->assertDatabaseHas('activity_log', ['causer_id' => $admin->id, 'event' => 'roles_assigned']);
    }

    public function test_admin_cannot_deactivate_the_current_account(): void
    {
        $admin = $this->adminWith(['users.view', 'users.update']);

        Livewire::actingAs($admin)->test(UserManager::class)
            ->call('edit', $admin->id)
            ->set('is_active', false)
            ->call('save')
            ->assertHasErrors('is_active');

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_non_super_admin_cannot_assign_the_super_admin_role(): void
    {
        $admin = $this->adminWith(['users.view', 'users.create']);
        $superAdmin = Role::create(['name' => 'super-admin', 'display_name' => 'Quản trị cao nhất', 'guard_name' => 'web', 'is_system' => true]);

        Livewire::actingAs($admin)->test(UserManager::class)
            ->call('create')
            ->set('name', 'Tài khoản vượt quyền')
            ->set('username', 'privilege-escalation')
            ->set('email', 'privilege@example.com')
            ->set('password', 'secret123')
            ->set('password_confirmation', 'secret123')
            ->set('roleIds', [(string) $superAdmin->id])
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['username' => 'privilege-escalation']);
    }

    public function test_role_permissions_can_be_assigned_and_are_enforced_by_gate(): void
    {
        $admin = $this->adminWith(['roles.view', 'roles.create', 'roles.update']);
        $productPermission = $this->permission('products.manage');

        Livewire::actingAs($admin)->test(RoleManager::class)
            ->call('create')
            ->set('name', 'product-editor')
            ->set('display_name', 'Biên tập sản phẩm')
            ->set('description', 'Quản lý danh mục sản phẩm')
            ->call('save')
            ->set('permissionIds', [(string) $productPermission->id])
            ->call('savePermissions')
            ->assertHasNoErrors();

        $editor = User::factory()->create();
        $editor->assignRole('product-editor');
        $this->assertTrue($editor->can('products.manage'));
        $this->actingAs($editor)->get('/admin/products')->assertOk();
    }

    public function test_granular_view_permission_does_not_grant_create_permission(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo($this->permission('products.view'));

        $this->actingAs($viewer)->get('/admin/products')->assertOk();
        $this->actingAs($viewer)->get('/admin/products/create')->assertForbidden();
    }

    public function test_role_page_renders_the_permission_matrix(): void
    {
        $admin = $this->adminWith(['roles.view']);
        Role::create(['name' => 'viewer', 'display_name' => 'Người xem', 'guard_name' => 'web']);

        $this->actingAs($admin)->get('/admin/access/roles')
            ->assertOk()
            ->assertSee('Ma trận phân quyền')
            ->assertSee('Nhóm người dùng')
            ->assertSee('Nội dung website')
            ->assertSee('Quản trị hệ thống')
            ->assertSee('Xóa')
            ->assertDontSee('Toàn quyền')
            ->assertDontSee('>Khác<', false);
    }

    public function test_activity_log_can_be_filtered_by_admin_action_module_and_date(): void
    {
        $admin = $this->adminWith(['activity.view']);
        Activity::create([
            'log_name' => 'admin', 'description' => 'Cập nhật sản phẩm mẫu', 'event' => 'updated',
            'causer_type' => User::class, 'causer_id' => $admin->id,
            'properties' => ['module' => 'Sản phẩm', 'ip_address' => '127.0.0.1'],
            'created_at' => now(), 'updated_at' => now(),
        ]);
        Activity::create([
            'log_name' => 'admin', 'description' => 'Đăng nhập hệ thống', 'event' => 'login',
            'causer_type' => User::class, 'causer_id' => $admin->id,
            'properties' => ['module' => 'Xác thực', 'ip_address' => '127.0.0.1'],
            'created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3),
        ]);

        Livewire::actingAs($admin)->test(ActivityLogs::class)
            ->set('adminId', (string) $admin->id)
            ->set('action', 'updated')
            ->set('module', 'Sản phẩm')
            ->set('dateFrom', now()->toDateString())
            ->set('dateTo', now()->toDateString())
            ->assertSee('Cập nhật sản phẩm mẫu')
            ->assertDontSee('Đăng nhập hệ thống');
    }

    private function adminWith(array $permissions): User
    {
        $admin = User::factory()->create();
        foreach ($permissions as $permission) {
            $admin->givePermissionTo($this->permission($permission));
        }

        return $admin;
    }

    private function permission(string $name): Permission
    {
        return Permission::findOrCreate($name, 'web');
    }
}
