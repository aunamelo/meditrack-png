@props([
    'href' => '#',
    'active' => false,
    'icon' => 'home',
    'label' => '',
    'description' => null,
    'badge' => null,
])

<a href="{{ $href }}"
   title="{{ $label }}"
   @class([
       'sidebar-link group flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-semibold transition',
       'bg-white/15 text-white shadow-sm backdrop-blur-sm' => $active,
       'text-white/65 hover:bg-white/10 hover:text-white' => ! $active,
   ])>
    <span @class([
        'relative flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition',
        'bg-white/20 text-white' => $active,
        'bg-white/10 text-white/70 group-hover:bg-white/15 group-hover:text-white' => ! $active,
    ])>
        @include('components.icons.'.$icon)
        @if($badge)
            <span class="sidebar-link-badge-dot absolute -right-0.5 -top-0.5 h-2 w-2 rounded-full bg-accent ring-2 ring-brand-800"></span>
        @endif
    </span>
    <span class="sidebar-link-label min-w-0 flex-1 truncate">{{ $label }}</span>
    @if($badge)
        <span class="sidebar-link-badge-count inline-flex min-w-[1.25rem] shrink-0 items-center justify-center rounded-full bg-accent px-1.5 py-0.5 text-[10px] font-bold text-white">{{ $badge }}</span>
    @endif
</a>
