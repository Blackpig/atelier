<?php

namespace BlackpigCreatif\Atelier\Blocks;

use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
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

    /**
     * Video blocks generate their own standalone VideoObject schema.
     */
    public function hasStandaloneSchema(): bool
    {
        return ! empty($this->get('video_url'));
    }

    /**
     * Generate VideoObject schema.
     */
    public function toStandaloneSchema(): ?array
    {
        $videoUrl = $this->get('video_url');

        if (! $videoUrl) {
            return null;
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'VideoObject',
            'contentUrl' => $videoUrl,
        ];

        if ($title = $this->get('title')) {
            $schema['name'] = $title;
        }

        if ($description = $this->get('description')) {
            $schema['description'] = $description;
        }

        // Try to extract embed URL for common platforms
        if ($embedUrl = $this->getEmbedUrl($videoUrl)) {
            $schema['embedUrl'] = $embedUrl;
        }

        return $schema;
    }

    /**
     * Extract embed URL from video URL.
     */
    protected function getEmbedUrl(string $url): ?string
    {
        // YouTube
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\?]+)/', $url, $matches)) {
            return 'https://www.youtube.com/embed/'.$matches[1];
        }

        // Vimeo
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
            return 'https://player.vimeo.com/video/'.$matches[1];
        }

        return null;
    }
}
