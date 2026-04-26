<x-filament-panels::page>
    <form wire:submit="save" class="mk-admin-form-shell space-y-6">
        <x-filament::section
            icon="heroicon-o-information-circle"
            heading="Storefront Configuration"
            description="Control visibility, merchandising sequence, and density of the public shop experience."
        >
            <div class="rounded-xl border border-amber-100 bg-amber-50/70 p-4 text-sm text-amber-900 dark:border-amber-400/25 dark:bg-amber-500/10 dark:text-amber-200">
                Changes applied here affect the public shop page immediately after save.
            </div>
        </x-filament::section>

        {{ $this->form }}

        <div class="flex justify-end">
            <x-filament::button type="submit" icon="heroicon-o-check-circle" color="warning">
                Save Shop Settings
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>