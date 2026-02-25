<?php

namespace Kennofizet\PackagesCore\Services\Validation;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ZoneValidationService
{
    /**
     * Validate create / update zone data.
     *
     * @throws ValidationException
     */
    public function validateZone(array $data): void
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'server_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
