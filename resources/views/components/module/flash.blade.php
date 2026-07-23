@if (session('success'))
    <div class="module-flash module-flash-success">{{ session('success') }}</div>
@endif
@if (session('info'))
    <div class="module-flash border border-blue-200 bg-blue-50 text-blue-800">{{ session('info') }}</div>
@endif
