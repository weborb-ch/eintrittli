<div>
    @if ($registration)
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/30 mb-4">
                <x-filament::icon
                    icon="heroicon-o-check-circle"
                    class="w-6 h-6 text-green-600 dark:text-green-400"
                />
            </div>
            <h2 class="text-lg font-semibold text-gray-950 dark:text-white mb-1">Registration Successful</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Your confirmation code:</p>
            <div class="bg-gray-100 dark:bg-gray-700 rounded-lg px-4 py-3 inline-block mb-4">
                <span class="text-xl font-mono font-bold text-gray-950 dark:text-white tracking-wider">{{ $registration->confirmation_code }}</span>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Registered {{ $registration->created_at->format('F j, Y \a\t H:i') }}
            </p>
        </div>
    @elseif (!$event->isRegistrationOpen())
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-900/30 mb-4">
                <x-filament::icon
                    icon="heroicon-o-clock"
                    class="w-6 h-6 text-amber-600 dark:text-amber-400"
                />
            </div>
            <h2 class="text-lg font-semibold text-gray-950 dark:text-white mb-1">Registration Closed</h2>
            @if ($event->registration_opens_at && $event->registration_opens_at->isFuture())
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Opens {{ $event->registration_opens_at->format('F j, Y \a\t H:i') }}
                </p>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">Registration for this event has ended.</p>
            @endif
        </div>
    @elseif (!$event->form)
        <div class="text-center">
            <p class="text-sm text-gray-500 dark:text-gray-400">No registration form configured.</p>
        </div>
    @else
        <form wire:submit="submit">
            @error('form')
                <div class="mb-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-3 text-sm text-red-700 dark:text-red-400">
                    {{ $message }}
                </div>
            @enderror

            {{ $this->form }}

            <div class="mt-6">
                <x-filament::button type="submit" class="w-full" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submit">Register</span>
                    <span wire:loading wire:target="submit">Processing...</span>
                </x-filament::button>
            </div>
        </form>
    @endif
</div>
