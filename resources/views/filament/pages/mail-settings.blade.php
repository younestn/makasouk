<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex justify-end gap-3">
            <x-filament::button
                type="button"
                color="gray"
                icon="heroicon-o-arrow-path"
                wire:click="mount"
            >
                {{ __('admin.mail.actions.reset') }}
            </x-filament::button>

            <x-filament::button type="submit" icon="heroicon-o-check-circle">
                {{ __('admin.mail.actions.save') }}
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
