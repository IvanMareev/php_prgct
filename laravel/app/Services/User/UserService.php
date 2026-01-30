<?php
declare(strict_types=1);

namespace App\Services\User;

use App\Repositories\User\EloquentUserRepository;
use App\Services\User\DTO\CreateTokenData;
use Illuminate\Support\Facades\Hash;


final class UserService
{

    public function __construct(
        private readonly EloquentUserRepository $userRepository,
    ) {
    }
    public function getAccessToken(CreateTokenData $data):string|false
    {
        $user = $this->userRepository->getUser($data->email);
        if (!Hash::check($data->password, $user->password)) {
            return false;

        }

        return $user->createToken('login')->plainTextToken;
    }
}
