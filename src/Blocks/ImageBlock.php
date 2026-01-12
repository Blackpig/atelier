<?php

namespace BlackpigCreatif\Atelier\Blocks;

use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
use BlackpigCreatif\Atelier\Concerns\HasCommonOptions;
use BlackpigCreatif\Atelier\Conversions\BlockGalleryConversion;
use BlackpigCreatif\Atelier\Forms\Components\TranslatableContainer;
use BlackpigCreatif\ChambreNoir\Concerns\HasRetouchMedia;
use BlackpigCreatif\ChambreNoir\Forms\Components\RetouchMediaUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Illuminate\Contracts\View\View;

class ImageBlock extends BaseBlock
{
    use HasCommonOptions, HasRetouchMedia;

    public static function getLabel(): string
    {
        return 'Image';
    }

    public static function getDescription(): ?string
    {
        return 'Single image with optional caption and lightbox support.';
    }

    public static function getIcon(): string
    {
        return 'atelier.icons.image';
    }

    public static function getSchema(): array
    {
        return [
            Section::make('Content')
                ->schema([
                    RetouchMediaUpload::make('image')
                        ->label('Image')
                        ->preset(BlockGalleryConversion::class)
                        ->imageEditor()
                        ->maxFiles(1)
                        ->deletable(true)
                        ->disk('public')
                        ->directory('blocks/image')
                        ->visibility('public')
                        ->acceptedFileTypes(['image/*'])
                        ->maxSize(5120) // 5MB
                        ->required()
                        ->hint('Auto-generates thumb (200x200), medium (800x600), and large (1600x1200) sizes.')
                        ->columnSpanFull(),

                    TranslatableContainer::make()
                        ->translatableFields([
                            TextInput::make('title')
                                ->label('Title')
                                ->maxLength(255)
                                ->placeholder('Optional image title'),

                            Textarea::make('caption')
                                ->label('Caption')
                                ->rows(2)
                                ->maxLength(500)
                                ->placeholder('Optional image caption'),
                        ])
                        ->columnSpanFull(),
                ])
                ->collapsible(),

            Section::make('Layout')
                ->schema([
                    Group::make([
                        Select::make('alignment')
                            ->label('Alignment')
                            ->options([
                                'left' => 'Left',
                                'center' => 'Center',
                                'right' => 'Right',
                            ])
                            ->default('center')
                            ->native(false),

                        Select::make('max_width')
                            ->label('Max Width')
                            ->options([
                                'max-w-full' => 'Full Width',
                                'max-w-5xl' => 'Extra Large',
                                'max-w-4xl' => 'Large',
                                'max-w-3xl' => 'Medium',
                                'max-w-2xl' => 'Small',
                            ])
                            ->default('max-w-4xl')
                            ->native(false),
                    ]),

                    Group::make([
                        Select::make('aspect_ratio')
                            ->label('Aspect Ratio')
                            ->options([
                                'aspect-auto' => 'Auto (Original)',
                                'aspect-video' => '16:9',
                                'aspect-square' => '1:1',
                                'aspect-[4/3]' => '4:3',
                                'aspect-[3/4]' => '3:4',
                            ])
                            ->default('aspect-auto')
                            ->native(false),

                        Toggle::make('lightbox')
                            ->label('Enable Lightbox')
                            ->default(true)
                            ->helperText('Click to view image in full screen'),
                    ]),
                ])
                ->collapsible(),

            // Include common options
            ...static::getCommonOptionsSchema(),
        ];
    }

    public static function getTranslatableFields(): array
    {
        return ['title', 'caption'];
    }

    public function render(): View
    {
        return view(static::getViewPath(), $this->getViewData());
    }
}
