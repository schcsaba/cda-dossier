<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadStoreRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $allowedClientApps = config('allowed_client_app');
        return [
            'client_app' => [
                'required',
                'numeric',
                'integer',
                Rule::in(array_keys($allowedClientApps)),
            ],
            'parameters' => 'required|array|min:2',
            'parameters.client_id' => 'required|numeric|integer',
            'parameters.user_id' => 'required|numeric|integer',
            'files' => 'required|array|min:1',
            'files.*.content' => 'required|starts_with:data:text/plain;base64,|min:27'
        ];
    }
}
