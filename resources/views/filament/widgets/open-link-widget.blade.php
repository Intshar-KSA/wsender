@php
    $user = filament()->auth()->user();
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center gap-x-3">
            <div class="flex-1">
                <h2 class="grid flex-1 text-base font-semibold leading-6 text-gray-950 dark:text-white">
                    {{ __('API Documentation') }}
                </h2>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Documentation') }}
                </p>
            </div>

            <a
                href="https://documenter.getpostman.com/view/17450141/2sAYdhKqnx"
                target="_blank"
                rel="noopener noreferrer"
                class="my-auto"
            >
                <x-filament::button
                    color="gray"
                    icon="heroicon-m-arrow-top-right-on-square"
                    icon-alias="panels::widgets.account.documentation-button"
                    labeled-from="sm"
                >
                    {{ __('Open') }}
                </x-filament::button>
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
