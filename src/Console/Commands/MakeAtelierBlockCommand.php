<?php

namespace BlackpigCreatif\Atelier\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeAtelierBlockCommand extends Command
{
    protected $signature = 'atelier:make-block {name? : The name of the block}';

    protected $description = 'Create a new Atelier block';

    public function handle(): int
    {
        $name = $this->argument('name') ?: $this->ask('What is the block name? (e.g., Quote, Testimonial, CTA)');

        if (empty($name)) {
            $this->error('Block name is required!');

            return self::FAILURE;
        }

        // Ensure name ends with "Block"
        if (! Str::endsWith($name, 'Block')) {
            $name .= 'Block';
        }

        $className = Str::studly($name);
        $kebabName = Str::kebab(str_replace('Block', '', $className));
        $blockLabel = Str::title(str_replace('Block', '', $className));

        // Paths
        $classPath = app_path("BlackpigCreatif/Atelier/Blocks/{$className}.php");
        $viewPath = resource_path("views/blackpig-creatif/atelier/blocks/{$kebabName}-block.blade.php");

        // Check if block already exists
        if (File::exists($classPath)) {
            $this->error("Block already exists: {$classPath}");

            return self::FAILURE;
        }

        // Create directories if they don't exist
        File::ensureDirectoryExists(dirname($classPath));
        File::ensureDirectoryExists(dirname($viewPath));

        // Generate block class
        $classStub = $this->getBlockClassStub();
        $classContent = str_replace(
            ['{{className}}', '{{blockLabel}}', '{{kebabName}}'],
            [$className, $blockLabel, $kebabName],
            $classStub
        );

        File::put($classPath, $classContent);
        $this->info("Block class created: {$classPath}");

        // Generate blade template
        $viewStub = $this->getBlockViewStub();
        $viewContent = str_replace(
            ['{{className}}', '{{blockLabel}}', '{{kebabName}}'],
            [$className, $blockLabel, $kebabName],
            $viewStub
        );

        File::put($viewPath, $viewContent);
        $this->info("Block template created: {$viewPath}");

        // Output usage instructions
        $this->newLine();
        $this->line('<fg=green>Block created successfully!</>');
        $this->newLine();
        $this->line('<fg=yellow>Next steps:</>');
        $this->line('1. Add your block to a BlockManager:');
        $this->line("   <fg=cyan>\\App\\BlackpigCreatif\\Atelier\\Blocks\\{$className}::class</>");
        $this->newLine();
        $this->line("2. Customize the schema in: <fg=cyan>{$classPath}</>");
        $this->line("3. Design the template in: <fg=cyan>{$viewPath}</>");

        return self::SUCCESS;
    }

    protected function getBlockClassStub(): string
    {
        return <<<'PHP'
<?php

namespace App\BlackpigCreatif\Atelier\Blocks;

use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
use BlackpigCreatif\Atelier\Concerns\HasCommonOptions;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\IconSize;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

class {{className}} extends BaseBlock
{
    use HasCommonOptions;

    public static function getLabel(): string
    {
        return '{{blockLabel}}';
    }

    public static function getDescription(): ?string
    {
        return 'Add a description for your {{blockLabel}} block.';
    }

    public static function getIcon(): string | IconSize | Htmlable | null
    {
        // Option 1: Use a Heroicon name (string)
        return 'heroicon-o-chat-bubble-left-right';

        // Option 2: Use custom SVG via HtmlString
        // return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2..."/></svg>');

        // Option 3: Return null for no icon
        // return null;
    }

    public static function getViewPath(): string
    {
        return 'blackpig-creatif.atelier.blocks.{{kebabName}}-block';
    }

    public static function getSchema(): array
    {
        return [
            Section::make('Content')
                ->schema([
                    TextInput::make('title')
                        ->label('Title')
                        ->maxLength(255)
                        ->placeholder('Enter a title')
                        ->translatable(),  // Must be LAST

                    RichEditor::make('content')
                        ->label('Content')
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'link',
                            'bulletList',
                            'orderedList',
                        ])
                        ->placeholder('Enter your content...')
                        ->translatable(),  // Must be LAST
                ])
                ->collapsible(),

            // Include common display options
            ...static::getCommonOptionsSchema(),
        ];
    }

    /**
     * Optional: Define translatable fields for frontend performance optimization.
     *
     * The admin panel automatically detects translatable fields by scanning the schema,
     * so this method is not required. However, including it can improve frontend
     * rendering performance by avoiding schema scanning on every page load.
     *
     * Only include this method if:
     * - Your block is used frequently on high-traffic pages
     * - You want to optimize frontend performance
     * - Your translatable fields are stable and won't change often
     *
     * If omitted, the system will automatically scan the schema to determine
     * which fields are translatable (both in admin and frontend).
     */
    // public static function getTranslatableFields(): array
    // {
    //     return ['title', 'content'];
    // }

    public function render(): View
    {
        return view(static::getViewPath(), $this->getViewData());
    }
}

PHP;
    }

    protected function getBlockViewStub(): string
    {
        return <<<'BLADE'
{{-- resources/views/blackpig-creatif/atelier/blocks/{{kebabName}}-block.blade.php --}}
@php
    /**
     * {{blockLabel}} Block Template
     *
     * Available Variables:
     * @var \App\BlackpigCreatif\Atelier\Blocks\{{className}} $block - The block instance
     * @var string|null $title - Translated title
     * @var string|null $content - Translated HTML content
     *
     * Helper Methods:
     * @method string $block->getTranslated(string $field) - Get translated value for field
     * @method string $block->getWrapperClasses() - Get wrapper classes (background, spacing)
     * @method string $block->getContainerClasses() - Get container classes (width)
     * @method string $block::getBlockIdentifier() - Get block identifier
     * @method string|null $block->getDividerComponent() - Get divider component name
     * @method string|null $block->getDividerToBackground() - Get divider target background class
     */

    $blockIdentifier = 'atelier-' . $block::getBlockIdentifier();
@endphp

<section class="{{ $blockIdentifier }} {{ $block->getWrapperClasses() }}"
         data-block-type="{{ $block::getBlockIdentifier() }}"
         data-block-id="{{ $block->blockId ?? '' }}">

    <div class="{{ $block->getContainerClasses() }}">

        {{-- Title --}}
        @if($title = $block->getTranslated('title'))
            <h2 class="text-3xl md:text-4xl font-bold mb-6 text-gray-900 dark:text-white">
                {{ $title }}
            </h2>
        @endif

        {{-- Content --}}
        @if($content = $block->getTranslated('content'))
            <div class="prose prose-lg max-w-none dark:prose-invert">
                {!! $content !!}
            </div>
        @endif

    </div>

    {{-- Block Divider --}}
    @if($block->getDividerComponent())
        <x-dynamic-component
            :component="$block->getDividerComponent()"
            :to-background="$block->getDividerToBackground()"
        />
    @endif
</section>

BLADE;
    }
}
