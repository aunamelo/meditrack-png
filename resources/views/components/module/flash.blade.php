@if (session('success'))
    <div class="module-flash module-flash-success" role="status" aria-live="polite">{{ session('success') }}</div>
@endif
@if (session('info'))
    <div class="module-flash border border-blue-200 bg-blue-50 text-blue-800" role="status" aria-live="polite">{{ session('info') }}</div>
@endif
@if (session('error'))
    <div class="module-flash border border-rose-200 bg-rose-50 text-rose-800" role="alert" aria-live="assertive">{{ session('error') }}</div>
@endif
@if (isset($errors) && $errors->any())
    <div class="module-flash border border-rose-200 bg-rose-50 text-rose-800" role="alert" aria-live="assertive">
        <p class="font-semibold">Please fix the following:</p>
        <ul class="mt-1 list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
