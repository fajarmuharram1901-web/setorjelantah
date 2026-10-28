@php
    $adaGalat = $errors->any();
@endphp

@if (session('success'))
    <div class="alert alert-success" data-snackbar="success">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="alert alert-danger" data-snackbar="error">{{ session('error') }}</div>
@endif

@if (session('status'))
    <div class="alert alert-success" data-snackbar="success">{{ session('status') }}</div>
@endif

@if ($adaGalat)
    <div class="alert alert-danger" role="alert">
        @if ($errors->count() === 1)
            {{ $errors->first() }}
        @else
            <ul style="margin:0;padding-left:18px;">
                @foreach ($errors->all() as $galat)
                    <li>{{ $galat }}</li>
                @endforeach
            </ul>
        @endif
    </div>
@endif