@props(['active'])

@php
    $classes = ($active ?? false)
        ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-kejati-gold text-start text-base font-medium text-kejati bg-green-50 focus:outline-none focus:text-kejati-dark focus:bg-green-50 focus:border-kejati transition duration-150 ease-in-out'
        : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-slate-600 hover:text-kejati-ink hover:bg-stone-50 hover:border-stone-300 focus:outline-none focus:text-kejati-ink focus:bg-stone-50 focus:border-stone-300 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>