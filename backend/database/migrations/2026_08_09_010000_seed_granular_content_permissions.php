<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
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

        $superAdminRoleId = DB::table('roles')->where('name', 'super-admin')->where('guard_name', 'web')->value('id');
        if ($superAdminRoleId) {
            foreach ($permissionIds as $permissionId) {
                DB::table('role_has_permissions')->insertOrIgnore(['permission_id' => $permissionId, 'role_id' => $superAdminRoleId]);
            }
        }
    }

    public function down(): void
    {
        // Permissions are intentionally retained to avoid invalidating existing role assignments.
    }
};
