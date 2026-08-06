<?php

namespace App\Http\Requests;

use App\Enums\DoctorGender;
use App\Enums\DoctorStatus;
use App\Models\Doctor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Doctor::class) ?? false;
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
            'gender' => ['required', Rule::enum(DoctorGender::class)],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('doctors', 'email')],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9\s-]{8,15}$/'],
            'mobile' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9\s-]{8,15}$/'],
            'speciality' => ['required', 'string', 'max:255'],
            'order_number' => ['nullable', 'string', 'max:255', Rule::unique('doctors', 'order_number')],
            'national_id' => ['nullable', 'string', 'max:255', Rule::unique('doctors', 'national_id')],
            'governorate' => ['nullable', 'string', 'max:255'],
            'consultation_fee' => ['nullable', 'numeric', 'min:0'],
            'consultation_duration' => ['nullable', 'integer', 'gt:0'],
            'start_working_date' => ['nullable', 'date'],
            'status' => ['required', Rule::enum(DoctorStatus::class)],
        ];
    }
}
