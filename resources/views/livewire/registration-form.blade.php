<div class="max-w-2xl mx-auto">
    @if ($registration)
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-8 text-center">
            <x-filament::icon
                icon="heroicon-o-check-circle"
                class="w-16 h-16 text-success-500 mx-auto mb-4"
            />
            <h2 class="text-2xl font-bold text-gray-950 dark:text-white mb-2">Registration Successful!</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Your confirmation code:</p>
            <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-4 inline-block mb-4">
                <span class="text-3xl font-mono font-bold text-gray-900 dark:text-white tracking-wider">{{ $registration->confirmation_code }}</span>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Registered on {{ $registration->created_at->format('F j, Y \a\t H:i') }}
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Please save this code for your records.</p>
        </div>
    @elseif (!$event->isRegistrationOpen())
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-8 text-center">
            <x-filament::icon
                icon="heroicon-o-exclamation-triangle"
                class="w-16 h-16 text-warning-500 mx-auto mb-4"
            />
            <h2 class="text-2xl font-bold text-gray-950 dark:text-white mb-2">Registration Closed</h2>
            @if ($event->registration_opens_at && $event->registration_opens_at->isFuture())
                <p class="text-gray-600 dark:text-gray-400">Registration opens on {{ $event->registration_opens_at->format('F j, Y \a\t H:i') }}</p>
            @else
                <p class="text-gray-600 dark:text-gray-400">Registration for this event has ended.</p>
            @endif
        </div>
    @elseif (!$event->form)
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-8 text-center">
            <p class="text-gray-600 dark:text-gray-400">No registration form configured for this event.</p>
        </div>
    @else
        <form wire:submit="submit">
            @error('form')
                <div class="mb-4 rounded-lg bg-danger-50 p-4 text-sm text-danger-600 dark:bg-danger-400/10 dark:text-danger-400">
                    {{ $message }}
                </div>
            @enderror

            {{ $this->form }}

            <div class="mt-6">
                <x-filament::button type="submit" class="w-full" wire:loading.attr="disabled">
                    <span wire:loading.remove>Register</span>
                    <span wire:loading>Processing...</span>
                </x-filament::button>
            </div>
        </form>
    @endif
</div>
