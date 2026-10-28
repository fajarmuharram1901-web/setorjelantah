@props([
    'title' => 'Belum ada data',
    'message' => null,
    'icon' => 'inbox',
    'action' => null,
    'actionLabel' => null,
    'actionRoute' => null,
])

<div class="empty">
    <x-icon :name="$icon" :size="34" />
    <h3>{{ $title }}</h3>
    @if ($message)
        <p>{{ $message }}</p>
    @endif
    @if ($actionRoute && $actionLabel)
        <a href="{{ $actionRoute }}" class="btn btn-sm">{{ $actionLabel }}</a>
    @elseif ($action)
        {{ $action }}
    @endif
</div>
