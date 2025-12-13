<?php

namespace BlackpigCreatif\Atelier;

use BlackpigCreatif\Atelier\Livewire\BlockFormModal;
use BlackpigCreatif\Atelier\Models\AtelierBlock;
use BlackpigCreatif\Atelier\Observers\AtelierBlockObserver;
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

        // Register Livewire components
        Livewire::component('atelier-block-form-modal', BlockFormModal::class);

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'atelier');

        // Load translations
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'atelier');
        
        // Publishing
        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                __DIR__.'/../config/atelier.php' => config_path('atelier.php'),
            ], 'atelier-config');
            
            // Publish migrations
            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'atelier-migrations');
            
            // Publish views
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/atelier'),
            ], 'atelier-views');
            
            // Publish BLOCK TEMPLATES separately for designers
            $this->publishes([
                __DIR__.'/../resources/views/blocks' => resource_path('views/vendor/atelier/blocks'),
            ], 'atelier-block-templates');
            
            // Publish preview views
            $this->publishes([
                __DIR__.'/../resources/views/preview' => resource_path('views/vendor/atelier/preview'),
            ], 'atelier-preview-views');
            
            // Publish translations
            $this->publishes([
                __DIR__.'/../resources/lang' => lang_path('vendor/atelier'),
            ], 'atelier-translations');
            
            // Publish all
            $this->publishes([
                __DIR__.'/../config/atelier.php' => config_path('atelier.php'),
                __DIR__.'/../database/migrations' => database_path('migrations'),
                __DIR__.'/../resources/views' => resource_path('views/vendor/atelier'),
                __DIR__.'/../resources/lang' => lang_path('vendor/atelier'),
            ], 'atelier');
        }
        
        // Register Blade directive for rendering blocks - Laravel 12 syntax
        Blade::directive('renderBlocks', function ($expression) {
            return "<?php echo {$expression}->renderBlocks(app()->getLocale()); ?>";
        });
    }
}
