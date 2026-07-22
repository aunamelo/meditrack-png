@if (session('success'))
    <div class="module-flash module-flash-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="module-flash module-flash-error">{{ session('error') }}</div>
@endif
