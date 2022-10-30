<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permission = \App\Models\Permission::create([
            'title' => 'order_cancel'
        ]);
        $role = \App\Models\Role::where('title', '=', 'Admin')->first();
        $role->permissions()->attach($permission);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $permission = \App\Models\Permission::where('title', '=', 'order_cancel')->first();
        $role = \App\Models\Role::where('title', '=', 'Admin')->first();
        $role->permissions()->detach($permission);
        $permission->delete();
    }
};
