<?php

namespace Tests\Feature\Http\Controllers\User\Account;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    public function user_can_update_own_profile()
    {
        $user = factory(User::class)->create();
        $response = $this->actingAs($user)->post(route('user.password.updateProfile', $user), [
            'name' => 'Test user',
        ]);

        $response->assertSuccessful();
        $user->refresh();
        $this->assertEquals('Test user', $user->name);
    }

    public function user_cannot_update_foreign_profile()
    {
        $user = factory(User::class)->create();
        $foreign_user = factory(User::class)->create([
            'name' => 'Foreign user',
        ]);
        $response = $this->actingAs($user)->post(route('user.password.updateProfile', $foreign_user), [
            'name' => 'Test user',
        ]);

        $response->assertForbidden();
        $foreign_user->refresh();
        $this->assertEquals('Foreign user', $foreign_user->name);
    }
}
