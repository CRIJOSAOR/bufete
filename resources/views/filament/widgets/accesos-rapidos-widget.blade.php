<x-filament-widgets::widget>
    <x-filament::section
        heading="Accesos rápidos"
        description="Las acciones más frecuentes, en un solo lugar."
        icon="heroicon-o-bolt"
    >
        <div class="dashboard-quick-actions-grid">
            @foreach ($actions as $action)
                <a
                    href="{{ $action['url'] }}"
                    class="dashboard-quick-action"
                >
                    <span class="dashboard-quick-action-icon">
                        <x-filament::icon :icon="$action['icon']" />
                    </span>
                    <span class="dashboard-quick-action-copy">
                        <span class="dashboard-quick-action-title">
                            {{ $action['label'] }}
                        </span>
                        <span class="dashboard-quick-action-description">
                            {{ $action['description'] }}
                        </span>
                    </span>
                    <x-filament::icon
                        icon="heroicon-m-arrow-up-right"
                        class="dashboard-quick-action-arrow"
                    />
                </a>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
