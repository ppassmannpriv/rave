<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\TimeSchedule;
use App\Models\TimeSchedule\Shift;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * CRUD + authorization for the admin users, roles, permissions, time schedules and time schedule
 * shifts resources (roadmap 3.6). Same hand-built SQLite schema as PayPalFriendsFamilyReportPipelineTest
 * (no MySQL in this sandbox). AuthGates defines one gate per permission title held by any role, so every
 * test seeds an "Admin" role holding all permissions; the acting user gets its own role with only the
 * permissions under test. Views are never rendered: GET routes are only checked for 403.
 */
class AccessAndSchedulesCrudTest extends TestCase
{
    private const ALL_PERMISSIONS = [
        'user_access', 'user_create', 'user_edit', 'user_show', 'user_delete',
        'role_access', 'role_create', 'role_edit', 'role_show', 'role_delete',
        'permission_access', 'permission_create', 'permission_edit', 'permission_show', 'permission_delete',
        'time_schedule_access', 'time_schedule_create', 'time_schedule_edit', 'time_schedule_show', 'time_schedule_delete',
        'time_schedule_shift_access', 'time_schedule_shift_create', 'time_schedule_shift_edit',
        'time_schedule_shift_show', 'time_schedule_shift_delete',
        'event_create', 'event_edit', 'dashboard_access',
    ];

