<?php

namespace App\Http\Controllers\Auth;

use App\Support\AturanValidasi;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validasi = AturanValidasi::siap('gantiSandi');
        $validated = $request->validateWithBag('updatePassword', $validasi['aturan'], $validasi['pesan']);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }
}
