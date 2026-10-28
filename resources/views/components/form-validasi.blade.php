@props([
    'form',
    'pengganti' => [],
])

{{-- Mengirim aturan validasi ke peramban dalam bentuk JSON.
     Dipasangkan dengan <form data-validasi data-form="..."> dan public/js/validasi.js. --}}
<script type="application/json" data-aturan="{{ $form }}">@json(\App\Support\AturanValidasi::untukPeramban($form, $pengganti))</script>
