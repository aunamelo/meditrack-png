@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-line focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm']) }}>
