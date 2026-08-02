@props([
    'for' => null,
    'required' => false,
    'optional' => false,
])

<label
    @if($for) for="{{ $for }}" @endif
    {{ $attributes->merge(['class' => 'form-label']) }}
>
    {{ $slot }}
    @if($required)
        <span class="text-red-500" aria-hidden="true">*</span>
        <span class="sr-only">(required)</span>
    @elseif($optional)
        <span class="font-normal normal-case tracking-normal text-muted">(optional)</span>
    @endif
</label>
