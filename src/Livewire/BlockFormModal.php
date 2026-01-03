<?php

namespace BlackpigCreatif\Atelier\Livewire;

use Filament\Forms\Components\Component;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Livewire\Component as LivewireComponent;

class BlockFormModal extends LivewireComponent implements HasForms
{
    use InteractsWithForms;

    public ?string $blockType = null;
    public ?string $uuid = null;
    public ?string $componentStatePath = null;
    public bool $isOpen = false;
    public bool $isPreview = false;
    public ?string $previewHtml = null;
    public array $blockData = [];

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
        if (!$this->blockType || !class_exists($this->blockType)) {
            return $schema->schema([]);
        }

        return $schema
            ->schema($this->blockType::getSchema())
            ->statePath('blockData');
    }

    public function open($componentStatePath, $blockType, $uuid = null, $data = []): void
    {
        // Handle both array (from Livewire 3 dispatch) and individual parameters
        if (is_array($componentStatePath)) {
            $params = $componentStatePath;
            $componentStatePath = $params['componentStatePath'] ?? null;
            $blockType = $params['blockType'] ?? null;
            $uuid = $params['uuid'] ?? null;
            $data = $params['data'] ?? [];
        }

        $this->blockType = $blockType;
        $this->uuid = $uuid;
        $this->componentStatePath = $componentStatePath;
        $this->isOpen = true;
        $this->isPreview = false;  // Ensure we're in form mode, not preview
        $this->previewHtml = null;  // Clear any previous preview HTML

        // Remove metadata fields before filling form - they interfere with field hydration
        $cleanData = [];
        foreach ($data as $key => $value) {
            if (!str_starts_with($key, '_')) {
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

            if (!$this->uuid) {
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
        if (!class_exists($blockType)) {
            return '<div class="text-red-600 p-4">Block type not found</div>';
        }

        try {
            // Create a new block instance
            $block = new $blockType();

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

            // Remove metadata fields and Spatie media UUIDs for preview
            // These cause issues during preview when media isn't accessible yet
            foreach (array_keys($mergedData) as $key) {
                // Remove metadata fields
                if (str_starts_with($key, '_') &&
                    (str_contains($key, '_type') || str_contains($key, '_attribute_id') || str_contains($key, '_collection'))) {
                    unset($mergedData[$key]);
                }

                // Remove Spatie media UUID arrays (preview can't display them)
                // Check if value is array of UUIDs
                if (isset($mergedData[$key]) && is_array($mergedData[$key]) && !empty($mergedData[$key])) {
                    $firstItem = reset($mergedData[$key]);
                    if (is_string($firstItem) &&
                        preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $firstItem) &&
                        !str_contains($firstItem, '/')) {
                        // This is a Spatie media UUID array - remove it for preview
                        unset($mergedData[$key]);
                    }
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

            return '<div class="text-red-600 p-4">Error generating preview: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }

    public function render()
    {
        return view('atelier::livewire.block-form-modal');
    }
}
