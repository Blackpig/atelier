<?php

namespace BlackpigCreatif\Atelier\Blocks;

use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
use BlackpigCreatif\Atelier\Concerns\HasCommonOptions;
use BlackpigCreatif\Atelier\Conversions\BlockHeroConversion;
use BlackpigCreatif\ChambreNoir\Concerns\HasRetouchMedia;
use BlackpigCreatif\ChambreNoir\Forms\Components\RetouchMediaUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Illuminate\Contracts\View\View;

class HeroBlock extends BaseBlock
{
    use HasCommonOptions, HasRetouchMedia;

    public static function getLabel(): string
    {
        return 'Hero Section';
    }

    public static function getDescription(): ?string
    {
        return 'Full-width hero section with background image, headline, and call-to-action.';
    }

    public static function getIcon(): string
    {
        return 'atelier.icons.hero';
    }

    public static function getSchema(): array
    {
        return [
            Section::make('Content')
                ->schema([
                    TextInput::make('headline')
                        ->label('Headline')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Your compelling headline')
                        ->translatable(),  // ← Must be LAST

                    TextInput::make('subheadline')
                        ->label('Subheadline')
                        ->maxLength(500)
                        ->placeholder('Supporting text or tagline')
                        ->translatable(),  // ← Must be LAST

                    Textarea::make('description')
                        ->label('Description')
                        ->rows(3)
                        ->maxLength(1000)
                        ->placeholder('Brief description or value proposition')
                        ->translatable(),  // ← Must be LAST
                ])
                ->collapsible(),

            Section::make('Call to Action')
                ->schema([
                    TextInput::make('cta_text')
                        ->label('Button Text')
                        ->maxLength(50)
                        ->placeholder('Get Started')
                        ->translatable(),  // ← Must be LAST

                    TextInput::make('cta_url')
                        ->label('Button URL')
                        ->url()
                        ->placeholder('https://example.com/signup')
                        ->helperText('Shared across all languages'),

                    Toggle::make('cta_new_tab')
                        ->label('Open in new tab')
                        ->default(false),
                ])
                ->collapsible(),

            Section::make('Background & Styling')
                ->schema([
                    RetouchMediaUpload::make('background_image')
                        ->label('Background Image')
                        ->preset(BlockHeroConversion::class)
                        ->imageEditor()
                        ->maxFiles(1)
                        ->deletable(true)
                        ->disk('public')
                        ->directory('blocks/hero')
                        ->visibility('public')
                        ->downloadable()
                        ->maxSize(10240) // 10MB
                        ->hint('Recommended: 1920x1080px or larger. Auto-generates thumb (200x200), medium (800x600), and large (1920x1080) sizes.')
                        ->columnSpanFull(),

                    Group::make([
                        Select::make('overlay_opacity')
                            ->label('Overlay Opacity')
                            ->options([
                                '0' => 'None',
                                '20' => 'Light (20%)',
                                '40' => 'Medium (40%)',
                                '60' => 'Dark (60%)',
                                '80' => 'Very Dark (80%)',
                            ])
                            ->default('40')
                            ->native(false)
                            ->helperText('Darkens the background image for better text readability'),

                        Select::make('text_color')
                            ->label('Text Color')
                            ->options([
                                'text-white' => 'White',
                                'text-gray-900' => 'Dark Gray',
                                'text-primary-600' => 'Primary Color',
                            ])
                            ->default('text-white')
                            ->native(false),
                    ]),

                    Group::make([
                        Select::make('height')
                            ->label('Section Height')
                            ->options([
                                'min-h-[400px]' => 'Small (400px)',
                                'min-h-[600px]' => 'Medium (600px)',
                                'min-h-[800px]' => 'Large (800px)',
                                'min-h-screen' => 'Full Screen',
                            ])
                            ->default('min-h-[600px]')
                            ->native(false),

                        Select::make('content_alignment')
                            ->label('Content Alignment')
                            ->options([
                                'text-left items-start' => 'Left',
                                'text-center items-center' => 'Center',
                                'text-right items-end' => 'Right',
                            ])
                            ->default('text-center items-center')
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
        return ['headline', 'subheadline', 'description', 'cta_text'];
    }

    public function render(): View
    {
        return view(static::getViewPath(), $this->getViewData());
    }

    public function getOverlayClass(): string
    {
        $opacity = $this->get('overlay_opacity', '40');

        if ($opacity === '0') {
            return '';
        }

        return "bg-black/[0.{$opacity}]";
    }

    public function getWrapperClasses(): string
    {
        // Hero block always needs relative positioning for background image/overlay
        $classes = ['relative'];

        // Add common wrapper classes (background, spacing, divider positioning)
        $parentClasses = parent::getWrapperClasses();
        if ($parentClasses) {
            $classes[] = $parentClasses;
        }

        return implode(' ', array_filter($classes));
    }
}
