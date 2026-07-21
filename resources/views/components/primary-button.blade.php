<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-brand text-xs uppercase tracking-widest']) }}>
    {{ $slot }}
</button>
