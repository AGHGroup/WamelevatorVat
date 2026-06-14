<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class OracleUserProvider extends EloquentUserProvider
{
    /**
     * Retrieve a user by their USER_ID credential.
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        $userId = $credentials['user_id'] ?? null;
        if (! $userId) return null;

        return User::where('USER_ID', $userId)
                   ->where('DEL_FLAG', '!=', 1)
                   ->first();
    }

    /**
     * The USERS table stores plain-text passwords (legacy Oracle system).
     * Compare directly; no bcrypt involved.
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        $input    = $credentials['password'] ?? '';
        $stored   = $user->getAuthPassword();

        // Plain-text comparison for legacy system
        if ($stored === $input) return true;

        // Fallback: try bcrypt in case some users were migrated
        return password_verify($input, $stored);
    }
}
