<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;


class ApiRequest extends FormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @param Validator $validator
     * @return HttpResponseException
     */
    protected function failedValidation(Validator $validator): HttpResponseException
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400) // ✅ статус здесь
        );
    }
}
