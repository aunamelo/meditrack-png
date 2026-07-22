@props(['colspan' => 1, 'title' => 'Nothing here yet', 'description' => 'Records will appear once activity starts in this module.', 'actionUrl' => null, 'actionLabel' => null])

<tr>
    <td colspan="{{ $colspan }}" class="module-empty-cell">
        <div class="module-empty">
            <div class="module-empty-icon">
                <x-dashboard.icon name="clipboard" class="h-6 w-6" />
            </div>
            <p class="text-sm font-semibold text-ink dark:text-zinc-200">{{ $title }}</p>
            <p class="mt-1 text-sm text-muted">{{ $description }}</p>
            @if($actionUrl && $actionLabel)
                <a href="{{ $actionUrl }}" class="btn-brand mt-4 text-xs">{{ $actionLabel }}</a>
            @endif
        </div>
    </td>
</tr>
