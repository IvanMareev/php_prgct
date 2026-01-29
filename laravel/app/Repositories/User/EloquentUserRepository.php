<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Repositories\User\UserRepositoryInterface;
use App\Models\User;

final class EloquentUserRepository implements UserRepositoryInterface
{

    public function getUser(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }
}