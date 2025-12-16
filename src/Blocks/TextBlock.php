<?php

namespace BlackpigCreatif\Atelier\Blocks;

use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
use BlackpigCreatif\Atelier\Concerns\HasCommonOptions;
use BlackpigCreatif\Atelier\Forms\Components\TranslatableContainer;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Illuminate\Contracts\View\View;

class TextBlock extends BaseBlock
{
    use HasCommonOptions;

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
            Section::make('Content')
                ->schema([
                    TranslatableContainer::make()
                        ->translatableFields([
                            TextInput::make('title')
                                ->label('Title')
                                ->maxLength(255)
                                ->placeholder('Optional section title'),

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
                                ->placeholder('Your content here...'),
                        ])
                        ->columnSpanFull(),
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
}
