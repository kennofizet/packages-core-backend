<?php

namespace Kennofizet\PackagesCore\Services\Validation;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Kennofizet\PackagesCore\Models\User;

class ServerManagerValidationService
{
    /**
     * Validate assign/remove manager data.
     *
     * @throws ValidationException
     */
    public function validateAssign(array $data): void
    {
        $userTable = (new User())->getTable();

        $validator = Validator::make($data, [
            'user_id' => ['required', 'integer', "exists:{$userTable},id"],
            'server_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
