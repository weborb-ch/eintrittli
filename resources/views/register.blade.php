<x-layouts.app :title="$event->name">
    <div class="min-h-[80vh] flex items-center justify-center">
        <div class="w-full max-w-md">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-950 dark:text-white">{{ $event->name }}</h1>
                @if ($event->registration_closes_at)
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Registration closes {{ $event->registration_closes_at->format('F j, Y \a\t H:i') }}
                    </p>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6">
                <livewire:registration-form :event="$event" />
            </div>
        </div>
    </div>
</x-layouts.app>
