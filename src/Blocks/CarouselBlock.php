<?php

namespace BlackpigCreatif\Atelier\Blocks;

use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
use BlackpigCreatif\Atelier\Concerns\HasCommonOptions;
use BlackpigCreatif\Atelier\Conversions\BlockGalleryConversion;
use BlackpigCreatif\Atelier\Forms\Components\TranslatableContainer;
use BlackpigCreatif\ChambreNoir\Concerns\HasRetouchMedia;
use BlackpigCreatif\ChambreNoir\Forms\Components\RetouchMediaUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Illuminate\Contracts\View\View;

class CarouselBlock extends BaseBlock
{
    use HasCommonOptions, HasRetouchMedia;

    public static function getLabel(): string
    {
        return 'Carousel';
    }

    public static function getDescription(): ?string
    {
        return 'Image carousel/slider with navigation controls.';
    }

    public static function getIcon(): string
    {
        return 'atelier.icons.carousel';
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
                                ->placeholder('Optional carousel title'),
                        ])
                        ->columnSpanFull(),

                    RetouchMediaUpload::make('images')
                        ->label('Images')
                        ->preset(BlockGalleryConversion::class)
                        ->imageEditor()
                        ->multiple()
                        ->reorderable()
                        ->deletable(true)
                        ->disk('public')
                        ->directory('blocks/carousel')
                        ->visibility('public')
                        ->acceptedFileTypes(['image/*'])
                        ->required()
                        ->minFiles(2)
                        ->maxFiles(20)
                        ->helperText('Upload 2-20 images. Each image auto-generates thumb, medium, and large sizes.')
                        ->columnSpanFull(),
                ])
                ->collapsible(),

            Section::make('Settings')
                ->schema([
                    Group::make([
                        Select::make('height')
                            ->label('Height')
                            ->options([
                                'h-64' => 'Small (256px)',
                                'h-96' => 'Medium (384px)',
                                'h-[600px]' => 'Large (600px)',
                            ])
                            ->default('h-96')
                            ->native(false),

                        Select::make('aspect_ratio')
                            ->label('Aspect Ratio')
                            ->options([
                                'aspect-auto' => 'Auto',
                                'aspect-video' => '16:9',
                                'aspect-square' => '1:1',
                                'aspect-[4/3]' => '4:3',
                            ])
                            ->default('aspect-auto')
                            ->native(false),
                    ]),

                    Group::make([
                        Toggle::make('autoplay')
                            ->label('Autoplay')
                            ->default(false),

                        Toggle::make('show_dots')
                            ->label('Show Navigation Dots')
                            ->default(true),

                        Toggle::make('show_arrows')
                            ->label('Show Navigation Arrows')
                            ->default(true),
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
