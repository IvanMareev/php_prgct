<?php


use Illuminate\Http\JsonResponse;

function resOk()
{
    return response()->json(['message' => 'Successfully']);
}

function responseFailed(string $message, int $code = 400): JsonResponse
{
    $message = (string) $message;
    return response()->json(['message' => $message], $code);
}

function transMessage(string $code): string
{
    return __("messages.$code");
}
