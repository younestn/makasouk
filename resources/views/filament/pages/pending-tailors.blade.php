<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section
            icon="heroicon-o-user-plus"
            heading="Tailor Approval Queue"
            description="Approve new tailor applications and review profile, phone verification, and business documentation before activation."
        >
            <div class="rounded-xl border border-blue-100 bg-blue-50/70 p-4 text-sm text-blue-900 dark:border-blue-400/25 dark:bg-blue-500/10 dark:text-blue-200">
                Reviewing approvals daily keeps response times healthy and ensures only verified providers join live order flow.
            </div>
        </x-filament::section>

        <x-filament::section
            icon="heroicon-o-clipboard-document-list"
            heading="Pending Applications"
            description="Sorted by latest registration first."
        >
            {{ $this->table }}
        </x-filament::section>
    </div>
</x-filament-panels::page>
