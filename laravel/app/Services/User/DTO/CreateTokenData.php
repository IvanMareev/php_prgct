<?php
namespace App\Services\User\DTO;


final class CreateTokenData
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {}

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password
        ];
    }
}
