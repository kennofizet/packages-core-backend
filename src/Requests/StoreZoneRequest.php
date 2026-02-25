<?php

namespace Kennofizet\PackagesCore\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'server_id' => 'nullable|integer',
        ];
    }
}
