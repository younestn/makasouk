<x-filament-panels::page.simple class="mk-admin-login-page">
    <div class="mk-auth-shell mk-auth-tone" data-auth-tone="admin">
        <div class="mk-auth-grid">
            <section class="mk-auth-brand-panel">
                <div class="mk-auth-brand-content">
                    <span class="mk-auth-pill">
                        <x-filament::icon icon="heroicon-o-shield-check" class="h-4 w-4" />
                        {{ __('admin.auth.pill') }}
                    </span>

                    <div class="mk-auth-brandmark" aria-hidden="true">MK</div>

                    <h2 class="mk-auth-brand-title">{{ __('admin.auth.brand_title') }}</h2>
                    <p class="mk-auth-brand-subtitle">
                        {{ __('admin.auth.brand_subtitle') }}
                    </p>

                    <ul class="mk-auth-brand-list">
                        <li>
                            <x-filament::icon icon="heroicon-o-chart-bar" class="h-4 w-4" />
                            {{ __('admin.auth.point_kpi') }}
                        </li>
                        <li>
                            <x-filament::icon icon="heroicon-o-cube" class="h-4 w-4" />
                            {{ __('admin.auth.point_catalog') }}
                        </li>
                        <li>
                            <x-filament::icon icon="heroicon-o-shield-check" class="h-4 w-4" />
                            {{ __('admin.auth.point_governance') }}
                        </li>
                    </ul>

                    <p class="mk-auth-trust-note">
                        {{ __('admin.auth.trust_note') }}
                    </p>
                </div>
            </section>

            <section class="mk-auth-form-panel">
                {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

                <x-filament-panels::form id="form" wire:submit="authenticate" class="mk-auth-form-card">
                    <div class="mk-auth-form-header">
                        <h3 class="mk-auth-form-title">{{ __('admin.auth.form_title') }}</h3>
                        <p class="mk-auth-form-subtitle">{{ __('admin.auth.form_subtitle') }}</p>
                    </div>

                    {{ $this->form }}

                    <x-filament-panels::form.actions
                        :actions="$this->getCachedFormActions()"
                        :full-width="$this->hasFullWidthFormActions()"
                    />

                    <p class="mk-auth-form-footer">
                        {{ __('admin.auth.form_footer') }}
                    </p>
                </x-filament-panels::form>

                {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
            </section>
        </div>
    </div>
</x-filament-panels::page.simple>
