<?php

namespace App\Http\Controllers\Auth;

use App\Support\AturanValidasi;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Tampilkan formulir lupa kata sandi.
     */
    public function create(Request $request): View
    {
        // Email yang tadi diketik dipertahankan agar tidak hilang.
        return view('auth.forgot-password', [
            'emailLama' => (string) $request->session()->get('email_reset', ''),
        ]);
    }

    /**
     * Kirim tautan set ulang kata sandi ke email yang diminta.
     *
     * Kegagalan koneksi SMTP ditangkap dan diubah jadi pesan yang bisa
     * dipahami pengguna, bukan halaman 500. Penyebab sebenarnya dicatat di
     * log supaya mudah ditelusuri.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validasi = AturanValidasi::siap('lupaSandi');
        $request->validate($validasi['aturan'], $validasi['pesan']);

        $email = trim((string) $request->string('email'));

        try {
            $status = Password::sendResetLink(['email' => $email]);
        } catch (\Throwable $e) {
            // Mailer gagal: SMTP mati, tidak ada internet, atau kredensial ditolak.
            Log::error('Gagal mengirim tautan reset kata sandi ke ' . $email . ': ' . $e->getMessage());

            return back()
                ->withInput(['email' => $email])
                ->withErrors([
                    'email' => 'Email gagal dikirim. ' . $this->petunjukGagal($e)
                        . ' Silakan coba lagi beberapa saat lagi.',
                ]);
        }

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with(
                'status',
                'Tautan set ulang kata sandi sudah dikirim ke ' . $email
                    . '. Silakan cek kotak masuk dan folder spam.'
            );
        }

        // Email tidak terdaftar pada sistem.
        return back()
            ->withInput(['email' => $email])
            ->withErrors([
                'email' => $status === Password::RESET_THROTTLED
                    ? 'Terlalu banyak permintaan. Coba lagi beberapa saat lagi.'
                    : 'Email ini belum terdaftar pada sistem kami.',
            ]);
    }

    /**
     * Terjemahkan galat mailer menjadi petunjuk yang bisa dicoba pengguna.
     */
    private function petunjukGagal(\Throwable $e): string
    {
        $pesan = $e->getMessage();

        if (preg_match('/535|534|authentication|credentials|Username|Password/i', $pesan)) {
            return 'Server email menolak kredensial pengirim.';
        }

        if (preg_match('/timed out|resolve|failed to connect|network/i', $pesan)) {
            return 'Server email tidak dapat dihubungi, cek koneksi internet.';
        }

        return 'Terjadi gangguan pada server email.';
    }
}
