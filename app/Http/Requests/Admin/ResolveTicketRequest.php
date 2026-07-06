<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ResolveTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Otorisasi tiket ditangani di controller via policy.
        return $this->user() !== null;
    }

    public function rules(): array
    {
        // Ketiga kolom penyelesaian wajib (PRD Bagian 5.7).
        return [
            'analysis' => ['required', 'string', 'max:5000'],
            'follow_up' => ['required', 'string', 'max:5000'],
            'resolution' => ['required', 'string', 'max:5000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'analysis' => 'analisis permasalahan',
            'follow_up' => 'tindak lanjut',
            'resolution' => 'penyelesaian',
        ];
    }
}
