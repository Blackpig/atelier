<?php

namespace BlackpigCreatif\Atelier\Blocks;

use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
use BlackpigCreatif\Sceau\Enums\SchemaType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Illuminate\Contracts\View\View;

class VideoBlock extends BaseBlock
{
    public static function getLabel(): string
    {
        return 'Video';
    }

    public static function getDescription(): ?string
    {
        return 'Embed videos from YouTube, Vimeo, or other platforms.';
    }

    public static function getIcon(): string
    {
        return 'atelier.icons.video';
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
                        ->placeholder('Optional video title')
                        ->translatable(),

                    TextInput::make('subtitle')
                        ->label('Subtitle')
                        ->maxLength(255)
                        ->placeholder('Optional video subtitle')
                        ->translatable(),

                    Textarea::make('description')
                        ->label('Description')
                        ->rows(2)
                        ->maxLength(500)
                        ->placeholder('Optional video description')
                        ->translatable(),

                    TextInput::make('video_url')
                        ->label('Video URL')
                        ->required()
                        ->url()
                        ->placeholder('https://www.youtube.com/watch?v=...')
                        ->helperText('Supports YouTube, Vimeo, and direct video URLs')
                        ->columnSpanFull(),
                ])
                ->collapsible(),

            Section::make('Settings')
                ->schema([
                    Group::make([
                        Select::make('aspect_ratio')
                            ->label('Aspect Ratio')
                            ->options([
                                'aspect-video' => '16:9 (Video)',
                                'aspect-square' => '1:1 (Square)',
                                'aspect-[4/3]' => '4:3 (Standard)',
                                'aspect-[3/4]' => '3:4 (Portrait)',
                            ])
                            ->default('aspect-video')
                            ->native(false),

                        Select::make('max_width')
                            ->label('Max Width')
                            ->options([
                                'max-w-7xl' => 'Full Width',
                                'max-w-4xl' => 'Large',
                                'max-w-3xl' => 'Medium',
                                'max-w-2xl' => 'Small',
                            ])
                            ->default('max-w-4xl')
                            ->native(false),
                    ]),

                    Group::make([
                        Toggle::make('autoplay')
                            ->label('Autoplay')
                            ->default(false)
                            ->helperText('Video starts playing automatically'),

                        Toggle::make('muted')
                            ->label('Muted')
                            ->default(false)
                            ->helperText('Start video muted'),
                    ]),
                ])
                ->collapsible(),

            // Include common options
            ...static::getCommonOptionsSchema(),
        ];
    }

    public static function getTranslatableFields(): array
    {
        return ['title', 'description'];
    }

    public function render(): View
    {
        return view(static::getViewPath(), $this->getViewData());
    }

    public function getSchemaType(): ?SchemaType
    {
        return ! empty($this->get('video_url')) ? SchemaType::VideoObject : null;
    }

    /**
     * @return array{content_url: string, embed_url: string|null, name: string|null, description: string|null}
     */
    public function getSchemaData(): array
    {
        $videoUrl = $this->get('video_url', '');

        return [
            'content_url' => $videoUrl,
            'embed_url' => $this->getEmbedUrl($videoUrl),
            'name' => $this->getTranslated('title'),
            'description' => $this->getTranslated('description'),
        ];
    }

    /**
     * Extract platform embed URL from a raw video URL.
     * Supports YouTube and Vimeo; returns null for direct/unknown URLs.
     */
    protected function getEmbedUrl(string $url): ?string
    {
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\?]+)/', $url, $matches)) {
            return 'https://www.youtube.com/embed/'.$matches[1];
        }

        if (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
            return 'https://player.vimeo.com/video/'.$matches[1];
        }

        return null;
    }
}
