@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-kejati-ink']) }}>
    {{ $value ?? $slot }}
</label>