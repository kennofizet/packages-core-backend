<?php

namespace Kennofizet\PackagesCore\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServerManagerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|integer',
            'server_id' => 'nullable|integer',
        ];
    }
}
