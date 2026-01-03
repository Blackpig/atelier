<?php

namespace BlackpigCreatif\Atelier\Blocks;

use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
use BlackpigCreatif\Atelier\Concerns\HasCommonOptions;
use BlackpigCreatif\Atelier\Concerns\HasMedia;
use BlackpigCreatif\Atelier\Conversions\BlockGalleryConversion;
use BlackpigCreatif\Atelier\Forms\Components\TranslatableContainer;
use BlackpigCreatif\ChambreNoir\Forms\Components\RetouchMediaUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Illuminate\Contracts\View\View;

class TextWithImageBlock extends BaseBlock
{
    use HasCommonOptions, HasMedia;

    public static function getLabel(): string
    {
        return 'Text + Image';
    }

    public static function getDescription(): ?string
    {
        return 'Text content paired with a single image, side by side or stacked.';
    }

    public static function getIcon(): string
    {
        return 'atelier.icons.text-image';
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
                                ->placeholder('Section title'),

                            RichEditor::make('content')
                                ->label('Content')
                                ->required()
                                ->toolbarButtons([
                                    'bold',
                                    'italic',
                                    'link',
                                    'bulletList',
                                    'orderedList',
                                    'h3',
                                ])
                                ->placeholder('Your content here...'),
                        ])
                        ->columnSpanFull(),

                    RetouchMediaUpload::make('image')
                        ->label('Image')
                        ->preset(BlockGalleryConversion::class)
                        ->image()
                        ->imageEditor()
                        ->maxFiles(1)
                        ->deletable(true)
                        ->disk('public')
                        ->directory('blocks/text-image')
                        ->visibility('public')
                        ->acceptedFileTypes(['image/*'])
                        ->maxSize(5120) // 5MB
                        ->required()
                        ->hint('Auto-generates thumb, medium, and large sizes.')
                        ->columnSpanFull(),
                ])
                ->collapsible(),

            Section::make('Layout')
                ->schema([
                    Select::make('image_position')
                        ->label('Image Position')
                        ->options([
                            'left' => 'Left',
                            'right' => 'Right',
                        ])
                        ->default('right')
                        ->native(false),

                    Select::make('image_width')
                        ->label('Image Width')
                        ->options([
                            '30' => '30%',
                            '40' => '40%',
                            '50' => '50%',
                        ])
                        ->default('40')
                        ->native(false),
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