    private Role $adminRole;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        DB::purge('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->dateTime('email_verified_at')->nullable();
            $table->string('password');
            $table->string('remember_token')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        foreach (['roles', 'permissions'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->timestamps();
                $table->softDeletes();
            });
        }
        Schema::create('permission_role', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');
        });
        Schema::create('role_user', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
        });
        Schema::create('time_schedules', function (Blueprint $table) {
            $table->id();
            $table->dateTime('start');
            $table->dateTime('end');
            $table->unsignedBigInteger('event_id')->nullable();
            $table->text('description')->nullable();
            $table->boolean('active')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('time_schedule_shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('time_schedule_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->dateTime('start');
            $table->dateTime('end');
            $table->boolean('crew_only');
            $table->timestamps();
            $table->softDeletes();
        });

        $this->adminRole = Role::create(['title' => 'Admin']);
        foreach (self::ALL_PERMISSIONS as $title) {
            $this->adminRole->permissions()->attach(Permission::create(['title' => $title])->id);
        }
    }

    /** A user whose only role holds exactly $permissions (gates for the rest exist via the Admin role). */
    private function actor(array $permissions, string $email = 'actor@example.com'): User
    {
        $user = User::create(['name' => 'Actor', 'email' => $email, 'password' => 'actor-password']);
        $role = Role::create(['title' => 'Actor role']);
        $role->permissions()->sync(Permission::whereIn('title', $permissions)->pluck('id')->all());
        $user->roles()->attach($role->id);

        return $user;
    }

    private function permissionIds(array $titles): array
    {
        return Permission::whereIn('title', $titles)->orderBy('id')->pluck('id')->all();
    }

    private function send(string $method, string $uri, array $data = []): TestResponse
    {
        return $method === 'get' ? $this->get($uri) : $this->{$method}($uri, $data);
    }

    private function assertAllForbidden(User $actor, array $requests): void
    {
        $this->actingAs($actor);
        foreach ($requests as $request) {
            [$method, $uri] = $request;
            $status = $this->send($method, $uri, $request[2] ?? [])->status();
            $this->assertSame(403, $status, strtoupper($method) . " $uri must be forbidden");
        }
    }

    private function assertAllRedirectGuestsToLogin(array $requests): void
    {
        foreach ($requests as $request) {
            [$method, $uri] = $request;
            $response = $this->send($method, $uri, $request[2] ?? []);
            $this->assertTrue($response->isRedirect(route('login')), strtoupper($method) . " $uri must redirect a guest to login");
        }
    }

    private function schedule(string $description = 'Build-up'): TimeSchedule
    {
        return TimeSchedule::create([
            'description' => $description,
            'start' => '2026-07-01 08:00:00',
            'end' => '2026-07-03 20:00:00',
            'active' => true,
        ]);
    }

    private function shift(TimeSchedule $schedule, string $name = 'Bar'): Shift
    {
        return Shift::create([
            'time_schedule_id' => $schedule->id,
            'name' => $name,
            'start' => '2026-07-01 20:00:00',
            'end' => '2026-07-01 22:00:00',
            'crew_only' => false,
        ]);
    }

    private function schedulePayload(array $overrides = []): array
    {
        return array_merge([
            'description' => 'Tear-down',
            'start' => '2026-07-04 09:00:00',
            'end' => '2026-07-04 18:00:00',
            'active' => '1',
            'event_id' => '7',
        ], $overrides);
    }

    public function test_user_store_creates_user_with_hashed_password_and_attached_roles(): void
    {
        $editor = Role::create(['title' => 'Editor']);

        $this->actingAs($this->actor(['user_create']))->post(route('admin.users.store'), [
            'name' => 'New Person',
            'email' => 'new@example.com',
            'password' => 'plain-secret',
            'roles' => [$editor->id],
        ])->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'new@example.com')->firstOrFail();
        $this->assertSame('New Person', $user->name);
        $this->assertNotSame('plain-secret', $user->getAttributes()['password']);
        $this->assertTrue(Hash::check('plain-secret', $user->getAttributes()['password']));
        $this->assertSame([$editor->id], $user->roles()->pluck('roles.id')->all());
    }

    public function test_user_store_with_duplicate_email_is_rejected_and_creates_nothing(): void
    {
        $actor = $this->actor(['user_create']);

        $this->actingAs($actor)->post(route('admin.users.store'), [
            'name' => 'Copycat',
            'email' => $actor->email,
            'password' => 'plain-secret',
            'roles' => [$this->adminRole->id],
        ])->assertSessionHasErrors('email');

        $this->assertSame(1, User::count());
    }

    public function test_user_store_without_roles_is_rejected_and_creates_nothing(): void
    {
        $this->actingAs($this->actor(['user_create']))->post(route('admin.users.store'), [
            'name' => 'No Roles',
            'email' => 'noroles@example.com',
            'password' => 'plain-secret',
        ])->assertSessionHasErrors('roles');

        $this->assertFalse(User::where('email', 'noroles@example.com')->exists());
    }

    public function test_user_update_changes_fields_and_resyncs_roles_and_keeps_password_when_omitted(): void
    {
        $oldRole = Role::create(['title' => 'Old']);
        $newRole = Role::create(['title' => 'New']);
        $target = User::create(['name' => 'Target', 'email' => 'target@example.com', 'password' => 'old-secret']);
        $target->roles()->attach($oldRole->id);
        $hashBefore = $target->getAttributes()['password'];

        $this->actingAs($this->actor(['user_edit']))->put(route('admin.users.update', $target), [
            'name' => 'Renamed',
            'email' => 'renamed@example.com',
            'roles' => [$newRole->id],
        ])->assertRedirect(route('admin.users.index'));

        $target->refresh();
        $this->assertSame('Renamed', $target->name);
        $this->assertSame('renamed@example.com', $target->email);
        $this->assertSame($hashBefore, $target->getAttributes()['password']);
        $this->assertSame([$newRole->id], $target->roles()->pluck('roles.id')->all());
    }

    public function test_user_update_may_keep_the_users_own_email_but_not_take_another_users_email(): void
    {
        $target = User::create(['name' => 'Target', 'email' => 'target@example.com', 'password' => 'x']);
        $actor = $this->actor(['user_edit']);

        $this->actingAs($actor)->put(route('admin.users.update', $target), [
            'name' => 'Same Mail', 'email' => 'target@example.com', 'roles' => [$this->adminRole->id],
        ])->assertSessionHasNoErrors();
        $this->put(route('admin.users.update', $target), [
            'name' => 'Stolen Mail', 'email' => $actor->email, 'roles' => [$this->adminRole->id],
        ])->assertSessionHasErrors('email');

        $this->assertSame('target@example.com', $target->refresh()->email);
        $this->assertSame('Same Mail', $target->name);
    }

    public function test_user_destroy_soft_deletes_the_user(): void
    {
        $target = User::create(['name' => 'Target', 'email' => 'target@example.com', 'password' => 'x']);

        $this->actingAs($this->actor(['user_delete']))->delete(route('admin.users.destroy', $target))->assertRedirect();

        $this->assertSoftDeleted('users', ['id' => $target->id]);
    }

    public function test_user_mass_destroy_soft_deletes_only_the_selected_users(): void
    {
        $a = User::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'x']);
        $b = User::create(['name' => 'B', 'email' => 'b@example.com', 'password' => 'x']);
        $keep = User::create(['name' => 'C', 'email' => 'c@example.com', 'password' => 'x']);

        $this->actingAs($this->actor(['user_delete']))
            ->delete(route('admin.users.massDestroy'), ['ids' => [$a->id, $b->id]])
            ->assertNoContent();

        $this->assertSoftDeleted('users', ['id' => $a->id]);
        $this->assertSoftDeleted('users', ['id' => $b->id]);
        $this->assertNotSoftDeleted('users', ['id' => $keep->id]);
    }

    public function test_user_without_user_create_permission_cannot_create_a_user(): void
    {
        $this->actingAs($this->actor(['user_access', 'user_edit']))->post(route('admin.users.store'), [
            'name' => 'Sneaky Admin',
            'email' => 'sneaky@example.com',
            'password' => 'plain-secret',
            'roles' => [$this->adminRole->id],
        ])->assertForbidden();

        $this->assertFalse(User::where('email', 'sneaky@example.com')->exists());
    }

    public function test_user_without_user_edit_permission_cannot_promote_themselves_to_admin(): void
    {
        $actor = $this->actor(['user_access', 'user_create']);
        $rolesBefore = $actor->roles()->pluck('roles.id')->all();

        $this->actingAs($actor)->put(route('admin.users.update', $actor), [
            'name' => $actor->name,
            'email' => $actor->email,
            'roles' => [$this->adminRole->id],
        ])->assertForbidden();

        $this->assertSame($rolesBefore, $actor->roles()->pluck('roles.id')->all());
        $this->assertNotContains($this->adminRole->id, $rolesBefore);
    }

    public function test_every_user_route_is_forbidden_for_a_role_without_user_permissions(): void
    {
        $target = User::create(['name' => 'Target', 'email' => 'target@example.com', 'password' => 'x']);
        $payload = ['name' => 'X', 'email' => 'x@example.com', 'password' => 'p', 'roles' => [$this->adminRole->id]];

        $this->assertAllForbidden($this->actor(['dashboard_access']), [
            ['get', route('admin.users.index')],
            ['get', route('admin.users.create')],
            ['get', route('admin.users.show', $target)],
            ['get', route('admin.users.edit', $target)],
            ['post', route('admin.users.store'), $payload],
            ['put', route('admin.users.update', $target), $payload],
            ['delete', route('admin.users.destroy', $target)],
            ['delete', route('admin.users.massDestroy'), ['ids' => [$target->id]]],
        ]);

        $this->assertSame(2, User::count());
        $this->assertSame('Target', $target->refresh()->name);
    }

    public function test_every_user_route_redirects_a_guest_to_login(): void
    {
        $target = User::create(['name' => 'Target', 'email' => 'target@example.com', 'password' => 'x']);

        $this->assertAllRedirectGuestsToLogin([
            ['get', route('admin.users.index')],
            ['post', route('admin.users.store'), ['name' => 'X', 'email' => 'x@example.com', 'password' => 'p', 'roles' => [1]]],
            ['put', route('admin.users.update', $target), ['name' => 'X', 'email' => 'x@example.com', 'roles' => [1]]],
            ['delete', route('admin.users.destroy', $target)],
            ['delete', route('admin.users.massDestroy'), ['ids' => [$target->id]]],
        ]);

        $this->assertSame(1, User::count());
        $this->assertNotSoftDeleted('users', ['id' => $target->id]);
    }

    public function test_role_store_creates_role_with_attached_permissions(): void
    {
        $ids = $this->permissionIds(['user_access', 'user_show']);

        $this->actingAs($this->actor(['role_create']))->post(route('admin.roles.store'), [
            'title' => 'Support',
            'permissions' => $ids,
        ])->assertRedirect(route('admin.roles.index'));

        $role = Role::where('title', 'Support')->firstOrFail();
        $this->assertSame($ids, $role->permissions()->orderBy('permissions.id')->pluck('permissions.id')->all());
    }

    public function test_role_store_without_title_or_permissions_is_rejected_and_creates_nothing(): void
    {
        $this->actingAs($this->actor(['role_create']))
            ->post(route('admin.roles.store'), [])
            ->assertSessionHasErrors(['title', 'permissions']);

        $this->assertSame(2, Role::count());
    }

    public function test_role_update_changes_title_and_replaces_permissions(): void
    {
        $role = Role::create(['title' => 'Support']);
        $role->permissions()->attach($this->permissionIds(['user_access']));
        $newIds = $this->permissionIds(['role_access', 'role_show']);

        $this->actingAs($this->actor(['role_edit']))->put(route('admin.roles.update', $role), [
            'title' => 'Senior Support',
            'permissions' => $newIds,
        ])->assertRedirect(route('admin.roles.index'));

        $this->assertSame('Senior Support', $role->refresh()->title);
        $this->assertSame($newIds, $role->permissions()->orderBy('permissions.id')->pluck('permissions.id')->all());
    }

    public function test_role_destroy_soft_deletes_the_role(): void
    {
        $role = Role::create(['title' => 'Support']);

        $this->actingAs($this->actor(['role_delete']))->delete(route('admin.roles.destroy', $role))->assertRedirect();

        $this->assertSoftDeleted('roles', ['id' => $role->id]);
    }

    public function test_role_mass_destroy_soft_deletes_only_the_selected_roles(): void
    {
        $a = Role::create(['title' => 'A']);
        $keep = Role::create(['title' => 'B']);

        $this->actingAs($this->actor(['role_delete']))
            ->delete(route('admin.roles.massDestroy'), ['ids' => [$a->id]])
            ->assertNoContent();

        $this->assertSoftDeleted('roles', ['id' => $a->id]);
        $this->assertNotSoftDeleted('roles', ['id' => $keep->id]);
    }

    public function test_every_role_route_is_forbidden_for_a_role_without_role_permissions(): void
    {
        $role = Role::create(['title' => 'Support']);
        $payload = ['title' => 'Hijacked', 'permissions' => $this->permissionIds(['user_delete'])];

        $this->assertAllForbidden($this->actor(['dashboard_access']), [
            ['get', route('admin.roles.index')],
            ['get', route('admin.roles.create')],
            ['get', route('admin.roles.show', $role)],
            ['get', route('admin.roles.edit', $role)],
            ['post', route('admin.roles.store'), $payload],
            ['put', route('admin.roles.update', $role), $payload],
            ['delete', route('admin.roles.destroy', $role)],
            ['delete', route('admin.roles.massDestroy'), ['ids' => [$role->id]]],
        ]);

        $this->assertSame(3, Role::count());
        $this->assertSame('Support', $role->refresh()->title);
        $this->assertSame(0, $role->permissions()->count());
    }

    public function test_every_role_route_redirects_a_guest_to_login(): void
    {
        $role = Role::create(['title' => 'Support']);
        $payload = ['title' => 'Hijacked', 'permissions' => [1]];

        $this->assertAllRedirectGuestsToLogin([
            ['get', route('admin.roles.index')],
            ['post', route('admin.roles.store'), $payload],
            ['put', route('admin.roles.update', $role), $payload],
            ['delete', route('admin.roles.destroy', $role)],
            ['delete', route('admin.roles.massDestroy'), ['ids' => [$role->id]]],
        ]);

        $this->assertSame(2, Role::count());
        $this->assertSame('Support', $role->refresh()->title);
    }

    public function test_permission_store_creates_the_permission(): void
    {
        $this->actingAs($this->actor(['permission_create']))
            ->post(route('admin.permissions.store'), ['title' => 'report_access'])
            ->assertRedirect(route('admin.permissions.index'));

        $this->assertDatabaseHas('permissions', ['title' => 'report_access', 'deleted_at' => null]);
    }

    public function test_permission_store_without_title_is_rejected_and_creates_nothing(): void
    {
        $before = Permission::count();

        $this->actingAs($this->actor(['permission_create']))
            ->post(route('admin.permissions.store'), ['title' => ''])
            ->assertSessionHasErrors('title');

        $this->assertSame($before, Permission::count());
    }

    public function test_permission_update_changes_the_title(): void
    {
        $permission = Permission::create(['title' => 'report_access']);

        $this->actingAs($this->actor(['permission_edit']))
            ->put(route('admin.permissions.update', $permission), ['title' => 'report_show'])
            ->assertRedirect(route('admin.permissions.index'));

        $this->assertSame('report_show', $permission->refresh()->title);
    }

    public function test_permission_destroy_soft_deletes_the_permission(): void
    {
        $permission = Permission::create(['title' => 'report_access']);

        $this->actingAs($this->actor(['permission_delete']))
            ->delete(route('admin.permissions.destroy', $permission))
            ->assertRedirect();

        $this->assertSoftDeleted('permissions', ['id' => $permission->id]);
    }

    public function test_permission_mass_destroy_soft_deletes_only_the_selected_permissions(): void
    {
        $a = Permission::create(['title' => 'report_access']);
        $keep = Permission::create(['title' => 'report_show']);

        $this->actingAs($this->actor(['permission_delete']))
            ->delete(route('admin.permissions.massDestroy'), ['ids' => [$a->id]])
            ->assertNoContent();

        $this->assertSoftDeleted('permissions', ['id' => $a->id]);
        $this->assertNotSoftDeleted('permissions', ['id' => $keep->id]);
    }

    public function test_every_permission_route_is_forbidden_for_a_role_without_permission_permissions(): void
    {
        $permission = Permission::create(['title' => 'report_access']);
        $before = Permission::count();

        $this->assertAllForbidden($this->actor(['dashboard_access']), [
            ['get', route('admin.permissions.index')],
            ['get', route('admin.permissions.create')],
            ['get', route('admin.permissions.show', $permission)],
            ['get', route('admin.permissions.edit', $permission)],
            ['post', route('admin.permissions.store'), ['title' => 'evil']],
            ['put', route('admin.permissions.update', $permission), ['title' => 'evil']],
            ['delete', route('admin.permissions.destroy', $permission)],
            ['delete', route('admin.permissions.massDestroy'), ['ids' => [$permission->id]]],
        ]);

        $this->assertSame($before, Permission::count());
        $this->assertSame('report_access', $permission->refresh()->title);
    }

    public function test_every_permission_route_redirects_a_guest_to_login(): void
    {
        $permission = Permission::create(['title' => 'report_access']);

        $this->assertAllRedirectGuestsToLogin([
            ['get', route('admin.permissions.index')],
            ['post', route('admin.permissions.store'), ['title' => 'evil']],
            ['put', route('admin.permissions.update', $permission), ['title' => 'evil']],
            ['delete', route('admin.permissions.destroy', $permission)],
            ['delete', route('admin.permissions.massDestroy'), ['ids' => [$permission->id]]],
        ]);

        $this->assertSame('report_access', $permission->refresh()->title);
        $this->assertNotSoftDeleted('permissions', ['id' => $permission->id]);
    }

    public function test_time_schedule_store_creates_the_schedule_for_a_user_with_event_create(): void
    {
        $this->actingAs($this->actor(['event_create']))
            ->post(route('admin.time-schedules.store'), $this->schedulePayload())
            ->assertRedirect(route('admin.time-schedules.index'));

        $schedule = TimeSchedule::where('description', 'Tear-down')->firstOrFail();
        $this->assertSame('2026-07-04 09:00:00', $schedule->start->format('Y-m-d H:i:s'));
        $this->assertSame('2026-07-04 18:00:00', $schedule->end->format('Y-m-d H:i:s'));
        $this->assertEquals(1, $schedule->active);
        $this->assertEquals(7, $schedule->event_id);
    }

    public function test_time_schedule_store_and_update_check_event_gates_not_time_schedule_gates_current_behaviour(): void
    {
        $schedule = $this->schedule();

        $this->actingAs($this->actor(['time_schedule_create', 'time_schedule_edit']));
        $this->post(route('admin.time-schedules.store'), $this->schedulePayload())->assertForbidden();
        $this->put(route('admin.time-schedules.update', $schedule), $this->schedulePayload())->assertForbidden();

        $this->assertSame(1, TimeSchedule::count());
        $this->assertSame('Build-up', $schedule->refresh()->description);
    }

    public function test_time_schedule_store_with_invalid_dates_is_rejected_and_creates_nothing(): void
    {
        $this->actingAs($this->actor(['event_create']))
            ->post(route('admin.time-schedules.store'), $this->schedulePayload(['start' => '04.07.2026 09:00', 'end' => '']))
            ->assertSessionHasErrors(['start', 'end']);

        $this->assertSame(0, TimeSchedule::count());
    }

    public function test_time_schedule_update_changes_the_schedule_for_a_user_with_event_edit(): void
    {
        $schedule = $this->schedule();

        $this->actingAs($this->actor(['event_edit']))
            ->put(route('admin.time-schedules.update', $schedule), $this->schedulePayload(['active' => '0']))
            ->assertRedirect(route('admin.time-schedules.index'));

        $schedule->refresh();
        $this->assertSame('Tear-down', $schedule->description);
        $this->assertSame('2026-07-04 09:00:00', $schedule->start->format('Y-m-d H:i:s'));
        $this->assertEquals(0, $schedule->active);
    }

    public function test_time_schedule_destroy_hard_deletes_although_table_has_deleted_at_current_behaviour(): void
    {
        $schedule = $this->schedule();

        $this->actingAs($this->actor(['time_schedule_delete']))
            ->delete(route('admin.time-schedules.destroy', $schedule))
            ->assertRedirect();

        $this->assertDatabaseMissing('time_schedules', ['id' => $schedule->id]);
    }

    public function test_time_schedule_mass_destroy_deletes_only_the_selected_schedules(): void
    {
        $a = $this->schedule('A');
        $keep = $this->schedule('B');

        $this->actingAs($this->actor(['time_schedule_delete']))
            ->delete(route('admin.time-schedules.massDestroy'), ['ids' => [$a->id]])
            ->assertNoContent();

        $this->assertDatabaseMissing('time_schedules', ['id' => $a->id]);
        $this->assertDatabaseHas('time_schedules', ['id' => $keep->id]);
    }

    public function test_every_time_schedule_route_is_forbidden_for_a_role_without_schedule_permissions(): void
    {
        $schedule = $this->schedule();

        $this->assertAllForbidden($this->actor(['dashboard_access']), [
            ['get', route('admin.time-schedules.index')],
            ['get', route('admin.time-schedules.create')],
            ['get', route('admin.time-schedules.show', $schedule)],
            ['get', route('admin.time-schedules.edit', $schedule)],
            ['post', route('admin.time-schedules.store'), $this->schedulePayload()],
            ['put', route('admin.time-schedules.update', $schedule), $this->schedulePayload()],
            ['delete', route('admin.time-schedules.destroy', $schedule)],
            ['delete', route('admin.time-schedules.massDestroy'), ['ids' => [$schedule->id]]],
        ]);

        $this->assertSame(1, TimeSchedule::count());
        $this->assertSame('Build-up', $schedule->refresh()->description);
    }

    public function test_every_time_schedule_route_redirects_a_guest_to_login(): void
    {
        $schedule = $this->schedule();

        $this->assertAllRedirectGuestsToLogin([
            ['get', route('admin.time-schedules.index')],
            ['post', route('admin.time-schedules.store'), $this->schedulePayload()],
            ['put', route('admin.time-schedules.update', $schedule), $this->schedulePayload()],
            ['delete', route('admin.time-schedules.destroy', $schedule)],
            ['delete', route('admin.time-schedules.massDestroy'), ['ids' => [$schedule->id]]],
        ]);

        $this->assertSame(1, TimeSchedule::count());
        $this->assertSame('Build-up', $schedule->refresh()->description);
    }

    public function test_shift_store_creates_the_requested_number_of_consecutive_shifts(): void
    {
        $schedule = $this->schedule();

        $this->actingAs($this->actor(['time_schedule_shift_create']))
            ->post(route('admin.time-schedules.time-schedule-shifts.store', $schedule), [
                'name' => 'Bar',
                'description' => 'Pour drinks',
                'start' => '2026-07-01 20:00:00',
                'end' => '2026-07-01 22:00:30',
                'repeat' => '2',
                'crew_only' => '1',
            ])->assertRedirect(route('admin.time-schedules.show', [$schedule->id]));

        $shifts = Shift::orderBy('id')->get();
        $this->assertCount(2, $shifts);
        $this->assertSame(
            [['2026-07-01 20:00:00', '2026-07-01 22:00:30'], ['2026-07-01 22:00:00', '2026-07-02 00:00:30']],
            $shifts->map(fn (Shift $s) => [$s->start->format('Y-m-d H:i:s'), $s->end->format('Y-m-d H:i:s')])->all()
        );
        foreach ($shifts as $shift) {
            $this->assertSame($schedule->id, (int) $shift->time_schedule_id);
            $this->assertSame('Bar', $shift->name);
            $this->assertEquals(1, $shift->crew_only);
        }
    }

    public function test_shift_store_with_a_whole_minute_duration_errors_and_creates_nothing_current_behaviour(): void
    {
        $schedule = $this->schedule();

        // Shift::createRepeating() checks only the seconds component of the duration ($duration->s < 1),
        // so any shift whose start and end share the same seconds value is refused with an exception.
        $this->actingAs($this->actor(['time_schedule_shift_create']))
            ->post(route('admin.time-schedules.time-schedule-shifts.store', $schedule), [
                'name' => 'Bar',
                'start' => '2026-07-01 20:00:00',
                'end' => '2026-07-01 22:00:00',
                'repeat' => '1',
                'crew_only' => '0',
            ])->assertStatus(500);

        $this->assertSame(0, Shift::count());
    }

    public function test_shift_store_without_name_is_rejected_and_creates_nothing(): void
    {
        $schedule = $this->schedule();

        $this->actingAs($this->actor(['time_schedule_shift_create']))
            ->post(route('admin.time-schedules.time-schedule-shifts.store', $schedule), [
                'start' => '2026-07-01 20:00:00',
                'end' => '2026-07-01 22:00:30',
                'repeat' => '1',
                'crew_only' => '0',
            ])->assertSessionHasErrors('name');

        $this->assertSame(0, Shift::count());
    }

    public function test_shift_update_changes_the_shift(): void
    {
        $schedule = $this->schedule();
        $shift = $this->shift($schedule);

        $this->actingAs($this->actor(['time_schedule_shift_edit']))
            ->put(route('admin.time-schedules.time-schedule-shifts.update', [$schedule, $shift]), [
                'name' => 'Entrance',
                'description' => 'Check tickets',
                'start' => '2026-07-02 18:00:00',
                'end' => '2026-07-02 21:00:00',
                'crew_only' => '1',
            ])->assertRedirect(route('admin.time-schedules.show', [$schedule->id]));

        $shift->refresh();
        $this->assertSame('Entrance', $shift->name);
        $this->assertSame('Check tickets', $shift->description);
        $this->assertSame('2026-07-02 18:00:00', $shift->start->format('Y-m-d H:i:s'));
        $this->assertEquals(1, $shift->crew_only);
    }

    public function test_shift_destroy_through_the_nested_route_errors_and_keeps_the_shift_current_behaviour(): void
    {
        $schedule = $this->schedule();
        $shift = $this->shift($schedule);

        // destroy(Shift $timeScheduleShift) omits the {time_schedule} route parameter, so the schedule id
        // string is passed positionally into the Shift argument.
        $this->actingAs($this->actor(['time_schedule_shift_delete']))
            ->delete(route('admin.time-schedules.time-schedule-shifts.destroy', [$schedule, $shift]))
            ->assertStatus(500);

        $this->assertDatabaseHas('time_schedule_shifts', ['id' => $shift->id]);
    }

    public function test_shift_show_errors_before_any_permission_check_current_behaviour(): void
    {
        $schedule = $this->schedule();
        $shift = $this->shift($schedule);

        $this->actingAs($this->actor(['dashboard_access']))
            ->get(route('admin.time-schedules.time-schedule-shifts.show', [$schedule, $shift]))
            ->assertStatus(500);
    }

    public function test_shift_mass_destroy_deletes_only_the_selected_shifts(): void
    {
        $schedule = $this->schedule();
        $a = $this->shift($schedule, 'A');
        $keep = $this->shift($schedule, 'B');

        $this->actingAs($this->actor(['time_schedule_shift_delete']))
            ->delete(route('admin.time-schedule-shifts.massDestroy'), ['ids' => [$a->id]])
            ->assertNoContent();

        $this->assertDatabaseMissing('time_schedule_shifts', ['id' => $a->id]);
        $this->assertDatabaseHas('time_schedule_shifts', ['id' => $keep->id]);
    }

    public function test_every_shift_route_is_forbidden_for_a_role_without_shift_permissions(): void
    {
        $schedule = $this->schedule();
        $shift = $this->shift($schedule);
        $payload = ['name' => 'Evil', 'start' => '2026-07-01 20:00:00', 'end' => '2026-07-01 22:00:30', 'repeat' => '1', 'crew_only' => '0'];

        $this->assertAllForbidden($this->actor(['dashboard_access']), [
            ['get', route('admin.time-schedules.time-schedule-shifts.index', $schedule)],
            ['get', route('admin.time-schedules.time-schedule-shifts.create', $schedule)],
            ['get', route('admin.time-schedules.time-schedule-shifts.edit', [$schedule, $shift])],
            ['post', route('admin.time-schedules.time-schedule-shifts.store', $schedule), $payload],
            ['put', route('admin.time-schedules.time-schedule-shifts.update', [$schedule, $shift]), $payload],
            ['delete', route('admin.time-schedule-shifts.massDestroy'), ['ids' => [$shift->id]]],
        ]);

        $this->assertSame(1, Shift::count());
        $this->assertSame('Bar', $shift->refresh()->name);
    }

    public function test_every_shift_route_redirects_a_guest_to_login(): void
    {
        $schedule = $this->schedule();
        $shift = $this->shift($schedule);
        $payload = ['name' => 'Evil', 'start' => '2026-07-01 20:00:00', 'end' => '2026-07-01 22:00:30', 'repeat' => '1', 'crew_only' => '0'];

        $this->assertAllRedirectGuestsToLogin([
            ['get', route('admin.time-schedules.time-schedule-shifts.index', $schedule)],
            ['post', route('admin.time-schedules.time-schedule-shifts.store', $schedule), $payload],
            ['put', route('admin.time-schedules.time-schedule-shifts.update', [$schedule, $shift]), $payload],
            ['delete', route('admin.time-schedules.time-schedule-shifts.destroy', [$schedule, $shift])],
            ['delete', route('admin.time-schedule-shifts.massDestroy'), ['ids' => [$shift->id]]],
        ]);

        $this->assertSame(1, Shift::count());
        $this->assertSame('Bar', $shift->refresh()->name);
    }
}
