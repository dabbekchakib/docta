<?php

namespace App\Http\Requests;

use App\Enums\PatientGender;
use App\Enums\PatientStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('patient')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $patient = $this->route('patient');

        return [
            'title' => ['nullable', Rule::in(['mr', 'mme', 'dr'])],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::enum(PatientGender::class)],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'cin' => ['nullable', 'string', 'max:255', Rule::unique('patients', 'cin')->ignore($patient)],
            'photo' => ['nullable', 'image', 'max:2048'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9\s-]{8,15}$/'],
            'phone_secondary' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9\s-]{8,15}$/'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('patients', 'email')->ignore($patient)],
            'governorate' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(PatientStatus::class)],
        ];
    }
}
