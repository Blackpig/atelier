<?php

namespace BlackpigCreatif\Atelier;

use BlackpigCreatif\Atelier\Livewire\BlockFormModal;
use BlackpigCreatif\Atelier\Livewire\LocaleSelector;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Livewire\Livewire;

class AtelierPlugin implements Plugin
{
    protected array $blockClasses = [];

    protected static bool $hasRegisteredLivewireComponents = false;

    public function getId(): string
    {
        return 'atelier';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        return filament(app(static::class)->getId());
    }

    /**
     * Configure panel-specific block classes
     */
    public function blocks(array $blockClasses): static
    {
        $this->blockClasses = $blockClasses;

        return $this;
    }

    public function getBlockClasses(): array
    {
        return ! empty($this->blockClasses)
            ? $this->blockClasses
            : config('atelier.blocks', []);
    }

    public function register(Panel $panel): void
    {
        // Register Filament assets
        FilamentAsset::register([
            Css::make('atelier-styles', __DIR__.'/../resources/dist/atelier.css'),
        ], package: 'blackpig-creatif/atelier');

        // Register Livewire components ONCE globally (with guard)
        if (! static::$hasRegisteredLivewireComponents) {
            Livewire::component('atelier-block-form-modal', BlockFormModal::class);
            Livewire::component('atelier-locale-selector', LocaleSelector::class);
            static::$hasRegisteredLivewireComponents = true;
        }
    }

    public function boot(Panel $panel): void
    {
        // Panel-specific boot logic if needed in future
    }
}
