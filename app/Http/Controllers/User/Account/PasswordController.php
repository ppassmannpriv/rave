<?php

namespace App\Http\Controllers\User\Account;

use App\Http\Controllers\Site\WebController;
use App\Http\Requests\Site\UserAccountUpdateProfileRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\Site\UserAccountUpdatePasswordRequest;

class PasswordController extends WebController
{
    public function update(UserAccountUpdatePasswordRequest $request): \Illuminate\Http\RedirectResponse
    {
        auth()->user()->update($request->validated());

        return redirect()->route('user.edit')->with('message', __('global.change_password_success'));
    }

    public function updateProfile(UserAccountUpdateProfileRequest $request): \Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();

        $user->update($request->validated());

        return redirect()->route('user.edit')->with('message', __('global.update_profile_success'));
    }

    public function destroy(): \Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();

        $user->update([
            'email' => time() . '_' . $user->email,
        ]);

        $user->delete();

        return redirect()->route('login')->with('message', __('global.delete_account_success'));
    }
}
