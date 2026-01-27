<div class="text-center p-4">
    <div class="mb-4">
        <h3 class="text-lg font-medium">{{ $event->name }}</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Scan to register</p>
    </div>
    
    <div class="flex justify-center mb-4">
        {!! (new \Milon\Barcode\DNS2D())->getBarcodeHTML($event->getRegistrationUrl(), 'QRCODE', 8, 8) !!}
    </div>
    
    <div class="mt-4">
        <p class="text-xs text-gray-500 dark:text-gray-400 break-all">{{ $event->getRegistrationUrl() }}</p>
    </div>
</div>
