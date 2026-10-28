<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Support\AturanValidasi;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Aturan dan pesan diambil dari AturanValidasi, sumber yang sama dengan
     * validasi di peramban.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return AturanValidasi::siap('masuk')['aturan'];
    }

    /**
     * Pesan validasi berbahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return AturanValidasi::siap('masuk')['pesan'];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * Pesan dibedakan supaya pengguna tahu masalahnya: email belum terdaftar
     * atau kata sandinya yang salah.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $email = trim((string) $this->string('email'));

        // 1. Cek apakah email-nya terdaftar sama sekali.
        $pengguna = User::where('email', $email)->first();

        if (! $pengguna) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Email ini belum terdaftar. Silakan daftar terlebih dahulu atau gunakan email lain.',
            ]);
        }

        // 2. Email ada, sekarang periksa kata sandinya.
        if (! Auth::attempt(['email' => $email, 'password' => (string) $this->string('password')], $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'password' => 'Kata sandi salah. Silakan periksa kembali dan coba lagi.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $detik = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => 'Terlalu banyak percobaan masuk. Coba lagi dalam ' . ceil($detik / 60) . ' menit.',
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(
            Str::lower((string) $this->string('email')) . '|' . $this->ip()
        );
    }
}
