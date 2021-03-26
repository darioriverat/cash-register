<?php

namespace App\Exceptions;

use App\Constants\StatusCodes;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;

class CustomException extends \Exception
{
    public string $error = '';

    public function render(Request $request)
    {
        return $this->validationErrors();
    }

    public function validationErrors()
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
