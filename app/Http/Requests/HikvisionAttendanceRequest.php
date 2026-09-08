<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class HikvisionAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Token auth handled by HikvisionTokenAuth middleware
    }

    public function rules(): array
    {
        return [
            'device_id'      => ['required', 'string', 'max:50'],
            'employee_no'    => ['required', 'string', 'max:50'],
            'employee_name'  => ['nullable', 'string', 'max:255'],
            'event_time'     => ['required', 'string'],
            'serial_no'      => ['required', 'integer', 'min:0'],
            'major'          => ['required', 'integer', 'min:0'],
            'minor'          => ['required', 'integer', 'min:0'],
            'verify_mode'    => ['required', 'string', 'max:100'],
            'door_no'        => ['required', 'integer', 'min:0'],
            'card_reader_no' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'event_time.required' => 'event_time is required (ISO 8601 with timezone offset).',
            'serial_no.required'  => 'serial_no is required for idempotency.',
        ];
    }

    // Return JSON 422 instead of redirect on validation failure
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'error'   => 'Validation failed.',
                'details' => $validator->errors(),
            ], 422)
        );
    }
}
