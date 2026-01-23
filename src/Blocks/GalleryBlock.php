<?php

namespace BlackpigCreatif\Atelier\Blocks;

use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
use BlackpigCreatif\Atelier\Conversions\BlockGalleryConversion;
use BlackpigCreatif\ChambreNoir\Concerns\HasRetouchMedia;
use BlackpigCreatif\ChambreNoir\Forms\Components\RetouchMediaUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Illuminate\Contracts\View\View;

class GalleryBlock extends BaseBlock
{
    use HasRetouchMedia;

    public static function getLabel(): string
    {
        return 'Gallery';
    }

    public static function getDescription(): ?string
    {
        return 'Grid-based image gallery with lightbox support.';
    }

    public static function getIcon(): string
    {
        return 'atelier.icons.gallery';
    }

    public static function getSchema(): array
    {
        return [
            Section::make('Content')
                ->schema([
                    TextInput::make('title')
                        ->label('Title')
                        ->maxLength(255)
                        ->placeholder('Optional gallery title')
                        ->translatable(),

                    RetouchMediaUpload::make('images')
                        ->label('Images')
                        ->preset(BlockGalleryConversion::class)
                        ->imageEditor()
                        ->multiple()
                        ->reorderable()
                        ->deletable(true)
                        ->disk('public')
                        ->directory('blocks/gallery')
                        ->visibility('public')
                        ->acceptedFileTypes(['image/*'])
                        ->required()
                        ->minFiles(3)
                        ->maxFiles(50)
                        ->helperText('Upload 3-50 images. Each image auto-generates thumb, medium, and large sizes.')
                        ->columnSpanFull(),
                ])
                ->collapsible(),

            Section::make('Layout')
                ->schema([
                    Group::make([
                        Select::make('columns')
                            ->label('Columns (Desktop)')
                            ->options([
                                '2' => '2 Columns',
                                '3' => '3 Columns',
                                '4' => '4 Columns',
                            ])
                            ->default('3')
                            ->native(false),

                        Select::make('gap')
                            ->label('Gap Between Images')
                            ->options([
                                'gap-2' => 'Small',
                                'gap-4' => 'Medium',
                                'gap-6' => 'Large',
                                'gap-8' => 'Extra Large',
                            ])
                            ->default('gap-4')
                            ->native(false),
                    ]),

                    Group::make([
                        Toggle::make('lightbox')
                            ->label('Enable Lightbox')
                            ->default(true)
                            ->helperText('Click to view images in full screen'),

                        Toggle::make('auto_rows')
                            ->label('Auto Row Height')
                            ->default(false)
                            ->helperText('Images maintain their aspect ratio'),
                    ]),
                ])
                ->collapsible(),

            // Include common options
            ...static::getCommonOptionsSchema(),
        ];
    }

    public static function getTranslatableFields(): array
    {
        return ['title'];
    }

    public function render(): View
    {
        return view(static::getViewPath(), $this->getViewData());
    }
}
