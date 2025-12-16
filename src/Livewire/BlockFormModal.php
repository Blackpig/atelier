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
    public array $blockData = [];

    protected $listeners = [
        'openBlockFormModal' => 'open',
        'open-block-form-modal' => 'open',
    ];

    public function getListeners()
    {
        return [
            'openBlockFormModal' => 'open',
            'open-block-form-modal' => 'open',
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

        // Let the form system initialize blockData through its statePath
        // This prevents Livewire Entangle errors
        $this->blockData = [];

        // Fill form - this will populate blockData with proper structure
        $this->form->fill($data);

        $this->dispatch('open-modal', id: 'block-form-modal');
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

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
        $this->blockType = null;
        $this->uuid = null;
        $this->componentStatePath = null;
        $this->blockData = [];

        // Reset form state
        $this->form->fill([]);

        $this->dispatch('close-modal', id: 'block-form-modal');
    }

    public function render()
    {
        return view('atelier::livewire.block-form-modal');
    }
}
