<?php

namespace BlackpigCreatif\Atelier\Blocks;

use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
use BlackpigCreatif\Atelier\Concerns\HasCommonOptions;
use BlackpigCreatif\Atelier\Concerns\HasMedia;
use BlackpigCreatif\Atelier\Forms\Components\TranslatableContainer;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Illuminate\Contracts\View\View;

class TextWithTwoImagesBlock extends BaseBlock
{
    use HasCommonOptions, HasMedia;
    
    public static function getLabel(): string
    {
        return 'Text with Two Images';
    }
    
    public static function getDescription(): ?string
    {
        return 'Rich text content with two accompanying images in various layout configurations.';
    }
    
    public static function getIcon(): string
    {
        return 'atelier.icons.text-two-images';
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
                                ->placeholder('Section title (optional)'),
                            
                            RichEditor::make('content')
                                ->label('Content')
                                ->required()
                                ->toolbarButtons([
                                    'bold',
                                    'italic',
                                    'link',
                                    'bulletList',
                                    'orderedList',
                                    'h2',
                                    'h3',
                                ]),
                            
                            TextInput::make('image_1_caption')
                                ->label('First Image Caption')
                                ->maxLength(255)
                                ->placeholder('Optional caption'),
                            
                            TextInput::make('image_2_caption')
                                ->label('Second Image Caption')
                                ->maxLength(255)
                                ->placeholder('Optional caption'),
                        ])
                        ->columnSpanFull(),
                ])
                ->collapsible(),
            
            Section::make('Images')
                ->schema([
                    FileUpload::make('image_1')
                        ->label('First Image')
                        ->image()
                        ->imageEditor()
                        ->maxFiles(1)
                        ->deletable(true)
                        ->disk('public')
                        ->directory('blocks/images')
                        ->visibility('public')
                        ->acceptedFileTypes(['image/*'])
                        ->maxSize(10240)
                        ->required(),

                    FileUpload::make('image_2')
                        ->label('Second Image')
                        ->image()
                        ->imageEditor()
                        ->maxFiles(1)
                        ->deletable(true)
                        ->disk('public')
                        ->directory('blocks/images')
                        ->visibility('public')
                        ->acceptedFileTypes(['image/*'])
                        ->maxSize(10240)
                        ->required(),
                ])
                ->columns(2)
                ->collapsible(),
            
            Section::make('Layout')
                ->schema([
                    Select::make('layout')
                        ->label('Layout Style')
                        ->options([
                            'images-left' => 'Images Left, Text Right',
                            'images-right' => 'Images Right, Text Left',
                            'images-stacked-left' => 'Images Stacked Left, Text Right',
                            'images-stacked-right' => 'Images Stacked Right, Text Left',
                            'images-top' => 'Images Side-by-Side Above Text',
                            'images-bottom' => 'Images Side-by-Side Below Text',
                            'masonry' => 'Masonry Grid (Text + Images Mixed)',
                        ])
                        ->default('images-left')
                        ->native(false),
                    
                    Select::make('image_aspect')
                        ->label('Image Aspect Ratio')
                        ->options([
                            'aspect-square' => 'Square (1:1)',
                            'aspect-video' => 'Video (16:9)',
                            'aspect-[4/3]' => 'Standard (4:3)',
                            'aspect-[3/4]' => 'Portrait (3:4)',
                            'aspect-auto' => 'Auto (Natural)',
                        ])
                        ->default('aspect-video')
                        ->native(false),
                    
                    Select::make('image_size')
                        ->label('Image Size')
                        ->options([
                            'small' => 'Small (30% width)',
                            'medium' => 'Medium (40% width)',
                            'large' => 'Large (50% width)',
                        ])
                        ->default('medium')
                        ->native(false)
                        ->visible(fn($get) => in_array($get('layout'), ['images-left', 'images-right', 'images-stacked-left', 'images-stacked-right'])),
                ])
                ->columns(3)
                ->collapsible(),
            
            // Include common options
            ...static::getCommonOptionsSchema(),
        ];
    }
    
    public static function getTranslatableFields(): array
    {
        return ['title', 'content', 'image_1_caption', 'image_2_caption'];
    }
    
    public function render(): View
    {
        return view(static::getViewPath(), $this->getViewData());
    }
    
    public function getImageSizeClass(): string
    {
        return match($this->get('image_size', 'medium')) {
            'small' => 'md:w-[30%]',
            'medium' => 'md:w-[40%]',
            'large' => 'md:w-[50%]',
            default => 'md:w-[40%]',
        };
    }
    
    public function getImage1(): ?string
    {
        return $this->getMediaUrl('image_1');
    }
    
    public function getImage2(): ?string
    {
        return $this->getMediaUrl('image_2');
    }
}
