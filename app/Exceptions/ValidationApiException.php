<?php

namespace App\Exceptions;

use App\Constants\StatusCodes;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ValidationApiException extends \Exception
{
    public string $error = '';

    public function render(Request $request): JsonResponse
    {
        return $this->validationErrors();
    }

    public function validationErrors(): JsonResponse
    {
        return response()->rest([
            'status' => [
                'code' => StatusCodes::VALIDATION_ERROR,
                'description' => 'Client validation errors',
                'error' => $this->error
            ]
        ], 400);
    }

    public static function fromValidator(Validator $validator): self
    {
        $exception = new self();
        $exception->error = $validator->errors()->first();

        return $exception;
    }
}
