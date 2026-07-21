@php
    $isEdit = isset($user);
    $formAction = $isEdit ? getDashboardUserRoute('update', $user) : getDashboardUserRoute('store');
    $selectedRole = old('role', $currentRole ?? array_key_first($roleOptions));
@endphp

<form action="{{ $formAction }}" method="POST">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-md border border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-950/40">
            <h3 class="text-sm font-medium text-red-800 dark:text-red-300">There were some errors with your submission.</h3>
            <ul class="mt-2 list-inside list-disc text-sm text-red-700 dark:text-red-400">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div>
            <label for="name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-zinc-300">Full name <span class="text-red-500">*</span></label>
            <input type="text" name="name" id="name" value="{{ old('name', $user->name ?? '') }}" required
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="mb-1 block text-sm font-medium text-gray-700 dark:text-zinc-300">Email address <span class="text-red-500">*</span></label>
            <input type="email" name="email" id="email" value="{{ old('email', $user->email ?? '') }}" required
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-medium text-gray-700 dark:text-zinc-300">
                Password @if(!$isEdit)<span class="text-red-500">*</span>@else<span class="text-gray-500">(leave blank to keep current)</span>@endif
            </label>
            <input type="password" name="password" id="password" {{ $isEdit ? '' : 'required' }}
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="mb-1 block text-sm font-medium text-gray-700 dark:text-zinc-300">Confirm password @if(!$isEdit)<span class="text-red-500">*</span>@endif</label>
            <input type="password" name="password_confirmation" id="password_confirmation" {{ $isEdit ? '' : 'required' }}
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
        </div>

        <div class="md:col-span-2">
            <label for="role" class="mb-1 block text-sm font-medium text-gray-700 dark:text-zinc-300">Role <span class="text-red-500">*</span></label>
            @if(count($roleOptions) === 1)
                @php $onlyRole = array_key_first($roleOptions); @endphp
                <input type="hidden" name="role" value="{{ $onlyRole }}">
                <p class="rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                    {{ $roleOptions[$onlyRole] }}
                </p>
            @else
                <select name="role" id="role" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
                    @foreach($roleOptions as $value => $label)
                        <option value="{{ $value }}" @selected($selectedRole === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            @endif
            @error('role')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="mt-8 flex items-center justify-end gap-3">
        <a href="{{ getDashboardUserRoute('index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800">
            Cancel
        </a>
        <button type="submit" class="inline-flex items-center rounded-md border border-transparent bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2">
            {{ $isEdit ? 'Save changes' : 'Create user' }}
        </button>
    </div>
</form>
