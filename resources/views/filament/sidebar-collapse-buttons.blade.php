<div class="hidden lg:flex">
    <x-filament::icon-button
        color="gray"
        :icon="__('filament-panels::layout.direction') === 'rtl'
            ? \Filament\Support\Icons\Heroicon::OutlinedChevronLeft
            : \Filament\Support\Icons\Heroicon::OutlinedChevronRight"
        icon-size="lg"
        :label="__('filament-panels::layout.actions.sidebar.expand.label')"
        x-cloak
        x-data="{}"
        aria-controls="fi-main-sidebar"
        x-bind:aria-expanded="$store.sidebar.isOpen"
        x-on:click="$store.sidebar.open()"
        x-show="! $store.sidebar.isOpen"
        class="fi-sidebar-open-collapse-sidebar-btn"
    />

    <x-filament::icon-button
        color="gray"
        :icon="__('filament-panels::layout.direction') === 'rtl'
            ? \Filament\Support\Icons\Heroicon::OutlinedChevronRight
            : \Filament\Support\Icons\Heroicon::OutlinedChevronLeft"
        icon-size="lg"
        :label="__('filament-panels::layout.actions.sidebar.collapse.label')"
        x-cloak
        x-data="{}"
        aria-controls="fi-main-sidebar"
        x-bind:aria-expanded="$store.sidebar.isOpen"
        x-on:click="$store.sidebar.close()"
        x-show="$store.sidebar.isOpen"
        class="fi-sidebar-close-collapse-sidebar-btn"
    />
</div>
