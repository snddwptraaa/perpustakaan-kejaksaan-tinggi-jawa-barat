@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-stone-300 focus:border-kejati focus:ring-kejati rounded-md shadow-sm']) }}>
