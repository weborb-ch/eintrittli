<x-layouts.app :title="$event->name">
    <div class="max-w-2xl mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $event->name }}</h1>
            @if ($event->registration_closes_at)
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Registration closes {{ $event->registration_closes_at->format('F j, Y \a\t H:i') }}
                </p>
            @endif
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 sm:p-8">
            <livewire:registration-form :event="$event" />
        </div>
    </div>
</x-layouts.app>
