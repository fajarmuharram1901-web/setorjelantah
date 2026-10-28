<?php

namespace App\Support;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Bantu menyusun daftar sumber daya untuk respons JSON.
 *
 * Menaruh `JsonResource::collection()` langsung ke dalam `response()->json()`
 * akan diratakan hasil ke daftar polos dan metadata paginasi ikut hilang.
 * `of()` mengubah resource itu menjadi array biasa yang sudah berisi
 * `data`, `links`, dan `meta`, sehingga bentuknya sama dengan resource yang
 * dikembalikan langsung dan jauh lebih mudah dibaca sisi klien.
 */
class ApiData
{
    /**
     * @return array<string, mixed>
     */
    public static function of(JsonResource $resource): array
    {
        return $resource->response()->getData(true);
    }

    /**
     * Daftar polos tanpa metadata, untuk data yang memang tidak dipaginasikan.
     *
     * @return array<int, mixed>
     */
    public static function polos(JsonResource $resource): array
    {
        return array_values($resource->resolve());
    }
}