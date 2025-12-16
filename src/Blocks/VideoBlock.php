<?php

namespace BlackpigCreatif\Atelier\Blocks;

use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
use BlackpigCreatif\Atelier\Concerns\HasCommonOptions;
use BlackpigCreatif\Atelier\Forms\Components\TranslatableContainer;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Illuminate\Contracts\View\View;

class VideoBlock extends BaseBlock
{
    use HasCommonOptions;

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
            Section::make('Content')
                ->schema([
                    TranslatableContainer::make()
                        ->translatableFields([
                            TextInput::make('title')
                                ->label('Title')
                                ->maxLength(255)
                                ->placeholder('Optional video title'),

                            Textarea::make('description')
                                ->label('Description')
                                ->rows(2)
                                ->maxLength(500)
                                ->placeholder('Optional video description'),
                        ])
                        ->columnSpanFull(),

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
}
