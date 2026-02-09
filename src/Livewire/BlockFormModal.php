<?php

namespace BlackpigCreatif\Atelier\Livewire;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Livewire\Component as LivewireComponent;

class BlockFormModal extends LivewireComponent implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public ?string $blockType = null;

    public ?string $uuid = null;

    public ?string $componentStatePath = null;

    public bool $isOpen = false;

    public bool $isPreview = false;

    public ?string $previewHtml = null;

    public array $blockData = [];

    public array $fieldConfigurations = [];

    protected $listeners = [
        'openBlockFormModal' => 'open',
        'open-block-form-modal' => 'open',
        'openBlockPreview' => 'openPreview',
    ];

    public function getListeners()
    {
        return [
            'openBlockFormModal' => 'open',
            'open-block-form-modal' => 'open',
            'openBlockPreview' => 'openPreview',
        ];
    }

    public function mount(): void
    {
        // Don't initialize form until modal is opened
        // Form will be filled when open() is called
    }

    public function getModalWidth(): string
    {
        return config('atelier.modal.width', '5xl');
    }

    public function form(Schema $schema): Schema
    {
        if (! $this->blockType || ! class_exists($this->blockType)) {
            return $schema->schema([]);
        }

        // STEP 1: Get base schema from block class
        $schemaComponents = $this->blockType::getSchema();

        // STEP 2: Apply global schema modifiers (from service provider)
        foreach (\BlackpigCreatif\Atelier\Support\BlockFieldConfig::getSchemaModifiers($this->blockType) as $modifier) {
            $schemaComponents = $modifier($schemaComponents);
        }

        // STEP 3: Apply per-resource schema modifier (if exists)
        if ($resourceModifier = $this->getResourceSchemaModifier()) {
            $schemaComponents = $resourceModifier($schemaComponents);
        }

        // STEP 4: Register global field configurations as temporary
        $this->registerGlobalConfigurationsForBlock($this->blockType);

        // STEP 5: Register per-resource field configurations as temporary (overrides global)
        if (! empty($this->fieldConfigurations)) {
            foreach ($this->fieldConfigurations as $key => $config) {
                // Skip schema modifiers
                if (str_contains($key, '::__schema_modifier__')) {
                    continue;
                }

                // Handle both formats:
                // 1. Flat format from PHP: 'BlockClass::fieldName' => config
                // 2. Nested format from JavaScript: 'fieldName' => config
                if (str_contains($key, '::')) {
                    [, $fieldName] = explode('::', $key, 2);
                } else {
                    // Already in nested format (just field name)
                    $fieldName = $key;
                }

                \BlackpigCreatif\Atelier\Support\BlockFieldConfig::registerTemporary(
                    $this->blockType,
                    $fieldName,
                    $config
                );
            }
        }

        // STEP 6: Apply field configurations to schema components
        // Merge global configs with per-resource configs (per-resource overrides global)
        $globalConfigs = \BlackpigCreatif\Atelier\Support\BlockFieldConfig::getAllForBlock($this->blockType);
        $resourceConfigs = array_filter(
            $this->fieldConfigurations,
            fn($key) => !str_contains($key, '::__schema_modifier__'),
            ARRAY_FILTER_USE_KEY
        );

        // Convert resource configs to field-name-keyed array
        // Handle both flat format ('BlockClass::fieldName') and nested format ('fieldName')
        $resourceConfigsFormatted = [];
        foreach ($resourceConfigs as $key => $config) {
            if (str_contains($key, '::')) {
                [, $fieldName] = explode('::', $key, 2);
                $resourceConfigsFormatted[$fieldName] = $config;
            } else {
                // Already in nested format (just field name)
                $resourceConfigsFormatted[$key] = $config;
            }
        }

        $allConfigs = array_merge($globalConfigs, $resourceConfigsFormatted);

        if (! empty($allConfigs)) {
            $schemaComponents = $this->applyConfigurationsToComponents($schemaComponents, $allConfigs);
        }

        // STEP 7: Clear temporary configurations after schema is built
        \BlackpigCreatif\Atelier\Support\BlockFieldConfig::clearTemporary();

        return $schema
            ->schema($schemaComponents)
            ->statePath('blockData');
    }

    /**
     * Get per-resource schema modifier for current block
     *
     * @return \Closure|null
     */
    protected function getResourceSchemaModifier(): ?\Closure
    {
        $key = $this->blockType . '::__schema_modifier__';

        if (isset($this->fieldConfigurations[$key]) && $this->fieldConfigurations[$key] instanceof \Closure) {
            return $this->fieldConfigurations[$key];
        }

        return null;
    }

    /**
     * Register all global configurations for a specific block type as temporary
     * This makes global configs available during schema building
     * Per-resource configs will override these when registered afterwards
     */
    protected function registerGlobalConfigurationsForBlock(string $blockType): void
    {
        // Get all global configurations for this block type
        $globalConfigs = \BlackpigCreatif\Atelier\Support\BlockFieldConfig::getAllForBlock($blockType);

        // Register each global config as temporary
        // Per-resource configs will override these when registered afterwards
        foreach ($globalConfigs as $fieldName => $config) {
            \BlackpigCreatif\Atelier\Support\BlockFieldConfig::registerTemporary(
                $blockType,
                $fieldName,
                $config
            );
        }
    }

    /**
     * Recursively apply configurations to components by field name
     * This scans the entire component tree BEFORE they're added to containers
     */
    protected function applyConfigurationsToComponents(array $components, array $configurations, int $depth = 0): array
    {
        foreach ($components as $component) {
            if (! ($component instanceof Component)) {
                continue;
            }

            // Check if this component has a name that matches a configuration
            if (method_exists($component, 'getName')) {
                $componentName = $component->getName();

                if ($componentName && isset($configurations[$componentName]) && is_array($configurations[$componentName])) {
                    // Apply each configuration method
                    foreach ($configurations[$componentName] as $method => $value) {
                        if (method_exists($component, $method)) {
                            $component->{$method}($value);
                        }
                    }
                }
            }

            // Recursively process child components
            // In Filament v4, child components are stored in $childComponents property
            if (property_exists($component, 'childComponents')) {
                try {
                    $reflection = new \ReflectionProperty($component, 'childComponents');
                    $reflection->setAccessible(true);
                    $childComponents = $reflection->getValue($component);

                    // $childComponents is an associative array keyed by 'default' or other keys
                    if (is_array($childComponents) && ! empty($childComponents)) {
                        foreach ($childComponents as $key => $children) {
                            // Children can be an array of components or a closure
                            if (is_array($children)) {
                                $this->applyConfigurationsToComponents($children, $configurations, $depth + 1);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Continue if we can't access the property
                }
            }
        }

        return $components;
    }


    public function open($componentStatePath, $blockType, $uuid = null, $data = [], $fieldConfigurations = []): void
    {
        // Handle both array (from Livewire 3 dispatch) and individual parameters
        if (is_array($componentStatePath)) {
            $params = $componentStatePath;
            $componentStatePath = $params['componentStatePath'] ?? null;
            $blockType = $params['blockType'] ?? null;
            $uuid = $params['uuid'] ?? null;
            $data = $params['data'] ?? [];
            $fieldConfigurations = $params['fieldConfigurations'] ?? [];
        }

        $this->blockType = $blockType;
        $this->uuid = $uuid;
        $this->componentStatePath = $componentStatePath;
        $this->fieldConfigurations = $fieldConfigurations;
        $this->isOpen = true;
        $this->isPreview = false;  // Ensure we're in form mode, not preview
        $this->previewHtml = null;  // Clear any previous preview HTML

        // Remove metadata fields before filling form - they interfere with field hydration
        $cleanData = [];
        foreach ($data as $key => $value) {
            if (! str_starts_with($key, '_')) {
                $cleanData[$key] = $value;
            }
        }

        \Log::info('BlockFormModal open - clean data', [
            'uuid' => $uuid,
            'cleanData' => $cleanData,
            'has_background_image' => isset($cleanData['background_image']),
            'background_image_value' => $cleanData['background_image'] ?? null,
        ]);

        // Let the form system initialize blockData through its statePath
        // This prevents Livewire Entangle errors
        $this->blockData = [];

        // Fill form - this will populate blockData with proper structure
        $this->form->fill($cleanData);

        $this->dispatch('open-modal', id: 'block-form-modal');
    }

    public function save(): void
    {
        try {
            // Validate the form
            $this->form->validate();

            // Get form state - this is our single source of truth
            // Livewire handles all the binding, including nested TranslatableContainer fields
            $data = $this->form->getState();

            \Log::info('BlockFormModal save - using form state directly', [
                'uuid' => $this->uuid,
                'blockType' => $this->blockType,
                'data' => $data,
            ]);

            if (! $this->uuid) {
                // Adding new block
                $this->uuid = (string) Str::uuid();
            }

            // Dispatch event to parent component with the saved data
            $this->dispatch('block-form-saved',
                uuid: $this->uuid,
                type: $this->blockType,
                data: $data,
                componentStatePath: $this->componentStatePath
            );

            $this->close();

            Notification::make()
                ->success()
                ->title('Block saved')
                ->send();

        } catch (\Exception $e) {
            \Log::error('BlockFormModal save error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Notification::make()
                ->danger()
                ->title('Error saving block')
                ->body($e->getMessage())
                ->send();
        }
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->isPreview = false;
        $this->previewHtml = null;
        $this->blockType = null;
        $this->uuid = null;
        $this->componentStatePath = null;
        $this->blockData = [];
        $this->fieldConfigurations = [];

        // Reset form state
        $this->form->fill([]);

        $this->dispatch('close-modal', id: 'block-form-modal');
    }

    public function openPreview($blockType, $data = []): void
    {
        // Handle both array (from Livewire 3 dispatch) and individual parameters
        if (is_array($blockType)) {
            $params = $blockType;
            $blockType = $params['blockType'] ?? null;
            $data = $params['data'] ?? [];
        }

        $this->blockType = $blockType;
        $this->isOpen = true;
        $this->isPreview = true;
        $this->blockData = $data;

        // Generate preview HTML
        $this->previewHtml = $this->generatePreview($blockType, $data);

        $this->dispatch('open-modal', id: 'block-form-modal');
    }

    protected function generatePreview(string $blockType, array $data): string
    {
        if (! class_exists($blockType)) {
            return '<div class="text-red-600 p-4">Block type not found</div>';
        }

        try {
            // Create a new block instance
            $block = new $blockType;

            // Common sensible defaults for all block types
            $defaults = [
                // Hero block defaults
                'height' => 'min-h-[600px]',
                'text_color' => 'text-white',
                'content_alignment' => 'text-center items-center',
                'overlay_opacity' => '40',
                'cta_new_tab' => false,

                // Text block defaults
                'text_alignment' => 'text-left',
                'max_width' => 'max-w-3xl',

                // Image block defaults
                'alignment' => 'center',
                'aspect_ratio' => 'aspect-auto',
                'lightbox' => true,

                // Gallery block defaults
                'columns' => '3',
                'gap' => 'gap-4',
                'auto_rows' => false,

                // Carousel block defaults
                'autoplay' => false,
                'show_dots' => true,
                'show_arrows' => true,

                // Video block defaults
                'muted' => false,

                // Text with image defaults
                'image_position' => 'right',
                'image_width' => '40',
            ];

            // Merge defaults with provided data (data overrides defaults)
            $mergedData = array_merge($defaults, $data);

            // Remove metadata fields for preview
            // These cause issues during preview when not yet saved
            foreach (array_keys($mergedData) as $key) {
                // Remove metadata fields
                if (str_starts_with($key, '_') &&
                    (str_contains($key, '_type') || str_contains($key, '_attribute_id') || str_contains($key, '_collection'))) {
                    unset($mergedData[$key]);
                }
            }

            // Fill the block with merged data
            $block->fill($mergedData);

            // Get the view data
            $viewData = $block->getViewData();

            // Debug logging
            \Log::info('BlockFormModal preview viewData', [
                'viewData' => $viewData,
                'blockType' => $blockType,
            ]);

            // Render the block view
            $viewPath = $block::getViewPath();

            return view($viewPath, $viewData)->render();
        } catch (\Exception $e) {
            \Log::error('BlockFormModal preview error', [
                'blockType' => $blockType,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return '<div class="text-red-600 p-4">Error generating preview: '.htmlspecialchars($e->getMessage()).'</div>';
        }
    }

    public function render()
    {
        return view('atelier::livewire.block-form-modal');
    }
}
