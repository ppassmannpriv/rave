<?php

namespace Tests\Feature\Http\Controllers\User\Account;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    private function customer(bool $mayUpdateAccount = true): User
    {
        $user = User::factory()->create();

        if ($mayUpdateAccount) {
            $role = Role::create(['title' => 'Customer']);
            $role->permissions()->attach(Permission::create(['title' => 'customer_update_account'])->id);
            $user->roles()->attach($role->id);
        }

        return $user;
    }

    public function test_user_can_update_own_profile()
    {
        $user = $this->customer();

        $response = $this->actingAs($user)->post(route('user.profile.update'), [
            'name' => 'Test user',
            'email' => $user->email,
        ]);

        $response->assertRedirect(route('user.edit'));
        $this->assertEquals('Test user', $user->fresh()->name);
    }

    public function test_user_cannot_update_foreign_profile()
    {
        $user = $this->customer();
        $foreign_user = User::factory()->create(['name' => 'Foreign user']);

        $this->actingAs($user)->post(route('user.profile.update'), [
            'id' => $foreign_user->id,
            'name' => 'Test user',
            'email' => $user->email,
        ])->assertRedirect(route('user.edit'));

        $this->assertEquals('Foreign user', $foreign_user->fresh()->name);
        $this->assertEquals('Test user', $user->fresh()->name);
    }

    public function test_user_without_the_account_permission_cannot_update_profile()
    {
        $user = $this->customer(false);
        $name = $user->name;

        $this->actingAs($user)->post(route('user.profile.update'), [
            'name' => 'Test user',
            'email' => $user->email,
        ])->assertForbidden();

        $this->assertEquals($name, $user->fresh()->name);
    }

    public function test_user_can_change_own_password()
    {
        $user = $this->customer();

        $this->actingAs($user)->post(route('user.password.update'), [
            'password' => 'a-new-password',
            'password_confirmation' => 'a-new-password',
        ])->assertRedirect(route('user.edit'));

        $this->assertTrue(Hash::check('a-new-password', $user->fresh()->password));
    }

    public function test_password_change_needs_a_matching_confirmation()
    {
        $user = $this->customer();
        $hash = $user->password;

        $this->actingAs($user)->post(route('user.password.update'), [
            'password' => 'a-new-password',
            'password_confirmation' => 'something-else',
        ])->assertSessionHasErrors('password');

        $this->assertSame($hash, $user->fresh()->password);
    }

    public function test_guest_is_redirected_to_login()
    {
        $this->post(route('user.profile.update'), ['name' => 'x', 'email' => 'x@example.com'])
            ->assertRedirect(route('login'));
    }
}
