<x-filament::page>
    <form wire:submit.prevent="saveContacts">
        {{ $this->form }}
        <x-filament::button type="submit" class="mt-6 w-full">
            Save Contacts
        </x-filament::button>
    </form>
</x-filament::page>
