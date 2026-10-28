<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return view('notifikasi.index', [
            'notifikasi' => $request->user()->notifications()->latest()->paginate(15),
        ]);
    }

    public function read(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->where('id', $id)->first();

        if ($notification && $notification->read_at === null) {
            $notification->markAsRead();
        }

        $data = $notification->data ?? [];
        $url = $data['url'] ?? route('dashboard');

        return redirect($url);
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return redirect()->route('notifikasi.index');
    }

    // GET /notifikasi/count (AJAX polling)
    public function count(Request $request)
    {
        return response()->json([
            'jumlah' => $request->user()->unreadNotifications->count(),
        ]);
    }
}