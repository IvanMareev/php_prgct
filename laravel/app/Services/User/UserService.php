<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Services\User\DTO\CreateTokenData;
use App\Repositories\User\EloquentUserRepository;


final class UserService
{

    public function __construct(
        private readonly EloquentUserRepository $userRepository,
    ) {
    }
    public function getAccessToken(CreateTokenData $data):string|false
    {
        $user = $this->userRepository->getUser($data->email);
        if (!$user || !Hash::check($data->password, $user->password)) {
            return false;

        }

        $token = $user->createToken('login')->plainTextToken;

        return $token;
    }
}