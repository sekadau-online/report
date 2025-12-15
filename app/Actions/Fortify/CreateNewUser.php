<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use PragmaRX\Google2FA\Google2FA;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ];

        // If users already exist, require 2FA code from an existing user
        if ($this->requiresInviteCode()) {
            $rules['invite_code'] = ['required', 'string', 'size:6'];
        }

        Validator::make($input, $rules)->validate();

        // Validate the 2FA code if required
        if ($this->requiresInviteCode()) {
            $this->validateInviteCode($input['invite_code'] ?? '');
        }

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);
    }

    /**
     * Check if registration requires an invite code (2FA from existing user).
     */
    public static function requiresInviteCode(): bool
    {
        return User::exists();
    }

    /**
     * Validate the invite code against existing users with 2FA enabled.
     */
    private function validateInviteCode(string $code): void
    {
        $usersWithTwoFactor = User::whereNotNull('two_factor_secret')->get();

        if ($usersWithTwoFactor->isEmpty()) {
            throw ValidationException::withMessages([
                'invite_code' => [__('Tidak ada user dengan 2FA aktif. Hubungi administrator.')],
            ]);
        }

        $google2fa = new Google2FA;

        foreach ($usersWithTwoFactor as $user) {
            $secret = decrypt($user->two_factor_secret);

            if ($google2fa->verifyKey($secret, $code)) {
                return; // Valid code found
            }
        }

        throw ValidationException::withMessages([
            'invite_code' => [__('Kode undangan tidak valid.')],
        ]);
    }
}
