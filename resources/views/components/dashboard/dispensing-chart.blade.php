@props(['config', 'url' => null, 'drugs' => []])

<div
    {{ $attributes->merge(['class' => 'medcare-panel overflow-hidden']) }}
    x-data="dashboardDispensingChart(@js(['config' => $config, 'url' => $url, 'drugs' => $drugs]))"
>
    <div class="border-b border-line px-3.5 py-2.5">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h3 class="text-sm font-semibold text-ink dark:text-zinc-100" x-text="config.title">{{ $config['title'] }}</h3>
                <p class="mt-0.5 text-[11px] text-muted" x-text="config.subtitle">{{ $config['subtitle'] }}</p>
            </div>
            @if($url)
                <label class="sr-only" for="dispensing-drug-filter">Filter by medicine</label>
                <select
                    id="dispensing-drug-filter"
                    class="input-field max-w-[14rem] text-xs"
                    x-model="drug"
                    @change="reload()"
                >
                    <option value="">All medicines</option>
                    @foreach($drugs as $drug)
                        <option value="{{ $drug }}">{{ $drug }}</option>
                    @endforeach
                </select>
            @endif
        </div>
    </div>

    <div class="relative h-56 px-2 py-3 sm:h-64">
        <canvas x-ref="canvas" aria-label="{{ $config['title'] }}"></canvas>
    </div>
</div>
