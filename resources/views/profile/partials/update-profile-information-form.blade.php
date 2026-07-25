<section>
    @php
        $justSaved = session('status') === 'profile-updated';
        $fieldValue = fn (string $field) => $justSaved ? '' : old($field, $user->{$field});
    @endphp

    <header class="mb-6 border-b border-line pb-4 dark:border-zinc-800">
        <h2 class="heading-section">Profile information</h2>
        <p class="mt-1 text-sm text-muted">Update your contact details and work information for the MediTrack PNG portal.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6"
          x-data="{
              previewUrl: @js($justSaved ? null : $user->profilePhotoUrl()),
              removePhoto: false,
              onFileChange(event) {
                  const file = event.target.files[0];
                  if (!file) return;
                  this.removePhoto = false;
                  this.previewUrl = URL.createObjectURL(file);
              },
              clearPhoto() {
                  this.previewUrl = null;
                  this.removePhoto = true;
                  this.$refs.photoInput.value = '';
              },
              init() {
                  if (@js($justSaved) && this.$refs.photoInput) {
                      this.$refs.photoInput.value = '';
                  }
              }
          }">
        @csrf
        @method('patch')

        <div class="rounded-xl border border-line bg-surface-muted/40 p-4 dark:border-zinc-800 dark:bg-zinc-900/40">
            <label class="form-label">Profile photo</label>
            <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-center">
                <template x-if="previewUrl">
                    <img :src="previewUrl" alt="Profile photo preview" class="h-20 w-20 shrink-0 rounded-2xl object-cover shadow-soft">
                </template>
                <template x-if="!previewUrl">
                    <x-user-avatar :user="$user" size="lg" />
                </template>
                <div class="flex-1 space-y-2">
                    <input type="file" name="profile_photo" id="profile_photo" accept="image/jpeg,image/png,image/webp" class="input-field" x-ref="photoInput" @change="onFileChange($event)">
                    <input type="hidden" name="remove_profile_photo" :value="removePhoto ? 1 : 0">
                    <p class="text-xs text-muted">JPG, PNG or WebP — max 2 MB. Shown in the top bar and profile overview.</p>
                    @if($user->profilePhotoUrl() && ! $justSaved)
                        <button type="button" class="text-xs font-medium text-rose-600 hover:underline" @click="clearPhoto()">Remove photo</button>
                    @endif
                    <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <label for="name" class="form-label">Full name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ $fieldValue('name') }}" required autofocus autocomplete="name" class="input-field">
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <label for="email" class="form-label">Email address <span class="text-red-500">*</span></label>
                <input type="email" name="email" id="email" value="{{ $fieldValue('email') }}" required autocomplete="username" class="input-field">
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="mt-2">
                        <p class="text-sm text-amber-700 dark:text-amber-300">
                            {{ __('Your email address is unverified.') }}
                            <button form="send-verification" class="font-medium text-brand-600 underline hover:text-brand-700">
                                {{ __('Re-send verification email') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 text-sm font-medium text-emerald-600 dark:text-emerald-400">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <div>
                <label for="phone" class="form-label">Phone / mobile</label>
                <input type="text" name="phone" id="phone" value="{{ $fieldValue('phone') }}" autocomplete="tel" placeholder="e.g. +675 7XXX XXXX" class="input-field">
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>

            <div>
                <label for="employee_id" class="form-label">Staff / employee ID</label>
                <input type="text" name="employee_id" id="employee_id" value="{{ $fieldValue('employee_id') }}" placeholder="e.g. NDoH-2024-001" class="input-field">
                <x-input-error class="mt-2" :messages="$errors->get('employee_id')" />
            </div>

            <div>
                <label for="job_title" class="form-label">Job title</label>
                <input type="text" name="job_title" id="job_title" value="{{ $fieldValue('job_title') }}" placeholder="e.g. Senior Procurement Officer" class="input-field">
                <x-input-error class="mt-2" :messages="$errors->get('job_title')" />
            </div>

            <div>
                <label for="facility" class="form-label">Work facility</label>
                <input type="text" name="facility" id="facility" value="{{ $fieldValue('facility') }}" placeholder="{{ $user->facilityLabel() }}" class="input-field">
                <p class="mt-1 text-xs text-muted">Leave blank to use your default facility for this role.</p>
                <x-input-error class="mt-2" :messages="$errors->get('facility')" />
            </div>
        </div>

        <div class="flex items-center gap-4 border-t border-line pt-4 dark:border-zinc-800">
            <button type="submit" class="btn-brand text-xs uppercase tracking-wider">Save profile</button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-sm text-emerald-600 dark:text-emerald-400"
                >Profile saved.</p>
            @endif
        </div>
    </form>
</section>
