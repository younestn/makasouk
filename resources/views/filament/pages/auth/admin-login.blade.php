<x-filament-panels::page.simple class="mk-admin-login-page">
    @php
        $statusMessage = session('status');
        $primaryError = $errors->first('data.email') ?: $errors->first('data.password') ?: $errors->first();
        $workspaceMetrics = [
            [
                'icon' => 'heroicon-o-lock-closed',
                'label' => __('admin_login.metric_security_label'),
                'value' => __('admin_login.metric_security_value'),
            ],
            [
                'icon' => 'heroicon-o-clipboard-document-list',
                'label' => __('admin_login.metric_operations_label'),
                'value' => __('admin_login.metric_operations_value'),
            ],
            [
                'icon' => 'heroicon-o-squares-2x2',
                'label' => __('admin_login.metric_catalog_label'),
                'value' => __('admin_login.metric_catalog_value'),
            ],
        ];
        $workspaceSignals = [
            [
                'icon' => 'heroicon-o-chart-bar',
                'title' => __('admin.auth.point_kpi'),
                'body' => __('admin_login.workspace_signal_activity'),
            ],
            [
                'icon' => 'heroicon-o-cube',
                'title' => __('admin.auth.point_catalog'),
                'body' => __('admin_login.workspace_signal_catalog'),
            ],
            [
                'icon' => 'heroicon-o-shield-check',
                'title' => __('admin.auth.point_governance'),
                'body' => __('admin_login.workspace_signal_governance'),
            ],
        ];
    @endphp

    <div class="mk-auth-shell mk-auth-tone" data-auth-tone="admin">
        <div class="mk-auth-grid">
            <section class="mk-auth-brand-panel" aria-labelledby="mk-admin-login-brand-title">
                <div class="mk-auth-brand-content">
                    <div class="mk-auth-brand-topbar">
                        <span class="mk-auth-pill">
                            <x-filament::icon icon="heroicon-o-shield-check" class="h-4 w-4" />
                            {{ __('admin.auth.pill') }}
                        </span>

                        <span class="mk-auth-status-chip">
                            <span class="mk-auth-status-dot" aria-hidden="true"></span>
                            {{ __('admin_login.status_live') }}
                        </span>
                    </div>

                    <div class="mk-auth-brand-copy">
                        <p class="mk-auth-brand-eyebrow">{{ __('admin_login.panel_label') }}</p>
                        <div class="mk-auth-brandmark" aria-hidden="true">{{ __('admin.brand.short') }}</div>

                        <h2 class="mk-auth-brand-title" id="mk-admin-login-brand-title">
                            {{ __('admin.auth.brand_title') }}
                        </h2>
                        <p class="mk-auth-brand-subtitle">
                            {{ __('admin.auth.brand_subtitle') }}
                        </p>
                    </div>

                    <div class="mk-auth-workspace-card" aria-hidden="true">
                        <div class="mk-auth-workspace-head">
                            <p class="mk-auth-workspace-label">{{ __('admin_login.workspace_label') }}</p>
                            <p class="mk-auth-workspace-note">{{ __('admin_login.workspace_note') }}</p>
                        </div>

                        <div class="mk-auth-workspace-metrics">
                            @foreach ($workspaceMetrics as $metric)
                                <article class="mk-auth-metric-card">
                                    <span class="mk-auth-metric-icon">
                                        <x-filament::icon :icon="$metric['icon']" class="h-4 w-4" />
                                    </span>
                                    <span class="mk-auth-metric-label">{{ $metric['label'] }}</span>
                                    <strong class="mk-auth-metric-value">{{ $metric['value'] }}</strong>
                                </article>
                            @endforeach
                        </div>

                        <div class="mk-auth-signal-grid">
                            @foreach ($workspaceSignals as $signal)
                                <article class="mk-auth-signal-card">
                                    <span class="mk-auth-signal-icon">
                                        <x-filament::icon :icon="$signal['icon']" class="h-4 w-4" />
                                    </span>
                                    <h3 class="mk-auth-signal-title">{{ $signal['title'] }}</h3>
                                    <p class="mk-auth-signal-body">{{ $signal['body'] }}</p>
                                </article>
                            @endforeach
                        </div>
                    </div>

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
                        <span class="mk-auth-form-badge">{{ __('admin_login.form_badge') }}</span>
                        <h3 class="mk-auth-form-title">{{ __('admin.auth.form_title') }}</h3>
                        <p class="mk-auth-form-subtitle">{{ __('admin.auth.form_subtitle') }}</p>
                    </div>

                    @if (filled($statusMessage))
                        <div class="mk-auth-alert mk-auth-alert-success" role="status" aria-live="polite">
                            <x-filament::icon icon="heroicon-o-check-circle" class="h-5 w-5" />
                            <div>
                                <p class="mk-auth-alert-title">{{ __('admin_login.alert_success_title') }}</p>
                                <p class="mk-auth-alert-body">{{ $statusMessage }}</p>
                            </div>
                        </div>
                    @endif

                    @if (filled($primaryError))
                        <div class="mk-auth-alert mk-auth-alert-danger" role="alert" aria-live="assertive">
                            <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-5 w-5" />
                            <div>
                                <p class="mk-auth-alert-title">{{ __('admin_login.alert_error_title') }}</p>
                                <p class="mk-auth-alert-body">{{ $primaryError }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="mk-auth-form-fields">
                        {{ $this->form }}
                    </div>

                    <div class="mk-auth-form-helper">
                        <x-filament::icon icon="heroicon-o-information-circle" class="h-4 w-4" />
                        <span>{{ __('admin_login.form_helper') }}</span>
                    </div>

                    <x-filament-panels::form.actions
                        :actions="$this->getCachedFormActions()"
                        :full-width="$this->hasFullWidthFormActions()"
                    />

                    <div class="mk-auth-submit-state" wire:loading.flex wire:target="authenticate" role="status" aria-live="polite">
                        <x-filament::loading-indicator class="h-4 w-4" />
                        <span>{{ __('admin_login.submit_loading') }}</span>
                    </div>

                    <p class="mk-auth-form-footer">
                        {{ __('admin.auth.form_footer') }}
                    </p>
                </x-filament-panels::form>

                {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
            </section>
        </div>
    </div>
</x-filament-panels::page.simple>
