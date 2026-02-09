<?php

namespace BlackpigCreatif\Atelier\Blocks;

use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
use BlackpigCreatif\Atelier\Concerns\HasCallToActions;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Illuminate\Contracts\View\View;

class TextBlock extends BaseBlock
{
    use HasCallToActions;

    public static function getLabel(): string
    {
        return 'Text';
    }

    public static function getDescription(): ?string
    {
        return 'Rich text content block with formatting options.';
    }

    public static function getIcon(): string
    {
        return 'atelier.icons.text';
    }

    public static function getSchema(): array
    {
        return [
            static::getPublishedField(),

            Section::make('Content')
                ->schema([
                    TextInput::make('title')
                        ->label('Title')
                        ->maxLength(255)
                        ->placeholder('Optional section title')
                        ->translatable(),
                    
                    TextInput::make('subtitle')
                        ->label('Subtitle')
                        ->maxLength(255)
                        ->placeholder('Optional section subtitle')
                        ->translatable(),

                    RichEditor::make('content')
                        ->label('Content')
                        ->required()
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'link',
                            'bulletList',
                            'orderedList',
                            'h2',
                            'h3',
                        ])
                        ->placeholder('Your content here...')
                        ->translatable(),
                ])
                ->collapsible(),

            Section::make('Call to Action')
                ->schema([
                    static::getCallToActionsField()
                ])
                ->collapsible(),

            Section::make('Settings')
                ->schema([
                    Group::make([
                        Select::make('text_alignment')
                            ->label('Text Alignment')
                            ->options([
                                'text-left' => 'Left',
                                'text-center' => 'Center',
                                'text-right' => 'Right',
                            ])
                            ->default('text-left')
                            ->native(false),

                        Select::make('max_width')
                            ->label('Max Width')
                            ->options([
                                'max-w-none' => 'Full Width',
                                'max-w-4xl' => 'Large (896px)',
                                'max-w-3xl' => 'Medium (768px)',
                                'max-w-2xl' => 'Small (672px)',
                            ])
                            ->default('max-w-3xl')
                            ->native(false),

                        Select::make('columns')
                            ->label('Columns display')
                            ->options([
                                '1' => '1 Column',
                                '2' => '2 Columns',
                                '3' => '3 Columns',
                                '4' => '4 Columns',
                            ])
                            ->default('1')
                            ->native(false),
                    ]),
                ])
                ->collapsible(),

            // Include common options
            ...static::getCommonOptionsSchema(),
        ];
    }

    public static function getTranslatableFields(): array
    {
        return ['title', 'content'];
    }

    public function render(): View
    {
        return view(static::getViewPath(), $this->getViewData());
    }

    /**
     * Text blocks contribute to composite Article schemas.
     */
    public function contributesToComposite(): bool
    {
        return true;
    }

    /**
     * Provide the text content to be included in article body.
     */
    public function getCompositeContribution(): array
    {
        return [
            'type' => 'text',
            'content' => strip_tags($this->get('content') ?? ''),
        ];
    }
}
