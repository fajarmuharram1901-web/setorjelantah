<?php

namespace App\Http\Requests;

use App\Support\AturanValidasi;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Aturan dan pesan diambil dari AturanValidasi, sumber yang sama dengan
     * validasi peramban, supaya pesan di layar dan di server tidak berbeda.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return AturanValidasi::siap('profil', ['id' => $this->user()->id])['aturan'];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return AturanValidasi::siap('profil', ['id' => $this->user()->id])['pesan'];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama lengkap',
            'email' => 'email',
            'no_hp' => 'nomor HP',
            'alamat' => 'alamat',
        ];
    }
}
