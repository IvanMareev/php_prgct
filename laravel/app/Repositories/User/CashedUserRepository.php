<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

final class CashedUserRepository implements UserRepositoryInterface
{
    public function __construct(private readonly UserRepositoryInterface $repository) {}

    public function getUser(string $email): User
    {
        $cacheKey = 'user:'.$email;

        return Cache::tags(['user'])->remember($cacheKey, now()->addDay(), function () use ($email) {
            return $this->repository->getUser($email);
        });
    }
}
