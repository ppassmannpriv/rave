<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateOrCreateUserAction
{
    use AsAction;

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
