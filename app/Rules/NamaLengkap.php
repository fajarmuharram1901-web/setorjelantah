<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Nama orang hanya boleh berisi huruf.
 *
 * Angka dan simbol ditolak, tapi tanda baca yang lazim pada nama orang
 * diperbolehkan: spasi, tanda hubung (Anne-Marie), apostrof (Ma'ruf),
 * dan titik pada nama gelar (St. Anne).
 *
 * Aksen huruf non-Latin juga diterima supaya nama seperti "Siti"
 * atau "Budi" tetap valid, begitu pula huruf berbahasa lain.
 */
class NamaLengkap implements ValidationRule
{
    /** Huruf (termasuk beraksen) + spasi + tanda hubung, apostrof, dan titik. */
    private const POLA = '/^[\p{L}\s\-’\'.]+$/u';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('Kolom :attribute harus berupa teks.');

            return;
        }

        $nilai = trim($value);

        if ($nilai === '') {
            // biarkan aturan "required" yang menanganinya
            return;
        }

        if (preg_match(self::POLA, $nilai) !== 1) {
            $fail(':attribute hanya boleh berisi huruf. Angka dan simbol tidak diperbolehkan.');

            return;
        }

        // Cegah nama yang hanya berisi spasi atau tanda baca, misalnya "..." atau "- -".
        if (preg_match('/^[\s\-’\'.]+$/u', $nilai) === 1) {
            $fail(':attribute harus mengandung minimal satu huruf.');
        }
    }

    /**
     * Pesan versi peramban, dikirim ke JavaScript lewat AturanValidasi.
     */
    public static function pesanPeramban(): string
    {
        return 'Nama lengkap hanya boleh berisi huruf. Angka dan simbol tidak diperbolehkan.';
    }
}
