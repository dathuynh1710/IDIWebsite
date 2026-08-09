<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
        });

        Schema::table('roles', function (Blueprint $table): void {
            $table->string('display_name')->nullable()->after('name');
            $table->text('description')->nullable()->after('display_name');
            $table->boolean('is_system')->default(false)->after('description');
        });

        Schema::table('permissions', function (Blueprint $table): void {
            $table->string('display_name')->nullable()->after('name');
            $table->text('description')->nullable()->after('display_name');
            $table->string('module')->nullable()->index()->after('description');
            $table->boolean('is_system')->default(false)->after('module');
        });

        $permissionIds = [];
        foreach (config('access-control.permissions') as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission['name'], 'guard_name' => 'web'],
                [
                    'display_name' => $permission['label'],
                    'module' => $permission['module'],
                    'is_system' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
            $permissionIds[] = DB::table('permissions')->where('name', $permission['name'])->where('guard_name', 'web')->value('id');
        }

        DB::table('roles')->where('name', 'super-admin')->where('guard_name', 'web')->update([
            'display_name' => 'Quản trị cao nhất',
            'description' => 'Có toàn quyền trên hệ thống và không thể bị xóa.',
            'is_system' => true,
        ]);
        DB::table('roles')->where('name', 'editor')->where('guard_name', 'web')->update([
            'display_name' => 'Biên tập viên',
            'description' => 'Quản lý nội dung website theo quyền được cấp.',
            'is_system' => true,
        ]);

        $superAdminRoleId = DB::table('roles')->where('name', 'super-admin')->where('guard_name', 'web')->value('id');
        if ($superAdminRoleId) {
            foreach ($permissionIds as $permissionId) {
                DB::table('role_has_permissions')->insertOrIgnore(['permission_id' => $permissionId, 'role_id' => $superAdminRoleId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['last_login_at', 'last_login_ip']));
        Schema::table('roles', fn (Blueprint $table) => $table->dropColumn(['display_name', 'description', 'is_system']));
        Schema::table('permissions', function (Blueprint $table): void {
            $table->dropIndex(['module']);
            $table->dropColumn(['display_name', 'description', 'module', 'is_system']);
        });
    }
};
