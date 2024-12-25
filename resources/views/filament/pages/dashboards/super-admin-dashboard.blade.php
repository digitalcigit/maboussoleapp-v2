<x-filament-panels::page>
    <x-filament-widgets::widgets
        :columns="$this->getColumns()"
        :widgets="$this->getHeaderWidgets()"
    />

    <x-filament-widgets::widgets
        :columns="$this->getColumns()"
        :widgets="$this->getFooterWidgets()"
    />
</x-filament-panels::page>
