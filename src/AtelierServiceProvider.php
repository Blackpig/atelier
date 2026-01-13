<?php

namespace BlackpigCreatif\Atelier;

use BlackpigCreatif\Atelier\Console\Commands\MakeAtelierBlockCommand;
use BlackpigCreatif\Atelier\Livewire\BlockFormModal;
use BlackpigCreatif\Atelier\Models\AtelierBlock;
use BlackpigCreatif\Atelier\Observers\AtelierBlockObserver;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AtelierServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/atelier.php',
            'atelier'
        );
    }
    
    public function boot(): void
    {
        // Register observer
        AtelierBlock::observe(AtelierBlockObserver::class);

        // Register Filament assets
        FilamentAsset::register([
            Css::make('atelier-styles', __DIR__.'/../resources/dist/atelier.css'),
        ], package: 'blackpig-creatif/atelier');

        // Register Livewire components
        Livewire::component('atelier-block-form-modal', BlockFormModal::class);

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'atelier');

        // Register Blade components for icons
        Blade::anonymousComponentPath(__DIR__.'/../resources/views/components/icons', 'atelier.icons');

        // Load translations
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'atelier');
        
        // Publishing
        if ($this->app->runningInConsole()) {
            // Register commands
            $this->commands([
                MakeAtelierBlockCommand::class,
            ]);

            // Publish config
            $this->publishes([
                __DIR__.'/../config/atelier.php' => config_path('atelier.php'),
            ], 'atelier-config');
            
            // Publish migrations
            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'atelier-migrations');
            
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
            
            // Publish translations
            $this->publishes([
                __DIR__.'/../resources/lang' => lang_path('vendor/atelier'),
            ], 'atelier-translations');
            
            // Publish all (common setup - excludes form components to avoid conflicts)
            $this->publishes([
                __DIR__.'/../config/atelier.php' => config_path('atelier.php'),
                __DIR__.'/../database/migrations' => database_path('migrations'),
                __DIR__.'/../resources/views/blocks' => resource_path('views/vendor/atelier/blocks'),
                __DIR__.'/../resources/lang' => lang_path('vendor/atelier'),
            ], 'atelier');
        }
        
        // Register Blade directive for rendering blocks - Laravel 12 syntax
        Blade::directive('renderBlocks', function ($expression) {
            return "<?php echo {$expression}->renderBlocks(app()->getLocale()); ?>";
        });
    }
}
