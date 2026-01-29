<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\User\DTO\CreateTokenData;
use App\Services\User\UserService;
use Symfony\Component\HttpFoundation\Response;


class UserController extends Controller
{
    public function __construct(private readonly UserService $userService)
    {
    }
    public function login(LoginRequest $request)
    {
        $dto = new CreateTokenData(
            email: $request->validated('email'),
            password: $request->validated('password'),
        );

        $token = $this->userService->getAccessToken($dto);

        if ($token === false) {
            return responseFailed('Неверные учетные данные', Response::HTTP_BAD_REQUEST);
        } else {
            return response()->json([
                'access_token' => $token,
            ], Response::HTTP_OK);
        }

    }
}
