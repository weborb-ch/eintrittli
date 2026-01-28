<div class="flex flex-col items-center text-center p-6 sm:p-8">
    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">{{ $event->name }}</h3>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mb-6">Scan to register</p>

    <div class="bg-white p-5 rounded-xl">
        {!! (new \Milon\Barcode\DNS2D())->getBarcodeHTML($event->getRegistrationUrl(), 'QRCODE', 8, 8) !!}
    </div>

    <p class="text-xs text-gray-500 dark:text-gray-400 mt-6 max-w-[280px] break-all leading-relaxed">
        {{ $event->getRegistrationUrl() }}
    </p>

    <x-filament::button
        color="gray"
        size="sm"
        icon="heroicon-m-clipboard-document"
        class="mt-4"
        x-data="{ copied: false }"
        x-on:click="navigator.clipboard.writeText('{{ $event->getRegistrationUrl() }}'); copied = true; setTimeout(() => copied = false, 2000)"
        x-text="copied ? 'Copied!' : 'Copy Link'"
    >
        Copy Link
    </x-filament::button>
</div>
