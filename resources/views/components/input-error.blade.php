@props(['messages'])

<div {{ $attributes->merge(['class' => 'text-sm text-red-700', 'role' => 'alert']) }} @if (! $messages) hidden @endif>
    <ul class="space-y-1">
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
</div>
