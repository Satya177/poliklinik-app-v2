<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadBuktiPembayaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        $periksa = $this->route('periksa');

        return $periksa !== null
            && $periksa->daftarPoli?->id_pasien === $this->user()?->id;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bukti_bayar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'bukti_bayar.required' => 'Foto bukti pembayaran wajib diunggah.',
            'bukti_bayar.image' => 'File harus berupa gambar.',
            'bukti_bayar.mimes' => 'Format gambar harus jpg, jpeg, png, atau webp.',
            'bukti_bayar.max' => 'Ukuran gambar maksimal 2 MB.',
        ];
    }
}
