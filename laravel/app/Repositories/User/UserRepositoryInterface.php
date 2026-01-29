<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Models\User;


interface UserRepositoryInterface
{
    public function getUser(string $email): User;
}