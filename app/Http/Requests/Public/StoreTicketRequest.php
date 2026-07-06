<?php

namespace App\Http\Requests\Public;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Form publik.
    }

    public function rules(): array
    {
        $maxKb = (int) config('helpdesk.attachments.max_size_kb');
        $mimes = implode(',', config('helpdesk.attachments.allowed_mimes'));
        $maxFiles = (int) config('helpdesk.attachments.max_files');

        return [
            'reporter_name' => ['required', 'string', 'max:150'],
            'reporter_nip' => ['nullable', 'string', 'max:30'],
            'reporter_email' => ['required', 'email', 'max:150'],
            'reporter_whatsapp' => ['nullable', 'string', 'max:30'],
            'category_id' => ['required', Rule::exists('categories', 'id')->where('is_active', true)],
            'subcategory_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string', 'max:5000'],
            'priority' => ['nullable', Rule::in(['rendah', 'sedang', 'tinggi'])],
            'consent' => ['accepted'], // Persetujuan penggunaan data pribadi (Bagian 9).
            'attachments' => ['nullable', 'array', "max:{$maxFiles}"],
            'attachments.*' => ['file', "mimes:{$mimes}", "max:{$maxKb}"],
            // Honeypot anti-spam (Bagian 8): harus kosong.
            'website' => ['prohibited'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Subkategori harus milik kategori yang dipilih.
            $subId = $this->input('subcategory_id');
            if ($subId) {
                $valid = Subcategory::where('id', $subId)
                    ->where('category_id', $this->input('category_id'))
                    ->where('is_active', true)
                    ->exists();
                if (! $valid) {
                    $validator->errors()->add('subcategory_id', 'Subkategori tidak sesuai dengan kategori yang dipilih.');
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'reporter_name' => 'nama lengkap',
            'reporter_email' => 'alamat surel',
            'reporter_nip' => 'NIP',
            'reporter_whatsapp' => 'nomor WhatsApp',
            'category_id' => 'kategori layanan',
            'subcategory_id' => 'subkategori',
            'title' => 'judul kendala',
            'description' => 'uraian kendala',
            'consent' => 'persetujuan penggunaan data',
        ];
    }

    public function messages(): array
    {
        return [
            'consent.accepted' => 'Anda harus menyetujui penggunaan data pribadi sebelum mengirim tiket.',
            'website.prohibited' => 'Pengiriman terdeteksi sebagai spam.',
        ];
    }

    /** Data tiket yang bersih untuk disimpan (tanpa lampiran/consent/honeypot). */
    public function ticketData(): array
    {
        return [
            'reporter_name' => $this->input('reporter_name'),
            'reporter_nip' => $this->input('reporter_nip'),
            'reporter_email' => $this->input('reporter_email'),
            'reporter_whatsapp' => $this->input('reporter_whatsapp'),
            'category_id' => $this->input('category_id'),
            'subcategory_id' => $this->input('subcategory_id') ?: null,
            'title' => $this->input('title'),
            'description' => $this->input('description'),
            'priority' => $this->input('priority', 'sedang'),
        ];
    }
}
