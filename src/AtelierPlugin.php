<?php

namespace BlackpigCreatif\Atelier;

use BlackpigCreatif\Atelier\Filament\Clusters\AtelierDocumentationCluster;
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

        // Register the Atelier user documentation Tome with Grimoire if it is installed.
        if (class_exists(\BlackpigCreatif\Grimoire\Facades\Grimoire::class)) {
            \BlackpigCreatif\Grimoire\Facades\Grimoire::registerTome(
                id: 'atelier',
                label: 'Block Builder',
                icon: 'heroicon-o-squares-2x2',
                path: dirname(__DIR__) . '/resources/grimoire/atelier',
                clusterClass: AtelierDocumentationCluster::class,
                slug: 'atelier',
            );

            // Discover the built-in Cluster and Chapter Page stubs from inside the package.
            $panel->discoverClusters(
                in: __DIR__ . '/Filament/Clusters',
                for: 'BlackpigCreatif\\Atelier\\Filament\\Clusters',
            );

            $panel->discoverPages(
                in: __DIR__ . '/Filament/Pages',
                for: 'BlackpigCreatif\\Atelier\\Filament\\Pages',
            );
        }
    }

    public function boot(Panel $panel): void
    {
        // Panel-specific boot logic if needed in future
    }
}
