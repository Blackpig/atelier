<?php

namespace BlackpigCreatif\Atelier\Blocks;

use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
use BlackpigCreatif\Atelier\Conversions\BlockGalleryConversion;
use BlackpigCreatif\ChambreNoir\Concerns\HasRetouchMedia;
use BlackpigCreatif\ChambreNoir\Forms\Components\RetouchMediaUpload;
use BlackpigCreatif\ChambreNoir\Services\ConversionManager;
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
            static::getPublishedField(),

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
                        ->minFiles(1)
                        ->maxFiles(50)
                        ->helperText('Upload 1–50 images. Each image auto-generates thumb, medium, and large sizes.')
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

                        Select::make('per_page')
                            ->label('Images per Page')
                            ->options([
                                '5' => '5 per page',
                                '10' => '10 per page',
                                '15' => '15 per page',
                                '20' => '20 per page',
                            ])
                            ->default('5')
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

    public function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            ['imagesData' => $this->getImagesData()]
        );
    }

    /**
     * Returns all available conversion URLs for each image, plus two virtual keys:
     *   - `display`  → the URL used for grid thumbnails (prefers medium/small/thumb)
     *   - `lightbox` → the URL used for the full-screen lightbox  (prefers large)
     *
     * Using named virtual keys means the template is decoupled from whatever
     * conversion preset is configured (BlockGalleryConversion, GalleryConversion, etc.).
     *
     * @return list<array<string, string>>
     */
    public function getImagesData(): array
    {
        $data = $this->get('images');

        if (! $data || ! is_array($data)) {
            return [];
        }

        // Handle a single image stored in ChambreNoir JSON format
        if (isset($data['original'])) {
            $data = [$data];
        }

        $manager = app(ConversionManager::class);
        $result = [];

        foreach ($data as $item) {
            if (! is_array($item) || ! isset($item['original'])) {
                continue;
            }

            $conversionKeys = array_keys($item['conversions'] ?? []);

            if (empty($conversionKeys)) {
                continue;
            }

            // Resolve all stored conversion URLs
            $urls = [];
            foreach ($conversionKeys as $name) {
                $url = $manager->getUrl($item, $name, 'public');
                if ($url) {
                    $urls[$name] = $url;
                }
            }

            if (empty($urls)) {
                continue;
            }

            // display: prefer medium > small > thumb > first available
            $displayUrl = $urls['medium'] ?? $urls['small'] ?? $urls['thumb'] ?? $urls[array_key_first($urls)];

            // lightbox: prefer large > last available
            $lightboxUrl = $urls['large'] ?? $urls[array_key_last($urls)];

            $result[] = array_merge($urls, [
                'display' => $displayUrl,
                'lightbox' => $lightboxUrl,
            ]);
        }

        return $result;
    }

    public function contributesToComposite(): bool
    {
        return true;
    }

    /**
     * @return array{type: string, urls: list<string>}
     */
    public function getCompositeContribution(): array
    {
        return [
            'type' => 'gallery',
            'urls' => $this->getMediaUrls('images', 'large'),
        ];
    }
}
