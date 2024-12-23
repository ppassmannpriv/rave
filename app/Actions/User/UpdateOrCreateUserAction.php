<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Str;
class UpdateOrCreateUserAction
{
    public function handle(array $userData): User
    {
        return User::updateOrCreate([
            'email' => $userData['email']],
            [
                'name' => $userData['firstname'] . ' ' . $userData['lastname'],
                'email' => $userData['email'],
                'street' => $userData['street'],
                'postcode' => $userData['postcode'],
                'city' => $userData['city'],
                'country' => $userData['country'],
                'password' => bcrypt(Str::random(20)),
                'remember_token' => null,
            ]);
    }
}
