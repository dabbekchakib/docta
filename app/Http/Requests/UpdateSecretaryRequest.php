<?php

namespace App\Http\Requests;

use App\Enums\SecretaryGender;
use App\Enums\SecretaryStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSecretaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('secretary')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::enum(SecretaryGender::class)],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('secretaries', 'email')->ignore($this->route('secretary'))],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9\s-]{8,15}$/'],
            'mobile' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9\s-]{8,15}$/'],
            'cin' => ['nullable', 'string', 'max:255', Rule::unique('secretaries', 'cin')->ignore($this->route('secretary'))],
            'governorate' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'hire_date' => ['nullable', 'date'],
            'employee_number' => ['nullable', 'string', 'max:255', Rule::unique('secretaries', 'employee_number')->ignore($this->route('secretary'))],
            'notes' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(SecretaryStatus::class)],
        ];
    }
}
