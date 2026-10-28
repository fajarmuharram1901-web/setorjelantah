<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotifikasiResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Endpoint notifikasi untuk semua role. */
class NotifikasiController extends Controller
{
    // GET /api/notifikasi
    public function index(Request $request)
    {
        $daftar = $request->user()->notifications()->latest()->paginate($request->integer('per_page', 20));

        return NotifikasiResource::collection($daftar);
    }

    // GET /api/notifikasi/count
    public function count(Request $request): JsonResponse
    {
        return response()->json(['jumlah' => $request->user()->unreadNotifications->count()]);
    }

    // POST /api/notifikasi/{id}/read
    public function read(Request $request, string $id): JsonResponse
    {
        $notifikasi = $request->user()->notifications()->where('id', $id)->first();

        if (! $notifikasi) {
            return response()->json(['message' => 'Notifikasi tidak ditemukan.'], 404);
        }

        if ($notifikasi->read_at === null) {
            $notifikasi->markAsRead();
        }

        return (new NotifikasiResource($notifikasi->fresh()))
            ->additional(['message' => 'Notifikasi ditandai sudah dibaca.'])
            ->response();
    }

    // POST /api/notifikasi/read-all
    public function readAll(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'message' => 'Semua notifikasi ditandai sudah dibaca.',
            'jumlah' => $request->user()->unreadNotifications->count(),
        ]);
    }
}