<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public const PERMISSION_STRINGS = [
        'time_schedule_shift_access',
        'time_schedule_shift_show',
        'time_schedule_shift_create',
        'time_schedule_shift_edit',
        'time_schedule_shift_delete'
    ];
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        foreach (static::PERMISSION_STRINGS as $permissionString) {
            $permission = \App\Models\Permission::create([
                'title' => $permissionString
            ]);
            $role = \App\Models\Role::where('title', '=', 'Admin')->first();
            $role->permissions()->attach($permission);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        foreach (static::PERMISSION_STRINGS as $permissionString) {
            $permission = \App\Models\Permission::where('title', '=', $permissionString)->first();
            $role = \App\Models\Role::where('title', '=', 'Admin')->first();
            $role->permissions()->detach($permission);
            $permission->delete();
        }
    }
};
