<div class="max-w-2xl mx-auto">
    @if ($registration)
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-8 text-center">
            <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h2 class="text-2xl font-bold text-green-800 dark:text-green-200 mb-2">Registration Successful!</h2>
            <p class="text-green-700 dark:text-green-300 mb-4">Your confirmation code:</p>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 inline-block">
                <span class="text-3xl font-mono font-bold text-gray-900 dark:text-white tracking-wider">{{ $registration->confirmation_code }}</span>
            </div>
            <p class="text-sm text-green-600 dark:text-green-400 mt-4">Please save this code for your records.</p>
        </div>
    @elseif (!$event->isRegistrationOpen())
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-8 text-center">
            <svg class="w-16 h-16 text-yellow-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <h2 class="text-2xl font-bold text-yellow-800 dark:text-yellow-200 mb-2">Registration Closed</h2>
            @if ($event->registration_opens_at && $event->registration_opens_at->isFuture())
                <p class="text-yellow-700 dark:text-yellow-300">Registration opens on {{ $event->registration_opens_at->format('F j, Y \a\t H:i') }}</p>
            @else
                <p class="text-yellow-700 dark:text-yellow-300">Registration for this event has ended.</p>
            @endif
        </div>
    @elseif (!$event->form)
        <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-8 text-center">
            <p class="text-gray-600 dark:text-gray-400">No registration form configured for this event.</p>
        </div>
    @else
        <form wire:submit="submit" class="space-y-6">
            @error('form')
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <p class="text-red-700 dark:text-red-300">{{ $message }}</p>
                </div>
            @enderror

            @foreach ($event->form->fields as $field)
                <div>
                    <label for="{{ $field->name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ $field->label }}
                        @if ($field->is_required)
                            <span class="text-red-500">*</span>
                        @endif
                    </label>

                    @switch($field->type)
                        @case(\App\Enums\FormFieldType::Text)
                        @case(\App\Enums\FormFieldType::Email)
                            <input
                                type="{{ $field->type === \App\Enums\FormFieldType::Email ? 'email' : 'text' }}"
                                id="{{ $field->name }}"
                                wire:model="formData.{{ $field->name }}"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                @if($field->is_required) required @endif
                            >
                            @break

                        @case(\App\Enums\FormFieldType::Number)
                            <input
                                type="number"
                                id="{{ $field->name }}"
                                wire:model="formData.{{ $field->name }}"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                @if($field->is_required) required @endif
                            >
                            @break

                        @case(\App\Enums\FormFieldType::Date)
                            <input
                                type="date"
                                id="{{ $field->name }}"
                                wire:model="formData.{{ $field->name }}"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                @if($field->is_required) required @endif
                            >
                            @break

                        @case(\App\Enums\FormFieldType::Boolean)
                            <div class="flex items-center">
                                <input
                                    type="checkbox"
                                    id="{{ $field->name }}"
                                    wire:model="formData.{{ $field->name }}"
                                    class="rounded border-gray-300 dark:border-gray-600 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Yes</span>
                            </div>
                            @break

                        @case(\App\Enums\FormFieldType::Select)
                            <select
                                id="{{ $field->name }}"
                                wire:model="formData.{{ $field->name }}"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                @if($field->is_required) required @endif
                            >
                                <option value="">Select...</option>
                                @foreach ($field->options ?? [] as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                            @break
                    @endswitch

                    @error("formData.{$field->name}")
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            <div class="pt-4">
                <button
                    type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg shadow-sm transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove>Register</span>
                    <span wire:loading>Processing...</span>
                </button>
            </div>
        </form>
    @endif
</div>
