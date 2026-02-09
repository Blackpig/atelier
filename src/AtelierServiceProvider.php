<?php

namespace BlackpigCreatif\Atelier;

use BlackpigCreatif\Atelier\Concerns\ConfiguresTranslatableFields;
use BlackpigCreatif\Atelier\Console\Commands\MakeAtelierBlockCommand;
use BlackpigCreatif\Atelier\Console\Commands\MakeAtelierCollectionCommand;
use BlackpigCreatif\Atelier\Models\AtelierBlock;
use BlackpigCreatif\Atelier\Observers\AtelierBlockObserver;
use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AtelierServiceProvider extends PackageServiceProvider
{
    public static string $name = 'atelier';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            ->hasMigrations([
                '2024_12_10_000001_create_atelier_blocks_table',
                '2024_12_10_000002_create_atelier_block_attributes_table',
                '2024_12_17_000001_add_collection_name_to_atelier_block_attributes',
                '2024_12_17_000002_add_collection_index_to_atelier_block_attributes',
            ])
            ->hasCommands([
                MakeAtelierBlockCommand::class,
                MakeAtelierCollectionCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        // Register global block field configurations
        $this->registerGlobalBlockConfigurations();
    }

    public function packageBooted(): void
    {
        // Register observer
        AtelierBlock::observe(AtelierBlockObserver::class);

        // Register translatable macro for Filament fields
        ConfiguresTranslatableFields::configureTranslatableMacro();

        // Register Blade components for icons
        Blade::anonymousComponentPath(
            __DIR__.'/../resources/views/components/icons',
            'atelier.icons'
        );

        // Register Blade directive for rendering blocks - Laravel 12 syntax
        Blade::directive('renderBlocks', function ($expression) {
            return "<?php echo {$expression}->renderBlocks(app()->getLocale()); ?>";
        });

        // Register custom publishable groups
        if ($this->app->runningInConsole()) {
            // Publish BLOCK TEMPLATES for designers to customize
            $this->publishes([
                __DIR__.'/../resources/views/blocks' => resource_path('views/vendor/atelier/blocks'),
            ], 'atelier-block-templates');

            // Publish DIVIDER COMPONENTS (visual elements users might want to customize)
            $this->publishes([
                __DIR__.'/../resources/views/components/dividers' => resource_path('views/vendor/atelier/components/dividers'),
            ], 'atelier-dividers');

            // Publish ALL views (use sparingly - for advanced customization only)
            // WARNING: Publishing form components may cause issues with package updates
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/atelier'),
            ], 'atelier-views-all');
        }
    }

    /**
     * Register global block field configurations
     * These apply to all resources unless overridden per-resource
     *
     * Override this method in your application's service provider
     * to register global block field configurations
     *
     * Example use cases:
     * - Hide fields that aren't needed across all projects
     * - Limit options for fields globally
     * - Set default visibility or behavior
     */
    protected function registerGlobalBlockConfigurations(): void
    {
        // Intentionally empty - override in app provider
    }
}
