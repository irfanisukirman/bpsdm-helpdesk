<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class TrackTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ticket_number' => ['required', 'string', 'max:30'],
            'reporter_email' => ['required', 'email', 'max:150'],
        ];
    }

    public function attributes(): array
    {
        return [
            'ticket_number' => 'nomor tiket',
            'reporter_email' => 'alamat surel',
        ];
    }
}
