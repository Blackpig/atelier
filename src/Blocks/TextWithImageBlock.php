<?php

namespace BlackpigCreatif\Atelier\Blocks;

use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
use BlackpigCreatif\Atelier\Concerns\HasCallToActions;
use BlackpigCreatif\Atelier\Conversions\BlockSingleImageConversion;
use BlackpigCreatif\ChambreNoir\Concerns\HasRetouchMedia;
use BlackpigCreatif\ChambreNoir\Forms\Components\RetouchMediaUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

use Illuminate\Contracts\View\View;

class TextWithImageBlock extends BaseBlock
{
    use HasRetouchMedia;
    use HasCallToActions;
    
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
            static::getPublishedField(),

            Section::make('Content')
                ->schema([
                    TextInput::make('title')
                        ->label('Title')
                        ->maxLength(255)
                        ->placeholder('Section title')
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
                            'h3',
                        ])
                        ->placeholder('Your content here...')
                        ->translatable(),

                    RetouchMediaUpload::make('image')
                        ->label('Image')
                        ->preset(BlockSingleImageConversion::class)
                        ->imageEditor()
                        ->maxFiles(1)
                        ->deletable(true)
                        ->disk('public')
                        ->directory('blocks/text-image')
                        ->visibility('public')
                        ->acceptedFileTypes(['image/*'])
                        ->required()
                        ->hint('Auto-generates thumb, medium, and large sizes.')
                        ->columnSpanFull(),
                ])
                ->collapsible(),

                Section::make('Call to Action')
                    ->schema([
                        static::getCallToActionsField()
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
